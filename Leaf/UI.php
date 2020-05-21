<?php
namespace Leaf;

/**
 * Leaf UI [BETA]
 * ---------------------
 * Simple UI components without markup
 * 
 * @version 1.0.0
 * @since 2.1.0
 * @author Michael Darko <mychi.darko@gmail.com>
 */
class UI {
	protected static $response;
	/**
	 * Elements defined by the user eg: `_avatar`
	 */
	protected static $custom_elements = [];
	/**
	 * A self closing tag
	 */
	public const SINGLE_TAG = "single-tag";
	public const SELF_CLOSING = "self-closing";

	public function __construct() {
		self::$response = new \Leaf\Http\Response;
	}
	/**
	 * Create an HTML element
	 * 
	 * Element Type Options:
	 * - UI::SELF_CLOSING
	 * - UI::SINGLE_TAG
	 * - Ignore to create a normal tag
	 * 
	 * @param string $element The HTML Element to create
	 * @param array $props The Element attributes eg: `style`
	 * @param string|array $children Element's children
	 * @param string $type The type of tag you want to create
	 */
	public static function create_element(string $element, array $props = [], $children = [], string $type = "normal") {
		$type = strtolower($type);
		$attributes = "";
		$subs = "";
		$id = time() . $element;

		if (is_array($children)) {
			foreach ($children as $child) {
				$subs .= $child;
			}
		} else {
			$subs = $children;
		}

		if (!empty($props)) {
			foreach ($props as $key => $value) {
				if ($key != "id") {
					$attributes .= "$key=\"" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "\" ";
				} else {
					$id = $props["id"];
				}
			}
		}

		if ($type == self::SELF_CLOSING) {
			return self::self_closing($element, $attributes, $id);
		}

		if ($type == self::SINGLE_TAG) {
			return self::self_closing($element, $attributes, $id);
		}

		return "<$element $attributes id=\"$id\">$subs</$element>";
	}

	/**
	 * Return a self closing tag
	 * 
	 * @param string $element The element you want to create
	 * @param string $attributes Element attributes eg: `name`, `style`
	 * @param string $id Element id (compulsory)
	 */
	public static function self_closing(string $element, string $attributes, string $id) {
		return "<$element $attributes id=\"$id\" />";
	}

	/**
	 * Return a single tag eg: `meta`, `link`
	 * 
	 * @param string $element The element you want to create
	 * @param string $attributes Element attributes eg: `name`, `style`
	 * @param string $id Element id (compulsory)
	 */
	public static function single_tag(string $element, string $attributes, string $id) {
		return "<$element $attributes id=\"$id\">";
	}

	/**
	 * Map styles to style tag
	 * 
	 * @param array $styles The styles to apply
	 * @param array $props Style tag attributes
	 */
	public static function create_styles(array $styles, array $props) {
		$parsed_styles = "";

		foreach ($styles as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $selector => $styling) {
					$parsed_styles .= "$key { $selector { $styling }}";
				}
			} else {
				$parsed_styles .= "$key { $value }";
			}
		}

		return self::create_element("style", $props, $parsed_styles);
	}

	/**
	 * Create your own element. [Experimental]
	 * 
	 * It is adviced that you parse all custom elements into native HTML code.
	 * 
	 * eg: `_column` parses to HTML <div> and CSS flex
	 * 
	 * Also, although not compulsory, custom elements should start with `_`
	 * 
	 * @param string $name The name of your custom element
	 * @param callable $handler
	 * @param array $props
	 */
	public static function make(string $name, callable $handler) {
		if (is_callable($handler)) {
			self::$custom_elements[$name] = call_user_func($handler, $name);
		}
	}

	/**
	 * Use a custom element
	 * 
	 * @param string $name The custom element you want to use
	 */
	public static function custom(string $name, array $props = [], array $children = [], string $type = "normal") {
		$element = self::$custom_elements[$name];
		$compile_to = "";
		$attributes = "";
		$type = strtolower($type);
		$subs = "";
		$compile_to = isset($element["compile"]) ? $element["compile"] : $name;
		
		$id = time() . $compile_to;

		foreach ($props as $key => $value) {
			if (isset($element[$key])) {
				$element[$key] .= " ".htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			} else {
				$element[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			}
			if ($key == "id") {
				$id = $props["id"];
			}
		}
		if (isset($element["props"])) {
			foreach ($element["props"] as $prop => $value) {
				$attributes .= " $prop=\"$value\""; 
			}
		}
		foreach ($element as $key => $value) {
			if ($key != "props" && $key != "compile") {
				$attributes .= " $key=\"$value\"";
			}
		}
		if (is_array($children)) {
			foreach ($children as $child) {
				$subs .= $child;
			}
		} else {
			$subs = $children;
		}

		if ($type == self::SELF_CLOSING) {
			return self::self_closing($compile_to, $attributes, $id);
		}

		if ($type == self::SINGLE_TAG) {
			return self::self_closing($compile_to, $attributes, $id);
		}

		return "<$compile_to $attributes id=\"$id\">$subs</$compile_to>";
	}

	/**
	 * Render a Leaf UI
	 */
	public static function render($element) {
		self::$response->renderMarkup($element);
	}

	public static function link(string $href, string $rel = "stylesheet", array $props = []) {
		$props["href"] = $href;
		$props["rel"] = $rel;
		return self::create_element("link", $props, [], self::SINGLE_TAG);
	}

	/**
	 * Import/Use a styleheet
	 */
	public static function _style($src, array $props = []) {
		if (!is_array($src)) {
			return self::create_element("link", ["href" => $src, "rel" => "stylesheet"], [], self::SINGLE_TAG);
		}
		return self::create_styles($src, $props);
	}

	/**
	 * Import/Use a script
	 */
	public static function _script($src, array $props = []) {
		if (!is_array($src)) {
			$props["src"] = $src;
			return self::create_element("script", $props);
		}
		return self::create_element("script", $props, $src);
	}

	// -------------------------- GENERAL HTML STUFF --------------------------
	/**
	 * HTML Element
	 */
	public static function html(array $children = [], array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."html";
			$props["id"] = $id;
		}
		return self::create_element("!Doctype html", [], [], self::SINGLE_TAG).self::create_element("html", $props, $children);
	}

	/**
	 * Head Tag
	 */
	public static function head(array $children = [], array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."head";
			$props["id"] = $id;
		}
		return self::create_element("head", $props, $children);
	}

	public static function title(string $title, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."title";
			$props["id"] = $id;
		}
		return self::create_element("title", $props, [$title]);
	}

	public static function meta(string $name, string $content, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."meta";
			$props["id"] = $id;
		}
		$props["name"] = $name;
		$props["content"] = $content;
		return self::create_element("meta", $props, [], self::SINGLE_TAG);
	}

	/**
	 * Body Element
	 */
	public static function body(array $children = [], array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."body";
			$props["id"] = $id;
		}
		return self::create_element("body", $props, $children);
	}

	public static function header(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."header";
			$props["id"] = $id;
		}
		return self::create_element("header", $props, $children);
	}

	public static function footer(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."footer";
			$props["id"] = $id;
		}
		return self::create_element("footer", $props, $children);
	}

	public static function aside(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."aside";
			$props["id"] = $id;
		}
		return self::create_element("aside", $props, $children);
	}

	public static function img(array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."img";
			$props["id"] = $id;
		}
		return self::create_element("img", $props, [], self::SINGLE_TAG);
	}

	public static function figure(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."figure";
			$props["id"] = $id;
		}
		return self::create_element("figure", $props, $children);
	}

	public static function a(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."a";
			$props["id"] = $id;
		}
		return self::create_element("a", $props, $children);
	}

	public static function _link(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."_link";
			$props["id"] = $id;
		}
		return self::create_element("a", $props, $children);
	}


	// --------------------- HTML CONTAINER ELEMENTS -----------------------

	/**
	 * HTML DIV Element
	 */
	public static function div(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."div";
			$props["id"] = $id;
		}
		return self::create_element("div", $props, $children);
	}

	/**
	 * Custom div Element (padding container)
	 */
	public static function _container(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."container";
			$props["id"] = $id;
		}
		if (!isset($props["style"])) {
			$props["style"] = "";
		}
		$props["style"] = "padding: 12px 25px; ".$props["style"];
		return self::create_element("div", $props, $children);
	}
	
	/**
	 * Custom div Element (flex row)
	 */
	public static function _row(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."div";
			$props["id"] = $id;
		}
		if (!isset($props["style"])) {
			$props["style"] = "";
		}
		$props["style"] = "display: flex; ".$props["style"];
		return self::create_element("div", $props, $children);
	}

	/**
	 * Custom div Element (flex column)
	 */
	public static function _column(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."div";
			$props["id"] = $id;
		}
		if (!isset($props["style"])) {
			$props["style"] = "";
		}
		$props["style"] = "display: flex; flex-direction: column; ".$props["style"];
		return self::create_element("div", $props, $children);
	}

	/**
	 * HTML Span Element
	 */
	public static function span(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."span";
			$props["id"] = $id;
		}
		return self::create_element("span", $props, $children);
	}

	public static function section(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."section";
			$props["id"] = $id;
		}
		return self::create_element("section", $props, $children);
	}

	// --------------------------- TYPOGRAPHY -----------------------------

	/**
	 * HTML H1 Element
	 */
	public static function h1(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."h1";
			$props["id"] = $id;
		}
		return self::create_element("h1", $props, $children);
	}

	/**
	 * HTML H2 Element
	 */
	public static function h2(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."h2";
			$props["id"] = $id;
		}
		return self::create_element("h2", $props, $children);
	}

	/**
	 * HTML H3 Element
	 */
	public static function h3(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."h3";
			$props["id"] = $id;
		}
		return self::create_element("h3", $props, $children);
	}

	/**
	 * HTML H4 Element
	 */
	public static function h4(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."h4";
			$props["id"] = $id;
		}
		return self::create_element("h4", $props, $children);
	}

	/**
	 * HTML H5 Element
	 */
	public static function h5(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."h5";
			$props["id"] = $id;
		}
		return self::create_element("h5", $props, $children);
	}

	/**
	 * HTML H6 Element
	 */
	public static function h6(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."h6";
			$props["id"] = $id;
		}
		return self::create_element("h6", $props, $children);
	}

	/**
	 * Custom text Element (span)
	 */
	public static function _text(string $text, array $styles = [], array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."_text";
			$props["id"] = $id;
		}
		if (!isset($styles["color"]))  $styles["color"] = "black";
		if (!isset($styles["size"])) $styles["size"] = "16px";
		if (!isset($styles["weight"])) $styles["weight"] = "normal";
		if (!isset($styles["family"])) $styles["family"] = "sans-serif";
		if (!isset($props["style"])) $props["style"] = "";
		
		$props["style"] = "color: {$styles['color']};font-size: {$styles['size']};font-weight: {$styles['weight']};font-family: {$styles['family']};margin: 0px;".$props["style"];
		return self::create_element("p", $props, [$text]);
	}

	public static function blockquote($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."blockquote";
			$props["id"] = $id;
		}
		return self::create_element("blockquote", $props, is_array($children) ? $children : [$children]);
	}

	public static function tt($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."tt";
			$props["id"] = $id;
		}
		return self::create_element("tt", $props, is_array($children) ? $children : [$children]);
	}

	public static function b($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."b";
			$props["id"] = $id;
		}
		return self::create_element("b", $props, is_array($children) ? $children : [$children]);
	}

	public static function i($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."i";
			$props["id"] = $id;
		}
		return self::create_element("i", $props, is_array($children) ? $children : [$children]);
	}

	public static function u($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."u";
			$props["id"] = $id;
		}
		return self::create_element("u", $props, is_array($children) ? $children : [$children]);
	}

	public static function sub($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."sub";
			$props["id"] = $id;
		}
		return self::create_element("sub", $props, is_array($children) ? $children : [$children]);
	}

	public static function sup($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."sup";
			$props["id"] = $id;
		}
		return self::create_element("sup", $props, is_array($children) ? $children : [$children]);
	}

	public static function uppercase(string $children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."uppercase";
			$props["id"] = $id;
		}
		$children = strtoupper($children);
		return self::create_element("p", $props, [$children]);
	}

	public static function lowercase(string $children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."lowercase";
			$props["id"] = $id;
		}
		$children = strtolower($children);
		return self::create_element("p", $props, [$children]);
	}

	public static function _icon(string $children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."_icon";
			$props["id"] = $id;
		}
		return self::create_element("i", $props, [$children]);
	}

	/**
	 * HTML Paragraph Element
	 */
	public static function p($children, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."p";
			$props["id"] = $id;
		}
		return self::create_element("p", $props, is_array($children) ? $children : [$children]);
	}

	/**
	 * HTML Article Element
	 */
	public static function article(array $props = [], array $children = []) {
		if (!isset($props["id"])) {
			$id = time()."article";
			$props["id"] = $id;
		}
		return self::create_element("article", $props, $children);
	}

	// ----------------- FORM ELEMENTS ----------------------

	/**
	 * Shorthand method for creating an HTML form label
	 * 
	 * @param string $label Label Text
	 * @param string $id Label ID
	 * @param array $props Other attributes eg: `style`
	 */
	public static function label(string $label, string $id = null, array $props = []) {
		if (!$id) {
			$id = time().$label;
		}
		$props["id"] = $id;
		$props["for"] = $id;
		return self::create_element("label", $props, [$label]);
	}
	
	/**
	 * Shorthand method for creating an HTML form input
	 * 
	 * @param string $type Input type
	 * @param string $name Input name
	 * @param array $props Other attributes eg: `style` and `value`
	 * @param string $label Input label text
	 */
	public static function input(string $type, string $name, array $props = []) {
		$id = time().$type;
		$output = "";

		if (!isset($props["id"])) {
			$props["id"] = $id;
		} else {
			$id = $props["id"];
		}

		if (!empty($props) && isset($props['label'])) {
			$output .= self::label($props['label'], $id);
		}

		$props["type"] = $type;
		$props["name"] = $name;

		$output .= self::create_element("input", $props, []);
		return $output;
	}

	/**
	 * HTML Article Element
	 */
	public static function button(string $text, array $props = []) {
		if (!isset($props["id"])) {
			$id = time()."button";
			$props["id"] = $id;
		}
		return self::create_element("button", $props, [$text]);
	}

	/**
	 * Shorthand method for creating an HTML form element
	 * 
	 * @param string $method HTTP method
	 * @param string $action Form action
	 * @param array $fields Form Fields
	 * @param array $props Other attributes eg: `style`
	 */
	public static function form(string $method, string $action, array $fields, array $props = []) {
		$id = time().$action;

		$props["action"] = $action;
		$props["method"] = $method;
		$props["id"] = $id;

		return self::create_element("form", $props, $fields);
	}
}
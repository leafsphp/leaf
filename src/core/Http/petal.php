<?php
namespace Leaf\Core\Http;

class Petal {
	private $request;
	private $supportedHttpMethods = ["GET", "POST"];
	private $callback;
	private $url;
	private $requestMethod;

	function __call($requestMethod, $args) {
		list($url, $callback) = $args;
		$this->url = $url;
		$this->callback = $callback;
		$this->requestMethod = $requestMethod;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getMethod() {
		return $this->requestMethod;
	}

	public function getResponse() {
		$res = "response.data";
		return $res;
	}

	public function getResponseParam($param) {
		$res = 'res[data][$param]';
		return $res;
	}

	function resolve() {
		return call_user_func_array($this->callback, array($this->url));
	}

	function __destruct() {
		$this->resolve();
	}
}
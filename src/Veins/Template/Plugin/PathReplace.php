<?php

/*-
 * Copyright © 2011–2014 Federico Ulfo and a lot of awesome contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * “Software”), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Leaf\Veins\Template\Plugin;

require_once __DIR__ . '/../Plugin.php';

class PathReplace extends \Leaf\Veins\Template\Plugin {
    protected $hooks = array('afterParse');
    private $tags = array('a', 'img', 'link', 'script', 'form', 'input', 'object', 'embed');

    /**
     * replace the path of image src, link href and a href.
     * url => template_dir/url
     * url# => url
     * http://url => http://url
     *
     * @param \ArrayAccess $context
     */
    public function afterParse(\ArrayAccess $context){

        // set variables
        $html = $context->code;
        $template_basedir = $context->template_basedir;
        $tags = $this->tags;
        $basecode = "<?php echo static::\$conf['base_url']; ?>";


        // get the template base directory
        $template_directory = $basecode . $context->conf['veins_dir'] . $context->template_basedir;

        // reduce the path
        $path = str_replace( "://", "@not_replace@", $template_directory );
        $path = preg_replace( "#(/+)#", "/", $path );
        $path = preg_replace( "#(/\./+)#", "/", $path );
        $path = str_replace( "@not_replace@", "://", $path );

        while( preg_match( '#\.\./#', $path ) ){
            $path = preg_replace('#\w+/\.\./#', '', $path );
        }



        $exp = $sub = array();

        if( in_array( "img", $tags ) ){
            $exp = array( '/<img(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<img(.*?)src=(?:")([^"]+?)#(?:")/i', '/<img(.*?)src="(.*?)"/', '/<img(.*?)src=(?:\@)([^"]+?)(?:\@)/i' );
            $sub = array( '<img$1src=@$2://$3@', '<img$1src=@$2@', '<img$1src="' . $path . '$2"', '<img$1src="$2"' );
        }

        if( in_array( "script", $tags ) ){
            $exp = array_merge( $exp , array( '/<script(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<script(.*?)src=(?:")([^"]+?)#(?:")/i', '/<script(.*?)src="(.*?)"/', '/<script(.*?)src=(?:\@)([^"]+?)(?:\@)/i' ) );
            $sub = array_merge( $sub , array( '<script$1src=@$2://$3@', '<script$1src=@$2@', '<script$1src="' . $path . '$2"', '<script$1src="$2"' ) );
        }

        if( in_array( "link", $tags ) ){
            $exp = array_merge( $exp , array( '/<link(.*?)href=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<link(.*?)href=(?:")([^"]+?)#(?:")/i', '/<link(.*?)href="(.*?)"/', '/<link(.*?)href=(?:\@)([^"]+?)(?:\@)/i' ) );
            $sub = array_merge( $sub , array( '<link$1href=@$2://$3@', '<link$1href=@$2@' , '<link$1href="' . $path . '$2"', '<link$1href="$2"' ) );
        }

        if( in_array( "a", $tags ) ){
            $exp = array_merge( $exp , array( '/<a(.*?)href=(?:")(http:\/\/|https:\/\/|javascript:|mailto:|\/|{)([^"]+?)(?:")/i','/<a(.*?)href="(.*?)"/', '/<a(.*?)href=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge( $sub , array( '<a$1href=@$2$3@', '<a$1href="' . $basecode . '$2"', '<a$1href="$2"' ) );
        }

        if( in_array( "input", $tags ) ){
            $exp = array_merge( $exp , array( '/<input(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<input(.*?)src=(?:")([^"]+?)#(?:")/i', '/<input(.*?)src="(.*?)"/', '/<input(.*?)src=(?:\@)([^"]+?)(?:\@)/i' ) );
            $sub = array_merge( $sub , array( '<input$1src=@$2://$3@', '<input$1src=@$2@', '<input$1src="' . $path . '$2"', '<input$1src="$2"' ) );
        }

        if( in_array( "object", $tags ) ){
            $exp = array_merge( $exp , array( '/<object(.*?)data=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<object(.*?)data=(?:")([^"]+?)#(?:")/i', '/<object(.*?)data="(.*?)"/', '/<object(.*?)data=(?:\@)([^"]+?)(?:\@)/i' ) );
            $sub = array_merge( $sub , array( '<object$1data=@$2://$3@', '<object$1data=@$2@' , '<object$1data="' . $path . '$2"', '<object$1data="$2"' ) );
        }

        if( in_array( "embed", $tags ) ){
            $exp = array_merge( $exp , array( '/<embed(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<embed(.*?)src=(?:")([^"]+?)#(?:")/i', '/<embed(.*?)src="(.*?)"/', '/<embed(.*?)src=(?:\@)([^"]+?)(?:\@)/i' ) );
            $sub = array_merge( $sub , array( '<embed$1src=@$2://$3@', '<embed$1src=@$2@', '<embed$1src="' . $path . '$2"', '<embed$1src="$2"' ) );
        }

	    if( in_array( "form", $tags ) ){
		    $exp = array_merge( $exp , array( '/<form(.*?)action="(.*?)"/' ) );
		    $sub = array_merge( $sub , array( '<form$1action="' . $basecode . '$2"' ) );
	    }

        $context->code = preg_replace( $exp, $sub, $html );
    }



    public function setTags($tags) {
        $this->tags = (array) $tags;
        return $this;
    }

}

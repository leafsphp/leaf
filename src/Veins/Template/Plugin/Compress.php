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

class Compress extends \Leaf\Veins\Template\Plugin {

    protected $hooks = array('afterDraw'),
              $cache_dir,
              $conf;

    protected static $configure = array('html'      =>array('status'=>true),
                                        'css'       =>array('status'=>true),
                                        'javascript'=>array('status'=>true, 'position'=>'bottom'),
                                       );
    /**
     * Initialize the local configuration
     */
    public function __construct(){
        $this->conf = self::$configure;
    }

    /**
     * Function called in the hook afterDraw
     * @param \ArrayAccess $context
     */
    public function afterDraw(\ArrayAccess $context) {

        // get the cache directory
        $this->cache_dir  = $context->conf['cache_dir'];

        $html = $context->code;
        if( $this->conf['css']['status'] )
            $html = $this->compressStyle( $html );

        if( $this->conf['javascript']['status'] )
            $html = self::compressScript( $html );

        if( $this->conf['html']['status'] )
            $html = $this->compressHTML($html);

        // save the compressed code
        $context->code = $html;
    }

    /**
     * Compress the HTML
     * @param type $html
     * @return type
     */
    protected function compressHTML($html) {

        // Set PCRE recursion limit to sane value = STACKSIZE / 500
        // ini_set("pcre.recursion_limit", "524"); // 256KB stack. Win32 Apache
        ini_set("pcre.recursion_limit", "16777");  // 8MB stack. *nix
        $re = '%# Collapse whitespace everywhere but in blacklisted elements.
                (?>             # Match all whitespans other than single space.
                [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
                | \s{2,}        # or two or more consecutive-any-whitespace.
                ) # Note: The remaining regex consumes no text at all...
                (?=             # Ensure we are not in a blacklist tag.
                [^<]*+        # Either zero or more non-"<" {normal*}
                (?:           # Begin {(special normal*)*} construct
                <           # or a < starting a non-blacklist tag.
                (?!/?(?:textarea|pre|script)\b)
                [^<]*+      # more non-"<" {normal*}
                )*+           # Finish "unrolling-the-loop"
                (?:           # Begin alternation group.
                <           # Either a blacklist start tag.
                (?>textarea|pre|script)\b
                | \z          # or end of file.
                )             # End alternation group.
                )  # If we made it here, we are not in a blacklist tag.
                %Six';
        $html = preg_replace($re, " ", $html);
        if ($html === null)
            exit("PCRE Error! File too big.\n");
        return $html;
    }



    /**
     * Compress the CSS
     * @param type $html
     * @return type
     */
    protected function compressStyle($html) {

        // search for all stylesheet
        if (!preg_match_all("/<link.*href=\"(.*?\.css)\".*>/", $html, $matches))
            return $html; // return the HTML if doesn't find any

        // prepare the variables
        $externalUrl = array();
        $css = $cssName = "";
        $urlArray = array();

        $cssFiles = $matches[1];
        $md5Name = "";
        foreach( $cssFiles as $file ){
            $md5Name .= basename($file);
        }

        $cachedFilename = md5($md5Name);
        $cacheFolder = $this->cache_dir . "compress/css/"; // css cache folder
        $cachedFilepath = $cacheFolder . $cachedFilename . ".css";

        if( !file_exists($cachedFilepath) ){

            // read all the CSS found
            foreach ($cssFiles as $url) {

                // if a CSS is repeat it takes only the first
                if (empty($urlArray[$url])) {

                    $urlArray[$url] = 1;

                    // parse the URL
                    $parse = parse_url($url);

                    // read file
                    $stylesheetFile = file_get_contents($url);

                    // optimize image URL
                    if (preg_match_all("/url\({0,1}(.*?)\)/", $stylesheetFile, $matches)) {
                        foreach ($matches[1] as $imageUrl) {
                            $imageUrl = preg_replace("/'|\"/", "", $imageUrl);
                            dirname($url) . "/" . $imageUrl;
                            $real_path = reduce_path("../../../" . dirname($url) . "/" . $imageUrl);
                            $stylesheetFile = str_replace($imageUrl, $real_path, $stylesheetFile);
                        }
                    }

                    // remove the comments
                    $stylesheetFile = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $stylesheetFile);

                    // minify the CSS
                    $stylesheetFile = preg_replace("/\n|\r|\t|\s{4}/", "", $stylesheetFile);

                    $css .= "/*---\n CSS compressed in Veins \n {$url} \n---*/\n\n" . $stylesheetFile . "\n";
                }
            }

            if (!is_dir($cacheFolder))
                mkdir($cacheFolder, 0755, $recursive = true);

            // save the stylesheet
            file_put_contents($cachedFilepath, $css);

        }

        // remove all the old stylesheet from the page
        $html = preg_replace("/<link.*href=\"(.*?\.css)\".*>/", "", $html);

        // create the tag for the stylesheet
        $tag = '<link href="' . $cachedFilepath . '" rel="stylesheet" type="text/css">';

        // add the tag to the end of the <head> tag
        $html = str_replace("</head>", $tag . "\n</head>", $html);

        // return the stylesheet
        return $html;
    }



    /**
     * Compress the CSS
     * @param type $html
     * @return type
     */
    protected function compressScript($html) {

        $htmlToCheck = preg_replace("<!--.*?-->", "", $html);

        // search for javascript
        preg_match_all("/<script.*src=\"(.*?\.js)\".*>/", $htmlToCheck, $matches);
        $externalUrl = array();
        $javascript = "";

        $javascriptFiles = $matches[1];
        $md5Name = "";
        foreach( $javascriptFiles as $file ){
            $md5Name .= basename($file);
        }

        $cachedFilename = md5($md5Name);
        $cacheFolder = $this->cache_dir . "compress/js/"; // css cache folder
        $cachedFilepath = $cacheFolder . $cachedFilename . ".js";


        if( !file_exists($cachedFilepath) ){
            foreach ($matches[1] as $url) {

                // if a JS is repeat it takes only the first
                if (empty($urlArray[$url])) {
                    $urlArray[$url] = $url;

                    // reduce the path
                    $url = \Leaf\Veins\Template\Parser::reducePath( $url );

                    $javascriptFile = file_get_contents($url);

                    // minify the js
                    $javascriptFile = preg_replace("#/\*.*?\*/#", "", $javascriptFile);
                    $javascriptFile = preg_replace("#\n+|\t+| +#", " ", $javascriptFile);

                    $javascript .= "/*---\n Javascript compressed in Veins \n {$url} \n---*/\n\n" . $javascriptFile . "\n\n";

                }
            }

            if (!is_dir($cacheFolder))
                mkdir($cacheFolder, 0755, $recursive = true);

            // save the stylesheet
            file_put_contents($cachedFilepath, $javascript);

        }

        $html = preg_replace("/<script.*src=\"(.*?\.js)\".*>/", "", $html);
        $tag = '<script src="' . $cachedFilepath . '"></script>';

        if( $this->conf['javascript']['position'] == 'bottom' ){
            $html = preg_replace("/<\/body>/", $tag . "</body>", $html);
        }
        else{
            $html = preg_replace("/<head>/", "<head>\n".$tag, $html);
        }

        return $html;
    }

    public function configure( $setting, $value ){
        $this->conf[$setting] = self::$configure[$setting] = $value;
    }

    public function configureLocal( $setting, $value ){
        $this->conf[$setting] = $value;
    }

}

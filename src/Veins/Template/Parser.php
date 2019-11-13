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

namespace Leaf\Veins\Template;

/**
 *  LeafVeins
 *  --------
 *  Official Templating Engine for LeafPHP framework
 */
class Parser {

    // variables
    public $var = array();

    protected $templateInfo = array(),
        $config = array(),
        $objectConf = array();

    /**
     * Plugin container
     *
     * @var \Leaf\Veins\Template\PluginContainer
     */
    protected static $plugins = null;

    // configuration
    protected static $conf = array();

    // tags registered by the developers
    protected static $registered_tags = array();

    // tags natively supported
    protected static $tags = array(
        'loop' => array(
            '({loop.*?})',
            '/{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/'
        ),
        'loop_close' => array('({\/loop})', '/{\/loop}/'),
        'loop_break' => array('({break})', '/{break}/'),
        'loop_continue' => array('({continue})', '/{continue}/'),
        'foreach' => array(
            '({foreach.*?})',
            '/{foreach="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/'
        ),
        'foreach_close' => array('({\/foreach})', '/{\/foreach}/'),
        'foreach_break' => array('({break})', '/{break}/'),
        'foreach_continue' => array('({continue})', '/{continue}/'),
        'if' => array('({if.*?})', '/{if="([^"]*)"}/'),
        'elseif' => array('({elseif.*?})', '/{elseif="([^"]*)"}/'),
        'else' => array('({else})', '/{else}/'),
        'if_close' => array('({\/if})', '/{\/if}/'),
        'autoescape' => array('({autoescape.*?})', '/{autoescape="([^"]*)"}/'),
        'autoescape_close' => array('({\/autoescape})', '/{\/autoescape}/'),
        'noparse' => array('({noparse})', '/{noparse}/'),
        'noparse_close' => array('({\/noparse})', '/{\/noparse}/'),
        'ignore' => array('({ignore}|{\*)', '/{ignore}|{\*/'),
        'ignore_close' => array('({\/ignore}|\*})', '/{\/ignore}|\*}/'),
        'include' => array('({include.*?})', '/{include="([^"]*)"}/'),
        'function' => array(
            '({function.*?})',
            '/{function="([a-zA-Z_][a-zA-Z_0-9\:]*)(\(.*\)){0,1}"}/'
        ),
        'ternary' => array('({.[^{?}]*?\?.*?\:.*?})', '/{(.[^{?}]*?)\?(.*?)\:(.*?)}/'),
        'variable' => array('({\$.*?})', '/{(\$.*?)}/'),
        'constant' => array('({#.*?})', '/{#(.*?)#{0,1}}/'),
    );

    // black list of functions and variables
    protected static $black_list = array(
        'exec', 'shell_exec', 'pcntl_exec', 'passthru', 'proc_open', 'system',
        'posix_kill', 'posix_setsid', 'pcntl_fork', 'posix_uname', 'php_uname',
        'phpinfo', 'popen', 'file_get_contents', 'file_put_contents', 'rmdir',
        'mkdir', 'unlink', 'highlight_contents', 'symlink',
        'apache_child_terminate', 'apache_setenv', 'define_syslog_variables',
        'escapeshellarg', 'escapeshellcmd', 'eval', 'fp', 'fput',
        'ftp_connect', 'ftp_exec', 'ftp_get', 'ftp_login', 'ftp_nb_fput',
        'ftp_put', 'ftp_raw', 'ftp_rawlist', 'highlight_file', 'ini_alter',
        'ini_get_all', 'ini_restore', 'inject_code', 'mysql_pconnect',
        'openlog', 'passthru', 'php_uname', 'phpAds_remoteInfo',
        'phpAds_XmlRpc', 'phpAds_xmlrpcDecode', 'phpAds_xmlrpcEncode',
        'posix_getpwuid', 'posix_kill', 'posix_mkfifo', 'posix_setpgid',
        'posix_setsid', 'posix_setuid', 'posix_uname', 'proc_close',
        'proc_get_status', 'proc_nice', 'proc_open', 'proc_terminate',
        'syslog', 'xmlrpc_entity_decode'
    );

    public function __construct($config, $plugins, $registered_tags) {
        $this->config = $config;
        static::$plugins = $plugins;
        static::$registered_tags = $registered_tags;
    }

    /**
     * Returns plugin container.
     *
     * @return \Leaf\Veins\Template\PluginContainer
     */
    protected static function getPlugins() {
        return static::$plugins
            ?: static::$plugins = new PluginContainer();
    }

    /**
     * Compile the file and save it in the cache
     *
     * @param string $templateName: name of the template
     * @param string $templateBaseDir
     * @param string $templateDirectory
     * @param string $templateFilepath
     * @param string $parsedTemplateFilepath: cache file where to save the template
     */
    public function compileFile(
        $templateName,
        $templateBasedir,
        $templateDirectory,
        $templateFilepath,
        $parsedTemplateFilepath
    ) {

        // open the template
        $fp = fopen($templateFilepath, "r");

        // lock the file
        if (flock($fp, LOCK_EX)) {

            // save the filepath in the info
            $this->templateInfo['template_filepath'] = $templateFilepath;

            // read the file
            $this->templateInfo['code'] = $code = fread($fp, filesize($templateFilepath));

            // xml substitution
            $code = preg_replace("/<\?xml(.*?)\?>/s", /*<?*/ "##XML\\1XML##", $code);

            // disable php tag
            if (!$this->config['php_enabled'])
                $code = str_replace(array("<?", "?>"), array("&lt;?", "?&gt;"), $code);

            // xml re-substitution
            $code = preg_replace_callback("/##XML(.*?)XML##/s", function( $match ) {
                    return "<?php echo '<?xml " . stripslashes($match[1]) . " ?>'; ?>";
                }, $code);

            $parsedCode = $this->compileTemplate($code, $isString = false, $templateBasedir, $templateDirectory, $templateFilepath);
            $parsedCode = "<?php if(!class_exists('Leaf\Veins\Template')){exit;}?>" . $parsedCode;

            // fix the php-eating-newline-after-closing-tag-problem
            $parsedCode = str_replace("?>\n", "?>\n\n", $parsedCode);

            // create directories
            if (!is_dir($this->config['cache_dir']))
                mkdir($this->config['cache_dir'], 0755, TRUE);

            // check if the cache is writable
            if (!is_writable($this->config['cache_dir']))
                throw new Exception('Cache directory ' . $this->config['cache_dir'] . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to FALSE. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

            // write compiled file
            file_put_contents($parsedTemplateFilepath, $parsedCode);

            // release the file lock
            flock($fp, LOCK_UN);
        }

        // close the file
        fclose($fp);
    }

    /**
     * Compile a string and save it in the cache
     *
     * @param string $templateName: name of the template
     * @param string $templateBaseDir
     * @param string $templateFilepath
     * @param string $parsedTemplateFilepath: cache file where to save the template
     * @param string $code: code to compile
     */
    public function compileString($templateName, $templateBasedir, $templateFilepath, $parsedTemplateFilepath, $code) {

        // open the template
        $fp = fopen($parsedTemplateFilepath, "w");

        // lock the file
        if (flock($fp, LOCK_SH)) {

            // xml substitution
            $code = preg_replace("/<\?xml(.*?)\?>/s", "##XML\\1XML##", $code);

            // disable php tag
            if (!$this->config['php_enabled'])
                $code = str_replace(array("<?", "?>"), array("&lt;?", "?&gt;"), $code);

            // xml re-substitution
            $code = preg_replace_callback("/##XML(.*?)XML##/s", function( $match ) {
                    return "<?php echo '<?xml " . stripslashes($match[1]) . " ?>'; ?>";
                }, $code);

            $parsedCode = $this->compileTemplate($code, $isString = true, $templateBasedir, $templateDirectory = null, $templateFilepath);

            $parsedCode = "<?php if(!class_exists('Leaf\Veins\Template')){exit;}?>" . $parsedCode;

            // fix the php-eating-newline-after-closing-tag-problem
            $parsedCode = str_replace("?>\n", "?>\n\n", $parsedCode);

            // create directories
            if (!is_dir($this->config['cache_dir']))
                mkdir($this->config['cache_dir'], 0755, true);

            // check if the cache is writable
            if (!is_writable($this->config['cache_dir']))
                throw new Exception('Cache directory ' . $this->config['cache_dir'] . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to false. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

            // write compiled file
            fwrite($fp, $parsedCode);

            // release the file lock
            flock($fp, LOCK_UN);
        }

        // close the file
        fclose($fp);
    }

    /**
     * Compile template
     * @access protected
     *
     * @param string $code: code to compile
     */
    protected function compileTemplate($code, $isString, $templateBasedir, $templateDirectory, $templateFilepath) {

        // Execute plugins, before_parse
        $context = $this->getPlugins()->createContext(array(
                'code' => $code,
                'template_basedir' => $templateBasedir,
                'template_filepath' => $templateFilepath,
                'conf' => $this->config,
            ));

        $this->getPlugins()->run('beforeParse', $context);
        $code = $context->code;

        // set tags
        foreach (static::$tags as $tag => $tagArray) {
            list( $split, $match ) = $tagArray;
            $tagSplit[$tag] = $split;
            $tagMatch[$tag] = $match;
        }

        $keys = array_keys(static::$registered_tags);
        $tagSplit += array_merge($tagSplit, $keys);

        //Remove comments
        if ($this->config['remove_comments']) {
            $code = preg_replace('/<!--(.*)-->/Uis', '', $code);
        }

        //split the code with the tags regexp
        $codeSplit = preg_split("/" . implode("|", $tagSplit) . "/", $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        //variables initialization
        $parsedCode = $commentIsOpen = $ignoreIsOpen = NULL;
        $openIf = $loopLevel = 0;

        // if the template is not empty
        if ($codeSplit)

            //read all parsed code
            foreach ($codeSplit as $html) {

                //close ignore tag
                if (!$commentIsOpen && preg_match($tagMatch['ignore_close'], $html))
                    $ignoreIsOpen = FALSE;

                //code between tag ignore id deleted
                elseif ($ignoreIsOpen) {
                    //ignore the code
                }

                //close no parse tag
                elseif (preg_match($tagMatch['noparse_close'], $html))
                    $commentIsOpen = FALSE;

                //code between tag noparse is not compiled
                elseif ($commentIsOpen)
                    $parsedCode .= $html;

                //ignore
                elseif (preg_match($tagMatch['ignore'], $html))
                    $ignoreIsOpen = TRUE;

                //noparse
                elseif (preg_match($tagMatch['noparse'], $html))
                    $commentIsOpen = TRUE;

                //include tag
                elseif (preg_match($tagMatch['include'], $html, $matches)) {

                    //get the folder of the actual template
                    $actualFolder = $templateDirectory;

                    if (is_array($this->config['veins_dir'])) {
                        foreach($this->config['veins_dir'] as $veins) {
                            if (substr($actualFolder, 0, strlen($veins)) == $veins) {
                                $actualFolder = substr($actualFolder, strlen($veins));
                            }
                        }
                    } elseif (substr($actualFolder, 0, strlen($this->config['veins_dir'])) == $this->config['veins_dir']) {
                        $actualFolder = substr($actualFolder, strlen($this->config['veins_dir']));
                    }

                    //get the included template
                    if (strpos($matches[1], '$') !== false) {
                        $includeTemplate = "'$actualFolder'." . $this->varReplace($matches[1], $loopLevel);
                    } else {
                        $includeTemplate = $actualFolder . $this->varReplace($matches[1], $loopLevel);
                    }

                    // reduce the path
                    $includeTemplate = Parser::reducePath( $includeTemplate );

                    if (strpos($matches[1], '$') !== false) {
                        //dynamic include
                        $parsedCode .= '<?php require $this->checkTemplate(' . $includeTemplate . ');?>';

                    } else {
                        //dynamic include
                        $parsedCode .= '<?php require $this->checkTemplate("' . $includeTemplate . '");?>';
                    }

                }

                //loop
                elseif (preg_match($tagMatch['loop'], $html, $matches)) {

                    // increase the loop counter
                    $loopLevel++;

                    //replace the variable in the loop
                    $var = $this->varReplace($matches['variable'], $loopLevel - 1, $escape = FALSE);
                    if (preg_match('#\(#', $var)) {
                        $newvar = "\$newvar{$loopLevel}";
                        $assignNewVar = "$newvar=$var;";
                    } else {
                        $newvar = $var;
                        $assignNewVar = null;
                    }

                    // check black list
                    $this->blackList($var);

                    //loop variables
                    $counter = "\$counter$loopLevel";       // count iteration

                    if (isset($matches['key']) && isset($matches['value'])) {
                        $key = $matches['key'];
                        $value = $matches['value'];
                    } elseif (isset($matches['key'])) {
                        $key = "\$key$loopLevel";               // key
                        $value = $matches['key'];
                    } else {
                        $key = "\$key$loopLevel";               // key
                        $value = "\$value$loopLevel";           // value
                    }



                    //loop code
                    $parsedCode .= "<?php $counter=-1; $assignNewVar if( isset($newvar) && ( is_array($newvar) || $newvar instanceof Traversable ) && sizeof($newvar) ) foreach( $newvar as $key => $value ){ $counter++; ?>";
                }



                //close loop tag
                elseif (preg_match($tagMatch['loop_close'], $html)) {

                    //iterator
                    $counter = "\$counter$loopLevel";

                    //decrease the loop counter
                    $loopLevel--;

                    //close loop code
                    $parsedCode .= "<?php } ?>";
                }

                //break loop tag
                elseif (preg_match($tagMatch['loop_break'], $html)) {
                    //close loop code
                    $parsedCode .= "<?php break; ?>";
                }

                //continue loop tag
                elseif (preg_match($tagMatch['loop_continue'], $html)) {
                    //close loop code
                    $parsedCode .= "<?php continue; ?>";
                }

                //loop
                elseif (preg_match($tagMatch['foreach'], $html, $matches)) {

                    // increase the foreach counter
                    $loopLevel++;

                    //replace the variable in the foreach
                    $var = $this->varReplace($matches['variable'], $loopLevel - 1, $escape = FALSE);
                    if (preg_match('#\(#', $var)) {
                        $newvar = "\$newvar{$loopLevel}";
                        $assignNewVar = "$newvar=$var;";
                    } else {
                        $newvar = $var;
                        $assignNewVar = null;
                    }

                    // check black list
                    $this->blackList($var);

                    //foreach variables
                    $counter = "\$counter$loopLevel";       // count iteration

                    if (isset($matches['key']) && isset($matches['value'])) {
                        $key = $matches['key'];
                        $value = $matches['value'];
                    } elseif (isset($matches['key'])) {
                        $key = "\$key$loopLevel";               // key
                        $value = $matches['key'];
                    } else {
                        $key = "\$key$loopLevel";               // key
                        $value = "\$value$loopLevel";           // value
                    }



                    //foreach code
                    $parsedCode .= "<?php $counter=-1; $assignNewVar if( isset($newvar) && ( is_array($newvar) || $newvar instanceof Traversable ) && sizeof($newvar) ) foreach( $newvar as $key => $value ){ $counter++; ?>";
                }



                //close foreach tag
                elseif (preg_match($tagMatch['foreach_close'], $html)) {

                    //iterator
                    $counter = "\$counter$loopLevel";

                    //decrease the foreach counter
                    $loopLevel--;

                    //close foreach code
                    $parsedCode .= "<?php } ?>";
                }

                //break foreach tag
                elseif (preg_match($tagMatch['foreach_break'], $html)) {
                    //close foreach code
                    $parsedCode .= "<?php break; ?>";
                }

                //continue foreach tag
                elseif (preg_match($tagMatch['foreach_continue'], $html)) {
                    //close foreach code
                    $parsedCode .= "<?php continue; ?>";
                }

                //if
                elseif (preg_match($tagMatch['if'], $html, $matches)) {

                    //increase open if counter (for intendation)
                    $openIf++;

                    //tag
                    $tag = $matches[0];

                    //condition attribute
                    $condition = $matches[1];

                    // check black list
                    $this->blackList($condition);

                    //variable substitution into condition (no delimiter into the condition)
                    $parsedCondition = $this->varReplace($condition, $loopLevel, $escape = FALSE);

                    //if code
                    $parsedCode .= "<?php if( $parsedCondition ){ ?>";
                }

                //elseif
                elseif (preg_match($tagMatch['elseif'], $html, $matches)) {

                    //tag
                    $tag = $matches[0];

                    //condition attribute
                    $condition = $matches[1];

                    // check black list
                    $this->blackList($condition);

                    //variable substitution into condition (no delimiter into the condition)
                    $parsedCondition = $this->varReplace($condition, $loopLevel, $escape = FALSE);

                    //elseif code
                    $parsedCode .= "<?php }elseif( $parsedCondition ){ ?>";
                }

                //else
                elseif (preg_match($tagMatch['else'], $html)) {

                    //else code
                    $parsedCode .= '<?php }else{ ?>';
                }

                //close if tag
                elseif (preg_match($tagMatch['if_close'], $html)) {

                    //decrease if counter
                    $openIf--;

                    // close if code
                    $parsedCode .= '<?php } ?>';
                }

                // autoescape off
                elseif (preg_match($tagMatch['autoescape'], $html, $matches)) {

                    // get function
                    $mode = $matches[1];
                    $this->config['auto_escape_old'] = $this->config['auto_escape'];

                    if ($mode == 'off' or $mode == 'false' or $mode == '0' or $mode == null) {
                        $this->config['auto_escape'] = false;
                    } else {
                        $this->config['auto_escape'] = true;
                    }

                }

                // autoescape on
                elseif (preg_match($tagMatch['autoescape_close'], $html, $matches)) {
                    $this->config['auto_escape'] = $this->config['auto_escape_old'];
                    unset($this->config['auto_escape_old']);
                }

                // function
                elseif (preg_match($tagMatch['function'], $html, $matches)) {

                    // get function
                    $function = $matches[1];

                    // var replace
                    if (isset($matches[2]))
                        $parsedFunction = $function . $this->varReplace($matches[2], $loopLevel, $escape = FALSE, $echo = FALSE);
                    else
                        $parsedFunction = $function . "()";

                    // check black list
                    $this->blackList($parsedFunction);

                    // function
                    $parsedCode .= "<?php echo $parsedFunction; ?>";
                }

                //ternary
                elseif (preg_match($tagMatch['ternary'], $html, $matches)) {
                    $parsedCode .= "<?php echo " . '(' . $this->varReplace($matches[1], $loopLevel, $escape = TRUE, $echo = FALSE) . '?' . $this->varReplace($matches[2], $loopLevel, $escape = TRUE, $echo = FALSE) . ':' . $this->varReplace($matches[3], $loopLevel, $escape = TRUE, $echo = FALSE) . ')' . "; ?>";
                }

                //variables
                elseif (preg_match($tagMatch['variable'], $html, $matches)) {
                    //variables substitution (es. {$title})
                    $parsedCode .= "<?php " . $this->varReplace($matches[1], $loopLevel, $escape = TRUE, $echo = TRUE) . "; ?>";
                }


                //constants
                elseif (preg_match($tagMatch['constant'], $html, $matches)) {
                    $parsedCode .= "<?php echo " . $this->conReplace($matches[1], $loopLevel) . "; ?>";
                }

                // registered tags
                else {

                    $found = FALSE;
                    foreach (static::$registered_tags as $tags => $array) {
                        if (preg_match_all('/' . $array['parse'] . '/', $html, $matches)) {
                            $found = true;
                            $parsedCode .= "<?php echo call_user_func( static::\$registered_tags['$tags']['function'], " . var_export($matches, 1) . " ); ?>";
                        }
                    }

                    if (!$found){
                        $parsedCode .= $html;
                    }
                }
            }


        if ($isString) {
            if ($openIf > 0) {

                $trace = debug_backtrace();
                $caller = array_shift($trace);

                $e = new SyntaxException("Error! You need to close an {if} tag in the string, loaded by {$caller['file']} at line {$caller['line']}");
                throw $e->templateFile($templateFilepath);
            }

            if ($loopLevel > 0) {

                $trace = debug_backtrace();
                $caller = array_shift($trace);
                $e = new SyntaxException("Error! You need to close the {loop} tag in the string, loaded by {$caller['file']} at line {$caller['line']}");
                throw $e->templateFile($templateFilepath);
            }
        } else {
            if ($openIf > 0) {
                $e = new SyntaxException("Error! You need to close an {if} tag in $templateFilepath template");
                throw $e->templateFile($templateFilepath);
            }

            if ($loopLevel > 0) {
                $e = new SyntaxException("Error! You need to close the {loop} tag in $templateFilepath template");
                throw $e->templateFile($templateFilepath);
            }
        }

        $html = str_replace('?><?php', ' ', $parsedCode);

        // Execute plugins, after_parse
        $context->code = $parsedCode;
        $this->getPlugins()->run('afterParse', $context);

        return $context->code;
    }

    protected function varReplace($html, $loopLevel = NULL, $escape = TRUE, $echo = FALSE) {

        // change variable name if loop level
        if (!empty($loopLevel))
            $html = preg_replace(array('/(\$key)\b/', '/(\$value)\b/', '/(\$counter)\b/'), array('${1}' . $loopLevel, '${1}' . $loopLevel, '${1}' . $loopLevel), $html);

        // if it is a variable
        if (preg_match_all('/(\$[a-z_A-Z][^\s]*)/', $html, $matches)) {
            // substitute . and [] with [" "]
            for ($i = 0; $i < count($matches[1]); $i++) {

                $rep = preg_replace('/\[(\${0,1}[a-zA-Z_0-9]*)\]/', '["$1"]', $matches[1][$i]);
                //$rep = preg_replace('/\.(\${0,1}[a-zA-Z_0-9]*)/', '["$1"]', $rep);
                $rep = preg_replace( '/\.(\${0,1}[a-zA-Z_0-9]*(?![a-zA-Z_0-9]*(\'|\")))/', '["$1"]', $rep );
                $html = str_replace($matches[0][$i], $rep, $html);
            }

            // update modifier
            $html = $this->modifierReplace($html);

            // if does not initialize a value, e.g. {$a = 1}
            if (!preg_match('/\$.*=.*/', $html)) {

                // escape character
                if ($this->config['auto_escape'] && $escape)
                    //$html = "htmlspecialchars( $html )";
                    $html = "htmlspecialchars( $html, ENT_COMPAT, '" . $this->config['charset'] . "', FALSE )";

                // if is an assignment it doesn't add echo
                if ($echo)
                    $html = "echo " . $html;
            }
        }

        return $html;
    }

    protected function conReplace($html) {
        $html = $this->modifierReplace($html);
        return $html;
    }

    protected function modifierReplace($html) {

        $this->blackList($html);
        if (strpos($html,'|') !== false && substr($html,strpos($html,'|')+1,1) != "|") {
            preg_match('/([\$a-z_A-Z0-9\(\),\[\]"->]+)\|([\$a-z_A-Z0-9\(\):,\[\]"->\s]+)/i', $html,$result);

            $function_params = $result[1];
            $result[2] = str_replace("::", "@double_dot@", $result[2] );
            $explode = explode(":",$result[2]);
            $function = str_replace('@double_dot@', '::', $explode[0]);
            $params = isset($explode[1]) ? "," . $explode[1] : null;

            $html = str_replace($result[0],$function . "(" . $function_params . "$params)",$html);

            if (strpos($html,'|') !== false && substr($html,strpos($html,'|')+1,1) != "|") {
                $html = $this->modifierReplace($html);
            }
        }

        return $html;
    }

    protected function blackList($html) {

        if (!$this->config['sandbox'] || !static::$black_list)
            return true;

        if (empty($this->config['black_list_preg']))
            $this->config['black_list_preg'] = '#[\W\s]*' . implode('[\W\s]*|[\W\s]*', static::$black_list) . '[\W\s]*#';

        // check if the function is in the black list (or not in white list)
        if (preg_match($this->config['black_list_preg'], $html, $match)) {

            // find the line of the error
            $line = 0;
            $rows = explode("\n", $this->templateInfo['code']);
            while (!strpos($rows[$line], $html) && $line + 1 < count($rows))
                $line++;

            // stop the execution of the script
            $e = new SyntaxException('Syntax ' . $match[0] . ' not allowed in template: ' . $this->templateInfo['template_filepath'] . ' at line ' . $line);
            throw $e->templateFile($this->templateInfo['template_filepath'])
                ->tag($match[0])
                ->templateLine($line);

            return false;
        }
    }

    public static function reducePath( $path ){
        // reduce the path
        $path = str_replace( "://", "@not_replace@", $path );
        $path = preg_replace( "#(/+)#", "/", $path );
        $path = preg_replace( "#(/\./+)#", "/", $path );
        $path = str_replace( "@not_replace@", "://", $path );
        while( preg_match('#\w+\.\./#', $path) ) {
            $path = preg_replace('#\w+/\.\./#', '', $path );
        }

        return $path;
    }
}

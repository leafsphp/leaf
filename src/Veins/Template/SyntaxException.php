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
 * Exception thrown when syntax error occurs.
 */
class SyntaxException extends Exception {

    /**
     * Line in template file where error has occured.
     *
     * @var int | null
     */
    protected $templateLine = null;

    /**
     * Tag which caused an error.
     *
     * @var string | null
     */
    protected $tag = null;

    /**
     * Handles the line in template file
     * where error has occured
     *
     * @param int | null $line
     *
     * @return \Leaf\Veins\Template\SyntaxException | int | null
     */
    public function templateLine($line){
        if(is_null($line))
            return $this->templateLine;

        $this->templateLine = (int) $line;
        return $this;
    }

    /**
     * Handles the tag which caused an error.
     *
     * @param string | null $tag
     *
     * @return \Leaf\Veins\Template_SyntaxException | string | null
     */
    public function tag($tag=null){
        if(is_null($tag))
            return $this->tag;

        $this->tag = (string) $tag;
        return $this;
    }
}

// -- end

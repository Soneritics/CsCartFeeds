<?php
/*
 * The MIT License
 *
 * Copyright 2018 Jordi Jolink.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Class GoogleSoneriticsParserFactory
 */
class SoneriticsFeedParserFactory
{
    /**
     * Parser path
     */
    const PARSER_PATH = __DIR__;

    /**
     * @var string File name pattern
     */
    private static $pattern = 'SoneriticsFeedParser.php';

    /**
     * Get a list of parsers
     * @return array
     */
    public static function getParserList(): array
    {
        $result = [];

        // Save the current path and move to the parser path
        $curDir = getcwd();
        chdir(static::PARSER_PATH);

        // Get parsers
        $files = glob("*" . static::$pattern);
        if ($files !== false && !empty($files)) {
            foreach ($files as $file) {
                $result[$file] = str_replace(static::$pattern, '', $file);
            }
        }

        // Return to the previous path
        chdir($curDir);

        // Return the parser list
        return $result;
    }
}

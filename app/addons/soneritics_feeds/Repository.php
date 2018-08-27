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
 * Repository class for the Soneritics Feeds addon
 */
class SoneriticsFeedRepository
{
    /**
     * Get a list of companies
     * @return array
     */
    public function getCompanyList(): array
    {
        $result = [];
        $iteration = db_get_array("SELECT company_id, company FROM ?:companies");

        if (!empty($iteration)) {
            foreach ($iteration as $i) {
                $result[$i['company_id']] = $i['company'];
            }
        }

        return $result;
    }

    /**
     * Get a list of languages
     * @return array
     */
    public function getLanguageList(): array
    {
        $result = [];
        $iteration = db_get_array("SELECT lang_code, name FROM ?:languages");

        if (!empty($iteration)) {
            foreach ($iteration as $i) {
                $result[$i['lang_code']] = $i['name'];
            }
        }

        return $result;
    }
}

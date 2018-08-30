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
 * Class SoneriticsFeedCurrency
 */
class SoneriticsFeedCurrency
{
    /**
     * @var string
     */
    private $code = '';

    /**
     * @var string
     */
    private $symbol = '';

    /**
     * @var string
     */
    private $description = 'Unknown';

    /**
     * SoneriticsFeedCurrency constructor.
     * @param array $cscartData
     */
    public function __construct(array $cscartData)
    {
        if (!empty($cscartData)) {
            if (count($cscartData) === 1) {
                $this->loadData(reset($cscartData));
            } else {
                foreach ($cscartData as $data) {
                    if (round($data['coefficient'], 2) == 1.00) {
                        $this->loadData(reset($data));
                        break;
                    }
                }
            }
        }
    }

    /**
     * Load the data into the model
     * @param array $data
     */
    private function loadData(array $data)
    {
        $this->code = $data['currency_code'];
        $this->symbol = $data['symbol'];
        $this->description = $data['description'];
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}

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
 * Class DaisyconSoneriticsFeedParser
 */
class DaisyconSoneriticsFeedParser implements ISoneriticsFeedParser
{
    /**
     * @var SoneriticsFeedGlobalData
     */
    private $globalData;

    /**
     * Get the name of the parser
     * @return string
     */
    public static function getName(): string
    {
        return 'Daisycon';
    }

    /**
     * Parse the products into the feed
     * @param array $products
     * @param SoneriticsFeedGlobalData $globalData
     * @param array $parserData
     * @return void
     */
    public function parse(array $products, SoneriticsFeedGlobalData $globalData, array $parserData = [])
    {
        // Set global data
        $this->globalData = $globalData;

        // Send the XML content type header
        header('Content-type: application/xml');

        // Create the XML header
        $xml = new DOMDocument('1.0', 'UTF-8');

        // Create the RSS root node
        $rss = $xml->createElement("rss");
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        // Add the channel node to the RSS node
        $channel = $xml->createElement('channel');
        $rss->appendChild($channel);

        // Add the feed title to the channel info
        $title = $xml->createElement('title', 'Google feed');
        $channel->appendChild($title);

        // Add the feed title to the channel info
        $title = $xml->createElement('link', $globalData->getShopUrl());
        $channel->appendChild($title);

        // Add the feed description to the channel info
        $description = $xml->createElement('description', $parserData['description']);
        $channel->appendChild($description);

        // Add the products
        $this->parseProductData($xml, $channel, $products);

        // Show the XML content
        $xml->appendChild($rss);
        echo $xml->saveXML();
    }
}

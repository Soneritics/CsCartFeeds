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
 * Class GoogleSoneriticsFeedParser
 */
class GoogleSoneriticsFeedParser implements ISoneriticsFeedParser
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
        return 'Google';
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

    /**
     * Add the product data to the feed
     * @param DOMDocument $xml
     * @param DOMElement $channel
     * @param array $products
     */
    private function parseProductData(DOMDocument $xml, DOMElement $channel, array $products)
    {
        if (!empty($products)) {
            foreach ($products as $product) {
                // Create the new item node
                $item = $xml->createElement('item');

                // Product data
                $productData = [
                    'g:id' => $product['product_code'],
                    'g:title' => $product['product'],
                    'g:description' => trim(strip_tags($product['short_description'])),
                    'g:link' => $product['url'],
                    'g:image_link' => $product['main_pair']['detailed']['image_path'],
                    'g:price' => round($product['price'], 2) . ' ' . $this->globalData->getCurrency()->getCode(),
                    'g:condition' => $this->getFeature($product, 'condition', 'new'),
                    'g:availability' => $product['amount'] > 0 ? 'in stock' : 'out of stock',
                    'g:brand' => $this->getBrand($product),
                    'g:gtin' => $this->getFeature($product, 'gtin'),
                    'g:mpn' => $this->getFeature($product, 'mpn'),
                    'g:google_product_category' => $this->getFeature($product, 'google product category'),
                    'g:product_type' => $this->getFeature($product, 'google product type'),
                ];

                foreach ($productData as $k => $v) {
                    if (!empty($v)) {
                        $item->appendChild($xml->createElement($k, $v));
                    }
                }

                // Add the item to the feed
                $channel->appendChild($item);
            }
        }
    }

    /**
     * Get the brand of a product
     * @param array $product
     * @return string
     */
    private function getBrand(array $product): string
    {
        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $feature) {
                if (!empty($feature['feature_type']) && $feature['feature_type'] === 'E') {
                    $activeVariantId = $feature['variant_id'];
                    return $feature['variants'][$activeVariantId]['variant'];
                }
            }
        }

        return '';
    }

    /**
     * Get a specific feature value
     * @param array $product
     * @param string $feature
     * @param string $default
     * @return string
     */
    private function getFeature(array $product, string $feature, string $default = ''): string
    {
        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $productFeature) {
                if (
                    !empty($productFeature['description']) &&
                    strtolower($productFeature['description']) === strtolower($feature)
                ) {
                    return $productFeature['value'];
                }
            }
        }

        return $default;
    }
}

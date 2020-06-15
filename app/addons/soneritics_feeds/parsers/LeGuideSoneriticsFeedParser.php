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
 * Class LeGuideSoneriticsFeedParser
 */
class LeGuideSoneriticsFeedParser implements ISoneriticsFeedParser
{
    /**
     * @var SoneriticsFeedGlobalData
     */
    private $globalData;

    /**
     * @var array
     */
    private $parserData = [];

    /**
     * Get the name of the parser
     * @return string
     */
    public static function getName(): string
    {
        return 'LeGuide';
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
        // Set properties
        $this->globalData = $globalData;
        $this->parserData = $parserData;

        // Send the XML content type header
        header('Content-type: application/xml');

        // Create the XML header
        $xml = new DOMDocument('1.0', 'UTF-8');

        // Create the RSS root node
        $productsRoot = $xml->createElement("products");

        // Add the products
        $this->parseProductData($xml, $productsRoot, $products);

        // Show the XML content
        $xml->appendChild($productsRoot);
        echo $xml->saveXML();
    }

    /**
     * Get the invalid products in a feed. These are the products that have missing info.
     * @param array $products
     * @param SoneriticsFeedGlobalData $globalData
     * @param array $parserData
     * @return array
     */
    public function getInvalidProductIds(
        array $products,
        SoneriticsFeedGlobalData $globalData,
        array $parserData = []
    ): array {
        $result = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                $check = [
                    $this->getFeature($product, 'ean'),
                    $this->getFeature($product, 'leguide category'),
                    $this->getFeature($product, 'leguide levertijd'),
                    $this->getFeature($product, 'leguide garantie')
                ];

                foreach ($check as $checkItem) {
                    if (empty($checkItem)) {
                        $result[$product['product_id']] = $product['product_id'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Add the product data to the feed
     * @param DOMDocument $xml
     * @param DOMElement $productsRoot
     * @param array $products
     */
    private function parseProductData(DOMDocument $xml, DOMElement $productsRoot, array $products)
    {
        if (!empty($products)) {
            foreach ($products as $product) {
                // Create the new item node
                $item = $xml->createElement('product');

                // Product data
                $productData = [
                    'unieke_ID' => $product['product_code'],
                    'titel' => $product['product'],
                    'omschrijving' => trim(strip_tags($product['short_description'])),
                    'URL_product' => $product['url'],
                    'URL_beeld' => $product['main_pair']['detailed']['image_path'],
                    'prijs' => number_format(round($product['price'], 2), 2, ',', '.'),
                    'merk' => $this->getBrand($product),
                    'beschikbaarheid' => '0',
                    'verzendkosten' => number_format($this->getFeature($product, 'shipment cost nl', '0'), 2, ',', '.'),
                    'EAN' => $this->getFeature($product, 'ean'),
                    'categorie' => $this->getFeature($product, 'leguide category'),
                    'levertijd' => $this->getFeature($product, 'leguide levertijd'),
                    'garantie' => $this->getFeature($product, 'leguide garantie')
                ];

                foreach ($productData as $k => $v) {
                    if (!empty($v)) {
                        $item->appendChild($xml->createElement($k, $v));
                    }
                }

                // Add the item to the feed
                $productsRoot->appendChild($item);
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

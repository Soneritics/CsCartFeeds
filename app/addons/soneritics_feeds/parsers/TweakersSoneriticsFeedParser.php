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
 * Class TweakersSoneriticsFeedParser
 */
class TweakersSoneriticsFeedParser implements ISoneriticsFeedParser
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
        return 'Tweakers';
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
        $productsRoot = $xml->createElement("Products");

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
                    $this->getFeature($product, 'tweakers category'),
                    $this->getFeature($product, 'tweakers subcategory'),
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
                $item = $xml->createElement('Product');

                // Product data
                $productData = [
                    'Shop_Code' => $product['product_code'],
                    'Product_Name' => $product['product'],
                    'Product_Description' => trim(strip_tags($product['short_description'])),
                    'Deeplink' => $product['url'],
                    'Picture_Link' => $product['main_pair']['detailed']['image_path'],
                    'Product_Price' => round($product['price'], 2),
                    'Product_Brand' => $this->getBrand($product),
                    'Delivery_Costs' => $this->getFeature($product, 'shipment cost nl', '0'),
                    'Product_Ean' => $this->getFeature($product, 'ean'),
                    'Product_in_stock' => $product['amount'],
                    'Category' => $this->getFeature($product, 'tweakers category'),
                    'Sub_category' => $this->getFeature($product, 'tweakers subcategory'),
                    'Delivery_Period' => '1' //@todo make this a variable?
                ];

                foreach ($productData as $k => $v) {
                    if (!empty($v)) {
                        $item->appendChild($xml->createElement($k, soneritics_feeds_xmlEscape($v)));
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

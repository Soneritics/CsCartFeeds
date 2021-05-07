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
abstract class GoogleSoneriticsFeedParserBase implements ISoneriticsFeedParser
{
    /**
     * @var SoneriticsFeedGlobalData
     */
    private $globalData;

    /**
     * The identifier of the ID field.
     * @return string
     */
    protected abstract function getIdField(): string;

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
        $title = $xml->createElement('title', static::getName() . ' feed');
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
                    $this->getFeature($product, 'gtin'),
                    $this->getFeature($product, 'mpn'),
                    $this->getFeature($product, 'google product category'),
                    $this->getFeature($product, 'google product type')
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
                    'g:id' => $product[$this->getIdField()],
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

                if (!empty($productData['g:gtin']) &&
                    !empty($productData['g:mpn']) &&
                    $productData['g:gtin'] === $productData['g:mpn']) {
                    unset($productData['g:mpn']);
                }

                foreach ($productData as $k => $v) {
                    if (!empty($v)) {
                        $item->appendChild($xml->createElement($k, soneritics_feeds_xmlEscape($v)));
                    }
                }

                // Set the shipments from the features
                $this->setShipments($product, $xml, $item);

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

    /**
     * Set the shipments based on the values of the features
     * @param array $product
     * @param DOMDocument $xml
     * @param DOMElement $item
     */
    private function setShipments(array $product, DOMDocument $xml, DOMElement $item)
    {
        $featureName = 'shipment cost ';
        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $productFeature) {
                $hasValue = !empty($productFeature['value']);
                $descriptionMatches = !empty($productFeature['description']) &&
                    substr(strtolower($productFeature['description']), 0, strlen($featureName)) === $featureName;

                if ($descriptionMatches) {
                    $shipmentCountry = strtoupper(substr($productFeature['description'], strlen($featureName)));
                    $shipmentCost = round($productFeature['value'], 2) . ' ' . $this->globalData->getCurrency()->getCode();

                    $shippingItem = $xml->createElement('g:shipping');
                    $shippingItem->appendChild($xml->createElement('g:country', $shipmentCountry));
                    $shippingItem->appendChild($xml->createElement('g:service', 'Standard'));
                    $shippingItem->appendChild($xml->createElement('g:price', $shipmentCost));
                    $item->appendChild($shippingItem);
                }
            }
        }
    }
}

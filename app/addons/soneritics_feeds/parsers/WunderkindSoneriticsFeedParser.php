<?php
class WunderkindSoneriticsFeedParser implements ISoneriticsFeedParser
{
    /**
     * @var SoneriticsFeedGlobalData
     */
    private $globalData;

    public static function getName(): string
    {
        return 'Wunderkind';
    }

    public function getInvalidProductIds(
        array $products,
        SoneriticsFeedGlobalData $globalData,
        array $parserData = []
    ): array
    {
        $result = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                $check = [
                    $this->getFeature($product, 'google product category'),
                    $this->getFeature($product, 'google product type'),
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
                    'sku' => $product['product_code'],
                    'item_group_id' => $this->getFeature($product, 'google product type'),
                    'title' => $product['product'],
                    'product_page_url' => $product['url'],
                    'image_url' => $product['main_pair']['detailed']['image_path'],
                    'available_quantity' => $product['amount'],
                    'product_description' => trim(strip_tags($product['short_description'])),
                    'category_navigation' => $this->getFeature($product, 'google product category'),
                    'sale_price' => round($product['price'], 2) . ' ' . $this->globalData->getCurrency()->getCode(),
                    'price' => round($product['list_price'], 2) . ' ' . $this->globalData->getCurrency()->getCode(),
                    'brand' => $this->getBrand($product)
                ];

                foreach ($productData as $k => $v) {
                    if (!empty($v)) {
                        $item->appendChild($xml->createElement($k, soneritics_feeds_xmlEscape($v)));
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
     * @param ?string $default
     * @return ?string
     */
    private function getFeature(array $product, string $feature, ?string $default = ''): ?string
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
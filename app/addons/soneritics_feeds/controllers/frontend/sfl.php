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
 * SFL - Soneritics Feed Link
 */

// Get feed data from the request parameters
$id = (int)$_GET['id'] ?? 0;
$parserData = db_get_array("SELECT * FROM ?:soneritics_feeds WHERE id = ?i", $id);

// Only proceed if the feed exists
if ($mode === 'show' && !empty($parserData)) {
    $data = $parserData[0];

    // Find the product ids for this feed
    $productIds = db_get_fields('SELECT product_id FROM ?:soneritics_feed_products WHERE feed_id = ?i', $id);

    // Fetch the product data using Cs Cart's built in functions
    $params = [
        'force_get_by_ids' => true,
        'pid' => $productIds,
        'get_frontend_urls' => true,
        'extend' => ['description']
    ];
    $productData = fn_get_products($params, 0, $data['lang_code']);

    // Get the products, if any were found
    $products = !empty($productData[0]) ? $productData[0] : [];

    // Gather additional product info
    fn_gather_additional_products_data(
        $products, [
            'get_icon' => true,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options' => true,
            'get_discounts' => true,
            'get_features' => true,
            'features_display_on' => 'A'
        ]
    );

    // Get parser specific data
    $parserData = empty($data['data']) ? [] : json_decode($data['data'], true);

    // Get shop URL
    $companyData = fn_get_company_data($data['company_id'], $data['lang_code']);
    $shopUrl = empty($companyData['secure_storefront']) ?
        'http://' . $companyData['storefront'] :
        'https://' . $companyData['secure_storefront'];

    // Get global parser data
    $globalData = (new SoneriticsFeedGlobalData)
        ->setShopUrl($shopUrl)
        ->setCurrency(new SoneriticsFeedCurrency(fn_get_currencies()));

    // Parse and end the script
    SoneriticsFeedParserFactory::getParserFromFilename($data['parser'])->parse($products, $globalData, $parserData);
    fn_flush();
    die;
}

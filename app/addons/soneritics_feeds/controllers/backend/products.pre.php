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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// Get the product ID
$productId = (int)($_REQUEST['product_id'] ?? 0);

// Controller for updating products
if ($mode === 'update') {
    // Set possible feeds
    $companyId = \Tygh\Registry::get('runtime.company_id');
    $availableFeeds = db_get_array("SELECT id, `name` FROM ?:soneritics_feeds WHERE company_id = ?i", $companyId);
    Tygh::$app['view']->assign('availableFeeds', $availableFeeds);

    // Update, but only when there are active feeds
    // This can only be used when a specific company has been chosen
    // and prevents deleting from a feed when 'all companies' is active
    if (!empty($_POST) && !empty($availableFeeds)) {
        db_query("DELETE FROM ?:soneritics_feed_products WHERE product_id = ?i", $productId);

        if (!empty($_POST['soneritics_feed_ids'])) {
            foreach ($_POST['soneritics_feed_ids'] as $feedId) {
                db_query("INSERT INTO ?:soneritics_feed_products(feed_id, product_id) VALUES(?i, ?i)", $feedId, $productId);
            }
        }
    }

    // Set active feeds for the product
    $activeFeeds = db_get_fields("SELECT feed_id FROM ?:soneritics_feed_products WHERE product_id = ?i", $productId);
    Tygh::$app['view']->assign('activeFeeds', $activeFeeds);
}

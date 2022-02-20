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

$repository = new SoneriticsFeedRepository();

/*******************************************************************/
//                          FEED OVERVIEW                          //
/*******************************************************************/
if ($mode === 'manage') {
    $feeds = db_get_array("SELECT * FROM ?:soneritics_feeds ORDER BY `name` ASC");
    Tygh::$app['view']->assign('feeds', $feeds);

/*******************************************************************/
//                           DELETE FEED                           //
/*******************************************************************/
} elseif ($mode === 'delete') {
    $feedId = empty($_GET['soneritics_feed_id']) ? 0 : (int)$_GET['soneritics_feed_id'];
    if (!empty($feedId)) {
        db_query("DELETE FROM ?:soneritics_feeds WHERE id = ?i", $feedId);
        fn_set_notification('N', 'feed deleted', 'The feed has been deleted');
        return array(CONTROLLER_STATUS_OK, 'soneritics_feeds.manage');
    }

/*******************************************************************/
//                      UPDATE FEED SETTINGS                       //
/*******************************************************************/
} elseif ($mode === 'update') {
    // Check if there's a feed to update or that this is a new feed
    $feedId = empty($_GET['soneritics_feed_id']) ? 0 : (int)$_GET['soneritics_feed_id'];

    // Process POST data
    if (!empty($_POST)) {
        if (empty($feedId)) {
            db_query(
                "INSERT INTO ?:soneritics_feeds(company_id, lang_code, `name`, parser, `data`) VALUES(?i, ?s, ?s, ?s, ?s)",
                $_POST['soneriticsFeed']['company_id'],
                $_POST['soneriticsFeed']['lang_code'],
                $_POST['soneriticsFeed']['name'],
                $_POST['soneriticsFeed']['parser'],
                json_encode($_POST['soneriticsFeed']['data'] ?? [])
            );

            $lastInsertId = (int)db_get_field('SELECT LAST_INSERT_ID()');
            return array(CONTROLLER_STATUS_OK, 'soneritics_feeds.update?soneritics_feed_id=' . $lastInsertId);
        } else {
            db_query(
                "UPDATE ?:soneritics_feeds SET company_id = ?i, lang_code = ?s, `name` = ?s, parser = ?s, `data` = ?s WHERE id = ?i",
                $_POST['soneriticsFeed']['company_id'],
                $_POST['soneriticsFeed']['lang_code'],
                $_POST['soneriticsFeed']['name'],
                $_POST['soneriticsFeed']['parser'],
                json_encode($_POST['soneriticsFeed']['data'] ?? []),
                $feedId
            );
        }
    }

    // Additional data to an existing item
    $soneriticsFeed = db_get_array("SELECT * FROM ?:soneritics_feeds WHERE id = ?i", $feedId);
    if (!empty($soneriticsFeed)) {
        $soneriticsFeed[0]['data'] =
            empty($soneriticsFeed[0]['data']) ? [] : json_decode($soneriticsFeed[0]['data'], true);

        Tygh::$app['view']->assign('soneriticsFeed', $soneriticsFeed[0]);
    }

/*******************************************************************/
//                      PRODUCTS IN THE FEED                       //
/*******************************************************************/
} elseif ($mode === 'products') {
    $feedId = empty($_GET['soneritics_feed_id']) ? 0 : (int)$_GET['soneritics_feed_id'];

    // If the feed ID is not set, no data can be shown and the user will be redirected to the feed overview
    if ($feedId === 0 || (int)db_get_field("SELECT COUNT(id) FROM ?:soneritics_feeds WHERE id = ?i", $feedId) === 0) {
        return [CONTROLLER_STATUS_REDIRECT, 'soneritics_feeds.manage'];
    }

    // Save the data, if POSTed
    if (!empty($_POST)) {
        $allProductsShownOnPage = $_POST['all_products'];
        $activeProducts = $_POST['active_products'];

        // Remove all the products that were shown on the page
        db_query("DELETE FROM ?:soneritics_feed_products WHERE feed_id = ?i AND product_id IN(?a)", $feedId, $allProductsShownOnPage);

        // Add the selected products to the database
        if (!empty($activeProducts)) {
            foreach ($activeProducts as $activeProduct) {
                db_query("INSERT INTO ?:soneritics_feed_products(feed_id, product_id) VALUES(?i, ?i)", $feedId, (int)$activeProduct);
            }
        }
    }

    // Get the data for the view
    $page = empty($_GET['page']) ? 0 : (int)$_GET['page'];
    list($products, $search) = fn_get_products(['page' => $page], \Tygh\Registry::get('settings.Appearance.admin_elements_per_page'));
    fn_gather_additional_products_data($products, ['get_icon' => true, 'get_detailed' => true]);

    // Get active products for this feed
    $activeProducts = db_get_fields('SELECT product_id FROM ?:soneritics_feed_products WHERE feed_id = ?i', $feedId);

    Tygh::$app['view']->assign('products', $products);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('activeProducts', $activeProducts);

/*******************************************************************/
//               COMPLETE PRODUCT OVERVIEW PER FEED                //
/*******************************************************************/
} elseif ($mode === 'products_overview_per_feed') {
    $companyId = fn_get_runtime_company_id();
    if ($companyId > 0) {
        // Save the data, if POSTed
        if (!empty($_POST)) {
            $allProductsShownOnPage = $_POST['all_products'];
            $activeProducts = $_POST['active_products'];

            // Remove all the products that were shown on the page
            db_query(
                "DELETE FROM ?:soneritics_feed_products
                WHERE 1=1
                    AND product_id IN(?a)
                    AND feed_id IN(
                        SELECT id
                        FROM ?:soneritics_feeds
                        WHERE company_id = ?i
                    )",
                $allProductsShownOnPage,
                $companyId
            );

            // Add the selected products to the database
            if (!empty($activeProducts)) {
                foreach ($activeProducts as $feedId => $activeProductsInFeed) {
                    foreach ($activeProductsInFeed as $activeProduct) {
                        db_query("INSERT INTO ?:soneritics_feed_products(feed_id, product_id) VALUES(?i, ?i)", $feedId, (int)$activeProduct);
                    }
                }
            }
        }

        // Get the data for the view
        $page = empty($_GET['page']) ? 0 : (int)$_GET['page'];
        $productCount = !empty($_GET['download']) ? 1000000 : \Tygh\Registry::get('settings.Appearance.admin_elements_per_page');
        list($products, $search) = fn_get_products(['page' => $page], $productCount);
        fn_gather_additional_products_data($products, ['get_icon' => true, 'get_detailed' => true]);

        // Get the feeds
        $availableFeedsComplete = db_get_array("SELECT id, `name` FROM ?:soneritics_feeds WHERE company_id = ?i", $companyId);
        $availableFeeds = [];
        foreach ($availableFeedsComplete as $_feed) {
            $availableFeeds[$_feed['id']] = $_feed['name'];
        }

        // Get active products for this feed
        $feedQuery = "
            SELECT feed_id, product_id
            FROM ?:soneritics_feed_products
            WHERE feed_id IN(
                SELECT id
                FROM ?:soneritics_feeds
                WHERE company_id = ?i
            )";
        $activeProductsComplete = db_get_array($feedQuery, $companyId);
        $activeProducts = [];
        foreach ($activeProductsComplete as $_activeProduct) {
            $feedId = $_activeProduct['feed_id'];
            $productId = $_activeProduct['product_id'];

            if (!isset($activeProducts[$feedId])) {
                $activeProducts[$feedId] = [];
            }

            $activeProducts[$feedId][] = $productId;
        }

        // Download as CSV
        if (!empty($_GET['download'])) {
            fn_clear_ob();
            header('Content-Type: text/csv');
            header('Content-Description: File Transfer');
            header("Content-Disposition: attachment; filename=feed-overview.csv");
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

            // Header row
            echo __("code") . ";" . __("name");
            foreach ($availableFeeds as $availableFeed) {
                echo ";" . $availableFeed;
            }
            echo "\n";

            // Product rows
            foreach ($products as $product) {
                echo '"' . str_replace('"', '', $product['product_code']) . '";';
                echo '"' . str_replace('"', '', $product['product']) . '"';

                foreach ($availableFeeds as $feedId => $availableFeed) {
                    echo ";";
                    if (in_array($product['product_id'], $activeProducts[$feedId])) {
                        echo "X";
                    }
                }
                echo "\n";
            }

            die;
        }

        Tygh::$app['view']->assign('products', $products);
        Tygh::$app['view']->assign('search', $search);
        Tygh::$app['view']->assign('availableFeeds', $availableFeeds);
        Tygh::$app['view']->assign('activeProducts', $activeProducts);
    }


/*******************************************************************/
//                      INCOMPLETE PRODUCTS                        //
/*******************************************************************/
} elseif ($mode === 'incomplete_products') {
    $feedId = empty($_GET['soneritics_feed_id']) ? 0 : (int)$_GET['soneritics_feed_id'];
    $parserData = db_get_array("SELECT * FROM ?:soneritics_feeds WHERE id = ?i", $feedId);

    $productIds = [];
    if (!empty($parserData)) {
        $data = $parserData[0];

        // Find the product ids for this feed
        $productIds = db_get_fields('SELECT product_id FROM ?:soneritics_feed_products WHERE feed_id = ?i', $feedId);

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
        $productIds = SoneriticsFeedParserFactory::getParserFromFilename($data['parser'])
            ->getInvalidProductIds($products, $globalData, $parserData);
    }

    // Keep the array from begin empty. CsCart doesn't like that :-)
    $productIds[] = 0;

    // Get the data for the view
    $page = empty($_GET['page']) ? 0 : (int)$_GET['page'];
    list($products, $search) = fn_get_products(
        ['page' => $page, 'pid' => $productIds],
        \Tygh\Registry::get('settings.Appearance.admin_elements_per_page')
    );
    fn_gather_additional_products_data($products, ['get_icon' => true, 'get_detailed' => true]);

    // Get active products for this feed
    Tygh::$app['view']->assign('products', $products);
    Tygh::$app['view']->assign('search', $search);
}

// Global view vars
Tygh::$app['view']->assign('companies', $repository->getCompanyList());
Tygh::$app['view']->assign('langs', $repository->getLanguageList());
Tygh::$app['view']->assign('parsers', SoneriticsFeedParserFactory::getParserList());

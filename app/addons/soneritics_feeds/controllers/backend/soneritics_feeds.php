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
//                      UPDATE FEED SETTINGS                       //
/*******************************************************************/
} elseif ($mode === 'products') {
    $feedId = empty($_GET['soneritics_feed_id']) ? 0 : (int)$_GET['soneritics_feed_id'];

    // If the feed ID is not set, no data can be shown and the user will be redirected to the feed overview
    if ($feedId === 0 || (int)db_get_field("SELECT COUNT(id) FROM ?:soneritics_feeds WHERE id = ?i", $feedId) === 0) {
        return [CONTROLLER_STATUS_REDIRECT, 'soneritics_feeds.manage'];
    }

    // @todo: Save

    $page = empty($_GET['page']) ? 0 : (int)$_GET['page'];
    list($products, $search) = fn_get_products(['page' => $page], \Tygh\Registry::get('settings.Appearance.admin_elements_per_page'));
    fn_gather_additional_products_data($products, ['get_icon' => true, 'get_detailed' => true]);

    Tygh::$app['view']->assign('products', $products);
    Tygh::$app['view']->assign('search', $search);
}

// Global view vars
Tygh::$app['view']->assign('companies', $repository->getCompanyList());
Tygh::$app['view']->assign('langs', $repository->getLanguageList());
Tygh::$app['view']->assign('parsers', SoneriticsFeedParserFactory::getParserList());

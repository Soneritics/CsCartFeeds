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
                "INSERT INTO ?:soneritics_feeds(company_id, lang_code, `name`, parser) VALUES(?i, ?s, ?s, ?s)",
                $_POST['soneriticsFeed']['company_id'],
                $_POST['soneriticsFeed']['lang_code'],
                $_POST['soneriticsFeed']['name'],
                $_POST['soneriticsFeed']['parser']
            );

            $lastInsertId = (int)db_get_field('SELECT LAST_INSERT_ID()');
            return array(CONTROLLER_STATUS_OK, 'soneritics_feeds.update?soneritics_feed_id=' . $lastInsertId);
        } else {
            db_query(
                "UPDATE ?:soneritics_feeds SET company_id = ?i, lang_code = ?s, `name` = ?s, parser = ?s WHERE id = ?i",
                $_POST['soneriticsFeed']['company_id'],
                $_POST['soneriticsFeed']['lang_code'],
                $_POST['soneriticsFeed']['name'],
                $_POST['soneriticsFeed']['parser'],
                $feedId
            );
        }
    }

    // Additional data to an existing item
    $soneriticsFeed = db_get_array("SELECT * FROM ?:soneritics_feeds WHERE id = ?i", $feedId);
    if (!empty($soneriticsFeed)) {
        Tygh::$app['view']->assign('soneriticsFeed', $soneriticsFeed[0]);
    }
}

// Global view vars
Tygh::$app['view']->assign('companies', $repository->getCompanyList());
Tygh::$app['view']->assign('langs', $repository->getLanguageList());
Tygh::$app['view']->assign('parsers', SoneriticsFeedParserFactory::getParserList());

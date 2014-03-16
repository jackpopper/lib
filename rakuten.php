<?php
require_once 'curl.php';
// 楽天API
define('RAKUTEN_DEV_ID',       '');
define('RAKUTEN_AFFILIATE_ID', '');
define('RAKUTEN_API',          'http://api.rakuten.co.jp/rws/3.0/rest?');

// 商品検索
function searchRakuten($keyword, $page = 1) {
    $params = array();
    $params['operation'] = 'ItemSearch';
    $params['keyword'] = $keyword;
//    $params['sort'] = '-affiliateRate';
    $params['availability'] = 1;
    $params['version'] = '2010-09-15';
    $params['page'] = $page;

    $xml = getRakutenXml($params);
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $ret = array();
    if ($xml !== false && $doc->getElementsByTagName('Status')->item(0)->nodeValue == 'Success') {
        $count = $doc->getElementsByTagName('count')->item(0)->nodeValue;
        $items = $doc->getElementsByTagName('Item');
        foreach ($items as $item) {
            $ret[] = array('itemName' => $item->getElementsByTagName('itemName')->item(0)->nodeValue,
                           'catchcopy' => $item->getElementsByTagName('catchcopy')->item(0)->nodeValue,
                           'itemCode' => $item->getElementsByTagName('itemCode')->item(0)->nodeValue,
                           'itemPrice' => $item->getElementsByTagName('itemPrice')->item(0)->nodeValue,
                           'itemCaption' => $item->getElementsByTagName('itemCaption')->item(0)->nodeValue,
                           'affiliateUrl' => $item->getElementsByTagName('affiliateUrl')->item(0)->nodeValue,
                           'mediumImageUrl' => $item->getElementsByTagName('mediumImageUrl')->item(0)->nodeValue,
                           'genreId' => $item->getElementsByTagName('genreId')->item(0)->nodeValue,
                           'affiliateRate' => $item->getElementsByTagName('affiliateRate')->item(0)->nodeValue,
                          );
        }
    } else {
        return false;
    }
//print_r($ret);
    return $ret;
}

// Books総合検索
function searchRakutenBooks($keyword = NULL, $genre = NULL, $sort = NULL, $page = 1) {
    $params = array();
    $params['operation'] = 'BooksTotalSearch';
    if ($keyword) {
        $params['keyword'] = $keyword;
    }
    if ($genre) {
        $params['booksGenreId'] = $genre;
    }
    if ($sort) {
        $params['sort'] = $sort;
    }
    $params['version'] = '2011-12-01';
    $params['page'] = $page;

    $xml = getRakutenXml($params);
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $ret = array();
    if ($xml !== false && $doc->getElementsByTagName('Status')->item(0)->nodeValue == 'Success') {
        $count = $doc->getElementsByTagName('count')->item(0)->nodeValue;
        $items = $doc->getElementsByTagName('Item');
        foreach ($items as $item) {
            $ret[] = array('title' => $item->getElementsByTagName('title')->item(0)->nodeValue,
                           'itemPrice' => $item->getElementsByTagName('itemPrice')->item(0)->nodeValue,
                           'affiliateUrl' => $item->getElementsByTagName('affiliateUrl')->item(0)->nodeValue,
                           'mediumImageUrl' => $item->getElementsByTagName('mediumImageUrl')->item(0)->nodeValue,
                          );
        }
    } else {
        return false;
    }
//print_r($ret);
    return $ret;
}

// Booksジャンル検索
function searchGenreRakutenBooks($genre) {
    $params = array();
    $params['operation'] = 'BooksGenreSearch';
    $params['booksGenreId'] = $genre;
    $params['version'] = '2009-03-26';
    $xml = getRakutenXml($params);
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $ret = array();
    if ($xml !== false && $doc->getElementsByTagName('Status')->item(0)->nodeValue == 'Success') {
        $items = $doc->getElementsByTagName('child');
        foreach ($items as $item) {
            $ret[] = array('booksGenreId' => $item->getElementsByTagName('booksGenreId')->item(0)->nodeValue,
                           'booksGenreName' => $item->getElementsByTagName('booksGenreName')->item(0)->nodeValue,
                          );
        }
    } else {
        return false;
    }
//print_r($ret);
    return $ret;
}

// 共通APIコール
function getRakutenXml($params) {
    $params['developerId'] = RAKUTEN_DEV_ID;
    $params['affiliateId'] = RAKUTEN_AFFILIATE_ID;
    $url = RAKUTEN_API.http_build_query($params);
//echo $url."\n";
    $response = getWebAPI($url);
    if ($response === false) {
        return false;
    }

    return $response;
}

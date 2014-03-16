<?php
require_once 'curl.php';
// amazon
define('AMAZON_ACCESS_KEY_ID',     '');
define('AMAZON_SECRET_ACCESS_KEY', '');
define('AMAZON_ASSOCIATE_TAG',     '-22');
define('AMAZON_API',               'http://ecs.amazonaws.jp/onca/xml');
define('AMAZON_NO_IMAGE_URL', 'http://g-ec2.images-amazon.com/images/G/09/nav2/dp/no-image-no-ciu._V192259616_AA100_.gif');

// 商品情報
function getAmazonItem($id) {
    $params = array();
    $params['Service']        = 'AWSECommerceService';
    $params['Operation']      = 'ItemLookup';
    $params['ItemId']         = $id;
    $params['AssociateTag']   = AMAZON_ASSOCIATE_TAG;
    $params['ResponseGroup']  = 'ItemAttributes,Offers,Images';
 
    $xml = getAmazonXml($params);
    $ret = array();
    if ($xml !== false && !$xml->faultstring && !$xml->Items->Request->Errors) {
        foreach($xml->Items->Item as $item){
            $ret['ASIN']          = (string)$item->ASIN;
            $ret['DetailPageURL'] = (string)$item->DetailPageURL;
            $ret['ImageURL']      = (string)$item->MediumImage->URL;
            $ret['Title']         = (string)$item->ItemAttributes->Title;
//            $ret['Price']         = (string)$item->OfferSummary->LowestNewPrice->FormattedPrice;
        }
    } else {
        return false;
    }

//print_r($ret);
    return $ret;
}

// 商品検索
function searchAmazon($keyword, $page = 1, $adult_ng = true) {
    $params = array();
    $params['Service']        = 'AWSECommerceService';
    $params['Operation']      = 'ItemSearch';
    $params['SearchIndex']    = 'All';
    $params['Keywords']       = $keyword; // search_key(UTF-8)
    $params['AssociateTag']   = AMAZON_ASSOCIATE_TAG;
    $params['ResponseGroup']  = 'ItemAttributes,Offers,Images';
    $params['TagPage']        = $page;
    $params['TagsPerPage']    = 20;
 
    $xml = getAmazonXml($params);
    $ret = array();
    if ($xml !== false && !$xml->faultstring && !$xml->Items->Request->Errors) {
        $total_results = $xml->Items->TotalResults;
        //$total_pages   = $xml->Items->TotalPages;
        $cnt = 0;
        foreach($xml->Items->Item as $item){
            if ($cnt > 0) break;
            if ($adult_ng && (string)$item->ItemAttributes->Format === 'アダルト') continue;
            $ret['ASIN']          = (string)$item->ASIN;
            $ret['DetailPageURL'] = (string)$item->DetailPageURL;
            $ret['ImageURL']      = (string)$item->MediumImage->URL;
            $ret['Title']         = (string)$item->ItemAttributes->Title;
//            $ret['Price']         = (string)$item->OfferSummary->LowestNewPrice->FormattedPrice;
            $cnt++;
        }
    } else {
        return false;
    }

//print_r($ret);
    return $ret;
}

// ランキング取得
function getAmazonRanking($id) {
    $params = array();
    $params['Service']        = 'AWSECommerceService';
    $params['Operation']      = 'BrowseNodeLookup';
    $params['BrowseNodeId']   = $id;
    $params['ResponseGroup']  = 'TopSellers';

    $xml = getAmazonXml($params);
    $ret = array();
    if ($xml !== false && !$xml->faultstring && !$xml->BrowseNodes->Request->Errors) {
        $cnt = 0;
        foreach($xml->BrowseNodes->BrowseNode->TopSellers->TopSeller as $item){
            $ret[$cnt]['ASIN']  = (string)$item->ASIN;
            $ret[$cnt]['Title'] = (string)$item->Title;
            $cnt++;
        }
    } else {
        return false;
    }

//print_r($ret);
    return $ret;
}

// XML取得
function getAmazonXml($params) {
    $params['SignatureMethod']  = 'HmacSHA256';   // signature format name.
    //$params['SignatureVersion'] = 2;              // signature version.
    $params['Timestamp']        = gmdate('Y-m-d\TH:i:s\Z'); 
    $params['AssociateTag']     = AMAZON_ASSOCIATE_TAG;
    $base_param                 = 'AWSAccessKeyId='.AMAZON_ACCESS_KEY_ID;
    ksort($params);

    // create canonical string.
    $canonical_string = $base_param;
    foreach ($params as $k => $v) {
        $canonical_string .= '&'.urlencode_RFC3986($k).'='.urlencode_RFC3986($v);
    }
 
    // create signature strings.( HMAC-SHA256 & BASE64 )
    $parsed_url     = parse_url(AMAZON_API);
    $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
    $signature      = base64_encode(hash_hmac('sha256', $string_to_sign, AMAZON_SECRET_ACCESS_KEY, true));
 
    // create URL strings.
    $url = AMAZON_API.'?'.$canonical_string.'&Signature='.urlencode_RFC3986($signature);
//echo $url."\n";
 
    // get XML
//    $response = `/usr/local/bin/curl -s '$url'`;
    $response = getWebAPI($url);
    if ($response === false) {
        return false;
    }
    $xml = simplexml_load_string($response);

    return $xml;
}

// Amazon用urlencode
function urlencode_RFC3986($str) {
    return str_replace('%7E', '~', rawurlencode($str));
}

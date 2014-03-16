<?php
require_once 'amazon.php';
// ranking
define('AMAZON_DISCOUNT_RANK_ASSOCIATE_TAG', '-22');
define('AMAZON_RANK_MAX_COUNT', 10);
//define('AMAZON_NO_IMAGE_URL', 'http://g-ec2.images-amazon.com/images/G/09/nav2/dp/no-image-no-ciu._V192259616_AA100_.gif');

// 割引率ランキング取得
function getAmazonDiscountRanking($index, $node, $adult = false) {
    $ret = array();
    for ($i = 1; $i <= AMAZON_RANK_MAX_COUNT; $i++) {
        $tmp = searchAmazonDiscount($index, $node, $i, $adult);
        $ret = array_merge($ret, $tmp);
    }
    foreach ($ret as $key => $row) {
        $discount[$key] = $row['Discount'];
    }
    array_multisort($discount, SORT_DESC, $ret);
    $asin_ary = array();
    $disrank  = array();
    foreach ($ret as $r) {
        if (!in_array($r['ASIN'], $asin_ary)) {
            $disrank[]  = $r;
            $asin_ary[] = $r['ASIN'];
        }
    }

    return $disrank;
}

// 割引率検索
function searchAmazonDiscount($search_index, $node, $page = 1, $adult = false) {
    $params = array();
    $params['Service']        = 'AWSECommerceService';
    $params['Operation']      = 'ItemSearch';
    $params['SearchIndex']    = $search_index;
    $params['BrowseNode']     = $node;
    $params['Sort']           = ($search_index != 'Beauty') ? 'salesrank' : 'reviewrank';
    $params['AssociateTag']   = AMAZON_DISCOUNT_RANK_ASSOCIATE_TAG;
    $params['ResponseGroup']  = 'ItemAttributes,Offers,Images';
    $params['ItemPage']       = $page;
 
    $xml = getAmazonXml($params);
    $ret = array();
    if ($xml !== false && !$xml->faultstring && !$xml->Items->Request->Errors) {
        $total_results = $xml->Items->TotalResults;
        $total_pages   = $xml->Items->TotalPages;
if ($page == 1) echo "results:$total_results pages:$total_pages\n";
        $cnt = ($page - 1) * 10 + 1;
        foreach($xml->Items->Item as $item){
//print_r($item);
            $adult_flg = 0;
            $format = $item->ItemAttributes->Format;
            foreach ($format as $f) {
                if ((string)$f === 'アダルト') {
                    $adult_flg = 1;
                    if (!$adult) {
                        echo "[WARN]アダルト除外 ".(string)$item->ItemAttributes->Title."\n";
                        $cnt++;
                        continue 2;
                    }
                }
            }
            if (!empty($item->ItemAttributes->ListPrice->Amount))
                $listprice = (int)$item->ItemAttributes->ListPrice->Amount;
            else
                $listprice = (int)$item->Offers->Offer->OfferListing->Price->Amount;
            if (!empty($item->Offers->Offer->OfferListing->Price->Amount))
                $lowprice = (int)$item->Offers->Offer->OfferListing->Price->Amount;
            else
                $lowprice = (int)$item->OfferSummary->LowestNewPrice->Amount;
            if (!empty($listprice) && !empty($lowprice)) {
                $offprice = $listprice - $lowprice;
                $discount = floor($offprice/$listprice*100);
            } else {
                $offprice = 0;
                $discount = 0;
            }

            if (!empty($listprice) && !empty($lowprice) && $discount != 0) {
                $ret[$cnt]['Number']        = $cnt;
                $ret[$cnt]['ASIN']          = (string)$item->ASIN;
                $ret[$cnt]['DetailPageURL'] = (string)$item->DetailPageURL;
                $ret[$cnt]['ImageURL']      = (string)$item->MediumImage->URL;
                $ret[$cnt]['Title']         = (string)$item->ItemAttributes->Title;
                $ret[$cnt]['ListPrice']     = $listprice;
                $ret[$cnt]['LowPrice']      = $lowprice;
                $ret[$cnt]['OffPrice']      = $listprice - $lowprice;
                $ret[$cnt]['Discount']      = $discount;
                $ret[$cnt]['Adult']         = $adult_flg;
//echo "title:".$ret[$cnt]['Title']." listprice: $listprice lowprice:$lowprice discount:".$ret[$cnt]['Discount']."\n";
            }
            $cnt++;
        }
    } else {
        return false;
    }

//print_r($ret);
    return $ret;
}

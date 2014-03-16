<?php
require_once 'curl.php';
// YJDNアプリケーションID
define('YJDN_APPID', '');
// 日本語形態素解析API
define('YJDN_MA_API', 'http://jlp.yahooapis.jp/MAService/V1/parse?appid='.YJDN_APPID);
// 日本語係り受け解析API
define('YJDN_DA_API', 'http://jlp.yahooapis.jp/DAService/V1/parse?appid='.YJDN_APPID);
// キーフレーズ抽出API
define('YJDN_KP_API', 'http://jlp.yahooapis.jp/KeyphraseService/V1/extract?appid='.YJDN_APPID);
// NGワード
define('YJDN_NG_WORD', '速報,お知らせ');

function getKeyword($str, $threshold = 10, $ng_flg = true) {
    // 全角スペース削除
    $str = str_replace('　', '', $str);
    // APIコール
    $url = YJDN_KP_API.'&sentence='.urlencode($str).'&output=php';
    $response = getWebAPI($url);
    if (empty($response)) return false;

    $ret = array();
    $ng_word = explode(',', YJDN_NG_WORD);
    foreach (unserialize($response) as $key => $score) {
        // しきい値以下は削除
        if ($score < $threshold) continue;
        // NGワードを含むものは削除
        if ($ng_flg) {
            foreach ($ng_word as $ng) {
                if (strpos($key, $ng) !== false) continue 2;
            }
        }
        $ret[] = $key;
    }

    return $ret;
}

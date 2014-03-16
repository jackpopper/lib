<?php
function getWeb($url, $mobile = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // Locationをたどる
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);    // Locationをたどる際にRefferer設定
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);         // Locationをたどる回数
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);    // ステータス400以上で処理しない
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // 返り値を取得データにする
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // CURL実行にかけられる秒数
    if ($mobile) {
        $header = array('User-Agent:Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; ja-jp) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $obj = curl_exec($ch);
    if ($obj === false ) {
        echo 'Curl error: '.curl_error($ch)."\n";
        return false;
    }
    curl_close($ch);

    return $obj;
}

function getWebAPI($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);    // ステータス400以上で処理しない
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // 返り値を取得データにする
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // CURL実行にかけられる秒数
    $obj = curl_exec($ch);
    if ($obj === false ) {
//        echo 'Curl error: '.curl_error($ch)."\n";
        return false;
    }
    curl_close($ch);

    return $obj;
}

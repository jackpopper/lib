<?php
define('DEBUG', true);

// ->UTF-8
function convertUTF8($str, $fenc = 'SJIS') {
    return mb_convert_encoding($str, 'UTF-8', $fenc);
}

// 記録用
function echoLogTime($type, $name) {
    switch ($type) {
        case 's':
            echo date('Y/m/d H:i:s')." [START] $name\n";
            break;
        case 'e':
            echo date('Y/m/d H:i:s')." [END] $name\n";
            break;
    }
}

// キルスイッチチェック
function checkKillSwitch($kill_switch) {
    if (file_exists($kill_switch)) {
        echo "kill switch during operation!\n";
        return true;
    }
    return false;
}

// doneファイル作成
function createDone($filepath) {
    $done_cmd = '/bin/date > '.$filepath;
    `$done_cmd`;
}

// doneファイルチェック
function checkDone($filepath, $wait = 2, $rm_flg = true) {
    $success = false;
    for ($roop=0; $roop<$wait; $roop++) {
        if (file_exists($filepath)) {
            if ($rm_flg) {
                $rm_cmd = '/bin/rm '.$filepath;
                `$rm_cmd`;
            }
            $success = true;
            break;
        } else {
            echo "wait donefile\n";
            sleep(10);
        }
    }

    if ($success) {
        echo "found donefile\n";
        return true;
    } else {
        echo "[ERROR]not found donefile\n";
        return false;
    }
}

// ファイル書き込み
function writeFile($filepath, $data) {
    $handle = fopen($filepath, 'w');

    // 配列なら改行する
    if (is_array($data)) {
        foreach ($data as $d) {
            fwrite($handle, $d."\n");
        }
    } else {
        fwrite($handle, $data);
    }

    fclose($handle);
}

// ファイルを配列として取得
function getFile($filepath) {
    if (!(file_exists($filepath))) {
        echo "[ERROR]not found $filepath\n";
        return false;
    }
    $data = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    return $data;
}

// debug用
function dprint($var) {
    if (DEBUG) {
        if (is_array($var)) print_r($var);
        else                echo $var."\n";
    }
}

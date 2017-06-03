<?php

require_once('config.php');

function setLog($message) {
    global $log_enable;
    if($log_enable) {
        $log_file_name = 'logs.txt';

        if(file_exists($log_file_name)) {
            $log = array_diff(explode("\r\n", file_get_contents($log_file_name)), array(''));
        }

        $log[] = date("m.d.Y-H:i:s").' | '.$message;

        if(file_put_contents($log_file_name, implode("\r\n", $log))) {
            return true;
        } else {
            return false;
        }
    }
}

function checkApiError($apiJsonArray) {
    if(in_array('28', $apiJsonArray['error']['error_code'])) { 
        echo '<h2>Ошибка: Сбой авторизации приложения. Срок действия access_token закончен</h2>';
        setLog('Ошибка: Сбой авторизации приложения. Срок действия access_token закончен');
        exit(); 
    } elseif(in_array('17', $apiJsonArray['error']['error_code'])) {
        echo '<h2>Ошибка: Пройдите валидацию клинув на ссылку: </h2><a hreaf="'.$apiJsonArray['error']['redirect_uri'].'">CLICK HERE</a>';
        setLog('Ошибка: Не прошли валидацию');
        exit();
    } elseif(in_array('5', $apiJsonArray['error']['error_code'])) {
        echo '<h2>Ошибка: Вы не авторизованы. Введите access_token в config.php</h2>';
        setLog('Ошибка:  Вы не авторизованы. Введите access_token в config.php');
        exit();
    } elseif(in_array('6', $apiJsonArray['error']['error_code'])) {
        echo '<h2>Ошибка: Слишком много запросов в секунду</h2>';
        setLog('Ошибка:  Слишком много запросов в секунду');
        exit();
    } elseif(in_array('14', $apiJsonArray['error']['error_code'])) {
        echo '<h2>Ошибка: Требуется ввод капчи</h2>';
        setLog('Ошибка: Требуется ввод капчи');
        exit();
    }
}

function DownloadImages($url, $filename){
    $ch = curl_init($url);
    $fp = fopen($filename, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}  

function getPOST($url, $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //урл сайта к которому обращаемся 
    curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
    curl_setopt($ch, CURLOPT_HEADER, false); //выводим заголовки
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true); //теперь curl вернет нам ответ, а не выведет
    curl_setopt($ch, CURLOPT_POST, true); //передача данных методом POST
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); //тут переменные которые будут переданы методом POST
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
    
function getApiMethod($method_name, $params) {
    global $access_token;
    global $api_version;

    // Сделаем проверки на токен и версию апи, если их не указали, добавим.
    if (!array_key_exists('access_token', $params) && !is_null($access_token)) {
        $params['access_token'] = $access_token;
    }

    if (!array_key_exists('v', $params) && !is_null($api_version)) {
        $params['v'] = $api_version;
    }
    
    // Сортируем массив по ключам
    ksort($params);
    
    // Отправим запрос
    return(getPOST('https://api.vk.com/method/'.$method_name, $params));
}

?>
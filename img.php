<?php

header('Content-type: image/png');

require_once('config.php');
require_once('api.php');
//Узнаем кто последний зашел в группу
$last_subscribe = getApiMethod('groups.getMembers', array(
            'group_id' => $GroupId,
            'sort' => 'time_desc',
            'count' => '1',
            'fields' => 'photo_200',
            'access_token' => $token
        ));

setLog('Ответ сервера #5 '.$last_subscribe);

if($last_subscribe) {
    $last_subscribe = json_decode($last_subscribe, true);

    $members_count = $last_subscribe['response']['count'];
    $last_subscribe_firstname = $last_subscribe['response']['users'][0]['first_name'];
    $last_subscribe_lastname = $last_subscribe['response']['users'][0]['last_name'];
    $last_subscribe_photo = $last_subscribe['response']['users'][0]['photo_200'];
    // Скачиваем фото
    if(!empty($last_subscribe_firstname) && !empty($last_subscribe_lastname) && !empty($last_subscribe_photo)){
        DownloadImages($last_subscribe_photo, 'header/last_subscribe.jpg');
    }

}
// Фоновая картинка
function RoundingOff($_imagick, $width, $height) {
    $_imagick->adaptiveResizeImage($width, $height, 140);
    $_imagick->setImageFormat('png');
        
    $_imagick->roundCornersImage(
        140, 140, 0, 0, 0
    );
}

date_default_timezone_set("Europe/Moscow");
$im = @ImageCreateFromJPEG ($path.'header/header.jpg');


// Аватар пользователя
// Цвет текста
$white = @imagecolorallocate($im, 39,39,39);
// Время
$time = date("H:i");

// Вывод последнего пользователя
$file_name = 'header/last_subscribe.jpg';
$last_subscribe_photo = new Imagick($file_name);
RoundingOff($last_subscribe_photo, 140,140);
file_put_contents ('header/last_subscribe.png', $last_subscribe_photo);
$user = @ImageCreateFromPNG($path.'header/last_subscribe.png');
@imagettftext($im, 30, 0, 45, 350, $white, $path.'font/BebasNeue Regular.ttf',$time);
@imagecopy($im, $user, 725, 97, 0, 0, 140, 140);
// Вывод имени
$font = $path.'font/Tahoma.ttf';
$text = $last_subscribe_firstname . ' ' . $last_subscribe_lastname;
$fontwidth = imagefontwidth($font);

$center = (imagesx($im)/2) - (14*strlen($text)/2);

// Adds the text to the image

@imagettftext($im, 20, 0, $center, 350, $white, $font, $text);
// Вывод фамилии
// @imagettftext($im, 20, 0, 1450, 130, $white, $path.'font/BebasNeue Regular.ttf',$UsersLastName);
//На этом все почти:)
// успешно загружено
imagejpeg($im, NULL, 100);
imagedestroy($im);



echo "<b style='margin: 20px;'>Hello world</b>";
?>

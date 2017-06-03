<?php
header('Content-type: image/png');
require_once('config.php');
//Узнаем кто последний зашел в группу
$GroupMembers = file_get_contents("https://api.vk.com/method/groups.getMembers?group_id=142528981&sort=time_desc&count=1&fields=photo_100&access_token=".$token);
$GroupMembersResult = json_decode($GroupMembers, true);
//print_r($GroupMembersResult);
// Информация о последнем вступившем
$Users_Count = $GroupMembersResult['response']['count'];
$UsersName = $GroupMembersResult['response']['users'][0]['first_name'];
$UsersLastName = $GroupMembersResult['response']['users'][0]['last_name'];
$UsersPhoto = $GroupMembersResult['response']['users'][0]['photo_100'];
//--------Самое интересное - рисование------------
// Фоновая картинка
date_default_timezone_set("Europe/Moscow");
$im = @ImageCreateFromJPEG ($path.'header/header.jpg');
// Аватар пользователя
$stamp = @ImageCreateFromJPEG($UsersPhoto);
// Цвет текста
$white = @imagecolorallocate($im, 39,39,39);
// Время
$time = date("H:i");
// Вывод последнего пользователя
@imagettftext($im, 30, 0, 45, 350, $white, $path.'font/BebasNeue Regular.ttf',$time);
@imagecopy($im, $stamp, 730, 120, 0, 0, imagesx($stamp), imagesy($stamp));

// Вывод имени
@imagettftext($im, 20, 0, 670, 350, $white, $path.'font/Tahoma.ttf',$UsersName . ' ' . $UsersLastName);
// Вывод фамилии
// @imagettftext($im, 20, 0, 1450, 130, $white, $path.'font/BebasNeue Regular.ttf',$UsersLastName);
//На этом все почти:)
// успешно загружено
imagejpeg($im, NULL, 100);
imagedestroy($im);

echo "<b style='margin: 20px;'>Hello world</b>";
?>

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
$im = @ImageCreateFromJPEG ($path.'header/day_bg.jpg');
// Аватар пользователя
$stamp = @ImageCreateFromJPEG($UsersPhoto);
// Цвет текста
$white = @imagecolorallocate($im, 255,255,255);
// Время
$time = date("H:i:s");
// Вывод последнего пользователя
@imagecopy($im, $stamp, 1336, 45, 50, 50, imagesx($stamp), imagesy($stamp));

// Вывод имени
@imagettftext($im, 10, 0, 625, 25, $white, $path.'fonts/open-sans-bold.ttf',$UsersName);
// Вывод фамилии
@imagettftext($im, 10, 0, 638, 38, $white, $path.'fonts/open-sans-bold.ttf',$UsersLastName);
// Вывод времени
@imagettftext($im, 10, 0, 20, 185, $white, $path.'fonts/open-sans-bold.ttf',$time);
//На этом все почти:)
// успешно загружено
imagejpeg($im, NULL, 100);
imagedestroy($im);
?>

<?php
//VK API Динамическая обложка вк - Как сделать динамическую обложку в группе вконтакте php скрипт бесплатно
require_once('config.php');
$tmp_image = file_get_contents('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'img.php');
file_put_contents('cover/tmp.jpg',$tmp_image);
$cover_path = dirname(__FILE__).'/cover/tmp.jpg';
$post_data = array('photo' => new CURLFile($cover_path, 'image/jpeg', 'image0'));
$upload_url = file_get_contents("https://api.vk.com/method/photos.getOwnerCoverPhotoUploadServer?group_id=".$GroupId."&crop_x2=1590&access_token=".$token);
$url = json_decode($upload_url)->response->upload_url;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$result = json_decode(curl_exec($ch),true);
// $safe = file_get_contents("https://api.vk.com/method/photos.saveOwnerCoverPhoto?hash=".$result['hash']."&photo=".$result['photo']."&access_token=".$token);
// print_r($safe);
// Ошибка случилась из за того, что мы не написали саму картинку img.php
// И Так я подготовил тестовую группу для примера и 3 файла php, папку fonts и в ней шрифт далее папкак cover и в ней будующая обложка
// сегодня научимся выводить последнего вошедшего пользователя а точнее его аватал имя и фамилию и время все это будет обновляться по средствам крон но мы для теста будем делать это сами ..
?>
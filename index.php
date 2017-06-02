<?php
//VK API Динамическая обложка вк - Как сделать динамическую обложку в группе вконтакте php скрипт бесплатно

if (!isset($_REQUEST)) { 
  return; 
} 

//Строка для подтверждения адреса сервера из настроек Callback API 
$confirmation_token = 'd8v2ve07'; 

//Ключ доступа сообщества 
$token = 'c0223f775444cf3d58a8a1442ec76a9571c8f58e3e24616d9440f73dc43022bbead9b2e576cb41d09c0a1'; 

//Получаем и декодируем уведомление 
$data = json_decode(file_get_contents('php://input')); 

//Проверяем, что находится в поле "type" 
switch ($data->type) { 
  //Если это уведомление для подтверждения адреса сервера... 
  case 'confirmation': 
    //...отправляем строку для подтверждения адреса 
    echo $confirmation_token; 
    break; 

//Если это уведомление о новом сообщении... 
  case 'message_new': 
    //...получаем id его автора 
    $user_id = $data->object->user_id; 
    //затем с помощью users.get получаем данные об авторе 
    $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0")); 

//и извлекаем из ответа его имя 
    $user_name = $user_info->response[0]->first_name; 

//С помощью messages.send и токена сообщества отправляем ответное сообщение 
    $request_params = array( 
      'message' => "Hello, {$user_name}!", 
      'user_id' => $user_id, 
      'access_token' => $token, 
      'v' => '5.0' 
    ); 

$get_params = http_build_query($request_params); 

file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 

//Возвращаем "ok" серверу Callback API 
    echo('ok'); 

} 

require_once('config.php');
$tmp_image = file_get_contents('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'img.php');
file_put_contents('tmp.jpg',$tmp_image);
$cover_path = dirname(__FILE__).'/tmp.jpg';
$post_data = array('photo' =>  new CURLFile($cover_path, 'image/jpeg', 'image0'));
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
$safe = file_get_contents("https://api.vk.com/method/photos.saveOwnerCoverPhoto?hash=".$result['hash']."&photo=".$result['photo']."&access_token=".$token);
print_r($safe);
// Ошибка случилась из за того, что мы не написали саму картинку img.php
// И Так я подготовил тестовую группу для примера и 3 файла php, папку fonts и в ней шрифт далее папкак cover и в ней будующая обложка
// сегодня научимся выводить последнего вошедшего пользователя а точнее его аватал имя и фамилию и время все это будет обновляться по средствам крон но мы для теста будем делать это сами ...
?>
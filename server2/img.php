<?php

header('Content-type: image/png');
ini_set('display_errors', 1);
error_reporting( error_reporting() & ~E_NOTICE );
require_once('api.php');

//Узнаем кто последний зашел в группу
$last_subscribe = getApiMethod('groups.getMembers', array(
            'group_id' => '142528981',
            'sort' => 'time_desc',
            'count' => '1',
            'fields' => 'photo_200',
            'access_token' => 'e4cd3352e7f5a65edef3af98a0bd8b0f3ded3b298cf5f8e6fcb57b4a2c9ee9415bb60e6270a29967cb587'
        ));
print_r(json_decode($last_subscribe, true));
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

// Получим посты со стены
// больше 100 постов получать нет смысла, так как в вк ограничение
// разрешено постить не больше 50 постов в сутки.
$wall_get = getApiMethod('wall.get', array(
    'owner_id' => '-'.$GroupId,
    'filter' => 'all',
    'count' => '100',
    'access_token' => $token
));

if($wall_get) {
    $wall_get = json_decode($wall_get, true);
    //checkApiError($wall_get);
    
    foreach($wall_get['response'] as $wall) {
        
        // Получим кол-во комментариев к посту
        $count = $wall['comments']['count'];
        $offset = 0;
        if($count > 0) { 
            // Получим все комментарии, так как их может быть больше 100.
            
           	$last = getApiMethod('wall.getComments', array( 
                'owner_id' => '-'.$GroupId,
                'post_id' => $wall['id'],
                'need_likes' => '1',
                'count' => '100',
                'offset' => $offset,
                'access_token' => $token,
                'sort' => 'desc'
            ));
            $last = json_decode($last, true);
            sleep(5);
	        foreach($last['response'] as $lat) {
	            if($lat['from_id'] != ('-'.$GroupId) && $lat['from_id'] != '') {
		            $last_coment = getApiMethod('users.get', array(
			            'user_ids' => $lat['from_id'],
			            'fields' => 'photo_100,first_name,last_name',
			            'access_token' => $token
			        ));
			        $last_coment = json_decode($last_coment, true);
			        $last_text = $lat['text'];
			        DownloadImages($last_coment['response'][0]['photo_100'], 'header/last_coment_user.jpg');
			        break;
			    }
	    	}
            break;
        }

    }
}
// Фоновая картинка
function RoundingOff($_imagick, $width, $height) {
    $_imagick->adaptiveResizeImage($width, $height, 99);
    $_imagick->setImageFormat('png');
        
    $_imagick->roundCornersImage(
        99, 99, 0, 0, 0
    );
}

date_default_timezone_set("Europe/Moscow");
$im = @ImageCreateFromPNG ($path.'header/header.png');



// Аватар пользователя
// Цвет текста
// $white = @imagecolorallocate($im, 39,39,39);
$white = @imagecolorallocate($im, 255,255,255);
// Время
$time = date("H:i");

// Длинный многострочный текст, который нужно разбить на строки нужной нам длины 
$text5 = $last_coment['response'][0]['first_name'] . ' ' . $last_coment['response'][0]['last_name'];

// Способ выравнивания текста
//$align = "left";
$align = "right";
$font = 'font/Gotham.ttf';
//$align = "right";

// Создаем цвета, которые понадобятся
$black	= @imagecolorallocate($im, 255, 255, 255);	// черный

// Заливаем изображение цветом

// Разбиваем наш текст на массив слов
$arr = explode(' ', $text5);

// Возращенный текст с нужными переносами строк, пока пустая
$ret = "";
$font_size = 28;

$width_text = 497;
// Перебираем наш массив слов
foreach($arr as $word)
	{
		// Временная строка, добавляем в нее слово
		$tmp_string = $ret.' '.$word;
		
		// Получение параметров рамки обрамляющей текст, т.е. размер временной строки 
		$textbox = @imagettfbbox($font_size, 0, $font, $tmp_string);
		
		// Если временная строка не укладывается в нужные нам границы, то делаем перенос строки, иначе добавляем еще одно слово
		if($textbox[2] > $width_text)
			$ret.=($ret==""?"":"\n").$word;
		else
			$ret.=($ret==""?"":" ").$word;
	}	

if($align=="left")
	{	
		// Накладываем возращенный многострочный текст на изображение, отступим сверху и слева по 50px
		@imagettftext($im, $font_size ,0 , 50, 50, $black, $font, $ret);
	}
else
	{
		// Разбиваем снова на массив строк уже подготовленный текст
		$arr = explode("\n", $ret);
		
		// Расчетная высота смещения новой строки
		$height_tmp = 0;
		
		//Выводить будем построчно с нужным смещением относительно левой границы
		foreach($arr as $str)
			{
				// Размер строки 
				$testbox = @imagettfbbox($font_size, 0, $font, $str);
				
				// Рассчитываем смещение
				if($align=="center")
					$left_x = round(($width_text - ($testbox[2] - $testbox[0]))/2);
				else
					$left_x = round($width_text - ($testbox[2] - $testbox[0]));
					
				// Накладываем текст на картинку с учетом смещений
				@imagettftext($im, $font_size ,0 , $left_x, 185, $black, $font, $str); // 50 - это отступы от края
				
				// Смещение высоты для следующей строки
				$height_tmp = $height_tmp + 19;
			}
	}

//-------------------------------------------------------------------------------------------------------------------------

// $file_name = 'header/last_subscribe.jpg';
// $last_subscribe_photo = new Imagick($file_name);
// RoundingOff($last_subscribe_photo, 99,99);
// file_put_contents ('header/last_subscribe.png', $last_subscribe_photo);
// $user = @ImageCreateFromPNG($path.'header/last_subscribe.png');
// @imagettftext($im, 30, 0, 45, 350, $white, $path.'font/BebasNeue Regular.ttf',$time);


// $file_name2 = 'header/last_coment_user.jpg';
// $last_coment_user_photo = new Imagick($file_name2);
// RoundingOff($last_coment_user_photo, 99,99);
// file_put_contents ('header/last_coment_user.png', $last_coment_user_photo);
// $user2 = @ImageCreateFromPNG($path.'header/last_coment_user.png');
// @imagecopy($im, $user2, 517.9, 103.5, 0, 0, 99, 99);
// @imagecopy($im, $user, 970.5, 103.5, 0, 0, 99, 99);
// // Вывод имени

// $text = $last_subscribe_firstname . ' ' . $last_subscribe_lastname;

// $center = (imagesx($im)/2) - (7.5*iconv_strlen($text,'UTF-8'));
// $center2 = (imagesx($im)/2) - (5*iconv_strlen($text2,'UTF-8'));

// // Adds the text to the image
// $font2 = 'Tahoma.ttf';
// @imagettftext($im, 28, 0, 1091, 185, $white, 'font/Gotham.ttf', $text);
// @imagettftext($im, 20, 0, $center2, 380, $white, 'font/BebasNeue Regular.ttf', $text2);

//-------------------------------------------------------------------------------------------------------------------------

// Вывод последнего пользователя
$file_name = 'header/last_subscribe.jpg';
$last_subscribe_photo = new Imagick($file_name);
RoundingOff($last_subscribe_photo, 140,140);
file_put_contents ('header/last_subscribe.png', $last_subscribe_photo);
$user = @ImageCreateFromPNG($path.'header/last_subscribe.png');
@imagettftext($im, 30, 0, 45, 350, $white, $path.'font/BebasNeue Regular.ttf',$time);
@imagecopy($im, $user, 725, 99, 0, 0, 140, 140);
// Вывод имени

$text = $last_subscribe_firstname . ' ' . $last_subscribe_lastname;
$text2 = $last_coment['response'][0]['first_name'] . ' ' . $last_coment['response'][0]['last_name'] . ' - ' . $last_text;

$center = (imagesx($im)/2) - (7.5*iconv_strlen($text,'UTF-8'));
$center2 = (imagesx($im)/2) - (5*iconv_strlen($text2,'UTF-8'));

// Adds the text to the image
$font2 = 'Tahoma.ttf';
@imagettftext($im, 20, 0, $center, 350, $white, 'font/Tahoma.ttf', $text);
@imagettftext($im, 20, 0, $center2, 380, $white, 'font/BebasNeue Regular.ttf', $text2);
// Вывод фамилии
@imagettftext($im, 20, 0, 1450, 130, $white, $path.'font/BebasNeue Regular.ttf',$UsersLastName);
//-------------------------------------------------------------------------------------------------------------------------
//На этом все почти:)
// успешно загружено
imagejpeg($im, NULL, 100);
imagedestroy($im);


?>

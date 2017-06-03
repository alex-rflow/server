<?php

/*
Автор: Гайдарлы Алексей (Sony Aimbo)
Канал: https://www.youtube.com/c/SonyAimboShow [SUBSCRIBE]
Группа: https://vk.com/sonyaimbo_channel [SUBSCRIBE]

Запрещено использование скрипта в коммерческих использований, перепродажа и т.п. 
Запрещено изменять копирайт.
Запрещено заливать на сторонние ресурсы без моего согласия.

ВОЗМОЖНОСТИ
- Выводит последнего подписчика в группе
- Выводит топового комментатора (кто больше оставил комментариев сегодня)
- Выводит топа по лайкам (чьи комментарии в сумме за сегодня, набрали большее кол-во лайков)
- Возможность менять фон обложки в зависимости от времени суток (утро, день, вечер, ночь)

*/

if($_GET['debug'] == 1) {ini_set("display_errors",1);} else {ini_set("display_errors",0);}
error_reporting(E_ALL);

require_once('config.php');
require_once('api.php');

header('Content-type: text/html; charset=utf-8');

echo '<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">';
echo '<link href="style.css" rel="stylesheet">';
echo '<b>--------------------------------------------------------------------------------</b></br></br>';
echo '<b>ВИДЕО ПО УСТАНОВКЕ <a href="https://www.youtube.com/c/vikimeyson?sub_confirmation=1 ">YOUTUBE</a></b></br>';
echo '<b style="color: #16a085;">АВТОР СКРИПТА:</b> <a href="https://vk.com/qlimbo">ВКОНТАКТЕ</a></br>';
echo '<b style="color: #16a085;">ГРУППА СКРИПТА:</b> <a href="https://vk.com/viki_meyson">ВКОНТАКТЕ</a></br>';
echo '<b style="color: #16a085;">--------------------------------------------------------------------------------</b></br></br>';

// Получим текущую дату
$date_today = date('Ymd');

if($show_top_like or $show_top_comments) {
    setLog('Получаю посты группы');
    // Получим посты со стены
    // больше 100 постов получать нет смысла, так как в вк ограничение
    // разрешено постить не больше 50 постов в сутки.
    $wall_get = getApiMethod('wall.get', array(
        'owner_id' => '-'.$group_id,
        'count' => '100'
    ));
    
    setLog('Ответ сервера #1 '.$wall_get);

    if($wall_get) {
        $wall_get = json_decode($wall_get, true);

        //checkApiError($wall_get);

        $countlike = array();
        $countcomments = array();
        
        foreach($wall_get['response']['items'] as $wall) {
            
            // Получим кол-во комментариев к посту
            $count = $wall['comments']['count'];
            $offset = 0;

            if($count > 0) { 
                // Получим все комментарии, так как их может быть больше 100.
                while($offset < $count) {
                    setLog('Получаю кол-во комментариев к посту '.$wall['id']);
                    // Отправим запрос на получение комментариев
                    $comments_get = getApiMethod('wall.getComments', array( 
                        'owner_id' => '-'.$group_id,
                        'post_id' => $wall['id'],
                        'need_likes' => '1',
                        'count' => '100',
                        'offset' => $offset
                    ));
                    
                    if($comments_get) {
                        $comments_get = json_decode($comments_get, true);
                        
                        foreach($comments_get['response']['items'] as $comments) {
                            
                            if($date_today == date('Ymd', $comments['date'])) {
                                // В двух словах мы заносим данные в массив, суммируя их
                                if(!isset($countcomments[$comments['from_id']]) and !isset($countlike[$comments['from_id']])) {
                                    $countcomments[$comments['from_id']] = 1;
                                    $countlike[$comments['from_id']] = $comments['likes']['count'];   
                                } else {
                                    $countcomments[$comments['from_id']]++;
                                    $countlike[$comments['from_id']] += $comments['likes']['count'];
                                } 
                                //var_dump($comments);
                            }
                            
                        }  
                    }

                    if($offset<$count) 
                        $offset = $offset + 100;
                    
                }
            }

        }
    }
}



if($show_top_like) {
    $day_like_top = 0;
    if(count($countlike) > 0) {
        // Теперь найдем кто суммарно получил большее кол-во лайков к комментариям
        $value = max($countlike); 
        $day_like_top = array_search($value, $countlike);
        setLog('COUNT LIKE: '.$countlike[$day_like_top]);
        setLog('Получаю ID кто сумарно набрал большее кол-во лайков к комментариям '.$day_like_top);

        sleep(5);

        if($day_like_top > 0) {
            $user_top_like = getApiMethod('users.get', array(
                'user_ids' => $day_like_top,
                'fields' => 'photo_200'
            ));

            setLog('Ответ сервера #3 '.$user_top_like);

            if($user_top_like) {
                $user_top_like = json_decode($user_top_like, true);

                $top_like_name = $user_top_like['response'][0]['first_name'];
                $top_like_lastname = $user_top_like['response'][0]['last_name'];
                $top_like_photo = $user_top_like['response'][0]['photo_200'];
                
                setLog('И.Ф Лайки: '.$top_like_name.' '.$top_like_lastname);
                echo '<p>*** Больше всех сегодня лайков набрал: '.$top_like_name.' '.$top_like_lastname.' - '.$countlike[$day_like_top].' шт.</p></br>';
                // Скачиваем фото
                if(!empty($top_like_name) && !empty($top_like_lastname) && !empty($top_like_photo)){
                    DownloadImages($top_like_photo, 'header/top_likes.jpg');
                }
            }
        }
    }
}

if($show_top_comments) {
    $day_comment_top = 0;
    if(count($countcomments) > 0) {
        // Теперь найдем кто суммарно написал больше всех комментариев
        $value = max($countcomments); 
        $day_comment_top = array_search($value, $countcomments);
        setLog('COUNT COMMENT: '.$countcomments[$day_comment_top]);
        setLog('Получаю ID кто суммарно написал больше всех комментариев '.$day_comment_top);
        
        
        sleep(5);

        if($day_comment_top > 0) {
            $user_top_comment = getApiMethod('users.get', array(
                'user_ids' => $day_comment_top,
                'fields' => 'photo_200'
            ));

            setLog('Ответ сервера #4 '.$user_top_comment);

            if($user_top_comment) {
                $user_top_comment = json_decode($user_top_comment, true);

                $top_comment_name = $user_top_comment['response'][0]['first_name'];
                $top_comment_lastname = $user_top_comment['response'][0]['last_name'];
                $top_comment_photo = $user_top_comment['response'][0]['photo_200'];
                
                setLog('И.Ф Комменты: '.$top_comment_name.' '.$top_comment_lastname);
                echo '<p>*** Больше всех сегодня комментариев написал: '.$top_comment_name.' '.$top_comment_lastname.' - '.$countcomments[$day_comment_top].' шт.</p></br>';
                // Скачиваем фото
                if(!empty($top_comment_name) && !empty($top_comment_lastname) && !empty($top_comment_photo)){
                    DownloadImages($top_comment_photo, 'header/top_comments.jpg');
                }
            }
        }
    }
}

if($show_last_subscribe) {
    sleep(5);
    // Теперь найдем последнего подписчика
    $last_subscribe = getApiMethod('groups.getMembers', array(
                'group_id' => $group_id,
                'sort' => 'time_desc',
                'count' => '1',
                'fields' => 'photo_200',
                'access_token' => $access_token
            ));

    setLog('Ответ сервера #5 '.$last_subscribe);

    if($last_subscribe) {
        $last_subscribe = json_decode($last_subscribe, true);

        $members_count = $last_subscribe['response']['count'];
        $last_subscribe_firstname = $last_subscribe['response']['items'][0]['first_name'];
        $last_subscribe_lastname = $last_subscribe['response']['items'][0]['last_name'];
        $last_subscribe_photo = $last_subscribe['response']['items'][0]['photo_200'];

        setLog('Получаю последнего вступившего в группу: '.$last_subscribe_firstname.' '.$last_subscribe_lastname);
        echo '<p>*** Последний подписчик '.$last_subscribe_firstname.' '.$last_subscribe_lastname.'</p></br>';
        // Скачиваем фото
        if(!empty($last_subscribe_firstname) && !empty($last_subscribe_lastname) && !empty($last_subscribe_photo)){
            DownloadImages($last_subscribe_photo, 'header/last_subscribe.jpg');
        }

    }
}

timeCountdown();

// -----------------------------------------------------------------------------
// --------------------------------- РИСОВАНИЕ ---------------------------------
// -----------------------------------------------------------------------------
setLog('Рисую обложку');

$draw = new ImagickDraw(); 

if(file_exists(BASEPATH.timeToDayBg())) {
    $bg = new Imagick(BASEPATH.timeToDayBg());
} else {
    setLog('ОШИБКА! Не найден '.BASEPATH.timeToDayBg().' Загрузите файл либо отключите $show_time_of_day в config.php');
    print_r('ОШИБКА! Не найдена обложка, смотрите logs.txt');
    exit;
}

$draw->setTextAlignment(Imagick::ALIGN_CENTER);

// Последний подписчик
if($show_last_subscribe) {
    $file_name = BASEPATH.'header/last_subscribe.jpg';

    if(file_exists($file_name) && $show_last_subscribe) {
        $last_subscribe_photo = new Imagick($file_name);
        if($roundingOff==true) {
            RoundingOff($last_subscribe_photo, $last_subscribe_width,$last_subscribe_height);
        }

        $draw->setFont(BASEPATH."/font/".$font_last_subscribe);
        $draw->setFontSize($last_subscribe_font_size);
        $draw->setFillColor("rgb(".$last_subscribe_font_color.")");

        $bg->compositeImage($last_subscribe_photo, Imagick::COMPOSITE_DEFAULT, $last_subscribe_photo_pixel_x, $last_subscribe_photo_pixel_y);
        $bg->annotateImage($draw, $last_subscribe_text_pixel_x, $last_subscribe_text_pixel_y, 0, mb_strtoupper($last_subscribe_firstname.' '.$last_subscribe_lastname, 'UTF-8'));
    }
}

// Топ по комментам
$file_name = BASEPATH.'header/top_comments.jpg';

if(file_exists($file_name) && $show_top_comments) {
    $top_comments_photo = new Imagick($file_name);
    if($roundingOff==true) {
        RoundingOff($top_comments_photo, $top_comments_width,$top_comments_height);
    }

    $draw->setFont(BASEPATH."/font/".$font_top_comments);
    $draw->setFontSize($top_comments_font_size);
    $draw->setFillColor("rgb(".$top_comments_font_color.")");

    $bg->compositeImage($top_comments_photo, Imagick::COMPOSITE_DEFAULT, $top_comments_photo_pixel_x, $top_comments_photo_pixel_y);
    $bg->annotateImage($draw, $top_comments_text_pixel_x, $top_comments_text_pixel_y, 0, mb_strtoupper($top_comment_name.' '.$top_comment_lastname, 'UTF-8'));
}

// Топ по лайкам
$file_name = BASEPATH.'header/top_likes.jpg';

if(file_exists($file_name) && $show_top_like) {
    $top_like_photo = new Imagick($file_name);
    if($roundingOff==true) {
        RoundingOff($top_like_photo, $top_like_width,$top_like_height);
    }

    $draw->setFont(BASEPATH."/font/".$font_top_like);
    $draw->setFontSize($top_like_font_size);
    $draw->setFillColor("rgb(".$top_like_font_color.")");

    $bg->compositeImage($top_like_photo, Imagick::COMPOSITE_DEFAULT, $top_like_photo_pixel_x, $top_like_photo_pixel_y);
    $bg->annotateImage($draw, $top_like_text_pixel_x, $top_like_text_pixel_y, 0, mb_strtoupper($top_like_name.' '.$top_like_lastname, 'UTF-8'));
}

// Обратный отсчет
if($show_time_countdown){
    $draw->setFont(BASEPATH."/font/".$font_time_countdown);
    $draw->setFontSize($time_countdown_font_size);
    $draw->setFillColor("rgb(".$time_countdown_font_color.")");
    $bg->annotateImage($draw, $time_countdown_text_pixel_x, $time_countdown_text_pixel_y, 0, mb_strtoupper(timeCountdown(), 'UTF-8'));
}

// Время последнего обновления
if($show_time_update) {
    $draw->setFont(BASEPATH."/font/".$font_time_update);
    $draw->setFontSize($time_update_font_size);
    $draw->setFillColor("rgb(".$time_update_font_color.")");
    $bg->annotateImage($draw, $time_update_text_pixel_x, $time_update_text_pixel_y, 0, mb_strtoupper(date("H:i:s"), 'UTF-8'));
}

$bg->setImageFormat("png");
$bg->writeImage($output_header);



// -----------------------------------------------------------------------------
// --------------------------- ЗАГРУЗКА НА СЕРВЕР ------------------------------
// -----------------------------------------------------------------------------

// Получим адресс сервера
$getUrl = getApiMethod('photos.getOwnerCoverPhotoUploadServer', array(
    'group_id' => $group_id,
    'crop_x2' => '1590'
));
setLog('Получаю адресс сервера '.$getUrl);

if($getUrl) {
    $getUrl = json_decode($getUrl, true);

    $url = $getUrl['response']['upload_url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => new CURLFile($output_header, 'image/jpeg', 'image0')));
    $upload = curl_exec( $ch );
    curl_close( $ch );

    if($upload) {
        $upload = json_decode($upload, true);

        $getUrl = getApiMethod('photos.saveOwnerCoverPhoto', array(
            'hash' => $upload['hash'],
            'photo' => $upload['photo'],
        ));
        
        setLog('Загружаю обложку '.$getUrl);

        if(stripos($getUrl, 'response":{"images":[{')) {
            print_r('<p>*** Успешно загрузили обложку в группу</p></br>');
            echo '<img src="'.'header/output.png'.'" width="795" height="200">';
            setLog('Загружаю обложку в '.$group_id);
        } else {
            print_r('Ошибка при загрузке обложки '.$getUrl);
            setLog('Ошибка при загрузке обложки '.$getUrl);
        }
        
    }

    
    if(file_exists('header/top_likes.jpg')) {
        unlink('header/top_likes.jpg');
    }
    if(file_exists('header/top_comments.jpg')) {
        unlink('header/top_comments.jpg');
    }
    if(file_exists('header/last_subscribe.jpg')) {
        unlink('header/last_subscribe.jpg');
    }
   
}

function timeToDayBg(){
    global $show_time_of_day;
    global $image_bg;

    $clock = date("H");
    if($show_time_of_day) {
        if($clock >= 04 && $clock < 12) {return $image_bg['morning'];} 
        if($clock >= 11 && $clock < 18) {return $image_bg['day'];} 
        if($clock >= 17 && $clock < 24) {return $image_bg['evening'];}
        if($clock >= 23 || $clock < 5) {return $image_bg['night'];}
    } else {
        return $image_bg['morning'];
    }  
}

// Функция склонения слов
function correctForm($number, $suffix) {
    $keys = array(2, 0, 1, 1, 1, 2);
    $mod = $number % 100;
    $suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
    return $suffix[$suffix_key];
}

function timeCountdown() {
    global $dateCountdown;

    $now_date = strtotime(date("Y-m-d H:i:s"));
    $future_date = strtotime($dateCountdown);
    $difference_days = $future_date - $now_date;
    $days = floor($difference_days/86400);
    $difference_hours = $difference_days % 86400;
    $hours = floor($difference_hours/3600);
    $difference_min = $difference_hours % 3600;
    $min = floor($difference_min/60);

    $array1 = array("день", "дня", "дней");
    $word1 = correctForm($days, $array1);
    $array2 = array("час", "часа", "часов");
    $word2 = correctForm($hours, $array2);
    $array3 = array("минута", "минуты", "минут");
    $word3 = correctForm($min, $array3);

    return ($days.' '.$word1.' '.$hours.' '.$word2.' '.$min.' '.$word3);
    printf('<div class="time"><center><h1>%s %s %s %s %s %s</h1> ДО ОКОНЧАНИЯ СОБЫТИЯ</center></div>', $days,$word1,$hours,$word2,$min,$word3);
}

function RoundingOff($_imagick, $width, $height) {
    $_imagick->setImageFormat('png');
        
    $_imagick->roundCornersImage(
        90, 90, 0, 0, 0
    );
}






?>
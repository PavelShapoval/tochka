<?php
error_reporting(-1);
header('Content-Type: text/html; charset=utf-8');

require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);


function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',		
		
        'і' => 'i',   'ї' => 'ji',  'є' => 'je',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',		
		
        'І' => 'I',   'Ї' => 'JI',  'Є' => 'JE',
    );
    return strtr($string, $converter);
}

function seoURL($str) {
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, '-');
	// добавляем рандомное число в конце
    $url = ($str . '-' . rand(10,99));
	
    return $url;
}

?>
<html>
    <head>
	<title>Создание SEO-URL</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <style>
        body {
            background-color: #f1f4f5;
            color: #37474f;
            line-height: 1.4;
            font-family: 'Open Sans', sans-serif;
            margin: 70px;
            padding: 0;
            }
        .back_button {
            background-color: #399bff;
            color: #fff;
            margin-top: 15px;
            font-size: 14px;
            padding: 7px 20px 7px 20px;
            border: none;
            border-radius: 3px;
            vertical-align: middle;
            cursor: pointer;
			text-decoration: none;
            }
    </style>    
    </head>
    <body>
        <center>
        <h1>Script by WebMakers</h1>
		<p><a href="http://mvo4x4.ru" target="_blank">http://mvo4x4.ru</a></p>
		<br>
<?php

if(isset($_GET['products'])) {
$products   = $db->query("SELECT * FROM " . DB_PREFIX . "product");
$products   = $products->rows;

$step = 100;
    $i=0;
foreach($products as $product) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product['product_id'] . "'");
    $url = $url->rows;


    
    if(!empty($url)) {
        echo 'В товаре с id=' . $product['product_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации о товаре с id=' . $product['product_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $product['product_id'] . "' LIMIT 1");

        $info = $info->rows;


        
        foreach($info as $data) {        
            echo 'Товар: ' . $data['name'] . ' | URL: ' . seoURL($data['name']);
            $data['name'] = seoURL($data['name']);
            sleep(1);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product['product_id'] . "', keyword = '" . $db->escape($data['name']) . "'");

            echo '<br>URL сгенерирован!<br><hr>';

            $i++;

            if($i>$step){
                die('die123');
            }

        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';

}


elseif(isset($_GET['categories'])) {
$categories   = $db->query("SELECT * FROM " . DB_PREFIX . "category");
$categories   = $categories->rows;

    $step = 100;
    $i=0;
foreach($categories as $category) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category['category_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url)) {
        echo 'В категории с id=' . $category['category_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации о категории с id=' . $category['category_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . $category['category_id'] . "' LIMIT 1");
        $info = $info->rows;
        
        foreach($info as $data) {        
            echo 'Категория: ' . $data['name'] . ' | URL: ' . seoURL($data['name']);
            $data['name'] = seoURL($data['name']);
            sleep(1);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category['category_id'] . "', keyword = '" . $db->escape($data['name']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
            $i++;
            if($i>$step){
                die('die123');
            }
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';

}


elseif(isset($_GET['manufacturers'])) {
$manufacturers   = $db->query("SELECT * FROM " . DB_PREFIX . "manufacturer");
$manufacturers   = $manufacturers->rows;
    $step = 100;
    $i=0;
foreach($manufacturers as $manufacturer) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer['manufacturer_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url)) {
        echo 'В производителе с id=' . $manufacturer['manufacturer_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации о производителе с id=' . $manufacturer['manufacturer_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . $manufacturer['manufacturer_id'] . "' LIMIT 1");
        $info = $info->rows;


        
        foreach($info as $data) {

            echo 'производитель: ' . $data['name'] . ' | URL: ' . seoURL($data['name']);
            $data['name'] = seoURL($data['name']);
            sleep(1);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$manufacturer['manufacturer_id'] . "', keyword = '" . $db->escape($data['name']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
            $i++;
            if($i>$step){
                die('die123');
            }
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';

}

elseif(isset($_GET['information'])) {
$informationp    = $db->query("SELECT * FROM " . DB_PREFIX . "information_description");
$informationp    = $informationp->rows;
    $step = 100;
    $i=0;

foreach($informationp as $information) {
    
    $url = $db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE query = 'information_id=" . (int)$information['information_id'] . "'");
    $url = $url->rows;
    
    if(!empty($url)) {
        echo 'В статье с id=' . $information['information_id'] . '. URL уже существует (перезапись не производится).<br><hr>';
    } else {
        echo 'Получение информации для статьи с id=' . $information['information_id'] . '...<br>';
        $info = $db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . $information['information_id'] . "' LIMIT 1");
        $info = $info->rows;
        
        foreach($info as $data) {        
            echo 'Статья: ' . $data['title'] . ' | URL: ' . seoURL($data['title']);
            $data['title'] = seoURL($data['title']);
            sleep(1);
            $db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'information_id=" . (int)$information['information_id'] . "', keyword = '" . $db->escape($data['title']) . "'");
            echo '<br>URL сгенерирован!<br><hr>';
            $i++;
            if($i>$step){
                die('die123');
            }
        }
    }
}

echo '<h2>Все сделано!</h3><a href="javascript:history.back(1)" class="back_button">Вернуться назад</a>';
    
}

else {
    echo '<p>Сгенерировать SEO-URL для <a href="?products">товаров</a></p>';
    echo '<p>Сгенерировать SEO-URL для <a href="?categories">категорий</a></p>';
    echo '<p>Сгенерировать SEO-URL для <a href="?manufacturers">производителей</a></p>';
    echo '<p>Сгенерировать SEO-URL для <a href="?information">статей</a></p>';
}

?>
<?




    //phpinfo();





?>
</center>
</body>
</html>
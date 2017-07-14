<?php

	function createResponse($page) {
		$news=json_decode($_SESSION['json'], true);
		$pageArray=$news['news'];
		//print_r($_SESSION['json']);
		//print_r($pageArray);
		//echo $page;
		if ( $page < 1) {
			echo '{"news":[{"page1":'.json_encode($pageArray[0]['page1']).'}, {"allPage": "'.$_SESSION['allPage'].'"}]}';
		}
		else if ( $page > $_SESSION['allPage']) {
			echo '{"news":[{"page'.$_SESSION['allPage'].'":'.json_encode($pageArray[$_SESSION['allPage']-1]['page'.$_SESSION['allPage']]).'}, {"allPage": "'.$_SESSION['allPage'].'"}]}';
		} else {
			echo '{"news":[{"page'.$page.'":'.json_encode($pageArray[$page-1]['page'.$page]).'}, {"allPage": "'.$_SESSION['allPage'].'"}]}';
		}
	}

	function createJson($debug, $startDate, $endDate) {
		$current = 1;
		$counter = 0;
	    $response = '{"news":[{"page'.$current.'":[';
		
		try {
		    //Пробуем получить данные
		    if (isset($_GET['mon'])) {
		    	$data = $_SESSION['db_object']->query("SELECT id, description from `news` WHERE `date` >= '".$startDate."' AND `date` <= '".$endDate."'");
		    } 
		    else if (isset($_GET['id'])) {
		    	$data = $_SESSION['db_object']->query("SELECT title, text, author, date from `news` WHERE `id` = '".$_GET['id']."'");
		    	foreach( $data as $news_object ) {
		    		$fullNews = '{"news":[{"full":[{"id":"'.$_GET['id'].'","title":"'.$news_object['title'].'", "text":"'.$news_object['text'].'", "author":"'.$news_object['author'].'", "date":"'.$news_object['date'].'"}]}]}';
		    	}
		    	$_SESSION['mon'] = 0;
		    	$_SESSION['db_object'] = false;
		    	die($fullNews);
		    } 
		    else {
		    	$data = $_SESSION['db_object']->query("SELECT id, description from `news`");
		    }
		    //var_dump($data);
			//print_r($data);
		    //$response .= '{"data": "'.$data.'"},';
		    foreach( $data as $news_object ) {
		    	if ($counter < 7) {
					$response .= '{"id": "'.$news_object['id'].'", "description": "'.$news_object['description'].'"},';
					$counter++;
				} 
				else if ($counter == 7) {
					$response .= '{"id": "'.$news_object['id'].'", "description": "'.$news_object['description'].'"}]},';
					$counter = 0;
					$current++;
					$response .= '{"page'.$current.'":[';
				}
		    }
		} catch (PDOException $err) {
			if ($debug) {
			    $response = '{"news":[{"page1":[{"status": "'.$err->getMessage().'"}]}]}';
			} 
			else {
				$response = '{"news":[{"page1":[{"status": "error(Ошибка в запросе)"}]}]}';
			}
			echo $response;
		    die();
		}

		$response .= '{"status": "success"}]}';
		$response .= ']}';
		$_SESSION['json'] = $response;
		$_SESSION['allPage'] = $current;
		$_SESSION['db_object'] = false;
		$response = false;
		$current = false;
		createResponse(1);
	}

	function createDate() {
	    if ($_SESSION['mon'] < 10) {
		    $startDate = '2017-0'.$_SESSION['mon'].'-01 00:00:00';
		    $endDate = '2017-0'.$_SESSION['mon'].'-31 23:59:59';
		}
		else {
			$startDate = '2017-'.$_SESSION['mon'].'-01 00:00:00';
		    $endDate = '2017-'.$_SESSION['mon'].'-31 23:59:59';
		}
		createJson(false, $startDate, $endDate);
	}

	function initConnection() {

		//echo 'Создана новая сессия!';

		$host = '127.0.0.1';
		$db   = 'news_mirrorproject';
		$user = 'newsUser';
		$pass = 'asd123qwe';
		$charset = 'utf8';

		$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
		$opt = [
		    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		    PDO::ATTR_EMULATE_PREPARES   => false,
		];

    	//Подключение к БД использую модуль PDO
	    $_SESSION['db_object'] = new PDO($dsn, $user, $pass, $opt);
	}

	session_name('news');
	session_start();

	try {
		if (isset($_GET['mon'])) {
	    	(int)$_GET['mon'];
	    }
	    if (isset($_GET['page'])) {
	    	(int)$_GET['page'];
	    }
	    if (isset($_GET['id'])) {
	    	(int)$_GET['id'];
	    }
	} catch (Notice $err) {
		echo '{"news":[{"page1":[{"status": "error(Ошибка параметра)"}]}]}';
	    die();
	}

	if (!(isset($_SESSION['mon']))) {
    	$_SESSION['mon'] = 0;
    }
	//var_dump(isset($_SESSION['init']));
	//var_dump((int)$_GET['mon']);
	//var_dump(($_SESSION['mon'] != (int)$_GET['mon']));

    if (isset($_GET['mon']) && ($_SESSION['mon'] != (int)$_GET['mon'])) {
    	if((int)$_GET['mon'] > 12) {
    		$_SESSION['mon'] = 12;
    	}
    	else if ((int)$_GET['mon'] < 1) {
    		$_SESSION['mon'] = 1;
    	}
    	else {
    		$_SESSION['mon'] = (int)$_GET['mon'];
    	}
    	//var_dump($_SESSION['mon']);
    	//var_dump(($_SESSION['mon'] != (int)$_GET['mon']));
    	//var_dump((int)$_GET['mon']);
    	initConnection();
    	createDate();
    }
    else if (!(isset($_GET['mon'])) && isset($_SESSION['json']) && isset($_GET['page'])) {
    	createResponse((int)$_GET['page']);
    }
    else if (isset($_GET['id'])) {
    	initConnection();
    	createJson(true, false, false);
    }
    else if (!(isset($_GET['mon']))) {
    	initConnection();
    	createDate();
    }
    else if (isset($_SESSION['json']) && ($_SESSION['mon'] == (int)$_GET['mon']) && isset($_GET['page'])) {
    	createResponse((int)$_GET['page']);
    }

?>
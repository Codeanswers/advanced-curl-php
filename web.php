<?php

# Curl с поддержкой прокси, проверкой прокси, логом ошибок, циклом повторных запросов
function curl_parser($url, $array) {
	
	$url = trim($url);

	# Конфигурация из массива
	if (!$array['TIMEOUT']) {$array['TIMEOUT'] = 3;}
	if (!$array['CONNECTTIMEOUT']) {$array['CONNECTTIMEOUT'] = 3;}
	if ($array['PROXY'] and !$array['USE_PROXY']) {include "service/addition.proxy.php";}
	if ($array['COOKIE']) {$array['COOKIE'] = $_SERVER['DOCUMENT_ROOT'].'/service/'.$array['COOKIE'];}
	if ($array['SSL']) {$array['SSL'] = $_SERVER['DOCUMENT_ROOT'].'/service/'.$array['SSL'];}
	if (!$array['TRY']) {$array['TRY'] = 1;} # Число попыток

	# Цикл - несколько попыток получениях данных
	$i_debag = 0;
	while ($i_debag < $array['TRY']) {
		$i_debag++;
		$ch = curl_init($url); //страница данных
		curl_setopt($ch, CURLOPT_HEADER, $array['HEADER']); //включаем в вывод заголовки
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //вывод получаемых данных не в браузер, а в переменную для последующей обработки
		curl_setopt($ch, CURLOPT_REFERER, $array['REFERER']);

		curl_setopt($ch, CURLOPT_TIMEOUT, $array['TIMEOUT']);  
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $array['CONNECTTIMEOUT']);  
		
		if (!$array['USERAGENT']) {
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0');
		} else {
			curl_setopt($ch, CURLOPT_USERAGENT, $array['USERAGENT']);
		}
		
		if (!$array['SSL']) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		if ($array['SSL'] == "cacert.pem") {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_CAINFO, $array['SSL']);
		}
		if ($array['POST']) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $array['POST']);
		}
		if ($array['ENCODING']) {
			curl_setopt($ch, CURLOPT_ENCODING, $array['ENCODING']);
		}

		
		
		
		//Включаем прокси в работы
		if ($array['PROXY']) {
			
			# ----------------------- ### ---------------------- #
			if (!$array['USE_PROXY']) {
				$rand_number = mt_rand(0,count($proxy_ip - 1));
				curl_setopt($ch, CURLOPT_PROXY, $proxy_ip[$rand_number]); 
				curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port[$rand_number]); 
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_login[$rand_number].":".$proxy_password[$rand_number].""); 
				
				//Включаем проверку работы прокси
				if ($array['PROXY_LOGGING']) {
					$ipuse[$id] = str_replace(".","", $proxy_ip[$rand_number]);
					$iporig[$id] = $proxy_ip[$rand_number];
				
				}
			}
			if ($array['USE_PROXY']) {
				$use_proxy = explode(":", $array['USE_PROXY']);
				curl_setopt($ch, CURLOPT_PROXY, $use_proxy[0]); 
				curl_setopt($ch, CURLOPT_PROXYPORT, $use_proxy[1]); 
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $use_proxy[2].":".$use_proxy[3].""); 
				
				//Включаем проверку работы прокси
				if ($array['PROXY_LOGGING']) {
					$ipuse[$id] = str_replace(".","", $use_proxy[0]);
					$iporig[$id] = $use_proxy[0];
				
				}
			}
			# ---------------------- ### ----------------------- #
			
		}
	
	
	
	
		if ($array['COOKIE']) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $array['COOKIE']);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $array['COOKIE']);
		}
		
		$info_curl = curl_exec($ch);

		# Вывод ошибок
		if ($array['CURL_ERROR']) {
			print curl_error($ch);
		}
		
		$curl_error = curl_error($ch);
		curl_close($ch);
		
		# Цикл запросов повторяется, если данных нет. Иначе обрываем его
		if ($info_curl == "") {} else {break;} 
	}
	
	
	
	
	# Логгирование неполученных данных
	if ($array['CURL_NOT_INFO']) {
		if ($info_curl == "" or strlen($info_curl) < 50) {
			logi("Данные не получены - $url - " . strlen($info_curl) .". Отчет ошибки CURL: $curl_error", "log.curl_not_info");
		}
	}
 
	//Включаем проверку работы прокси
	if ($array['PROXY_LOGGING']) {
		mysqli_query("INSERT INTO `problem_proxy` (`ip`, `count`, `iporig`) VALUES ('".$ipuse[$id]."', '0', '".$iporig[$id]."')");
		if ($result[$id] == "") {
			mysqli_query("UPDATE `problem_proxy` SET count = count+'1' WHERE `ip`='".$ipuse[$id]."' ");
		}
		
	}
	
	# Отладка 
	if ($array['PRINT_INFO_CURL']) {print $info_curl;}
	
	return $info_curl;
}






# Multi_curl с поддержкой прокси, проверкой прокси
function multi_curl_parser($urls, $array) {
	
	$url = trim($url);

	# Конфигурация из массива
	if (!$array['TIMEOUT']) {$array['TIMEOUT'] = 3;}
	if (!$array['CONNECTTIMEOUT']) {$array['CONNECTTIMEOUT'] = 3;}
	if ($array['PROXY'] and !$array['USE_PROXY']) {include "service/addition.proxy.php";}
	if ($array['COOKIE']) {$array['COOKIE'] = $_SERVER['DOCUMENT_ROOT'].'/service/'.$array['COOKIE'];}
	if ($array['SSL']) {$array['SSL'] = $_SERVER['DOCUMENT_ROOT'].'/service/'.$array['SSL'];}
 
	
	$curl = [];
	$result = [];
	$ch = curl_multi_init();

	foreach ($urls as $id => $url) {
		
		//print $url; print "<br>"; 
		$curl[$id] = curl_init();

		curl_setopt($curl[$id], CURLOPT_URL, "$url");
		curl_setopt($curl[$id], CURLOPT_HEADER, $array['HEADER']);
		curl_setopt($curl[$id], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl[$id], CURLOPT_TIMEOUT, $array['TIMEOUT']);  
		curl_setopt($curl[$id], CURLOPT_CONNECTTIMEOUT, $array['CONNECTTIMEOUT']);  
		curl_setopt($curl[$id], CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl[$id], CURLOPT_REFERER, $array['REFERER']);
	
		if (!$array['SSL']) {
			curl_setopt($curl[$id], CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl[$id], CURLOPT_SSL_VERIFYHOST, false);
		}
		if ($array['SSL'] == "cacert.pem") {
			curl_setopt($curl[$id], CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($curl[$id], CURLOPT_CAINFO, $array['SSL']);
		}
		if ($array['POST']) {
			curl_setopt($curl[$id], CURLOPT_POST, true);
			curl_setopt($curl[$id], CURLOPT_POSTFIELDS, $array['POST']);
		}
		if ($array['ENCODING']) {
			curl_setopt($curl[$id], CURLOPT_ENCODING, $array['ENCODING']);
		}
		
		
		
		
		//Включаем прокси в работы
		if ($array['PROXY']) {
			
			# ----------------------- ### ---------------------- #
			if (!$array['USE_PROXY']) {
				$rand_number = mt_rand(0,count($proxy_ip - 1));
				curl_setopt($curl[$id], CURLOPT_PROXY, $proxy_ip[$rand_number]); 
				curl_setopt($curl[$id], CURLOPT_PROXYPORT, $proxy_port[$rand_number]); 
				curl_setopt($curl[$id], CURLOPT_PROXYUSERPWD, $proxy_login[$rand_number].":".$proxy_password[$rand_number].""); 
				
				//Включаем проверку работы прокси
				if ($array['PROXY_LOGGING']) {
					$ipuse[$id] = str_replace(".","", $proxy_ip[$rand_number]);
					$iporig[$id] = $proxy_ip[$rand_number];
				
				}
			}
			if ($array['USE_PROXY']) {
				$use_proxy = explode(":", $array['USE_PROXY']);
				curl_setopt($curl[$id], CURLOPT_PROXY, $use_proxy[0]); 
				curl_setopt($curl[$id], CURLOPT_PROXYPORT, $use_proxy[1]); 
				curl_setopt($curl[$id], CURLOPT_PROXYUSERPWD, $use_proxy[2].":".$use_proxy[3].""); 
				
				//Включаем проверку работы прокси
				if ($array['PROXY_LOGGING']) {
					$ipuse[$id] = str_replace(".","", $use_proxy[0]);
					$iporig[$id] = $use_proxy[0];
				
				}
			}
			# ---------------------- ### ----------------------- #
			
		}
		
		
		
		
		
		
		if (!$array['USERAGENT']) {
			curl_setopt($curl[$id], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0');
		} else {
			curl_setopt($curl[$id], CURLOPT_USERAGENT, $array['USERAGENT']);
		}
		

		
		if ($array['COOKIE']) {
			curl_setopt($curl[$id], CURLOPT_COOKIEJAR, $array['COOKIE']);
			curl_setopt($curl[$id], CURLOPT_COOKIEFILE, $array['COOKIE']);
		}
		curl_multi_add_handle($ch, $curl[$id]);
	}

	$running = null;
	do {
		usleep(25000); //sleep 0.025 seconds
		curl_multi_exec($ch, $running);
	} while($running > 0);

	foreach($curl as $id => $c) {
		$result[$id] = curl_multi_getcontent($c);  
		curl_multi_remove_handle($ch, $c);
	
	//Включаем проверку работы прокси
	if ($array['PROXY_LOGGING']) {
		mysqli_query("INSERT INTO `problem_proxy` (`ip`, `count`, `iporig`) VALUES ('".$ipuse[$id]."', '0', '".$iporig[$id]."')");
		if ($result[$id] == "") {
			mysqli_query("UPDATE `problem_proxy` SET count = count+'1' WHERE `ip`='".$ipuse[$id]."' ");
		}
		
	}
	
	}
	curl_multi_close($ch);
	return $result;

}






# Отправка запроса без задержек
function fast_request($url)
{
    $parts=($url);
    $fp = fsockopen($parts['host'],isset($parts['port'])?$parts['port']:80,$errno, $errstr, 30);
    $out = "GET ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Length: 0"."\r\n";
    $out.= "Connection: Close\r\n\r\n";

    fwrite($fp, $out);
    fclose($fp);
}

	
	
	
	
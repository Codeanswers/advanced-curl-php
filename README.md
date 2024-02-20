# advanced-curl-php
Function for working with curl php, you just need to comment or uncomment the lines

# example

## CURL
```php
$info_curl = curl_parser($url, array(
	#"PRINT_INFO_CURL" => true, # only curl_parser
	#"PROXY" => true, 
	#"PROXY_LOGGING" => true, 
	#"USE_PROXY" => '127.0.0.1:9050:login:password', 
	#"TRY" => '1', # only curl_parser
	#"TIMEOUT" => "3",
	#"CONNECTTIMEOUT" => "3",
	#"HEADER" => true, 
	#"CURL_ERROR" => true, # only curl_parser
	#"COOKIE" => 'cookies.txt',
	#"USERAGENT" => '',
	#"REFERER" => '',
	#"POST" => 'a=1&b=2',
	#"HTTPHEADER" => $headers,
	#"ENCODING" => 'utf-8',
	#"CURL_NOT_INFO" => true, # only curl_parser
	"SSL" => 'cacert.pem'
));
```

## MULTI CURL
```php
$info_curl = multi_curl_parser($urls, array(
	#"PRINT_INFO_CURL" => true, # only curl_parser
	#"PROXY" => true, 
	#"PROXY_LOGGING" => true, 
	#"USE_PROXY" => '127.0.0.1:9050:login:password', 
	#"TRY" => '1', # only curl_parser
	#"TIMEOUT" => "3",
	#"CONNECTTIMEOUT" => "3",
	#"HEADER" => true, 
	#"CURL_ERROR" => true, # only curl_parser
	#"COOKIE" => 'cookies.txt',
	#"USERAGENT" => 'Mozilla/5.0',
	#"REFERER" => '',
	#"POST" => 'a=1&b=2',
	#"HTTPHEADER" => $headers,
	#"ENCODING" => 'utf-8',
	#"CURL_NOT_INFO" => true, # only curl_parser
	"SSL" => 'cacert.pem'
));
```

## Example of passing URLs to multi_curl 
```php
$urls[] = $row['link_start_download'];
$urls_id[] = $row['id'];
```

## Sending a request without delay via fsockopen
```php
fast_request($url); 
```

## headers for array
```php
$headers = [
    'X-Apple-Tz: 0',
    'X-Apple-Store-Front: 143444,12',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: en-US,en;q=0.5',
    'Cache-Control: no-cache',
    'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
    'Host: www.example.com',
    'Referer: http://www.example.com/index.php', //Your referrer address
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
    'X-MicrosoftAjax: Delta=true'
];
```
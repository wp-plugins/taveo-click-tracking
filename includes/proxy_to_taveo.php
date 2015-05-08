<?php
/*--------------------------------------------------------------/
| proxy_to_taveo.php                                            |
| Created By: James Tyra                                        |
| Contact: mox@taveo.net                                        |
| Description: Forwards a http get request to taveo's server    |
|         so taveo can track and return correct detination,     |
/--------------------------------------------------------------*/
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

$proxied_headers = array('Set-Cookie', 'Content-Type', 'Cookie', 'Location');

$dom_api_key = '6d582131b3559c070ec8221bf61bc28b';
$dest_url = 'http://tav.so/api/l/'.$dom_api_key;


if(!function_exists('apache_request_headers')) {
// from http://www.electrictoolbox.com/php-get-headers-sent-from-browser/
    function apache_request_headers() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
if (!function_exists('http_parse_headers')) {
    function http_parse_headers($raw_headers) {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else { 
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                elseif (!$key) 
                    $headers[0] = trim($h[0]); 
            }
        }

        return $headers;
    }
}
//$request_uri = $_SERVER['REQUEST_URI'];

$final_url = $dest_url . '/' . $_GET['link'];
#echo $final_url;

$headers = array();
foreach (apache_request_headers() as $key => $value) {
    if($key == "Connection" || $key == "Host") {
        continue;
    }
    $headers[] = "$key: $value";
}
$remote  = $_SERVER['REMOTE_ADDR'];
$headers[] = "X-Real-IP: $remote";
/* Init CURL */
$curl_session = curl_init();
curl_setopt($curl_session, CURLOPT_URL, $final_url);
curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl_session, CURLOPT_AUTOREFERER, 0);
curl_setopt($curl_session, CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl_session, CURLOPT_TIMEOUT,30);
curl_setopt($curl_session, CURLOPT_FOLLOWLOCATION, 0);
//curl_setopt($curl_session, CURLOPT_SSL_VERIFYHOST, 1);
curl_setopt($curl_session, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
curl_setopt($curl_session, CURLOPT_HEADER, 1);

/* Make Actual Request */
$res = curl_exec($curl_session);
curl_close($curl_session);

//echo $res;
//die();
/* parse response */
list($res_headers, $body) = explode("\r\n\r\n", $res, 2);

$hs = http_parse_headers($res_headers);
//echo $hs;


foreach($proxied_headers as $hname)
{
    if( isset($hs[$hname]) )
    {
            if( $hname === 'Set-Cookie' ) 
            {
                header($hname.": " . $hs[$hname], false);
            }
            else
            {
                header($hname.": " . $hs[$hname]);
            }
    }
}
die($body);
?>

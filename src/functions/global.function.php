<?php
/********************************
 *  ����������������
 *  ���ߣ�heiyeluren <heiyeluren@gmail.com>
 *  ������2007-04-02 16:59
 *  �޸ģ�2008-09-02 18:28
 ********************************/

//���������ļ�
include_once("../configs/common.config.php");


/**
 * ��ȡ�ͻ����ύ�Ĳ���
 *
 * @param mix $v - ������
 * @param bool $filter - �Ƿ���˱���
 * @return mix - ��PHP�����������ҵ������򷵻ر���ֵ
 */
function _request($v, $filter=true){
	$val = "";
	if (array_key_exists($v, $_GET)){
		$val = $_GET[$v];
	} elseif (array_key_exists($v, $_POST)){
		$val = $_POST[$v];
	} elseif (array_key_exists($v, $_REQUEST)){
		$val = $_REQUEST[$v];
	}
	if ($filter && $val!="" && !get_magic_quotes_gpc()){
		return addslashes($val);
	}
	return $val;
}

/**
 * ��ȡSession����
 */
function _session($v){
	if (array_key_exists($v, $_SESSION)){
		return $_SESSION[$v];
	}
	return "";
}

/**
 * ��ȡServer����
 */
function _server($v){
	if (array_key_exists($v, $_SERVER)){
		return $_SERVER[$v];
	}
	return "";
}

/**
 * ��ȡFiles����
 */
function _files($v){
	if (array_key_exists($v, $_FILES)){
		return $_FILES[$v];
	}
	return "";
}

/**
 * ��ȡȫ�ֱ���
 */
function _globals($v){
	if (array_key_exists($v, $GLOBALS)){
		return $GLOBALS[$v];
	}
	return "";	
}


/**
 * ��ά��������
 *
 * @param $arr:����
 * @param $keys:����Ľ�ֵ
 * @param $type:����/����
 *
 * @return array
 */
function multi_array_sort($arr, $keys, $type = "asc") {
	if (!is_array($arr)) {
		return false;
	}
	$keysvalue = array();
	foreach($arr as $key => $val) {
		$keysvalue[$key] = $val[$keys];
	}
	if($type == "asc"){
		asort($keysvalue);
	}else {
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach($keysvalue as $key => $vals) {
		$keysort[$key] = $key;
	}
	$new_array = array();
	foreach($keysort as $key => $val) {
		$new_array[$key] = $arr[$val];
	}
	return $new_array;
}


/**
 * ��ȡ�����û�IP��ַ
 */
function client_ip() {
	if ($_SERVER[REMOTE_HOST]!=''){
		$ip = $_SERVER[REMOTE_ADDR];
	}elseif(getenv('HTTP_CLIENT_IP')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR')) {
		$ip = getenv('REMOTE_ADDR');
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * ����һ������ַ���
 *
 * @param $length:���������
 * @return string
 */
function random_str($length){
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 * ����һ��32λ����֮�ڵ�Hash�ַ���
 */
function hash_str($len){
	return substr(md5(uniqid(rand(), true)), 0, $len);
}

/**
 * ʮ������ת��Ϊ����������
 *
 * @param string $hex ʮ�����Ƶ�����
 * @return string �����Ƶ����ݷ���
 */
function hex2bin($hex){
	return pack("H*", $hex);
}

/**
 * �ַ������ܺ���(ֻ��ʹ�� crypt_dec �������н���)
 *
 * @paran string $str ��Ҫ���ܵ��ַ���
 * @return string ���ؼ��ܺ���ַ���
 */
function crypt_enc($str){
	list($s1, $s2) = sscanf('YWJjZGVmLT0vW118IyQlQCFhZHNmJioqKigpXyshfkBhc2Rmc2Rme306OyciLC4vPz48PFwxMjMz', "%32s%32s");
    return bin2hex($s1.base64_encode(~$str).$s2);
}

/**
 * �ַ������ܺ���(ֻ�ܽ�ʹ�� crypt_enc �������ܵ��ַ���)
 *
 * @paran string $str �Ѽ��ܵ��ַ���
 * @return string ���������ַ���
 */
function crypt_dec($str){
    return ~base64_decode(substr(pack("H*", $str), 32, -32));
}

/**
 * �����༶Ŀ¼$dir
 *
 * @param $dir:path�ľ���·��
 * @return bool
 */
function mmkdir($dir) {
	$path = array();
	$dir = preg_replace("/\/*$/", "", $dir);
	while (!is_dir($dir) && strlen(str_replace("/", "", $dir))) {
		$path[] = $dir;
		$dir = preg_replace("/\/[\w-]+$/", "", $dir);
	}
	krsort($path);
	if (sizeof($path)) {
		foreach($path as $key=>$val) {
			@mkdir($val, 0777);
		}
	}
	return true;
}

/**
 * ɾ����Ŀ¼�µ������ļ�
 *
 * @param $dir:Ŀ¼
 * @param $tag:true:ͬʱɾ����Ŀ¼��false:����ɾ����Ŀ¼�µ��ļ�����Ŀ¼
 * @return bool
 */
function m_rmdir($dir, $tag = false) {
	if ($handle = @opendir($dir)) {
		while (false !== ($file = @readdir($handle))) {
			if ($file != "." && $file != "..") {
				$ff = $dir . "/" . $file;
				if (is_file($ff)){
					@unlink($ff) ;
				}elseif (is_dir($ff)){
					m_rmdir($ff);
					@rmdir($ff);
				}
			}
		}
		closedir($handle);
	}
	if ($tag){
		@rmdir($dir);
	}
}

/**
 * ��ȡ�ٷֱ�
 *
 * @param $num:����
 * @param $sum����ĸ
 * @param $precision������С��λ��
 * @return float
 */
function  get_percent($num, $sum, $precision = 2) {
	if($num > 0) {
		$percent = sprintf("%01.{$precision}f", @round($num / $sum, $precision + 2) * 100);
	}else {
		return 0;
	}
	
	return $percent;
}


/**
 * ���ص�ǰ�ű����ļ���
 *
 * @return string
 */
function get_self () {
	return str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']);
}


/**
 * ʹ��GET��������HTTP����
 *
 * @param string $url ��Ҫ�����URL������URL�����磺http://www.example.com:8080/test.php?parm1=var1&parm2=var2
 * @param array/string $cookies �����COOKIE���ݿ��Է��͹�ȥ��������Cookie���飬Ҳ������Cookie�ַ���
 * @return mixed �ɹ�����GET���������ݣ�ʧ�ܷ���false
 */
function http_get($url, $cookies = array()) {
	/**
	 * ʹ��cURL����GET����
	 */
	if (function_exists('curl_init')){
		//��֯COOKIE����
		$header = array();
		if (!empty($cookies)){
			if (is_array($cookies)){
				$encoded = '';
				while (list($k,$v) = each($cookies)) { 
					$encoded .= ($encoded ? ";" : ""); 
					$encoded .= rawurlencode($k)."=".rawurlencode($v); 
				}
				$header = array("Cookie :". $encoded);
			} elseif (is_string($cookies)){
				if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
					$header = array($cookies);
				} else {
					$header = array("Cookie: ". $cookies);
				}
			}
		}

		//��������
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);        
		curl_close($ch);      
		if ($data)   
			return $data;
		else
			return false; 
	}
	
	/**
	 * ʹ��fsockopen����GET����
	 */
	else {
		//��֯COOKIE����
		$cookie = '';
		if (!empty($cookies)){
			if (is_array($cookies)){
				$encoded = '';
				while (list($k,$v) = each($cookies)) { 
					$encoded .= ($encoded ? ";" : ""); 
					$encoded .= rawurlencode($k)."=".rawurlencode($v); 
				}
				$cookie = $encoded;
			} elseif (is_string($cookies)){
				if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
					$cookie = substr($cookies, 7);
				} else {
					$cookie = $cookies;
				}
			}
		}

		//��������
		$url = parse_url($url); 
		if (strtolower($url['scheme']) != 'http' && $url['scheme'] != ''){
			return false;
		}
		if ( !($fp = fsockopen($url['host'], $url['port'] ? $url['port'] : 80, $errno, $errstr, 10))){
			return false;
		}
		fputs($fp, sprintf("GET %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query'])); 
		fputs($fp, "Host: $url[host]\n"); 
		fputs($fp, "User-Agent: HFHttp-Client\n");
		if ($cookie != ''){
			fputs($fp, "Cookie: $cookie\n\n"); 
		}
		fputs($fp, "Connection: close\n\n"); 
		fputs($fp, "$post \n");
		$ret = '';
		while (!feof($fp)) { 
			$ret .= fgets($fp, 1024); 
		} 
		fclose($fp);

		return $ret;		
	}
}


/**
 * ʹ��POST��������HTTP����
 *
 * @param string $url ��Ҫ�����URL������URL�����磺http://www.example.com:8080/test.php?parm1=var1&parm2=var2
 * @param array $vars ��ҪPOST�ύ�ı�������
 * @param array/string $cookies �����COOKIE���ݿ��Է��͹�ȥ��������Cookie���飬Ҳ������Cookie�ַ���
 * @return mixed �ɹ�����GET���������ݣ�ʧ�ܷ���false
 */

function http_post($url, $vars = array(), $cookies = array()) {
	/**
	 * ʹ��cURL����POST����
	 */
	if (function_exists('curl_init')){
		//��֯COOKIE����
		$header = array();
		if (!empty($cookies)){
			if (is_array($cookies)){
				$encoded = '';
				while (list($k,$v) = each($cookies)) { 
					$encoded .= ($encoded ? ";" : ""); 
					$encoded .= rawurlencode($k)."=".rawurlencode($v); 
				}
				$header = array("Cookie :". $encoded);
			} elseif (is_string($cookies)){
				if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
					$header = array($cookies);
				} else {
					$header = array("Cookie: ". $cookies);
				}
			}
		}

		//ִ��POST����
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1 );     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);   
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);      
		$data = curl_exec($ch);        
		curl_close($ch);      
		if ($data)   
			return $data;     
		else
			return false;
	}

	/**
	 * ʹ��fsockopen����POST����
	 */
	else {
		//��֯COOKIE����
		$cookie = '';
		if (!empty($cookies)){
			if (is_array($cookies)){
				$encoded = '';
				while (list($k,$v) = each($cookies)) { 
					$encoded .= ($encoded ? ";" : ""); 
					$encoded .= rawurlencode($k)."=".rawurlencode($v); 
				}
				$cookie = $encoded;
			} elseif (is_string($cookies)){
				if (strtolower(substr($cookies, 0, 7)) == 'cookie:'){
					$cookie = substr($cookies, 7);
				} else {
					$cookie = $cookies;
				}
			}
		}

		//��֯POST����
		$post = '';
		if (!empty($vars)){
			if (is_array($vars)){
				$encoded = '';
				while (list($k,$v) = each($vars)) { 
					$encoded .= ($encoded ? "&" : ""); 
					$encoded .= rawurlencode($k)."=".rawurlencode($v); 
				}
				$post = $encoded;
			} else {
				$post = $vars;
			}
		}


		//��������
		$url = parse_url($url); 
		if (strtolower($url['scheme']) != 'http' && $url['scheme'] != ''){
			return false;
		}
		if ( !($fp = fsockopen($url['host'], $url['port'] ? $url['port'] : 80, $errno, $errstr, 10))){
			return false;
		}
		fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query'])); 
		fputs($fp, "Host: $url[host]\n"); 
		fputs($fp, "User-Agent: HFHttp-Client\n");
		if ($cookie != ''){
			fputs($fp, "Cookie: $cookie\n\n"); 
		}
		fputs($fp, "Content-type: application/x-www-form-urlencoded\n"); 
		fputs($fp, "Content-length: " . strlen($post) . "\n"); 
		fputs($fp, "Connection: close\n\n"); 
		fputs($fp, "$post \n");
		$ret = '';
		while (!feof($fp)) { 
			$ret .= fgets($fp, 1024); 
		} 
		fclose($fp);

		return $ret;	
	}
} 



/**
 * ͨ��IP��ȡ��ʵ��ַ
 *
 * @param string $ip ip��ַ
 * @return string ���ص�ַ�ַ���
 */
function get_ip_location($ip) {
	//���ip�����ļ��Ƿ����
	if (file_exists(_QQ_IPADDR_DAT)){
		return false;
	}
    //IP�����ļ�·��
    $dat_path = _QQ_IPADDR_DAT;

    //���IP��ַ
    if(!preg_match("/^d{1,3}.d{1,3}.d{1,3}.d{1,3}$/", $ip)) {
        return 'IP Address Error';
    }
    //��IP�����ļ�
    if(!$fd = @fopen($dat_path, 'rb')){
        return 'IP date file not exists or access denied';
    }

    //�ֽ�IP�������㣬�ó�������
    $ip = explode('.', $ip);
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

    //��ȡIP����������ʼ�ͽ���λ��
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $ipbegin = implode('', unpack('L', $DataBegin));
    if($ipbegin < 0) $ipbegin += pow(2, 32);
    $ipend = implode('', unpack('L', $DataEnd));
    if($ipend < 0) $ipend += pow(2, 32);
    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
    
    $BeginNum = 0;
    $EndNum = $ipAllNum;

    //ʹ�ö��ֲ��ҷ���������¼������ƥ���IP��¼
    while($ip1num>$ipNum || $ip2num<$ipNum) {
        $Middle= intval(($EndNum + $BeginNum) / 2);

        //ƫ��ָ�뵽����λ�ö�ȡ4���ֽ�
        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if(strlen($ipData1) < 4) {
            fclose($fd);
            return 'System Error';
        }
        //��ȡ����������ת���ɳ����Σ���������Ǹ��������2��32����
        $ip1num = implode('', unpack('L', $ipData1));
        if($ip1num < 0) $ip1num += pow(2, 32);
        
        //��ȡ�ĳ���������������IP��ַ���޸Ľ���λ�ý�����һ��ѭ��
        if($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }
        
        //ȡ����һ��������ȡ��һ������
        $DataSeek = fread($fd, 3);
        if(strlen($DataSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
        fseek($fd, $DataSeek);
        $ipData2 = fread($fd, 4);
        if(strlen($ipData2) < 4) {
            fclose($fd);
            return 'System Error';
        }
        $ip2num = implode('', unpack('L', $ipData2));
        if($ip2num < 0) $ip2num += pow(2, 32);

        //û�ҵ���ʾδ֪
        if($ip2num < $ipNum) {
            if($Middle == $BeginNum) {
                fclose($fd);
                return 'Unknown';
            }
            $BeginNum = $Middle;
        }
    }

    $ipFlag = fread($fd, 1);
    if($ipFlag == chr(1)) {
        $ipSeek = fread($fd, 3);
        if(strlen($ipSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
        fseek($fd, $ipSeek);
        $ipFlag = fread($fd, 1);
    }

    if($ipFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if(strlen($AddrSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }

        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr2 .= $char;

        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
        fseek($fd, $AddrSeek);

        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;

        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while(($char = fread($fd, 1)) != chr(0)){
            $ipAddr2 .= $char;
        }
    }
    fclose($fd);

    //�������Ӧ���滻�����󷵻ؽ��
    if(preg_match('/http/i', $ipAddr2)) {
        $ipAddr2 = '';
    }
    $ipaddr = "$ipAddr1 $ipAddr2";
    $ipaddr = preg_replace('/CZ88.NET/is', '', $ipaddr);
    $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
    $ipaddr = preg_replace('/s*$/is', '', $ipaddr);
    if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
        $ipaddr = 'Unknown';
    }

    return $ipaddr;
}

/**
 * ����Ƿ�����зǷ�����
 *
 * @param string $content - ��Ҫ���м�������
 * @param string $filter_type - ��Ҫ���˵����ͣ����˵��ϸ�̶Ȳ�һ����TΪ���⼶���ˣ�BΪ���ݹ��ˣ�UΪ�û�������
 * @return bool ��������а����Ƿ������򷵻�true�����򷵻�false
 */
function check_illegal_word($content, $filterType = 'B'){
	if (!file_exists(__FILTER_WORD_FILE)){
		return false;
	}
	include_once(__FILTER_WORD_FILE);

	switch($filterType){
		case 'T':
			$verify = preg_match($filter_title, $content);
			break;
		case 'B':
			$verify = preg_match($filter_body, $content);
			break;
		case 'U':
			$verify = preg_match($filter_username, $content);
			break;
		default:
			$verify = preg_match($filter_body, $content);
			break;
	}
	return $verify;
}

/**
 * �滻/���������еķǷ��ִ�
 *
 * @param string $content - ��Ҫ���й��˵�����
 * @param string $filter_type - ��Ҫ���˵����ͣ����˵��ϸ�̶Ȳ�һ����TΪ���⼶���ˣ�BΪ���ݹ��ˣ�UΪ�û�������
 * @paran string $replace ��Ҫ�滻Ϊʲô�ַ���ȱʡ�滻Ϊ*
 * @return bool �����滻֮����ı�����
 */
function filter_illegal_word($content,  $filterType = 'B', $replace = '*'){
	if (!file_exists(__FILTER_WORD_FILE)){
		return $content;
	}
	include_once(__FILTER_WORD_FILE);

	switch($filterType){
		case 'T':
			$result = preg_replace($filter_title, $replace, $content);
			break;
		case 'B':
			$result = preg_replace($filter_body, $replace, $content);
			break;
		case 'U':
			$result = preg_replace($filter_username, $replace, $content);
			break;
		default:
			$result = preg_replace($filter_body, $replace, $content);
			break;
	}
	return $result;
}



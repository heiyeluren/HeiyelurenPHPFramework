<?php
/********************************
 *  描述：公共函数库
 *  作者：heiyeluren <heiyeluren@gmail.com>
 *  创建：2007-04-02 16:59
 *  修改：2008-09-02 18:28
 ********************************/

//包含配置文件
include_once("../configs/common.config.php");


/**
 * 获取客户端提交的参数
 *
 * @param mix $v - 变量名
 * @param bool $filter - 是否过滤变量
 * @return mix - 在PHP内置数组中找到变量则返回变量值
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
 * 获取Session变量
 */
function _session($v){
	if (array_key_exists($v, $_SESSION)){
		return $_SESSION[$v];
	}
	return "";
}

/**
 * 获取Server变量
 */
function _server($v){
	if (array_key_exists($v, $_SERVER)){
		return $_SERVER[$v];
	}
	return "";
}

/**
 * 获取Files变量
 */
function _files($v){
	if (array_key_exists($v, $_FILES)){
		return $_FILES[$v];
	}
	return "";
}

/**
 * 获取全局变量
 */
function _globals($v){
	if (array_key_exists($v, $GLOBALS)){
		return $GLOBALS[$v];
	}
	return "";	
}


/**
 * 二维数组排序
 *
 * @param $arr:数据
 * @param $keys:排序的健值
 * @param $type:升序/降序
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
 * 获取访问用户IP地址
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
 * 产生一个随机字符串
 *
 * @param $length:随机数长度
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
 * 生成一个32位长度之内的Hash字符串
 */
function hash_str($len){
	return substr(md5(uniqid(rand(), true)), 0, $len);
}

/**
 * 十六进制转换为二进制内容
 *
 * @param string $hex 十六进制的内容
 * @return string 二进制的内容返回
 */
function hex2bin($hex){
	return pack("H*", $hex);
}

/**
 * 字符串加密函数(只能使用 crypt_dec 函数进行解密)
 *
 * @paran string $str 想要加密的字符串
 * @return string 返回加密后的字符串
 */
function crypt_enc($str){
	list($s1, $s2) = sscanf('YWJjZGVmLT0vW118IyQlQCFhZHNmJioqKigpXyshfkBhc2Rmc2Rme306OyciLC4vPz48PFwxMjMz', "%32s%32s");
    return bin2hex($s1.base64_encode(~$str).$s2);
}

/**
 * 字符串解密函数(只能解使用 crypt_enc 函数加密的字符串)
 *
 * @paran string $str 已加密的字符串
 * @return string 返回明文字符串
 */
function crypt_dec($str){
    return ~base64_decode(substr(pack("H*", $str), 32, -32));
}

/**
 * 创建多级目录$dir
 *
 * @param $dir:path的绝对路径
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
 * 删除该目录下的所有文件
 *
 * @param $dir:目录
 * @param $tag:true:同时删除该目录，false:仅仅删除该目录下的文件及子目录
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
 * 获取百分比
 *
 * @param $num:分子
 * @param $sum：分母
 * @param $precision：保留小数位数
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
 * 返回当前脚本的文件名
 *
 * @return string
 */
function get_self () {
	return str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']);
}


/**
 * 使用GET方法发送HTTP请求
 *
 * @param string $url 需要请求的URL，完整URL，例如：http://www.example.com:8080/test.php?parm1=var1&parm2=var2
 * @param array/string $cookies 如果有COOKIE数据可以发送过去，可以是Cookie数组，也可以是Cookie字符串
 * @return mixed 成功返回GET回来的数据，失败返回false
 */
function http_get($url, $cookies = array()) {
	/**
	 * 使用cURL处理GET请求
	 */
	if (function_exists('curl_init')){
		//组织COOKIE数据
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

		//处理请求
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
	 * 使用fsockopen处理GET请求
	 */
	else {
		//组织COOKIE数据
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

		//处理请求
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
 * 使用POST方法发送HTTP请求
 *
 * @param string $url 需要请求的URL，完整URL，例如：http://www.example.com:8080/test.php?parm1=var1&parm2=var2
 * @param array $vars 需要POST提交的变量数组
 * @param array/string $cookies 如果有COOKIE数据可以发送过去，可以是Cookie数组，也可以是Cookie字符串
 * @return mixed 成功返回GET回来的数据，失败返回false
 */

function http_post($url, $vars = array(), $cookies = array()) {
	/**
	 * 使用cURL处理POST请求
	 */
	if (function_exists('curl_init')){
		//组织COOKIE数据
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

		//执行POST请求
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
	 * 使用fsockopen处理POST请求
	 */
	else {
		//组织COOKIE数据
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

		//组织POST数据
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


		//处理请求
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
 * 通过IP获取真实地址
 *
 * @param string $ip ip地址
 * @return string 返回地址字符串
 */
function get_ip_location($ip) {
	//检测ip数据文件是否存在
	if (file_exists(_QQ_IPADDR_DAT)){
		return false;
	}
    //IP数据文件路径
    $dat_path = _QQ_IPADDR_DAT;

    //检查IP地址
    if(!preg_match("/^d{1,3}.d{1,3}.d{1,3}.d{1,3}$/", $ip)) {
        return 'IP Address Error';
    }
    //打开IP数据文件
    if(!$fd = @fopen($dat_path, 'rb')){
        return 'IP date file not exists or access denied';
    }

    //分解IP进行运算，得出整形数
    $ip = explode('.', $ip);
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

    //获取IP数据索引开始和结束位置
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $ipbegin = implode('', unpack('L', $DataBegin));
    if($ipbegin < 0) $ipbegin += pow(2, 32);
    $ipend = implode('', unpack('L', $DataEnd));
    if($ipend < 0) $ipend += pow(2, 32);
    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
    
    $BeginNum = 0;
    $EndNum = $ipAllNum;

    //使用二分查找法从索引记录中搜索匹配的IP记录
    while($ip1num>$ipNum || $ip2num<$ipNum) {
        $Middle= intval(($EndNum + $BeginNum) / 2);

        //偏移指针到索引位置读取4个字节
        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if(strlen($ipData1) < 4) {
            fclose($fd);
            return 'System Error';
        }
        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
        $ip1num = implode('', unpack('L', $ipData1));
        if($ip1num < 0) $ip1num += pow(2, 32);
        
        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
        if($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }
        
        //取完上一个索引后取下一个索引
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

        //没找到提示未知
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

    //最后做相应的替换操作后返回结果
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
 * 检查是否包含有非法内容
 *
 * @param string $content - 需要进行检查的内容
 * @param string $filter_type - 需要过滤的类型，过滤的严格程度不一样，T为标题级过滤，B为内容过滤，U为用户名过滤
 * @return bool 如果发现有包含非法内容则返回true，否则返回false
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
 * 替换/过滤内容中的非法字词
 *
 * @param string $content - 需要进行过滤的内容
 * @param string $filter_type - 需要过滤的类型，过滤的严格程度不一样，T为标题级过滤，B为内容过滤，U为用户名过滤
 * @paran string $replace 需要替换为什么字符，缺省替换为*
 * @return bool 返回替换之后的文本内容
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



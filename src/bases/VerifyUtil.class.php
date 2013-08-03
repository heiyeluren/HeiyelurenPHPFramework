<?php
/*******************************************
 *  描述：变量检查和过滤类
 *  作者：heiyeluren
 *  创建：2007-04-02 16:59
 *  修改：2007-04-05 09:30
 *******************************************/


define("__VER_ERROR_NO", -1);

//包含基础异常处理类
include_once("Exception.class.php");
include_once("Socket.class.php");

/**
 * 数据校验检查过滤类
 *
 * 包含基本的数据完整性、正确性、安全性、合法性检查的函数接口，一般提供直接调用
 */
class VerifyUtil extends ExceptionClass
{

	/**
	 * 检测一个变量是否为空
	 */
	function isEmpty($value){
		return (empty($value) || $value=="");
	}

	/**
	 * 检测一个文件是否存在（可以是本地文件或者是HTTP协议的文件）
	 *
	 * @param string $inputPath 文件路径（可以是一个URL或者是本地文件路径）
	 * @return mixed 返回false文件不存在，返回true文件存在，返回对象说明有错误
	 */
	function fileIsExists($inputPath){
		//检测输入
		 $inputPath = trim($inputPath);
		 if (empty($inputPath)) 
			return false;

		//如果是URL判断URL文件是否存在
		if (self::isUrl($inputPath)){
			$urlArray = parse_url($inputPath);
			if (!is_array($urlArray) || empty($urlArray)){ return false; }

			$host = $urlArray['host'];
			$path = $urlArray['path'] ."?". $urlArray['query'];
			$port = isset($urlArray['port']) ? $urlArray['port'] : 80;

			$socket =& new Socket($host, $port);
			if ($socket->isError($obj = $socket->connect())){
				die($obj->getMessage());
				return self::raiseError($obj->getMessage(), __VER_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			if ($socket->isError($obj = $socket->write("GET ". $path ." HTTP/1.1\r\nHost: ". $host ."\r\nConnection: Close\r\n\r\n"))){
				return self::raiseError($obj->getMessage(), __VER_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			if ($socket->isError($httpHeader = $socket->readLine())){
				return self::raiseError($httpHeader->getMessage(), __VER_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			if (!preg_match("/200/", $httpHeader)){
				return false;
			}
			return true;
		}

		//判断普通文件是否存在
		return file_exists($inputPath);
	}

	/**
	 * 检测一个用户名的合法性
	 * 
	 * @param string $str 需要检查的用户名字符串
	 * @param int $chkType 要求用户名的类型，
	 * @		  1为英文、数字、下划线，2为任意可见字符，3为中文(GBK)、英文、数字、下划线，4为中文(UTF8)、英文、数字，缺省为1
	 * @return bool 返回检查结果，合法为true，非法为false
	 */
	function chkUserName($str, $chkType=1){
		switch($chkType){
			case 1:
				$result = preg_match("/^[a-zA-Z0-9_]+$/i", $str);
				break;
			case 2:
				$result = preg_match("/^[\w\d]+$/i", $str);
				break;
			case 3:
				$result = preg_match("/^[_a-zA-Z0-9\0x80-\0xff]+$/i", $str);
				break;
			case 4:
				$result = preg_match("/^[_a-zA-Z0-9\u4e00-\u9fa5]+$/i", $str);
				break;
			default:
				$result = preg_match("/^[a-zA-Z0-9_]+$/i", $str);
				break;
		}
		return $result;
	}
	

	/**
	 * email地址合法性检测
	 */
	function isEmail($value){
		return preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $value);
	}

	/**
	 * URL地址合法性检测
	 */
	function isUrl($value){
		return preg_match("/^http:\/\/[\w]+\.[\w]+[\S]*/", $value);
	}

	/**
	 * 是否是一个合法域名
	 */
	function isDomainName($str){
		return preg_match("/^[a-z0-9]([a-z0-9-]+\.){1,4}[a-z]{2,5}$/i", $str);
	}

	/**
	 * 检测IP地址是否合法
	 */
	function isIpAddr($ip){
		return preg_match("/^[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}$/", $ip);
	}

	/**
	 * 邮编合法性检测
	 */
	function isPostalCode($value){
		return (is_numeric($value) && (strlen($value)==6));
	}

	/**
	 * 电话(传真)号码合法性检测
	 */
	function isPhone($value){
		return preg_match("/^(\d){2,4}[\-]?(\d+){6,9}$/", $value);
	}

	/**
	 * 手机号码合法性检查
	 */
	 function isMobile($str){
		return preg_match("/^(13|15)\d{9}$/i", $str);
	 }

	/**
	 * 身份证号码合法性检测
	 */
	function isIdCard($value){
		return preg_match("/^(\d{15}|\d{17}[\dx])$/i", $value);
	}

	/**
	* 严格的身份证号码合法性检测(按照身份证生成算法进行检查)
	*/
	function chkIdCard($value){
		if (strlen($value) != 18){
			return false;
		}
		$wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); 
		$ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
		$value = strtoupper($value);
		$sigma = '';
		for ($i = 0; $i < 17; $i++) {
			$sigma += ((int) $value{$i}) * $wi[$i]; 
		} 
		$parity_bit = $ai[($sigma % 11)];
		if ($parity_bit != substr($value, -1)){
			return false;
		}
		return true;
	}

	/**
	 * 检测是否包含特殊字符
	 */
	function chkSpecialWord($value){
		return preg_match('/>|<|,|\[|\]|\{|\}|\?|\/|\+|=|\||\'|\\|\"|:|;|\~|\!|\@|\#|\*|\$|\%|\^|\&|\(|\)|`/i', $value);
	}

	/**
	 * 过滤特殊字符
	 */
	function filterSpecialWord($value){
		return preg_replace('/>|<|,|\[|\]|\{|\}|\?|\/|\+|=|\||\'|\\|\"|:|;|\~|\!|\@|\#|\*|\$|\%|\^|\&|\(|\)|`/i', "", $value);
	}

	/**
	 * 过滤SQL注入攻击字符串
	 */
	function filterSqlInject($str){
		if (!get_magic_quotes_gpc()){
			return addslashes($str);
		}
		return $str;		
	}

	/**
	 * 过滤HTML标签
	 *
	 * @param string text - 传递进去的文本内容
	 * @param bool $strict - 是否严格过滤（严格过滤将把所有已知HTML标签开头的内容过滤掉）
	 * @return string 返回替换后的结果
	 */
	function stripHtmlTag($text, $strict=false){
		$text = strip_tags($text);
		if (!$strict){
			return $text;
		}
		$html_tag = "/<[\/|!]?(html|head|body|div|span|DOCTYPE|title|link|meta|style|p|h1|h2|h3|h4|h5|h6|strong|em|abbr|acronym|address|bdo|blockquote|cite|q|code|ins|del|dfn|kbd|pre|samp|var|br|a|base|img|area|map|object|param|ul|ol|li|dl|dt|dd|table|tr|td|th|tbody|thead|tfoot|col|colgroup|caption|form|input|textarea|select|option|optgroup|button|label|fieldset|legend|script|noscript|b|i|tt|sub|sup|big|small|hr)[^>]*>/is";
		return preg_replace($html_tag, "", $text);
	}

	/**
	 * 转换HTML的专有字符
	 */
	 function filterHtmlWord($text){
		if (function_exists('htmlspecialchars')){
			return htmlspecialchars($text);
		}
		$search = array("&", '"', "'", "<", ">");
		$replace = array("&amp;", "&quot;", "&#039;", "&lt;", "&gt;");
		return str_replace($search, $replace, $text);
	 }

	 /**
	  * 剔除JavaScript、CSS、Object、Iframe
	  */
	 function filterScript($text){
		$text = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","&111n\\2",$text);
		$text = preg_replace ("/<style.+<\/style>/iesU", '', $text);
		$text = preg_replace ("/<script.+<\/script>/iesU", '', $text);
		$text = preg_replace ("/<iframe.+<\/iframe>/iesU", '', $text);
		$text = preg_replace ("/<object.+<\/object>/iesU", '', $text);
		return $text;
	 }

	/**
	 * 过滤JAVASCRIPT不安全情况
	 */
	function escapeScript($string){
		$string = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","&111n\\2",$string);
		$string = preg_replace("/<script(.*?)>(.*?)<\/script>/si","",$string);
		$string = preg_replace("/<iframe(.*?)>(.*?)<\/iframe>/si","",$string);
		$string = preg_replace ("/<object.+<\/object>/iesU", '', $string);
		return $string;
	}



}



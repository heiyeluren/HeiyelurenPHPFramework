<?php
/*******************************************
 *  �������������͹�����
 *  ���ߣ�heiyeluren
 *  ������2007-04-02 16:59
 *  �޸ģ�2007-04-05 09:30
 *******************************************/


define("__VER_ERROR_NO", -1);

//���������쳣������
include_once("Exception.class.php");
include_once("Socket.class.php");

/**
 * ����У���������
 *
 * �������������������ԡ���ȷ�ԡ���ȫ�ԡ��Ϸ��Լ��ĺ����ӿڣ�һ���ṩֱ�ӵ���
 */
class VerifyUtil extends ExceptionClass
{

	/**
	 * ���һ�������Ƿ�Ϊ��
	 */
	function isEmpty($value){
		return (empty($value) || $value=="");
	}

	/**
	 * ���һ���ļ��Ƿ���ڣ������Ǳ����ļ�������HTTPЭ����ļ���
	 *
	 * @param string $inputPath �ļ�·����������һ��URL�����Ǳ����ļ�·����
	 * @return mixed ����false�ļ������ڣ�����true�ļ����ڣ����ض���˵���д���
	 */
	function fileIsExists($inputPath){
		//�������
		 $inputPath = trim($inputPath);
		 if (empty($inputPath)) 
			return false;

		//�����URL�ж�URL�ļ��Ƿ����
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

		//�ж���ͨ�ļ��Ƿ����
		return file_exists($inputPath);
	}

	/**
	 * ���һ���û����ĺϷ���
	 * 
	 * @param string $str ��Ҫ�����û����ַ���
	 * @param int $chkType Ҫ���û��������ͣ�
	 * @		  1ΪӢ�ġ����֡��»��ߣ�2Ϊ����ɼ��ַ���3Ϊ����(GBK)��Ӣ�ġ����֡��»��ߣ�4Ϊ����(UTF8)��Ӣ�ġ����֣�ȱʡΪ1
	 * @return bool ���ؼ�������Ϸ�Ϊtrue���Ƿ�Ϊfalse
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
	 * email��ַ�Ϸ��Լ��
	 */
	function isEmail($value){
		return preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $value);
	}

	/**
	 * URL��ַ�Ϸ��Լ��
	 */
	function isUrl($value){
		return preg_match("/^http:\/\/[\w]+\.[\w]+[\S]*/", $value);
	}

	/**
	 * �Ƿ���һ���Ϸ�����
	 */
	function isDomainName($str){
		return preg_match("/^[a-z0-9]([a-z0-9-]+\.){1,4}[a-z]{2,5}$/i", $str);
	}

	/**
	 * ���IP��ַ�Ƿ�Ϸ�
	 */
	function isIpAddr($ip){
		return preg_match("/^[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}$/", $ip);
	}

	/**
	 * �ʱ�Ϸ��Լ��
	 */
	function isPostalCode($value){
		return (is_numeric($value) && (strlen($value)==6));
	}

	/**
	 * �绰(����)����Ϸ��Լ��
	 */
	function isPhone($value){
		return preg_match("/^(\d){2,4}[\-]?(\d+){6,9}$/", $value);
	}

	/**
	 * �ֻ�����Ϸ��Լ��
	 */
	 function isMobile($str){
		return preg_match("/^(13|15)\d{9}$/i", $str);
	 }

	/**
	 * ���֤����Ϸ��Լ��
	 */
	function isIdCard($value){
		return preg_match("/^(\d{15}|\d{17}[\dx])$/i", $value);
	}

	/**
	* �ϸ�����֤����Ϸ��Լ��(�������֤�����㷨���м��)
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
	 * ����Ƿ���������ַ�
	 */
	function chkSpecialWord($value){
		return preg_match('/>|<|,|\[|\]|\{|\}|\?|\/|\+|=|\||\'|\\|\"|:|;|\~|\!|\@|\#|\*|\$|\%|\^|\&|\(|\)|`/i', $value);
	}

	/**
	 * ���������ַ�
	 */
	function filterSpecialWord($value){
		return preg_replace('/>|<|,|\[|\]|\{|\}|\?|\/|\+|=|\||\'|\\|\"|:|;|\~|\!|\@|\#|\*|\$|\%|\^|\&|\(|\)|`/i', "", $value);
	}

	/**
	 * ����SQLע�빥���ַ���
	 */
	function filterSqlInject($str){
		if (!get_magic_quotes_gpc()){
			return addslashes($str);
		}
		return $str;		
	}

	/**
	 * ����HTML��ǩ
	 *
	 * @param string text - ���ݽ�ȥ���ı�����
	 * @param bool $strict - �Ƿ��ϸ���ˣ��ϸ���˽���������֪HTML��ǩ��ͷ�����ݹ��˵���
	 * @return string �����滻��Ľ��
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
	 * ת��HTML��ר���ַ�
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
	  * �޳�JavaScript��CSS��Object��Iframe
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
	 * ����JAVASCRIPT����ȫ���
	 */
	function escapeScript($string){
		$string = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","&111n\\2",$string);
		$string = preg_replace("/<script(.*?)>(.*?)<\/script>/si","",$string);
		$string = preg_replace("/<iframe(.*?)>(.*?)<\/iframe>/si","",$string);
		$string = preg_replace ("/<object.+<\/object>/iesU", '', $string);
		return $string;
	}



}



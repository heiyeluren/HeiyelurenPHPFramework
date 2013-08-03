<?php
/*******************************************
 *  ���������ֽ��ַ���������
 *  ���ߣ�heiyeluren
 *  ������2007-04-03 12:06
 *  �޸ģ�2007-04-09 17:14
 *******************************************/

//���������쳣������
include_once("Exception.class.php");

/**
 * ���ֽ��ַ��������ࣨGBK/GB2312��UTF8/Unicode��
 *
 * �������ֽ��ַ�������Ļ���������������ֽڵĲ���
 */
class MultiString extends ExceptionClass
{
	/**
	 * �ж���������û������(GBK)
	 */
	function GBIsChinese($s){
		return preg_match('/[\x80-\xff]./', $s);
	}

	/**
	 * ��ȡ�ַ�������(GBK)
	 */
	function GBStrlen($str){
		$count = 0;
		for($i=0; $i<strlen($str); $i++){
			$s = substr($str, $i, 1);
			if (preg_match("/[\x80-\xff]/", $s)) ++$i;
			++$count;
		}
		return $count;
	}

	/**
	 * ��ȡ�ַ����Ӵ�(GBK)
	 *
	 * @param string $str ԭʼ�ַ���
	 * @param int $len ��Ҫ��ȡ�ַ����ĳ���
	 * @return string ���ؽ�ȡ�����ַ���
	 */
	function GBSubstr($str, $len){
		$count = 0;
		for($i=0; $i<strlen($str); $i++){
			if($count == $len) break;
			if(preg_match("/[\x80-\xff]/", substr($str, $i, 1))) ++$i;
			++$count;        
		}
		return substr($str, 0, $i);
	}

	/**
	 * ��ȡ�ַ����Ӵ�����2��GB)
	 * 
	 * @param string $src Դ�ַ���
	 * @param int $start ��ʼ��ȡ��λ��
	 * @param int $length ��Ҫ��ȡ�ַ����ĳ���
	 * @return string ���ؽ�ȡ���ַ���
	 */

	function GBSubstr2($src, $start=0, $length=0){
		$suffix="";
		$len = strlen($src);
		if ( $len <= $length ) return $src; 
		
		$cut_length = 0;
		for( $idx = 0; $idx<$length; $idx++){ 
			$char_value = ord($src[$idx]); 
			if ( $char_value < 0x80 || ( $char_value & 0x40 ) )
				$cut_length++;
			else
				$cut_length = $cut_length + 3; 
		} 
		$curstr = substr($src, 0, $cut_length) ;
		preg_match('/^([\x00-\x7f]|.{3})*/', $curstr, $result);
		return  $result[0];
	}


	/**
	 * ͳ���ַ�������(UTF-8)
	 */
	function utfStrlen($str) {
		$count = 0;
		for($i=0; $i<strlen($str); $i++){
			$value = ord($str[$i]);
			if($value > 127) {
				$count++;
				if($value>=192 && $value<=223) $i++;
				elseif($value>=224 && $value<=239) $i = $i + 2;
				elseif($value>=240 && $value<=247) $i = $i + 3;
				else return self::raiseError("\"$str\" Not a UTF-8 compatible string", 0, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			$count++;
		}
		return $count;
	}


	/**
	 * ��ȡ�ַ���(UTF-8)
	 *
	 * @param string $str ԭʼ�ַ���
	 * @param $position ��ʼ��ȡλ��
	 * @param $length ��Ҫ��ȡ��ƫ����
	 * @return string ��ȡ���ַ���
	 */
	function utfSubstr($str, $position, $length){
		$startPos = strlen($str);
		$startByte = 0;
		$endPos = strlen($str);
		$count = 0;
		for($i=0; $i<strlen($str); $i++){
			if($count>=$position && $startPos>$i){
				$startPos = $i;
				$startByte = $count;
			}
			if(($count-$startByte) >= $length) {
				$endPos = $i;
				break;
			}    
			$value = ord($str[$i]);
			if($value > 127){
				$count++;
				if($value>=192 && $value<=223) $i++;
				elseif($value>=224 && $value<=239) $i = $i + 2;
				elseif($value>=240 && $value<=247) $i = $i + 3;
				else return self::raiseError("\"$str\" Not a UTF-8 compatible string", 0, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			$count++;

		}
		return substr($str, $startPos, $endPos-$startPos);
	}


	/**
	 * �����ַ����жϳ��ȣ�֧��GB2312/GBK/UTF-8/BIG5��
	 *
	 * @param string $str Ҫȡ���ȵ��ִ�
	 * @param string $charset �ַ������ַ����������� utf-8|gb2312|gbk|big5 ����
	 * @return int �����ַ����ĳ���
	 */
	function CStrlen($str, $charset="gbk"){
		if(function_exists("mb_strlen")){
			//return mb_strlen($str, $charset);
		}
		$re['utf-8']	= "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312']	= "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']		= "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']		= "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		
		preg_match_all($re[$charset], $str, $match);
		return count($match[0]);
	}

	/**
	 * �����ַ�����ȡ��֧��GB2312/GBK/UTF-8/BIG5��
	 *
	 * @param string $str Ҫ��ȡ���ִ�
	 * @param int $start ��ȡ��ʼλ��
	 * @param int $length ��ȡ����
	 * @param string $charset �ַ������ַ����������� utf-8|gb2312|gbk|big5 ����
	 * @param bool $suffix �Ƿ��β׺
	 * @return string ���ؽ����ַ����Ľ��
	 */
	function CSubstr($str, $start=0, $length, $charset="gbk", $suffix=false){
		if(function_exists("mb_substr")){
			return mb_substr($str, $start, $length, $charset);
		}
		$re['utf-8']	= "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312']	= "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']		= "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']		= "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		
		preg_match_all($re[$charset], $str, $match);
			$slice = join("", array_slice($match[0], $start, $length));

		if($suffix) {
			return $slice ."��";
		}
		return $slice;
	}

	/**
	 * ��ȡȫ�ǺͰ�ǻ�ϵ��ַ����Ա�������
	 *
	 * @param $str_cut:��Ҫ�ضϵ��ַ��� 
	 * @param $length:�����ַ�����ʾ����󳤶�
	 * @return string
	 */
	function cutSubstr($str_cut,$length){  

		if (strlen($str_cut) > $length){ 
			for($i=0; $i < $length; $i++) 
			if (ord($str_cut[$i]) > 128){
				$i++;
			} 
			$str_cut = substr($str_cut,0,$i); 
		} 
		return $str_cut; 
	}

	/**
	 * GBK ת UTF8 ����
	 */
	function gb2Utf8($str){
		if (function_exists('iconv')){
			return iconv("GBK", "UTF-8", $str);
		}elseif(function_exists('mb_convert_encoding')){
			return mb_convert_encoding($str, 'UTF-8', 'GBK');
		}
		return $str;
	}

	/**
	 * UTF8 ת GBK ����
	 */
	function utf2GB($str){
		if (function_exists('iconv')){
			return iconv("UTF-8", "GBK", $str);
		}elseif(function_exists('mb_convert_encoding')){
			return mb_convert_encoding($str, 'GBK', 'UTF-8');
		}
		return $str;	
	}
	
}



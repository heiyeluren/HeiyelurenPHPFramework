<?php
/*******************************************
 *  �������û��������ݼ��͹���
 *  ���ߣ�heiyeluren
 *  ������2008-09-02 19:05
 *  �޸ģ�2008-09-02 21:22
 *******************************************/


//��������������
include_once("Exception.class.php");
include_once("VerifyUtil.class.php");


/**
 * ������Դ����
 */
define("IV_POST",			1);
define("IV_GET",			2);
define("IV_REQUEST",		3);
define("IV_ENV",			4);
define("IV_SERVER",			5);

/**
 * ���ݹ�������
 */
define("FILTER_UNSAFE_RAW",	1);
define("FILTER_STRIPPED",	2);
define("FILTER_COOKED",		3);
define("FILTER_HTML",		4);
define("FILTER_EMAIL",		5);
define("FILTER_URL",		6);
define("FILTER_NUMBER",		7);

/**
 * HTML��������
 */
define("HTML_NO_TAGS",		1);
define("HTML_SHOW_TAGS",	2);
define("HTML_LITTLE_TAGS",	3);
define("HTML_MOSTLY_TAGS",	4);
define("HTML_TEXT_TAGS",	5);


/**
 * ����У���������
 *
 * �������������������ԡ���ȷ�ԡ���ȫ�ԡ��Ϸ��Լ��ĺ����ӿڣ�һ���ṩֱ�ӵ���
 */
class InputValidate
{


	/**
	 * ͨ�����ݻ�ȡ����(�ܹ�����ȱʡ����)
	 *
	 * @param int $type ��Ҫ��ʲô�ط���ȡ������$_POST, $_GET, $_REQUEST �ȣ��ο���Ӧ���ೣ��
	 * @param string $varName ����������Ҫ��ȡ�ı�������
	 * @param int $filter ��Ҫ���õĹ�������ȱʡ�� getStripped���ο���Ӧ���ೣ����
	 *					  ע�⣺����趨�� UNSAFE����᷵��ԭʼ���ݣ����Ǻ�Σ�յ�
	 * @return mixed ���ش����Ľ��
	 */
	function getData($type, $varName, $filter = ''){
		$var = '';
		switch ($type) {
			case IV_POST:
				if(!isset($_POST[$varName])){
					return '';
				}
				$var = $_POST[$varName];
				break;
			case IV_GET:
				if(!isset($_GET[$varName])){
					return '';
				}
				$var = $_GET[$varName];
				break;
			case IV_REQUEST:
				if(!isset($_REQUEST[$varName])){
					return '';
				}
				$var = $_REQUEST[$varName];
				break;
			case IV_ENV:
				if(!isset($_ENV[$varName])){
					return '';
				}
				$var = $_ENV[$varName];
				break;
			case IV_SERVER:
				if(!isset($_SERVER[$varName])){
					return '';
				}
				$var = $_SERVER[$varName];
				break;
			default:
				if(!isset($_REQUEST[$varName])){
					return '';
				}
				$var = $_REQUEST[$varName];
		}
		if (!$filter){
			switch($filter){
				case FILTER_UNSAFE_RAW: return $var;
				case FILTER_STRIPPED: return self::getStripped($var);
				case FILTER_COOKED: return self::getCooked($var);
				case FILTER_HTML: return self::getHtml($var);
				case FILTER_EMAIL: return self::getEmail($var);				
				case FILTER_URL: return self::getUrl($var);
				case FILTER_NUMBER: return self::getNumber($var);
				default: return self::getStripped($var);
			}
		}
		return self::getStripped($var);		
	}

	/**
	 * ��ȡHTML����
	 *
	 * @param string $str ��Ҫ���˵��ַ���
	 * @param int $htmlType ���˵ļ�������ͣ��ο���Ӧ���ೣ����ȱʡΪ�������б��
	 * @return string ���ع��˵ĺ�Ľ��
	 */
	function getHtml($str, $htmlType = HTML_NO_TAGS){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		switch($htmlType){
			//�޳�����HTML
			case HTML_NO_TAGS:
				$str = VerifyUtil::stripHtmlTag(VerifyUtil::filterScript($str), true);
				break;
			//��HTMLת��Ϊ����ʾ
			case HTML_SHOW_TAGS:
				$str = VerifyUtil::filterHtmlWord($str);
				break;
			//���沿��Σ����С��HTML��ǩ
			case HTML_LITTLE_TAGS:
				$str = strip_tags(VerifyUtil::filterScript($str), '<h1><h2><h3><h4><h5><h6><strong><code><b><i><tt><sub><sup><big><small><hr><br><font>');
				break;
			//����󲿷�HTML��ǩ
			case HTML_MOSTLY_TAGS:
				$str = strip_tags(VerifyUtil::filterScript($str), '<p><h1><h2><h3><h4><h5><h6><strong><em><abbr><acronym><address><bdo><blockquote><cite><q><code><ins><del><dfn><kbd><pre><samp><var><br><a><base><img><area><map><ul><ol><li><dl><dt><dd><table><tr><td><th><tbody><thead><tfoot><col><colgroup><caption><b><i><tt><sub><sup><big><small><hr><div><span>');
				break;
			//��������HTML��ǩ(����script,iframe,object)
			case HTML_TEXT_TAGS:
				$str = VerifyUtil::escapeScript($str);
				break;	
			default:
				$str = VerifyUtil::stripHtmlTag(VerifyUtil::filterScript($str), true);
		}
		return $str;
	}


	/**
	 * �滻���е� <,>,',",& ΪHTMLʵ��
	 *
	 * @param string $str ��Ҫ���˵��ַ���
	 * @return string ���ع��˵ĺ�Ľ��
	 */
	function getHtmlFull($str){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		return VerifyUtil::filterHtmlWord($str);
	}

	/**
	 * ���ַ��������ϸ���޳�����(���޳�����HTML��ASC��С��7�Ŀ����ַ���SQLע���ַ�ת��)
	 *
	 * @param string $str ��Ҫ�޳���ԭʼ��
	 * @reutrn string �����޳���Ĵ�
	 */
	function getStripped($str){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		return VerifyUtil::filterSqlInject(self::getHtml(preg_replace("/([\x00-\x07])/", "", $str), HTML_NO_TAGS));
	}

	/**
	 * ���ַ��������ϸ��ת������(��ת������HTMLΪ����ʾ�ģ�ASC��С��7�Ŀ����ַ�ת��Ϊ�ո�)
	 *
	 * @param string $str ��Ҫת����ԭʼ��
	 * @reutrn string ����ת����Ĵ�
	 */
	function getCooked(){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		return self::getHtml(preg_replace("/([\x00-\x07])/", "&nbsp;", $str), HTML_SHOW_TAGS);
	}

	/**
	 * ����Email��ַ
	 *
	 * @param string $str ��Ҫ�����ԭʼ��
	 * @param bool $strict �Ƿ��ȡ�ϸ�ʽ������ǣ���ôEmail��ַ���Ϸ���᷵�ؿ�
	 * @return �����Ĵ�
	 */
	function getEmail($str, $strict = false){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		if ($strict){
			if (!VerifyUtil::isEmail($str)){
				return '';
			}
			return $str;
		}
		return preg_replace("/(^[a-zA-Z0-9\.@_\-])/", "", $str);
	}

	/**
	 * ����URL��ַ
	 *
	 * @param string $str URL��ַ��
	 * @return string ������ǺϷ���URL�������ؿ��ַ���
	 */
	function getUrl($str){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		if (!VerifyUtil::isUrl($str)){
			return '';
		}
		return $str;
	}

	/**
	 * ��������
	 *
	 * @param string $str Ҫ��������ִ�
	 * @return string ���ᱣ�����֣���ѧ��������Ӧ���ַ����������ᱻ�޳�
	 */
	function getNumber($str){
		if (is_array($str) || is_object($str)){
			return $str;
		}
		if (is_numeric($str)){
			return $str;
		}
		return preg_replace("/(^[0-9\.+E])/", "", $str);
	}



}



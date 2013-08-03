<?php
/*******************************************
 *  ������Socket����������
 *  ���ߣ�heiyeluren
 *  ������2007-04-06 10:45
 *  �޸ģ�2007-04-11 19:57
 *******************************************/


//��������
define("__XML_ERROR_NO", -1);

//�����ļ�
include_once("Exception.class.php");
include_once("VerifyUtil.class.php");


/**
 * �򵥵�XML�ļ�������
 * ������Ľ�������ڶ����У����Է��ض����ȡ�������
 */
class XMLParser extends ExceptionClass
{
	var $path;
	var $result;
	var $index = 0;
	var $parser = null;

	/**
	 * ���캯��
	 */
	function XMLParser(){
		$this->path = "\$this->result";
	}

	/**
	 * ���н���XML�ļ���������Զ�̻����Ǳ����ļ���
	 * 
	 * @param string $fileName �����ļ����ƻ�����URL
	 * @return mixed ����з������Ǵ�����󣬳ɹ��޷���
	 */
	function parseFile( $fileName ){
		if (self::isError($result = VerifyUtil::fileIsExists($fileName))){
			return self::raiseError($result->getMessage(), __XML_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (!$result){
			return self::raiseError("XML file $filename not exists", __XML_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (trim($data = file_get_contents($fileName)) == ''){
			return self::raiseError("XML file content is empty", __XML_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $this->parseString($data);
	}

	/**
	 * ��XML�ַ������н���
	 *
	 * @param string $data ��Ҫ���н�����XML�ַ���
	 * @return mixed ����з������Ǵ�����󣬳ɹ��޷���
	 */
	function parseString( $data ){
		if (!function_exists('xml_parser_create')){
			return self::raiseError("XML parser lib not install", __XML_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (trim($data) == ""){
			return self::raiseError("XML content is empty", __XML_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startElement', 'endElement');
		xml_set_character_data_handler($this->parser, 'characterData');
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);

		xml_parse($this->parser, $data, true);
		xml_parser_free($this->parser);		
	}

	/**
	 * ���ؽ������
	 */
	function getResult(){
		return $this->retusl;
	}

	/**
	 * �ص�������ʼ�ڵ�
	 */
	function startElement( $parser, $tag, $attributeList ){
		$this->path .= "->".$tag;
		eval("\$data = ".$this->path.";");
		if (is_array($data)){
			$index = sizeof($data);
			$this->path .= "[".$index."]";
		} else if (is_object($data)){ 
			eval($this->path." = array(".$this->path.");");
			$this->path .= "[1]";
		}

		foreach($attributeList as $name => $value){
			eval($this->path."->".$name. " = '".self::cleanString($value)."';");
		}
	}

	/**
	 * �ص����������ڵ�
	 */
	function endElement( $parser, $tag ){
		$this->path = substr($this->path, 0, strrpos($this->path, "->"));
	}

	/**
	 * �ص�������������
	 */
	function characterData( $parser, $data ){
		if ($data = self::cleanString($data)){
			eval("$this->path .= \$data;");
		}
	}

	/**
	 * �滻xml�е���������
	 */
	function cleanString( $string ){
		return trim(str_replace("'", "&#39;", $string)); 
	}

}


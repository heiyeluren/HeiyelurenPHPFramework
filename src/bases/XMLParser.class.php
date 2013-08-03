<?php
/*******************************************
 *  描述：Socket操作基础类
 *  作者：heiyeluren
 *  创建：2007-04-06 10:45
 *  修改：2007-04-11 19:57
 *******************************************/


//常量定义
define("__XML_ERROR_NO", -1);

//包含文件
include_once("Exception.class.php");
include_once("VerifyUtil.class.php");


/**
 * 简单的XML文件解析类
 * 解析后的结果保存在对象中，可以返回对象获取解析结果
 */
class XMLParser extends ExceptionClass
{
	var $path;
	var $result;
	var $index = 0;
	var $parser = null;

	/**
	 * 构造函数
	 */
	function XMLParser(){
		$this->path = "\$this->result";
	}

	/**
	 * 进行解析XML文件（可以是远程或者是本地文件）
	 * 
	 * @param string $fileName 本地文件名称或者是URL
	 * @return mixed 如果有返回则是错误对象，成功无返回
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
	 * 对XML字符串进行解析
	 *
	 * @param string $data 需要进行解析的XML字符串
	 * @return mixed 如果有返回则是错误对象，成功无返回
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
	 * 返回解析结果
	 */
	function getResult(){
		return $this->retusl;
	}

	/**
	 * 回调函数开始节点
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
	 * 回调函数结束节点
	 */
	function endElement( $parser, $tag ){
		$this->path = substr($this->path, 0, strrpos($this->path, "->"));
	}

	/**
	 * 回调函数基础数据
	 */
	function characterData( $parser, $data ){
		if ($data = self::cleanString($data)){
			eval("$this->path .= \$data;");
		}
	}

	/**
	 * 替换xml中的特殊数据
	 */
	function cleanString( $string ){
		return trim(str_replace("'", "&#39;", $string)); 
	}

}


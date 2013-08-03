<?php
/*******************************************
 *  描述：异常和错误对象基础类
 *  作者：heiyeluren
 *  创建：2007-04-03 15:08
 *  修改：2007-04-12 18:12
 *******************************************/


/**
 * 异常操作处理类，是基础类
 */
class ExceptionClass
{  
	/**
	 * 触发一个异常
	 * 
	 * @param string $errMessag - 错误信息
	 * @param int $errCode - 错误代码
	 * @param string $errClass - 发生异常的类
	 * @param string $errFunction - 发生异常的方法
	 * @param string $errfile - 发生异常的文件
	 * @param string $errLIne - 发生异常的行
	 * 
	 * @access public
	 * @return object 返回Error对象
	 */
	function &raiseError($errMessage, $errCode='', $errClass='', $errFunction='', $errFile='', $errLine=''){
		$obj = new ErrorClass($errMessage, $errCode, $errFile, $errLine, $errClass, $errFunction);
		return $obj;
	}

	/**
	 * 抛出一个异常
	 * 
	 * @param string $errMessag - 错误信息
	 * @param int $errCode - 错误代码
	 * @param string $errfile - 发生异常的文件
	 * @param string $errLIne - 发生异常的行
	 *
	 * @access public
	 * @return object 返回Error对象
	 */
	function &throwError($errMessage, $errCode='', $errFile='', $errLine=''){
		$obj = new ErrorClass($errMessage, $errCode, $errFile, $errLine);
		return $obj;  
	}

	/**
	 * 判断一个对象是否是Error对象
	 *
	 * @param object $obj -Error对象
	 *
	 * @acces public
	 * @return bool 如果对象是一个Error对象则返回true，否则false
	 */
	function isError($obj){
		if (!is_object($obj)) {
			return false;
		}
		if (is_a($obj, 'ErrorClass')){
			return true;
		}
		return false;
	}

}


/**
 * 错误对象类，用于异常处理中生成抛出错误
 */
class ErrorClass
{
	/**
	 * 错误信息前缀
	 */
	var $msgPrefix = 'Exception';

	/**
	 * 错误信息
	 */
	var $errMessage = 'unknown error';

	/**
	 * 错误代码
	 */
	var $errCode = 0;

	/**
	 * 错误发生的文件
	 */
	var $errFile;

	/**
	 * 错误发生的行
	 */
	var $errLine;

	/**
	 * 错误所属的类
	 */
	var $errClass;

	/**
	 * 错误所属的方法
	 */
	var $errFunction;

	/**
	 * 构造函数
	 * 
	 * @param string $message - 错误信息
	 * @param int $code - 错误代码
	 * @param string $file - 发生异常的文件
	 * @param string $line - 发生异常的行
	 * @param string $class - 发生异常的类
	 * @param string $function - 发生异常的方法
	 * 
	 * @access public
	 * @return void
	 */
	function ErrorClass($message='unknown error', $code='', $file='', $line='', $class='', $function=''){
		$this->errMessage = $message;
		$this->errCode = $code;
		$this->errClass = $class;
		$this->errFunction = $function;
		$this->errFile = $file;
		$this->errLine = $line;
	}

	/**
	 * 获取错误信息
	 *
	 * @access public
	 * @return string 错误消息
	 */
	function getMessage(){
		return   $this->msgPrefix .": ". $this->errMessage;
	}

	/**
	 * 获取错误代码
	 *
	 * @access public
	 * @return string 错误代码
	 */
	function getCode(){
		return $this->errCode;
	}

	/**
	 * 获取错误文件
	 *
	 * @access public
	 * @return string 错误文件
	 */
	function getFile(){
		return $this->errFile;
	}

	/**
	 * 获取错误发生行
	 *
	 * @access public
	 * @return string 错误行信息
	 */
	function getLine(){
		return $this->errLine;
	}

	/**
	 * 获取错误发生类（对象）
	 *
	 * @access public
	 * @return string 错误发生类
	 */
	function getClass(){
		return $this->errClass;
	}

	/**
	 * 获取错误发生的方法或函数
	 *
	 * @access public
	 * @return string 错误发生的方法
	 */
	function getFunction(){
		return $this->errFunction;
	}


	/**
	 * 跟踪所有错误对象信息
	 *
	 * @access public
	 * @return arrar 包含所有错误对象信息的数组
	 */
	function getBacktrace(){
		$trace = array(
			"errFile"		=> $this->errFile,
			"errLine"		=> $this->errLine,
			"errClass"		=> $this->errClass,
			"errFunction"	=> $this->errFunction,
			"errMessage"	=> $this->errMessage,
			"errCode"		=> $this->errCode
		);
		return $trace;
	}

	/**
	 * 把所有错误对象信息转换为单个字符串
	 *
	 * @access public
	 * @return string 异常完整消息字符串
	 */
	function toString(){
		$msgString = sprintf("%s: %s (%s)\n[file %s line %s]\n[method %s at class %s]", 
			$this->msgPrefix,
			$this->errMessage, 
			$this->errCode, 
			$this->errFile, 
			$this->errLine, 
			$this->errFunction,
			$this->errClass
		);
		return $msgString;
	}

	
	/**
	 * 显示异常信息后终止脚本执行
	 */
	function exitError(){
		echo $this->toString();
		exit;
	}

	/**
	 * 显示异常信息
	 */
	function show(){
		echo $this->toString();
	}

	/**
	 * show()方法的别名
	 */
	function showError(){
		$this->show();
	}

}



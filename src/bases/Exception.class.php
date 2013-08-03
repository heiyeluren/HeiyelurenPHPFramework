<?php
/*******************************************
 *  �������쳣�ʹ�����������
 *  ���ߣ�heiyeluren
 *  ������2007-04-03 15:08
 *  �޸ģ�2007-04-12 18:12
 *******************************************/


/**
 * �쳣���������࣬�ǻ�����
 */
class ExceptionClass
{  
	/**
	 * ����һ���쳣
	 * 
	 * @param string $errMessag - ������Ϣ
	 * @param int $errCode - �������
	 * @param string $errClass - �����쳣����
	 * @param string $errFunction - �����쳣�ķ���
	 * @param string $errfile - �����쳣���ļ�
	 * @param string $errLIne - �����쳣����
	 * 
	 * @access public
	 * @return object ����Error����
	 */
	function &raiseError($errMessage, $errCode='', $errClass='', $errFunction='', $errFile='', $errLine=''){
		$obj = new ErrorClass($errMessage, $errCode, $errFile, $errLine, $errClass, $errFunction);
		return $obj;
	}

	/**
	 * �׳�һ���쳣
	 * 
	 * @param string $errMessag - ������Ϣ
	 * @param int $errCode - �������
	 * @param string $errfile - �����쳣���ļ�
	 * @param string $errLIne - �����쳣����
	 *
	 * @access public
	 * @return object ����Error����
	 */
	function &throwError($errMessage, $errCode='', $errFile='', $errLine=''){
		$obj = new ErrorClass($errMessage, $errCode, $errFile, $errLine);
		return $obj;  
	}

	/**
	 * �ж�һ�������Ƿ���Error����
	 *
	 * @param object $obj -Error����
	 *
	 * @acces public
	 * @return bool ���������һ��Error�����򷵻�true������false
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
 * ��������࣬�����쳣�����������׳�����
 */
class ErrorClass
{
	/**
	 * ������Ϣǰ׺
	 */
	var $msgPrefix = 'Exception';

	/**
	 * ������Ϣ
	 */
	var $errMessage = 'unknown error';

	/**
	 * �������
	 */
	var $errCode = 0;

	/**
	 * ���������ļ�
	 */
	var $errFile;

	/**
	 * ����������
	 */
	var $errLine;

	/**
	 * ������������
	 */
	var $errClass;

	/**
	 * ���������ķ���
	 */
	var $errFunction;

	/**
	 * ���캯��
	 * 
	 * @param string $message - ������Ϣ
	 * @param int $code - �������
	 * @param string $file - �����쳣���ļ�
	 * @param string $line - �����쳣����
	 * @param string $class - �����쳣����
	 * @param string $function - �����쳣�ķ���
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
	 * ��ȡ������Ϣ
	 *
	 * @access public
	 * @return string ������Ϣ
	 */
	function getMessage(){
		return   $this->msgPrefix .": ". $this->errMessage;
	}

	/**
	 * ��ȡ�������
	 *
	 * @access public
	 * @return string �������
	 */
	function getCode(){
		return $this->errCode;
	}

	/**
	 * ��ȡ�����ļ�
	 *
	 * @access public
	 * @return string �����ļ�
	 */
	function getFile(){
		return $this->errFile;
	}

	/**
	 * ��ȡ��������
	 *
	 * @access public
	 * @return string ��������Ϣ
	 */
	function getLine(){
		return $this->errLine;
	}

	/**
	 * ��ȡ�������ࣨ����
	 *
	 * @access public
	 * @return string ��������
	 */
	function getClass(){
		return $this->errClass;
	}

	/**
	 * ��ȡ�������ķ�������
	 *
	 * @access public
	 * @return string �������ķ���
	 */
	function getFunction(){
		return $this->errFunction;
	}


	/**
	 * �������д��������Ϣ
	 *
	 * @access public
	 * @return arrar �������д��������Ϣ������
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
	 * �����д��������Ϣת��Ϊ�����ַ���
	 *
	 * @access public
	 * @return string �쳣������Ϣ�ַ���
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
	 * ��ʾ�쳣��Ϣ����ֹ�ű�ִ��
	 */
	function exitError(){
		echo $this->toString();
		exit;
	}

	/**
	 * ��ʾ�쳣��Ϣ
	 */
	function show(){
		echo $this->toString();
	}

	/**
	 * show()�����ı���
	 */
	function showError(){
		$this->show();
	}

}



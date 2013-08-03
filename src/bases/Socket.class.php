<?php
/*******************************************
 *  ������Socket����������
 *  ���ߣ�heiyeluren
 *  ������2007-04-04 15:51
 *  �޸ģ�2007-04-09 19:48
 *******************************************/


//���������ʾ
define("__SOCKET_ERR_NO", -1);
define("__SOCKET_ERR_ADDR_INVALID", -2);
define("__SOCKET_ERR_PORT_INVALID", -3);
define("__SOCKET_ERR_NOT_CONN", -4);
define("__SOCKET_ERR_SET_BUFF_FAILED", -5);
define("__SOCKET_ERR_WRITE_FAILED", -6);
define("__SOCKET_ERR_READ_FAILED", -7);
define("__SOCKET_ERR_DATA_ERR", -8);


//���������쳣������ͼ����
include_once("Exception.class.php");
include_once("VerifyUtil.class.php");

/**
 * Socket����������
 *
 * ���������Ŀͻ���Socket��������
 */
class Socket extends ExceptionClass
{
	/**
	 * Socket������ָ��
	 */
	var $fp = null;

	/**
	 * �Ƿ�ʹ������ģʽ
	 */
	var $blocking = true;

	/**
	 * �Ƿ�ʹ�ó�����
	 */
	var $persistent = false;

	/**
	 * ��Ҫ���ʵ�IP��������
	 */
	var $addr = '';

	/**
	 * ���ʵĶ˿ں�
	 */
	var $port = 0;

	/**
	 * ���ӳ�ʱʱ��
	 */
	var $connTimeout = 30;

	/**
	 * ������ ��ȡ/д�� ��ʱʱ��
	 */
	var $streamTimeout = 30;

	/**
	 * ȱʡÿ����������ȡ�����ݴ�С
	 */
	var $readSize = 8192;

	/**
	 * ȱʡÿ��������д������ݴ�С
	 */
	var $writeSize = 8192;


	//----------------------------------
	//
	//      ������ʼ��������
	//
	//----------------------------------

	/**
	 * ���캯��
	 * 
	 * @param string $addr ��Ҫ���ʵ�IP��ַ��������
	 * @param int $port ��Ҫ���ʵĶԷ��������Ķ˿ں�
	 * @param bool $persistent �Ƿ���ó��������ӵ���������ȱʡΪ��
	 * @param int $connTimeout ���������ӳ�ʱʱ�䣬ȱʡΪ30��
	 * @param int $streamTimeout ���������ʶ�ȡ/д��ĳ�ʱʱ�䣬ȱʡΪ30��
	 */
	function Socket($addr, $port, $persistent=false, $connTimeout=null, $streamTimeout=null){
		if ($connTimeout!==null && is_int($connTimeout)){
			$this->connTimeout = $connTimeout;
		}
		if ($streamTimeout!==null && is_int($streamTimeout)){
			$this->streamTimeout = $streamTimeout;
		}
		$this->addr = $addr;
		$this->port = intval($port);
		$this->persistent = $persistent;
	}

	/**
	 * ���ӵ�������
	 * 
	 * @param bool $blocking �Ƿ���������ķ�ʽ����������ͨ��
	 * @return mixed �ɹ�����true��ʧ�ܷ���һ���������
	 */
	function connect($blocking = true){
		if (!VerifyUtil::isIpAddr($this->addr) && !VerifyUtil::isDomainName($this->addr)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_ADDR_INVALID), __SOCKET_ERR_ADDR_INVALID, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (!is_int($this->port) || $this->port<0 || $this->port>65535){
			return self::raiseError($this->getMessage(__SOCKET_ERR_PORT_INVALID), __SOCKET_ERR_PORT_INVALID, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		$errCode = -1;
		$errMessage = '';
		if ($this->persistent){
			$this->fp = @pfsockopen($this->addr, $this->port, $errCode, $errMessage, $this->connTimeout);
		}else{
			$this->fp = @fsockopen($this->addr, $this->port, $errCode, $errMessage, $this->connTimeout);
		}
		if (!$this->fp){
			return self::raiseError($errMessage, $errCode, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $this->setStreamBlocking($blocking);
	}

	/**
	 * �ر�Socket����
	 *
	 * @return mixed �ɹ�����true��ʧ�ܷ��ش������
	 */
	function disconnect(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		@fclose($this->fp);
		$this->fp = null;
		return true;
	}

	/**
	 * ����Ƿ��п��õ�����
	 */
	function isConnected(){
		if (!$this->fp || !is_resource($this->fp)){
			return false;
		}	
		return true;
	}

	/**
	 * ��ȡ��������Ӧ�Ĵ�����Ϣ
	 *
	 * @param int $code �������
	 * @return string �����ҵ��Ķ�Ӧ�������Ĵ�����Ϣ
	 */
	function getMessage($code){
		$errMessage = array(
			__SOCKET_ERR_NO					=> 'Unknow error',
			__SOCKET_ERR_ADDR_INVALID		=> 'IP address or domain-name invalid',
			__SOCKET_ERR_PORT_INVALID		=> 'Port number invalid or not a number',
			__SOCKET_ERR_NOT_CONN			=> 'Not availability socket connection',
			__SOCKET_ERR_SET_BUFF_FAILED	=> 'Cannot set write buffer',
			__SOCKET_ERR_WRITE_FAILED		=> 'Socket write data failed',
			__SOCKET_ERR_READ_FAILED		=> 'Socket read data failed',
			__SOCKET_ERR_DATA_ERR			=> 'Fetch data error'
		);
		if (!array_key_exists($code, $errMessage)){
			return '';
		}
		return $errMessage[$code];
	}


	//----------------------------------
	//
	//   Socket ����/��ȡ ����
	//
	//----------------------------------

	/**
	 * ��������������ʽ���Ƿ�������
	 *
	 * @param bool $mode �Ƿ���������Ϊtrue����Ϊfalse
	 * @return mixed �ɹ��������ý����ʧ�ܷ��ش������
	 */
	function setStreamBlocking($mode){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		$this->blocking = $mode;
		return stream_set_blocking($this->fp, $mode);
	}

	/**
	 * ��ȡ��ǰ��������
	 * 
	 * @return bool Ŀǰ��������
	 */
	function getStreamBlocking(){
		return $this->blocking;
	}

	/**
	 * ��������ȡ��ʱʱ��
	 *
	 * @param int $seconds ��ʱʱ�����
	 * @param int $microseconds ��ʱʱ��ĺ���
	 * @return mixed ���óɹ����ؽ����ʧ�ܷ��ش������
	 */
	function setStreamTimeout($seconds, $microseconds=0){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return stream_set_timeout($this->fp, $seconds, $microseconds);
	}

	/**
	 * ����д�뻺���С
	 * 
	 * @param int $size ��ȡ����д����Ĵ�С
	 * @return mixed ���óɹ����ؽ����ʧ�ܷ��ش������
	 */
	function setWriteBuffer($size){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if ($set = stream_set_write_buffer($this->fp, $size) == 0){
			return true;
		}
		return self::raiseError($this->getMessage(__SOCKET_ERR_SET_BUFF_FAILED), __SOCKET_ERR_SET_BUFF_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
	}

	/**
	 * ��ȡ��ǰSocket��״̬
	 *
	 * @return mixed ���óɹ����ؽ����ʧ�ܷ��ش������
	 */
	function getSocketStatus(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return socket_get_status($this->fp);
	}

	/**
	 * ��ȡ��ǰ�Ƿ�ﵽ������β��
	 *
	 * @return mixed ���óɹ����ؽ����ʧ�ܷ��ش������
	 */
	function isEof(){
		return  (!is_resource($this->fp) || feof($this->fp));
	}



	//----------------------------------
	//
	//     ������ ��ȡ/д�� ����
	//
	//----------------------------------
	
	/**
	 * д�����ݵ�Socket��
	 *
	 * @param string $data ��Ҫд�������
	 * @param int $blockSize ÿ��д�����Ĵ�С�����Բ����ã�Ĭ��Ϊһ����д��
	 * @return mixed ���óɹ�����д��ɹ����ֽ�����ʧ�ܷ��ش������
	 */
	function write($data, $blockSize = null){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//û���趨�������windowsϵͳֱ��д����
		if (is_null($blockSize) && substr(PHP_OS, 0, 3)!='WIN'){
			if (($writeLen = @fwrite($this->fp, $data)) === false){
				return self::raiseError($this->getMessage(__SOCKET_ERR_WRITE_FAILED), __SOCKET_ERR_WRITE_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			return $writeLen;
		}
		
		//�ֿ�д������
		if (is_null($blockSize)){
			$blockSize = $this->writeSize;
		}
		$position = 0;
		$maxSize = strlen($data);

		while($position < $maxSize){
			$writeLen = @fwrite($this->fp, substr($data, $position, $blockSize));
			if ($writeLen === false){
				return self::raiseError($this->getMessage(__SOCKET_ERR_WRITE_FAILED), __SOCKET_ERR_WRITE_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			$position += $writeLen;
		}
		return $position;
	}

	/**
	 * writeAll��write�ı���
	 */
	function writeAll($data, $blockSize = null){
		return $this->write($data, $blockSize);
	}

	/**
	 * д��һ�����ݵ�Socket������β����\r\n
	 * 
	 * @param string $data ��Ҫд�������
	 * @return mixed ���óɹ�����д��ɹ����ֽ�������ʧ�ܷ��ش������
	 */
	function writeLine($data){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (($writeLen = @fwrite($this->fp, $data."\r\n")) === false){
			return self::raiseError($this->getMessage(__SOCKET_ERR_WRITE_FAILED), __SOCKET_ERR_WRITE_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $writeLen;
	}
	
	/**
	 * ��ȡָ���ֽ�����������������
	 *
	 * @param int $size ��Ҫ��ȡ���ݵ��ֽ����������û��������ʹ��ȱʡֵ
	 * @return mixed ���óɹ������������ݣ�ʧ�ܷ��ش������
	 */
	function read($size = null){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (is_null($size) || !is_int($size)){
			$size = $this->readSize;
		}
		if (($data = @fread($this->fp, $size)) === false){
			return self::raiseError($this->getMessage(__SOCKET_ERR_READ_FAILED), __SOCKET_ERR_READ_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $data;
	}

	/**
	 * ��ȡ���е���������
	 * 
	 * @param int $size ϣ��ÿ�ζ�ȡ�����ݳ���
	 * @return mixed ���óɹ����ض�ȡ���������ݣ�ʧ�ܷ��ش������
	 */
	function readAll($size = null){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (is_null($size)){
			$size = $this->readSize;
		}
		while(!feof($this->fp)){
			if (($read = @fread($this->fp, $size)) === false){
				return self::raiseError($this->getMessage(__SOCKET_ERR_READ_FAILED), __SOCKET_ERR_READ_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}			
			$data .= $read;
		}
		return $data;
	}

	/**
	 * ��ȡһ��������Ϣ����\r\n��βΪһ�У������ȡ��һ�У�
	 * 
	 * @return mixed ���óɹ����ض�ȡ�����ʧ�ܷ��ش������
	 */
	function readLine(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		
		$dataLine = '';
		$timeout = time() + $this->streamTimeout;
		while(!feof($this->fp) || time()<$timeout){
			if (($line = @fgets($this->fp, $this->readSize)) === false){
				return self::raiseError($this->getMessage(__SOCKET_ERR_READ_FAILED), __SOCKET_ERR_READ_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			$dataLine .= $line;
			if (substr($dataLine, -1) == "\n"){
				return rtrim($dataLine, "\r\n");
			}
		}
		return $dataLine;
	}

	/**
	 * ��ȡһ����Ϣ�����������ָ�����ȣ���ֱ�ӷ����Ѷ�ȡ���ݣ�
	 * 
	 * @param int $size �������һ����ϣ����ȡ�ֽ�����ȱʡΪ���չ���������
	 * @return mixed ���óɹ����ض�ȡ���������ݣ�ʧ�ܷ��ش������
	 */
	function gets($size = null){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (is_null($size) || !is_int($size)){
			$size = $this->readSize;
		}
		if (($data = @fgets($this->fp, $size)) === false){
			return self::raiseError($this->getMessage(__SOCKET_ERR_READ_FAILED), __SOCKET_ERR_READ_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $data;		
	}

	/**
	 * ��ȡһ����\0��β���ַ���
	 * 
	 * @return mixed ���óɹ����ػ�ȡ���������ݣ�ʧ�ܷ��ش������
	 */
	function readString(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		$string = '';
		while($char=@fgetc($this->fp) != "\0"){
			$string .= $char;
		}
		return $string;
	}

	/**
	 * ��ȡһ���ֽڵ�����
	 *
	 * @return mixed ���óɹ�����һ���ֽ��������ݣ�ʧ�ܷ��ش������
	 */
	function readByte(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return @fgetc($this->fp);
	}

	/**
	 * ��ȡһ�����ζ��������ݣ�4���ֽڣ�
	 *
	 * @return mixed ���óɹ������������֣�ʧ�ܷ��ش������
	 */
	function readInt(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (($read = @fread($this->fp, 4)) === false){
			return self::raiseError($this->getMessage(__SOCKET_ERR_READ_FAILED), __SOCKET_ERR_READ_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (strlen($read) != 4){
			return self::raiseError($this->getMessage(__SOCKET_ERR_DATA_ERR), __SOCKET_ERR_DATA_ERR, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return array_pop(unpack('I', $read));
	}

	/**
	 * ��ȡһ����ʽ����IP��ַ����
	 *
	 * @return mixed ��ȡ�ɹ�����ip��ַ��ʧ�ܷ��ش������
	 */
    function readIPAddress(){
		if (!$this->fp || !is_resource($this->fp)){
			return self::raiseError($this->getMessage(__SOCKET_ERR_NOT_CONN), __SOCKET_ERR_NOT_CONN, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (($read = @fread($this->fp, 4)) === false){
			return self::raiseError($this->getMessage(__SOCKET_ERR_READ_FAILED), __SOCKET_ERR_READ_FAILED, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
        return sprintf("%s.%s.%s.%s", ord($read[0]), ord($read[1]), ord($read[2]), ord($read[3]));
    }
}


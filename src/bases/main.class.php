<?php
/*******************************************
 *  ����������������ȡ��ģ�¶��󹤳�ģʽ��
 *  ���ߣ�heiyeluren
 *  ������2007-04-12 16:07
 *  �޸ģ�2008-09-07 19:28
 *******************************************/

 
 //���������ļ�
include_once("../configs/common.config.php");
include_once("Exception.class.php");


class MainClass
{
	/**
	 * ��ȡ���ݿ���ʶ���
	 *
	 * @return mixed �ɹ����ض���ʧ�ܷ��ش�����ʾ��Ϣ�ַ���
	 */
	function &get_db(){
		include_once("DB.class.php");
		$db =& new DB(_DB_HOST, _DB_USER, _DB_PASSWD, _DB_NAME, _DB_IS_PCONNECT);
		if ($db->isError($res = $db->connect())){
			return $res->getMessage();
		}
		return $db;
	}

	/**
	 * ��ȡ���� SMTP Socket ���ʷ�ʽ����
	 *
	 * @param string $mailFrom �����˵�ַ
	 * @param string $mailTo �ռ��˵�ַ�������ַ֮��ʹ�ö��ŷָ���߹����һ��һά����
	 * @param string $mailSubject �ʼ�����
	 * @param string $mailBody �ʼ�����
	 * @param int $mailType ��Ҫָ����MIMEͷ��1Ϊhtml(text/html)��2Ϊtxt(text/plain)��ȱʡ��html
	 * @return mixed �ɹ����ض���ʧ�ܷ��ش�����ʾ��Ϣ�ַ���
	 */
	function &get_smtp($mailFrom, $mailTo, $mailSubject, $mailBody, $mailType = 1){
		include_once("SMTP.class.php");
		$smtp =& new SMTP( _SMTP_HOST, _SMTP_PORT, _SMTP_USER, _SMTP_PASSWD, $mailFrom, $mailTo, $mailSubject, $mailBody, $mailType);
		if ($smtp->isError($res = $smtp->connect())){
			return $res->getMessage();
		}
		return $smtp;
	}

	/**
	 * ��ȡSocket���Ӷ���
	 *
	 * @param string $addr ��Ҫ���ʵ�IP��ַ��������
	 * @param int $port ��Ҫ���ʵĶԷ��������Ķ˿ں�
	 * @param bool $persistent �Ƿ���ó��������ӵ���������ȱʡΪ��
	 * @param int $connTimeout ���������ӳ�ʱʱ�䣬ȱʡΪ30��
	 * @param int $streamTimeout ���������ʶ�ȡ/д��ĳ�ʱʱ�䣬ȱʡΪ30��
	 * @return mixed �ɹ����ض���ʧ�ܷ��ش�����ʾ��Ϣ�ַ���
	 */
	function &get_socket($addr, $port, $persistent=false, $connTimeout=null, $streamTimeout=null){
		include_once("Socket.class.php");
		$socket =& new Socket($addr, $port, $persistent, $connTimeout, $streamTimeout);
		if ($socket->isError($res = $socket->connect())){
			return $res->getMessage();
		}
		return $socket;
	}

	/**
	 * ��ȡһ��XML��������
	 *
	 * @return mixed �ɹ�����XML�������
	 */
	function &get_xml_parser(){
		include_once("XMLParser.class.php");
		$xml =& new XMLParser();
		return $xml;
	}

	/**
	 * ��ȡһ����ҳ�������
	 *
	 * @return mixed �ɹ����ط�ҳ����
	 */
	function &get_pager(){
		include_once("Pager.class.php");
		$pager =& new Pager();
		return $pager;
	}

	/**
	 * ��ȡһ����������������
	 *
	 * @return object ����һ�������������
	 */
	function &get_verify_util(){
		include_once("VerifyUtil.class.php");
		$v =& new VerifyUtil();
		return $v;
	}

	/**
	 * ��ȡһ���ļ��������
	 * 
	 * @param string $fileName ��Ҫ�����Ŀ���ļ�����
	 * @return object ����һ���ļ��������
	 */
	function &get_file($fileName = ''){
		include_once("File.class.php");
		$file =& new File($fileName);
		return $file;
	}

	/**
	 * ��ȡһ��ͼ�������
	 *
	 * @return object ����ͼ�������
	 */
	function &get_image(){
		include_once("Image.class.php");
		$image =& new Image();
		return $image;
	}

	/**
	 * ��ȡһ�����ַ����������
	 *
	 * @return object ��ȡһ�����ֽ��ַ����������
	 */
	function &get_multi_string(){
		include_once("MultiString.class.php");
		$str =& new MultiString();
		return $str;
	}

	/**
	 * ��ȡһ������Master/Slave�����ݿ������
	 *
	 * $masterConf = array(
	 *		"host"	=> Master���ݿ�������ַ
	 *		"user"	=> ��¼�û���
	 *		"pwd"	=> ��¼����
	 *		"db"	=> Ĭ�����ӵ����ݿ�
	 *	);
	 * $slaveConf = array(
	 *		"host"	=> Slave1���ݿ�������ַ|Slave2���ݿ�������ַ|...
	 *		"user"	=> ��¼�û���
	 *		"pwd"	=> ��¼����
	 *		"db"	=> Ĭ�����ӵ����ݿ�
	 *	)
	 *
	 * @return object
	 */
	function &get_db_multi(){
		//�趨Master��Slave��������ַ����
		//$masterConf = array();
		//$slaveConf = array();
		include_once("DBMulti.class.php");
		$db =& new DBMulti($masterConf, $slaveConf);
		return $db;
	}

	/**
	 * ��ȡһ�����洦�������
	 *
	 * @param int $cacheType �ǵ����ĸ����棬APC ���� Memcache��������鿴 Cache.class.php
	 * @param array $param ��Ҫ���ݵĲ��������ѡ�����Memcache�Ļ�����ô��Ҫ����һ��Memcache�����������ȥ������
	 *			array(
	 *				array('192.168.0.1', 11211), 
	 *				array('192.168.0.2', 11211), 
	 *				array('192.168.0.3', 11211),
	 *			)
	 *
	 * @return object
	 */
	function &get_cache($cacheType = NULL, $param = array()){
		include_once("Cache.class.php");
		//$param = array();	//�˴��趨memcache�������ļ�·��
		if ($cacheType){
			$cache =& new Cache($cacheType, $param);
		} else {
			$cache =& new Cache;
		}
		return $cache;
	}

	/**
	 * ��ȡһ��JSON���ݱ���/���봦�����
	 *
	 * @return object
	 */
	function &get_json(){
		include_once("Json.class.php");
		$json =& new JSON;
		return $json;
	}

	/**
	 * ��ȡһ����HTTP���������
	 *
	 * @param string $host ������/IP
	 * @param int $port WEB�˿ڣ�ȱʡΪ80
	 * @return object
	 */
	function &get_http_simple($host, $port = 80){
		include_once("Http.class.php");
		$http =& new HttpSimple($host, $port);
		return $http;
	}

	/**
	 * ��ȡһ���߼�HTTP���������
	 *
	 * @return object
	 */
	function &get_http_adv(){
		include_once("Http.class.php");
		$http =& new HttpAdvanced;
		return $http;
	}

	/**
	 * ��ȡһ��Session��������
	 *
	 * @param int $type ��Ҫ�洢���ļ�����Memcache�У��ļ�Ϊ 1 ��MemcacheΪ2
	 * @param mixed $param ��Ҫ���ݵĲ�����������ļ����ͣ�����Դ��ݱ���Session���ļ�·��
	 * @return object
	 */
	function &get_session($type, $param = array()){
		include_once("Session.class.php");
		if ($type == SESS_TYPE_FILE){
			$session =& new Session(false, $param);
		} else {
			$session =& new Session;
		}
		return $session;
	}



}




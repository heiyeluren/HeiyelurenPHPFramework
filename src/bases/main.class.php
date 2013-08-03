<?php
/*******************************************
 *  描述：基础对象提取（模仿对象工厂模式）
 *  作者：heiyeluren
 *  创建：2007-04-12 16:07
 *  修改：2008-09-07 19:28
 *******************************************/

 
 //包含基础文件
include_once("../configs/common.config.php");
include_once("Exception.class.php");


class MainClass
{
	/**
	 * 获取数据库访问对象
	 *
	 * @return mixed 成功返回对象，失败返回错误提示信息字符串
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
	 * 获取常用 SMTP Socket 访问方式对象
	 *
	 * @param string $mailFrom 发件人地址
	 * @param string $mailTo 收件人地址，多个地址之间使用逗号分割，或者构造成一个一维数组
	 * @param string $mailSubject 邮件主题
	 * @param string $mailBody 邮件内容
	 * @param int $mailType 需要指定的MIME头，1为html(text/html)，2为txt(text/plain)，缺省是html
	 * @return mixed 成功返回对象，失败返回错误提示信息字符串
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
	 * 获取Socket链接对象
	 *
	 * @param string $addr 需要访问的IP地址或者域名
	 * @param int $port 需要访问的对方服务器的端口号
	 * @param bool $persistent 是否采用长连接连接到服务器，缺省为否
	 * @param int $connTimeout 服务器连接超时时间，缺省为30秒
	 * @param int $streamTimeout 数据流访问读取/写入的超时时间，缺省为30秒
	 * @return mixed 成功返回对象，失败返回错误提示信息字符串
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
	 * 获取一个XML解析对象
	 *
	 * @return mixed 成功返回XML处理对象
	 */
	function &get_xml_parser(){
		include_once("XMLParser.class.php");
		$xml =& new XMLParser();
		return $xml;
	}

	/**
	 * 获取一个分页处理对象
	 *
	 * @return mixed 成功返回分页对象
	 */
	function &get_pager(){
		include_once("Pager.class.php");
		$pager =& new Pager();
		return $pager;
	}

	/**
	 * 获取一个基础变量检查对象
	 *
	 * @return object 返回一个变量处理对象
	 */
	function &get_verify_util(){
		include_once("VerifyUtil.class.php");
		$v =& new VerifyUtil();
		return $v;
	}

	/**
	 * 获取一个文件处理对象
	 * 
	 * @param string $fileName 需要处理的目标文件名称
	 * @return object 返回一个文件处理对象
	 */
	function &get_file($fileName = ''){
		include_once("File.class.php");
		$file =& new File($fileName);
		return $file;
	}

	/**
	 * 获取一个图像处理对象
	 *
	 * @return object 返回图像处理对象
	 */
	function &get_image(){
		include_once("Image.class.php");
		$image =& new Image();
		return $image;
	}

	/**
	 * 获取一个多字符串处理对象
	 *
	 * @return object 获取一个多字节字符串处理对象
	 */
	function &get_multi_string(){
		include_once("MultiString.class.php");
		$str =& new MultiString();
		return $str;
	}

	/**
	 * 获取一个处理Master/Slave多数据库类对象
	 *
	 * $masterConf = array(
	 *		"host"	=> Master数据库主机地址
	 *		"user"	=> 登录用户名
	 *		"pwd"	=> 登录密码
	 *		"db"	=> 默认连接的数据库
	 *	);
	 * $slaveConf = array(
	 *		"host"	=> Slave1数据库主机地址|Slave2数据库主机地址|...
	 *		"user"	=> 登录用户名
	 *		"pwd"	=> 登录密码
	 *		"db"	=> 默认连接的数据库
	 *	)
	 *
	 * @return object
	 */
	function &get_db_multi(){
		//设定Master和Slave的主机地址数组
		//$masterConf = array();
		//$slaveConf = array();
		include_once("DBMulti.class.php");
		$db =& new DBMulti($masterConf, $slaveConf);
		return $db;
	}

	/**
	 * 获取一个缓存处理类对象
	 *
	 * @param int $cacheType 是调用哪个缓存，APC 还是 Memcache，定义请查看 Cache.class.php
	 * @param array $param 需要传递的参数，如果选择的是Memcache的话，那么需要传递一个Memcache的主机数组过去，例：
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
		//$param = array();	//此处设定memcache主机或文件路径
		if ($cacheType){
			$cache =& new Cache($cacheType, $param);
		} else {
			$cache =& new Cache;
		}
		return $cache;
	}

	/**
	 * 获取一个JSON数据编码/解码处理对象
	 *
	 * @return object
	 */
	function &get_json(){
		include_once("Json.class.php");
		$json =& new JSON;
		return $json;
	}

	/**
	 * 获取一个简单HTTP处理类对象
	 *
	 * @param string $host 主机名/IP
	 * @param int $port WEB端口，缺省为80
	 * @return object
	 */
	function &get_http_simple($host, $port = 80){
		include_once("Http.class.php");
		$http =& new HttpSimple($host, $port);
		return $http;
	}

	/**
	 * 获取一个高级HTTP处理类对象
	 *
	 * @return object
	 */
	function &get_http_adv(){
		include_once("Http.class.php");
		$http =& new HttpAdvanced;
		return $http;
	}

	/**
	 * 获取一个Session操作对象
	 *
	 * @param int $type 需要存储到文件还是Memcache中，文件为 1 ，Memcache为2
	 * @param mixed $param 需要传递的参数，如果是文件类型，则可以传递保存Session的文件路径
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




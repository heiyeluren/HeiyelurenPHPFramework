<?
/**
 * 本程序是示例程序用于演示整个逻辑框架的执行过程
 * 2007-04-12 17:51
 */

/* 包含基础文件 */
include_once("../configs/common.config.php");
include_once("../bases/main.class.php");

/**
 * 本Class用于演示一个注册流程的基本校验和保存过程
 *
 */
class Example
{
	//数据库对象
	var $db;
	var $ver;

	//用户信息
	var $userName;
	var $userEmail;
	var $userPasswd;

	/**
	 * 构造函数 (初始化基本需要的数据)
	 * 
	 * @param string $userName 需要进行操作的用户名
	 * @param string $userEmail 需要进行操作的邮件地址
	 * @param string $userPasswd 需要进行操作的用户密码
	 */
	function Example($userName='', $userEmail='', $userPasswd=''){
		$this->userName = $userName;
		$this->userEmail = $userEmail;
		$this->userPasswd = $userPasswd;

		$this->db = MainClass::get_db();
		$this->ver = MainClass::get_verify_util();
	}

	/**
	 * 按照初始化的用户名、密码、邮件地址注册一个用户
	 * 同时注册过程中进行相应的正确性检测
	 *
	 * @return mixed 如果返回字符串说明有错误产生，如果返回true说明添加用户成功
	 */
	function addUser(){
		//基础检测
		if (!is_object($this->db)){
			return "数据库初始化失败：" . $this->db;
		}
		if (!$this->ver->chkUserName($this->userName)){
			return "用户名不合法";
		}
		if (!$this->ver->isEmail($this->userEmail)){
			return "用户邮件地址不合法";
		}
		if (strlen($this->userPasswd) < 6){
			return "为了你的安全，用户名长度不能低于六个字符";
		}
		
		//检测这个用户名的用户是否存在
		if (ExceptionClass::isError($res = $this->db->getOne("SELECT COUNT(*) FROM `user_info` WHERE `user_name` = '". $this->userName ."'"))){
			return $res->getMessage();
		}
		if ($res > 0){
			return "用户名已经在数据库中存在";
		}

		//检测这个邮件地址的用户名是否存在了
		if (ExceptionClass::isError($res = $this->db->getOne("SELECT COUNT(*) FROM `user_info` WHERE `user_email` = '". $this->userEmail ."'"))){
			return $res->getMessage();
		}
		if ($res > 0){
			return "该邮件地址已经有人注册使用了";
		}

		//插入用户数据到数据库
		$userInfo = array(
			"user_name"		=> $this->userName,
			"uesr_email"	=> $this->userEmail,
			"user_passwd"	=> md5($this->userPasswd),
		);
		if (ExceptionClass::isError($res = $this->db->autoExecute("user_info", $userInfo, 1))){
			return $res->getMessage();
		}
		
		//注册成功返回true
		return true;
	}

	/**
	 * 通过用户名删除一个用户
	 *
	 * @return mixed 如果返回字符串说明有错误产生，如果返回true说明删除用户成功
	 */
	function delUser(){
		//检测用户名
		if (!is_object($this->db)){
			return "数据库初始化失败：" . $this->db;
		}
		if (trim($this->userName) == ""){
			return "没有初始化用户名";
		}

		//执行删除
		$sql = "DELETE FROM user_info WHERE user_name = '". $this->userName ."' LIMIT 1";
		if (ExceptionClass::isError($res = $this->db->execute($sql))){
			return $res->getMessage();
		}
		return true;
	}

	/**
	 * 通过用户名获取一个用户的信息
	 *
	 * @return mixed 如果返回字符串说明有错误产生，如果返回数组说明获取用户信息成功
	 */
	function getUserInfo(){
		//检测用户名
		if (!is_object($this->db)){
			return "数据库初始化失败：" . $this->db;
		}
		if (trim($this->userName) == ""){
			return "没有初始化用户名";
		}

		//执行查询
		$sql = "SELECT * FROM `user_info` WHERE `user_name` = '". $this->userName ."' LIMIT 1";
		if (ExceptionClass::isError($res = $this->db->getRow($sql))){
			return $res->getMessage();
		}
		return $res;
	}

}





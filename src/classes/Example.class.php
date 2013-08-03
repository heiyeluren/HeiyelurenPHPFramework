<?
/**
 * ��������ʾ������������ʾ�����߼���ܵ�ִ�й���
 * 2007-04-12 17:51
 */

/* ���������ļ� */
include_once("../configs/common.config.php");
include_once("../bases/main.class.php");

/**
 * ��Class������ʾһ��ע�����̵Ļ���У��ͱ������
 *
 */
class Example
{
	//���ݿ����
	var $db;
	var $ver;

	//�û���Ϣ
	var $userName;
	var $userEmail;
	var $userPasswd;

	/**
	 * ���캯�� (��ʼ��������Ҫ������)
	 * 
	 * @param string $userName ��Ҫ���в������û���
	 * @param string $userEmail ��Ҫ���в������ʼ���ַ
	 * @param string $userPasswd ��Ҫ���в������û�����
	 */
	function Example($userName='', $userEmail='', $userPasswd=''){
		$this->userName = $userName;
		$this->userEmail = $userEmail;
		$this->userPasswd = $userPasswd;

		$this->db = MainClass::get_db();
		$this->ver = MainClass::get_verify_util();
	}

	/**
	 * ���ճ�ʼ�����û��������롢�ʼ���ַע��һ���û�
	 * ͬʱע������н�����Ӧ����ȷ�Լ��
	 *
	 * @return mixed ��������ַ���˵���д���������������true˵������û��ɹ�
	 */
	function addUser(){
		//�������
		if (!is_object($this->db)){
			return "���ݿ��ʼ��ʧ�ܣ�" . $this->db;
		}
		if (!$this->ver->chkUserName($this->userName)){
			return "�û������Ϸ�";
		}
		if (!$this->ver->isEmail($this->userEmail)){
			return "�û��ʼ���ַ���Ϸ�";
		}
		if (strlen($this->userPasswd) < 6){
			return "Ϊ����İ�ȫ���û������Ȳ��ܵ��������ַ�";
		}
		
		//�������û������û��Ƿ����
		if (ExceptionClass::isError($res = $this->db->getOne("SELECT COUNT(*) FROM `user_info` WHERE `user_name` = '". $this->userName ."'"))){
			return $res->getMessage();
		}
		if ($res > 0){
			return "�û����Ѿ������ݿ��д���";
		}

		//�������ʼ���ַ���û����Ƿ������
		if (ExceptionClass::isError($res = $this->db->getOne("SELECT COUNT(*) FROM `user_info` WHERE `user_email` = '". $this->userEmail ."'"))){
			return $res->getMessage();
		}
		if ($res > 0){
			return "���ʼ���ַ�Ѿ�����ע��ʹ����";
		}

		//�����û����ݵ����ݿ�
		$userInfo = array(
			"user_name"		=> $this->userName,
			"uesr_email"	=> $this->userEmail,
			"user_passwd"	=> md5($this->userPasswd),
		);
		if (ExceptionClass::isError($res = $this->db->autoExecute("user_info", $userInfo, 1))){
			return $res->getMessage();
		}
		
		//ע��ɹ�����true
		return true;
	}

	/**
	 * ͨ���û���ɾ��һ���û�
	 *
	 * @return mixed ��������ַ���˵���д���������������true˵��ɾ���û��ɹ�
	 */
	function delUser(){
		//����û���
		if (!is_object($this->db)){
			return "���ݿ��ʼ��ʧ�ܣ�" . $this->db;
		}
		if (trim($this->userName) == ""){
			return "û�г�ʼ���û���";
		}

		//ִ��ɾ��
		$sql = "DELETE FROM user_info WHERE user_name = '". $this->userName ."' LIMIT 1";
		if (ExceptionClass::isError($res = $this->db->execute($sql))){
			return $res->getMessage();
		}
		return true;
	}

	/**
	 * ͨ���û�����ȡһ���û�����Ϣ
	 *
	 * @return mixed ��������ַ���˵���д�������������������˵����ȡ�û���Ϣ�ɹ�
	 */
	function getUserInfo(){
		//����û���
		if (!is_object($this->db)){
			return "���ݿ��ʼ��ʧ�ܣ�" . $this->db;
		}
		if (trim($this->userName) == ""){
			return "û�г�ʼ���û���";
		}

		//ִ�в�ѯ
		$sql = "SELECT * FROM `user_info` WHERE `user_name` = '". $this->userName ."' LIMIT 1";
		if (ExceptionClass::isError($res = $this->db->getRow($sql))){
			return $res->getMessage();
		}
		return $res;
	}

}





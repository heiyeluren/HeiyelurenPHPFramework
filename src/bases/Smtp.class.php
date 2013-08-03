<?php
/*******************************************
 *  �������ʼ�������
 *  ���ߣ�heiyeluren
 *  ������2007-04-09 18:30
 *  �޸ģ�2007-04-11 16:09
 *******************************************/


//��������
define("__SMTP_ERROR_NO", "-1");

//���������ļ�
include_once("Exception.class.php");
include_once("Socket.class.php");


class SMTP extends ExceptionClass
{
	/**
	 * SMTP����
	 */
	var $smtpHost = 'localhost';

	/**
	 * SMTP�˿�
	 */
	var $smtpPort = 25;

	/**
	 * ��¼�û�
	 */
	var $loginUser = '';

	/**
	 * ��¼����
	 */
	var $loginPasswd = '';

	/**
	 * ������
	 */
	var $mailFrom = '';

	/**
	 * �ռ���
	 */
	var $mailTo = '';

	/**
	 * �ʼ�����
	 */
	var $mailSubject = '';

	/**
	 * �ʼ�����
	 */
	var $mailBody = '';

	/**
	 * �ʼ�����MIME����
	 */
	var $mailType = 1;

	/**
	 * Sockect�����
	 */
	var $socket;
	
	//------------------------
	//
	//      ��������
	//
	//------------------------

	/**
	 * ���캯��
	 * 
	 * @param string $smtpHost SMTP����
	 * @param int $smtpPort SMTP�����˿�
	 * @param string $loginUser ��¼�û� 
	 * @param string $loginPasswd ��¼����
	 * @param string $mailFrom �����˵�ַ
	 * @param string $mailTo �ռ��˵�ַ�������ַ֮��ʹ�ö��ŷָ���߹����һ��һά����
	 * @param string $mailSubject �ʼ�����
	 * @param string $mailBody �ʼ�����
	 * @param int $mailType ��Ҫָ����MIMEͷ��1Ϊhtml(text/html)��2Ϊtxt(text/plain)��ȱʡ��html
	 */
	function SMTP( $smtpHost, $smtpPort, $loginUser, $loginPasswd, $mailFrom, $mailTo, $mailSubject, $mailBody, $mailType = 1){
		$this->smtpHost		= $smtpHost;
		$this->smtpPort		= $smtpPort = $smtpPort=="" ? 25 : $smtpPort;
		$this->loginUser	= base64_encode($loginUser);
		$this->loginPasswd	= base64_encode($loginPasswd);
		$this->mailFrom		= $mailFrom;
		$this->mailTo		= $mailTo;
		$this->mailSubject	= $mailSubject;
		$this->mailBody		= $mailBody;
	}

	/**
	 * ���ӵ�SMTP������
	 *
	 * @return mixed �ɹ�����true��ʧ�ܷ��ش������
	 */
	function connect(){
		$this->socket = new Socket($this->smtpHost, $this->smtpPort);
		$obj = $this->socket->connect();
		if (self::isError($obj)){
			return self::raiseError($this->socket->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return true;
	}

	/**
	 * ����SMTPЭ��ָ��
	 *
	 * @param string $cms ��Ҫ���͵�ָ����Ϣ
	 * @return mixed �ɹ����ط��������ֽ�����ʧ�ܷ��ش������
	 */
	function sendCommand($cmd){
		if (!is_object($this->socket) || !$this->socket->isConnected()){
			return self::raiseError("Not availability socket connection", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $this->socket->write($cmd);
	}

	/**
	 * ��ȡһ�з�����Ϣ
	 *
	 * @param string ������Ϣ�е�һ�� ��ʧ�ܷ��ش������
	 */
	function getLine(){
		if (!is_object($this->socket) || !$this->socket->isConnected()){
			return self::raiseError("Not availability socket connection", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $this->socket->readLine();
	}

	/**
	 * �����ʼ���������
	 */
	function getMailMessage(){
		$mailMessage = "";
		$this->mailFrom = trim($this->mailFrom);
		$mailMessage .= "From:" . $this->mailFrom ."\r\n";
		if (is_array($this->mailTo)){
			$this->mailTo = trim(implode(",", $this->mailTo));
		}
		$mailMessage .= "To:". $this->mailTo ."\r\n";
		$mailMessage .= "Subject:". ($this->mailSubject=="" ? "Not Subject" : trim($this->mailSubject)) ."\r\n";
		$mailMessage .= "Content-Type: ". ($this->mailType==1 ? "text/html" : "text/plain") .";\r\n";
		$mailMessage .= "\r\n\r\n";
		$mailMessage .= ($this->mailBody=="" ? "Not Contents" : $this->mailBody);

		return $mailMessage;
	}


	/**
	 * �����ʼ�
	 *
	 * @return �ɹ�����true��ʧ�ܻ��߷��������ش������
	 */
	function sendMail(){
		//��ȡ�ʼ�������Ϣ
		$mailMessage = $this->getMailMessage();

		//��SMTP
		$obj = $this->sendCommand("EHLO HELO\r\n");

		//��֤��¼
		$this->sendCommand("AUTH LOGIN\r\n");
		$this->sendCommand($this->loginUser ."\r\n");
		$this->sendCommand($this->loginPasswd ."\r\n");
		if (!preg_match("/235|220/", $this->getLine())){
			return self::raiseError("SMTP user auth failed", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//�ύ�����û���Ϣ
		$obj = $this->sendCommand("MAIL FROM:". $this->mailFrom ."\r\n");
		if (self::isError($obj)){
			return self::raiseError($obj->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//�ύ�����û���Ϣ
		$obj = $this->sendCommand("RCPT TO:". $this->mailTO ."\r\n");
		if (self::isError($obj)){
			return self::raiseError($obj->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//�����ʼ�����
		$this->sendCommand("DATA\r\n");
		$obj = $this->sendCommand($mailMessage ."\r\n.\r\n");
		if (self::isError($obj)){
			return self::raiseError($obj->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (!preg_match("/250/", $this->getLine())){
			return self::raiseError("Send mail failed". $this->loginUser, __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//�˳�SMTP
		$this->sendCommand("QUIT\r\n");

		//�ر�����
		$this->socket->disconnect();

		return true;
	}

}


/**
 *
 * ���ñ����ʼ����������ʼ����ͳ����������ʼ��������ǲ��Խ׶Σ�ʹ�����������������������ʼ�ʹ�ã�
 * ȱʡ�Ƽ�ʹ��qmail��sendmail�����ͣ��������Ӧ������޸ģ�Ŀǰ֧�ֵ���qmail
 *
 */
class SmtpCMD extends ExceptionClass
{

	var $cmd = "/usr/bin/qmail";		//�ʼ�·��������/usr/sbin/sendmail��/usr/bin/mail
	var $mailForm = '';
	var $mailTo = '';
	var $mailSubject;
	var $mailBody = '';
	var $mailType = 1;

	/**
	 * ���캯��
	 * 
	 * @param string $mailFrom �����˵�ַ
	 * @param string $mailTo �ռ��˵�ַ�������ַ֮��ʹ�ö��ŷָ���߹����һ��һά����
	 * @param string $mailSubject �ʼ�����
	 * @param string $mailBody �ʼ�����
	 * @param int $mailType ��Ҫָ����MIMEͷ��1Ϊhtml(text/html)��2Ϊtxt(text/plain)��ȱʡ��html
	 * @param string $cmd ��Ҫ���÷����ʼ������ȱʡ��qmail����
	 */
	function SmtpCMD($mailFrom, $mailTo, $mailSubject, $mailBody, $mailType=1, $cmd=''){
		$this->cmd = $cmd!='' ? $cmd : $this->cmd;
		$this->mailFrom = $mailFrom;
		$this->mailTo = $mailTo;
		$this->mailType = $mailType;
		$this->mailSubject = $mailSubject;
		$this->mailBody = $mailBody;
	}

	/**
	 * ������user_send_validate_mail($email_addr, $subject,$to_uid,$content)
	 * ���ܣ�ʹ��qmail����У���ʼ�������֧��HTML��
	 * ������
	 * $email_addr		�����û��ʼ���ַ
	 * $subject			�ʼ�����
	 * $to_uid			���ܵ��û�
	 * $content			�ʼ�����
	 * ���أ��ɹ�����true��ʧ�ܷ���false
	 */	
	function sendMail(){
		//����ָ��
		$command =  $cmd." -f ".$this->mailFrom ." ". $this->mailTo; 

		//�򿪹ܵ�
		if (!($handle = @popen($command, "w"))){
			return self::raiseError("Open mail cmd ". $command ." failed.", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		} 

		//���ܵ�д����
		$mimeHeader = $this->mailType==1 ? "text/html" : "text/plain";
		@fwrite($handle, "From: ".$this->mailFrom."\n"); 
		@fwrite($handle, "Return-Path: ".$this->mailFrom."\n");
		@fwrite($handle, "To: ".$this->mailTo."\n");
		@fwrite($handle, "Subject: ".$this->mailSubject."\n");
		@fwrite($handle, "Mime-Version: 1.0\n");
		@fwrite($handle, "Content-Type: $mimeHeader; charset=\"gb2312\"\n\n");
		@fwrite($handle, $this->mailBody);
		if (!@pclose($handle)){
			return true;
		}
		return false;
	}
}


/**
 * ʹ������ mail ���������ʼ�������
 *
 *
 */
class SmtpMail {

	/**
	 * ʹ�� mail ����������ͨ�ʼ�
	 *
	 *
	 */
	function sendMail($arr){
		$mail_to        = $arr['mail_to'];
		$mail_from      = $arr['mail_from'];
		$mail_subject   = $arr['mail_subject'];
		$mail_body      = $arr['mail_body'];
		$is_html        = $arr['is_html'];
		$user_type      = $arr['user_type'];

		$header = "";
		if ($is_html){
			$header .= "MIME-Version: 1.0\r\n";
			$header .= "Content-Type: text/html; charset=UTF-8\r\n";
			$header .= "To: $mail_to\r\n";
		}
		$header .= "From: $mail_from\r\n";
		$header .= "Reply-To: $mail_from\r\n";
		$header .= "X-Mailer: Heiyeluren-Framework-Mailer\r\n";

		return mail($mail_to, $mail_subject, $mail_body, $header); 
	}

	/**
	 * ʹ�� mail �������ʹ��и������ʼ�(Ŀǰ��֧�ֲ�������)
	 *
	 */
	function sendAttaMail($to, $from, $subject, $body, $attachment, $is_html = true){
		$mail_to = implode(",", preg_split("/,|;|:|��|��/", preg_replace("/\s+/", "", $to)));
		$mail_from = $from;
		$mail_subject = mb_convert_encoding($subject, "UTF-8", "GBK");
		$body = base64_encode($body);
		$mail_attachment = chunk_split(base64_encode($attachment));
		$mail_boundary = uniqid( "");
		$atta_name = "cand_site_". date("Ymd") .".csv";

		$header = "";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-type: multipart/mixed; boundary=".$mail_boundary."\r\n";
		$header .= "From: $mail_from\r\n";
		$header .= "Reply-To: $mail_from\r\n";
		$header .= "X-Mailer: Heiyeluren-Framework-Mailer\r\n";

		$mail_body = "";
		$mail_body .= "--$mail_boundary\r\n";

		if ($is_html){
			$mail_body .= "Content-Type: text/html; charset=UTF-8\r\n";
		} else {
			$mail_body .= "Content-Type: text/plain; charset=UTF-8\r\n";
		}
		$mail_body .= "Content-Disposition: inline\r\n";
		$mail_body .= "Content-Transfer-Encoding: base64\r\n";
		$mail_body .= "\r\n";
		$mail_body .= $body ."\r\n";
		$mail_body .= "\r\n";
		$mail_body .= "--$mail_boundary\r\n";
		//$mail_body .= "\r\n";
		$mail_body .= "Content-Type: text/plain; charset=UTF8\r\n";//name=$atta_name\r\n";
		$mail_body .= "Content-Disposition: attachment; filename=$atta_name\r\n";
		//������ʹ�� Content-disposition: inline ������ʽ ���� attachment ������ʽ
		$mail_body .= "Content-Transfer-Encoding: base64\r\n";
		$mail_body .= "\r\n";
		$mail_body .= $mail_attachment ."\r\n";
		$mail_body .= "\r\n";
		$mail_body .= "--$mail_boundary--";

		return mail($mail_to, $mail_subject, $mail_body, $header); 
	}
}



<?php
/*******************************************
 *  描述：邮件发送类
 *  作者：heiyeluren
 *  创建：2007-04-09 18:30
 *  修改：2007-04-11 16:09
 *******************************************/


//常量定义
define("__SMTP_ERROR_NO", "-1");

//基本包含文件
include_once("Exception.class.php");
include_once("Socket.class.php");


class SMTP extends ExceptionClass
{
	/**
	 * SMTP主机
	 */
	var $smtpHost = 'localhost';

	/**
	 * SMTP端口
	 */
	var $smtpPort = 25;

	/**
	 * 登录用户
	 */
	var $loginUser = '';

	/**
	 * 登录密码
	 */
	var $loginPasswd = '';

	/**
	 * 发件人
	 */
	var $mailFrom = '';

	/**
	 * 收件人
	 */
	var $mailTo = '';

	/**
	 * 邮件主题
	 */
	var $mailSubject = '';

	/**
	 * 邮件内容
	 */
	var $mailBody = '';

	/**
	 * 邮件内容MIME类型
	 */
	var $mailType = 1;

	/**
	 * Sockect类对象
	 */
	var $socket;
	
	//------------------------
	//
	//      基础操作
	//
	//------------------------

	/**
	 * 构造函数
	 * 
	 * @param string $smtpHost SMTP主机
	 * @param int $smtpPort SMTP主机端口
	 * @param string $loginUser 登录用户 
	 * @param string $loginPasswd 登录密码
	 * @param string $mailFrom 发件人地址
	 * @param string $mailTo 收件人地址，多个地址之间使用逗号分割，或者构造成一个一维数组
	 * @param string $mailSubject 邮件主题
	 * @param string $mailBody 邮件内容
	 * @param int $mailType 需要指定的MIME头，1为html(text/html)，2为txt(text/plain)，缺省是html
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
	 * 连接到SMTP服务器
	 *
	 * @return mixed 成功返回true，失败返回错误对象
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
	 * 发送SMTP协议指令
	 *
	 * @param string $cms 需要发送的指令信息
	 * @return mixed 成功返回发送内容字节数，失败返回错误对象
	 */
	function sendCommand($cmd){
		if (!is_object($this->socket) || !$this->socket->isConnected()){
			return self::raiseError("Not availability socket connection", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $this->socket->write($cmd);
	}

	/**
	 * 获取一行返回信息
	 *
	 * @param string 返回消息中的一行 ，失败返回错误对象
	 */
	function getLine(){
		if (!is_object($this->socket) || !$this->socket->isConnected()){
			return self::raiseError("Not availability socket connection", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		return $this->socket->readLine();
	}

	/**
	 * 构造邮件信体内容
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
	 * 发送邮件
	 *
	 * @return 成功返回true，失败或者发生错发返回错误对象
	 */
	function sendMail(){
		//获取邮件内容信息
		$mailMessage = $this->getMailMessage();

		//打开SMTP
		$obj = $this->sendCommand("EHLO HELO\r\n");

		//验证登录
		$this->sendCommand("AUTH LOGIN\r\n");
		$this->sendCommand($this->loginUser ."\r\n");
		$this->sendCommand($this->loginPasswd ."\r\n");
		if (!preg_match("/235|220/", $this->getLine())){
			return self::raiseError("SMTP user auth failed", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//提交发送用户信息
		$obj = $this->sendCommand("MAIL FROM:". $this->mailFrom ."\r\n");
		if (self::isError($obj)){
			return self::raiseError($obj->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//提交接受用户信息
		$obj = $this->sendCommand("RCPT TO:". $this->mailTO ."\r\n");
		if (self::isError($obj)){
			return self::raiseError($obj->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//发送邮件信体
		$this->sendCommand("DATA\r\n");
		$obj = $this->sendCommand($mailMessage ."\r\n.\r\n");
		if (self::isError($obj)){
			return self::raiseError($obj->getMessage(), __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}
		if (!preg_match("/250/", $this->getLine())){
			return self::raiseError("Send mail failed". $this->loginUser, __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		}

		//退出SMTP
		$this->sendCommand("QUIT\r\n");

		//关闭连接
		$this->socket->disconnect();

		return true;
	}

}


/**
 *
 * 调用本地邮件服务器或邮件发送程序来发送邮件（本类是测试阶段，使用请谨慎，建议大批量发送邮件使用）
 * 缺省推荐使用qmail、sendmail来发送，最好做相应程序的修改，目前支持的是qmail
 *
 */
class SmtpCMD extends ExceptionClass
{

	var $cmd = "/usr/bin/qmail";		//邮件路径可能是/usr/sbin/sendmail、/usr/bin/mail
	var $mailForm = '';
	var $mailTo = '';
	var $mailSubject;
	var $mailBody = '';
	var $mailType = 1;

	/**
	 * 构造函数
	 * 
	 * @param string $mailFrom 发件人地址
	 * @param string $mailTo 收件人地址，多个地址之间使用逗号分割，或者构造成一个一维数组
	 * @param string $mailSubject 邮件主题
	 * @param string $mailBody 邮件内容
	 * @param int $mailType 需要指定的MIME头，1为html(text/html)，2为txt(text/plain)，缺省是html
	 * @param string $cmd 需要调用发送邮件的命令，缺省是qmail命令
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
	 * 函数：user_send_validate_mail($email_addr, $subject,$to_uid,$content)
	 * 功能：使用qmail发送校验邮件函数（支持HTML）
	 * 参数：
	 * $email_addr		接受用户邮件地址
	 * $subject			邮件主题
	 * $to_uid			接受的用户
	 * $content			邮件内容
	 * 返回：成功返回true，失败返回false
	 */	
	function sendMail(){
		//构造指令
		$command =  $cmd." -f ".$this->mailFrom ." ". $this->mailTo; 

		//打开管道
		if (!($handle = @popen($command, "w"))){
			return self::raiseError("Open mail cmd ". $command ." failed.", __SMTP_ERROR_NO, __CLASS__, __METHOD__, __FILE__, __LINE__);
		} 

		//往管道写数据
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
 * 使用内置 mail 函数进行邮件发送类
 *
 *
 */
class SmtpMail {

	/**
	 * 使用 mail 函数发送普通邮件
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
	 * 使用 mail 函数发送带有附件的邮件(目前仅支持部分邮箱)
	 *
	 */
	function sendAttaMail($to, $from, $subject, $body, $attachment, $is_html = true){
		$mail_to = implode(",", preg_split("/,|;|:|，|、/", preg_replace("/\s+/", "", $to)));
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
		//还可以使用 Content-disposition: inline 内联方式 或者 attachment 附件方式
		$mail_body .= "Content-Transfer-Encoding: base64\r\n";
		$mail_body .= "\r\n";
		$mail_body .= $mail_attachment ."\r\n";
		$mail_body .= "\r\n";
		$mail_body .= "--$mail_boundary--";

		return mail($mail_to, $mail_subject, $mail_body, $header); 
	}
}



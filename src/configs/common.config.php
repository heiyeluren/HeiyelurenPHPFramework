<?php
/********************************
 *  描述：公共配置文件
 *  作者：heiyeluren <heiyeluren@gmail.com>
 *  创建：2007-04-02 16:32
 *  修改：2007-04-12 17:14
 ********************************/


/**
 * 基本路径配置
 */
define("_ROOT_PATH", str_replace("\\", "/", substr(dirname(__FILE__), 0, -7)));	//根路径
define("_BASECLASS_PATH", _ROOT_PATH . "/bases/");			//基础类目录
define("_APPCLASS_PATH", _ROOT_PATH . "/classes/");			//应用类路径
define("_FUNCTION_PATH", _ROOT_PATH . "/functions/");		//函数路径
define("_CONFIG_PATH", _ROOT_PATH . "/configs/");			//配置文件路径
define("_TEMPLATES_PATH", _ROOT_PATH . "/templates/");		//模板文件路径
define("_WEBROOT_PATH", _ROOT_PATH . "/webroot/");			//网站页面路径


/**
 * 基础网站URL配置
 */
define("_WEB_URL", "http://localhost");						//网站URL
define("_WEB_JS_URL", _WEB_URL . "/js/");					//js路径
define("_WEB_CSS_URL", _WEB_URL . "/css/");					//css路径
define("_WEB_IMGS_URL", _WEB_URL . "/imgs/");				//图片路径


/**
 * 数据库配置
 */
define("_DB_HOST", "localhost");							//数据库主机地址
define("_DB_USER", "root");									//数据库访问用户
define("_DB_PASSWD", "");									//数据库访问密码
define("_DB_NAME", "test");									//数据库
define("_DB_IS_PCONNECT", false);							//是否使用长链接

/**
 * 邮件发送配置
 */
define("_SMTP_HOST", "localhost");							//SMTP服务器主机
define("_SMTP_PORT", 25);									//SMTP服务器端口
define("_SMTP_USER", "test");								//SMTP服务器登录用户
define("_SMTP_PASSWD", "");									//SMTP服务器登录密码

/**
 * 其他配置
 */
define("_QQ_IPADDR_DAT", _CONFIG_PATH . "/qqwrt.dat");		//纯真IP数据库（2008-08-25版)
define("_FILTER_WORD_FILE", _CONFIG_PATH ."/filterword.data.inc");	//非法词语过滤词表



?>
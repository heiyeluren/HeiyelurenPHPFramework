<?php
/********************************
 *  ���������������ļ�
 *  ���ߣ�heiyeluren <heiyeluren@gmail.com>
 *  ������2007-04-02 16:32
 *  �޸ģ�2007-04-12 17:14
 ********************************/


/**
 * ����·������
 */
define("_ROOT_PATH", str_replace("\\", "/", substr(dirname(__FILE__), 0, -7)));	//��·��
define("_BASECLASS_PATH", _ROOT_PATH . "/bases/");			//������Ŀ¼
define("_APPCLASS_PATH", _ROOT_PATH . "/classes/");			//Ӧ����·��
define("_FUNCTION_PATH", _ROOT_PATH . "/functions/");		//����·��
define("_CONFIG_PATH", _ROOT_PATH . "/configs/");			//�����ļ�·��
define("_TEMPLATES_PATH", _ROOT_PATH . "/templates/");		//ģ���ļ�·��
define("_WEBROOT_PATH", _ROOT_PATH . "/webroot/");			//��վҳ��·��


/**
 * ������վURL����
 */
define("_WEB_URL", "http://localhost");						//��վURL
define("_WEB_JS_URL", _WEB_URL . "/js/");					//js·��
define("_WEB_CSS_URL", _WEB_URL . "/css/");					//css·��
define("_WEB_IMGS_URL", _WEB_URL . "/imgs/");				//ͼƬ·��


/**
 * ���ݿ�����
 */
define("_DB_HOST", "localhost");							//���ݿ�������ַ
define("_DB_USER", "root");									//���ݿ�����û�
define("_DB_PASSWD", "");									//���ݿ��������
define("_DB_NAME", "test");									//���ݿ�
define("_DB_IS_PCONNECT", false);							//�Ƿ�ʹ�ó�����

/**
 * �ʼ���������
 */
define("_SMTP_HOST", "localhost");							//SMTP����������
define("_SMTP_PORT", 25);									//SMTP�������˿�
define("_SMTP_USER", "test");								//SMTP��������¼�û�
define("_SMTP_PASSWD", "");									//SMTP��������¼����

/**
 * ��������
 */
define("_QQ_IPADDR_DAT", _CONFIG_PATH . "/qqwrt.dat");		//����IP���ݿ⣨2008-08-25��)
define("_FILTER_WORD_FILE", _CONFIG_PATH ."/filterword.data.inc");	//�Ƿ�������˴ʱ�



?>
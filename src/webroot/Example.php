<?php
/**
 * ��������ʾ������������ʾ�����߼���ܵ�ִ�й���
 * 2007-04-12 17:51
 */

/* ���������ļ� */
include_once("init.php");
include_once("../classes/Example.class.php");

//ʵ����ǰ��Ӧ�ö���
$user =& new Example('heiyeluren', 'heiyeluren@gmail.com', '123456');

//����һ���û�
if (is_string($res = $user->addUser())){
	echo $res;
	exit;
}

echo "�����û��ɹ���";


?>
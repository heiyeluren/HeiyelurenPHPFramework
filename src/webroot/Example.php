<?php
/**
 * 本程序是示例程序用于演示整个逻辑框架的执行过程
 * 2007-04-12 17:51
 */

/* 包含基础文件 */
include_once("init.php");
include_once("../classes/Example.class.php");

//实例化前端应用对象
$user =& new Example('heiyeluren', 'heiyeluren@gmail.com', '123456');

//增加一个用户
if (is_string($res = $user->addUser())){
	echo $res;
	exit;
}

echo "增加用户成功！";


?>
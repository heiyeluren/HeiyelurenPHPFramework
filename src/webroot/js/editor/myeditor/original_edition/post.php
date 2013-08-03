<?php 
//print_r($_POST);
//$a = $_POST['content'];
//$conn = mysql_connect("localhost","root","");
//mysql_select_db("guestbookdb",$conn);
//$insertcommand = "INSERT INTO image VALUES(NULL,'$a')";
//mysql_query($insertcommand,$conn);
echo htmlspecialchars($_POST['content']);
//echo $_POST['content'];
?>
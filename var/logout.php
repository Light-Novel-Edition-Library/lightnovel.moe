<?php 
session_start();

if(isset($_SESSION['uid'])){
	require_once("config.php");
	require_once("functions.php");
	
	//连接数据库
	$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
	if(!$sql_conn){
		die(mysqli_connect_error());
	}
	
	//更新token
	$token = newToken();
	$sql_query = "UPDATE ln_auth SET token='{$token}' WHERE uid='{$_SESSION['uid']}'";
	mysqli_query($sql_conn, $sql_query);
	
	//关闭数据库连接
	mysqli_close($sql_conn);
	
	session_destroy();
}
?>
<script>
	document.cookie = "uid=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
	document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.replace("/");
</script>
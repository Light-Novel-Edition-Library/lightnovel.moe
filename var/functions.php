<?php 
function newToken(){
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$token = "";
	for($i = 1; $i <= 64; $i++){
		$token = $token.$chars[rand(0,61)];
	}
	return $token;
}
?>
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php 
		if($_1 == "login"){
			echo "Sign in / Sign up - Light Novel Edition Library";
		}else{
			echo "Light Novel Edition Library | 輕小說版本圖書館";
		}
		?></title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.6.1/font/bootstrap-icons.css">
	</head>
	<body>
		<nav class="py-2 bg-light border-bottom">
			<div class="container d-flex flex-wrap">
				<ul class="nav me-auto">
					<li class="nav-item"><a href="/" class="nav-link link-dark px-2 active" aria-current="page">Books</a></li>
					<li class="nav-item"><a href="/" class="nav-link link-dark px-2">Papers</a></li>
				</ul>
				<ul class="nav">
					<?php 
					if(isset($_SESSION['uid'])){
						if($_SESSION['nick'] == ""){
							echo '<li class="nav-item"><a href="/profile" class="nav-link link-dark px-2">'.$_SESSION['username'].'</a></li>';
						}else{
							echo '<li class="nav-item"><a href="/profile" class="nav-link link-dark px-2">'.$_SESSION['nick'].'</a></li>';
						}
						echo '<li class="nav-item"><a href="/logout" class="nav-link link-dark px-2">Sign out</a></li>';
					}elseif(isset($_COOKIE['uid'])){
						require_once("config.php");
						//连接数据库
						$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
						if(!$sql_conn){
							die(mysqli_connect_error());
						}
						$uid = mysqli_real_escape_string($sql_conn, $_COOKIE['uid']);
						$token = mysqli_real_escape_string($sql_conn, $_COOKIE['token']);
						$sql_query = "SELECT id,nick,email FROM ln_auth WHERE uid='{$uid}' AND token='{$token}'";
						$sql_result = mysqli_query($sql_conn, $sql_query);
						if(mysqli_num_rows($sql_result) == 0){
							echo '<li class="nav-item"><a href="/login" class="nav-link link-dark px-2">Sign in / Sign up</a></li>';
						}else{
							$sql_row = mysqli_fetch_assoc($sql_result);
							$_SESSION['uid'] = $uid;
							$_SESSION['username'] = $sql_row['id'];
							$_SESSION['nick'] = $sql_row['nick'];
							$_SESSION['email'] = $sql_row['email'];
							if($_SESSION['nick'] == ""){
								echo '<li class="nav-item"><a href="/profile" class="nav-link link-dark px-2">'.$_SESSION['username'].'</a></li>';
							}else{
								echo '<li class="nav-item"><a href="/profile" class="nav-link link-dark px-2">'.$_SESSION['nick'].'</a></li>';
							}
							echo '<li class="nav-item"><a href="/logout" class="nav-link link-dark px-2">Sign out</a></li>';
						}
						//关闭数据库连接
						mysqli_close($sql_conn);
					}else{
						echo '<li class="nav-item"><a href="/login" class="nav-link link-dark px-2">Sign in / Sign up</a></li>';
					}
					?>
				</ul>
			</div>
		</nav>
		<header class="py-3 mb-4 border-bottom">
			<div class="container d-flex flex-wrap justify-content-center">
				<a href="/" class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-dark text-decoration-none">
				<span class="fs-4">Light Novel Edition Library</span>
				</a>
			<form class="col-12 col-lg-auto mb-3 mb-lg-0">
				<input type="search" class="form-control" placeholder="Search" aria-label="Search">
			</form>
			</div>
		</header>
<?php 
function checkvali($token_request){
    $post_data = array(
        'secret'=>'6Le2tmgaAAAAAIO_10kusKgEZTHY_7fIuw5Q-zyl',
        'response'=>$token_request
    );
    return send_post('https://www.recaptcha.net/recaptcha/api/siteverify', $post_data);
}
function send_post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = array(
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}


if(isset($_POST['g_token'])){
	$recaptcha_json_result = checkvali($_POST['g_token']);
	$g_result = json_decode($recaptcha_json_result,true);
	if(isset($g_result['success']) && $g_result['success']==true){
		if(isset($g_result['score']) && $g_result['score']>0.5){
			require_once("config.php");
			//连接数据库
			$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
			if(!$sql_conn){
				die(mysqli_connect_error());
			}
			$id = mysqli_real_escape_string($sql_conn, $_POST['username']);
			$password = md5("light".$_POST['password']."novel");
			$sql_query = "SELECT password FROM ln_auth WHERE id='{$id}'";
			$sql_result = mysqli_query($sql_conn, $sql_query);
			if(mysqli_num_rows($sql_result) == 0){//若用户未注册，则注册
				//生成新token
				require_once("functions.php");
				$token = newToken();
				
				//将新用户信息写入数据库
				$sql_query = "INSERT INTO ln_auth (id, password, token) VALUES ('{$id}', '{$password}', '{$token}')";
				if(!mysqli_query($sql_conn, $sql_query)){
					die(mysqli_error($sql_conn));
				}
				
				//获取用户的UID
				$sql_query = "SELECT uid FROM ln_auth WHERE id='{$id}'";
				$sql_result = mysqli_query($sql_conn, $sql_query);
				$sql_row = mysqli_fetch_assoc($sql_result);
				
				//存储用户信息到浏览器会话
				$_SESSION['uid'] = $sql_row['uid'];
				$_SESSION['username'] = $id;
				$_SESSION['nick'] = "";
				$_SESSION['email'] = "";
				
				//检查是否保存登录状态
				if(isset($_POST['remember']) && $_POST['remember']=="on"){
					echo '<script>var d = new Date(); d.setTime(d.getTime()+(30*24*60*60*1000)); var expires = "expires="+d.toGMTString();';
					echo 'document.cookie = "uid='.$_SESSION['uid'].';" + expires + ";path=/";';
					echo 'document.cookie = "token='.$token.';" + expires + ";path=/";</script>';
				}
				
				//提示成功注册并跳转
				echo '<div class="alert alert-success alert-dismissible">';
				echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
				echo '<strong>Successfully registered, redirecting...</strong>';
				echo '</div>';
				echo '<script>window.location.replace("/");</script>';
			}else{//若用户已注册，则检查密码是否正确
				$sql_row = mysqli_fetch_assoc($sql_result);
				if($password == $sql_row['password']){//密码正确时
					//获取用户的UID、昵称和token
					$sql_query = "SELECT uid,nick,email,token FROM ln_auth WHERE id='{$id}'";
					$sql_result = mysqli_query($sql_conn, $sql_query);
					$sql_row = mysqli_fetch_assoc($sql_result);
					
					//存储用户信息到浏览器会话
					$_SESSION['uid'] = $sql_row['uid'];
					$_SESSION['username'] = $id;
					$_SESSION['nick'] = $sql_row['nick'];
					$_SESSION['email'] = $sql_row['email'];
					
					//检查是否保存登录状态
					if(isset($_POST['remember']) && $_POST['remember']=="on"){
						echo '<script>var d = new Date(); d.setTime(d.getTime()+(30*24*60*60*1000)); var expires = "expires="+d.toGMTString();';
						echo 'document.cookie = "uid='.$_SESSION['uid'].';" + expires + ";path=/";';
						echo 'document.cookie = "token='.$sql_row['token'].';" + expires + ";path=/";</script>';
					}
					
					//提示登录成功并跳转
					echo '<div class="alert alert-success alert-dismissible">';
					echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
					echo '<strong>Successfully logged in, redirecting...</strong>';
					echo '</div>';
					echo '<script>window.location.replace("/");</script>';
				}else{//密码错误时
					echo '<div class="alert alert-warning alert-dismissible">';
					echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
					echo '<strong>Incorrect password, please try again.</strong><br />';
					echo 'If you are a new user, please change your username.</div>';
				}
			}
			//关闭数据库连接
			mysqli_close($sql_conn);
		}else{
			echo '<div class="alert alert-danger alert-dismissible">';
			echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
			echo '<strong>Login failed, please do not use automated programs.</strong><br />';
			echo $recaptcha_json_result.'</div>';
		}
	}else{
		echo '<div class="alert alert-danger alert-dismissible">';
		echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
		echo '<strong>Login failed, please refresh the page and try again.</strong><br />';
		echo 'If the problem persists, please report the following information to the administrator:<br />';
		echo $recaptcha_json_result.'</div>';
	}
}
?>
<script src="https://www.recaptcha.net/recaptcha/api.js?render=6Le2tmgaAAAAAD7sUlWs1NicxlBcu-qmEZrGN8Hk"></script>
<div class="row">
	<div class="col-md-6 offset-md-3">
		<h1 class="text-center">Sign in / Sign up</h1>
		<p class="text-center">If you log in for the first time, the account will be created automatically.</p>
		<div class="card">
			<div class="card-body">
				<form id="login_form" method="post">
				  <div class="mb-3">
					<label for="username" class="form-label">Username</label>
					<input type="text" class="form-control" id="username" name="username" required="required" onkeyup="checkUsername()"><span class="text-danger" id="usernameAlert"></span>
					<span id="usernameHelp" class="form-text">Only English letters, numbers and underscores are supported, with a length of 4 to 16 characters.</span>
				  </div>
				  <div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input type="password" class="form-control" id="password" required="required" onkeyup="checkPassword()"><span class="text-danger" id="passwordAlert"></span>
					<span class="form-text" id="passwordHelp">The password must contain English letters, numbers, can have special characters (~!@#$%^&*._?), and the length is 6 to 18 characters.</span>
				  </div>
				  <input type="hidden" name="password" id="sha1_password" value="" />
				  <div class="mb-3 form-check">
					<input type="checkbox" class="form-check-input" id="remember" name="remember" >
					<label class="form-check-label" for="remember">Keep logged in</label>
				  </div>
				  <input type="hidden" name="g_token" id="g_token" value=""/>
				  <button type="submit" class="btn btn-primary">Submit</button>
				  <button type="button" class="btn btn-light" onclick="window.location.replace('forgotpassword.php')">Forgot Password</button>
				</form>
				<script src="/js/sha1.min.js"></script>
				<script>
					var username = document.getElementById("username");
					var usernameAlert = document.getElementById("usernameAlert");
					var password = document.getElementById("password");
					var passwordAlert = document.getElementById("passwordAlert");
					var sha1_password = document.getElementById("sha1_password");
					
					function checkUsername(){
						var regex = /^[a-zA-Z0-9_]{4,16}$/;
						if(regex.test(username.value)){
							usernameAlert.innerHTML = "";
							return true;
						}else{
							usernameAlert.innerHTML = "(!)";
							return false;
						}
					}
					function checkPassword(){
						var regex = /^(?=.*[0-9])(?=.*[a-zA-Z])[0-9A-Za-z~!@#$%^&*._?]{6,18}$/;
						if(regex.test(password.value)){
							passwordAlert.innerHTML = "";
							return true;
						}else{
							passwordAlert.innerHTML = "(!)";
							return false;
						}
					}
					grecaptcha.ready(function() {
					    document.getElementById('login_form').addEventListener("submit", function(event) {
					        event.preventDefault();
					        grecaptcha.execute('6Le2tmgaAAAAAD7sUlWs1NicxlBcu-qmEZrGN8Hk', { action: 'login' }).then(function (token) {
					           document.getElementById("g_token").value = token;
							   if(checkUsername() && checkPassword()){
							   	sha1_password.value = sha1(password.value);
							   	document.getElementById('login_form').submit();
							   }
					        });        
					    }, false);
					});
				</script>
			</div>
		</div>
	</div>
</div>
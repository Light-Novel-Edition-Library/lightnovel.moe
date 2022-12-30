<?php 
if(!(isset($_SESSION['uid']))){
	header("Location: /");
}

require_once("config.php");
?>
<h1>Profile</h1>
<div class="row">
	<div class="col-md-4">
		<img style="border-radius: 50%;" src="<?php echo 'https://cravatar.cn/avatar/' .md5(strtolower( trim($_SESSION['email']))).'?d=mp' ?>" />
		<h4><?php 
		if($_SESSION['nick'] == ""){
			echo $_SESSION['username'];
		}else{
			echo $_SESSION['nick'];
		}
		?></h4>
		<p><?php echo $_SESSION['username'] ?></p>
	</div>
	<div class="col-md-8">
		<div class="card mb-3">
			<div class="card-header">Customize</div>
			<div class="card-body">
				<?php 
				if(isset($_POST['nick'])){
					//连接数据库
					$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
					if(!$sql_conn){
						die(mysqli_connect_error());
					}
					$nick = mysqli_real_escape_string($sql_conn, $_POST['nick']);
					$sql_query = "UPDATE ln_auth SET nick='{$nick}' WHERE uid='{$_SESSION['uid']}'";
					mysqli_query($sql_conn, $sql_query);
					$_SESSION['nick'] = $nick;
					//关闭数据库连接
					mysqli_close($sql_conn);
					header("Refresh: 0");
				}
				?>
				<!-- 昵称 -->
				<form class="row" method="post">
					<div class="col-sm-12 col-md-2">
						<label for="nick" class="col-form-label">Nickname</label>
					</div>
					<div class="col-9 col-md-8" style="padding-right: 0px;">
						<input type="text" class="form-control" name="nick" id="nick" maxlength="32" value=<?php echo '"'.$_SESSION['nick'].'"' ?> />
					</div>
					<div class="col-3 col-md-2" style="padding-left: 0px;">
						<input type="submit" class="btn btn-primary" value="Update" />
					</div>
					<span class="form-text">It can be any name you like.</span>
				</form>
				<hr />
				Avatar<br />
				<span class="form-text">Set your avatar in <a href="https://gravatar.com/" target="_blank">Gravatar</a> or <a href="https://cravatar.cn/" target="_blank">Cravatar</a> after binding your email address.</span>
			</div>
		</div>
		<div class="card">
			<div class="card-header">Account</div>
			<div class="card-body">
				<?php 
				if(isset($_POST['username'])){
					//连接数据库
					$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
					if(!$sql_conn){
						die(mysqli_connect_error());
					}
					$id = mysqli_real_escape_string($sql_conn, $_POST['username']);
					$sql_query = "SELECT id FROM ln_auth WHERE id='{$id}'";
					$sql_result = mysqli_query($sql_conn, $sql_query);
					if(mysqli_num_rows($sql_result) == 0){
						$sql_query = "UPDATE ln_auth SET id='{$id}' WHERE uid='{$_SESSION['uid']}'";
						mysqli_query($sql_conn, $sql_query);
						//关闭数据库连接
						mysqli_close($sql_conn);
						$_SESSION['username'] = $id;
						header("Refresh: 0");
					}else{
						//关闭数据库连接
						mysqli_close($sql_conn);
						echo '<div class="alert alert-danger alert-dismissible">';
						echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
						echo '<strong>Your new username has been used!</strong><br />';
						echo 'Please choose another new username.</div>';
					}
				}
				?>
				
				<!-- 用户名 -->
				<form class="row" method="post" id="username_form">
					<div class="col-sm-12 col-md-2">
						<label for="username" class="col-form-label">Username</label>
					</div>
					<div class="col-9 col-md-8" style="padding-right: 0px;">
						<input type="text" class="form-control" name="username" id="username" maxlength="16" onkeyup="checkUsername()"
						 value=<?php echo '"'.$_SESSION['username'].'"' ?> />
					</div>
					<div class="col-3 col-md-2" style="padding-left: 0px;">
						<input type="submit" class="btn btn-primary" value="Update" />
					</div>
					<span class="form-text"><span class="text-danger" id="usernameAlert"></span>This is the username used to log in. Please modify it carefully. Only English letters, numbers and underscores are supported, with a length of 4 to 16 characters.</span>
				</form>
				<script>
					var username = document.getElementById("username");
					var usernameAlert = document.getElementById("usernameAlert");
					
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
					document.getElementById('username_form').addEventListener("submit", function(event) {
					    event.preventDefault();
						if(checkUsername()){
							document.getElementById('username_form').submit();
					    }
					}, false);
				</script>
				<hr />
				<?php 
				if(isset($_POST['old_pass']) && isset($_POST['new_pass'])){
					//连接数据库
					$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
					if(!$sql_conn){
						die(mysqli_connect_error());
					}
					$old_pass = md5("light".$_POST['old_pass']."novel");
					$new_pass = md5("light".$_POST['new_pass']."novel");
					$sql_query = "SELECT password FROM ln_auth WHERE uid='{$_SESSION['uid']}'";
					$sql_result = mysqli_query($sql_conn, $sql_query);
					$sql_row = mysqli_fetch_assoc($sql_result);
					if($old_pass == $sql_row['password']){
						$sql_query = "UPDATE ln_auth SET password='{$new_pass}' WHERE uid='{$_SESSION['uid']}'";
						mysqli_query($sql_conn, $sql_query);
						echo '<div class="alert alert-success alert-dismissible">';
						echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
						echo '<strong>Password Updated!</strong>';
						echo '</div>';
						echo '<script>window.history.replaceState(null, null, window.location.href);</script>';
					}else{
						echo '<div class="alert alert-danger alert-dismissible">';
						echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
						echo '<strong>The old password was entered incorrectly!</strong>';
						echo '</div>';
					}
					//关闭数据库连接
					mysqli_close($sql_conn);
				}
				?>
				<p>Change Password</p>
				<form method="post" id="password_form">
					<div class="row">
						<label class="col-sm-3 col-form-label" for="old_pass">Old Password</label>
						<div class="col-sm-9 mb-1">
							<input type="password" id="old_pass" class="form-control" />
							<input type="hidden" name="old_pass" id="sha1_old" />
						</div>
						<label class="col-sm-3 col-form-label" for="new_pass">New Password</label>
						<div class="col-sm-9 mb-1">
							<input type="password" id="new_pass" class="form-control" onkeyup="checkPassword()" />
							<input type="hidden" name="new_pass" id="sha1_new" />
						</div>
						<label class="col-sm-3 col-form-label" for="con_pass">Confirm Password</label>
						<div class="col-sm-9 mb-1">
							<input type="password" id="con_pass" class="form-control" onkeyup="checkPassword()" />
						</div>
						<span class="form-text"><span class="text-danger" id="passwordAlert"></span>The password must contain English letters, numbers, can have special characters (~!@#$%^&*._?), and the length is 6 to 18 characters. The new password and the confirmed password must be the same.</span>
					</div>
					<input type="submit" class="btn btn-primary" value="Update" />
				</form>
				<hr />
				<!-- 邮箱 -->
				<?php
				if(isset($_POST['email'])){
					//连接数据库
					$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
					if(!$sql_conn){
						die(mysqli_connect_error());
					}
					$email = mysqli_real_escape_string($sql_conn, $_POST['email']);
					$sql_query = "UPDATE ln_auth SET email='{$email}' WHERE uid='{$_SESSION['uid']}'";
					mysqli_query($sql_conn, $sql_query);
					$_SESSION['email'] = $email;
					//关闭数据库连接
					mysqli_close($sql_conn);
					echo '<div class="alert alert-success alert-dismissible">';
					echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
					echo '<strong>Email Updated!</strong>';
					echo '</div>';
				}
				?>
				<form id="email_form" class="row" method="post">
					<div class="col-sm-12 col-md-2">
						<label for="email" class="col-form-label">Email</label>
					</div>
					<div class="col-9 col-md-8" style="padding-right: 0px;">
						<input type="email" class="form-control" name="email" id="email" maxlength="32" value=<?php echo '"'.$_SESSION['email'].'"' ?> />
					</div>
					<div class="col-3 col-md-2" style="padding-left: 0px;">
						<input type="submit" class="btn btn-primary" value="Update" />
					</div>
				</form>
				<script src="../js/sha1.min.js"></script>
				<script>
					var old_pass = document.getElementById("old_pass");
					var new_pass = document.getElementById("new_pass");
					var con_pass = document.getElementById("con_pass");
					var sha1_old = document.getElementById("sha1_old");
					var sha1_new = document.getElementById("sha1_new");
					
					function checkPassword(){
						var regex = /^(?=.*[0-9])(?=.*[a-zA-Z])[0-9A-Za-z~!@#$%^&*._?]{6,18}$/;
						if(regex.test(new_pass.value) && con_pass.value==new_pass.value){
							passwordAlert.innerHTML = "";
							return true;
						}else{
							passwordAlert.innerHTML = "(!)";
							return false;
						}
					}
					document.getElementById('password_form').addEventListener("submit", function(event) {
					    event.preventDefault();
						if(checkPassword()){
							sha1_old.value = sha1(old_pass.value);
							sha1_new.value = sha1(new_pass.value);
							document.getElementById('password_form').submit();
					    }
					}, false);
				</script>
			</div>
		</div>
	</div>
</div>
<?php 

?>
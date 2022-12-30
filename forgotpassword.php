<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Forgot Password - Light Novel Edition Library</title>
		<script src="https://www.recaptcha.net/recaptcha/api.js?render=6Le2tmgaAAAAAD7sUlWs1NicxlBcu-qmEZrGN8Hk"></script>
		<script src="./js/sha1.min.js"></script>
	</head>
	<body>
		<h1>Reset Password</h1>
		<form id="reset_form">
			<label for="username">Username</label>
			<input type="text" id="username" name="username" required="required" />
			<br />
			<input type="button" id="sendmail_button" value="Send Verification Code to the Bound Mailbox" onclick="sendmail()" />
			<br />
			<label for="code">Code</label>
			<input type="number" id="code" maxlength="6" required="required" />
			<br />
			<label for="password">New Password</label>
			<input id="password" type="password" required="required" onkeyup="checkPassword()" /><span class="text-danger" id="passwordAlert"></span><span class="form-text" id="passwordHelp">The password must contain English letters, numbers, can have special characters (~!@#$%^&*._?), and the length is 6 to 18 characters.</span>
			<input type="hidden" id="sha1_password" />
			<br />
			<input id="submit" type="button" value="Reset Password" onclick="changepassword()" />
			<br />
			<span id="email_status"></span>
		</form>
		<script>
			var password = document.getElementById("password");
			var passwordAlert = document.getElementById("passwordAlert");
			
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
			
			function sendmail(){
				document.getElementById('sendmail_button').disabled = true;
				document.getElementById('email_status').innerText ='Wait...';
				grecaptcha.ready(function() {
				          grecaptcha.execute('6Le2tmgaAAAAAD7sUlWs1NicxlBcu-qmEZrGN8Hk', {action: 'submit'}).then(function(token) {
				              // Add your logic to submit to your backend server here.
							  var xmlhttp = new XMLHttpRequest();
							  xmlhttp.open('POST', 'resetpassword.php', false);
							  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
							  xmlhttp.send('id=' + document.getElementById('username').value + '&g_token=' + token);
							  document.getElementById('email_status').innerText = xmlhttp.responseText;
				          });
				        });
			}
			function changepassword(){
				grecaptcha.ready(function() {
				          grecaptcha.execute('6Le2tmgaAAAAAD7sUlWs1NicxlBcu-qmEZrGN8Hk', {action: 'submit'}).then(function(token) {
				              // Add your logic to submit to your backend server here.
							  if(checkPassword()){
								  var xmlhttp = new XMLHttpRequest();
								  xmlhttp.open('POST', 'resetpassword.php', false);
								  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
								  document.getElementById('sha1_password').value = sha1(document.getElementById('password').value);
								  xmlhttp.send('code=' + document.getElementById('code').value + '&password=' + document.getElementById('sha1_password').value+ '&g_token=' + token);
								  document.getElementById('email_status').innerText = xmlhttp.responseText;
							  }
							  
				          });
				        });
			}
		</script>
	</body>
</html>

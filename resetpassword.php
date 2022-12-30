<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/PHPMailer-6.6.0/src/Exception.php';
require 'lib/PHPMailer-6.6.0/src/PHPMailer.php';
require 'lib/PHPMailer-6.6.0/src/SMTP.php';


session_start();

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
	if(isset($_POST['id'])){
		
		
		$recaptcha_json_result = checkvali($_POST['g_token']);
		$g_result = json_decode($recaptcha_json_result,true);
		if(isset($g_result['success']) && $g_result['success']==true){
			if(isset($g_result['score']) && $g_result['score']>0.5){
				require_once("var/config.php");
				//连接数据库
				$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
				if(!$sql_conn){
					die(mysqli_connect_error());
				}
				$id = mysqli_real_escape_string($sql_conn, $_POST['id']);
				$_SESSION['id'] = $id;
				$sql_query = "SELECT nick,email FROM ln_auth WHERE id='{$id}'";
				$sql_result = mysqli_query($sql_conn, $sql_query);
				if(mysqli_num_rows($sql_result) == 0){
					echo 'User does not exist!';
				}else{
					$sql_row = mysqli_fetch_assoc($sql_result);
					$nickname = $sql_row['nick'];
					$email = $sql_row['email'];
					if($email == ''){
						die('Email is not bound!');
					}
					$_SESSION['code'] = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
					
					function sendmail($email, $nickname){
						
						
						
						
						//Create an instance; passing `true` enables exceptions
						$mail = new PHPMailer(true);
						try {
							//Server settings
							$mail->isSMTP();                                            //Send using SMTP
							$mail->CharSet ="UTF-8";
							$mail->Host       = 'smtp.yandex.com';                     //Set the SMTP server to send through
							$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
							$mail->Username   = rand(0,9).'@zimoe.com';                     //SMTP username
							$mail->Password   = '';                               //SMTP password
							$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
							$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
						
							//Recipients
							$mail->setFrom($mail->Username, '轻版馆');
							$mail->addAddress($email, $nickname);     //Add a recipient
						
							//Content
							$mail->isHTML(true);                                  //Set email format to HTML
							$mail->Subject = 'Your Verification Code from Light Novel Edition Library';
							$mail->Body    = '<p>Dear '.$nickname.',</p><p>Your Verification Code in Light Novel Edition Library is:<br />您在輕小說版本圖書館的驗證碼是：</p><p>'.$_SESSION['code'].'</p><p>Please do not disclose it to someone you cannot trust.<br />請勿將其透露給您無法信任的人。</p>';
						
							$mail->send();
							echo 'Message has been sent';
						} catch (Exception $e) {
							echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
						}
						
					}
					sendmail($email, $nickname);
				}
				mysqli_close($sql_conn);
			}else{
				echo 'failed';
			}
		}else{
			echo 'failed';
		}
	}
	if(isset($_POST['code'])){
		$recaptcha_json_result = checkvali($_POST['g_token']);
		$g_result = json_decode($recaptcha_json_result,true);
		if(isset($g_result['success']) && $g_result['success']==true){
			if(isset($g_result['score']) && $g_result['score']>0.5){
				if($_POST['code'] == $_SESSION['code']){
					$_SESSION['code'] = '';
					require_once("var/config.php");
					//连接数据库
					$sql_conn = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_db);
					if(!$sql_conn){
						die(mysqli_connect_error());
					}
					$new_pass = md5("light".$_POST['password']."novel");
					$sql_query = "UPDATE ln_auth SET password='{$new_pass}' WHERE id='{$_SESSION['id']}'";
					mysqli_query($sql_conn, $sql_query);
					mysqli_close($sql_conn);
					echo 'Password Changed!';
				}else{
					$_SESSION['code'] = '';
					echo 'Wrong code, please refresh this page and get a new one.';
				}
				
			}else{
				echo 'failed';
			}
		}else{
			echo 'failed';
		}
	}
}
?>
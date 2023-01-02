<?php
namespace app\controller;

use app\BaseController;
use app\validate\User as UserValidate;
use app\validate\Captcha;
use think\exception\ValidateException;
use think\facade\Db;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Ramsey\Uuid\Uuid;

class User extends BaseController
{
    public function login(){
        $userInvalid = false; // 用户名和密码格式验证失败标志
        $captchaInvalid = false;
        $passwordWrong = false;
        
        function keepLogin(){
            if(input('?post.remember') && input('post.remember')=='on'){
                $uuid = Uuid::uuid4()->toString();
                Db::execute('INSERT INTO ln_tokens (uid, uuid, login_ip, user_agent) VALUES (:uid, :uuid, :login_ip, :user_agent)', [
                    'uid' => session('uid'),
                    'uuid' => $uuid,
                    'login_ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                ]);
                cookie('uuid', $uuid, 60*60*24*30);
            }
        }
        
        function directFrom(){
            if(input('?get.from') && input('get.from')!=''){
                return redirect(input('get.from'));
            }else{
                return redirect('/');
            }
        }
        
        if(session('?uid')){
            return directFrom();
        }
        if(input('?post.username') && input('?post.password')){
            try{
                validate(UserValidate::class)->scene('login')->check([
                    'username' => input('post.username'),
                    'password' => input('post.password')
                ]);
                if(input('?post.g_token')){
                    try{
                        validate(Captcha::class)->scene('recaptcha')->check([
                            'g_token' => input('post.g_token')
                        ]);
                        $users = Db::query('SELECT uid, password, nickname FROM ln_users WHERE username=:username', ['username' => input('post.username')]);
                        if(count($users) == 0){
                            Db::execute('INSERT INTO ln_users (username, password) VALUES (:username, :password)', [
                                'username' => input('post.username'),
                                'password' => password_hash(input('post.password'), PASSWORD_BCRYPT)
                            ]);
                            $users = Db::query('SELECT uid, nickname FROM ln_users WHERE username=:username', ['username' => input('post.username')]);
                            session('uid', $users[0]['uid']);
                            session('username', input('post.username'));
                            session('nickname', htmlspecialchars($users[0]['nickname']));
                            keepLogin();
                            return directFrom();
                        }else{
                            if(password_verify(input('post.password'), $users[0]['password'])){
                                session('uid', $users[0]['uid']);
                                session('username', input('post.username'));
                                session('nickname', htmlspecialchars($users[0]['nickname']));
                                keepLogin();
                                return directFrom();
                            }else{
                                $passwordWrong = true;
                            }
                        }
                    }catch(ValidateException $e){
                        $captchaInvalid = true;
                    }
                }else{
                    $captchaInvalid = true;
                }
            }catch(ValidateException $e){
                $userInvalid = true;
            }
        }
        return view('/login', [
            'title' => '登录/注册',
            'userInvalid' => $userInvalid,
            'captchaInvalid' => $captchaInvalid,
            'passwordWrong' => $passwordWrong
        ]);
    }
    
    public function logout(){
        session(null);
        if(cookie('?uuid')){
            Db::execute('DELETE FROM ln_tokens WHERE uuid=:uuid', ['uuid' => cookie('uuid')]);
            cookie('uuid', null);
        }
        return redirect('/');
    }
    
    public function reset(){
        if(input('?post.g_token')){
            try{
                validate(Captcha::class)->scene('recaptcha')->check([
                    'g_token' => input('post.g_token')
                ]);
                if(input('?post.username')){
                    $users = Db::query('SELECT email, nickname FROM ln_users WHERE username=:username', ['username' => input('post.username')]);
                    if(empty($users)){
                        return response('USER_NOT_FOUND')->contentType('text/plain');
                    }else if($users[0]['email']!=null && $users[0]['email']!=''){
                        session('username', input('post.username'));
                        session('code', rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));
                        session('code_expires', strtotime('+10 minutes'));
                        if($users[0]['nickname'] != ''){
                            $nickname = $users[0]['nickname'];
                        }else{
                            $nickname = session('username');
                        }
                        //Create an instance; passing `true` enables exceptions
                        $mail = new PHPMailer(true);
                        try {
                            //Server settings
                            $mail->isSMTP();                                            //Send using SMTP
                            $mail->CharSet = "UTF-8";
                            $mail->Host       = 'smtp.yandex.com';                     //Set the SMTP server to send through
                            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                            $mail->Username   = rand(0,9).'@lightnovel.moe';                     //SMTP username
                            $mail->Password   = env('email.password', '');                               //SMTP password
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                            $mail->Port       = 465; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                        
                            //Recipients
                            $mail->setFrom($mail->Username, '轻版馆');
                            $mail->addAddress($users[0]['email'], $nickname);     //Add a recipient
                        
                            //Content
                            $mail->isHTML(true);                                  //Set email format to HTML
                            $mail->Subject = 'Your Verification Code from Light Novel Edition Library';
                            $mail->Body    = '<p>Dear '.$nickname.',</p><p>Your Verification Code in Light Novel Edition Library is:<br />您在輕小說版本圖書館的驗證碼是：</p><p>'.session('code').'</p><p>Valid for 10 minutes.<br />10分鐘內有效。</p><p>Please do not disclose it to someone you cannot trust.<br />請勿將其透露給您無法信任的人。</p>';
                        
                            $mail->send();
                            return response('CODE_MAIL_SENT')->contentType('text/plain');
                        } catch (PHPMailerException $e) {
                            return response('MAIL_NOT_SENT')->contentType('text/plain');
                        }
                    }else{
                        return response('EMAIL_NOT_SET')->contentType('text/plain');
                    }
                }else if(input('?post.code') && input('?post.password')){
                    try{
                        validate(UserValidate::class)->scene('changePassword')->check([
                            'password' => input('post.password')
                        ]);
                        if(session('?code')){
                            if(time() < session('code_expires')){
                                if(input('post.code') == session('code')){
                                    Db::execute('UPDATE ln_users SET password=:password WHERE username=:username', [
                                        'username' => session('username'),
                                        'password' => password_hash(input('post.password'), PASSWORD_BCRYPT)
                                    ]);
                                    return response('PASSWORD_CHANGED')->contentType('text/plain');
                                }else{
                                    return response('CODE_WRONG')->contentType('text/plain');
                                }
                            }else{
                                return response('CODE_EXPIRED')->contentType('text/plain');
                            }
                        }else{
                            return response('CODE_NOT_EXIST')->contentType('text/plain');
                        }
                    }catch(ValidateException $e){
                        return response('PASSWORD_INVALID')->contentType('text/plain');
                    }
                }
            }catch(ValidateException $e){
                return response('CAPTCHA_ERROR')->contentType('text/plain');
            }
        }else{
            return response('CAPTCHA_ERROR')->contentType('text/plain');
        }
    }
    
    public function updateOneWholly($uid){
        if($uid != session('uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }
        if(input('?username') && input('?email') && input('?nickname') && input('?bio')){
            try{
                validate(UserValidate::class)->scene('updateByPut')->check([
                    'username' => input('username'),
                    'email' => input('email'),
                    'nickname' => input('nickname'),
                    'bio' => input('bio')
                ]);
            }catch(ValidateException $e){
                return json(['message' => $e->getMessage()], 422);
            }
            $users = Db::query('SELECT username FROM ln_users WHERE uid=:uid', ['uid' => $uid]);
            if(empty($users)){
                return json(['message' => 'USER_NOT_FOUND'], 404);
            }
            if(input('username') != $users[0]['username']){
                $users = Db::query('SELECT uid FROM ln_users WHERE username=:username', ['username' => input('username')]);
                if(!empty($users)){
                    return json(['message' => 'USERNAME_USED'], 409);
                }
            }
            Db::execute('UPDATE ln_users SET username=:username, email=:email, nickname=:nickname, bio=:bio WHERE uid=:uid', [
                'username' => input('username'),
                'email' => input('email'),
                'nickname' => input('nickname'),
                'bio' => input('bio'),
                'uid' => $uid
            ]);
            if(session('uid') == $uid){
                session('username', input('username'));
                session('nickname', htmlspecialchars(input('nickname')));
            }
            return json(input(''), 200);
        }else{
            return json(['message' => 'BAD_REQUEST'], 400);
        }
    }
    
    public function updateOnePartially($uid){
        if($uid != session('uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }
        if(input('?password')){
            try{
                validate(UserValidate::class)->scene('changePassword')->check([
                    'password' => input('password')['new']
                ]);
            }catch(ValidateException $e){
                return json(['message' => $e->getMessage()], 422);
            }
            $users = Db::query('SELECT password FROM ln_users WHERE uid=:uid', ['uid' => $uid]);
            if(empty($users)){
                return json(['message' => 'USER_NOT_FOUND'], 404);
            }
            if(password_verify(input('password')['old'], $users[0]['password'])){
                Db::execute('UPDATE ln_users SET password=:password WHERE uid=:uid', [
                    'password' => password_hash(input('password')['new'], PASSWORD_BCRYPT),
                    'uid' => $uid
                ]);
                return json([
                    'uid' => $uid,
                    'message' => 'PASSWORD_CHANGED'
                ], 200);
            }else{
                return json(['message' => 'PASSWORD_WRONG'], 422);
            }
        }else{
            return json(['message' => 'BAD_REQUEST'], 400);
        }
    }
}
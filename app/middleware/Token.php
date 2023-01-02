<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Db;

class Token
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        if(!session('?uid') && cookie('?uuid')){
            $users = Db::query('SELECT uid, username, nickname FROM ln_users WHERE uid=(SELECT uid FROM ln_tokens WHERE uuid=:uuid)', [
                'uuid' => cookie('uuid')
            ]);
            if(!empty($users)){
                session('uid', $users[0]['uid']);
                session('username', $users[0]['username']);
                session('nickname', htmlspecialchars($users[0]['nickname']));
                Db::execute('UPDATE ln_tokens SET login_ip=:login_ip, user_agent=:user_agent, login_time=CURRENT_TIMESTAMP WHERE uuid=:uuid', [
                    'login_ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'uuid' => cookie('uuid')
                ]);
                cookie('uuid', cookie('uuid'), 60*60*24*30);
            }else{
                cookie('uuid', null);
            }
        }
        
        return $next($request);
    }
}

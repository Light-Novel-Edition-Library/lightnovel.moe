<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Token extends BaseController{
    public function readAll(){
        if(!input('?get.uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }else if(input('?get.uid') && input('get.uid')!=session('uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }
        $tokens = Db::query('SELECT uid, uuid, login_ip, user_agent, login_time FROM ln_tokens WHERE uid=:uid', ['uid' => input('get.uid')]);
        return json($tokens, 200);
    }
    
    public function deleteOne($uuid){
        $tokens = Db::query('SELECT uid FROM ln_tokens WHERE uuid=:uuid', ['uuid' => $uuid]);
        if(empty($tokens)){
            return json(['message' => 'TOKEN_NOT_FOUND'], 404);
        }
        if($tokens[0]['uid'] != session('uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }
        Db::execute('DELETE FROM ln_tokens WHERE uuid=:uuid', ['uuid' => $uuid]);
        return json([], 204);
    }
    
    public function deleteAll(){
        if(!input('?get.uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }else if(input('?get.uid') && input('get.uid')!=session('uid')){
            return json(['message' => 'FORBIDDEN'], 403);
        }
        Db::execute('DELETE FROM ln_tokens WHERE uid=:uid', ['uid' => input('get.uid')]);
        return json([], 204);
    }
}
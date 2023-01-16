<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use think\facade\Db;
use lightnovel\Tool;

Route::rule('/', function(){
    return view('home', ['title' => '首页']);
});

Route::group('user', function(){
    Route::rule('login', 'User/login');
    Route::rule('logout', 'User/logout');
    
    Route::get('reset', function(){
        if(session('?uid')){
            return redirect('/');
        }
        return view('/reset', ['title' => '重置密码']);
    });
    Route::post('reset', 'User/reset');
    
    Route::rule('profile', function(){
        $users = Db::query('SELECT email, bio, register_time FROM ln_users WHERE uid=:uid', ['uid' => session('uid')]);
        return view('/profile', [
            'title' => '用户资料',
            'email' => $users[0]['email'],
            'bio' => $users[0]['bio'],
            'register_time' => Tool::formatClientTime($users[0]['register_time'])
        ]);
    })->middleware('guard');
    
    Route::put(':uid$', 'User/updateOneWholly')->pattern(['uid' => '\d+'])->middleware('guard', true);
    Route::patch(':uid$', 'User/updateOnePartially')->pattern(['uid' => '\d+'])->middleware('guard', true);
});

Route::group('token', function(){
    Route::get('', 'Token/readAll')->middleware('guard', true);
    Route::delete(':uuid$', 'Token/deleteOne')->pattern(['uuid' => '[A-Za-z0-9\-]{36}'])->middleware('guard', true);
    Route::delete('', 'Token/deleteAll')->middleware('guard', true);
});

Route::miss(function(){
    return view('/404', ['title' => '页面不存在'], 404);
});
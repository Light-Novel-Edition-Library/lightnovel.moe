<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'username' => 'require|regex:/^[a-zA-Z0-9_]{4,16}$/',
        'password' => 'require|length:6,18',
        'email' => 'email|max:128',
        'nickname' => 'max:32',
        'bio' => 'max:255'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'username' => 'USERNAME_INVALID',
        'password' => 'PASSWORD_INVALID',
        'email' => 'EMAIL_INVALID',
        'nickname' => 'NICKNAME_INVALID',
        'bio' => 'BIO_INVALID'
    ];
    
    protected $scene = [
        'login' => ['username', 'password'],
        'changePassword' => ['password'],
        'updateByPut' => ['username', 'email', 'nickname', 'bio']
    ];
}

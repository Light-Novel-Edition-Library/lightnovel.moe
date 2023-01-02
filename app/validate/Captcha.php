<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class Captcha extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'g_token' => 'require|recaptcha'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];
    
    protected $scene = [
        'recaptcha' => ['g_token']
    ];
    
    protected function recaptcha($value){
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ],
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => env('recaptcha.secret_key', ''),
                    'response' => $value
                ])
            ]
        ]);
        $response = file_get_contents('https://www.recaptcha.net/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response, true);
        if(isset($result['success']) && $result['success'] && isset($result['score']) && $result['score']>0.5){
            return true;
        }else{
            return '无法通过人机验证';
        }
    }
}

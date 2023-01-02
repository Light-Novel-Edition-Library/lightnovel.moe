<?php
declare (strict_types = 1);

namespace app\middleware;

class Guard
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next, $api=false)
    {
        if($api){
            if(!session('?uid')){
                return json(['message' => 'UNAUTHORIZED'], 401);
            }
        }else{
            if(!session('?uid')){
                return redirect('/user/login?from='.request()->baseUrl());
            }
        }
        
        return $next($request);
    }
}

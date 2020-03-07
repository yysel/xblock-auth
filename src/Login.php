<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-9-30
 * Time: 下午3:41
 */

namespace XBlock\Auth;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Login
{
    protected $server;

    public function __construct()
    {
        $this->server = new AuthService();
    }

    public function index(Request $request)
    {
        $model = $this->server::userModel();
        $user = null;
        $user_modal = new $model;
        $key = $user_modal->getKeyName();
        if (method_exists($model, 'login')) {
            $user = $user_modal->login($request);
        } else {
            $user = $this->checkUser($user_modal, $request);
        }
        if (!($user instanceof $user_modal)) return $user;
        $token = $this->server->createToken($user->{$key});
        return message(true)->data((string)$token);
    }

    public function checkUser($user_modal, $request)
    {
        $user = $user_modal->where('username', $request->input('username'))->first();
        if ($user) {
            return $this->checkPassword($request->input('password'), $user->password) ? $user : message(false, '密码错误!');
        }
        return message(false, '账号不存在!');
    }

    protected function checkPassword($password, $origin)
    {
        return Hash::check($password, $origin) ? $this : false;
    }

    public function getLoginUser(Request $request)
    {
        $user = Auth::user();
        if (!$user) return message(false);
        if (method_exists($user, 'loginUser')) {
            $user = $user->loginUser($request);
        }
        $user->permission = $user->permission;

        return message(true)->data($user);
    }
}
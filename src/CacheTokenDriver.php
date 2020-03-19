<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-19
 * Time: 下午8:54
 */

namespace XBlock\Auth;


use Illuminate\Support\Facades\Cache;

class CacheTokenDriver
{
    protected $prefix = 'xblock-auth-token';

    public $id;
    public $user_id;
    public $expires_at;

    public function getUser(string $token_id)
    {
        $data = Cache::get($this->prefix . $token_id);
        if (!$data) return null;
        $userModel = AuthService::userModel();
        return (new $userModel)->find($data['user_id']);
    }

    public function create(Array $data)
    {
        if (isset($data['id']) && isset($data['user_id'])) {
            $expires = strtotime($data['expires_at']) - time();
            if ($expires > 0) Cache::put($this->prefix . $data['id'], $data, $expires);
            else Cache::forever($this->prefix . $data['id'], $data);
            return true;
        }
        return null;
    }
}
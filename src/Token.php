<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-9-30
 * Time: 下午1:59
 */

namespace XBlock\Auth;


use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'oauth_token';
    protected $fillable = ['user_id', 'id', 'expires_at'];
    public $timestamps = false;
    public $incrementing = false;

    public function getUser($token_id)
    {
        $token = $this->find($token_id);
        if (!$token) return null;
        if (($token->expires_at && strtotime($token->expires_at) < time())) return null;
        $userModel = AuthService::userModel();
        return (new $userModel)->find($token->user_id);
    }
}
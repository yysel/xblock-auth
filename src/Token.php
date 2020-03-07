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
    protected $fillable = ['user_id', 'id', 'client_id'];

    public $incrementing = false;

    public function user()
    {
        $userModel = AuthService::userModel();
        return $this->belongsTo($userModel, 'user_id', (new $userModel)->getKeyName());
    }
}
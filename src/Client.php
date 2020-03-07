<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-12-3
 * Time: 下午7:47
 */

namespace XBlock\Auth;


use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'oauth_client';
}
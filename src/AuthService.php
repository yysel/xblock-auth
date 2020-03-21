<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-9-30
 * Time: 上午9:42
 */

namespace XBlock\Auth;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Keychain;

class AuthService
{
    private $signer;
    private $keychain;
    private $builder;

    public function __construct()
    {
        $this->signer = new Sha256();
        $this->keychain = new Keychain();
        $this->builder = new Builder();
    }

    public function createSecretString()
    {
        $config = ["private_key_bits" => 4096, "private_key_type" => OPENSSL_KEYTYPE_RSA];
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        return compact('public_key', 'private_key');
    }

    /**
     * @param String|null $path 秘钥存放地址
     */
    public function createSecretFile(String $path = null)
    {
        if (!$path) $path = storage_path();
        $keys = $this->createSecretString();
        $this->writeStringToFile($path . '/public.key', $keys['public_key']);
        $this->writeStringToFile($path . '/private.key', $keys['private_key']);
    }

    protected function writeStringToFile($name, $content)
    {
        $file = fopen($name, $mode = 'w');
        fwrite($file, $content);
    }

    public function createToken($user_uuid = '')
    {
        $id = guid();
        openssl_private_encrypt($id, $private_token, file_get_contents(storage_path('private.key')));
        $private_token = base64_encode($private_token);
        $this->builder->setId(md5(time()), true);
//        $builder->setNotBefore(time() + 60);
//        $builder->setExpiration(time() + 3600);
        $this->builder->set('uid', $private_token);
        $this->builder->sign($this->signer, $this->keychain->getPrivateKey('file://' . storage_path('private.key')));
        $token = $this->builder->getToken();
        if ($token) {
            $res = $this->getTokenDriver()->create([
                'id' => $id,
                'user_id' => $user_uuid,
                'expires_at' => $this->getExpires()
            ]);
            if (!$res) return false;
        }
        return $token;
    }

    public function parseToken(String $token)
    {
        try {
            $parse = new \Lcobucci\JWT\Parser();
            $token = $parse->parse($token);
            if (!$token->verify($this->signer, $this->keychain->getPublicKey('file://' . storage_path('private.key')))) return false;
            $uid = $token->getClaim('uid');
            $private_token = base64_decode($uid);
            openssl_public_decrypt($private_token, $id, file_get_contents(storage_path('public.key')));
            return $id;
        } catch (\Exception $exception) {
            return null;
        }

    }

    public function getUserFormParseBearerToken($request)
    {
        $bearer_token = $request->header('authorization');
        $token_array = explode(' ', $bearer_token);
        if (count($token_array) != 2) return null;
        $bearer_token = $token_array[1];
        $uid = $this->parseToken($bearer_token);
        if($uid) return $this->getTokenDriver()->getUser($uid);
        return null;
    }

    public static function userModel()
    {
        $default = config('auth.defaults.guard');
        return config('auth.guards.' . $default . '.model', \App\User::class);
    }


    public function getExpires()
    {
        $expires = config('auth.providers.xblock.expires', null);
        return $expires > 0 ? date('Y-m-d H:i:s', time() + $expires * 60) : null;
    }

    public function getTokenDriver()
    {
        $driver = config('auth.providers.xblock.driver', 'cache');
        if ($driver === 'database') {
            $model = config('auth.providers.xblock.model', Token::class);
            if (!$model) return new Token();
            if (class_exists($model)) {
                $model = new $model;
                if ($model instanceof Token) return (new $model);
                else throw new \Exception('Token模型应继承自\XBlock\Auth\Token');
            }
        } else {
            return new CacheTokenDriver();
        }


    }
}
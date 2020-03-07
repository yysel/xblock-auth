<?php

namespace XBlock\Auth;


use Illuminate\Console\Command;

class CreateKey extends Command
{
    protected $signature = 'xblock:key';
    protected $comment = 'db';
    protected $description = '创建加密密钥对';

    public function handle()
    {
        (new AuthService())->createSecretFile();
        $this->info('秘钥创建成功！');
    }


    public function out($str)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return $str;
        return iconv("UTF-8", "GBK", $str);
    }

    public function in($str)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') return $str;
        return iconv("GBK", "UTF-8", $str);
    }

}

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

}

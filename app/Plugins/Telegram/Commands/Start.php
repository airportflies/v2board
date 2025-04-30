<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class Bind extends Telegram {
    public $command = '/start';
    public $description= '启动bot';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;
        $telegramService = $this->telegramService;
        $telegramService->sendMessage($message->chat_id, '/start 显示帮助消息\n/bind 将Telegram账号绑定到网站 用法为 /bind 您的订阅网址\n/unbind 解绑Telegram账号\n/getlatesturl 显示最新机场地址\n/traffic 查询流量信息');
    }
}
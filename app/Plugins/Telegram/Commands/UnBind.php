<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;

class UnBind extends Telegram {
    public $command = '/unbind';
    public $description = '解绑Telegram账号';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;
        if (!isset($message->args[0])) {
            $user = User::where('telegram_id', $message->chat_id)->first();
        } else {
            $chat = User::where('telegram_id', $message->chat_id)->first();
            if (!$chat) return;
            if (!($chat->is_admin || $chat->is_staff)) return;
            if (strpos($message->args[0], '@') !== true) {
                $user = User::where('email', $message->args[0])->first();
            } else {
                $user = User::where('telegram_id', $message->args[0])->first();
            }
        }
        $telegramService = $this->telegramService;
        if (!$user) {
            $telegramService->sendMessage($message->chat_id, '没有查询到您的用户信息，请先绑定账号', 'markdown');
            return;
        }
        $user->telegram_id = NULL;
        if (!$user->save()) {
            abort(500, '解绑失败');
        }
        $telegramService->sendMessage($message->chat_id, '解绑成功', 'markdown');
    }
}

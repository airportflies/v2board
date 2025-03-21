<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function notify($method, $uuid, Request $request)
    {
        try {
            $paymentService = new PaymentService($method, null, $uuid);
            $verify = $paymentService->notify($request->input());
            if (!$verify) abort(500, 'verify error');
            if (!$this->handle($verify['trade_no'], $verify['callback_no'])) {
                abort(500, 'handle error');
            }
            return(isset($verify['custom_result']) ? $verify['custom_result'] : 'success');
        } catch (\Exception $e) {
            abort(500, 'fail');
        }
    }

    private function handle($tradeNo, $callbackNo)
    {
        $order = Order::where('trade_no', $tradeNo)->first();
        if (!$order) {
            abort(500, 'order is not found');
        }
        if ($order->status !== 0) return true;
        $orderService = new OrderService($order);
        if (!$orderService->paid($callbackNo)) {
            return false;
        }
        $payment = Payment::where('id', $order->payment_id)->first();
        $user = User::where('id', $order->user_id)->first();
        
        $telegramService = new TelegramService();
        $message = sprintf(
          "💰成功收款%s元\n———————————————\n用户邮箱：`%s`\n支付接口：%s\n支付渠道：%s\n订单号：`%s`",
          $order->total_amount / 100,
          $user->email ?? '未知邮箱',
          $payment->payment ?? '未知接口',
          $payment->name ?? '未知渠道',
          $order->trade_no
        );
        $telegramService->sendMessageWithAdmin($message);
        return true;
    }
}

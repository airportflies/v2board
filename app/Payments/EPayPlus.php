<?php

namespace App\Payments;

class EPayPlus {
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function form()
    {
        return [
            'url' => [
                'label' => 'API网关',
                'description' => '',
                'type' => 'input',
            ],
            'pid' => [
                'label' => '商户PID',
                'description' => '',
                'type' => 'input',
            ],
            'key' => [
                'label' => '商户KEY',
                'description' => '',
                'type' => 'input',
            ],
            'type' => [
                'label' => '支付方式',
                'description' => '',
                'type' => 'input',
            ]
        ];
    }

    public function pay($order)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $uri = $protocol . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
        if ( $order['notify_url'] == "http://a.com") {
            $notify_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $notify_url = $order['notify_url'];
        }
        $params = [
            'money' => $order['total_amount'] / 100,
            'name' => $order['trade_no'],
            'notify_url' => $notify_url,
            'return_url' => $order['return_url'],
            'out_trade_no' => $order['trade_no'],
            'type' => $this->config['type'] ?? '',
            'pid' => $this->config['pid']
        ];
        ksort($params);
        reset($params);
        $str = stripslashes(urldecode(http_build_query($params))) . $this->config['key'];
        $params['sign'] = md5($str);
        $params['sign_type'] = 'MD5';
        return [
            'type' => 1, // 0:qrcode 1:url
            'data' => $this->config['url'] . '/submit.php?' . http_build_query($params)
        ];
    }

    public function notify($params)
    {
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['sign_type']);
        ksort($params);
        reset($params);
        $str = stripslashes(urldecode(http_build_query($params))) . $this->config['key'];
        if ($sign !== md5($str)) {
            return false;
        }
        return [
            'trade_no' => $params['out_trade_no'],
            'callback_no' => $params['trade_no']
        ];
    }
}

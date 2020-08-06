<?php

class weixin {

    public function index() {
        //微信
        $url = $_REQUEST['urll']; //获取当前页面的url，接收请求参数
        //$url = "http://n.znds.com/view_test.php?id=25836";
        $root['url'] = $url;
        //获取access_token，并缓存
        $file = 'access_token'; //缓存文件名access_token
        $expires = 3600; //缓存时间1个小时
        if (file_exists($file)) {
            $time = filemtime($file);
            if (time() - $time > $expires) {
                $token = null;
            } else {
                $token = file_get_contents($file);
            }
        } else {
            fopen($file, "w+");
            $token = null;
        }
        if (!$token || strlen($token) < 6) {
            $res = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxe00d56bf62d842c4&secret=549826076980b01eec97c5d5c44b9a5f"); //自己的appid,通过微信公众平台查看appid和AppSecret
            $res = json_decode($res, true);
            $token = $res['access_token'];
            // write('access_token', $token, 3600);
            @file_put_contents($file, $token);
        }

        //获取jsapi_ticket，并缓存
        $file1 = 'jsapi_ticket';
        if (file_exists($file1)) {
            $time = filemtime($file1);
            if (time() - $time > $expires) {
                $jsapi_ticket = null;
            } else {
                $jsapi_ticket = file_get_contents($file1);
            }
        } else {
            fopen("$file1", "w+");
            $jsapi_ticket = null;
        }
        if (!$jsapi_ticket || strlen($jsapi_ticket) < 6) {
            $ur = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
            $res = file_get_contents($ur);
            $res = json_decode($res, true);
            $jsapi_ticket = $res['ticket'];
            @file_put_contents($file1, $jsapi_ticket);
        }

        $timestamp = time(); //生成签名的时间戳
        $metas = range(0, 9);
        $metas = array_merge($metas, range('A', 'Z'));
        $metas = array_merge($metas, range('a', 'z'));
        $nonceStr = '';
        for ($i = 0; $i < 16; $i++) {
            $nonceStr .= $metas[rand(0, count($metas) - 1)]; //生成签名的随机串
        }

        $string1 = "jsapi_ticket=" . $jsapi_ticket . "&noncestr=$nonceStr" . "&timestamp=$timestamp" . "&url=$url";
        $signature = sha1($string1);
        $root['appid'] = 'wxe00d56bf62d842c4';
        $root['nonceStr'] = $nonceStr;
        $root['timestamp'] = $timestamp;
        $root['signature'] = $signature;


        return $root;
    }

}

$weixin = new weixin();
$res = $weixin->index();

echo json_encode($res);

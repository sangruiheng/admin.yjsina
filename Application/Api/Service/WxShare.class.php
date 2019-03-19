<?php

namespace Api\Service;
use Api\Exception\ParameterException;

Vendor('WxShare.jssdk');

class WxShare
{

    public function wxShare($url){
        $jssdk = new \JSSDK(C('APPID'), C('AppSecret'));
        $signPackage = $jssdk->GetSignPackage($url);
        if(!$signPackage){
            $result = (new ParameterException([
                'msg' => '获取微信分享参数失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        return [
            'code' => 200,
            'data' => $signPackage,
        ];
    }

}


?>

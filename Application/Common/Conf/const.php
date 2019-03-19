<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-20
 * Time: 下午3:55
 */

return array(

    'APPID' => 'wx39db10849bb004d4',

    'AppSecret' => '7421f91e7d3d224e1b085c00c8ff6959',

    'MCHID' => '1512017481',

    'payKEY' => 'yanjiaoxinlangjiajuweixinzhifu11',

    //异步回调地址
    'JSAPI_Notify_URL' => 'http://admin.yjsina.com/api/notify',

    //会员卡回调地址
    'JSAPI_Member_URL' => 'http://admin.yjsina.com/api/membernotify',

    //未支付
    'Unpaid' => 1,

    //已支付
    'Paid' => 2,

    //已发货
    'shipped' => 3,

    //待评价
    'evaluated' => 4,

    //已取消
    'cencel' => 5,

    //退款申请中
    'orderRefunding' => 6,

    //退款成功
    'RefundOK' => 7,

    //退款成功
    'RefundERR' => 8,





    //取消退款
    'CancelRefund' => 0,

    //退款中
    'Refunding' => 1,

    //已退款
    'Refunded' => 2,

    //退款未同意
    'NoRefund' => 3,


    //token过期时间
    'token_expire_in' =>21600,

    //图片路径
    "img_prefix" => "http://admin.yjsina.com/Uploads/Manage/",
//    "img_prefix" => "http://www.wyyx1.com/Uploads/Manage/",

    //订单支付回调
    'NOTIFY_URL' => "http://jiaju.icpnt.com/api/notify",

    // 微信请求code的url
    'login_url' => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect",


    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code",

    // 快递鸟电商ID
    'EBusinessID' => '1379652',

    // 快递鸟key
    'KDN_APIkey' => 'f8790cd2-f9b2-4d58-8aa9-705870c3a12b',

    //快递鸟请求URL
    'KDN_ReqURL' => 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx',

     //好评
    'Praise' => 5,

    //中评
    'Review' => '2,4',

    //差评
    'Negative' => 1,

    //未签到
    'NoSign'   => 1,

    //已签到
    'Signed'   => 2,

    //增加积分
    'Up_Bounds'   => 1,

    //减少积分
    'Dn_Bounds'   => 2,

    //订金商品
    'Deposit_Product'   => 1,

    //全款商品
    'Full_product'   => 2,

    //限时购商品
    'Discount_Product'   => 3,

    //短信平台uid
    'SMS_UID' =>    2309,

    //短信平台密码 md5
    'SMS_PWD' =>    'e10adc3949ba59abbe56e057f20f883e',


    //smtp登录的账号
    'STMP_NAME' =>    '603793103@qq.com',

    //smtp登录的密码
    'STMP_PWD' =>    'nztmcljklqllbcde',

    //邮件主题
    'MAIL_Subject' =>    '新浪优选订单提醒通知',

    //发件人昵称
    'MAIL_FromName' =>    '燕郊新浪优选',






);
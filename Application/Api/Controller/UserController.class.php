<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\OrderException;
use Api\Exception\ParameterException;
use Api\Exception\SuccessException;
use Api\Exception\UserException;
use Api\Model\OrderModel;
use Api\Model\UserModel;
use Api\Service\Token;
use Api\Service\UserToken;
use Api\Service\WxShare;
use Api\Validate\PayPwd;
use Api\Validate\UserNew;
use Api\Validate\UserPwd;
use Think\Controller;


class UserController extends CommonController
{


    protected $uid;

    //获取验证码
    //1.获取手机号 验证手机号
    //2.通过手机号查询验证码表里是否存在
    public function getSmsCode()
    {
        $telPhone = I('tel');
//        if (empty($telPhone)) $this->return_ajax(401, '请输入手机号');
//        $map['tel'] = array('eq', $telPhone);
//        $sms_info = M('sms')->where($map)->field('id,sms_sendTime')->find();
        $code = (new UserModel())->randomKeys(4);
//
//        if (!empty($sms_info)) {
//            if (time() < ($sms_info['sms_sendTime'] + 60)) $this->return_ajax(402, '60秒之内不能重新发送');
//            $data['id'] = $sms_info['id'];
//            $data['sms_code'] = $code;
//            $data['sms_sendTime'] = time();
//            $rs = M('sms')->save($data);
//        } else {
//            $data['sms_code'] = $code;
//            $data['sms_sendTime'] = time();
//            $data['tel'] = $telPhone;
//            $rs = M('sms')->add($data);
//        }
        $rs = $this->is_TelSms($telPhone,$code);
        if ($rs) {
            $content = '【新浪优选】"' . $code . '"燕郊新浪优选平台验证码，60秒之内有效（如非本人操作，请忽略本短信）';
            $phone = $telPhone;
            //发送短信
            $result = $this->sendSms($content, $phone);
            $this->return_ajax(200, '发送成功', $result);
        } else {
            $this->return_ajax(400, '发送失败');
        }
    }

    //登录验证
    public function login_pwd()
    {
        $telPhone = I('tel');
        if (empty($telPhone)) $this->return_ajax(401, '请输入手机号');
        $map['tel'] = array('eq', $telPhone);
        $user_info = M('user')->where($map)->field('id,tel')->find();

        $code = I('code');
        if (empty($code)) $this->return_ajax(402, '请输入验证码');
        $where['tel'] = array('eq', $telPhone);
        $sms_info = M('sms')->where($where)->field('id,sms_code,sms_sendTime')->find();
        if (empty($sms_info)) $this->return_ajax(403, '请先发验证码');
        if ($sms_info['sms_code'] != $code) $this->return_ajax(404, '验证码不正确');
        if (time() > ($sms_info['sms_sendTime'] + 60)) $this->return_ajax(405, '验证码已过期');

        if (!empty($user_info)) {
            $data['id'] = $user_info['id'];
            $data['loginTime'] = time();
            $rs = M('user')->save($data);
            $user_id = $user_info['id'];
        } else {
            $data['telPhone'] = $telPhone;
            $data['addTime'] = time();
            $rs = M('user')->add($data);
            $user_id = $rs;
        }
        if ($rs) {
            $returnData['uid'] = $user_id;
            $this->return_ajax(200, '登录成功', $returnData);
        } else {
            $this->return_ajax(400, '登录失败');
        }
    }

    //判断是否有token对应的用户
    public function is_user()
    {
        $user = D('user')->where("id=$this->uid")->find();
        return $user;
    }


    //微信授权登录
    public function wxLogin($shareUID = '',$login = '', $proId = '')
    {
//        $this->printLog($login);
//        $this->printLog($proId);

        $userToken = new UserToken();
        $userToken->getCode($shareUID, $login, $proId);
    }

    //授权回调
    public function getToken()
    {
        $userToken = new UserToken();
        $result = $userToken->getUserInfo();
        $this->printLog($result['login']);
        $this->printLog($result['proId']);
//        header("location:http://www.yjsina.com?token=" . $result['token'] . "&fristLogin=" . $result['fristLogin']);

        if ($result['login'] == 1) {   //购物车
            header("location:http://www.yjsina.com/ShoppingCart/View.html?token=" . $result['token']);
//            $(window).attr("location", "../ShoppingCart/View.html");
        } else if ($result['login'] == 2) {  //个人中心
            header("location:http://www.yjsina.com?token=" . $result['token'] . "&fristLogin=" . $result['fristLogin']);
//            header("location:http://www.yjsina.com/Personal/View.html?token=" . $result['token'] . "&fristLogin=" . $result['fristLogin']);
//            $(window).attr("location", "../Personal/View.html");
        } else if ($result['login'] == 3) {  //商品详情
            header("location:http://www.yjsina.com/ProductDetails/View.html?token=" . $result['token'] . "&id=" . $result['proId']);
//            console.log(proId);
//            $(window).attr("location", "../ProductDetails/View.html?id=" + proId);
        } else if ($result['login'] == 4) {  //规格详情
            header("location:http://www.yjsina.com/specificationAndQuantity/View.html?token=" . $result['token'] . "&id=" . $result['proId']);
//            $(window).attr("location", "../specificationAndQuantity/View.html?id=" + proId);
        }


//        $url = 'http://www.baidu.com/index.php?m=content&c=index&a=lists&catid=6&area=0&author=0&h=0®ion=0&s=1&page=1';
//        $arr = parse_url($url);
//        $arr_query = $this->convertUrlQuery($arr['query']);


        // $this->ajaxReturn([
        // 'code' => 200,
        // 'msg' => '微信登陆成功',
        // 'Token' => $Token
        // ]); //返回客户端令牌
    }


    //手机号登陆
    public function login($shareUID = '')
    {
        //验证参数
        //根据手机号去数据库看下 存在取出uid  不存在新增
//        $telPhone = I('tel');
//        $code = I('code');
//        if (empty($telPhone)) $this->return_ajax(401, '请输入手机号');
//        if (empty($code)) $this->return_ajax(402, '请输入验证码');
//        $where['tel'] = array('eq', $telPhone);
//        $sms_info = M('sms')->where($where)->field('id,sms_code,sms_sendTime')->find();
//        if (empty($sms_info)) $this->return_ajax(403, '请先发验证码');
//        if ($sms_info['sms_code'] != $code) $this->return_ajax(404, '验证码不正确');
//        if (time() > ($sms_info['sms_sendTime'] + 60)) $this->return_ajax(405, '验证码已过期');
        $this->validateLogin(I('tel'), I('code'));
        $result = (new UserToken($shareUID))->getCache(I('tel'));
        $this->ajaxReturn([
            'code' => 200,
            'msg' => '手机号登陆成功',
            'Token' => $result['token'],
            'fristLogin' => $result['fristLogin'],
        ]); //返回客户端令牌

    }


    //修改支付密码 6位 两组参数
    public function updatePayPwd()
    {
        //根据token来获取uid
        $this->uid = Token::getCurrentUid();
        $user = $this->is_user();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        (new PayPwd())->goCheck();
        $user = new UserModel();
        $result = $user->updatePay($_POST['paypwd'], $_POST['confirm_paypwd'], $this->uid);
        if ($result) {
            $this->ajaxReturn((new SuccessException([
                'msg' => '设置支付密码成功！'
            ]))->getException());
        }
    }


    //修改密码
    public function updateUserPwd()
    {
        $this->uid = Token::getCurrentUid();
        $user = $this->is_user();
        if (!$user) {
            $result = (new UserException())->getException();
            $this->ajaxReturn($result);
        }
        (new UserPwd())->goCheck();
        $user = new UserModel();
        $result = $user->updatePwd($_POST['pwd'], $_POST['confirm_pwd'], $_POST['ord_pwd'], $this->uid);
        if ($result) {
            $this->ajaxReturn((new SuccessException([
                'msg' => '修改密码成功！'
            ]))->getException());
        }
    }


    //个人中心显示页
    public function personalCenter()
    {
        //token
        $this->uid = Token::getCurrentUid();
        $user = D('user')->where("id=$this->uid")->field('nickName,avatarUrl,tel,last_sign_time')->find();
        $user['nickName'] = urldecode($user['nickName']);
        if ($user['nickName'] == null) {
            $user['avatarUrl'] = 'http://admin.yjsina.com/' . $user['avatarUrl'];
            $user['nickName'] = $user['tel'];
            $user['login_type'] = 1;   //手机号登陆
        }else{
            $user['login_type'] = 0;  //微信登陆
        }
        $userMember = $this->getUserMember($this->uid);
        $time = date('Y-m-d',time());
        if($userMember && $userMember['membercard_endtime'] > $time){
            $user['is_mamber'] = 0;
        }else{
            $user['is_mamber'] = 1;
        }
        $lastTime = substr($user['last_sign_time'], 0, 10);
        $currentTime = date('Y-m-d', time());   //当前日期
        if (empty($user['last_sign_time'])) {
            $user['sign'] = C('NoSign');
        } elseif ($lastTime == $currentTime) {
            $user['sign'] = C('Signed');
        } elseif ($lastTime != $currentTime) {
            $user['sign'] = C('NoSign');
        }
        unset($user['tel']);
        unset($user['last_sign_time']);
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $user
        ]);
    }



    //签到
    public function sign()
    {
        //                      2018-10-10  2018-10-10
        //登陆时判断是否已签到    最后签到日期  当前日期
        $this->uid = Token::getCurrentUid();
        $UserModel = M('user');
        $time = date('Y-m-d H:i:s', time());
        $user = $UserModel->where("id=$this->uid")->find();
        $lastTime = substr($user['last_sign_time'], 0, 10);  //最后签到日期
        $currentTime = date('Y-m-d', time());   //当前日期
        if ($lastTime == $currentTime) {
            $this->ajaxReturn([
                'code' => 401,
                'msg' => '今天已经签到过了',
            ]);
        } else {
            $UserModel->last_sign_time = $time;
            $result = $UserModel->where("id=$this->uid")->save();
            $UserModel->where("id=$this->uid")->setInc('bounds', 10); // 用户的积分加10
            $this->boundsDetail($this->uid, 10, '签到', C('Up_Bounds'));  //添加积分明细
            if ($result) {
                $this->ajaxReturn([
                    'code' => 200,
                    'msg' => '签到成功,获得10新浪币',
                ]);
            } else {
                $this->ajaxReturn([
                    'code' => 400,
                    'msg' => '签到失败',
                ]);
            }
        }

    }


    //积分中心
    public function boundsCenter()
    {
        $this->uid = Token::getCurrentUid();
        $userModel = M('user');
        $boundsDetailModel = M('boundsdetail');
        $user = $userModel->where("id=$this->uid")->find();
        $result['boundsDetail'] = $boundsDetailModel->where("user_id=$this->uid")->select();
        foreach ($result['boundsDetail'] as &$value) {
            if ($value['bounds_type'] == C('Up_Bounds')) {
                $value['make_bounds'] = '+' . $value['make_bounds'];
            } elseif ($value['bounds_type'] == C('Dn_Bounds')) {
                $value['make_bounds'] = '-' . $value['make_bounds'];
            }
        }
        $result['user_sum_bounds'] = $user['bounds'];
        if (!$result) {
            $this->ajaxReturn([
                'code' => 400,
                'msg' => '获取积分明细失败',
            ]);
        } else {
            $this->ajaxReturn([
                'code' => 200,
                'msg' => 'success',
                'data' => $result
            ]);
        }
    }


    //短信订单详情
    public function smsOrderDetail(){
        //uid  order_id
        $uid = $_POST['uid'];
        $order_id = $_POST['order_id'];
        if(!$uid){
            $this->ajaxReturn((new UserException([
                'msg' => 'uid为空'
            ]))->getException());
        }
        if(!$order_id){
            $this->ajaxReturn((new OrderException([
                'msg' => 'order_id为空'
            ]))->getException());
        }
        $orderModel = new OrderModel();
        $map['id'] = $order_id;
        $map['user_id'] = $uid;
        $map['order_status'] = C('Paid');
        $order = $orderModel->where($map)->find();
        if(!$order){
            $this->ajaxReturn((new OrderException([
                'msg' => '当前订单与用户不匹配或订单不是已支付'
            ]))->getException());
        }
        $result = (new OrderModel())->orderDetail($uid, $order_id);
        if (!$result) {
            $this->ajaxReturn((new OrderException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);

    }


    //微信登陆绑定手机号
    public function wxBindTel(){
        //参数  手机号  验证码
        //判断微信要绑定的手机号是否已经注册或者绑定
        //向绑定手机号发送验证码 通过后绑定成功
        //当用手机号登陆时，如果是微信用户显示微信的信息
        $this->uid = Token::getCurrentUid();
//        $telPhone = I('tel');
//        $code = I('code');
//        if (empty($telPhone)) $this->return_ajax(401, '请输入手机号');
//        if (empty($code)) $this->return_ajax(402, '请输入验证码');
//        $where['tel'] = array('eq', $telPhone);
//        $sms_info = M('sms')->where($where)->field('id,sms_code,sms_sendTime')->find();
//        if (empty($sms_info)) $this->return_ajax(403, '请先发验证码');
//        if ($sms_info['sms_code'] != $code) $this->return_ajax(404, '验证码不正确');
//        if (time() > ($sms_info['sms_sendTime'] + 60)) $this->return_ajax(405, '验证码已过期');
        $this->validateLogin(I('tel'), I('code'));
        //微信绑定手机号
        $result = (new UserModel())->BindTel($this->uid, I('tel'));
        $this->ajaxReturn([
            'code' => 200,
            'msg' => '绑定成功',
        ]);
    }


    //显示绑定手机号页面
    public function getBindTel(){
        //根据token来获取uid
        $this->uid = Token::getCurrentUid();
        $user = $this->is_user();
        $userModel = M('user');
        $user = $userModel->where("id=$this->uid")->find();
        if($user['tel']){
            $user_tel = $user['tel'];
        }else{
            $user_tel = '';
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'user_tel' => $user_tel,
        ]);
    }

    //更换绑定手机号 发送验证码
    public function sendBindSms(){
        $telPhone = I('tel');
        $code = (new UserModel())->randomKeys(4);
        $rs = $this->is_TelSms($telPhone,$code);
        if ($rs) {
            $content = '【新浪优选】验证码："' . $code . '"，您正在新浪优选进行手机验证，请勿将此码告诉别人，如非本人操作，请忽略此短信';
            $phone = $telPhone;
            //发送短信
            $result = $this->sendSms($content, $phone);
            $this->return_ajax(200, '发送成功', $result);
        } else {
            $this->return_ajax(400, '发送失败');
        }

    }


    //更换绑定手机号 验证身份
    public function validateIdentity(){
        $result = $this->validateLogin($_POST['tel'], $_POST['code']);
        if($result){
            $this->ajaxReturn([
                'code' => 200,
                'msg' => '验证成功',
            ]);
        }
    }


    //邀请好友界面
    public function shareFriend()
    {
        $this->uid = Token::getCurrentUid();
        if ($this->uid) {
            $this->ajaxReturn([
                'code' => 200,
                'uid' => $this->uid,
            ]);
        } else {
            $this->ajaxReturn([
                'code' => 400,
                'msg' => 'uid获取失败',
            ]);
        }
    }


    //微信分享朋友，朋友圈
    public function wxShare()
    {
        $this->uid = Token::getCurrentUid();
        $signPackage = (new WxShare())->wxShare($_POST['url']);
        $this->ajaxReturn($signPackage);
    }

    //微信分享商品
    public function wxShareProduct()
    {
        $signPackage = (new WxShare())->wxShare($_POST['url']);
        $this->ajaxReturn($signPackage);
    }


    //关于我们
    public function about()
    {
        $about = M('help')->find(1);
        $about['help_content'] = replacePicUrl($about['help_content'], "http://admin.yjsina.com");
        if (!$about) {
            $this->ajaxReturn((new ParameterException([
                'code' => 89000,
                'msg' => '关于我们不存在'
            ])));
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $about
        ]);
    }

    //帮助中心
    public function help()
    {
        $help = M('help')->find(2);
        $help['help_content'] = replacePicUrl($help['help_content'], "http://admin.yjsina.com");
        if (!$help) {
            $this->ajaxReturn((new ParameterException([
                'code' => 89001,
                'msg' => '帮助信息不存在'
            ])));
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $help
        ]);
    }


    public function userPay()
    {
        $this->display();
    }


}

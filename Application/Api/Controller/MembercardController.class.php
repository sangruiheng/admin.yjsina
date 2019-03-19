<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\UserException;
use Api\Service\MemberNotify;
use Api\Service\MemberPay;
use Api\Service\MemberH5Pay;
use Api\Service\Token;
use Api\Model\MemberCardModel;
use Api\Validate\IDMustBePostiveInt;
use Think\Controller;
Vendor('Wxpay.lib.WxPay#Config');
class MembercardController extends CommonController
{

    protected $uid;

//    public function __construct()
//    {
//        $this->uid = Token::getCurrentUid();
//        $this->is_user();
//    }

    //判断是否有token对应的用户
    public function is_user()
    {
        $user = D('user')->where("id=$this->uid")->find();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        return $user;
    }

    public function getMemberCard(){
        //姓名 头像 会员详情 到期时间 是什么会员  购买还是续费
        $this->uid = Token::getCurrentUid();
        $this->is_user();
        $userModel = D('user');
        $memberCardModel = M('membercard');
        $user = $userModel->relation('memberdetail')->where("id=$this->uid")->field('id,nickName,avatarUrl,tel')->find();
        $user['memberCard'] = $memberCardModel->select();
        $user['nickName'] = urldecode($user['nickName']);
        if ($user['nickName'] == null) {
            $user['avatarUrl'] = 'http://admin.yjsina.com/' . $user['avatarUrl'];
            $user['nickName'] = $user['tel'];
        }
        unset($user['tel']);
        $time = date('Y-m-d',time());
        //是否是会员 0不是 1是
        if(!$user['memberdetail'] || $user['memberdetail']['membercard_endtime'] < $time){
            $user['is_member'] = 0;
        }else{
            $user['is_member'] = 1;
        }
        if (!$user) {
            $result = (new UserException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $user
        ]);
    }

    //会员卡微信支付
    public function memberCardPay(){
        //下单  添加订单号 uid  结束时间  下单时间 会员卡类型
        //token  会员卡类型id

        $this->uid = Token::getCurrentUid();
        $this->is_user();
        $mambercard_id = $_POST['id'];
        (new IDMustBePostiveInt())->goCheck();
        $MemberPay = (new MemberPay())->MemberCardPay($this->uid,$mambercard_id);
        $this->ajaxReturn(json_decode($MemberPay));
    }

    //会员卡回调
    public function memberNotify()
    {
        $notify = new MemberNotify();
        $config = new \WxPayConfig();
        $notify->Handle($config, false);
    }

    //会员卡h5支付
    public function memberH5Pay()
    {
        //token  会员卡类型id
        $this->uid = Token::getCurrentUid();
        $this->is_user();
        $mambercard_id = $_POST['id'];
        (new IDMustBePostiveInt())->goCheck();
        $MemberH5Pay = new MemberH5Pay();
        $result = $MemberH5Pay->MemberH5Pay($this->uid,$mambercard_id);
//        $result = $MemberH5Pay->MemberH5Pay(2,3);
        $this->ajaxReturn($result);
//        $this->assign('objectxml',$result);
//        $this->display();
    }



}
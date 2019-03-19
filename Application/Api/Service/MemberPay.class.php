<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;

use Api\Exception\OrderException;
use Api\Exception\ParameterException;
use Api\Controller\CommonController;

Vendor('Wxpay.lib.WxPay#Api');
Vendor('Wxpay.lib.WxPay#Config');
Vendor('Wxpay.lib.WxPay#JsApiPay');
Vendor('Wxpay.lib.log');

class MemberPay
{

    protected $uid;
    protected $member_no;
    protected $memberDetail_id;

    public function MemberCardPay($uid, $mambercard_id)
    {
        //下单  添加订单号 下单时间 会员卡类型
        //续费用户 查询表中有无此用户 判断当前用户结束时间 更新字段
        //支付成功 设置uid 结束时间
        $this->uid = $uid;
        $memberDetailModel = M('memberdetail');
        $memberCardModel = M('membercard');
        $this->member_no = (new CommonController())->createNonce(32);
        $memberDetail = $memberDetailModel->where("member_no='$this->member_no'")->find();
        if ($memberDetail) {
            $this->member_no = (new CommonController())->createNonce(32);
        }
        $member_time = date('Y-m-d H:i:s', time());

        $memberDetail = $memberDetailModel->where("user_id=$this->uid")->find();
        if ($memberDetail) {   //已过期用户
            //会员已过期 重新替换用户购买信息
            $memberDetailModel->member_no = $this->member_no;
            $memberDetailModel->mambercard_id = $mambercard_id;
            $memberDetailModel->member_time = $member_time;
            $memberDetailModel->where("id=" . $memberDetail['id'])->save();
            $memberDetail_id = $memberDetail['id'];
        } else {
            $memberDetailModel->member_no = $this->member_no;
            $memberDetailModel->mambercard_id = $mambercard_id;
            $memberDetailModel->member_time = $member_time;
            $memberDetail_id = $memberDetailModel->add();
        }


        if (!$memberDetail_id) {
            $result = (new OrderException([
                'code' => 10088,
                'msg' => '添加购买会员记录失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->memberDetail_id = $memberDetail_id;
        $memberCard = $memberCardModel->where("id=$mambercard_id")->find();
        return $this->makeWxPreOrder($memberCard['membercard_price']);
    }

    //组装预订单参数
    private function makeWxPreOrder($totalPrice)
    {
        $openid = Token::getCurrentTokenVar('openid');
//        $openid = "oNXZs1ZERML_WDhsgXcq5Pa3AGp0";
        if (!$openid) {
            // 异常
            $result = (new ParameterException([
                'code' => 10008,
                'msg' => '获取openid失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $attach = $this->memberDetail_id . ',' . $this->uid;
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("新浪家居");
        $input->SetOut_trade_no($this->member_no);
        $input->SetTotal_fee($totalPrice * 100);
        $input->SetGoods_tag("test");
        $input->SetNotify_url(C('JSAPI_Member_URL'));
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);
        $input->SetAttach($attach);
        return $this->getPaySignature($input);
    }

    //调用微信预订单接口
    private function getPaySignature($wxOrderData)
    {
        $config = new \WxPayConfig();
        $wxOrder = \WxPayApi::unifiedOrder($config, $wxOrderData);
//        $fp = fopen('test6.txt', 'a+b');
//        fwrite($fp, var_export($wxOrder, true));
//        fclose($fp);
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            $result = (new OrderException([
                'code' => 11005,
                'msg' => '获取预支付订单失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }

        return $this->getJsApi($wxOrder);
    }
//{
//"appid": "wx39db10849bb004d4",
//"mch_id": "1512017481",
//"nonce_str": "7O1Zbv96bRGmVXUT",
//"prepay_id": "wx311148464855866a8043d6b20058344177",
//"result_code": "SUCCESS",
//"return_code": "SUCCESS",
//"return_msg": "OK",
//"sign": "CBAF4ADD24B34B1D348CCD5BD1A57AC315CE01320BFCA8779DB7DE6E1BFD6F9B",
//"trade_type": "JSAPI"
//}

    //生成微信预订单接口所需信息
    private function getJsApi($wxOrder)
    {

        $tools = new \JsApiPay();
        return $jsApiParameters = $tools->GetJsApiParameters($wxOrder);
    }

}
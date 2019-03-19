<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;


use Api\Exception\OrderException;
use Api\Exception\AddressException;
use Api\Exception\ParameterException;
use Api\Model\OrderModel;
Vendor('Wxpay.lib.WxPay#Api');
Vendor('Wxpay.lib.WxPay#Config');
Vendor('Wxpay.lib.WxPay#JsApiPay');
Vendor('Wxpay.lib.log');
class WxRefund
{
    protected $orderID;
    //transaction_id  微信订单号
    //out_refund_no   商户退款单号
    //total_fee       订单金额
    //refund_fee      退款金额

    function __construct($orderID)
    {
        if (!$orderID) {
            $result = (new OrderException([
                'code' => 11002,
                'msg' => '订单id不能为空'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->orderID = $orderID;

    }

    public function WxRefund()
    {

        $orderModel = M('order');
        $order = $orderModel->where("id=$this->orderID")->find();
        $input = new \WxPayRefund();
//        $input->SetOut_trade_no($order['order_sn']);			//自己的订单号
        $input->SetTransaction_id($order['transaction_id']);  	//微信官方生成的订单流水号，在支付成功中有返回
        $input->SetOut_refund_no($this->getRandom(32));			//退款单号
        $input->SetTotal_fee($order['order_price']*100);			//订单标价金额，单位为分
        $input->SetRefund_fee($order['order_price']*100);			//退款总金额，订单总金额，单位为分，只能为整数
        $config = new \WxPayConfig();
        $result = \WxPayApi::refund($config, $input);	//退款操作
        if(($result['return_code']=='SUCCESS') && ($result['result_code']=='SUCCESS')){
            //退款成功
            return [
              'code' => 200,
              'msg' => '退款成功'
            ];
        }else if(($result['return_code']=='FAIL') || ($result['result_code']=='FAIL')){
            //退款失败
            //原因
            $reason = (empty($result['err_code_des'])?$result['return_msg']:$result['err_code_des']);
            return [
                'code' => 201,
                'msg' => $reason
            ];
        }else{
            //失败
            return [
                'code' => 400,
                'msg' => '退款接口错误'
            ];
        }
    }

    private function getRandom($param)
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for ($i = 0; $i < $param; $i++) {
            $key .= $str{mt_rand(0, 32)};    //生成php随机数
        }
        return $key;
    }



    private function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }



}
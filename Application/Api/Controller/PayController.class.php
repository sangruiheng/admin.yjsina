<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\OrderException;
use Api\Exception\SuccessException;
use Api\Exception\UserException;
use Api\Service\h5Pay;
use Api\Service\Pay;
use Api\Service\WxNotify;
use Api\Service\Token;
use Api\Service\WxRefund;
use Api\Validate\IDMustBePostiveInt;

Vendor('Wxpay.lib.WxPay#Config');

class PayController extends CommonController
{

    protected $uid;

    //请求预订单信息  需要传递订单id   openid可以用令牌换取
    public function getPreOrder($id = '', $address_id = '', $order_bounds = '0')
    {
        $pay = new Pay($id, $address_id, $order_bounds);
        $res = $pay->pay();
        $this->ajaxReturn(json_decode($res));
//        $this->ajaxReturn($res);
    }


    //微信支付显示页
    public function orderList()
    {
        $this->display();
    }


    //微信回调
    public function notify()
    {
        $notify = new WxNotify();
        $config = new \WxPayConfig();
        $notify->Handle($config, false);
    }


    public function h5Pay($id = "", $address_id = "", $order_bounds = "0")
    {
//        Token::getCurrentUid();
        $h5Pay = new h5Pay($id, $address_id, $order_bounds);
        $result = $h5Pay->h5Pay();
        $this->ajaxReturn($result);
//        $this->assign('objectxml',$result);
//        $this->display();
    }


    //判断是否有token对应的用户
    public function is_user()
    {
        $user = D('user')->where("id=$this->uid")->find();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        return $user;
    }


    //已付款退款
    public function orderRefund()
    {
        //id 订单id  refund_cause 退款原因
        $this->uid = Token::getCurrentUid();
        $this->is_user();
        (new IDMustBePostiveInt())->goCheck();
        $orderID = $_POST['id'];
        $refund_cause = $_POST['refund_cause'];
        $orderModel = M('order');
        if (!$refund_cause) {
            $this->ajaxReturn([
                'code' => 11009,
                'msg' => '退款原因不能为空'
            ]);
        }
        $order = $orderModel->where("id=$orderID and user_id=$this->uid")->find();
        if(!$order){
            $result = (new OrderException([
                'code' => 11014,
                'msg' => '当前订单不存在或用户与当前订单不匹配'
            ]))->getException();
            $this->ajaxReturn($result);
        }
//        if($order['refund_type'] != C('CancelRefund')){
//            $result = (new OrderException([
//                'code' => 11015,
//                'msg' => '当前订单商品已经申请过退款了'
//            ]))->getException();
//            $this->ajaxReturn($result);
//        }
        if ($order['order_status'] != C('Paid')){
            $result = (new OrderException([
                'code' => 11013,
                'msg' => '该订单未付款或订单已经发货'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        //添加退款记录
        $refundorderModel = M('refundorder');
        $refundorderModel->orefund_type = C('Refunding');
        $refundorderModel->orefund_cause = $refund_cause;
        $refundorderModel->orefund_time = date("Y-m-d H:i:s", time());
        $refundorderModel->order_id = $orderID;
        $refundorder = $refundorderModel->add();

        //更改订单表状态
//        $orderModel->order_status = C('orderRefunding');   //退款申请中
//        $orderModel->where("id=$orderID")->save();

        if (!$refundorder) {
            $result = (new OrderException([
                'code' => 11012,
                'msg' => '申请退款失败'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $result = (new SuccessException([
            'msg' => '申请退款成功'
        ]))->getException();
        $this->ajaxReturn($result);
    }



    //已发货退款退货
    public function productRefund(){
        //delivery_id 订单发货id  refund_cause 退款原因
        $this->uid = Token::getCurrentUid();
        $this->is_user();
        $delivery_id = $_POST['delivery_id'];
        if(!$delivery_id){
            $this->ajaxReturn([
                'code' => 11011,
                'msg' => '订单发货id不能为空'
            ]);
        }
        $refund_cause = $_POST['refund_cause'];
        $deliveryModel = M('delivery');
        $refundproductModel = M('refundproduct');
        if (!$refund_cause) {
            $result = (new OrderException([
                'code' => 11009,
                'msg' => '退款原因不能为空'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $delivery = $deliveryModel->where("id=$delivery_id and user_id=$this->uid")->find();
        if(!$delivery){
            $result = (new OrderException([
                'code' => 11010,
                'msg' => '暂无当前发货商品,或当前用户与该发货商品不匹配'
            ]))->getException();
            $this->ajaxReturn($result);
        }
//        if($delivery['refund_type'] != C('CancelRefund')){
//            $result = (new OrderException([
//                'code' => 11015,
//                'msg' => '当前订单商品已经申请过退款了'
//            ]))->getException();
//            $this->ajaxReturn($result);
//        }
        //添加退款记录
        $refundproductModel->prefund_type = C('Refunding');
        $refundproductModel->prefund_cause = $refund_cause;
        $refundproductModel->prefund_time = date("Y-m-d H:i:s", time());
        $refundproductModel->delivery_id = $delivery_id;
        $result = $refundproductModel->add();

        //修改订单商品发货表中状态
//        $deliveryModel->product_status = C('orderRefunding');  //退款申请中
//        $deliveryModel->where("id=$delivery_id")->save();
        if (!$result) {
            $result = (new OrderException([
                'code' => 11008,
                'msg' => '申请退款失败'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $result = (new SuccessException([
            'msg' => '申请退款成功'
        ]))->getException();
        $this->ajaxReturn($result);
    }

    public function getTailmoneyNo( $length = 6 )
    {
        $str = substr(md5(time()), 0, $length);//md5加密，time()当前时间戳
        $TailmoneyNo = $this->orderNo.'_'.$str;
        return $TailmoneyNo;
    }


    //已付款订单退款详情
    public function orderRefundDetail(){
//        $this->uid = Token::getCurrentUid();
//        $this->is_user();
//        (new IDMustBePostiveInt())->goCheck();
        $order_id = $_POST['id'];
        $orderModel = D('order');
        $order = $orderModel->relation('refundorder')->where("id=$order_id")->find();
        $order['snap_items'] = json_decode($order['snap_items'],true);
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $order
        ]);
    }

    //已发货订单退款详情
    public function productRefundDetail(){
        //订单id  product_id prefund_id
        $order_id = $_POST['id'];
        $product_id = $_POST['product_id'];
        $delivery_id = $_POST['delivery_id'];
        $orderModel = M('order');
        $refundproductModel = M('refundproduct');
        $order = $orderModel->where("id=$order_id")->find();
        $order['snap_items'] = json_decode($order['snap_items'],true);
        foreach($order['snap_items'] as $item){
            if($item['id'] == $product_id){
                $order['products'] = $item;
            }
        }
        unset($order['snap_items']);
        $order['refundproduct'] = $refundproductModel->where("delivery_id=$delivery_id")->order('id desc')->find();
        if(!$order){
            $result = (new OrderException([
                'code' => 11015,
                'msg' => '查询失败'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $order
        ]);
    }


}
<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;

use Api\Exception\AddressException;
use Api\Exception\OrderException;
use Api\Exception\ParameterException;
use Api\Exception\ProductException;
use Api\Model\OrderModel;

Vendor('Wxpay.lib.WxPay#Api');
Vendor('Wxpay.lib.WxPay#Config');
Vendor('Wxpay.lib.WxPay#JsApiPay');
Vendor('Wxpay.lib.log');

class Pay
{

    protected $orderID;
    protected $orderNo;
    protected $addressID;
    protected $order_bounds;

    function __construct($orderID, $addressID, $order_bounds)
    {
        if (!$orderID) {
            $result = (new OrderException([
                'code' => 11002,
                'msg' => '订单号不能为空'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->orderID = $orderID;
        if (!$addressID) {
            $result = (new AddressException([
                'code' => 70004,
                'msg' => '地址id不能为空'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->addressID = $addressID;
        $this->order_bounds = $order_bounds;
    }


    //主方法
    public function pay()
    {
        //参数检测
        //库存量检测
        //请求微信预订单接口


        //参数检测
        $this->checkOrderValid();

        //判断是订金支付还是全款支付 当是全款支付时检测库存量
        $orderModel = M('order');
        $order = $orderModel->where("id=$this->orderID")->find();
    /*    if ($order['order_producttype'] == 2) {
            //检测库存量
            $orderService = new Order();
            $stats = $orderService->checkOrderStock($this->orderID);
            if ($stats['pass'] == false) {
                return $this->orderID;
            }
            //积分操作
            $orderPrice = $this->bounds($this->order_bounds, $stats['orderPrice']);
            
        } elseif ($order['order_producttype'] == 1) {
            if ($order['order_tailmoney']) {   //尾款支付
                //设置支付尾款订单号
                $orderModel->tailmoney_no = $this->getTailmoneyNo();
                $orderModel->where("id=$this->orderID")->save();
                //赋值尾款订单号
                $order = $orderModel->where("id=$this->orderID")->find();
                $this->orderNo = $order['tailmoney_no'];
                //积分操作
                $orderPrice = $this->bounds($this->order_bounds, $order['order_tailmoney']);
            } else {   //订金支付
                $orderPrice = $order['order_price'];
            }
        }*/
        $orderPrice = $this->orderType($order);

        //修改地址
        $order = M('order')->where("id=$this->orderID")->find();
        $order_info = json_decode($order['snap_address'], true);
        if ($this->addressID != $order_info['id']) {
            $this->updateAddress();
        }
        return $this->makeWxPreOrder($orderPrice, $order);

    }


    //组装预订单参数
    private function makeWxPreOrder($totalPrice, $order)
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

        $attach = $order['order_producttype'] . ',' . $order['id'];
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("新浪家居");
        $input->SetOut_trade_no($this->orderNo);
        $input->SetTotal_fee($totalPrice * 100);
//        $input->SetTotal_fee($totalPrice * 100);
//        $input->SetTime_start(date("YmdHis"));
//        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url(C('JSAPI_Notify_URL'));
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


    //判断是订金支付还是全款支付 当是全款支付时检测库存量
    private function orderType($order){
        $orderModel = M('order');
        if ($order['order_producttype'] == 2 || $order['order_producttype'] == 3) {
            //检测库存量
            $orderService = new Order();
            $stats = $orderService->checkOrderStock($this->orderID,$order['user_id']);
            if ($stats['pass'] == false) {
                return $this->orderID;
            }

               //积分操作
            $orderPrice = $this->bounds($this->order_bounds, $order['order_price']);

            return $orderPrice;

        } elseif ($order['order_producttype'] == 1) {
            if ($order['order_tailmoney']) {   //尾款支付
                //设置支付尾款订单号
                $orderModel->tailmoney_no = $this->getTailmoneyNo();
                $orderModel->where("id=$this->orderID")->save();
                //赋值尾款订单号
                $order = $orderModel->where("id=$this->orderID")->find();
                $this->orderNo = $order['tailmoney_no'];
                //积分操作
                $orderPrice = $this->bounds($this->order_bounds, $order['order_tailmoney']);
                return $orderPrice;
            } else {   //订金支付
                $orderPrice = $order['order_price'];
                return $orderPrice;
            }
        }
    }


    //判断订单参数是否符合要求
    private function checkOrderValid()
    {
        //订单号根本不存在
        $order = D('order')->where("id=" . $this->orderID)->find();
        if (!$order) {
            //异常 订单号不存在
            $result = (new OrderException([
                'code' => 11000,
                'msg' => '订单号不存在'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        //用户是否绑定手机号
        if(!(new OrderModel())->is_BindTel($order['user_id'])){
            $this->ajaxReturn((new ProductException([
                'code' => 20099,
                'msg' => '用户未绑定手机号'
            ]))->getException());
        }

        //订单号确实存在，但订单号与当前用户不匹配
        if (!Token::isValidOperate($order['user_id'])) {
            //异常  订单与用户不匹配
            $result = (new OrderException([
                'code' => 11006,
                'msg' => '订单与用户不匹配'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
//        //订单是否已经被支付
        if ($order['order_status'] != C('Unpaid')) {
            //异常  订单已经支付了
            $result = (new OrderException([
                'msg' => '订单已经支付了',
                'code' => $order,
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }

        //用户只能购买一件限时购商品
        $orderModel = M('order');
        $map1['user_id'] = $order['user_id'];
        $map1['order_producttype'] = C('Discount_Product');
        $map1['order_status'] = array('neq',C('Unpaid'));
        $order_Discount = $orderModel->where($map1)->select();
        if($order_Discount){
            $result = (new OrderException([
                'code' => 11099,
                'msg' => '每个用户只能购买一件限时购商品'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }


        $map['id'] = $this->addressID;
        $map['user_id'] = $order['user_id'];
        $address = M('address')->where($map)->find();
        if (!$address) {
            //异常 用户和地址不匹配
            $result = (new OrderException([
                'code' => 11007,
                'msg' => '用户和地址不匹配'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }

        $this->orderNo = $order['order_no'];
        return true;
    }


    //修改订单地址
    public function updateAddress()
    {
        $address = M('address')->where("id=$this->addressID")->find();
        if (!$address) {
            $result = (new AddressException([
                'code' => 70000,
                'msg' => '地址不存在'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $order = new OrderModel();
        $order->snap_address = json_encode($address);
        $result = $order->where("id=$this->orderID")->save();
        if (!$result) {
            $result = (new AddressException([
                'code' => 70007,
                'msg' => '地址更新失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        return $result;
    }


    //用户使用积分
    public function bounds($bounds, $orderPrice)
    {

        if($bounds == 0 || $orderPrice<$bounds){
            return $orderPrice;
        }
        if ($orderPrice > $bounds) {
            $this->orderBounds();
            //修改金额
            $boundsOrderPrice = $orderPrice - $bounds;

//            $result = (new OrderException([
//                'code' => $boundsOrderPrice,
//                'msg' => '用户使用积分大于支付金额'
//            ]))->getException();
//            echo json_encode($result, JSON_UNESCAPED_UNICODE);
//            die; //抛出异常

            $this->updateOrderPrice($boundsOrderPrice);
            return $boundsOrderPrice;
        } else {
            $result = (new OrderException([
                'code' => 11011,
                'msg' => '用户使用积分大于支付金额'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
    }


    //添加用户积分到附加表  方便回调后的积分操作
    public function orderBounds()
    {
        $order = M('order')->where("id=$this->orderID")->find();
        $orderAttach = M('orderattach');
        $orderAttach->order_id = $this->orderID;
        $orderAttach->user_id = $order['user_id'];
        $orderAttach->order_bounds = $this->order_bounds;
        $result = $orderAttach->add();
        return $result;
    }

    //使用积分后修改金额
    public function updateOrderPrice($boundsOrderPrice){
        $orderModel = M('order');
        $orderModel->order_price = $boundsOrderPrice;
        $result = $orderModel->where("id=$this->orderID")->save();
        return $result;
    }


}
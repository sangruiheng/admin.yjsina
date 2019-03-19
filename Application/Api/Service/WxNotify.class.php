<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;

use Api\Controller\CommonController;
use Api\Model\OrderModel;
use Api\Model\UserModel;
use think\Db;

Vendor('Wxpay.lib.WxPay#Api');
Vendor('Wxpay.lib.WxPay#Config');

class WxNotify extends \WxPayNotify
{

    public function NotifyProcess($objData, $config, &$msg)
    {

        $data = $objData->GetValues();
        if ($data['result_code'] == 'SUCCESS') {  //支付成功
//            Db::startTrans();
            try {
                $orderModel = M('order');
                $orderNo = $data['out_trade_no'];

                $order_attach = explode(",", $data['attach']);
                //判断订金订单还是全款订单
                $order = $this->isOrderType($order_attach, $orderNo);

                if ($order['order_status'] == 1) {   //未支付

                    $this->sendSms($order_attach[1]);
                    $this->sendMail($order_attach[1]);
                    if ($order['order_producttype'] == 1) {   //订金订单
                        if ($order['order_tailmoney']) {   //尾款支付 获得积分 更新支付状态
                            //更新status  未支付改为已支付 记录支付方式
                            $this->updateOrderStatus($order['id'], $data['trade_type']);
                            //积分操作
                            $this->updateBounds($data, $order);
                        }
                    } elseif ($order['order_producttype'] == 2 || $order['order_producttype'] == 3) {   //全款订单
                        $orderService = new Order();
                        $stockStatus = $orderService->checkOrderStock($order['id'], $order['user_id']);
//                        $fp = fopen('test5.txt', 'a+b');
//                        fwrite($fp, var_export($data, true));
//                        fclose($fp);
                        $orderModel->where("order_no='$orderNo'")->save();
                        if ($stockStatus['pass']) {    //检测库存量通过
                            //更新status  未支付改为已支付 记录支付方式
                            $this->updateOrderStatus($order['id'], $data['trade_type']);
                            //减去库存量
                            $this->reduceStock($order['id'], $stockStatus);
                            //积分操作
                            $this->updateBounds($data, $order);
                        }
                    }

                }
//                Db::commit();
                return true;
            } catch (Exception $ex) {
                // 如果出现异常，向微信返回false，请求重新发送通知
//                Db::rollback();
                return false;
            }

        } else { //支付失败   true false只是用来控制微信是否继续还向自己的服务器发送消息
            return true;
        }
    }

    //发送短信
    protected function sendSms($order_id)
    {

        $orderModel = D('order');
        $order = $orderModel->relation('user')->where("id=$order_id")->find();
        //发送给用户
        $user_phone = $order['tel'];
        //判断订金订单还是全款订单
        $url = 'http://www.yjsina.com/SmsOrderDetail/View.html?uid='.intval($order['user_id']).urlencode('&').'order_id='.intval($order['id']);
        if($order['order_producttype'] == 1){  //订金  有实体店位置
            $business = $this->getBusiness($order['snap_items']);
            $user_content = '【新浪优选】感谢您选择新浪优选！,请出示此链接到实体店体验您选购的产品。' . $url . '实体店位置：'.$business[0]['business_address'] ;
        }elseif ($order['order_producttype'] == 2 || $order['order_producttype'] == 3){ // 全款
            $user_content = '【新浪优选】感谢您选择新浪优选！,查看您选购的产品。' . $url ;
        }
        $result = (new CommonController())->sendSms($user_content, $user_phone);
//        $fp = fopen('test8.txt', 'a+b');
//        fwrite($fp, var_export($user_content, true));
//        fclose($fp);
        //发送给商家
        $business_content = '【新浪优选】您新浪优选有一笔订单以生成，请查看'.$url;
        $business = $this->getBusiness($order['snap_items']);
        for ($i = 0; $i < count($business); $i++) {
            $business_phone = $business[$i]['business_tel'];
            $result = (new CommonController())->sendSms($business_content, $business_phone);
        }

        //发送给平台
        $platform_phone = 13082055003;
        $platform_content = '【新浪优选】平台有一笔订单生成，请注意查看';
        $result = (new CommonController())->sendSms($platform_content, $platform_phone);

    }


    //发送邮件
    protected function sendMail($order_id){
        $orderModel = D('order');
        $order = $orderModel->relation('user')->where("id=$order_id")->find();
        $url = 'http://www.yjsina.com/SmsOrderDetail/View.html?uid='.intval($order['user_id']).'&order_id='.intval($order['id']);
        //发送给商家
        $business_content = '【新浪优选】您新浪优选有一笔订单以生成，请查看'.$url;
        $business = $this->getBusiness($order['snap_items']);
        for ($i = 0; $i < count($business); $i++) {
            $business_mailbox = $business[$i]['business_mailbox'];
            if($business_mailbox){
                $result = (new CommonController())->sendMail($business_mailbox, $business_content);
            }
        }

    }

    //获取当前用户购买商品的商家
    protected function getBusiness($snap_items)
    {
        $products = json_decode($snap_items, true);
        $businessModel = M('business');
        $business_ids = [];
        foreach ($products as $value) {
            array_push($business_ids, $value['business_id']);
        }
        $map['id'] = array('in', $business_ids);
        $business = $businessModel->where($map)->select();
        return $business;
    }

    protected function isOrderType($order_attach, $orderNo)
    {
        $orderModel = M('order');
        if ($order_attach[0] == 1) {   //订金
            $orderModel->Deposit_type = 1;
            $orderModel->where("order_no='$orderNo'")->save();
            $order = M('order')->where("tailmoney_no='$orderNo'")->find();
            return $order;
        } else if ($order_attach[0] == 2) { //全款
            $order = M('order')->where("order_no='$orderNo'")->find();
            return $order;
        }
    }

    private function updateOrderStatus($orderID, $trade_type)
    {
        $order = new OrderModel();
        $data['order_status'] = C('Paid');
        if ($trade_type == 'JSAPI') {
            $data['order_pay'] = 1;
        }
        if ($trade_type == 'MWEB') {
            $data['order_pay'] = 2;
        }
        $order->where("id=$orderID")->save($data);
    }

    private function reduceStock($orderID, $stockStatus)
    {
        foreach ($stockStatus['pStatusArray'] as $singlePStatus) {
            $map['order_id'] = $orderID;
            $map['product_id'] = $singlePStatus['id'];
            $orderProduct = M('orderproduct')->where($map)->find();
            M('productattr1')->where("id=" . $orderProduct['productattr_id'])->setDec('stock', $singlePStatus['count']);
        }
    }

    private function updateBounds($data, $Orders)
    {
        $userModel = M('user');
        $productPrice = $data['total_fee'] * 0.01;  //把分转化成元  支付金额
        $order = D('order')->relation('orderAttach')->where("id=" . $Orders['id'])->find();
        $bounds_detail = '购买订单号' . $data['out_trade_no'] . '的商品';
        //判断用户金额是否大于积分金额
//           100  10
        if ($productPrice >= $order['orderAttach']['order_bounds']) {
            if ($Orders['order_producttype'] == 2) {    //订金订单不使用积分
                //减去用户使用积分
                $user = $userModel->where("id=" . $order['user_id'])->find();
                //判断积分相减后是否为负数
                if ($user['bounds'] - $order['orderAttach']['order_bounds'] >= 0) {
                    $userModel->where("id=" . $order['user_id'])->setDec('bounds', $order['orderAttach']['order_bounds']);
                    (new CommonController())->boundsDetail($order['user_id'], $order['orderAttach']['order_bounds'], $bounds_detail, C('Dn_Bounds')); //记录积分明细
                }
            }
        }
        //根据用户支付金额增加积分
        if ($productPrice >= 100) {
            $productPrice = $productPrice * 0.01;  //每消费100增加1积分
            $userModel->where("id=" . $order['user_id'])->setInc('bounds', floor($productPrice));  //增加积分
            (new CommonController())->boundsDetail($order['user_id'], $productPrice, $bounds_detail, C('Up_Bounds')); //记录积分明细
        }
    }

}
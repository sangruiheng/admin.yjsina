<?php

namespace Manage\Controller;

use Manage\Model\OrderModel;
use Think\Controller;

Vendor('Wxpay.lib.WxPay#Api');
Vendor('Wxpay.lib.WxPay#Config');
Vendor('Wxpay.lib.WxPay#JsApiPay');
Vendor('Wxpay.lib.log');

class RefundController extends CommonController
{


    //已付款订单退款
/*    public function orderRefund()
    {
        if (!empty($_GET['keyWord'])) {
//            $map = $this->Search('order', $_GET['keyWord']);
        }


        if ($_GET['status']) {
            $map['order_status'] = $_GET['status'];
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }

        $orderModel = M('order');
        $refundorderModel = D('refundorder');
        $map['refund_type'] = array('NEQ', 0);
        $order = $refundorderModel->relation('order')->where($map)->order('id desc')->page($p . ',10')->select();
//                print_r($order);
        foreach ($order as &$value) {
            $value['snap_address'] = json_decode($value['order']['snap_address'], true);
            $value['snap_items'] = json_decode($value['order']['snap_items'], true);
            $value['order_status'] = OrderModel::refundStatus($value['orefund_type']);
            $value['order']['order_producttype'] = OrderModel::orderProductType($value['order']['order_producttype']);
            $value['order']['nickName'] = urldecode($value['order']['nickName']);
            if(!$value['order']['nickName']){
                $value['order']['nickName'] = $value['order']['tel'];
            }
        }
        $count = $refundorderModel->count();
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
//        print_r($order);
        $this->assign('page', $Page->show());
        $this->assign('list', $order);
        $this->display();
    }*/

    //已付款订单退款
    public function orderRefund()
    {
        $keyWord = $_GET['keyWord'];
        if (!empty($keyWord)) {
            $where["order_no|order_price|nickName|tel|orefund_time|order_status|order_id|order_producttype|order_count|order_tailmoney"] = array('like', "%$keyWord%");
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $refundorderViewModel = D('refundorderView');
        $order = $refundorderViewModel->where($where)->order('id desc')->page($p . ',10')->select();
//                print_r($order);
        foreach ($order as &$value) {
            $value['snap_address'] = json_decode($value['snap_address'], true);
            $value['order_status'] = OrderModel::refundStatus($value['orefund_type']);
            $value['order_producttype'] = OrderModel::orderProductType($value['order_producttype']);
            $value['nickName'] = urldecode($value['nickName']);
            if (!$value['nickName']) {
                $value['nickName'] = $value['tel'];
            }
        }
        $count = $refundorderViewModel->where($where)->count();
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
//        print_r($order);
        $this->assign('page', $Page->show());
        $this->assign('list', $order);
        $this->display();
    }


    //已付款订单退款 不同意
    public function refundOpinionData()
    {
        $id = $_POST['id'];
        $refundorderModel = D('refundorder');
        $refundorderModel->orefund_type = C('NoRefund');
        $refundorderModel->orefund_nocause = $_POST['refund_nocause'];
        $refundorder = $refundorderModel->where("id=$id")->save();
        if ($refundorder) {
            $this->success('编辑成功！', U('Refund/orderRefund'));
        }
    }


    //已付款订单退款 同意
    public function WxOrderRefund()
    {
        //1.全款订单 判断有无运费 有运费(总价格为订单价格，价格为总价格-运费价格) 无运费(总价格为总价格为订单价格,价格为总价格)
        //2.订金订单 判断有无运费 有运费(总价格为订金价格，价格为订金价格) 无运费(总价格为订金价格，价格为订金价格) 在退尾款 判断有无运费
        $orderID = $_GET['order_id'];
        $orefund_id = $_GET['orefund_id'];
        $refundorderModel = M('refundorder');
        $orderModel = M('order');
        $order = $orderModel->where("id=$orderID")->find();
        $orderPrice = $order['order_price'];
        $refundorder = $refundorderModel->where("id=$orefund_id")->find();

        //有运费时
        if ($refundorder['orefund_freight'] > 0) {
            $totalPrice = $order['order_price'];
            if ($order['order_producttype'] == 1) {  //订金
                $orderPrice = $order['order_price'];
            } else if ($order['order_producttype'] == 2) {  //全款
                $orderPrice = $order['order_price'] - $refundorder['orefund_freight'];
            }
        } else { //无运费
            $totalPrice = $order['order_price'];
            $orderPrice = $order['order_price'];
        }
        $config = new \WxPayConfig();
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($order['order_no']);            //自己的订单号
//        $input->SetTransaction_id($order['transaction_id']);    //微信官方生成的订单流水号，在支付成功中有返回
        $input->SetOut_refund_no($this->getRandom(64));            //退款单号
        $input->SetTotal_fee($totalPrice * 100);                        //订单总金额，单位为分
        $input->SetRefund_fee($orderPrice * 100);            //退款总金额 单位为分，只能为整数
        $input->SetOp_user_id($config->GetMerchantId());               //商户号
        $result = \WxPayApi::refund($config, $input);
        if (($result['return_code'] == 'SUCCESS') && ($result['result_code'] == 'SUCCESS')) {
            //修改订单状态
            $orderModel->order_status = C('evaluated');   //总状态 交易成功
            $orderModel->where("id=$orderID")->save();
            //修改退款表状态
            $refundorderModel = M('refundorder');
            $refundorderModel->orefund_type = 2;   //发货状态 退款成功
            $refundorderModel->where("id=$orefund_id")->save();
            //退尾款
            if ($order['order_producttype'] == 1) {
                //有运费时
                if ($refundorder['orefund_freight'] > 0) {
                    $totalPrice = $order['order_tailmoney'];
                    $orderPrice = $order['order_tailmoney'] - $refundorder['orefund_freight'];
                } else { //无运费
                    $totalPrice = $order['order_tailmoney'];
                    $orderPrice = $order['order_tailmoney'];
                }
                $this->WxProductDJRefund($totalPrice, $orderPrice, $order['tailmoney_no']);
            }
            echo "<script>alert('退款成功');location.href='" . $_SERVER["HTTP_REFERER"] . "';</script>";
        } else if (($result['return_code'] == 'FAIL') || ($result['result_code'] == 'FAIL')) {
            //退款失败
            //原因
            $reason = (empty($result['err_code_des']) ? $result['return_msg'] : $result['err_code_des']);
            print_r($reason);

        } else {
            //失败

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


    //已发货商品退款
   /* public function productRefund()
    {
        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('order', $_GET['keyWord']);
        }

        if ($_GET['status']) {
            $map['order_status'] = $_GET['status'];
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }

        $deliveryModel = M('delivery');
        $refundproductModel = D('refundproduct');
        $orderModel = M('order');
        $map['refund_type'] = array('NEQ', 0);
        $result = $refundproductModel->relation(array('delivery','user'))->order('id desc')->page($p . ',10')->select();
//        print_r($result);
        foreach ($result as &$value) {
            $order = $orderModel->where("id=" . $value['delivery']['order_id'])->find();
            $snap_items = json_decode($order['snap_items'], true);
            $snap_address = json_decode($order['snap_address'], true);
            $value['snap_address'] = $snap_address;
            foreach ($snap_items as $item) {
                if ($value['delivery']['product_id'] == $item['id']) {
                    $value['product_name'] = $item['name'];
                    $value['product_img'] = $item['image'];
                    $value['product_count'] = $item['count'];
                    $value['product_totalPrice'] = $item['totalPrice'];
                    $value['productvalue'] = $item['productvalue'];
                    $value['product_status'] = OrderModel::deliveryStatus($value['delivery']['product_status']);    //订单商品状态
                    $value['refund_type_name'] = OrderModel::refundStatus($value['prefund_type']);   //订单类型
                    $value['order_no'] = $order['order_no'];
                    $value['order_tailmoney'] = $order['order_tailmoney'];
                    $value['order_producttype'] = OrderModel::orderProductType($order['order_producttype']);
                    $value['delivery']['nickName'] = urldecode($value['delivery']['nickName']);
                    if(!$value['delivery']['nickName']){
                        $value['delivery']['nickName'] = $value['delivery']['tel'];
                    }
                }
            }
        }
        $count = $refundproductModel->relation('delivery')->count();
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
//        print_r($result);
        $this->assign('page', $Page->show());
        $this->assign('list', $result);
        $this->display();
    }*/




    //已发货商品退款
    public function productRefund()
    {
        $keyWord = $_GET['keyWord'];
        if (!empty($keyWord)) {
            $map["order_no|order_price|nickName|tel|prefund_cause|prefund_time|product_status|order_producttype"] = array('like', "%$keyWord%");
        }

        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }

        $refundproductViewModel = D('refundproductView');
        $result = $refundproductViewModel->where($map)->order('id desc')->page($p . ',10')->select();
//        print_r($result);
        foreach ($result as &$value) {
            $value['snap_items'] = json_decode($value['snap_items'], true);
            $value['snap_address'] = json_decode($value['snap_address'], true);
            foreach ($value['snap_items'] as $item) {
                if ($value['product_id'] == $item['id']) {
                    $value['product_name'] = $item['name'];
                    $value['product_img'] = $item['image'];
                    $value['product_count'] = $item['count'];
                    $value['product_totalPrice'] = $item['totalPrice'];
                    $value['productvalue'] = $item['productvalue'];
                    $value['product_status'] = OrderModel::deliveryStatus($value['product_status']);    //订单商品状态
                    $value['refund_type_name'] = OrderModel::refundStatus($value['prefund_type']);   //订单类型
                    $value['order_producttype'] = OrderModel::orderProductType($value['order_producttype']);
                    $value['nickName'] = urldecode($value['nickName']);
                    if (!$value['nickName']) {
                        $value['nickName'] = $value['tel'];
                    }
                }
            }
            unset($value['snap_items']);
        }
        $count = $refundproductViewModel->where($map)->count();
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
//        print_r($result);
        $this->assign('page', $Page->show());
        $this->assign('list', $result);
        $this->display();
    }

    //已发货商品退款 不同意
    public function productRefundOpinionData()
    {
        $refund_id = $_POST['id'];
        $deliveryID = $_POST['deliver_id'];
        $deliveryModel = M('delivery');
        //修改退款记录
        $refundproductModel = D('refundproduct');
        $refundproductModel->prefund_type = C('NoRefund');
        $refundproductModel->prefund_nocause = $_POST['refund_nocause'];
        $refundproduct = $refundproductModel->where("id=$refund_id and delivery_id=$deliveryID")->save();
        //修改订单发货表状态 退款未同意
//        $deliveryModel->product_status = C('RefundERR');
//        $deliveryModel->where("id=$deliveryID")->save();
        //修改总订单状态 订单总状态为退款之前的状态 多个商品时按照最低的状态给

        if ($refundproduct) {
            $this->success('编辑成功！', U('Refund/productRefund'));
        }
    }

    //已发货商品退款 同意
    public function WxProductRefund()
    {
        //1.全款订单 判断有无运费 有运费(总价格为订单价格，价格为总价格-运费价格) 无运费(总价格为总价格为订单价格,价格为总价格)
        //2.订金订单 判断有无运费 有运费(总价格为订金价格，价格为订金价格) 无运费(总价格为订金价格，价格为订金价格) 在退尾款 判断有无运费
        $deliver_id = $_GET['deliver_id'];
        $product_totalPrice = $_GET['product_totalPrice'];
        $order_id = $_GET['order_id'];
        $refund_id = $_GET['refund_id'];
        $deliveryModel = M('delivery');
        $orderModel = M('order');
        $refundproductModel = M('refundproduct');
        $delivery = $deliveryModel->where("id=$deliver_id")->find();
        $order = $orderModel->where("id=" . $delivery['order_id'])->find();
        $refundproduct = $refundproductModel->where("id=$refund_id")->find();
        
        //有运费时
        if ($refundproduct['prefund_freight'] > 0) {
            $totalPrice = $order['order_price'];
            if ($order['order_producttype'] == 1) {  //订金
                $orderPrice = $product_totalPrice;
            } else if ($order['order_producttype'] == 2) {  //全款
                $orderPrice = $product_totalPrice - $refundproduct['prefund_freight'];
            }
        } else { //无运费
            $totalPrice = $order['order_price'];
            $orderPrice = $product_totalPrice;
        }

        $config = new \WxPayConfig();
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($order['order_no']);            //自己的订单号
//        $input->SetTransaction_id($order['transaction_id']);    //微信官方生成的订单流水号，在支付成功中有返回
        $input->SetOut_refund_no($this->getRandom(64));            //退款单号
        $input->SetTotal_fee($totalPrice * 100);            //订单总金额，
        $input->SetRefund_fee($orderPrice * 100);            //退款总金额，单位为分，只能为整数
        $input->SetOp_user_id($config->GetMerchantId());               //商户号
        $result = \WxPayApi::refund($config, $input);    //退款操作
        if (($result['return_code'] == 'SUCCESS') && ($result['result_code'] == 'SUCCESS')) {
            //退款成功
            //修改退款表状态
            $refundproductModel = M('refundproduct');
            $refundproductModel->prefund_type = 2;
            $refundproductModel->where("id=$refund_id")->save();
            //修改订单发货表状态
            $deliveryModel->product_status = 5;
            $deliveryModel->where("id=$deliver_id")->save();
            //修改总状态
            //对比订单表的商品与发货订单表中的相同订单id为已退款的商品  如果都已退款 则总状态为交易完成
            $order = $orderModel->where("id=$order_id")->find();
            $order_product = json_decode($order['snap_items'], true);
            $where['product_status'] = array('egt', 4);
            $where['order_id'] = $order['id'];
            $delivery_product = $deliveryModel->where($where)->select();
            $i = 0;
            foreach ($order_product as $value) {
                foreach ($delivery_product as $item) {
                    if ($value['id'] == $item['product_id']) {
                        $i++;
                    }
                }
            }
            if (count($order_product) == $i) {
                $orderModel->order_status = C('evaluated');  //交易完成
            }
            //退尾款
            if ($order['order_producttype'] == 1) {
                //有运费时
                if ($refundproduct['prefund_freight'] > 0) {
                    $totalPrice = $order['order_tailmoney'];
                    $orderPrice = $order['order_tailmoney'] - $refundproduct['prefund_freight'];
                } else { //无运费
                    $totalPrice = $order['order_tailmoney'];
                    $orderPrice = $order['order_tailmoney'];
                }
                $this->WxProductDJRefund($totalPrice, $orderPrice, $order['tailmoney_no']);
            }
            echo "<script>alert('退款成功');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";

        } else if (($result['return_code'] == 'FAIL') || ($result['result_code'] == 'FAIL')) {
            //退款失败
            //原因
            $reason = (empty($result['err_code_des']) ? $result['return_msg'] : $result['err_code_des']);
            print_r($reason);

        } else {
            //失败

        }
    }


    //订金订单 退尾款
    public function WxProductDJRefund($order_totalTailmoney, $order_tailmoney, $tailmoney_no)
    {
        $config = new \WxPayConfig();
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($tailmoney_no);            //自己的订单号
//        $input->SetTransaction_id($order['transaction_id']);    //微信官方生成的订单流水号，在支付成功中有返回
        $input->SetOut_refund_no($this->getRandom(64));            //退款单号
        $input->SetTotal_fee($order_totalTailmoney * 100);            //订单总金额，单位为分
        $input->SetRefund_fee($order_tailmoney * 100);            //退款总金额，订单总金额，单位为分，只能为整数
        $input->SetOp_user_id($config->GetMerchantId());               //商户号
        $result = \WxPayApi::refund($config, $input);    //退款操作
        if (($result['return_code'] == 'SUCCESS') && ($result['result_code'] == 'SUCCESS')) {
            //退款成功
//            echo "<script>alert('退款成功');location.href='" . $_SERVER["HTTP_REFERER"] . "';</script>";
            return true;
        } else if (($result['return_code'] == 'FAIL') || ($result['result_code'] == 'FAIL')) {
            //退款失败
            //原因
            $reason = (empty($result['err_code_des']) ? $result['return_msg'] : $result['err_code_des']);
            print_r($reason);

        } else {
            //失败

        }
    }


    //修改已支付退款金额 增加邮费   bug订单订金状态
    public function updateOrderPriceData()
    {
        $orefund_freight = $_POST['orefund_freight'];
        $order_id = $_POST['id'];
        $orefund_id = $_POST['aaa'];
        $orderModel = M('order');
        $refundorderModel = M('refundorder');
        $order = $orderModel->where("id=$order_id")->find();

        //增加订单退款记录中的邮费
        if ($order['order_price'] > $orefund_freight || $order['order_tailmoney'] > $orefund_freight) {
            $refundorderModel->orefund_freight = $orefund_freight;
            $refundorderModel->where("id=$orefund_id")->save();
        }
        $this->success('退款成功！', U('Refund/orderRefund'));

    }


    //修改已发货退款金额 增加邮费   bug订单订金状态
    public function updateProductPriceData()
    {
        $prefund_freight = $_POST['prefund_freight'];
        $order_id = $_POST['id'];
        $prefund_id = $_POST['aaa'];
        $orderModel = M('order');
        $refundorderModel = M('refundproduct');
        $order = $orderModel->where("id=$order_id")->find();

        //增加订单退款记录中的邮费
        if ($order['order_price'] > $prefund_freight || $order['order_tailmoney'] > $prefund_freight) {
            $refundorderModel->prefund_freight = $prefund_freight;
            $refundorderModel->where("id=$prefund_id")->save();
        }
        $this->success('退款成功！', U('Refund/productRefund'));

    }

}

?>
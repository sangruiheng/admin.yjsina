<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-20
 * Time: 下午3:49
 */

namespace Api\Service;


use Api\Controller\CommonController;
use Api\Exception\AddressException;
use Api\Exception\OrderException;
use Api\Model\OrderModel;
use Api\Model\OrderproductModel;

class DepositOrder
{
    protected $uid;
    protected $productID;


    public function place($uid, $productID)
    {

        //创建订单
        $this->uid = $uid;
        $this->productID = $productID;
        $orderSnap = $this->snapOrder();   //生成订单快照信息
        $order = $this->createOrderByTrans($orderSnap); //创建订单
        return $order;


    }

    //创建订单
    private function createOrderByTrans($snap)
    {
//        try {
        $orderNo = $this->makeOrderNo();
        $order = M('order')->where("order_no='$orderNo'")->find();
        if ($order) {
            $orderNo = $this->makeOrderNo();
        }
        $order = new OrderModel();
        $order->user_id = $this->uid;
        $order->order_no = $orderNo;
        $order->order_price = $snap['orderPrice'];
        $order->order_count = $snap['totalCount'];
        $order->snap_img = $snap['snapImg'];
        $order->snap_name = $snap['snapName'];
        $order->snap_address = $snap['snapAddress'];
        $order->snap_items = json_encode($snap['pStatus']);
        $order->snap_attr = $snap['snapAttr'];
        $order->order_addTime = date("Y-m-d H:i:s", time());
        $order->order_producttype = C('Deposit_Product');  //订金订单
        $result = $order->add();

        //填充orderproduct表
//        $orderID = $result;
//        foreach ($this->oProducts as &$p) {
//            $p['order_id'] = $orderID;
//        }
//        $orderProduct = new OrderproductModel();
//        $orderProduct->addAll($this->oProducts);
        return [
            'code' => 200,
            'order_no' => $orderNo,
            'order_id' => $result,
        ];
//        } catch (Exception $ex) {
//            throw $ex;
//        }
    }




    private function snapOrder()
    {
        $snap = [   //定义快照信息
            'orderPrice' => 0,   //订单总价格
            'totalCount' => 0,   //订单总数量
            'pStatus' => [],     //订单总状态  pstatusarray
            'snapAddress' => '',
            'snapName' => '',
            'snapImg' => '',
            'snapAttr' => ''
        ];
        $productModel = M('product');
        $product = $productModel->where("id=$this->productID")->find();

        if($product['product_type'] != 1){
            $result = (new OrderException([
                'code' => 11008,
                'msg' => '该下单商品不是订金商品'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $pStatus = $this->getpStatus($product);

        $snap['orderPrice'] = $pStatus[0]['totalPrice'];
        $snap['totalCount'] = 1;
        $snap['pStatus'] = $pStatus;
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $product['product_name'];
        $snap['snapImg'] = $this->getProductImg();
        $snap['snapAttr'] = '';
        return $snap;
    }

    private function getpStatus($product){
        $pStatus = [   //保存订单中的某一个商品的详细信息
            'id' => null,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0,
            'image' => '',
            'productvalue' => '',
            'business_id' => '',
            'product_brand' => '',
            'orig' => ''
        ];
        $userMember = (new CommonController())->getUserMember($this->uid);
        $pStatus['id'] = $product['id'];
        $pStatus['count'] = 1;
        $pStatus['name'] = $product['product_name'];
        $pStatus['category_id'] = $product['category_id'];
        $pStatus['totalPrice'] = $product['product_djprice'];
//        if($userMember){  //会员折扣
//            $pStatus['totalPrice'] = round($product['product_djprice']*$userMember['membercard']['membercard_discount'],2);
//            $pStatus['orig'] = $product['product_djprice'] - ($product['product_djprice']*$userMember['membercard']['membercard_discount']);
//        }else{
            $pStatus['totalPrice'] = $product['product_djprice'];
            $pStatus['orig'] = 0;
//        }
        $pStatus['product_type'] = $product['product_type'];
        $pStatus['business_id'] = $product['business_id'];
        $pStatus['product_brand'] = $product['product_brand'];
        $pStatus['image'] = $this->getProductImg();
        return array($pStatus);
    }

    private function getProductImg(){
        $productImageModel = M('productimage');
        $map['product_id'] = $this->productID;
        $map['is_thumb'] = 1;
        $productImage = $productImageModel->where($map)->find();
        return C('img_prefix') . $productImage['productimage_url'];
    }


    private function getUserAddress()
    {

//        $map['id'] = $this->oProducts['address_id'];
        $addressModel = M('address');
        $address = $addressModel->where("user_id=$this->uid")->find();
        if(!$address){
            $result = (new AddressException([
                'code' => 70000
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $map['user_id'] = $this->uid;
        $map['is_default'] = 1;
        $userAddress = D('address')->where($map)->find();
        if (!$userAddress) {
            $userAddress = M('address')->where("user_id=$this->uid")->order("id desc")->find();
        }
        return $userAddress;
    }


    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }




}
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
use Api\Model\OrderModel;
use Api\Model\OrderproductModel;

class Order
{
    protected $oProducts;   //客户端传来的订单

    protected $products;  //数据库真实的订单 (库存量, 价格)

    protected $uid;


//[{"id":"1","haveStock":true,"count":3,"name":"\u591a\u529f\u80fd\u6599\u7406\u673a\/\u69a8\u6c41\u673a","totalPrice":774},{"id":"6","haveStock":true,"count":2,"name":"\u4fdd\u6e29\u7535\u6c34\u58f6","totalPrice":176},{"id":"8","haveStock":true,"count":3,"name":"\u7537\u5f0f\u57fa\u7840\u8272\u7ec7\u7eaf\u68c9\u5706\u9886T\u6064","totalPrice":89.7}]
    //1.检测库存量  2.创建订单
    public function place($uid, $oProduct)
    {
        //对比这个两个订单来检测库存量
        $this->oProducts = $oProduct;
        $this->products = $this->getProductsByOrder($oProduct);
        $this->uid = $uid;

        $status = $this->getOrderStatus();
        if (!$status['pass']) {   //判断是否有库存
            $status['order_id'] = -1;  //失败返回-1
            return $status;
        }

        //库存量通过 创建订单
        //仅依靠客户端传递来id  count无法满足订单的需求  需要 order product  address

        $orderSnap = $this->snapOrder($status);   //生成订单快照信息
        $order = $this->createOrderByTrans($orderSnap); //创建订单
        $order['pass'] = true;
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
        $order->order_producttype = $this->is_ProductType($snap['pStatus']);  //全款订单或限时购订单
        $result = $order->add();

        //填充orderproduct表
        $orderID = $result;
        foreach ($this->oProducts as &$p) {
            $p['order_id'] = $orderID;
        }
        $orderProduct = new OrderproductModel();
        $orderProduct->addAll($this->oProducts);
        return [
            'order_no' => $orderNo,
            'order_id' => $orderID,
        ];
//        } catch (Exception $ex) {
//            throw $ex;
//        }
    }

    //设置订单类型
    protected function is_ProductType($products){
        $productModel = M('product');
        foreach ($products as $value){
            $product = $productModel->where("id=".$value['id'])->find();
            if($product['product_type'] == C('Discount_Product')){
                return C('Discount_Product');
            }
        }
        return C('Full_product');

    }

    //分割属性
    public function imProductValue($productValue)
    {
        $res = '';
        foreach ($productValue as $val) {
            $res .= $val["productvalue_id"] . ",";
        }
        return substr($res, 0, -1);
    }


    private function snapOrder($status)
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
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $status['pStatusArray'][0]['name'];
        $snap['snapImg'] = $status['pStatusArray'][0]['image'];
        $snap['snapAttr'] = $status['pStatusArray'][0]['productvalue'];

        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }
        return $snap;
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

    //根据客户端传递的订单信息来查找数据库真实信息
    private function getProductsByOrder($oProduct)
    {
        $arr = [];
        foreach ($oProduct as $item) {
            $product = M('product');
            $map['t.id'] = $item['producttype_id'];
            $map['a.id'] = $item['productattr_id'];
            $result = $product
                ->alias('p')
                ->join('icpnt_producttype1 as t ON p.id = t.product_id')
                ->join('icpnt_productattr1 as a ON t.id = a.attrtype_id')
                ->where($map)
//                ->field('icpnt_product.product_price',true)
                ->find();
            array_push($arr, $result);
        }
        return $arr;
    }

    //获取订单的真实状态
    private function getOrderStatus()
    {
        $status = [
            'pass' => true,    //库存量状态  一组商品
            'orderPrice' => 0, //订单总价格
            'pStatusArray' => []  //（保存订单所有商品的详细信息）历史订单存储 不是动态查询的
        ];
        foreach ($this->oProducts as $oProduct) {  //遍历客户端传来的订单
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products, $oProduct['producttype_id'], $oProduct['productattr_id']);

            //填充pStatus
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;  //返回这个订单的状态
    }

    //两个作用 1.作为pstatusarray的参数  2.这个里面的库存量有一个不通过。将影响上方法的pass状态
    private function getProductStatus($oPID, $oCount, $products, $productTypeID, $productAttrID)
    {
        $pStatus = [   //保存订单中的某一个商品的详细信息
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0,
            'image' => '',
            'productvalue' => '',
            'business_id' => '',
            'category_id' => '',
            'product_brand' => '',
            'orig' => ''
        ];
        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['product_id']) {
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            // 客户端传递的productid有可能根本不存在
//            throw new OrderException(
//                [
//                    'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
//                ]);
            return false;

        } else {
            //填充pstatus
            $userMember = (new CommonController())->getUserMember($this->uid);
            $product = $products[$pIndex];
            $pStatus['id'] = $product['product_id'];
            $pStatus['name'] = $product['product_name'];
            $pStatus['count'] = $oCount;
            if($userMember && $product['category_id'] != 59){  //会员折扣
                    $pStatus['totalPrice'] = round(($product['price'] * $oCount)*$userMember['membercard']['membercard_discount'],2);
                    $pStatus['orig'] = ($product['price'] * $oCount) - ($product['price'] * $oCount)*$userMember['membercard']['membercard_discount'];
            }else{
                $pStatus['totalPrice'] = $product['price'] * $oCount;
                $pStatus['orig'] = 0;
            }
            $pStatus['image'] = C('img_prefix') . $product['attr_img'];
            $pStatus['productvalue'] = $this->getProductValue($productTypeID, $productAttrID);
            $pStatus['business_id'] = $product['business_id'];
            $pStatus['category_id'] = $product['category_id'];
            $pStatus['product_brand'] = $product['product_brand'];

            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;


    }

    //设置家电类的不享受会员
//    public function setApplianceAuthority($category_id){
//        $categoryModel = M('navcategory');
//        $category = $categoryModel->where("id=$category_id")->find();
//        if($category['navcate_pid'] == 14){
//            return false;
//        }
//        return true;
//    }

    //根据productvalue数组查询商品的属性值  19,18
    public function getProductValue($productTypeID, $productAttrID)
    {
        $productType = M('producttype1');
        $productAttr = M('productattr1');
        $res_productType = $productType->where("id=$productTypeID")->find();
        $res_productAttr = $productAttr->where("id=$productAttrID")->find();
        $productValue = $res_productType['color_name'] . '*' . $res_productAttr['attr_name'];
        return $productValue;
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

    public function getReadyOrder($uid, $orderID)
    {

        $produtIDS = [];
        $orderModel = D('order');
        $deliveryModel = M('delivery');
        $order = $orderModel->relation('user')->where("id=$orderID and user_id=$uid")->field("order_freight,order_message,snap_img,snap_name,order_addTime,snap_attr", true)->find();
        $order['snap_items'] = json_decode($order['snap_items'], true);
        $order['snap_address'] = json_decode($order['snap_address'], true);

        //产品可用最大积分
        foreach ($order['snap_items'] as $value){
            array_push($produtIDS, $value['id']);
        }
        $map['id']  = array('in', $produtIDS);
        $product = M('product')->where($map)->select();
        foreach ($product as $item){
            $order['product_bounds'] += $item['product_bounds'];  //用户可用最大积分
        }
        $maps['user_id'] = $uid;
        $maps['order_status'] = C('evaluated');
        $res_order = $orderModel->where($maps)->find();
        $where['user_id'] = $uid;
        $where['product_status']  = array('EGT',C('evaluated'));
        $res_delivery = $deliveryModel->where($where)->find();
        if($res_order || $res_delivery){
            $order['is_newpeople'] = 1;    //是新人
        }else{
            $order['is_newpeople'] = 0;
        }

        //会员用户
        $order['order_orig'] = 0;
        $userMember = (new CommonController())->getUserMember($uid);
        if($userMember){
            foreach ($order['snap_items'] as $item){
                $order['order_orig'] += $item['orig'];
            }
        }


        return $order;
    }

    public function getReadyAddress($orderID, $address_id, $uid)
    {
        $produtIDS = [];
        $Order = D('order');
        $Address = D('address');
        $order = $Order->relation('user')->where("id=$orderID and user_id=$uid")->field("order_freight,order_message,snap_img,snap_name,order_addTime,snap_attr", true)->find();
        $order['snap_address'] = json_decode($order['snap_address'], true);
        $order['snap_items'] = json_decode($order['snap_items'], true);
        //产品可用最大积分
        foreach ($order['snap_items'] as $value){
            array_push($produtIDS, $value['id']);
        }
        $map['id']  = array('in', $produtIDS);
        $product = M('product')->where($map)->select();
        foreach ($product as $item){
            $order['product_bounds'] += $item['product_bounds'];  //用户可用最大积分
        }

        $beForAddress = $Address->where("id=" . $order['snap_address']['id'])->find();
        $order['snap_address'] = $beForAddress;
        if ($order['snap_address']['id'] != $address_id) {
            $map['id'] = $address_id;
            $map['user_id'] = $order['user_id'];
            $address = $Address->where($map)->find();
            if (!$address) {
                $result = (new AddressException([
                    'code' => 70006,
                    'msg' => '地址-订单-与当前用户不匹配'
                ]))->getException();
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                die; //抛出异常
            }
            $orders = M('order');
            $orders->snap_address = json_encode($address);
            $orders->where("id=$orderID")->save();
            $order['snap_address'] = $address;
        }

        //会员用户
        $order['order_orig'] = 0;
        $userMember = (new CommonController())->getUserMember($uid);
        if($userMember){
            foreach ($order['snap_items'] as $item){
                $order['order_orig'] += $item['orig'];
            }
        }

        return $order;
    }

    //支付前检测库存量
    public function checkOrderStock($orderID,$uid)
    {
        $this->uid = $uid;
        $oProducts = D('orderproduct')->where("order_id=$orderID")->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($this->oProducts);
        $stats = $this->getOrderStatus();
        return $stats;
    }


}
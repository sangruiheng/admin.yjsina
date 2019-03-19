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
use Api\Exception\ProductException;
use Api\Model\OrderModel;
use Api\Model\UserModel;
use Api\Service\DepositOrder;
use Api\Service\Order;
use Api\Service\Token;
use Api\Service\UserToken;
use Api\Validate\IDMustBePostiveInt;
use Api\Validate\OrderPlace;
use Api\Validate\readyAddress;
use Api\Validate\UserNew;
use Think\Controller;

class OrderController extends CommonController
{
    

    protected $uid;

    function __construct()
    {
        //根据token来获取uid
        $this->uid = Token::getCurrentUid();
        $this->is_user();
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


    //订金商品下单
    public function depositOrder(){
        //商品id
        (new IDMustBePostiveInt())->goCheck();
        if(!(new OrderModel())->is_BindTel($this->uid)){
            $this->ajaxReturn((new ProductException([
                'code' => 20099,
                'msg' => '用户未绑定手机号'
            ]))->getException());
        }
        $result = (new DepositOrder())->place($this->uid, $_POST['id']);
        $this->ajaxReturn($result);
    }


    //全款商品下单
    public function placeOrder()
    {
        //一组商品
        //创建订单 添加默认地址
        $products = $_POST['products'];
//        $products = json_decode($products, true);
        if(!(new OrderModel())->is_BindTel($this->uid)){
            $this->ajaxReturn((new ProductException([
                'code' => 20099,
                'msg' => '用户未绑定手机号'
            ]))->getException());
        }
        if(empty($products)){
            $this->ajaxReturn((new ProductException([
                'code' => 20004,
                'msg' => '商品为空'
            ]))->getException());
        }
        if(!is_array($products)){
            $this->ajaxReturn((new ProductException([
                'code' => 20005,
                'msg' => '商品必须是数组'
            ]))->getException());
        }
        foreach($products as $product){
            if(empty($product['product_id'])){
                $this->ajaxReturn((new ProductException([
                    'code' => 20006,
                    'msg' => '商品id为空'
                ]))->getException());
            }
            if(OrderModel::isPositiveInteger($product['product_id']) == false){
                $this->ajaxReturn((new ProductException([
                    'code' => 20007,
                    'msg' => '商品id必须为正整数'
                ]))->getException());
            }
            if(empty($product['count'])){
                $this->ajaxReturn((new ProductException([
                    'code' => 20008,
                    'msg' => '商品数量为空'
                ]))->getException());
            }
            if(OrderModel::isPositiveInteger($product['count']) == false){
                $this->ajaxReturn((new ProductException([
                    'code' => 20009,
                    'msg' => '商品数量必须为正整数'
                ]))->getException());
            }
            if(empty($product['producttype_id'])){
                $this->ajaxReturn((new ProductException([
                    'code' => 20010,
                    'msg' => '商品属性为空'
                ]))->getException());
            }
            if(OrderModel::isPositiveInteger($product['producttype_id']) == false){
                $this->ajaxReturn((new ProductException([
                    'code' => 20011,
                    'msg' => '商品属性必须为正整数'
                ]))->getException());
            }
            if(empty($product['productattr_id'])){
                $this->ajaxReturn((new ProductException([
                    'code' => 20012,
                    'msg' => '商品规格为空'
                ]))->getException());
            }
            if(OrderModel::isPositiveInteger($product['productattr_id']) == false){
                $this->ajaxReturn((new ProductException([
                    'code' => 20013,
                    'msg' => '商品规格必须为正整数'
                ]))->getException());
            }
            //判断当前商品与属性是否匹配
            $map['t.id'] = $product['producttype_id'];
            $map['a.id'] = $product['productattr_id'];
            $result = M('product')
                ->alias('p')
                ->join('icpnt_producttype1 as t ON p.id = t.product_id')
                ->join('icpnt_productattr1 as a ON t.id = a.attrtype_id')
                ->where($map)
                ->find();
            if(!$result){
                $this->ajaxReturn((new ProductException([
                    'code' => 20014,
                    'msg' => '当前商品与其属性不匹配'
                ]))->getException());
            }
        }
        (new OrderModel())->is_UserDiscountProduct($products,$this->uid);
//        (new OrderPlace())->goChecks($products);
        $order = new Order();
        $status = $order->place($this->uid, $products);
        $this->ajaxReturn($status);

    }

    //获取全部订单
    public function getAllOrder()
    {
        //订单默认显示当前订单的第一个商品 后面加等几件商品
        $result = D('order')->where("user_id=" . $this->uid)->field("id,Deposit_type,order_producttype,order_tailmoney,snap_name,snap_img,snap_address,order_no,snap_attr,order_status,order_price,order_count,snap_items")->order('id desc')->select();
        foreach ($result as &$val) {
            $val['order_status'] = OrderModel::orderStatus($val['order_status']);
            $val['snap_type'] = count(json_decode($val['snap_items']));
            $val['snap_address'] = json_decode($val['snap_address'],true);
            unset($val['snap_items']);
        }
        if (!$result) {
            $this->ajaxReturn((new OrderException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取待付款订单
    public function getWaitOrder()
    {
        $result = (new OrderModel())->getByOrderStatus($this->uid, C('Unpaid'));
        if (!$result) {
            $this->ajaxReturn((new OrderException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取待发货订单
    public function getPaidOrder()
    {
        $result = (new OrderModel())->getByOrderStatus($this->uid, C('Paid'));
        foreach ($result as &$val) {
            $val['order_status'] = OrderModel::orderStatus($val['order_status']);
        }
        if (!$result) {
            $this->ajaxReturn((new OrderException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取订单详情
    public function getOrderDetail()
    {
        (new IDMustBePostiveInt())->goCheck();
        $result = (new OrderModel())->orderDetail($this->uid, $_POST['id']);
        if (!$result) {
            $this->ajaxReturn((new OrderException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //删除订单
    public function delOrder()
    {
        (new IDMustBePostiveInt())->goCheck();
        $result = (new OrderModel())->orderDel($this->uid, $_POST['id']);
        $this->ajaxReturn((new SuccessException([
            'msg' => '删除订单成功!'
        ]))->getException());
    }

    //准备下单页面显示
    public function getReadyOrder()
    {
        //订单id
        $order = new Order();
        $order_product = $order->getReadyOrder($this->uid, $_POST['id']);
        $this->ajaxReturn($order_product);
    }


    //订单地址修改
    public function orderReadyAddress(){
        //address_id order_id
        (new readyAddress())->goCheck();
        $order = (new Order())->getReadyAddress($_POST['order_id'], $_POST['address_id'], $this->uid);
        $this->ajaxReturn($order);
    }

    //取消订单
    public function cencelOrder(){
        // 订单id
        (new IDMustBePostiveInt())->goCheck();
        $result = (new OrderModel())->cencelOrder($this->uid, $_POST['id']);
        $this->ajaxReturn((new SuccessException([
            'msg' => '取消订单成功!'
        ]))->getException());
    }





}


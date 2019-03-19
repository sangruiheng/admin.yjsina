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
use Api\Model\DeliveryModel;
use Api\Service\Logistics;
use Api\Service\Token;
use Api\Validate\Comments;
use Api\Validate\Express;
use Api\Validate\Confirm;

class DeliverController extends CommonController
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

    //查询已发货商品
    public function getDeliveryProduct(){
        //uid   product_status=3
        $deliveryProduct = (new DeliveryModel())->deliveryProduct($this->uid,C('shipped'));
        if(!$deliveryProduct){
            $this->ajaxReturn((new UserException([
                'code' => 90010,
                'msg' => '暂无用户发货商品'
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $deliveryProduct
        ]);

    }


    //查询待评价商品
    public function getEvaluateProduct(){
        //uid   product_status=4
        $evaluatedProduct = (new DeliveryModel())->evaluatedProduct($this->uid,C('evaluated'));
        if(!$evaluatedProduct){
            $this->ajaxReturn((new UserException([
                'code' => 90010,
                'msg' => '暂无用户发货商品'
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $evaluatedProduct
        ]);
    }

    //评价商品
    public function evaProduct(){
        //uid product_id comment_content comment_star  product_attr
        (new Comments())->goCheck();
        $result = (new DeliveryModel())->addEvaProduct($this->uid, $_POST['product_id'], $_POST['comment_content'], $_POST['comment_star'], $_POST['product_attr'], $_POST['delivery_id']);
        if(!$result){
            $this->ajaxReturn((new UserException([
                'code' => 90011,
                'msg' => '添加评论失败'
            ]))->getException());
        }
        $this->ajaxReturn((new SuccessException([
            'msg' => '评论成功'
        ]))->getException());
    }




    //根据订单查询物流
    public function getSendInformation()
    {
//        $ShipperCode = 'YTO';  //快递公司编号
//        $LogisticCode = '800705274074585991'; //物流单号
        (new Express())->goCheck();
        $result = (new Logistics())->getOrderTracesByJson($_POST['ShipperCode'], $_POST['LogisticCode']);
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => json_decode($result)
        ]);
    }


    //确认收货
    public function confirmProduct(){
        //订单商品id
        (new Confirm())->goCheck();
        $delivery = M('delivery');
        $map['id'] = $_POST['id'];
        $map['order_id'] = $_POST['order_id'];
        $res_delivery = $delivery->where($map)->find();
        if(!$res_delivery){
            $this->ajaxReturn((new OrderException([
                'code' => 11003,
                'msg' => '当前商品与订单不匹配'
            ]))->getException());
        }
        if($res_delivery['product_status'] == 4){
            $this->ajaxReturn((new OrderException([
                'code' => 11004,
                'msg' => '请勿重复确认收货'
            ]))->getException());
        }
        $delivery->product_status = 4;
        $delivery->deliver_time = date("Y-m-d H:i:s", time());
        $result = $delivery->where("id=".$_POST['id'])->save();

        $orderModel = M('order');
        $order = $orderModel->where("id=".$_POST['order_id'])->find();

        if($order['order_producttype'] == C('Deposit_Product')){  //订金商品
            $orderModel = M('order');
            $orderModel->order_status = C('evaluated');
            $orderModel->where("id=".$_POST['order_id'])->save();
        }elseif ($order['order_producttype'] == C('Full_product' || $order['order_producttype'] == C('Discount_Product'))){    //全款商品
            (new DeliveryModel())->productContrast($_POST['order_id']);
        }
        if(!$result){
            $this->ajaxReturn((new OrderException([
                'code' => 11001,
                'msg' => '确认收货失败'
            ]))->getException());
        }
        $this->ajaxReturn((new SuccessException([
            'msg' => '确认收货成功'
        ]))->getException());
    }







}
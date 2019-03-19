<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Api\Controller\CommonController;
use Api\Exception\OrderException;
use Api\Service\UserToken;
use Think\Model\RelationModel;

class OrderModel extends RelationModel
{
    protected $_link = array(
        'user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'user',//要关联的表名
            'foreign_key' => 'user_id', //外键的字段名称
            'as_fields' => 'bounds:user_bounds,tel:tel',  //被关联表中的字段名：要变成的字段名
            //       'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
        ),
        'orderAttach' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'orderattach',//要关联的表名
            'foreign_key' => 'order_id', //外键的字段名称
//            'as_fields' => 'bounds:user_bounds',  //被关联表中的字段名：要变成的字段名
            //       'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
        ),
        'delivery' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'delivery',//要关联的表名
            'foreign_key' => 'order_id', //外键的字段名称
//            'as_fields' => 'groupID,imgPath',  //被关联表中的字段名：要变成的字段名
                   'relation_deep'    =>    'express',   //多表关联  关联第三个表的名称
        ),
        'delivery1' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'delivery',//要关联的表名
            'foreign_key' => 'order_id', //外键的字段名称
//            'as_fields' => 'groupID,imgPath',  //被关联表中的字段名：要变成的字段名
            'relation_deep'    =>    array('refundproduct','express','product'),   //多表关联  关联第三个表的名称
//            'relation_deep'    =>    'express',   //多表关联  关联第三个表的名称

        ),
        'refundorder' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'refundorder',//要关联的表名
            'foreign_key' => 'order_id', //外键的字段名称
            'mapping_order' => 'id desc',  //被关联表中的字段名：要变成的字段名
            'mapping_limit'    =>    1,   //多表关联  关联第三个表的名称
        ),

    );

    public static function isPositiveInteger($value)
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
        }

    }


    public static function orderStatus($status){
        switch ($status){
            case '1':
                return "待付款";
                break;
            case '2':
                return "待发货";
                break;
            case 3:
                return "已发货";
                break;
            case 4:
                return "交易成功";
                break;
            case 5:
                return "已取消";
                break;
            case 6:
                return "退款中";
                break;
            case 7:
                return "退款成功";
                break;
            case 8:
                return "退款未同意";
                break;
            default:
                return '未知状态';
        }
    }

    //判断下单是否绑定手机号
    public function is_BindTel($uid){
        $userModel = M('user');
        $user = $userModel->where("id=$uid")->find();
        if(!$user['tel']){
            return false;
        }else{
            return true;
        }
    }

    //判断用户购买的限时购商品数量
    public function is_UserDiscountProduct($products,$uid){
        //判断是否是限时购商品
        $productModel = M('product');
        $n = 0;
        foreach ($products as $value){
            $product = $productModel->where("id=".$value['product_id'])->find();
            if($product['product_type'] == C('Discount_Product')){
                //判断限时购商品是否是一个
                if($value['count'] != 1){
                    $result = (new OrderException([
                        'code' => 11098,
                        'msg' => '限时购商品只能购买一个'
                    ]))->getException();
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    die; //抛出异常
                }
                $n++;
            }
        }
        //限时购订单
        if($n>0){
            $orderModel = M('order');
            $map['user_id'] = $uid;
            $map['order_producttype'] = C('Discount_Product');
            $map['order_status'] = array('neq',C('Unpaid'));
            $order = $orderModel->where($map)->select();
            if($order){
                $result = (new OrderException([
                    'code' => 11099,
                    'msg' => '每个用户只能购买一件限时购商品'
                ]))->getException();
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                die; //抛出异常
            }
        }else{
            return true;
        }
    }


    //获取订单详情
    public function orderDetail($uid, $id){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $productModel = M('product');
        $deliveryModel = M('delivery');
        $result = self::where($map)->relation('refundorder')->field("snap_attr,snap_img,order_freight,user_id,snap_name",true)->find();
        $result['snap_address'] = json_decode($result['snap_address']);
        $result['snap_items'] = json_decode($result['snap_items'],true);
        if($result['order_producttype'] == 1){
            $result['order_producttype'] = '订金订单';
        }else if($result['order_producttype'] == 2){
            $result['order_producttype'] = '全款订单';
        }

        //会员用户
        $result['order_orig'] = 0;
        $userMember = (new CommonController())->getUserMember($uid);
        if($userMember){
            foreach ($result['snap_items'] as $item){
                $result['order_orig'] += $item['orig'];
            }
        }

        //订单中的商品状态

        //1.订单退款
        if($result['refundorder']){
            $result['orefund_type_name'] = DeliveryModel::refundStatus($result['refundorder'][0]['orefund_type']);
        }
        //2.订单中的商品状态
        if($result['order_status'] == C('Unpaid')){
            foreach ($result['snap_items'] as &$item){
                $item['product_status'] = '待付款';
            }
        }
//        elseif (){
//
//        }
        //订单详情的用户抵用积分
        if($result['order_status'] > C('Unpaid')){
            $orderAttach = M('orderattach')->where($map)->find();
            //用户购买金额超过1000
            if($orderAttach){
                $result['order_bounds'] = $orderAttach['order_bounds'];
            }else{
                $result['order_bounds'] = 0;
            }
        }else{
            $result['order_bounds'] = 0;
        }

        //后台删除商品时前台显示已下架
        foreach ($result['snap_items'] as &$item){
            $product = $productModel->where("id=".$item['id'])->find();
            if(!$product){
                $item['product_status'] = '已下架';
            }
        }
//        $result['order_status'] = self::orderStatus($result['order_status']);
        return $result;
    }

    //删除订单
    public function orderDel($uid, $id){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $order = self::where($map)->find();
        if(!$order){
           $result = (new OrderException())->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        try {
            $result = self::where($map)->delete();
            $result = D('orderproduct')->where("order_id=$id")->delete();
        }catch (\Exception $e){
            echo json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE);
            die;
        }
        return $result;
    }


    public function getByOrderStatus($uid, $status){

        $map['user_id'] = $uid;
        $map['order_status'] = $status;
        $result = D('order')->relation('refundorder')->where($map)->field("id,order_price,snap_name,snap_img,order_no,snap_attr,order_status,order_count,snap_items,snap_address,order_tailmoney,Deposit_type,order_producttype")->order('id desc')->select();
        foreach ($result as &$val){
            $val['snap_type'] = count(json_decode($val['snap_items']));
            $val['snap_address'] = json_decode($val['snap_address'],true);
            unset($val['snap_items']);
            if($val['refundorder']){
                $val['orefund_type'] = $val['refundorder'][0]['orefund_type'];
                $val['orefund_nocause'] = $val['refundorder'][0]['orefund_nocause'];
                unset($val['refundorder']);
            }
        }
        return $result;
    }

    //取消订单
    public function cencelOrder($uid, $orderID){
        $user_order = self::where("id=$orderID and user_id=$uid")->find();
        if(!$user_order){
            $result = (new OrderException([
                'code' => 11009,
                'msg' => '当前用户和订单不匹配'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        if($user_order['order_status'] >= C('Unpaid')){
            $result = (new OrderException([
                'code' => 11010,
                'msg' => '订单已被支付'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->order_status = C('cencel');
        $this->user_id = $uid;
        $this->id = $orderID;
        $result = self::where("id=$orderID and user_id=$uid")->save();
        if(!$result){
            $result = (new OrderException([
                'code' => 11008,
                'msg' => '取消订单失败，或订单已被取消'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        return $result;
    }
}

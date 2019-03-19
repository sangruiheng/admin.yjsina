<?php

namespace Api\Model;

use Think\Model\RelationModel;

class DeliveryModel extends RelationModel
{
    protected $_link = array(
        'order' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'order',//要关联的表名
            'foreign_key' => 'order_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        ),
        'express' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'express',//要关联的表名
            'foreign_key' => 'express_id', //本表的字段名称
            'as_fields' => 'express_name:express_name,express_code,express_code',  //被关联表中的字段名：要变成的字段名
        ),
        'refundproduct' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'refundproduct',//要关联的表名
            'foreign_key' => 'delivery_id', //外键的字段名称
            'mapping_order' => 'id desc',  //被关联表中的字段名：要变成的字段名
            'mapping_limit'    =>    1,   //多表关联  关联第三个表的名称
//            'as_fields' => 'groupID,imgPath',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'refundproduct',   //多表关联  关联第三个表的名称
        ),
        'product' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'product',//要关联的表名
            'foreign_key' => 'product_id', //外键的字段名称
            'mapping_fields' => 'id,business_id',  //被关联表中的字段名：要变成的字段名
            'relation_deep'    =>    'business',   //多表关联  关联第三个表的名称
        )
    );


    public static function deliveryStatus($status){
        switch ($status){
            case 3:
                return "已发货";
                break;
            case 4:
                return "待评价";
                break;
            case 5:
                return "交易完成";
                break;
            default:
                return '未知状态';
        }
    }


    public static function refundStatus($status){
        switch ($status){
            case 1:
                return "退款中";
                break;
            case 2:
                return "退款成功";
                break;
            case 3:
                return "退款未同意";
                break;
            default:
                return '未知状态';
        }
    }





    //已发货商品列表
    public function deliveryProduct($user_id, $product_status)
    {
        $orderModel = D('order');
        $map['order_status'] = $product_status;
        $map['order_status'] = C('Paid');
        $map['_logic'] = 'OR';
        $order = $orderModel->relation('delivery1')->where("user_id=$user_id")->where($map)->order('id desc')->select();
        $product = [];
        foreach ($order as &$value) {
            $value['snap_items'] = json_decode($value['snap_items'], true);
        }
        foreach ($order as &$value) {
            foreach ($value['delivery1'] as &$val) {
                foreach ($value['snap_items'] as &$item) {
                    $item['express_name'] = $val['express_name'];
                    $item['express_code'] = $val['express_code'];
                    $item['contacts'] = $val['contacts'];   //送货人
                    $item['contacts_tel'] = $val['contacts_tel']; //送货人联系电话
                    $item['delivery_no'] = $val['delivery_no'];
                    $item['deliver_type'] = $val['deliver_type'];
                    $item['order_no'] = $value['order_no'];
                    $item['delivery_id'] = $val['id'];
                    $item['order_id'] = $val['order_id'];
                    $item['user_id'] = $val['user_id'];
                    $item['business_name'] = $val['product']['business']['business_name'];  //商家名称
                    $item['business_tel'] = $val['product']['business']['business_tel'];   //商家联系电话
                    $item['product_status'] = OrderModel::orderStatus($val['product_status']);
                    if ($val['product_id'] == $item['id'] && $val['product_status'] == $product_status) {
                            if($val['refundproduct']){
                                $item['prefund_type'] = $val['refundproduct'][0]['prefund_type'];
                                $item['prefund_nocause'] = $val['refundproduct'][0]['prefund_nocause'];
                                $item['prefund_id'] = $val['refundproduct'][0]['id'];
//                                $item['refundproduct'] = $val['refundproduct'];
                            }
                            $product[] = $item;
                    }
                }
            }
        }
        return $product;
    }



    //待评价列表
    public function evaluatedProduct($user_id, $product_status)
    {
        $orderModel = D('order');
        $map['order_status'] = $product_status;
        $map['order_status'] = C('shipped');
        $map['_logic'] = 'OR';
        $order = $orderModel->relation('delivery1')->where("user_id=$user_id")->where($map)->order('id desc')->select();
        $product = [];
        foreach ($order as &$value) {
            $value['snap_items'] = json_decode($value['snap_items'], true);
        }
        foreach ($order as &$value) {
            foreach ($value['delivery1'] as &$val) {
                foreach ($value['snap_items'] as &$item) {
                    $item['express_name'] = $val['express_name'];
                    $item['express_code'] = $val['express_code'];
                    $item['contacts'] = $val['contacts'];   //送货人
                    $item['contacts_tel'] = $val['contacts_tel']; //送货人联系电话
                    $item['delivery_no'] = $val['delivery_no'];
                    $item['order_no'] = $value['order_no'];
                    $item['delivery_id'] = $val['id'];
                    $item['order_id'] = $val['order_id'];
                    $item['user_id'] = $val['user_id'];
                    $item['product_status'] = '待评价';
                    $item['business_name'] = $val['product']['business']['business_name'];  //商家名称
                    $item['business_tel'] = $val['product']['business']['business_tel'];   //商家联系电话
                    if ($val['product_id'] == $item['id'] && $val['product_status'] == $product_status) {
                        if($val['refundproduct']){
                            $item['prefund_type'] = $val['refundproduct'][0]['prefund_type'];
                            $item['prefund_nocause'] = $val['refundproduct'][0]['prefund_nocause'];
                            $item['prefund_id'] = $val['refundproduct'][0]['id'];
                        }
                        $product[] = $item;
                    }
                }
            }
        }
        return $product;
    }


    public function addEvaProduct($uid, $product_id, $comment_content, $comment_star, $product_attr, $delivery_id)
    {
        $comment = M('comment');
        $deliveryModel = M('delivery');
        $comment->user_id = $uid;
        $comment->product_id = $product_id;
        $comment->comment_addTime = date('Y-m-d H:i:s', time());
        $comment->comment_content = $comment_content;
        $comment->comment_star = $comment_star;
        $comment->product_attr = $product_attr;
        $result = $comment->add();
        if ($result) {
            $deliveryModel->product_status = 5;
            $result = $deliveryModel->where("id=$delivery_id")->save();
        }
        return $result;
    }


    //对比订单商品与订单中发货的商品 如果订单所有商品都已确认收货 则修改订单表状态
    public function productContrast($order_id)
    {
        $order_product = M('orderproduct')->where("order_id=$order_id")->select();
        $delivery_product = M('delivery')->where("order_id=$order_id and product_status=4")->select();
        if (count($order_product) == count($delivery_product)) {
            $order = M('order');
            $order->order_status = 4;
            $result = $order->where("id=$order_id")->save();
            return $result;
        }
        return true;
    }

}
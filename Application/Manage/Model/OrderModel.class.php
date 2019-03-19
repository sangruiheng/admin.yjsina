<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class OrderModel extends RelationModel{
	protected $_link = array(
        'user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'user',//要关联的表名
            'foreign_key' => 'user_id', //本表的字段名称
            'as_fields' => 'nickName:nickName,tel:tel',  //被关联表中的字段名：要变成的字段名
        ),
	);


    //把订单设置为已读
    public function setUnreadMessage(){
//        $orderModel = M('order');
        $orderMessageModel = M('ordermessage');
        $orderMessage = $orderMessageModel->select();
        if(empty($orderMessage)){
            $map['order_status'] = C('Paid');
            $map['Deposit_type'] = 1;
            $map['_logic'] = 'OR';
            $order = self::where($map)->field('id')->select();
            foreach ($order as &$item){
                $item['order_id'] = $item['id'];
                unset($item['id']);
            }
            $orderMessageModel->addAll($order);
        }else{
            foreach ($orderMessage as $value){
                $order_ids[] = $value['order_id'];
            }
            $map['id'] = array('not in', $order_ids);
            $where['order_status'] = C('Paid');
            $where['Deposit_type'] = 1;
            $where['_logic'] = 'OR';
            $map['_complex'] = $where;
            $order = self::where($map)->field('id')->select();
            foreach ($order as &$item){
                $item['order_id'] = $item['id'];
                unset($item['id']);
            }
            $orderMessageModel->addAll($order);
        }
        return true;

    }
    

	public function getOrder($map, $p){
        $Order = self::relation('user')->where($map)->field("snap_img,snap_name,snap_attr", true)->order('id desc')->page($p.',10')->select();

        foreach ($Order as &$val) {
            $val['snap_address'] = json_decode($val['snap_address'], true);
            $val['address'] = $val['snap_address']['address_city'] . $val['snap_address']['address_detail'];
            $val['order_status'] = OrderModel::orderStatus($val['order_status']);
            $val['snap_type'] = count(json_decode($val['snap_items']));
            unset($val['snap_items']);
        }
        return $Order;
    }

    //对比订单商品与订单中发货的商品 如果订单所有商品都发货 修改订单表状态
    public function productContrast($order_id){
        $order_product = M('orderproduct')->where("order_id=$order_id")->select();
        $delivery_product = M('delivery')->where("order_id=$order_id")->select();
        if(count($order_product) == count($delivery_product)){
            $order = M('order');
            $order->order_status = 3;
            $result = $order->where("id=$order_id")->save();
            return $result;
        }
        return true;
    }


    public static function orderStatus($status){
        switch ($status){
            case 1:
                return "未支付";
                break;
            case 2:
                return "已支付";
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
            default:
                return '全部';
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

    public static function orderProductType($status){
        switch ($status){
            case 1:
                return "订金订单";
                break;
            case 2:
                return "全款订单";
                break;
            case 3:
                return "限时购订单";
                break;
            default:
                return '未知状态';
        }
    }




    //获取商家
    public function getBusiness($id){
        $businessModel = M('business');
        $business = $businessModel->where("id=$id")->find();
        return $business;
    }

    //获取分类
    public function getCategory($id){
        $navCategoryModel = M('navcategory');
        $navCategory = $navCategoryModel->where("id=$id")->find();
        return $navCategory;
    }




}
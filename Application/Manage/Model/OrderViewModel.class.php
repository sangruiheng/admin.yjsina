<?php
namespace Manage\Model;
use Think\Model\ViewModel;

class OrderViewModel extends ViewModel {


    public $viewFields = array(
        'order'=>array('_as'=>'myorder','id','user_id','order_price','order_status','order_producttype','order_no','snap_address','snap_items','order_count','order_addTime','order_tailmoney','Deposit_type'),
//        'refundproduct'=>array('id','prefund_cause','prefund_time','prefund_type','prefund_nocause','delivery_id','prefund_freight'),
//        'delivery'=>array('order_id','product_id','user_id','product_status', '_on'=>'refundproduct.delivery_id=delivery.id'),
        'user'=>array('nickName','tel', '_on'=>'myorder.user_id=user.id'),
    );
    

}
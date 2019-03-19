<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:57
 */

namespace Manage\Model;



use Think\Model\ViewModel;

class RefundproductViewModel extends ViewModel
{

    public $viewFields = array(
        'refundproduct'=>array('id','prefund_cause','prefund_time','prefund_type','prefund_nocause','delivery_id','prefund_freight'),
        'delivery'=>array('order_id','product_id','user_id','product_status', '_on'=>'refundproduct.delivery_id=delivery.id'),
        'order'=>array('_as'=>'myorder','user_id','order_price','order_status','order_producttype','order_no','snap_address','snap_items','order_count','order_tailmoney','Deposit_type', '_on'=>'delivery.order_id=myorder.id'),
        'user'=>array('nickName','tel', '_on'=>'delivery.user_id=user.id'),
    );

}
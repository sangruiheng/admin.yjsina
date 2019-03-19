<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:57
 */

namespace Manage\Model;


use Think\Model\ViewModel;

class RefundorderViewModel extends ViewModel
{


   /* public $_link = array(
        'order' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'order',//要关联的表名
            'foreign_key' => 'order_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
            'condition' => '',   //条件查询
        )
    );*/

    public $viewFields = array(
        'refundorder'=>array('id','orefund_cause','orefund_time','orefund_type','orefund_nocause','order_id','orefund_freight'),
        'order'=>array('_as'=>'myorder','user_id','order_price','order_status','order_producttype','order_no','snap_address','snap_items','order_count','order_tailmoney','Deposit_type', '_on'=>'refundorder.order_id=myorder.id'),
        'user'=>array('nickName','tel', '_on'=>'myorder.user_id=user.id'),
    );








}
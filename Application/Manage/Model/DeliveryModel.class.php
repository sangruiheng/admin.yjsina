<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class DeliveryModel extends RelationModel{
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
            'as_fields' => 'express_name:express_name',  //被关联表中的字段名：要变成的字段名
        ),
        'user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'user',//要关联的表名
            'foreign_key' => 'user_id', //本表的字段名称
            'as_fields' => 'nickName:nickName,tel:tel',  //被关联表中的字段名：要变成的字段名
        ),
	);

    protected $_validate = array(
        array('express_id','require','快递公司不能为空'),
        array('delivery_no','require','快递单号不能为空'),
    );

}
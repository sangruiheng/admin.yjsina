<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class ProducttypeModel extends RelationModel{
	protected $_link = array(
        'productAttr' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'productattr',//要关联的表名
            'foreign_key' => 'producttype_id', //本表的字段名称
//            'as_fields' => 'img:img',  //被关联表中的字段名：要变成的字段名
            'relation_deep'    =>    'productValue',   //多表关联  关联第三个模型的名称
        )
	);

    protected $_validate = array(
        array('producttype_name','require','商品属性不能为空')
    );

}
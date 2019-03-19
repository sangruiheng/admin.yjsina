<?php
namespace Api\Model;
use Think\Model\RelationModel;
class Productattr1Model extends RelationModel{
	protected $_link = array(
        'producttype1' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'producttype1',//要关联的表名
            'foreign_key' => 'attrtype_id', //本表的字段名称
//            'as_fields' => 'id:productattr_id,attrtype_id:attrtype_id,attr_name:attr_name',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'producttype1',   //多表关联  关联第三个表的名称
        ),
	);



}
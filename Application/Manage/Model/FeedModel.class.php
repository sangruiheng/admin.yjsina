<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class FeedModel extends RelationModel{
	protected $_link = array(
        'user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'user',//要关联的表名
            'foreign_key' => 'userID', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        ),
	);



}
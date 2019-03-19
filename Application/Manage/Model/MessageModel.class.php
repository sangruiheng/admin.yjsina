<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class MessageModel extends RelationModel{
    protected $_link = array(
        'news' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'news',//要关联的表名
            'foreign_key' => 'news_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        ),
        'user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'user',//要关联的表名
            'foreign_key' => 'user_id', //本表的字段名称
//            'as_fields' => 'img:img',  //被关联表中的字段名：要变成的字段名
            //       'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
        )
    );


}
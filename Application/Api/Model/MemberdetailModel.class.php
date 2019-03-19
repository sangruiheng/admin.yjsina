<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Api\Exception\CateException;
use Api\Service\UserToken;
use Think\Model\RelationModel;

class MemberdetailModel extends RelationModel
{

    protected $_link = array(
        'membercard' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'membercard',//要关联的表名
            'foreign_key' => 'mambercard_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
        ),
    );


}

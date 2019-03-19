<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:57
 */

namespace Manage\Model;


use Think\Model\RelationModel;

class RefundproductModel extends RelationModel
{


    protected $_link = array(
        'delivery' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'delivery',//要关联的表名
            'foreign_key' => 'delivery_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
            'relation_deep' => 'user',   //多表关联  关联第三个模型的名称

        ),
    );

    //form表单自动验证
    protected $_validate = array(
        array('refund_nocause', 'require', '原因不能为空'),
    );


}
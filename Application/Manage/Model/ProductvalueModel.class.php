<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class ProductvalueModel extends RelationModel{
	protected $_link = array(
//        'productValues' => array(
//
//        )
	);

    protected $_validate = array(
        array('productvalue_name','require','商品属性值不能为空')
    );


}
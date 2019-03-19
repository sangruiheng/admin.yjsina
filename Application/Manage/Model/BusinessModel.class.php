<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class BusinessModel extends RelationModel{
	protected $_link = array(

	);
    protected $_validate = array(
        array('business_name','require','商家名称不能为空'),
        array('business_tel','require','商家电话不能为空'),
        array('business_address','require','商家地址不能为空'),
    );



}
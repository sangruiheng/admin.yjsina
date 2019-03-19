<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class NavcategoryModel extends RelationModel{
	protected $_link = array(

	);
    protected $_validate = array(
        array('navcate_name','require','分类不能为空')
    );

}
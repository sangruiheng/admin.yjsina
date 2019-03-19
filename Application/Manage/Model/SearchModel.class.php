<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class SearchModel extends RelationModel{
	protected $_link = array(

	);
    protected $_validate = array(
        array('search_name','require','关键词不能为空')
    );

}
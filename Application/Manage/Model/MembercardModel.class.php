<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class MemberCardModel extends RelationModel{
	protected $_link = array(

	);
    protected $_validate = array(
        array('membercard_name','require','会员卡名称'),
        array('membercard_discount','require','会员卡折扣'),
    );



}
<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class BannerModel extends RelationModel{
	protected $_link = array(

	);
    protected $_validate = array(
        array('banner_title','require','标题不能为空'),
        array('banner_img','require','轮播图不能为空'),
    );



}
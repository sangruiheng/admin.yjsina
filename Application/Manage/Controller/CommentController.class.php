<?php

namespace Manage\Controller;

use Think\Controller;

class CommentController extends CommonController
{

    public function commentList()
    {

        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('comment', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if(empty($p)){
            $p = 1;
        }
        $Product = D('comment')->relation(true)->where($map)->order('id desc')->page($p.',10')->select();
//        print_r($Product);
        $count = D('comment')->relation(true)->where($map)->count();
        $Page = getpage($count, 10);
        foreach($map as $key=>$val) {
            $page->parameter .= "$key=".urlencode($val).'&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $Product);
        $this->display();

    }
}

?>
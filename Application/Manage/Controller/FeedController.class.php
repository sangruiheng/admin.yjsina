<?php
namespace Manage\Controller;
use Think\Controller;
class FeedController extends CommonController {

    public function feedList(){

        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('feed', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if(empty($p)){
            $p = 1;
        }
        $Feed = D('feed')->relation(true)->where($map)->order('id desc')->page($p.',10')->select();
        foreach ($Feed as &$value){
            $value['user']['nickName'] = urldecode($value['user']['nickName']);
        }
//        print_r($Feed);
        $count = D('feed')->relation(true)->where($map)->count();
        $Page = getpage($count, 10);
        foreach($map as $key=>$val) {
            $page->parameter .= "$key=".urlencode($val).'&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $Feed);
        $this->display();
    }

    public function addFeed(){

        $user = M("user")->select(); // 实例化User对象
        $this->assign('user',$user);
        $this->display();
    }
}
?>
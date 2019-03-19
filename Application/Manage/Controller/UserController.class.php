<?php
namespace Manage\Controller;
use Think\Controller;

class UserController extends CommonController {

    public function userList(){


        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('user', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if(empty($p)){
            $p = 1;
        }
        $user = D('user')->where($map)->order('id desc')->page($p.',10')->select();
        foreach ($user as &$value){
            $value['nickName'] = urldecode($value['nickName']);
            if(!$value['openid']){
                $value['avatarUrl'] ='http://admin.yjsina.com/'.$value['avatarUrl'];
            }
        }
//        print_r($user);
        $count = D('user')->where($map)->count();
        $Page = getpage($count, 10);
        foreach($map as $key=>$val) {
            $page->parameter .= "$key=".urlencode($val).'&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $user);
        $this->display();


    }
    

}
?>
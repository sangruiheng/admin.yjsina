<?php

namespace Manage\Controller;

use Think\Controller;

class HelpController extends CommonController
{

    public function helpList()
    {
        $this->getDlist('help', $_GET['keyWord']);
    }

    public function addHelpData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                $sql->help_content = htmlspecialchars_decode($_POST['help_content']);
                $result = $sql->add();

            } else {  //修改
                $sql->help_content = htmlspecialchars_decode($_POST['help_content']);
                $result = $sql->save();
            }
        }
        if ($result) {
            $this->success('编辑成功！', U($controller . '/' . $backUrl));
        } else {
            $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
        }
    }


    //关于我们
    public function aboutList()
    {
        $aboutList = M('help')->find(1);
//        print_r($aboutList);
        $this->assign('aboutList', $aboutList);
        $this->display();
    }

    //编辑关于我们
    public function addAboutData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        if ($sql->create()) {
            if (empty($id)) {  //添加
                $sql->id = NULL;
                $sql->help_content = htmlspecialchars_decode($_POST['help_content']);
                $result = $sql->add();
            } else {     //修改
                $sql->help_content = htmlspecialchars_decode($_POST['help_content']);
                $result = $sql->save();
            }
            if ($result) {
                $this->success('编辑成功！', U($controller . '/' . $backUrl));
            }
        } else {
            $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
        }
    }

    //帮助中心
    public function helpCenter()
    {
        $helpCenter = M('help')->find(2);
        $this->assign('helpCenter', $helpCenter);
        $this->display();
    }


    //编辑帮助中心
    public function addHelpCenter()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        if ($sql->create()) {
            if (empty($id)) {  //添加
                $sql->id = NULL;
                $sql->help_content = htmlspecialchars_decode($_POST['help_content']);
                $result = $sql->add();
            } else {     //修改
                $sql->help_content = htmlspecialchars_decode($_POST['help_content']);
                $result = $sql->save();
            }
            if ($result) {
                $this->success('编辑成功！', U($controller . '/' . $backUrl));
            }
        } else {
            $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
        }
    }

    //首页滚动字
    public function loopWord(){
        $loopWord = M('help')->find(3);
        $this->assign('loopWord', $loopWord);
        $this->display();
    }

    //编辑首页滚动字
    public function addLoopWord(){
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        if ($sql->create()) {
            if (empty($id)) {  //添加
                $sql->id = NULL;
                $sql->help_content = $_POST['help_content'];
                $result = $sql->add();
            } else {     //修改
                $sql->help_content = $_POST['help_content'];
                $result = $sql->save();
            }
            if ($result) {
                $this->success('编辑成功！', U($controller . '/' . $backUrl));
            }
        } else {
            $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
        }
    }

}

?>
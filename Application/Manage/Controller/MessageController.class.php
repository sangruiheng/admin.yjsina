<?php

namespace Manage\Controller;

use Manage\Model\MessageModel;
use Think\Controller;

class MessageController extends CommonController
{

    public function messageList()
    {

        $this->getDlist('message', $_GET['keyWord']);
    }

    public function addMessage(){
        $newsList = D('news')->select();
        $this->assign('newsList', $newsList);
        $userList = D('user')->select();
        $this->assign('userList', $userList);
        return $this->display();
    }

    public function addMessageData()
    {
        $Message = new MessageModel();
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                $sql->content =  htmlspecialchars_decode($_POST['content']);
                $sql->addTime = date('Y-m-d H:i:s', time());
                $result = $sql->add();
                $maxID = D('news')->max('id');
                $request = I('post.');
                if ($request['hid']) {
                    $Message->getAddImg($request, $maxID);
                }

            } else {  //修改
                $request = I('post.');
                if ($request['hid']) {  //判断是否上传图片
                    $sql->content =  htmlspecialchars_decode($_POST['content']);
                    $sql->addTime = date('Y-m-d H:i:s', time());
                    $result = $sql->save();
                    $Message->getAddImg($request, $id);

                } else {
                    $sql->content =  htmlspecialchars_decode($_POST['content']);
                    $sql->addTime = date('Y-m-d H:i:s', time());
                    $result = $sql->save();
                    $this->success('编辑成功！', U($controller . '/' . $backUrl));
                }
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
<?php

namespace Manage\Controller;

use Manage\Model\BannerModel;
use Think\Controller;

class BannerController extends CommonController
{

    public function bannerList()
    {
//        echo $_SERVER['SERVER_NAME']. '/Uploads/Manage/';

        $this->getDlist('banner', $_GET['keyWord'], "banner_type=1");
//        $this->display();
    }


    //新闻添加、编辑数据的方法
    public function addBannerData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                switch ($table) {
                    case $table == 'banner':
                        $sql->banner_addTime = date('Y-m-d H:i:s', time());
                        $sql->banner_img = substr($request['hid'][0], 16);
                        $sql->banner_type = 1;   //首页轮播
                        break;
                    case $table == 'news':
                        $sql->content = htmlspecialchars_decode($_POST['content']);
                        $sql->image = substr($request['hid'][0], 16);
                        $sql->addTime = date('Y-m-d H:i:s', time());
                        break;
                    default:
                        true;
                }
                $result = $sql->add();
            } else {  //修改
                if ($request['hid']) {  //判断是否上传图片
                    switch ($table) {
                        case $table == 'banner':
                            $banner = D('banner')->where("id=$id")->find();
                            $file = ('Uploads/Manage/' . $banner['banner_img']);
                            if (file_exists($file)) {
                                @unlink($file);
                            }
                            $sql->banner_addTime = date('Y-m-d H:i:s', time());
                            $sql->banner_img = substr($request['hid'][0], 16);
                            $sql->banner_title = $request['banner_title'];
                            $sql->banner_url = $request['banner_url'];
                            $sql->banner_type = 1;   //首页轮播
                            break;
                        case $table == 'news':
                            $news = D('news')->where("id=$id")->find();
                            $file = ('Uploads/Manage/' . $news['image']);
                            if (file_exists($file)) {
                                @unlink($file);
                            }
                            $sql->image = substr($request['hid'][0], 16);
                            $sql->content = htmlspecialchars_decode($_POST['content']);
                            $sql->addTime = date('Y-m-d H:i:s', time());
                            break;
                        default:
                            true;
                    }
                    $result = $sql->save();

                } else {
                    switch ($table) {
                        case $table == 'banner':
                            $sql->banner_addTime = date('Y-m-d H:i:s', time());
                            $sql->banner_type = 1;   //首页轮播
                            break;
                        case $table == 'news':
                            $sql->content = htmlspecialchars_decode($_POST['content']);
                            $sql->addTime = date('Y-m-d H:i:s', time());
                            break;

                    }
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

    //广告图轮播
    public function commercialList()
    {
        $this->getDlist('banner', $_GET['keyWord'], "banner_type=2");
    }

    public function addCommercialData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                $sql->banner_addTime = date('Y-m-d H:i:s', time());
                $sql->banner_img = substr($request['hid'][0], 16);
                $sql->banner_type = 2;   //广告轮播

                $result = $sql->add();
            } else {  //修改
                if ($request['hid']) {  //判断是否上传图片
                    $banner = D('banner')->where("id=$id")->find();
                    $file = ('Uploads/Manage/' . $banner['banner_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_img = substr($request['hid'][0], 16);
                    $sql->banner_title = $request['banner_title'];
                    $sql->banner_url = $request['banner_url'];
                    $sql->banner_type = 2;   //广告轮播

                    $result = $sql->save();

                } else {
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_type = 2;   //广告轮播
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


    //删除轮播及图片
    public function deleteBanner()
    {
        $table = $_POST['table'];
        $ids = $_POST['delID'];
        $sql = M($table);
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        //删除图片
        $map['id'] = array('in', $ids);
        $GroupImg_list = M('banner')->where($map)->select();
        foreach ($GroupImg_list as $value) {
//            $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $value["banner_img"]);
            $file = ('Uploads/Manage/' . $value["banner_img"]);
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        return $Result = $sql->delete($ids);
    }


    //广告图轮播
    public function loginBannerList()
    {
        $this->getDlist('banner', $_GET['keyWord'], "banner_type=3");
    }

    public function addLoginBannerData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                $sql->banner_addTime = date('Y-m-d H:i:s', time());
                $sql->banner_img = substr($request['hid'][0], 16);
                $sql->banner_type = 3;   //广告轮播

                $result = $sql->add();
            } else {  //修改
                if ($request['hid']) {  //判断是否上传图片
                    $banner = D('banner')->where("id=$id")->find();
                    $file = ('Uploads/Manage/' . $banner['banner_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_img = substr($request['hid'][0], 16);
                    $sql->banner_title = $request['banner_title'];
                    $sql->banner_url = $request['banner_url'];
                    $sql->banner_type = 3;   //广告轮播

                    $result = $sql->save();

                } else {
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_type = 3;   //广告轮播
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


    //家电广告位
    public function advertisingList()
    {
        $this->getDlist('banner', $_GET['keyWord'], "banner_type=4");
    }

    public function addAdvertisingData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                $sql->banner_addTime = date('Y-m-d H:i:s', time());
                $sql->banner_img = substr($request['hid'][0], 16);
                $sql->banner_type = 4;   //广告轮播

                $result = $sql->add();
            } else {  //修改
                if ($request['hid']) {  //判断是否上传图片
                    $banner = D('banner')->where("id=$id")->find();
                    $file = ('Uploads/Manage/' . $banner['banner_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_img = substr($request['hid'][0], 16);
                    $sql->banner_title = $request['banner_title'];
                    $sql->banner_url = $request['banner_url'];
                    $sql->banner_type = 4;   //广告轮播

                    $result = $sql->save();

                } else {
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_type = 4;   //广告轮播
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

    //首页服务区
    public function serviceImg()
    {
        $this->getDlist('banner', $_GET['keyWord'], "banner_type=5");
    }

    public function addServiceImgData()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) { //添加
                $sql->id = NULL;
                $sql->banner_addTime = date('Y-m-d H:i:s', time());
                $sql->banner_img = substr($request['hid'][0], 16);
                $sql->banner_type = 5;   //服务图

                $result = $sql->add();
            } else {  //修改
                if ($request['hid']) {  //判断是否上传图片
                    $banner = D('banner')->where("id=$id")->find();
                    $file = ('Uploads/Manage/' . $banner['banner_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_img = substr($request['hid'][0], 16);
                    $sql->banner_title = $request['banner_title'];
                    $sql->banner_url = $request['banner_url'];
                    $sql->banner_type = 5;

                    $result = $sql->save();

                } else {
                    $sql->banner_addTime = date('Y-m-d H:i:s', time());
                    $sql->banner_type = 4;   //广告轮播
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
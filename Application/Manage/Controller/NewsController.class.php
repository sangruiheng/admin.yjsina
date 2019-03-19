<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:03
 */

namespace Manage\Controller;


use Manage\Model\NewsModel;

class NewsController extends CommonController
{
    public function newsList()
    {
        $this->getDlist('news', $_GET['keyWord']);
    }

    public function addNews()
    {
        $newstypeList = D('newstype')->select();
        $this->assign('newstypeList', $newstypeList);
        $this->display();
    }

    public function newsTypeList()
    {
        $this->getDlist('newstype', $_GET['keyWord']);
    }

    //新闻添加、编辑数据的方法
    public function addNewsData()
    {
        $News = new NewsModel();
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
                    $News->getAddImg($request, $maxID);
                }

            } else {  //修改
                $request = I('post.');
                if ($request['hid']) {  //判断是否上传图片
                    $sql->content =  htmlspecialchars_decode($_POST['content']);
                    $sql->addTime = date('Y-m-d H:i:s', time());
                    $result = $sql->save();
                    $News->getAddImg($request, $id);

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




    //删除修改时的图片
    public function delImg()
    {
        $result  = NewsModel::delEditImg($_POST['id']);
        return $result;

    }


    //删除新闻及图片
    public function deleteNewsImg()
    {
        NewsModel::deleteNewsImg($_POST['table'], $_POST['delID']);
    }


}
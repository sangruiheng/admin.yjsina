<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:57
 */

namespace Manage\Model;


use Think\Model\RelationModel;

class NewsModel extends RelationModel
{


    protected $_link = array(
        'newsType' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'newstype',//要关联的表名
            'foreign_key' => 'newstypeID', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        ),
        'newsImg' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'newsimg',//要关联的表名
            'foreign_key' => 'newsID', //本表的字段名称
//            'as_fields' => 'img:img',  //被关联表中的字段名：要变成的字段名
            //       'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
        )
    );

    //form表单自动验证
    protected $_validate = array(
        array('newstypeID', 'require', '请输入新闻类型'),
        array('title', 'require', '请输入项目标题'),
        array('desc', 'require', '请输入新闻描述'),
        array('author', 'require', '请输入新闻作者！'),
        array('content', 'require', '请输入新闻内容'),
    );



    //获取图片循环入库=
    public function getAddImg($request, $maxID)
    {
        $model = D('newsimg');
        for ($i = 0; $i < count($request['hid']); $i++) {
            $model->newsID = $maxID;
            $model->imgPath = substr($request['hid'][$i], 16);
            $result = $model->add();
        }
        return $result;
    }

    //删除编辑新闻时的图片
    public static function delEditImg($id)
    {
        $NewsImg = M('newsimg')->where("id=$id")->find();
        $file = ('Uploads/Manage/' . $NewsImg['imgPath']);
        if (file_exists($file)) {
            @unlink($file);
        }
        $result = M('newsimg')->where("id=$id")->delete();
        return $result;

    }

    //删除新闻及图片
    public static function deleteNewsImg($table, $ids)
    {
        $sql = M($table);
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }

        $news = M('news')->where("id=$ids")->find();
            $file = ('Uploads/Manage/' . $news["image"]);
            if (file_exists($file)) {
                @unlink($file);
        }
        $Result = $sql->delete($ids);
//        return $res = M('newsimg')->where("newsID=$ids")->delete();
    }


}
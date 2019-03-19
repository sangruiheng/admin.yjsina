<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\CategoryException;
use Api\Exception\UserException;
use Api\Model\GroupsModel;
use Api\Validate\IDMustBePostiveInt;
use Think\Controller;

class CategoryController extends CommonController
{

    public function index()
    {

    }

    //获取全部分类
    public function getAllCategory()
    {
        $result = D('navcategory')->select();
        foreach($result as &$val){
            if($val['navcate_img'] != ''){
                $val['navcate_img'] = C('img_prefix') . $val['navcate_img'];
            }
        }
        if (!$result) {
            $result = (new CategoryException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取二级分类
    public function getCategory()
    {
         (new IDMustBePostiveInt())->goCheck();
        $result = D('navcategory')->relation(true)->where("navcate_pid=".$_POST['id'])->select();
        foreach($result as &$val){
                $val['navcate_img'] = C('img_prefix') . $val['navcate_img'];
        }

        if (!$result) {
            $result = (new CategoryException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取推荐一级分类
    public function topCategory(){
        $result = D('navcategory')->where("navcate_pid=0 and status=1")->select();
        if (!$result) {
            ;
            $this->ajaxReturn((new CategoryException([
                'code' => 30001,
                'msg' => '获取一级分类失败'
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //分类
    public function levelCategory(){
        $result = M('navcategory')->where("navcate_pid=0")->select();
        $result[0]['category'] = D('navcategory')->where("navcate_pid=".$result[0]['id'])->select();
        foreach ($result[0]['category'] as &$val){
            $val['navcate_img'] = C('img_prefix') . $val['navcate_img'];
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取全部一级分类
    public function topAllCategory(){
        $result = D('navcategory')->where("navcate_pid=0")->select();
        if (!$result) {
            ;
            $this->ajaxReturn((new CategoryException([
                'code' => 30001,
                'msg' => '获取一级分类失败'
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


}
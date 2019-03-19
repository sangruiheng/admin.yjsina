<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\CateException;
use Api\Exception\ParameterException;
use Api\Exception\ProductException;
use Api\Exception\SuccessException;
use Api\Exception\UserException;
use Api\Model\CateModel;
use Api\Service\Token;
use Api\Validate\Cate;
use Api\Validate\CateCount;
use Api\Validate\IDMustBePostiveInt;
use Api\Model\OrderModel;
use Api\Validate\ManyIDs;
use Api\Validate\OrderPlace;
use Think\Controller;

class CateController extends CommonController
{

    protected $uid;

    function __construct()
    {
        //根据token来获取uid
        $this->uid = Token::getCurrentUid();
    }

    //判断是否有token对应的用户
    public function is_user()
    {
        $user = D('user')->where("id=$this->uid")->find();
        return $user;
    }

    public function addCate()
    {
        //判断是否携带令牌  是否登陆
        //判断uid是否存在数据库中
        //判断参数
        //添加购物车首先要查询购物车是否有相同商品【他包括商品id，型号id，用户id】
        //进行判断，如果有就进行数据的修改，如果木有就将商品添加到购物车

        $user = $this->is_user();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        (new Cate())->goCheck();

        $params = I('param.');
//        $productvalue = $_POST['productvalue'];
//        $params['productvalue'] = json_decode($productvalue,true);
        $cate = new CateModel();
        //商品和属性是否匹配
        $cate->marryAttr($params);
        //库存
        $res = $cate->cateStock($params);
        if($params['cate_count'] > $res['stock']){
            $result = (new CateException([
                'code' => 15005,
                'msg' => '当前商品库存不足'
            ]))->getException();
            $this->ajaxReturn($result);
        }

        $userCate = $cate->is_repeat($this->uid, $params);
        if ($userCate) {    //有重复商品
            //库存
            if($userCate['cate_count']+$params['cate_count'] > $res['stock']){
                $result = (new CateException([
                    'code' => 15005,
                    'msg' => '当前商品库存不足'
                ]))->getException();
                $this->ajaxReturn($result);
            }
            $cate->cate_count = $userCate['cate_count'] + $params['cate_count'];
            $result = $cate->where('id=' . $userCate['id'])->save();
        } else {
            if ($cate->create()) {
                $cate->user_id = $this->uid;
                $cate->producttype_id = $params['producttype_id'];
                $cate->productattr_id = $params['productattr_id'];
                $cate->cate_addTime = date("Y-m-d H:i:s", time());
                $result = $cate->add(); // 写入数据到数据库
            }
        }
        $this->ajaxReturn((new SuccessException([
            'msg' => '加入购物车成功！'
        ]))->getException());

    }

    //获取用户购物车列表
    public function getUserCate()
    {
        $user = $this->is_user();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        $userCate = D('cate')->relation(true)->field('id,cate_count,product_id,producttype_id,productattr_id')->where("user_id=" . $this->uid)->select();
        foreach ($userCate as &$value){
            $value['attr_img'] = C('img_prefix') . $value['attr_img'];
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $userCate
        ]);
    }


    //增加购物车数量
    public function upCate(){
        $user = $this->is_user();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        (new IDMustBePostiveInt())->goCheck();
        //判断库存
        (new CateModel())->changeCateStock($_POST['id']);
        //增加数量
        $count = D('cate')->where("id=" . $_POST['id'])->setInc('cate_count');
        if(!$count){
            $this->ajaxReturn((new CateException([
                'code' => 15006,
                'msg' => '增加购物车数量失败'
            ]))->getException());
        }
        $this->ajaxReturn((new SuccessException([
            'msg' => '购物车数量增加成功'
        ]))->getException());
    }

    //减少购物车数量
    public function dnCate(){
        $user = $this->is_user();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        (new IDMustBePostiveInt())->goCheck();
        $cate = M('cate')->where("id=" . $_POST['id'])->find();
        if($cate['cate_count']>=2){
            $count = D('cate')->where("id=" . $_POST['id'])->setDec('cate_count');
            if(!$count){
                $this->ajaxReturn((new CateException([
                    'code' => 15007,
                    'msg' => '减少购物车数量失败'
                ]))->getException());
            }
            $this->ajaxReturn((new SuccessException([
                'msg' => '购物车数量减少成功'
            ]))->getException());
        }else{
            $this->ajaxReturn((new CateException([
                'code' => 15008,
                'msg' => '购物车数量最少为1'
            ]))->getException());
        }


    }

    //输入数量
    public function countCate(){
        $user = $this->is_user();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        (new CateCount)->goCheck();
        $res = M('cate')->where("id=" . $_POST['id'])->find();
        if($res['cate_count'] == $_POST['count']){
            $this->ajaxReturn((new CateException([
                'code' => 15009,
                'msg' => '购物车数量相同'
            ]))->getException());
        }
        $cate = M('cate');
        $cateModel = new CateModel();
        $res_cate = $cateModel->cateType($_POST['id']);
        $ocate_res = $cateModel->cateStock($res_cate);
        //库存
        if($_POST['count'] > $ocate_res['stock']){
            $result = (new CateException([
                'code' => 15005,
                'msg' => '当前商品库存不足'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $cate->cate_count = $_POST['count'];
        $count = $cate->where("id=" . $_POST['id'])->save();
        if(!$count){
            $this->ajaxReturn((new CateException([
                'code' => 15010,
                'msg' => '改变购物车数量失败'
            ]))->getException());
        }
        $this->ajaxReturn((new SuccessException([
            'msg' => '购物车数量改变成功'
        ]))->getException());

    }







    /*{
    "ids":
    [
    {
    "id":1
    },
    {
        "id":6
        },
    {
        "id":8
        }
    ]
    }*/
    //删除购物车商品
    public function delUserCate()
    {
        $user = $this->is_user();
        if (!$user) {
            $result = (new UserException())->getException();
            $this->ajaxReturn($result);
        }
        $ids = $_POST['ids'];
        if(empty($ids)){
            $this->ajaxReturn((new CateException([
                'code' => 15001,
                'msg' => '购物车为空'
            ]))->getException());
        }
        if(!is_array($ids)){
            $this->ajaxReturn((new CateException([
                'code' => 15002,
                'msg' => '购物车必须为数组'
            ]))->getException());
        }
        foreach ($ids as $id){
            if(empty($id['id'])){
                $this->ajaxReturn((new CateException([
                    'code' => 15003,
                    'msg' => '购物车id不能为空'
                ]))->getException());
            }

            if(OrderModel::isPositiveInteger($id['id']) == false){
                $this->ajaxReturn((new CateException([
                    'code' => 15004,
                    'msg' => '购物车id必须为正整数'
                ]))->getException());
            }
        }

//        $ids = json_decode($ids, true);
        $arr_ids = [];
        foreach ($ids as $value){
            $arr_ids[] = $value['id'];
        }
        $cate = new CateModel();
        $result = $cate->delCate($arr_ids, $this->uid);
        if($result){
            $this->ajaxReturn((new SuccessException([
                'msg' => '删除购物车商品成功！'
            ]))->getException());
        }
        $this->ajaxReturn($ids);
    }


}
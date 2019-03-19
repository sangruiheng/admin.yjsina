<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Api\Exception\CateException;
use Api\Service\UserToken;
use Think\Model\RelationModel;

class CateModel extends RelationModel
{
    protected $_link = array(
        'product' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'product',//要关联的表名
            'foreign_key' => 'product_id', //本表的字段名称
            'mapping_fields' => 'product_name',  //被关联表中的字段名：要变成的字段名
            'as_fields' => 'product_name:product_name',  //被关联表中的字段名：要变成的字段名  可以多个
//            'relation_deep'    =>    'productimage',   //多表关联  关联第三个表的名称
        ),
        'productattr1' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'productattr1',//要关联的表名
            'foreign_key' => 'productattr_id', //本表的字段名称
            'as_fields' => 'id:productattr_id,attrtype_id:attrtype_id,attr_name:attr_name,price:product_price,stock:product_stock',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'producttype1',   //多表关联  关联第三个表的名称
        ),
        'producttype1' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'producttype1',//要关联的表名
            'foreign_key' => 'producttype_id', //本表的字段名称
            'as_fields' => 'color_name:color_name,attr_img:attr_img',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'producttype1',   //多表关联  关联第三个表的名称
        ),


    );

    //判断是否有重复商品
    public function is_repeat($uid, $param)
    {
//        $productvalue = $this->imProductValue($param);
        $map['user_id'] = $uid;
        $map['product_id'] = $param['product_id'];
        $map['producttype_id'] = $param['producttype_id'];
        $map['productattr_id'] = $param['productattr_id'];
        $userCate = self::where($map)->find();
        return $userCate;
    }

    //属性与商品是否匹配
    public function marryAttr($param){
        $product = M('product');
        $map['t.id'] = $param['producttype_id'];
        $map['a.id'] = $param['productattr_id'];
        $result = $product
            ->alias('p')
            ->join('icpnt_producttype1 as t ON p.id = t.product_id')
            ->join('icpnt_productattr1 as a ON t.id = a.attrtype_id')
            ->where($map)
//                ->field('icpnt_product.product_price',true)
            ->find();
        if(!$result){
            $result = (new CateException([
                'code' => 15011,
                'msg' => '当前商品与属性不匹配'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
    }

    //加入购物车时的库存
    public function cateStock($param){
        $product = M('product');
        $map['t.id'] = $param['producttype_id'];
        $map['a.id'] = $param['productattr_id'];
        $map['p.id'] = $param['product_id'];
        $result = $product
            ->alias('p')
            ->join('icpnt_producttype1 as t ON p.id = t.product_id')
            ->join('icpnt_productattr1 as a ON t.id = a.attrtype_id')
            ->where($map)
//                ->field('icpnt_product.product_price',true)
            ->find();
        return $result;
    }


    //增加购物车时判断库存
    public function changeCateStock($cate_id){
        $oCate = $this->cateType($cate_id);
        $cate = $this->cateStock($oCate);
        if($oCate['cate_count']+1 > $cate['stock']){
            $result = (new CateException([
                'code' => 15005,
                'msg' => '库存不足'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        return true;
    }

    //当前购物车id是否真实存在
    public function cateType($cate_id){
        $cateModel = M('cate');
        $cate = $cateModel->where("id=$cate_id")->find();
        if(!$cateModel){
            $result = (new CateException([
                'code' => 15012,
                'msg' => '当前购物车商品不存在'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        return $cate;
    }

    public function imProductValue($param){
        $arr = [];
        foreach ($param['productvalue'] as $value){
            $arr[] = $value['productvalue_id'];
        }
        return $productvalue =  implode(",",$arr);
    }


    public function delCate($ids, $uid){
        $map['id'] = array('in',$ids);
        $map['user_id'] = $uid;
        $cate = D('cate')->where($map)->delete();
        return $cate;
    }


}

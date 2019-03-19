<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\ProductException;
use Api\Exception\UserException;
use Api\Model\GroupsModel;
use Api\Model\ProductModel;
use Api\Service\Token;
use Api\Validate\IDMustBePostiveInt;
use Api\Validate\ProductAttr;
use Think\Controller;

class ProductController extends CommonController
{
    //获取指定分类下的商品
    public function getCateProduct()
    {
        (new IDMustBePostiveInt())->goCheck();
        $map['category_id'] = $_POST['id'];
        $map['product_type'] = array('neq', 3);
        $result = D('product')->relation('productimage')->where($map)->field('product_content,product_stock,category_id,product_parm', true)->select();
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['productimage']['productimage_url'] = C('img_prefix') . $result[$i]['productimage']['productimage_url'];
            $result[$i]['product_name'] = strip_tags($this->subtext($result[$i]['product_name'], 12));
            $res = D('product')->relation('producttype1')->where("id=" . $result[$i]['id'])->find();
            $result[$i]['product_price'] = $res['producttype1'][0]['productarrt1'][0]['price'];
            if ($result[$i]['product_type'] == C('Full_product')) {
                $result[$i]['original_price'] = $res['producttype1'][0]['productarrt1'][0]['original_price'];
            }
        }
        if (!$result) {
            $this->ajaxReturn((new ProductException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取限时购商品
    public function getSupplyProduct()
    {

        $product = M('product');
        $time = time();
        $map['i.is_thumb'] = 1;
        $map['p.product_type'] = 3;
//        $map['discount_endtime'] = array('gt',date('Y-m-d', time()));
        $result['product'] = $product
//            ->field('p.product_content')
            ->alias('p')
            ->join('icpnt_producttype1 as t ON p.id = t.product_id')
            ->join('icpnt_productattr1 as a ON t.id = a.attrtype_id')
            ->join('icpnt_productimage as i ON p.id = i.product_id')
            ->join('icpnt_discount as d ON p.id = d.product_id')
            ->where($map)
            ->group('p.id desc')
            ->order('discount_endtime desc')
            ->select();

        foreach ($result['product'] as &$item) {
            $item['productimage_url'] = C('img_prefix') . $item['productimage_url'];
            $item['attr_img'] = C('img_prefix') . $item['attr_img'];
            $item['product_name'] = strip_tags($this->subtext($item['product_name'], 12));
            $item['remaining_time'] = strtotime($item['discount_endtime']) - $time;
            //是否过期  当前时间>到期时间
            $time > strtotime($item['discount_endtime']) ? $item['is_overdue'] = 1 : $item['is_overdue'] = 0;
            //是否开始  当前时间<开始时间
            if($time < strtotime($item['discount_starttime'])){
                $item['is_overdue'] = 2;
                $item['remaining_time'] = strtotime($item['discount_starttime']) - $time;
            }
        }

//        $year = date("Y");
//        $month = date("m");
//        $day = date("d");
//        $end= mktime(23,59,59,$month,$day,$year);
//        $time1 = time();
//        $result['current_time'] = $end-$time1;

        if (!$result) {
            $this->ajaxReturn((new ProductException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取商品具体信息
    public function getProductDetail()
    {
        //id   商品id
        (new IDMustBePostiveInt())->goCheck();
        $result = D('product')->relation(array('productimages', 'comment', 'business'))->where("id=" . $_POST['id'])->find();
        //评论
        foreach ($result['comment'] as &$value) {
            if ($value['nickName'] == '') {
                $value['avatarUrl'] = "http://admin.yjsina.com/" . $value['avatarUrl'];
            }
        }
        //商品详情
        $result['product_content'] = replacePicUrl($result['product_content'], "http://admin.yjsina.com");
        $map['id'] = array('in', $result['product_parm']);
        if ($map['id'][1] == null) {
            $this->ajaxReturn((new ProductException([
                'msg' => '暂无商品服务',
                'code' => 20002,
            ]))->getException());
        }

        //商品轮播
        for ($i = 0; $i < count($result['productimages']); $i++) {
            $result['productimages'][$i]['productimage_url'] = C('img_prefix') . $result['productimages'][$i]['productimage_url'];
        }

        //商品服务
        $productServe = D('productserve')->where($map)->field('id,productserve_title')->select();
        $result['product_parm_name'] = $productServe;

        //推荐商品
        if ($result['product_recommend']) {
            $item['id'] = array('in', $result['product_recommend']);
            $result['recommend'] = D('product')->relation(array('productimage', 'producttype1'))->where($item)->field("id,product_djprice,product_name,product_brand,product_type")->order('id desc')->select();
            foreach ($result['recommend'] as &$val) {
                $val['productimage']['productimage_url'] = C('img_prefix') . $val['productimage']['productimage_url'];
                $val['product_name'] = strip_tags($this->subtext($val['product_name'], 12));
                if ($val['product_type'] == C('Deposit_Product')) {   //订金商品
//                    $val['product_price'] = $val['product_djprice'];
                    unset($val['producttype1']);
                } elseif ($val['product_type'] == C('Full_product') || $val['product_type'] == C('Discount_Product')) {  //全款商品或限时购商品
                    $val['product_price'] = $val['producttype1'][0]['productarrt1'][0]['price'];
                    $val['original_price'] = $val['producttype1'][0]['productarrt1'][0]['original_price'];
                    unset($val['producttype1']);
                }
            }
        } else {
            $result['recommend'] = '';
        }

        //商品评论
        $comment = D('comment')->where("product_id=" . $result['id'])->select();
        $result['comment_count'] = count($comment);
        //商品属性
        $result['ps'] = D('product')->relation('producttype1')->where("id=" . $_POST['id'])->find();
        $result['product_price'] = $result['ps']['producttype1'][0]['productarrt1'][0]['price'];
        $result['product_original_price'] = $result['ps']['producttype1'][0]['productarrt1'][0]['original_price'];
        unset($result['ps']);

        //商品属性
        $result['product_attr'] = D('producttype1')->relation('producttype1')->where("product_id=" . $_POST['id'])->select();
        //商品浏览量
        $click = M('product')->where("id=" . $_POST['id'])->setInc('product_click');
        //商品交易成功量
        $delivery = M('delivery')->where("product_id=" . $_POST['id'])->select();
        $result['successful_product'] = count($delivery);
        if (!$result) {
            $this->ajaxReturn((new ProductException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //获取商品的属性
    public function getProductType()
    {

        //商品id
        (new IDMustBePostiveInt())->goCheck();
        $productModel = M('product');
        $productAttr = D('producttype1')->relation('producttype1')->where("product_id=" . $_POST['id'])->select();
        foreach ($productAttr as &$value) {
            $value['attr_img'] = C('img_prefix') . $value['attr_img'];
        }
        $product = $productModel->where("id=" . $_POST['id'])->find();
//        $productAttr['product_type'] = $product['product_type'];
        if (!$productAttr) {
            $this->ajaxReturn((new ProductException([
                'msg' => '商品规格属性不存在',
                'code' => 20003,
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'product_type' => $product['product_type'],
            'data' => $productAttr,
        ]);
    }

    //全部评论
    public function getProductComment()
    {
        //id  商品id
        (new IDMustBePostiveInt())->goCheck();
        $result['product'] = D('comment')->relation('user')->where("product_id=" . $_POST['id'])->select();
        foreach ($result['product'] as &$value) {
            if ($value['nickName'] == '') {
                $value['avatarUrl'] = "http://admin.yjsina.com/" . $value['avatarUrl'];
            }
        }
        $result['Praise'] = M('comment')->where("product_id=" . $_POST['id'] . " and comment_star=" . C('Praise'))->count(); //好评
        $map['product_id'] = $_POST['id'];
        $map['comment_star'] = array('between', C('Review'));
        $result['Review'] = M('comment')->where($map)->count(); //中评
        $result['Negative'] = M('comment')->where("product_id=" . $_POST['id'] . " and comment_star=" . C('Negative'))->count(); //差评
        if (!$result) {
            $this->ajaxReturn((new ProductException([
                'msg' => '商品评论不存在',
                'code' => 20015
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //好评
    public function getProductPraise()
    {
        //id  商品id
        (new IDMustBePostiveInt())->goCheck();
        $map['product_id'] = $_POST['id'];
        $map['comment_star'] = C('Praise');
        $result = D('comment')->relation('user')->where($map)->select();
        if (!$result) {
            $this->ajaxReturn((new ProductException([
                'msg' => '商品好评不存在',
                'code' => 20016
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //中评
    public function getProductReview()
    {
        //id  商品id
        (new IDMustBePostiveInt())->goCheck();
        $map['product_id'] = $_POST['id'];
        $map['comment_star'] = array('between', C('Review'));
        $result = D('comment')->relation('user')->where($map)->select();
        if (!$result) {
            $this->ajaxReturn((new ProductException([
                'msg' => '商品中评不存在',
                'code' => 20017
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //差评
    public function getProductNegative()
    {
        //id  商品id
        (new IDMustBePostiveInt())->goCheck();
        $map['product_id'] = $_POST['id'];
        $map['comment_star'] = C('Negative');
        $result = D('comment')->relation('user')->where($map)->select();
        if (!$result) {
            $this->ajaxReturn((new ProductException([
                'msg' => '商品差评不存在',
                'code' => 20018
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取商品服务
    public function getProductServe()
    {
        $map['id'] = array('in', $_POST['product_parm']);
        $productServe = D('productserve')->where($map)->select();
        if (!$productServe) {
            $this->ajaxReturn((new ProductException([
                'msg' => '商品服务不存在',
                'code' => 20002,
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $productServe
        ]);
    }

    //首页商品随机推荐10条 后台商品推荐  上划加载更多
    public function getHomeProduct()
    {
        (new IDMustBePostiveInt())->goCheck();
        $statr_page = ($_POST['id'] - 1) * 10;
        $page = 10;
        $map['product_type'] = array('neq', 3);
        $map['status'] = 1;
        $result = D('product')->relation('productimage')->field('product_content,product_stock,category_id,product_parm', true)->where($map)->order('id desc')->select();
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['productimage']['productimage_url'] = C('img_prefix') . $result[$i]['productimage']['productimage_url'];
            $result[$i]['product_name'] = strip_tags($this->subtext($result[$i]['product_name'], 12));
            $res = D('product')->relation('producttype1')->where("id=" . $result[$i]['id'])->find();
            $result[$i]['product_price'] = $res['producttype1'][0]['productarrt1'][0]['price'];
            if ($result[$i]['product_type'] == C('Full_product')) {  //全款商品显示原价
                $result[$i]['original_price'] = $res['producttype1'][0]['productarrt1'][0]['original_price'];
            }
        }
        shuffle($result);  //打乱顺序
        $final = array_slice($result, $statr_page, $page);  //每页十条
        if (!$final) {
            $this->ajaxReturn((new ProductException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $final
        ]);
    }


    //获取一级分类下的 二级分类商品
    public function getPageProduct()
    {
        // id 一级分类id
        (new IDMustBePostiveInt())->goCheck();
        $result = D('navcategory')->relation(true)->where("navcate_pid=" . $_POST['id'])->field("id,navcate_name")->select();
        foreach ($result as &$item) {
            foreach ($item['product'] as &$value) {
                $value['product_name'] = strip_tags($this->subtext($value['product_name'], 12));
                $value['productimage']['productimage_url'] = C('img_prefix') . $value['productimage']['productimage_url'];
                $res = D('product')->relation('producttype1')->where("id=" . $value['id'])->find();
                $value['product_price'] = $res['producttype1'][0]['productarrt1'][0]['price'];
                if ($value['product_type'] == C('Full_product')) {
                    $value['original_price'] = $res['producttype1'][0]['productarrt1'][0]['original_price'];
                }
            }
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取全部分类下的所有商品
    public function getAllProduct()
    {
        (new IDMustBePostiveInt())->goCheck();
        $statr_page = ($_POST['id'] - 1) * 10;
        $page = 10;
        $map['product_type'] = array('neq', 3);
        $productModel = new ProductModel();
        $result = $productModel->relation('productimage')->where($map)->field('product_content,product_stock,category_id,product_parm', true)->limit($statr_page, $page)->order('id desc')->select();
        foreach ($result as &$item) {
            $item['product_name'] = strip_tags($this->subtext($item['product_name'], 12));
            $item['productimage']['productimage_url'] = C('img_prefix') . $item['productimage']['productimage_url'];
            $res = $productModel->relation('producttype1')->where("id=" . $item['id'])->find();
            $item['product_price'] = $res['producttype1'][0]['productarrt1'][0]['price'];
            if ($item['product_type'] == C('Full_product')) {
                $item['original_price'] = $res['producttype1'][0]['productarrt1'][0]['original_price'];
            }
        }
        if (!$result) {
            $this->ajaxReturn((new ProductException())->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


}
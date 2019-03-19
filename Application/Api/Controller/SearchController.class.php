<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\SearchException;
use Api\Model\NavcategoryModel;
use Api\Model\ProductModel;
use Api\Validate\Count;
use Api\Validate\IDMustBePostiveInt;
use Think\Controller;

class SearchController extends CommonController
{

    //获取热门搜索
    public function getHotSearch()
    {
        $result = D('search')->field('sort', true)->select();
        if (!$result) {
            $result = (new SearchException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //根据输入显示下拉分类
    public function getVagueCategory()
    {
//        (new Count())->goCheck();
        $name = $_POST['name'];
        $data['navcate_name'] = array('like', "%$name%");
        $result = D('navcategory')->where($data)->where("navcate_pid!=0")->field("navcate_name")->select();
        if (!$result) {
            $result = (new SearchException([
                'code' => 60001,
                'msg' => '暂无搜索分类',
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //下拉搜索商品
    public function getSelectProduct()
    {
//        (new Count())->goCheck();
        $name = $_POST['name'];
        $navCategoryModel = new NavcategoryModel();
        $productModel = new ProductModel();
        $navCategory = $navCategoryModel->where("navcate_name='$name'")->find();
        $navCategory['id'];
        $product = $productModel->relation('productimage')->where("category_id=" . $navCategory['id'])->select();
        foreach ($product as &$value) {
            $value['productimage']['productimage_url'] = C('img_prefix') . $value['productimage']['productimage_url'];
            $value['product_name'] = strip_tags($this->subtext($value['product_name'], 12));
            $res = D('product')->relation('producttype1')->where("id=" . $value['id'])->find();
            $value['product_price'] = $res['producttype1'][0]['productarrt1'][0]['price'];
        }
        if (!$product) {
            $result = (new SearchException([
                'code' => 60006,
                'msg' => '暂无搜索结果',
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $product
        ]);
    }
    

    //综合排序
    public function integrateSearch()
    {
        (new Count())->goCheck();
        $statr_page = ($_POST['id']-1)*10;
        $page = 10;
        $name = $_POST['name'];
        $navCategoryModel = new NavcategoryModel();
        $map['navcate_name'] = array('like', "%$name%");
        $navCategory = $navCategoryModel->where($map)->select();
        $Navcategoey_ids = [];
        foreach ($navCategory as &$value) {
            if ($value['navcate_pid'] == 0) {
                $twoCategory = $navCategoryModel->where("navcate_pid=" . $value['id'])->select();
                foreach ($twoCategory as $item) {
                    array_push($Navcategoey_ids, $item['id']);
                }
            }
            array_push($Navcategoey_ids, $value['id']);
        }
        $productModel = D('product');
//        if ($Navcategoey_ids) {
//            $where1['category_id'] = array('in', $Navcategoey_ids);
//            $where['_logic'] = 'OR';
//        }else{
//            $where['_logic'] = 'AND';
//        }
//        $where1['product_type'] = array('neq',3);
//        $where['_complex'] = $where1;
//        $where['product_name|product_brand|business_name'] = array('like', "%$name%");

        if($Navcategoey_ids){
            $where['category_id'] = array('in', $Navcategoey_ids);
        }else{
            $where['product_name|product_brand|business_name'] = array('like', "%$name%");
        }
        $where['product_type'] = array('neq',3);
        $where['is_thumb'] = 1;

        $product = $productModel
            ->alias('p')
            ->join('icpnt_productimage as i ON p.id = i.product_id')
            ->join('left join icpnt_producttype1 as t ON p.id = t.product_id')
            ->join('left join icpnt_productattr1 as a ON t.id = a.attrtype_id')
            ->join('left join icpnt_business as b ON p.business_id = b.id')
            ->where($where)
//            ->where('is_thumb=1')
            ->group("p.id")
            ->order('p.id desc')
            ->field('p.id,product_name,productimage_url,price,original_price,product_djprice,product_brand,product_type')
            ->limit($statr_page, $page)
            ->select();
        foreach ($product as &$item) {
            $item['productimage_url'] = C('img_prefix') . $item['productimage_url'];
            $item['product_name'] = strip_tags($this->subtext($item['product_name'], 12));
            if ($item['product_type'] == 1) {  //订金商品
                $item['price'] = $item['product_djprice'];
                unset($item['product_djprice']);
                unset($item['original_price']);
            } elseif ($item['product_type'] == 2) {  //全款商品
                unset($item['product_djprice']);
            }
        }

        if (!$product) {
            $result = (new SearchException([
                'code' => 60002,
                'msg' => '暂无搜索商品',
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $product
        ]);
    }

    //价格从高到低
    public function getProductHigh()
    {

        (new Count())->goCheck();
//        $statr_page = ($_POST['id']-1)*10;
//        $page = 10;
        $name = $_POST['name'];
        $navCategoryModel = new NavcategoryModel();
        $map['navcate_name'] = array('like', "%$name%");
        $navCategory = $navCategoryModel->where($map)->select();
        $Navcategoey_ids = [];
        foreach ($navCategory as &$value) {
            if ($value['pid'] == 0) {
                $twoCategory = $navCategoryModel->where("navcate_pid=" . $value['id'])->select();
                foreach ($twoCategory as $item) {
                    array_push($Navcategoey_ids, $item['id']);
                }
            }
            array_push($Navcategoey_ids, $value['id']);
        }
        $productModel = D('product');
        if ($Navcategoey_ids) {
            $where1['category_id'] = array('in', $Navcategoey_ids);
            $where['_logic'] = 'OR';
        }else{
            $where['_logic'] = 'AND';
        }
        $where1['product_type'] = array('neq',3);
        $where['_complex'] = $where1;
        $where['product_name|product_brand|business_name'] = array('like', "%$name%");
//        $where['_logic'] = 'OR';
        $product = $productModel
            ->alias('p')
            ->join('icpnt_productimage as i ON p.id = i.product_id')
            ->join('left join icpnt_producttype1 as t ON p.id = t.product_id')
            ->join('left join icpnt_productattr1 as a ON t.id = a.attrtype_id')
            ->join('left join icpnt_business as b ON p.business_id = b.id')
            ->where($where)
            ->group("p.id")
            ->order('p.id desc')
            ->field('p.id,product_name,productimage_url,price,original_price,product_djprice,product_brand,product_type')
//            ->limit($statr_page, $page)
            ->select();
        foreach ($product as &$item) {
            $item['productimage_url'] = C('img_prefix') . $item['productimage_url'];
            $item['product_name'] = strip_tags($this->subtext($item['product_name'], 12));
            if ($item['product_type'] == 1) {  //订金商品
                $item['price'] = $item['product_djprice'];
                unset($item['product_djprice']);
                unset($item['original_price']);
            } elseif ($item['product_type'] == 2) {  //全款商品
                unset($item['product_djprice']);
            }
        }

//        $a = [
//            [
//                'name' => '张三',
//                'score' => 60
//            ],
//            [
//                'name' => '李四',
//                'score' => 90
//            ],
//            [
//                'name' => '王二',
//                'score' => 80
//            ],
//        ];
        $score = [];
        foreach ($product as $key => $value) {
            $score[$key] = $value['price'];
        }
        array_multisort($score, SORT_DESC, $product);

        $statr_page = ($_POST['id']-1)*10;
        $page = 10;
        $a = array_slice($product,$statr_page,$page);

        if (!$a) {
            $result = (new SearchException([
                'code' => 60002,
                'msg' => '暂无搜索商品',
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $a
        ]);
    }

    //价格从低到高
    public function getProductLow()
    {
        (new Count())->goCheck();
        $name = $_POST['name'];
        $navCategoryModel = new NavcategoryModel();
        $map['navcate_name'] = array('like', "%$name%");
        $navCategory = $navCategoryModel->where($map)->select();
        $Navcategoey_ids = [];
        foreach ($navCategory as &$value) {
            if ($value['pid'] == 0) {
                $twoCategory = $navCategoryModel->where("navcate_pid=" . $value['id'])->select();
                foreach ($twoCategory as $item) {
                    array_push($Navcategoey_ids, $item['id']);
                }
            }
            array_push($Navcategoey_ids, $value['id']);
        }
        $productModel = D('product');
        if ($Navcategoey_ids) {
            $where1['category_id'] = array('in', $Navcategoey_ids);
            $where['_logic'] = 'OR';
        }else{
            $where['_logic'] = 'AND';
        }
        $where1['product_type'] = array('neq',3);
        $where['_complex'] = $where1;
        $where['product_name|product_brand|business_name'] = array('like', "%$name%");
//        $where['_logic'] = 'OR';
        $product = $productModel
            ->alias('p')
            ->join('icpnt_productimage as i ON p.id = i.product_id')
            ->join('left join icpnt_producttype1 as t ON p.id = t.product_id')
            ->join('left join icpnt_productattr1 as a ON t.id = a.attrtype_id')
            ->join('left join icpnt_business as b ON p.business_id = b.id')
            ->where($where)
            ->group("p.id")
            ->order('p.id desc')
            ->field('p.id,product_name,productimage_url,price,original_price,product_djprice,product_brand,product_type')
//            ->limit($statr_page, $page)
            ->select();
        foreach ($product as &$item) {
            $item['productimage_url'] = C('img_prefix') . $item['productimage_url'];
            $item['product_name'] = strip_tags($this->subtext($item['product_name'], 12));
            if ($item['product_type'] == 1) {  //订金商品
                $item['price'] = $item['product_djprice'];
                unset($item['product_djprice']);
                unset($item['original_price']);
            } elseif ($item['product_type'] == 2) {  //全款商品
                unset($item['product_djprice']);
            }
        }

//        $a = [
//            [
//                'name' => '张三',
//                'score' => 60
//            ],
//            [
//                'name' => '李四',
//                'score' => 90
//            ],
//            [
//                'name' => '王二',
//                'score' => 80
//            ],
//        ];
        $score = [];
        foreach ($product as $key => $value) {
            $score[$key] = $value['price'];
        }
        array_multisort($score, SORT_ASC, $product);
        $statr_page = ($_POST['id']-1)*10;
        $page = 10;
        $a = array_slice($product,$statr_page,$page);
        if (!$a) {
            $result = (new SearchException([
                'code' => 60002,
                'msg' => '暂无搜索商品',
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $a
        ]);
    }



}
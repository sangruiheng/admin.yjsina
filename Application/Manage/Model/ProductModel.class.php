<?php

namespace Manage\Model;

use Think\Model\RelationModel;

class ProductModel extends RelationModel
{
    protected $_link = array(
        'navCategory' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'navcategory',//要关联的表名
            'foreign_key' => 'category_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        ),
        'productimage' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'productimage',//要关联的表名
            'foreign_key' => 'product_id', //本表的字段名称
//            'as_fields' => 'img:img',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'attributeValue',   //多表关联  关联第三个模型的名称
        ),
        'business' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'business',//要关联的表名
            'foreign_key' => 'business_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
//            'relation_deep' => 'productAttr',   //多表关联  关联第三个模型的名称
        ),
        'producttype1' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'producttype1',//要关联的表名
            'foreign_key' => 'product_id', //本表的字段名称
//            'as_fields' => 'img:img',  //被关联表中的字段名：要变成的字段名
            'relation_deep'    =>    'producttype1',   //多表关联  关联第三个模型的名称
        ),
        //限时购商品具体信息
        'discount' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'discount',//要关联的表名
            'foreign_key' => 'product_id', //本表的字段名称
            'as_fields' => 'discount_starttime:discount_starttime,discount_endtime:discount_endtime',  //被关联表中的字段名：要变成的字段名
//            'relation_deep' => 'productAttr',   //多表关联  关联第三个模型的名称
        ),
    );

    protected $_validate = array(
        array('category_id', 'require', '商品分类不能为空'),
        array('product_name', 'require', '商品名称不能为空'),
//        array('product_bounds', 'require', '积分额度不能为空'),
        array('product_content', 'require', '商品内容不能为空'),
        array('product_brand', 'require', '品牌内容不能为空'),
        array('product_type', 'require', '付款方式不能为空'),
        array('business_id', 'require', '商家名称不能为空'),
    );

    public function isPositiveInteger($value, $rule = '', $date = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
        }

    }


    public static function getProductStatus($status){
        switch ($status){
            case '1':
                return "订金商品";
                break;
            case '2':
                return "全款商品";
                break;
            case 3:
                return "限时购商品";
                break;
            default:
                return '未知状态';
        }
    }




    //删除属性
    public function delAttr($product_id)
    {
        $producttype1 = M('producttype1');
        $productattr1 = M('productattr1');
        $result_protype = $producttype1->where("product_id=$product_id")->select();
        for ($i = 0; $i < count($result_protype); $i++) {
            $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $result_protype[$i]['attr_img']);
            if (file_exists($file)) {
                @unlink($file);
            }
            $productattr1->where("attrtype_id=" . $result_protype[$i]['id'])->delete();
        }
        $result = $producttype1->where("product_id=$product_id")->delete();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }


    //分类树
    public function catetree()
    {
        $cateres = D('navcategory')->select();
        return $this->sort($cateres);
    }

    public function sort($data, $pid = 0, $level = 0)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['navcate_pid'] == $pid) {
                $v['level'] = $level;
                $arr[] = $v;
                $this->sort($data, $v['id'], $level + 1);
            }
        }
        return $arr;
    }


    //添加轮播图
    public function getAddImg($request, $maxID)
    {
        $model = D('productimage');
        for ($i = 0; $i < count($request['hid']); $i++) {
            if($request['is_thumb'][$i] == 1){
                $model->is_thumb = 1;
            }else{
                $model->is_thumb = 0;
            }
            $model->product_id = $maxID;
            $model->productimage_url = substr($request['hid'][$i], 16);
            $result = $model->add();
        }
        return $result;
    }


    //修改轮播图
    public function editImg($maxID){
        $productImgModel = M('productimage');
        //更新数据
        foreach ($_POST['imgID'] as $key => $value) {
            $updateData['productimage_url'] = $_POST['imgURL'][$key];
            $updateData['product_id'] = $maxID;
            $updateData['is_thumb'] = $_POST['is_thumb'][$key];
            $productImgModel->where('id = ' . $value)->save($updateData);
        }

        //添加新图片
        foreach ($_POST['hid'] as $key => $value){
            $items = count($_POST['imgID'])+$key;
            $updateData['productimage_url'] = substr($_POST['hid'][$key], 16);
            $updateData['product_id'] = $maxID;
            $updateData['is_thumb'] = $_POST['is_thumb'][$items];
            $productImgModel->add($updateData);
        }

        return true;
    }

    //删除编辑商品时的图片
    public static function delEditImg($id)
    {
        $NewsImg = M('productimage')->where("id=$id")->find();
        $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $NewsImg['productimage_url']);
        if (file_exists($file)) {
            @unlink($file);
        }
        $result = M('productimage')->where("id=$id")->delete();
        return $result;

    }

    //删除商品文件及图片
    public static function deleteProductImg($table, $ids)
    {
        $sql = M($table);
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        //删除商品图片
        $map['product_id'] = array('in', $ids);
        $GroupImg_list = M('productimage')->where($map)->select();
        foreach ($GroupImg_list as $value) {
            $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $value["productimage_url"]);
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        //删除商品属性
        $productID = explode(",", $ids);
        foreach ($productID as $value) {
            self::delAttr($value);
        }
        //删除商品
        $Result = $sql->delete($ids);
        //删除图片表
        $res = M('productimage')->where($map)->delete();

        //删除商品时 删除购物车商品
        $cateModel = M('cate');
        $cate = $cateModel->where($map)->select();
        if ($cate) {
            foreach ($cate as $value){
                $cateModel->where("product_id=". $value['product_id'])->delete();
            }
        }

        return $res;
    }

    //添加商品属性
    public function addAttr($product_id, $color_name, $attr_name, $attr_img, $attr_num, $price, $stock, $original_price)
    {
//        $color_name = $_POST['color_name'];
//        $attr_name = $_POST['attr_name'];
        $typeSql = M('Producttype1');
        $attrSql = M('Productattr1');
        //添加颜色和图片
        $index = 0;
        foreach ($color_name as $key => $value) {
            $forNum = $attr_num[$key];
            $newArr = array_slice($attr_name, $index, $forNum);
            //echo "第一个循环=".$forNum."<p>";
            $addType['product_id'] = $product_id;
            $addType['color_name'] = $value;
            $addType['attr_img'] = $attr_img[$key];
            $typeID = $typeSql->add($addType);
            //添加规格
            foreach ($newArr as $k => $v) {
                $addAttr['attrtype_id'] = $typeID;
                $addAttr['attr_name'] = $v;
                $addAttr['price'] = $price[$index];
                $addAttr['stock'] = $stock[$index];
                $addAttr['original_price'] = $original_price[$index];
                $attrID = $attrSql->add($addAttr);
                //echo "k = ".$k.", 规格=".$v.", 价格=".$_POST['price'][$index]."，库存=".$_POST['stock'][$index]."<p>";
                $index++;
            }
        }
        return true;
    }


    //修改商品属性
    public function updateAttr($id)
    {

        $typeSql = M('Producttype1');
        $attrSql = M('Productattr1');
        //更新颜色数据
        foreach ($_POST['colorID'] as $key => $value) {
            $updateData['color_name'] = $_POST['color_name_edit'][$key];
            $updateData['attr_img'] = $_POST['attr_img_eidt'][$key];
            $typeSql->where('id = ' . $value)->save($updateData);
        }
        //更新规格数据
        foreach ($_POST['atIDs'] as $key => $value) {
            $updateData['attr_name'] = $_POST['attr_name_edit'][$key];
            $updateData['price'] = $_POST['price_edit'][$key];
            $updateData['stock'] = $_POST['stock_edit'][$key];
            $updateData['original_price'] = $_POST['original_price_edit'][$key];
            $attrSql->where('id = ' . $value)->save($updateData);
        }

        //在现有颜色数据下添加规格
        foreach ($_POST['edit_color_id'] as $key => $value) {
            $addAttr['attrtype_id'] = $value;
            $addAttr['attr_name'] = $_POST['attr_name_edit_add'][$key];
            $addAttr['price'] = $_POST['price_edit_add'][$key];
            $addAttr['stock'] = $_POST['stock_edit_add'][$key];
            $addAttr['original_price'] = $_POST['original_price_edit_add'][$key];
            $attrSql->add($addAttr);
        }

        //编辑状态下添加新颜色和规格
        $color_name = $_POST['color_name'];
        $index = 0;
        foreach ($color_name as $key => $value) {
            $forNum = $_POST['attr_num'][$key];
            $newArr = array_slice($_POST['attr_name'], $index, $forNum);
            $addType['product_id'] = $id;
            $addType['color_name'] = $value;
            $addType['attr_img'] = $_POST['attr_img'][$key];
            $typeID = $typeSql->add($addType);
            //编辑状态下添加新规格
            foreach ($newArr as $k => $v) {
                $addAttr['attrtype_id'] = $typeID;
                $addAttr['attr_name'] = $v;
                $addAttr['price'] = $_POST['price'][$index];
                $addAttr['stock'] = $_POST['stock'][$index];
                $addAttr['original_price'] = $_POST['original_price'][$index];
                $attrID = $attrSql->add($addAttr);
                $index++;
            }
        }
    }







}
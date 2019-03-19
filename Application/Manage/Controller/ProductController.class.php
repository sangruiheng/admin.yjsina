<?php

namespace Manage\Controller;

use Manage\Model\AttributenameModel;
use Manage\Model\ProductModel;
use Think\Controller;

class ProductController extends CommonController
{


    public function productList()
    {
        $search = array();
        $keyWord = urldecode($_GET['keyWord']);
        if (!empty($keyWord)) {
            $map = $this->Search('product', $keyWord);
            $search['keyWord'] = $keyWord;
        }

        //商家
        if ($_GET['business_id']) {
            $map['business_id'] = $_GET['business_id'];
            $search['business_id'] = $_GET['business_id'];
        }

        $p = $_GET['p'];
        if(empty($p)){
            $p = 1;
        }
        $map['product_type'] = array('neq',3);
        $Product = D('product')->relation(array('navCategory','productType','productimage','business','producttype1'))->where($map)->order('id desc')->page($p.',10')->select();
        foreach ($Product as &$value){
            $value['product_type'] = ProductModel::getProductStatus($value['product_type']);
        }
//        print_r($Product);
        $count = D('product')->relation(array('navCategory','productType','productimage','business','producttype1'))->where($map)->count();
        $Page = getpage($count, 10);
        foreach($map as $key=>$val) {
            $page->parameter .= "$key=".urlencode($val).'&';
        }
        $businessModel = M('business');
        $business = $businessModel->select();
        $this->assign('business', $business);
        $this->assign('search', $search);
        $this->assign('page', $Page->show());
        $this->assign('list', $Product);
        $this->display();
    }

    //导出商家产品数据
    public function exportExcel()
    {
        //商家
        if ($_GET['business_id']) {
            $map['business_id'] = $_GET['business_id'];
        }

        $Product = D('product')->relation(array('navCategory','productType','productimage','business','producttype1'))->where($map)->order('id desc')->select();
        foreach ($Product as &$value){
            if($value['product_type'] == C('Deposit_Product')){
                $value['product_type'] = '订金商品';
            } elseif ($value['product_type'] == C('Full_product')){
                $value['product_type'] = '全款商品';
            }
        }

        $excelData = array();
        $j = 1;
        $i = 1;
        foreach ($Product as $k => $val) {
            $excelData[$k][id] = $i++;//编号
            $excelData[$k][product_name] = $val['product_name'];//客户端
            $excelData[$k][navcate_name] = $val['navCategory']['navcate_name'];//城市
            $excelData[$k][product_type] = $val['product_type'];//名字
            $excelData[$k][business_name] = $val['business']['business_name'];//电话
            $excelData[$k][business_tel] = $val['business']['business_tel'];//电话
            $excelData[$k][business_address] = $val['business']['business_address'];//电话
            if($val['producttype1']){
                foreach($val['producttype1'] as $item){
                    foreach ($item['producttype1'] as $attrs){
                        $excelData[$k][producttype1] .= $j++.'： 属性:'.$item['color_name'].'/'.$attrs['attr_name'].'  价格:'.$attrs['price']."   ";//商品属性
                    }
                }
            }else{
                $excelData[$k][producttype1] = '订金价格'.$val['product_djprice'];//商品属性
            }
//            $excelData[$k][count] = $val['count'];//面积
//            $excelData[$k][totalPrice] = $val['totalPrice'];//设计师
//            $excelData[$k][productvalue] = $val['productvalue'];//小区
//            $excelData[$k][contacts] = $val['contacts'];//小区
//            $excelData[$k][contacts_tel] = $val['contacts_tel'];//小区
//            $excelData[$k][express_name] = $val['express_name'];//小区
//            $excelData[$k][delivery_no] = $val['delivery_no'];//小区
//            $excelData[$k][order_producttype_name] = $val['order_producttype_name'];//渠道
//            $excelData[$k][deliver_time] = $val['deliver_time'];//关键字

        }
        foreach ($excelData as $field => $v) {
            if ($field == 'id') {
                $headArr[] = '编号';
            }

            if ($field == 'product_name') {
                $headArr[] = '商品名称';
            }
            if ($field == 'navcate_name') {
                $headArr[] = '所属分类';
            }
            if ($field == 'product_type') {
                $headArr[] = '商品类型';
            }
            if ($field == 'business_name') {
                $headArr[] = '商家名称';
            }
            if ($field == 'business_tel') {
                $headArr[] = '商家电话';
            }
            if ($field == 'business_address') {
                $headArr[] = '商家位置';
            }
            if ($field == 'producttype1') {
                $headArr[] = '商品属性';
            }
//            if ($field == 'productvalue') {
//                $headArr[] = '商品属性';
//            }
//            if ($field == 'contacts') {
//                $headArr[] = '商家联系人';
//            }
//            if ($field == 'contacts_tel') {
//                $headArr[] = '商家联系方式';
//            }
//            if ($field == 'express_name') {
//                $headArr[] = '快递公司';
//            }
//            if ($field == 'delivery_no') {
//                $headArr[] = '物流单号';
//            }
//            if ($field == 'order_producttype_name') {
//                $headArr[] = '订单类型';
//            }
//            if ($field == 'deliver_time') {
//                $headArr[] = '确认收货时间';
//            }
        }
        $filename = $val['business']['business_name'].'--商品详情表';  //生成的Excel文件文件名
        getExcel($filename, $headArr, $excelData);
    }



    //编辑商品查询数据
    //   productController product.js product.css addProduct common.js CommonController的方法移动到productController
    //  建立模型
    public function getEditProductData()
    {
        $table = I('table');
        if (empty($table)) $this->ajaxReturn(array('code' => 400, 'msg' => '表名缺失'));
        $where = I('where');
        if (empty($where)) $this->ajaxReturn(array('code' => 400, 'msg' => '条件缺失'));
        $field = I('field');
        if (empty($field)) $field = '*';
        $product = M($table)->where($where)->field($field)->find();
        $id = $product['id'];
        $productImg = M('productimage')->where("product_id=$id")->select();
        $product['ProductImg'] = $productImg;
        $productAttr = D('producttype1')->relation(true)->where("product_id=$id")->select();
        $product['productAttr'] = $productAttr;
        if ($product) {
            $this->ajaxReturn(array('code' => 200, 'data' => $product));
        } else {
            $this->ajaxReturn(array('code' => 400, 'msg' => '暂无数据'));
        }
    }


    public function addProduct()
    {
        $Product = new ProductModel();
        if (IS_POST) {
            $request = I('post.');
            $backUrl = $_GET['backUrl'];
            $table = $_GET['table'];
            $controller = $_GET['controller'];
            $id = $_POST['id'];
            $productServeID = $request['productServeID'];
            $productServeID = implode(",", $productServeID);  //商品服务
            $recommendProduct = $request['recommendProduct'];
            $recommendProduct = implode(",", $recommendProduct);  //推荐商品
            $sql = D($table);
            $typeSql = M('Producttype1');
            $attrSql = M('Productattr1');
            if ($sql->create()) {
                if (empty($id)) { //添加
                    $sql->id = NULL;
                    $sql->product_content = htmlspecialchars_decode($_POST['product_content']);
                    $sql->product_brand = $_POST['product_brand'];
                    $sql->product_parm = $productServeID;
                    $sql->product_recommend = $recommendProduct;
                    if($_POST['product_type'] == 1){
                        $sql->product_bounds = 0;
                    }
                    $result = $sql->add();
                    //当分类下面没有推荐商品时 默认推荐自己
                    if (!$recommendProduct) {
                        $product = M('product');
                        $product->product_recommend = $result;
                        $product->where("id=$result")->save();
                    }
                    //全款
                    if ($_POST['product_type'] == 2){
                        $Product->addAttr($result, $_POST['color_name'], $_POST['attr_name'], $_POST['attr_img'], $_POST['attr_num'], $_POST['price'], $_POST['stock'], $_POST['original_price']);
                    }
                    if ($request['hid']) {
                        $Product->getAddImg($request, $result);
                    }

                } else {  //修改
                    $sql->product_parm = $productServeID;
                    $sql->product_content = htmlspecialchars_decode($_POST['product_content']);
                    $sql->product_brand = $_POST['product_brand'];
                    $sql->product_recommend = $recommendProduct;
                    //编辑轮播图
                    $Product->editImg($id);
                    //编辑属性
                    $Product->updateAttr($id);
                    $result = $sql->save();
                }
//                $this->success('编辑成功！', U($controller . '/' . $backUrl));
                $this->success('编辑成功！', U($controller . '/' . $backUrl . '/p/' . $request['productPage'] . '/keyWord/' . $request['keyWord']));
            } else {
                $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
            }
        } else {

            $productPage = $_GET['productPage'];
            $keyWord = urldecode($_GET['keyWord']);
            if($keyWord == 'index.php'){
                $keyWord = '';
            }


            $productCateTree = $Product->catetree();
//            print_r($productCateTree);
            $productServe = D('productserve')->select();
            $this->assign('productServe', $productServe);
            $this->assign('category', $productCateTree);
            $productType = D('producttype')->select();
            $this->assign('productType', $productType);
            $business = M('business')->select();
            $this->assign('business', $business);
            $this->assign('productPage', $productPage);
            $this->assign('keyWord', $keyWord);
            $this->display();
        }

    }


    //获取商品颜色
    public function getProductColor(){
        $sql = D('Producttype1');
        $where['product_id'] = $_POST['product_id'];
        $list = $sql->where($where)->relation(true)->select();
        $this->ajaxReturn($list);
    }

    //删除商品颜色
    public function delProductColor(){
        $sql = M('Productattr1');
        $where['attrtype_id'] = $_POST['id'];
        $attrIDs = $sql->where($where)->getField('id',true);//先查找属性数据并删除
        if(!empty($attrIDs)){
            $attrIDs = implode(',',$attrIDs);
            $sql->delete($attrIDs);
        }
        //颜色图片删除没做
        M('Producttype1')->where('id = '.$_POST['id'])->delete();//再删除颜色数据
    }

    //删除商品规格
    public function delProductAttr(){
        M('Productattr1')->where('id = '.$_POST['id'])->delete();
    }


    //删除修改商品时的图片
    public function delImg()
    {
        $result = ProductModel::delEditImg($_POST['id']);
        if ($result) {
            $this->ajaxReturn([
                'code' => 200,
                'msg' => 'success',
                'data' => $result
            ]);
        } else {
            $this->ajaxReturn([
                'code' => 400,
                'msg' => 'error'
            ]);
        }

    }


    //删除属性图片
    public function deleteUploadImg(){
        $file = $_SERVER["DOCUMENT_ROOT"].$_POST['path'];
        if(file_exists($file)){
            $result = @unlink ($file);
        }
        if(empty($_POST['pictureID']))return false;
        M('picture')->delete($_POST['pictureID']);
    }

    //设置为商品缩略图
    public function thumb()
    {
        $productImageID = $_POST['productImgID'];
        $productID = $_POST['productID'];
        $productImage = D('productimage');
        $product = D('productimage')->where("product_id=$productID and is_thumb=1")->select();
        if ($product) {
            $productImage->is_thumb = 0;
            $result = $productImage->where("product_id=$productID")->save();
        }
        $productImage->is_thumb = 1;
        $result = $productImage->where("product_id=$productID and id=$productImageID")->save();
        if ($result) {
            $this->ajaxReturn($result);
        }
    }

    //删除商品及图片
    public function deleteProduct()
    {
        ProductModel::deleteProductImg($_POST['table'], $_POST['delID']);
    }


    public function productType()
    {
        $this->getDlist('producttype', $_GET['keyWord']);
    }


    public function productAttr()
    {
        $type_id = $_GET['type_id'];

        $this->getDlist('productattr', $_GET['keyWord'], "producttype_id=$type_id");
    }


    public function productTypeList()
    {
        $type_id = $_POST['type_id'];
        $productType = D('productattr')->relation(true)->where("producttype_id=$type_id")->order('id desc')->select();
        if ($productType) {
            $this->ajaxReturn($productType);
        } else {
            $this->ajaxReturn(false);
        }
    }

    //删除属性及下面的值
    public function deleteAttrData()
    {
        $table = $_POST['table'];
        $sql = M($table);
        $ids = $_POST['delID'];
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        $Result = $sql->delete($ids);     //删除当前表数据
        if (strpos($ids, ',') == false) {  //没有逗号  选择一条
            $result = D('productvalue')->where("productattr_id=$ids")->delete();
        } else {   //选择多条
            $arr_id = explode(",", $ids);
            for ($i = 0; $i < count($arr_id); $i++) {
                $result = D('productvalue')->where("productattr_id=$arr_id[$i]")->delete();
            }
        }
        $this->auth_save_group($table, $ids);
    }


    //删除分类
    public function deleteTypeData()
    {
        $table = $_POST['table'];
        $sql = M($table);
        $ids = $_POST['delID'];
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        $Result = $sql->delete($ids);     //删除当前表数据
        if (strpos($ids, ',') == false) {  //没有逗号  选择一条
            $productattr = D('productattr')->where("producttype_id=$ids")->select();
            if ($productattr) {
                D('productattr')->where("producttype_id=$ids")->delete();
                for ($i = 0; $i < count($productattr); $i++) {
                    $productvalue = D('productvalue')->where("productattr_id=" . $productattr[$i]['id'])->find();
                    if ($productvalue) {
                        D('productvalue')->where("productattr_id=" . $productattr[$i]['id'])->delete();
                    }
                }
            }

//            $sql = "DELETE `icpnt_producttype`,`icpnt_productattr`,`icpnt_productvalue` from icpnt_producttype
//                    JOIN icpnt_productattr ON icpnt_producttype.id=icpnt_productattr.producttype_id
//                    JOIN icpnt_productvalue ON icpnt_productattr.id=icpnt_productvalue.productattr_id WHERE icpnt_producttype.id=$ids";
//            $result = $productattr->execute($sql);

        } else {  //多个删除
            $arr_id = explode(",", $ids);
            for ($i = 0; $i < count($arr_id); $i++) {
                $productattr = D('productattr')->where("producttype_id=" . $arr_id[$i])->find();
                if ($productattr) {
                    D('productattr')->where("producttype_id=" . $arr_id[$i])->delete();
                }
                $productvalue = D('productvalue')->where("productattr_id=" . $productattr['id'])->find();
                if ($productvalue) {
                    D('productvalue')->where("productattr_id=" . $productattr['id'])->delete();
                }
            }
//            for ($i=0; $i<count($arr_id); $i++){
//                $sql = "DELETE `icpnt_producttype`,`icpnt_productattr`,`icpnt_productvalue` from icpnt_producttype
//                    JOIN icpnt_productattr ON icpnt_producttype.id=icpnt_productattr.producttype_id
//                    JOIN icpnt_productvalue ON icpnt_productattr.id=icpnt_productvalue.productattr_id WHERE icpnt_producttype.id=$arr_id[$i]";
//                $result = $productattr->execute($sql);
//            }
        }
        $this->auth_save_group($table, $ids);
    }


    //删除属性值
    public function deleteValueData()
    {
        $table = $_POST['table'];
        $sql = M($table);
        $ids = $_POST['delID'];
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        $Result = $sql->delete($ids);
        $this->auth_save_group($table, $ids);
    }


    public function addProductAttr()
    {
        if (IS_POST) {
            $backUrl = $_GET['backUrl'];
            $table = $_GET['table'];
            $controller = $_GET['controller'];
            $id = $_POST['id'];
            $ids = $_POST['sss'];
            $product_type = D('producttype')->where("id=" . $_POST['sss'])->find();
            $type_name = $product_type['producttype_name'];
            $sql = D($table);
            if ($sql->create()) {
                if (empty($id)) {
                    $sql->id = NULL;
                    $sql->producttype_id = $_POST['sss'];
                    $result = $sql->add();
                    $this->setAuth($table, $result);
                } else {
                    $result = $sql->save();
                }
                if ($result) {
                    $this->success('编辑成功！', U($controller . '/' . $backUrl . '/type_id/' . $_POST['sss'] . '/&type_name/' . $type_name));
                }
            } else {
                $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
            }

        } else {
            $this->display();
        }
    }

    public function productValue()
    {

        $attr_id = $_GET['attr_id'];

        $this->getDlist('productvalue', $_GET['keyWord'], "productattr_id=$attr_id");
    }

    public function addProductValue()
    {
        if (IS_POST) {
            $backUrl = $_GET['backUrl'];
            $table = $_GET['table'];
            $controller = $_GET['controller'];
            $request = I('post.');
            $id = $_POST['id'];
            $product_attr = D('productattr')->where("id=" . $_POST['sss'])->find();
            $attr_name = $product_attr['productattr_name'];
            $sql = D($table);
            if ($sql->create()) {
                if (empty($id)) {
                    $sql->id = NULL;
                    $sql->productattr_id = $_POST['sss'];
                    $sql->productvalue_img = substr($request['hid'][0], 16);
                    $result = $sql->add();
                    $this->setAuth($table, $result);
                } else {
                    $productvalue = D('productvalue')->where("id=$id")->find();
                    $file = ('Uploads/Manage/' . $productvalue['productvalue_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->productvalue_img = substr($request['hid'][0], 16);
                    $sql->productvalue_name = $_POST['productvalue_name'];
                    $result = $sql->save();
                }
                if ($result) {
                    $this->success('编辑成功！', U($controller . '/' . $backUrl . '/attr_id/' . $_POST['sss'] . '/&attr_name/' . $attr_name));
                }
            } else {
                $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
            }

        } else {
            $this->display();
        }
    }

    public function serveList(){
        $this->getDlist('productserve', $_GET['keyWord']);
    }


    //根据分类查询商品
    public function cateProductList(){
        $category_id = $_POST['category_id'];
        $map['category_id'] = $category_id;
        $map['product_type'] = array('neq',3);
        $product = M('product')->where($map)->order('id desc')->select();
        if ($product) {
            $this->ajaxReturn([
                'code' => 200,
                'msg' => 'success',
                'data' => $product
            ]);
        } else {
            $this->ajaxReturn([
                'code' => 400,
                'msg' => 'error'
            ]);
        }
    }

    //修改时显示推荐商品
    public function showProductRecommend(){
        $category_id = $_POST['category_id'];
//        $map['id'] = array('in', $product_recommend);
//        $map['id'] = array('in',array('1','5','8'));
        $products = D('product')->where("category_id=$category_id")->order('id desc')->select();   //查找真实的数据库商品数据
        if ($products) {
            $this->ajaxReturn([
                'code' => 200,
                'msg' => 'success',
                'data' => $products
            ]);
        } else {
            $this->ajaxReturn([
                'code' => 400,
                'msg' => 'error'
            ]);
        }

    }

    public function supplyProductList(){
        $productModel = D('product');
        $keyWord = $_GET['keyWord'];
//        $search = array();
        if ($keyWord) {
            $map = $this->Search('product',$keyWord);
        }
        $p = $_GET['p'];
        if(empty($p)){
            $p = 1;
        }
        $map['product_type'] = 3;
        $Product = $productModel->relation(array('navCategory','discount','productType','productimage','business','producttype1'))->where($map)->order('id desc')->page($p.',10')->select();
        foreach ($Product as &$value){
            $value['product_type'] = ProductModel::getProductStatus($value['product_type']);
        }
//        print_r($Product);
        $count = D('product')->relation(array('navCategory','discount','productType','productimage','business','producttype1'))->where($map)->count();
        $Page = getpage($count, 10);
        foreach($map as $key=>$val) {
            $page->parameter .= "$key=".urlencode($val).'&';
        }
        $businessModel = M('business');
        $business = $businessModel->select();
        $this->assign('business', $business);
//        $this->assign('search', $search);
        $this->assign('page', $Page->show());
        $this->assign('list', $Product);
        $this->display();
    }

    public function addSupplyProduct(){
        $Product = new ProductModel();
        if (IS_POST) {
            $request = I('post.');
            $backUrl = $_GET['backUrl'];
            $table = $_GET['table'];
            $controller = $_GET['controller'];
            $id = $_POST['id'];
            $productServeID = $request['productServeID'];
            $productServeID = implode(",", $productServeID);  //商品服务
            $recommendProduct = $request['recommendProduct'];
            $recommendProduct = implode(",", $recommendProduct);  //推荐商品
            $sql = D($table);
            $typeSql = M('Producttype1');
            $attrSql = M('Productattr1');
            $discountModel = M('discount');
            if ($sql->create()) {
                if (empty($id)) { //添加
                    $sql->id = NULL;
                    $sql->product_content = htmlspecialchars_decode($_POST['product_content']);
                    $sql->product_brand = $_POST['product_brand'];
                    $sql->product_parm = $productServeID;
                    $sql->product_recommend = $recommendProduct;
                    $sql->product_type = 3;
                    $result = $sql->add();

                    //添加限时购商品时间
                    $discountModel->product_id = $result;
                    $discountModel->discount_starttime = $_POST['discount_starttime'];
                    $discountModel->discount_endtime = $_POST['discount_endtime'];
                    $discountModel->add();

                    //当分类下面没有推荐商品时 默认推荐自己
                    if (!$recommendProduct) {
                        $product = M('product');
                        $product->product_recommend = $result;
                        $product->where("id=$result")->save();
                    }
                    //全款
                    $Product->addAttr($result, $_POST['color_name'], $_POST['attr_name'], $_POST['attr_img'], $_POST['attr_num'], $_POST['price'], $_POST['stock'], $_POST['original_price']);
                    if ($request['hid']) {
                        $Product->getAddImg($request, $result);
                    }

                } else {  //修改
                    $sql->product_parm = $productServeID;
                    $sql->product_content = htmlspecialchars_decode($_POST['product_content']);
                    $sql->product_brand = $_POST['product_brand'];
                    $sql->product_recommend = $recommendProduct;

                    //修改限时购商品时间
                    $discountModel->discount_starttime = $_POST['discount_starttime'];
                    $discountModel->discount_endtime = $_POST['discount_endtime'];
                    $discountModel->where("product_id=$id")->save();

                    //编辑轮播图
                    $Product->editImg($id);
                    //编辑属性
                    $Product->updateAttr($id);
                    $result = $sql->save();
                }
                $this->success('编辑成功！', U($controller . '/' . $backUrl));
            } else {
                $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
            }
        } else {
            $productCateTree = $Product->catetree();
//            print_r($productCateTree);
            $productServe = D('productserve')->select();
            $this->assign('productServe', $productServe);
            $this->assign('category', $productCateTree);
            $productType = D('producttype')->select();
            $this->assign('productType', $productType);
            $business = M('business')->select();
            $this->assign('business', $business);
            $this->display();
        }
    }

    //获取限时购商品详情
    public function getDiscount(){
        $product_id = $_POST['product_id'];
        $discountModel = M('discount');
        $discount = $discountModel->where("product_id=$product_id")->find();
        if(!$discount){
            $this->ajaxReturn([
                'code' => 400,
                'msg' => 'error',
            ]);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $discount
        ]);
    }


}


?>
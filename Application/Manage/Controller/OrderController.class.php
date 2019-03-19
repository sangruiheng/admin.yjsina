<?php

namespace Manage\Controller;

use Manage\Model\OrderModel;
use Think\Controller;

class OrderController extends CommonController
{


    public function orderList()
    {
        $keyWord = $_GET['keyWord'];
        if (!empty($keyWord)) {
            $map["order_no|order_price|nickName|tel|order_status|order_producttype|order_count|order_tailmoney"] = array('like', "%$keyWord%");
        }

        if ($_GET['status']) {
            $map['order_status'] = $_GET['status'];
        }

        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        (new OrderModel())->setUnreadMessage();  //设置已读订单
//        $Order = (new OrderModel())->getOrder($map, $p);   //根据订单状态 查询订单
        $orderViewModel = D('orderView');
        $order = $orderViewModel->where($map)->order('id desc')->page($p . ',10')->select();

        foreach ($order as &$val) {
            $val['snap_address'] = json_decode($val['snap_address'], true);
            $val['address'] = $val['snap_address']['address_city'] . $val['snap_address']['address_detail'];
            $val['order_status'] = OrderModel::orderStatus($val['order_status']);
            $val['snap_type'] = count(json_decode($val['snap_items']));
            $val['order_producttype_name'] = OrderModel::orderProductType($val['order_producttype']);
            if ($val['order_tailmoney']) {
                $val['order_price'] += $val['order_tailmoney'];
            }
            $val['nickName'] = urldecode($val['nickName']);
            if (!$val['nickName']) {
                $val['nickName'] = $val['tel'];
            }
            unset($val['snap_items']);
        }
//        print_r($order);
        $count = $orderViewModel->where($map)->count();
        $Page = getpage($count, 10);
//        foreach ($map as $key => $val) {
//            $page->parameter .= "$key=" . urlencode($val) . '&';
//        }
        $this->assign('page', $Page->show());
        $this->assign('sataus', OrderModel::orderStatus($_GET['status']));
        $this->assign('list', $order);
        $this->display();
    }


    public function getAllOrder()
    {
        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('order', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $orderModel = D('order');
        $Order = $orderModel->where($map)->where("id=" . $_GET['order_id'])->order('id desc')->page($p . ',10')->find();

        if ($Order['order_producttype'] == 1) {    //订金
            $Orders = json_decode($Order['snap_items'], true);
            foreach ($Orders as &$val) {
                $val['productvalue'] = '---';
                $val['price'] = $val['totalPrice'];
                $val['order_producttype'] = $val['product_type'];
                $val['totalPrice'] = $Order['order_tailmoney'];
                $business = (new OrderModel())->getBusiness($val['business_id']);
                $val['business'] = $business['business_name'];
                $navCategory = (new OrderModel())->getCategory($val['category_id']);
                $val['category_name'] = $navCategory['navcate_name'];
            }


        } elseif ($Order['order_producttype'] == 2 || $Order['order_producttype'] == 3) {   //全款 限时购
            $Orders = json_decode($Order['snap_items'], true);
            foreach ($Orders as &$val) {
                $val['price'] = $val['totalPrice'] / $val['count'];
                $val['order_producttype'] = $Order['order_producttype'];
                $business = (new OrderModel())->getBusiness($val['business_id']);
                $val['business'] = $business['business_name'];
                $navCategory = (new OrderModel())->getCategory($val['category_id']);
                $val['category_name'] = $navCategory['navcate_name'];
            }
        }

//        print_r($Orders);
        $count = count($Orders);
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $Orders);
        $this->display();
    }


    //订单发货显示
    public function deliveryList()
    {
        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('order', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $map['order_status'] = C('Paid');
        $Order = (new OrderModel())->getOrder($map, $p);   //根据订单状态 查询订单
        foreach ($Order as &$value) {
            $value['order_producttype_name'] = OrderModel::orderProductType($value['order_producttype']);
            if ($value['order_tailmoney']) {
                $value['order_price'] += $value['order_tailmoney'];
            }
            $value['nickName'] = urldecode($value['nickName']);
            if (!$value['nickName']) {
                $value['nickName'] = $value['tel'];
            }
        }
        (new OrderModel())->setUnreadMessage();  //设置已读订单
        $count = D('order')->relation('user')->where($map)->count();
//        print_r($Order);
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $Order);
        $this->display();
    }

    //查看待发货订单详情
    public function SendDelivery()
    {
        //对比当前订单的所有商品 与 已发货订单的商品  ==已发货
        $delivery = M('delivery')->where("order_id=" . $_GET['order_id'])->select();
        $this->assign('order_id', $_GET['order_id']);
        $this->assign('delivery', $delivery);
        $this->getAllOrder();
    }


    //物流发货
    public function readyOrder()
    {
        //快递公司  快递订单号
        if (IS_POST) {
            $delivery = D('delivery');
            $id = $_POST['id'];
            $product_id = $_POST['product_id'];

            //获取商家信息 分类信息
            $orderModel = M('order');
            $navCategoryModel = M('navcategory');
            $order = $orderModel->where('id=' . $_POST['order_id'])->find();
            $order_product = json_decode($order['snap_items'], true);
            foreach ($order_product as $value) {
                if ($value['id'] == $product_id) {
                    $business_id = $value['business_id'];
                    $category_id = $value['category_id'];
                    $navCategory = $navCategoryModel->where("id=$category_id")->find();
                    $topCategory_id = $navCategory['navcate_pid'];
                }
            }

            if ($delivery->create()) {
                if (empty($id)) {
                    $delivery->id = null;
                    $delivery->user_id = $_POST['user_id'];
                    $delivery->product_status = C('shipped');
                    $delivery->deliver_type = 1;
                    $delivery->business_id = $business_id;
                    $delivery->category_id = $category_id;
                    $delivery->topcategory_id = $topCategory_id;
                    $result = $delivery->add();
                    //订单所有商品发货后， 状态改为已发货
                    (new OrderModel())->productContrast($_POST['order_id']);
                } else {
                    $result = $delivery->save();
                }
                if ($result) {
                    if ($id) {
                        $this->success('编辑成功！', U('Order/deliveryProduct'));
                    }
                    $this->success('编辑成功！', U('Order/deliveryList'));
                }
            }
        } else {
            $express = M('express')->select();
            $product = M('product')->where('id=' . $_GET['product_id'])->find();
            $this->assign('express', $express);
            $this->assign('product', $product);
            $this->assign('order_id', $_GET['order_id']);
            $this->display();
        }
    }

    //自主发货
    public function autonomyOrder()
    {
        if (IS_POST) {
            $delivery = D('delivery');
            $id = $_POST['id'];
            $order_id = $_POST['order_id'];
            $product_id = $_POST['product_id'];
            $order_producttype = $_POST['order_producttype'];


            //获取商家信息 分类信息
            $orderModel = M('order');
            $navCategoryModel = M('navcategory');
            $order = $orderModel->where("id=$order_id")->find();
            $order_product = json_decode($order['snap_items'], true);
            foreach ($order_product as $value) {
                if ($value['id'] == $product_id) {
                    $business_id = $value['business_id'];
                    $category_id = $value['category_id'];
                    $navCategory = $navCategoryModel->where("id=$category_id")->find();
                    $topCategory_id = $navCategory['navcate_pid'];
                }
            }

            if ($delivery->create()) {
                if (empty($id)) {
                    $delivery->id = null;
                    $delivery->user_id = $_POST['user_id'];
                    $delivery->product_status = C('shipped');
                    $delivery->deliver_type = 2;
                    $delivery->business_id = $business_id;
                    $delivery->category_id = $category_id;
                    $delivery->topcategory_id = $topCategory_id;
                    $result = $delivery->add();
                    //订单所有商品发货后， 状态改为已发货
                    if ($order_producttype == C('Deposit_Product')) {  //订金商品
                        $orderModel = M('order');
                        $orderModel->order_status = C('shipped');
                        $orderModel->where("id=$order_id")->save();
                    } elseif ($order_producttype == C('Full_product') || $order_producttype == C('Discount_Product')) {    //全款商品 限时购
                        (new OrderModel())->productContrast($_POST['order_id']);
                    }
                } else {
                    $result = $delivery->save();
                }
                if ($result) {
                    if ($id) {
                        $this->success('编辑成功！', U('Order/deliveryProduct'));
                    }
                    $this->success('编辑成功！', U('Order/deliveryList'));
                }
            }
        } else {
            $product = M('product')->where('id=' . $_GET['product_id'])->find();
            $this->assign('product', $product);
            $this->assign('order_id', $_GET['order_id']);
            $this->display();
        }
    }

    //物流发货商品列表
    public function deliveryProduct()
    {
        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('order', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $map['deliver_type'] = 1;  //物流
        $map['product_status'] = C('shipped');
        $delivery = D('delivery')->relation(true)->where($map)->order('id desc')->page($p . ',10')->select();
        foreach ($delivery as &$value) {
            $value['order']['snap_items'] = json_decode($value['order']['snap_items'], true);
        }
//        print_r($delivery);
        $product = [];
        foreach ($delivery as $val) {
            foreach ($val['order']['snap_items'] as &$item) {
                $item['express_name'] = $val['express_name'];
                $item['delivery_no'] = $val['delivery_no'];
                $item['order_no'] = $val['order']['order_no'];
                $item['delivery_id'] = $val['id'];
                $item['order_id'] = $val['order_id'];
                $item['user_id'] = $val['order']['user_id'];
                if ($val['order']['order_producttype'] == 1) {
                    $item['totalPrice'] += $val['order']['order_tailmoney'];
                }
                $item['order_producttype'] = OrderModel::orderProductType($val['order']['order_producttype']);
                $business = (new OrderModel())->getBusiness($item['business_id']);
                $item['business'] = $business['business_name'];

                if ($val['product_id'] == $item['id']) {
                    $product[] = $item;
                }

            }
        }
//        print_r($product);
        $count = count($product);
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $product);
        $this->display();
    }


    //自主发货商品列表
    public function autonomyProduct()
    {
        if (!empty($_GET['keyWord'])) {
            $map = $this->Search('order', $_GET['keyWord']);
        }
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $map['deliver_type'] = 2;
        $map['product_status'] = C('shipped');
        $delivery = D('delivery')->relation(true)->where($map)->order('id desc')->page($p . ',10')->select();
        foreach ($delivery as &$value) {
            $value['order']['snap_items'] = json_decode($value['order']['snap_items'], true);
        }
//        print_r($delivery);
        $product = [];
        foreach ($delivery as $val) {
            foreach ($val['order']['snap_items'] as &$item) {
                $item['express_name'] = $val['express_name'];
                $item['delivery_no'] = $val['delivery_no'];
                $item['order_no'] = $val['order']['order_no'];
                $item['delivery_id'] = $val['id'];
                $item['order_id'] = $val['order_id'];
                $item['user_id'] = $val['order']['user_id'];
                $item['contacts'] = $val['contacts'];
                $item['contacts_tel'] = $val['contacts_tel'];
                if ($val['order']['order_producttype'] == 1) {
                    $item['totalPrice'] += $val['order']['order_tailmoney'];
                }
                $item['order_producttype'] = OrderModel::orderProductType($val['order']['order_producttype']);
                $business = (new OrderModel())->getBusiness($item['business_id']);
                $item['business'] = $business['business_name'];

                if ($val['product_id'] == $item['id']) {
                    $product[] = $item;
                }

            }
        }
//        print_r($product);
        $count = count($product);
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $product);
        $this->display();
    }

    //交易完成商品列表
    public function successProduct()
    {

        $search = array();
        //时间
        if ($_GET['time']) {
            $time = $_GET['time'];
            $array_time = explode("—", $time);  //2018-10-27 10:18:59
            $startTime = $array_time[0] . ' 00:00:00';
            $endTime = $array_time[1] . ' 24:00:00';
            $map['deliver_time'] = array('between', "$startTime,$endTime");
            $search['startTime'] = $startTime;
            $search['endTime'] = $endTime;
        }

        //商家
        if ($_GET['business_id']) {
            $map['business_id'] = $_GET['business_id'];
            $search['business_id'] = $_GET['business_id'];
        }

        //一级分类
        if ($_GET['one_category']) {
            $map['topcategory_id'] = $_GET['one_category'];
            $search['topcategory_id'] = $_GET['one_category'];
        }

        //二级分类
        if ($_GET['two_category']) {
            $map['category_id'] = $_GET['two_category'];
            $search['category_id'] = $_GET['two_category'];
        }

        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $map['product_status'] = C('evaluated');
        $delivery = D('delivery')->relation(array('order', 'express', 'user'))->where($map)->order('id desc')->page($p . ',10')->select();
        foreach ($delivery as &$value) {
            $value['order']['snap_items'] = json_decode($value['order']['snap_items'], true);
        }
//        print_r($delivery);
        $product = [];
        foreach ($delivery as &$val) {
            foreach ($val['order']['snap_items'] as &$item) {
                $item['order_no'] = $val['order']['order_no'];
                $item['delivery_id'] = $val['id'];
                $item['order_id'] = $val['order_id'];
                $item['user_id'] = $val['order']['user_id'];
                $item['deliver_time'] = $val['deliver_time'];
                $item['deliver_type'] = $val['deliver_type'];
                $item['express_name'] = $val['express_name'];
                $item['delivery_no'] = $val['delivery_no'];
                $item['contacts'] = $val['contacts'];
                $item['contacts_tel'] = $val['contacts_tel'];
                $business = (new OrderModel())->getBusiness($item['business_id']);
                $item['business'] = $business['business_name'];
                $category = (new OrderModel())->getCategory($item['category_id']);
                if ($category['navcate_pid'] != 0) {
                    $pCategory = M('navcategory')->where('navcate_pid=' . $category['navcate_pid'])->find();
                }
                if ($val['order']['order_producttype'] == 1) {
                    $item['totalPrice'] += $val['order']['order_tailmoney'];
                }
                $value['nickName'] = urldecode($value['nickName']);
                if (!$val['nickName']) {
                    $item['nickName'] = $val['tel'];
                } else {
                    $item['nickName'] = $val['nickName'];
                }
                $item['topCategory_name'] = $category['navcate_name'];
                $item['twoCategory_name'] = $pCategory['navcate_name'];
                $item['order_producttype_name'] = OrderModel::orderProductType($val['order']['order_producttype']);
                if ($val['product_id'] == $item['id']) {
                    $product[] = $item;
                }

            }
        }
//        print_r($product);
        $count = count($product);
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }

        $categoryModel = M('navcategory');
        $topCategory = $categoryModel->where('navcate_pid=0')->select();
        $businessModel = M('business');
        $business = $businessModel->select();
        $this->assign('topCategory', $topCategory);
        $this->assign('business', $business);
        $this->assign('search', $search);
        $this->assign('page', $Page->show());
        $this->assign('list', $product);
        $this->display();
    }


    //导出订单列表数据
    public function exportExcel()
    {

        //时间
        if ($_GET['startTime'] && $_GET['endTime']) {
            $startTime = $_GET['startTime'];
            $endTime = $_GET['endTime'];
            $map['deliver_time'] = array('between', "$startTime,$endTime");
        }

        //商家
        if ($_GET['business_id']) {
            $map['business_id'] = $_GET['business_id'];
        }

        //一级分类
        if ($_GET['one_category']) {
            $map['topcategory_id'] = $_GET['one_category'];
        }

        //二级分类
        if ($_GET['two_category']) {
            $map['category_id'] = $_GET['two_category'];
        }

//        $p = $_GET['p'];
//        if (empty($p)) {
//            $p = 1;
//        }
        $map['product_status'] = C('evaluated');
        $delivery = D('delivery')->relation(array('order', 'express', 'user'))->where($map)->order('id desc')->select();
        foreach ($delivery as &$value) {
            $value['order']['snap_items'] = json_decode($value['order']['snap_items'], true);
        }
//        print_r($delivery);
        $product = [];
        foreach ($delivery as $val) {
            foreach ($val['order']['snap_items'] as &$item) {
                $item['order_no'] = $val['order']['order_no'];
                $item['delivery_id'] = $val['id'];
                $item['order_id'] = $val['order_id'];
                $item['user_id'] = $val['order']['user_id'];
                $item['deliver_time'] = $val['deliver_time'];
                $item['deliver_type'] = $val['deliver_type'];
                $item['express_name'] = $val['express_name'];
                $item['delivery_no'] = $val['delivery_no'];
                $item['contacts'] = $val['contacts'];
                $item['contacts_tel'] = $val['contacts_tel'];
                $business = (new OrderModel())->getBusiness($item['business_id']);
                $item['business'] = $business['business_name'];
                $category = (new OrderModel())->getCategory($item['category_id']);
                if ($category['navcate_pid'] != 0) {
                    $pCategory = M('navcategory')->where('navcate_pid=' . $category['navcate_pid'])->find();
                }
                if ($val['order']['order_producttype'] == 1) {
                    $item['totalPrice'] += $val['order']['order_tailmoney'];
                }
                $item['topCategory_name'] = $category['navcate_name'];
                $item['twoCategory_name'] = $pCategory['navcate_name'];
                $item['order_producttype_name'] = OrderModel::orderProductType($val['order']['order_producttype']);
                if ($val['product_id'] == $item['id']) {
                    $product[] = $item;
                }

            }
        }

        $excelData = array();
        foreach ($product as $k => $val) {
            $excelData[$k][id] = $val['id'];//编号
            $excelData[$k][order_no] = $val['order_no'];//订单编号
            $excelData[$k][name] = $val['name'];//商品名称
            $excelData[$k][business] = $val['business'];//商家名称
            $excelData[$k][topCategory_name] = $val['topCategory_name'];//一级分类
            $excelData[$k][twoCategory_name] = $val['twoCategory_name'];//二级分类
            $excelData[$k][count] = $val['count'];//数量
            $excelData[$k][totalPrice] = $val['totalPrice'];//总价
            $excelData[$k][productvalue] = $val['productvalue'];//属性
            $excelData[$k][contacts] = $val['contacts'];//联系人
            $excelData[$k][contacts_tel] = $val['contacts_tel'];//联系电话
            $excelData[$k][express_name] = $val['express_name'];//快递名称
            $excelData[$k][delivery_no] = $val['delivery_no'];//快递单号
            $excelData[$k][order_producttype_name] = $val['order_producttype_name'];//订单类型
            $excelData[$k][deliver_time] = $val['deliver_time'];//确认收货时间
            $excelData[$k][product_brand] = $val['product_brand'];//品牌

        }
        foreach ($excelData as $field => $v) {
            if ($field == 'id') {
                $headArr[] = '编号';
            }

            if ($field == 'order_no') {
                $headArr[] = '订单号';
            }
            if ($field == 'name') {
                $headArr[] = '商品名称';
            }
            if ($field == 'business') {
                $headArr[] = '商家名称';
            }
            if ($field == 'topCategory_name') {
                $headArr[] = '商品一级分类';
            }
            if ($field == 'twoCategory_name') {
                $headArr[] = '商品二级分类';
            }
            if ($field == 'count') {
                $headArr[] = '商品数量';
            }
            if ($field == 'totalPrice') {
                $headArr[] = '商品总价';
            }
            if ($field == 'productvalue') {
                $headArr[] = '商品属性';
            }
            if ($field == 'contacts') {
                $headArr[] = '商家联系人';
            }
            if ($field == 'contacts_tel') {
                $headArr[] = '商家联系方式';
            }
            if ($field == 'express_name') {
                $headArr[] = '快递公司';
            }
            if ($field == 'delivery_no') {
                $headArr[] = '物流单号';
            }
            if ($field == 'order_producttype_name') {
                $headArr[] = '订单类型';
            }
            if ($field == 'deliver_time') {
                $headArr[] = '确认收货时间';
            }
            if ($field == 'product_brand') {
                $headArr[] = '品牌介绍';
            }
        }
        $filename = '交易成功表';  //生成的Excel文件文件名
        getExcel($filename, $headArr, $excelData);
    }

    //分类二级联动
    public function changeCategory()
    {
        $one_category = $_POST['one_category'];
        $categoryModel = M('navcategory');
        $category = $categoryModel->where("navcate_pid=" . $one_category)->select();
        $str = "<option value=>-请选择- </option>";
        $i = 0;
        foreach ($category as $key => $rs) {
            $str .= "<option value='" . $rs['id'] . "'>" . $rs['navcate_name'] . "</option>";
            $i++;
        }
        if ($i == 0) {
            $str = '<option value=>暂无信息 </option>';
        }

        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $str
        ]);
    }


    //订金订单管理
    public function depositOrderList()
    {
        $orderModel = D('order');
        $p = $_GET['p'];
        if (empty($p)) {
            $p = 1;
        }
        $map['order_producttype'] = C('Deposit_Product');  //定金商品
        $map['order_status'] = C('Unpaid');  //待付款订单
        $order = $orderModel->relation('user')->where($map)->order('id desc')->page($p . ',10')->select();
        $businessModel = M('business');
        foreach ($order as &$value) {
            $value['snap_items'] = json_decode($value['snap_items'], true);
            $value['snap_address'] = json_decode($value['snap_address'], true);
            $value['order_status'] = OrderModel::orderStatus($value['order_status']);
            if ($value['Deposit_type'] == 1) {
                $value['Deposit_type'] = '已支付订金';
            } elseif ($value['Deposit_type'] == 0) {
                $value['Deposit_type'] = '未支付订金';
            }
            //商家名称
            foreach ($value['snap_items'] as &$item) {
                $business = $businessModel->where("id=" . $item['business_id'])->find();
                $value['business_name'] = $business['business_name'];
            }
            if (!$value['nickName']) {
                $value['nickName'] = $value['tel'];
            }

        }
//        print_r($order);
        $count = count($order);
        $Page = getpage($count, 10);
        foreach ($map as $key => $val) {
            $page->parameter .= "$key=" . urlencode($val) . '&';
        }
        $this->assign('page', $Page->show());
        $this->assign('list', $order);
        $this->display();
    }


    //订金订单设为已付款
    public function editOrderStatus()
    {
        $orderID = $_POST['orderID'];
        $orderModel = M('order');
        $orderModel->order_status = C('Paid');
        $order = $orderModel->where("id=$orderID")->save();
        return $order;
    }


    //修改尾款金额
    public function updateTailMoney()
    {
        if (IS_POST) {
            $orderModel = M('order');
            $id = $_POST['id'];
            $order_tailmoney = $_POST['order_tailmoney'];
            $orderModel->order_tailmoney = $order_tailmoney;
            $order = $orderModel->where("id=$id")->save();
            if ($order) {
                $this->success('编辑成功！', U('Order/depositOrderList'));
            }

        } else {
            $this->display();
        }
    }


}

?>
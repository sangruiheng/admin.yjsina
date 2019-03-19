<?php
namespace Manage\Controller;
use Think\Controller;
class NavCategoryController extends CommonController {

    public function navCategoryList(){

        $this->getDlist('navcategory',$_GET['keyWord'],"navcate_pid=0");
    }

    public function SecondLevel(){
        $navcate_pid = $_GET['navcate_pid'];
        $category = D('navcategory')->find($navcate_pid);
        $this->assign('category',$category);
        $this->getDlist('navcategory',$_GET['keyWord'],"navcate_pid=$navcate_pid");
    }

    //新增一级分类
    public function addCate(){
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) {
                $sql->id = NULL;
                $sql->navcate_img = substr($request['hid'][0], 16);
                $result = $sql->add();
            } else {
                if ($request['hid']) {  //判断是否上传图片
                    $navcategory = D('navcategory')->where("id=$id")->find();
                    $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $navcategory['navcate_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->navcate_img = substr($request['hid'][0], 16);
                }
                $result = $sql->save();
            }
        }
        if ($result) {
            $this->success('编辑成功！', U($controller . '/' . $backUrl));
        } else {
            $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
        }
    }



    public function addSecond()
    {
        $backUrl = $_GET['backUrl'];
        $table = $_GET['table'];
        $controller = $_GET['controller'];
        $id = $_POST['id'];
        $sql = D($table);
        $request = I('post.');
        if ($sql->create()) {
            if (empty($id)) {
                $sql->id = NULL;
                $sql->navcate_img = substr($request['hid'][0], 16);
                $result = $sql->add();
            } else {
                if ($request['hid']) {  //判断是否上传图片
                    $navcategory = D('navcategory')->where("id=$id")->find();
                    $file = ('Uploads/Manage/' . $navcategory['navcate_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $sql->navcate_img = substr($request['hid'][0], 16);
                    $sql->navcate_name = $request['navcate_name'];
                }
                $result = $sql->save();
            }
        }
        if ($result) {
            $this->success('编辑成功！', U($controller . '/' . $backUrl . '/navcate_pid/' . $_POST['navcate_pid']));
        } else {
            $this->error($sql->getError(), $jumpUrl = '', $ajax = true);
        }
    }


    //删除一级分类
    public function delNavCate()
    {
        $table = $_POST['table'];
        $ids = $_POST['delID'];
        $sql = M($table);
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        if (strpos($ids, ',') == false) {  //没有逗号  选择一条
            $pcate = D('navcategory')->where("id=$ids")->find();
            $pfile = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $pcate["navcate_img"]);
            if (file_exists($pfile)) {
                @unlink($pfile);
            }
            $Result = $sql->delete($ids);     //删除当前表数据
            $cate = D('navcategory')->where("navcate_pid=$ids")->select();
            //判断是否有二级
            if ($cate) {
                for ($i=0; $i<count($cate); $i++){
                    $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $cate[$i]['navcate_img']);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
                $res = D('navcategory')->where("navcate_pid=$ids")->delete();
            }
        } else {   //选择多条
            $arr_id = explode(",", $ids);
            for ($i = 0; $i < count($arr_id); $i++) {
                $pcate = D('navcategory')->where("id=$arr_id[$i]")->find();
                $pfile = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $pcate["navcate_img"]);
                if (file_exists($pfile)) {
                    @unlink($pfile);
                }
                $Result = $sql->delete($arr_id[$i]);     //删除当前表数据
                $cate = D('navcategory')->where("navcate_pid=$arr_id[$i]")->find();
                //判断是否有二级
                if ($cate) {
                    $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $cate["navcate_img"]);
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                    $res = D('navcategory')->where("navcate_pid=$arr_id[$i]")->delete();
                }
            }

        }
    }

    //删除二级分类
    public function delCategoryData(){
        $table = $_POST['table'];
        $ids = $_POST['delID'];
        $sql = M($table);
        $productModel = M('product');
        $productImageModel = M('productimage');
        if (strlen($ids) > 0) {
            $ids = substr($ids, 0, strlen($ids) - 1);
        }
        $arr_id = explode(",",$ids);
//
//        //删除商品图片
//        $map['category'] = array('in', $arr_id);
//        $product = $productModel->where($map)->select();
//        foreach ($product as $value){
//            $productImage = $productImageModel->where("product_id=".$value['id'])->select();
//            foreach($productImage as $item){
//                $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $item["productimage_url"]);
//                if (file_exists($file)) {
//                    @unlink($file);
//                }
//            }
//            //删除商品图片表
//            $productImageModel->where("product_id=".$value['id'])->delete();
//            //删除商品表
//            $productModel->where("id=".$value['id'])->delete();
//        }

        //删除分类图片
//        for ($i=0;$i<count($arr_id);$i++){
//            $navCategory = M('navcategory')->where("id=".$arr_id[$i])->find();
//            $file = ($_SERVER["DOCUMENT_ROOT"] . 'Uploads/Manage/' . $navCategory["navcate_img"]);
//            if (file_exists($file)) {
//                @unlink($file);
//            }
//        }
        //删除分类表
        $Result = $sql->delete($ids);     //删除当前表数据
        $this->auth_save_group($table,$ids);
    }
}
?>
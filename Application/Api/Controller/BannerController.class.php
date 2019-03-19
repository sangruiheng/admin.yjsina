<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\BannerException;
use Think\Controller;

class BannerController extends CommonController
{

    //获取banner
    public function getBanner(){
        $result = $this->allBanner(1);
        if (!$result) {
            $result = (new BannerException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取广告banner
    public function getCommercialBanner(){
        $result = $this->allBanner(2);
        if (!$result) {
            $result = (new BannerException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取登陆banner
    public function getLoginBanner(){
        $result = $this->allBanner(3);
        if (!$result) {
            $result = (new BannerException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //家电广告位图片
    public function getAdvertising(){
        $result = $this->allBanner(4);
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //首页服务头图
    public function getServiceImg(){
        $result = $this->allBanner(5);
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    public function allBanner($status){
        $result = D('banner')->where("banner_type=$status")->field('banner_addTime',true)->order("sort asc")->select();
        foreach ($result as &$value){
            $value['banner_img'] = C('img_prefix') . $value['banner_img'];
        }
        return $result;
    }



}
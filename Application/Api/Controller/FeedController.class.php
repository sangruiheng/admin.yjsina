<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\SuccessException;
use Api\Exception\UserException;
use Api\Model\FeedModel;
use Api\Service\Token;
use Api\Validate\FeedNew;

class FeedController extends CommonController
{

    protected $uid;

    public function __construct()
    {
        //根据token来获取uid
        $this->uid = Token::getCurrentUid();
    }

    //判断是否有token对应的用户
    public function is_user(){
        $user = D('user')->where("id=$this->uid")->find();
        return $user;
    }


    //新增反馈意见
    public function addFeed(){
        $user = $this->is_user();
       (new FeedNew())->goCheck();
        if(!$user){
            $this->ajaxReturn((new UserException())->getException());
        }else{
            $result = (new FeedModel())->addFeed($this->uid);
            if($result){
                $this->ajaxReturn((new SuccessException([
                    'msg' => '新增反馈意见成功!'
                ]))->getException());
            }
        }

   }







}
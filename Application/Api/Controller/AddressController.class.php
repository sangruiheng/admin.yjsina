<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\AddressException;
use Api\Exception\SuccessException;
use Api\Exception\UserException;
use Api\Model\AddressModel;
use Api\Service\Token;
use Api\Validate\AddressNew;
use Api\Validate\IDMustBePostiveInt;
use Think\Controller;


class AddressController extends CommonController
{


    protected $uid;

    //根据token来获取uid
    //根据uid来查找用户数据，判断用户是否存在 不存在则抛出异常
    //用户存在，获取用户从客户端提交的信息
    //根据用户地址信息是否存在，从而判断是添加地址还是更新地址

    function __construct()
    {
        //根据token来获取uid
        $this->uid = Token::getCurrentUid();
        $this->is_user();
    }


    //判断是否有token对应的用户
    public function is_user()
    {
        $user = D('user')->where("id=$this->uid")->find();
        if (!$user) {
            $this->ajaxReturn((new UserException())->getException());
        }
        return $user;
    }

    //获取用户地址
    public function getUserAddress()
    {


        $result = D('address')->where("user_id=$this->uid")->order('id desc')->select();
        if (!$result) {
            $result = (new AddressException([
                'code' => 70001,
                'msg' => '暂无当前用户地址'
            ]))->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //删除地址
    public function delUserAddress()
    {
        $id = $_POST['id'];
        (new IDMustBePostiveInt())->goCheck();
        $user = D('address')->where("id=$id and user_id=$this->uid")->find();
        if ($user) {
            $result = D('address')->where("id=$id")->delete();
            $this->ajaxReturn((new SuccessException([
                'msg' => '删除地址成功!'
            ]))->getException());
        } else {
            $result = (new UserException([
                'code' => 70002,
                'msg' => '地址不存在或用户不存在,请检查输入参数'
            ]))->getException();
            $this->ajaxReturn($result);
        }
    }


    //新增地址
    public function createUserAddress()
    {
        (new AddressNew())->goCheck();
            $result = (new AddressModel())->addAddress($this->uid);
            if ($result) {
                $this->ajaxReturn((new SuccessException([
                    'msg' => '新增地址成功!'
                ]))->getException());
            }
    }

    //修改前
    public function beforUpdateAddress()
    {
        $id = $_POST['id'];
        (new IDMustBePostiveInt())->goCheck();
        $result = D('address')->where("id=$id and user_id=$this->uid")->find();
        if (!$result) {
            $this->ajaxReturn((new AddressException([
                'code' => 70002,
                'msg' => '地址不存在或用户不存在,请检查输入参数'
            ]))->getException());
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }

    //修改地址
    public function updateUserAddress()
    {
        (new IDMustBePostiveInt())->goCheck();
            $result = (new AddressModel())->updateAddress($_POST['id'],$this->uid, $_POST['name'], $_POST['mobile'], $_POST['city'], $_POST['detail'], $_POST['is_default']);
        if ($result == 0) {
            $this->ajaxReturn((new SuccessException([
                'code' => 70003,
                'msg' => '当前地址未做任何修改!'
            ]))->getException());
        }
        $this->ajaxReturn((new SuccessException([
            'msg' => '修改地址成功!'
        ]))->getException());
    }


}
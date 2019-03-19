<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Api\Service\UserToken;
use Think\Model\RelationModel;

class AddressModel extends RelationModel
{
    protected $_link = array();

    public function addAddress($uid)
    {
        $Address = D("address");
        //判断是否设置默认地址
        if ($_POST['is_default'] == 1) {
            $map['user_id'] = $uid;
            $map['is_default'] = 1;
            $res = $Address->where($map)->find();
            if ($res) {
                $data['is_default'] = 0;
                $Address->where('id=' . $res['id'])->save($data); // 根据条件更新记录
            }
        }
        $data['address_name'] = $_POST['name'];
        $data['address_mobile'] = $_POST['mobile'];
        $data['address_city'] = $_POST['city'];
        $data['address_detail'] = $_POST['detail'];
        $data['user_id'] = $uid;
        $data['is_default'] = $_POST['is_default'];
        $result = $Address->add($data);
        return $result;
    }


    public function updateAddress($id, $uid, $name, $mobile, $city, $detail, $is_default)
    {

        //修改地址时  先判断是否设置默认地址 如果设置取消当前用户的默认地址 如果没有修改地址


        $Address = D("address");
        //判断是否设置默认地址
        if ($is_default == 1) {
            $map['user_id'] = $uid;
            $map['is_default'] = 1;
            $res = $Address->where($map)->find();
            if ($res) {
                $data['is_default'] = 0;
                $Address->where('id=' . $res['id'])->save($data); // 根据条件更新记录
            }
        }
        $data['address_name'] = $name;
        $data['address_mobile'] = $mobile;
        $data['address_city'] = $city;
        $data['address_detail'] = $detail;
        $data['is_default'] = $is_default;
        $result = $Address->where("id=$id")->save($data);
        return $result;
    }


}

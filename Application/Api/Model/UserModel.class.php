<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Api\Controller\CommonController;
use Api\Exception\UserException;
use Api\Service\UserToken;
use Think\Model\RelationModel;

class UserModel extends RelationModel
{
    protected $_link = array(
        'memberdetail' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'memberdetail',//要关联的表名
            'foreign_key' => 'user_id', //外键的字段名称
            'mapping_fields' => 'mambercard_id,membercard_endtime',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'productattr',   //多表关联  关联第三个表的名称
        ),
    );



    public function getByOpenID($openid)
    {
        $result = M('user')->where("openid='$openid'")->find();
        return $result;
    }

    //增加邀请人积分
    public function addShareBounds($shareUID){
        $result = self::where("id=$shareUID")->setInc('bounds', 50);  //增加积分
        if($result){
            (new CommonController())->boundsDetail($shareUID, 50, '好友邀请', C('Up_Bounds'));
        }
        return $result;
    }
    
    public function updateLastTime($uid){
        $user = M('user');
        $user->lastTime =  date("Y-m-d H:i:s",time());
        $result = $user->where("id=$uid")->save();
        return $result;
    }


    //验证微信绑定手机
    public function BindTel($uid, $bind_tel){
        $userModel = M('user');
        $user = $userModel->where("tel=$bind_tel")->find();
        if($user){
            $result = (new UserException([
                'msg' => "手机号已经注册或者已经绑定",
                'code' => 90002
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $userModel->tel = $bind_tel;
        $result = $userModel->where("id=$uid")->save();
        if(!$result){
                $res = (new UserException([
                    'msg' => "绑定失败",
                    'code' => 90003
                ]))->getException();
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                die; //抛出异常
        }
        return true;
    }

    //验证是否绑定
//    public function is_Bind($bind_tel){
//        $userModel = M('user');
//        $user = $userModel->where("tel=$bind_tel")->find();
//        return $user;
//    }


    public function randomKeys($length)
    {
        $key='';
        $pattern='1234567890';
        for($i=0;$i<$length;++$i) {
            $key .= $pattern{mt_rand(0,9)};    // 生成php随机数
        }
        return $key;
    }


    public static function getUserMember($status){
        switch ($status){
            case 1:
                return "金牌会员";
                break;
            case 2:
                return "银牌会员";
                break;
            case 3:
                return "铜牌会员";
                break;
            default:
                return false;
        }
    }


    public static function login($params)
    {
        $user = D('user')->where('tel=' . $params['tel'])->find();
        if ($user) {
            if ($user['password'] == sha1($params['password'])) {
//              //登录密码正确的情况
                $tk = new UserToken();
                $Token = $tk->getCache($user);
                return $returnData = [
                    'code' => 200,
                    'msg' => '登陆成功',
                    'Token' => $Token
                ];
            } else {
                //登录密码错误
                return $returnData = [
                    'errorCode' => 400,
                    'msg' => '登陆密码错误'
                ];
            }
        } else {
            //用户不存在的情况
            return $returnData = [
                'errorCode' => 400,
                'msg' => '用户不存在'
            ];
        }

    }


    public function updatePay($paypwd, $confirm_paypwd, $uid)
    {
        if ($paypwd === $confirm_paypwd) {
            $data['paypwd'] = sha1($paypwd);
            $result = self::where("id=$uid")->save($data); // 根据条件更新记录
            return $result;
        }else{
            $result = (new UserException([
                'msg' => "两次输入密码不一致",
                'errorCode' => "90006"
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
    }


    public function updatePwd($pwd, $confirm_pwd, $ord_pwd, $uid)
    {
        //判断密码和确认密码是否一致
        //判断用户输入的原密码是否正确 是否重复
        $ord_pwd = sha1($ord_pwd);
        if ($pwd === $confirm_pwd) {
            $user = self::where("password='$ord_pwd'")->find();
            if (!$user) {
                $result = (new UserException([
                    'msg' => "输入的原密码不正确",
                    'errorCode' => "90002"
                ]))->getException();
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                die; //抛出异常
            }
            if ($user['password'] == sha1($pwd)) {
                $result = (new UserException([
                    'msg' => "输入的新密码与原密码相同",
                    'errorCode' => "90003"
                ]))->getException();
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                die; //抛出异常
            }
            $data['password'] = sha1($pwd);
            $result = self::where("id=$uid")->save($data); // 根据条件更新记录
            return $result;
        } else {
            $result = (new UserException([
                'msg' => "新密码和确认密码不一致",
                'errorCode' => "90001"
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
    }


    /**
     * 将字符串参数变为数组
     * @param $query
     * @return array array (size=10)
    'm' => string 'content' (length=7)
    'c' => string 'index' (length=5)
    'a' => string 'lists' (length=5)
    'catid' => string '6' (length=1)
    'area' => string '0' (length=1)
    'author' => string '0' (length=1)
    'h' => string '0' (length=1)
    'region' => string '0' (length=1)
    's' => string '1' (length=1)
    'page' => string '1' (length=1)
     */
    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
    /**
     * 将参数变为字符串
     * @param $array_query
     * @return string string 'm=content&c=index&a=lists&catid=6&area=0&author=0&h=0®ion=0&s=1&page=1' (length=73)
     */
    function getUrlQuery($array_query)
    {
        $tmp = array();
        foreach($array_query as $k=>$param)
        {
            $tmp[] = $k.'='.$param;
        }
        $params = implode('&',$tmp);
        return $params;
    }


}

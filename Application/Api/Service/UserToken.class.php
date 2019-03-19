<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-20
 * Time: 下午3:50
 */

namespace Api\Service;


use Api\Exception\CacheException;
use Api\Model\UserModel;
use Api\Controller\CommonController;


header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods:POST,GET");
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

class UserToken extends Token
{


    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;
    protected $shareUID;
    protected $login;
    protected $proId;

    function __construct()
    {
//        $this->code = $code;
        $this->wxAppID = C('APPID');
        $this->wxAppSecret = C('AppSecret');
//        $this->shareUID = $shareUID;
//        $this->login = $login;
//        $this->proId = $proId;
//        $this->wxLoginUrl = sprintf(config('wx.login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);
    }



    //获取code
    public function getCode($shareUID, $login, $proId){    //授权登录

        $redirect_uri = urlencode('http://admin.yjsina.com/api/user/getuserinfo?shareUID='.$shareUID.'&login='.$login.'&proId='.$proId);
        echo $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->wxAppID."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
        //授权后重定向的回调链接地址，请使用urlEncode对链接进行处理
        header('location:'.$url);
        //以下步骤走getUserInfo
        //2.获取到网页授权的access_token
        //3.拉取用户的open_id

    }



    /*
     * 拿到openid
     * 数据库里看一下，openid是否存在
     * 如果存在则不处理，如果不存在则新增一条user记录
     * 生成令牌 准备缓存数据 写入缓存(加快访问速度)
     * (缓存：用户通过携带令牌 找到找到一系列和他相关的变量      key：令牌   value：wxResult uid scope权限)
     * 把令牌返回到客户端里去
     * */
    //获取openid  用户信息
    public function  getUserInfo(){
        $this->shareUID = $_GET['shareUID'];
        $this->login = $_GET['login'];
        $this->proId = $_GET['proId'];
        $code = $_GET["code"];
        $access_token_uri = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->wxAppID."&secret=".$this->wxAppSecret."&code=".$code."&grant_type=authorization_code";
        $arr = $this->http_curl($access_token_uri);
        $access_token = $arr['access_token'];
        $openid = $arr['openid'];
        //获取用户信息
        $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN ";
        $userinfo = $this->http_curl($userinfo_url);

        $openid=$userinfo['openid'];
        return $this->wxGetCache($userinfo);

    }



    //微信登陆 准备缓存数据
    public function wxGetCache($wxResult){
        $openid = $wxResult['openid'];
        $user = (new UserModel())->getByOpenID($openid);
        if ($user) { //如果存在 取出这个uid
            $uid = $user['id'];
            $fristLogin = 0;  //老用户
            (new UserModel())->updateLastTime($uid);
        } else { //不存在插入新用户
            $user = $this->wxAddUser($wxResult);
            $uid = $user['uid'];
            $fristLogin = $user['fristLogin'];
        }
        //准备缓存数据value（uid 个人信息）
        $cachedVaule = $this->prepareCaChedVaule($wxResult, $uid);   //缓存数据
        //生成token  写入缓存
        $token = $this->saveCache($cachedVaule);
        //把令牌返回到客户端里去
        return [
            'token' => $token,
            'fristLogin' => $fristLogin,
            'login' => $this->login,
            'proId' => $this->proId,
        ];

    }
    

    //手机号登录 准备缓存数据
    public function getCache($tel){
        $user = M('user')->where("tel=$tel")->find();
        if($user){
            $uid = $user['id'];
            $fristLogin = 0;  //老用户
            (new UserModel())->updateLastTime($uid);
        }else{
            $user = $this->addUser($tel);
            $uid = $user['uid'];
            $fristLogin = $user['fristLogin'];
        }
        //准备缓存数据value（uid 个人信息）
        $cachedVaule = $this->prepareCaChedVaule($user, $uid);   //缓存数据
        //生成token  写入缓存
        $token = $this->saveCache($cachedVaule);
        //把令牌返回到客户端里去
        return [
            'token' => $token,
            'fristLogin' => $fristLogin,
        ];

    }

    //生成令牌  写入缓存
    public function saveCache($cachedVaule){
        //生成Token  公共的 放在基类中 key
        $key = self::generateToken();  //令牌 key
        $value = json_encode($cachedVaule);   //转换为字符串
        $expire_in = C('token_expire_in');  //获取过期时间
        $request = S($key, $value, $expire_in);    //写入缓存 （key  value  expire_in）  默认写入文件 runtime/temp
        if (!$request) {
            $result = (new CacheException())->getException();
            echo json_encode($result,JSON_UNESCAPED_UNICODE);  die; //抛出异常
        }
        return $key;
    }


    //准备缓存中的value数据
    public function prepareCaChedVaule($wxResult, $uid){
        $caChedVaule = $wxResult;
        $caChedVaule['uid'] = $uid;
//        $caChedVaule['scope'] = ScopeEnum::User;
        return $caChedVaule;
    }


    //不存在添加一条用户  微信
    public function wxAddUser($wxResult){
        $User = M('user');
        $data['openid'] = $wxResult['openid'];

        $data['nickName'] = urlencode($wxResult['nickname']);
        $data['avatarUrl'] = $wxResult['headimgurl'];
        $data['city'] = $wxResult['city'];
        $data['gender'] = $wxResult['sex'];
        $data['province'] = $wxResult['province'];
        $data['country'] = $wxResult['country'];
        $data['lastTime'] = date('Y-m-d H:i:s',time());
        $data['bounds'] = 50;
        $uid = $User->add($data);
        //添加积分明细
        if($uid){
            (new CommonController())->boundsDetail($uid, 50, '微信首次登陆', C('Up_Bounds'));
        }
        $fristLogin = 1;  //新用户
        //注册成功 增加邀请人积分
//        (new CommonController())->printLog($this->shareUID);

        if($uid && $this->shareUID){
            (new UserModel())->addShareBounds($this->shareUID);
        }
        return [
            'uid' => $uid,
            'fristLogin' => $fristLogin
        ];
    }

    //手机号登录插入信息
    public function addUser($tel){
//        $_SERVER["DOCUMENT_ROOT"] .
        $User = M('user');
        $data['tel'] = $tel;
        $data['avatarUrl'] = 'Uploads/Home/img/touxiang.jpeg';
        $data['lastTime'] = date('Y-m-d H:i:s',time());
        $data['bounds'] = 50;
        $uid = $User->add($data);
        //添加积分明细
        if($uid){
            (new CommonController())->boundsDetail($uid, 50, '手机号首次登陆', C('Up_Bounds'));
        }
        $fristLogin = 1;  //新用户
        //注册成功 增加邀请人积分
        if($uid && $this->shareUID){
            (new UserModel())->addShareBounds($this->shareUID);
        }
        return [
            'uid' => $uid,
            'fristLogin' => $fristLogin
        ];
    }

    






}
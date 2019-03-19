<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-20
 * Time: 下午3:49
 */

namespace Api\Service;


use Api\Exception\TokenException;
use Api\Exception\UserException;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods:POST,GET");
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');


class Token
{


    /**
     * @param string $url get请求地址
     * @param int $httpCode 返回状态码
     * @return mixed
     */
    public function curl_get($url, &$httpCode = 0)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //不做证书校验，部署在linux环境下请改为true
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $file_contents = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $file_contents;

    }

    public static function  getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }

    //生成TOken
    public static function generateToken()
    {

        //32个字符组成一组随机字符串
        $randChars = self::getRandChar(32);  //通用方法
        //用三组字符串，进行md5加密
        $timestarp = $_SERVER['REQUEST_TIME_FLOAT'];  //当前时间戳
        //salt  盐
//        $salt = config('secure.token_salt');

        return md5($randChars . $timestarp );
    }





    //获取缓存中的某个变量
    public static function getCurrentTokenVar($key)
    {
        //获取用户token
//        $token = header('token');   //约定token必须在header头中传递
        $token = $_POST['token'];
        //从缓存读取value值
        $vars = S($token);
        if (!$vars) {
            $result = (new TokenException())->getException();
            echo json_encode($result,JSON_UNESCAPED_UNICODE);  die; //抛出异常

        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);  //字符串转换为数组  好处理
            }
            if (array_key_exists($key, $vars)) {   //根据指定key获取相应参数
                return $vars[$key];
            } else {
                $result = (new TokenException([
                    'msg' => "尝试获取的token变量并不存在"
                ]))->getException();
                echo json_encode($result,JSON_UNESCAPED_UNICODE);  die; //抛出异常
            }
        }
    }


    //获取当前用户的id号
    public static function getCurrentUid()
    {
        $uid = self::getCurrentTokenVar('uid');
        return  $uid;
    }




    public function http_curl($url, $type = 'get', $res = 'json', $arr = '')
    {   //抓取
        //获取imooc
        //1.初始化curl
        $ch = curl_init();
        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        //3.采集
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if ($res == 'json') {
            return json_decode($output, true);
        }
    }

    //被检测的uid和当前令牌的uid是否一致
    public static function isValidOperate($checkUID){
        if(!$checkUID){
            //异常  必须传入uid
            $result = (new UserException([
                'msg' => '必须传入uid'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $currentOperateUID = self::getCurrentUid();
        if($checkUID == $currentOperateUID){
            return true;
        }
        return false;
    }



}
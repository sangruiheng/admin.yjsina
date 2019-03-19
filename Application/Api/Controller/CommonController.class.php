<?php

namespace Api\Controller;

use Api\Model\UserModel;
use Think\Controller;

Vendor('PHPMailer.src.PHPMailer');
Vendor('PHPMailer.src.SMTP');

class CommonController extends Controller
{
    //请求接口验证
    //域名验证

    public function _initialize()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods:POST,GET");
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }

    /*字符串截断函数+省略号*/
    function subtext($text, $length)
    {
        if (mb_strlen($text, 'utf8') > $length)
            return mb_substr($text, 0, $length, 'utf8') . '...';
        return $text;
    }

    //统一返回res
//	public function return_ajax($code=400;$msg='',$data=''){
//		$this->ajaxReturn(array('code'=>$code,'msg'=>$msg,'data'=>$data));
//	}

    public function return_ajax($code = 400, $msg = '', $data = array())
    {
        $this->ajaxReturn(array('code' => $code, 'msg' => $msg, 'data' => $data));
    }

    //增加积分明细
    public function boundsDetail($user_id, $bounds, $bounds_detail, $type)
    {
        $boundsDetailModel = M('boundsdetail');
        $time = date('Y-m-d H:i:s', time());
        $boundsDetailModel->user_id = $user_id;
        $boundsDetailModel->make_bounds = $bounds;
        $boundsDetailModel->bounds_time = $time;
        $boundsDetailModel->bounds_detail = $bounds_detail;
        $boundsDetailModel->bounds_type = $type; //增加还是减少
        $result = $boundsDetailModel->add();
        if (!$result) {
            $this->ajaxReturn([
                'code' => 400,
                'msg' => '积分明细添加失败',
            ]);
        }
        return true;
    }

    //随机数
    public function createNonce($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    /**
     * 打印数据
     * @param  string $txt 日志记录
     * @param  string $file 日志目录
     * @return
     */
    public function printLog($txt = "", $file = "ceshi.log")
    {
        $myfile = fopen($file, "a+");
        $StringTxt = "[" . date("Y-m-d H:i:s") . "]" . var_export($txt, true) . "\n";
        fwrite($myfile, $StringTxt);
        fclose($myfile);

    }

    //获取会员
    public function getUserMember($uid)
    {
        $memberDetailModel = D('memberdetail');
        $memberDetail = $memberDetailModel->relation('membercard')->where("user_id=$uid")->find();
        if ($memberDetail) {
            return $memberDetail;
        } else {
            return false;
        }
    }

    //登陆验证
    public function validateLogin($telPhone, $code){
        if (empty($telPhone)) $this->return_ajax(401, '请输入手机号');
        if (empty($code)) $this->return_ajax(402, '请输入验证码');
        $where['tel'] = array('eq', $telPhone);
        $sms_info = M('sms')->where($where)->field('id,sms_code,sms_sendTime')->find();
        if (empty($sms_info)) $this->return_ajax(403, '请先发验证码');
        if ($sms_info['sms_code'] != $code) $this->return_ajax(404, '验证码不正确');
        if (time() > ($sms_info['sms_sendTime'] + 60)) $this->return_ajax(405, '验证码已过期');
        return true;
    }

    //验证手机号验证码 公共方法
    public function is_TelSms($telPhone,$code)
    {
        if (empty($telPhone)) $this->return_ajax(401, '请输入手机号');
        $map['tel'] = array('eq', $telPhone);
        $sms_info = M('sms')->where($map)->field('id,sms_sendTime')->find();
//        $code = (new UserModel())->randomKeys(4);

        if (!empty($sms_info)) {
            if (time() < ($sms_info['sms_sendTime'] + 60)) $this->return_ajax(402, '60秒之内不能重新发送');
            $data['id'] = $sms_info['id'];
            $data['sms_code'] = $code;
            $data['sms_sendTime'] = time();
            $rs = M('sms')->save($data);
        } else {
            $data['sms_code'] = $code;
            $data['sms_sendTime'] = time();
            $data['tel'] = $telPhone;
            $rs = M('sms')->add($data);
        }
        return $rs;
    }

    //短信验证码
    public function sendSms($content, $phone)
    {
        $sms_url = "http://sms.bamikeji.com:8890/mtPort/mt/normal/send?uid=" . C('SMS_UID') . "&passwd=" . C('SMS_PWD') . "&content=" . $content . "&phonelist=" . $phone;
        $result = $this->http_curl($sms_url);
        return $result;
    }

    //发送邮件
    public function sendMail($mail_addAddress, $mail_Body)
    {
        // 实例化PHPMailer核心类
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = 1;
        // 使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        // smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
        // 链接qq域名邮箱的服务器地址
        $mail->Host = 'smtp.qq.com';
        // 设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        // 设置ssl连接smtp服务器的远程服务器端口号
        $mail->Port = 465;
        // 设置发送的邮件的编码
        $mail->CharSet = 'UTF-8';
        // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = C('MAIL_FromName');
        // smtp登录的账号 QQ邮箱即可
        $mail->Username = C('STMP_NAME');
        // smtp登录的密码 使用生成的授权码
        $mail->Password = C('STMP_PWD');
        // 设置发件人邮箱地址 同登录账号
        $mail->From = C('STMP_NAME');
        // 邮件正文是否为html编码 注意此处是一个方法
        $mail->isHTML(true);
        // 设置收件人邮箱地址
        $mail->addAddress($mail_addAddress);
        // 添加多个收件人 则多次调用方法即可
        //$mail->addAddress('87654321@163.com');
        // 添加该邮件的主题
        $mail->Subject = C('MAIL_Subject');
        // 添加邮件正文
        $mail->Body = $mail_Body;
        // 为该邮件添加附件
        //$mail->addAttachment('./example.pdf');
        // 发送邮件 返回状态
        $status = $mail->send();

        return $status;

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

}
<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;


use Api\Controller\CommonController;
use Api\Exception\OrderException;

Vendor('WxPayPubHelper.WxPayPubHelper');

class MemberH5Pay
{
    protected $uid;
    protected $member_no;
    protected $memberDetail_id;


    public function MemberH5Pay($uid, $mambercard_id)
    {
//        $fp = fopen('test5.txt', 'a+b');
//        fwrite($fp, var_export('1111', true));
//        fclose($fp);


        //下单  添加订单号 下单时间 会员卡类型
        //续费用户 查询表中有无此用户 判断当前用户结束时间 更新字段
        //支付成功 设置uid 结束时间
        $this->uid = $uid;
        $memberDetailModel = M('memberdetail');
        $memberCardModel = M('membercard');
        $this->member_no = (new CommonController())->createNonce(32);
        $memberDetail = $memberDetailModel->where("member_no='$this->member_no'")->find();
        if ($memberDetail) {
            $this->member_no = (new CommonController())->createNonce(32);
        }
        $member_time = date('Y-m-d H:i:s', time());

        $memberDetail = $memberDetailModel->where("user_id=$this->uid")->find();
        if ($memberDetail) {   //已过期用户
            //会员已过期 重新替换用户购买信息
            $memberDetailModel->member_no = $this->member_no;
            $memberDetailModel->mambercard_id = $mambercard_id;
            $memberDetailModel->member_time = $member_time;
            $memberDetailModel->where("id=" . $memberDetail['id'])->save();
            $memberDetail_id = $memberDetail['id'];
        } else {
            $memberDetailModel->member_no = $this->member_no;
            $memberDetailModel->mambercard_id = $mambercard_id;
            $memberDetailModel->member_time = $member_time;
            $memberDetail_id = $memberDetailModel->add();
        }


        if (!$memberDetail_id) {
            $result = (new OrderException([
                'code' => 10088,
                'msg' => '添加购买会员记录失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->memberDetail_id = $memberDetail_id;
        $memberCard = $memberCardModel->where("id=$mambercard_id")->find();
        return $this->makeWxH5Order($memberCard['membercard_price']);


    }

    //统一下单
    private function makeWxH5Order($orderPrice)
    {
        $attach = $this->memberDetail_id . ',' . $this->uid;
        $unifiedOrder = new \UnifiedOrder_pub();
        $dataxml['appid'] = C('APPID');
        $dataxml['mch_id'] = C('MCHID');
        $dataxml['nonce_str'] = (new CommonController())->createNonce(32);
        $dataxml['total_fee'] = $orderPrice * 100;
        $dataxml['attach'] = $attach;
        $dataxml['body'] = 'h5支付';
        $dataxml['out_trade_no'] = $this->member_no;
//    	$dataxml['out_trade_no'] = time();
        $dataxml['spbill_create_ip'] = $this->get_client_ip();
        $dataxml['notify_url'] = C('JSAPI_Member_URL');
        $dataxml['trade_type'] = 'MWEB';
        $dataxml['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "admin.yjsina.com","wap_name": "家居"}}';
        $dataxml['sign'] = $unifiedOrder->getSign($dataxml);
        //将数组转成xml
        $xml = $unifiedOrder->arrayToXml($dataxml);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $execUrl = $unifiedOrder->postXmlCurl($xml, $url);
        //将xml转成数组
        $xml_array = $unifiedOrder->xmlToArray($execUrl);
//    	print_r($xml_array);exit;
        if ($xml_array['return_code'] == 'SUCCESS') {
            if ($xml_array['result_code'] == 'SUCCESS') {//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
                $redirect_url = urlencode('http://www.yjsina.com/Vip/View.html');
                $url = $xml_array['mweb_url'] . '&redirect_url=' . $redirect_url;
//    			echo "<script>location.href='".$url."';</script>";exit;
                return $url;
            }
            if ($xml_array['return_code'] == 'FAIL') {//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
                echo $xml_array['return_msg'];
            }
        }
    }


    private function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置 header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post 提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行 curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo "curl 出错，错误码:$error" . "<br>";
        }
    }


    private function get_client_ip($type = 0)
    {
        $type = $type ? 1 : 0;
        $ip = 'unknown';
        if ($ip !== 'unknown') return $ip[$type];
        if ($_SERVER['HTTP_X_REAL_IP']) {//nginx 代理模式下，获取客户端真实 IP
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的 ip
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的 ip 地址
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP 地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


}
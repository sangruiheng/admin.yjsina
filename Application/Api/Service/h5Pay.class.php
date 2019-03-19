<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;


use Api\Exception\OrderException;
use Api\Exception\AddressException;
use Api\Exception\ParameterException;
use Api\Exception\ProductException;
use Api\Model\OrderModel;
Vendor('WxPayPubHelper.WxPayPubHelper');
class h5Pay
{
    protected $orderID;
    protected $orderNo;
    protected $addressID;
    protected $order_bounds;
	
    function __construct($orderID,$addressID,$order_bounds)
    {
        if (!$orderID) {
            $result = (new OrderException([
                'code' => 11002,
                'msg' => '订单号不能为空'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->orderID = $orderID;
        if(!$addressID){
            $result = (new AddressException([
                'code' => 70004,
                'msg' => '地址id不能为空'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->addressID = $addressID;
        $this->order_bounds = $order_bounds;
    }


    public function h5Pay()
    {
//                            $fp = fopen('test5.txt', 'a+b');
//                    fwrite($fp, var_export('1111', true));
//                    fclose($fp);

        //参数检测
        $this->checkOrderValid();
        //判断是订金支付还是全款支付 当是全款支付时检测库存量
        $orderModel = M('order');
        $order = $orderModel->where("id=$this->orderID")->find();
//        return $order['order_producttype'];
        if($order['order_producttype'] == 2 || $order['order_producttype'] == 3) {
            //检测库存量
            $orderService = new Order();
            $stats = $orderService->checkOrderStock($this->orderID,$order['user_id']);
            if ($stats['pass'] == false) {
                return $this->orderID;
            }
            //积分操作
            $orderPrice = $this->bounds($this->order_bounds, $stats['orderPrice']);
        }elseif ($order['order_producttype'] == 1){
            if($order['order_tailmoney']){   //尾款支付
                //设置支付尾款订单号
                $orderModel->tailmoney_no = $this->getTailmoneyNo();
                $orderModel->where("id=$this->orderID")->save();
                //赋值尾款订单号
                $order = $orderModel->where("id=$this->orderID")->find();
                $this->orderNo = $order['tailmoney_no'];
                //积分操作
                $orderPrice = $this->bounds($this->order_bounds, $order['order_tailmoney']);
            }else{   //订金支付
                $orderPrice = $order['order_price'];
            }
        }
        //修改地址
        $order = M('order')->where("id=$this->orderID")->find();
        $order_info = json_decode($order['snap_address'],true);
        if($this->addressID != $order_info['id']){
            $this->updateAddress();
        }
        return $this->makeWxH5Order($orderPrice,$order);
    }

    //统一下单
    private function makeWxH5Order($orderPrice,$order)
    {
        $attach = $order['order_producttype'].','.$order['id'];
        $unifiedOrder = new \UnifiedOrder_pub();
    	$dataxml['appid'] = C('APPID');
    	$dataxml['mch_id'] = C('MCHID');
    	$dataxml['nonce_str'] = $unifiedOrder->createNoncestr();
    	$dataxml['total_fee'] = $orderPrice*100;
    	$dataxml['attach'] = $attach;
    	$dataxml['body'] = 'h5支付';
    	$dataxml['out_trade_no'] = $this->orderNo;
//    	$dataxml['out_trade_no'] = time();
    	$dataxml['spbill_create_ip'] = $this->get_client_ip();
    	$dataxml['notify_url'] = C('JSAPI_Notify_URL');
    	$dataxml['trade_type'] = 'MWEB';
    	$dataxml['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "admin.yjsina.com","wap_name": "家居"}}';
    	$dataxml['sign'] = $unifiedOrder->getSign($dataxml);
    	//将数组转成xml
    	$xml = $unifiedOrder->arrayToXml($dataxml);
    	$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    	$execUrl = $unifiedOrder->postXmlCurl($xml,$url);
    	//将xml转成数组
    	$xml_array = $unifiedOrder->xmlToArray($execUrl);
//    	print_r($xml_array);exit;
    	if($xml_array['return_code'] == 'SUCCESS')  {
    		if($xml_array['result_code'] == 'SUCCESS'){//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
                $redirect_url = urlencode('http://www.yjsina.com/MyOrder/View.html');
    			$url=$xml_array['mweb_url'].'&redirect_url='.$redirect_url;
//    			echo "<script>location.href='".$url."';</script>";exit;
                return $url;
    		}
    		if($xml_array['return_code'] == 'FAIL'){//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
    			echo $xml_array['return_msg'];
    		}
    	}
    }

    //判断订单参数是否符合要求
    private function checkOrderValid()
    {
        //订单号根本不存在
        $order = D('order')->where("id=" . $this->orderID)->find();
        if (!$order) {
            //异常 订单号不存在
            $result = (new OrderException([
                'code' => 11000,
                'msg' => '订单号不存在'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }

        //用户是否绑定手机号
        if(!(new OrderModel())->is_BindTel($order['user_id'])){
            $this->ajaxReturn((new ProductException([
                'code' => 20099,
                'msg' => '用户未绑定手机号'
            ]))->getException());
        }


        //订单号确实存在，但订单号与当前用户不匹配
//        if(!Token::isValidOperate($order['user_id'])){
//            //异常  订单与用户不匹配
//            $result = (new OrderException([
//                'msg' => '订单与用户不匹配'
//            ]))->getException();
//            echo json_encode($result, JSON_UNESCAPED_UNICODE);
//            die; //抛出异常
//        }
//        //订单是否已经被支付
        if($order['order_status'] != C('Unpaid')){
            //异常  订单已经支付了
            $result = (new OrderException([
                'msg' => '订单已经支付了',
                'code' => $order,
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }

        //用户只能购买一件限时购商品
        $orderModel = M('order');
        $map1['user_id'] = $order['user_id'];
        $map1['order_producttype'] = C('Discount_Product');
        $map1['order_status'] = array('neq',C('Unpaid'));
        $order_Discount = $orderModel->where($map1)->select();
        if($order_Discount){
            $result = (new OrderException([
                'code' => 11099,
                'msg' => '每个用户只能购买一件限时购商品'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }


        $map['id'] = $this->addressID;
        $map['user_id'] = $order['user_id'];
        $address = M('address')->where($map)->find();
        if(!$address){
            //异常 用户和地址不匹配
            $result = (new OrderException([
                'code' => 11007,
                'msg' => '用户和地址不匹配'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $this->orderNo = $order['order_no'];
        return true;
    }


    //修改订单地址
    public function updateAddress(){
        $orders = M('order')->where("id=$this->orderID")->find();
        $map['id'] = $this->addressID;
        $map['user_id'] = $orders['user_id'];
        $user_address = M('address')->where($map)->find();
        if(!$user_address){
            $result = (new AddressException([
                'code' => 11007,
                'msg' => '当前地址与用户不匹配'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        $order = new OrderModel();
        $order->snap_address = json_encode($user_address);
        $result = $order->where("id=$this->orderID")->save();
        if(!$result){
            $result = (new AddressException([
                'code' => 70007,
                'msg' => '地址更新失败'
            ]))->getException();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        return $result;
    }

    //用户使用积分
    public function bounds($bounds, $orderPrice){
        if($bounds == 0 || $orderPrice<$bounds || $orderPrice<1000){
            return $orderPrice;
        }
        if($orderPrice>$bounds && $orderPrice>1000){
            $this->orderBounds();
            return $orderPrice-$bounds;
        }
    }


    //添加用户积分到附加表  方便回调后的积分操作
    public function orderBounds(){
        $order = M('order')->where("id=$this->orderID")->find();
        $orderAttach = M('orderattach');
        $orderAttach->order_id = $this->orderID;
        $orderAttach->user_id = $order['user_id'];
        $orderAttach->order_bounds = $this->order_bounds;
        $result = $orderAttach->add();
        return $result;
    }



    private function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
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

    public function getTailmoneyNo( $length = 6 )
    {
        $str = substr(md5(time()), 0, $length);//md5加密，time()当前时间戳
        $TailmoneyNo = $this->orderNo.'_'.$str;
        return $TailmoneyNo;
    }


}
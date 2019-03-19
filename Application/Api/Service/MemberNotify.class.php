<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Service;

use Api\Controller\CommonController;
use Api\Model\OrderModel;
use Api\Model\UserModel;
use think\Db;

Vendor('Wxpay.lib.WxPay#Api');
Vendor('Wxpay.lib.WxPay#Config');

class MemberNotify extends \WxPayNotify
{

    public function NotifyProcess($objData, $config, &$msg)
    {

        $data = $objData->GetValues();
        if ($data['result_code'] == 'SUCCESS') {  //支付成功
//            Db::startTrans();
            try {
                $memberDetailModel = M('memberdetail');
                $memberNo = $data['out_trade_no'];
                $attach = explode(",", $data['attach']);
                $memberDetail_id = $attach[0];
                $uid = $attach[1];
//                $fp = fopen('test6.txt', 'a+b');
//                fwrite($fp, var_export($data, true));
//                fclose($fp);
                //判断是续费还是新购买
                $memberDetail = $memberDetailModel->where("id=$memberDetail_id and user_id=$attach[1]")->find();
                if($memberDetail){   // 续费用户 或 已过期用户
                    //判断当前时间是否小于会员到期时间
                    $memberDetail['membercard_endtime'];
                    $time = date('Y-m-d', time());
                    if(strtotime($time) < strtotime($memberDetail['membercard_endtime'])){  //续费 到期时间+一年
                        $membercard_endtime = $memberDetail['membercard_endtime'];
                        $memberDetailModel->membercard_endtime = date('Y-m-d',strtotime("$membercard_endtime +1 year"));
                        $memberDetailModel->where("id=$memberDetail_id")->save();
                    }else{  //会员已过期
                        $memberDetailModel->membercard_endtime = date('Y-m-d', strtotime("+1 year"));
                        $memberDetailModel->where("id=$memberDetail_id")->save();
                    }
                }else{  //新购买用户
                    $memberDetailModel->membercard_endtime = date('Y-m-d', strtotime("+1 year"));
                    $memberDetailModel->user_id = $uid;
                    $memberDetailModel->where("id=$memberDetail_id")->save();
                }

//                Db::commit();
                return true;
            } catch
            (Exception $ex) {
                // 如果出现异常，向微信返回false，请求重新发送通知
//                Db::rollback();
                return false;
            }

        } else { //支付失败   true false只是用来控制微信是否继续还向自己的服务器发送消息
            return true;
        }
    }


}
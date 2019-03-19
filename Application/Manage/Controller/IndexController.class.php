<?php

namespace Manage\Controller;

use Think\Controller;

class IndexController extends CommonController
{
    public function index()
    {
        $this->display();
    }

    //欢迎页
    public function welcome()
    {
        $this->display();
    }


    //读取已支付未读订单
    public function unreadOrderMessage()
    {
//        ajax 查询 订单附加表中的id  然后去订单表中查询 id not in 1,2,3的数据  在count
//        点击查看订单后  把not in 查询来的数据  add到附加表中
        $orderModel = M('order');
        $orderMessageModel = M('ordermessage');
        $orderMessage = $orderMessageModel->select();
        if(empty($orderMessage)){
            $map['order_status'] = C('Paid');
            $map['Deposit_type'] = 1;
            $map['_logic'] = 'OR';
            $order = $orderModel->where($map)->field('id,order_status')->count();
        }else{
            foreach ($orderMessage as $value){
                $order_ids[] = $value['order_id'];
            }
            $map['id']  = array('not in',$order_ids);
            $where['order_status'] = C('Paid');
            $where['Deposit_type'] = 1;
            $where['_logic'] = 'OR';
            $map['_complex'] = $where;
            $order = $orderModel->where($map)->field('id,order_status')->count();
        }

        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'order_count' => $order,
        ]);

    }
}

?>
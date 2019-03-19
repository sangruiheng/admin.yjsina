<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-10
 * Time: 下午6:25
 */

namespace Api\Exception;


class OrderException extends BaseException
{

    public $code = 11000;

    public $msg = "订单不存在";

//    public $errorCode = 11000;
}
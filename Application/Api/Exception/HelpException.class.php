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

    public $code = 14000;

    public $msg = "查询帮助信息失败";

//    public $errorCode = 14000;
}
<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午10:17
 */

namespace Api\Exception;


class CateException extends BaseException
{
    public $code = 15000;

    public $msg = "购物车不存在";

//    public $errorCode = 50000;
}
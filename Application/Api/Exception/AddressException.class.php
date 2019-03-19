<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午10:17
 */

namespace Api\Exception;


class AddressException extends BaseException
{
    public $code = 70000;

    public $msg = "地址不存在";

//    public $errorCode = 70000;
}
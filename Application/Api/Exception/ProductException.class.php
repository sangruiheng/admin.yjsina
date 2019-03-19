<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午10:17
 */

namespace Api\Exception;


class ProductException extends BaseException
{
    public $code = 20000;

    public $msg = "商品不存在";

//    public $errorCode = 20000;
}
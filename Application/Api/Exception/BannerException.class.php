<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午10:17
 */

namespace Api\Exception;


class BannerException extends BaseException
{
    public $code = 50000;

    public $msg = "Banner不存在";

//    public $errorCode = 50000;
}
<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午10:17
 */

namespace Api\Exception;


class NewsException extends BaseException
{
    public $code = 40000;

    public $msg = "专题新闻不存在";

//    public $errorCode = 40000;
}
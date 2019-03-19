<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class Express extends BaseValidate
{
    protected $rule = [
        'ShipperCode' => 'require|checkIDs',   //快递公司编号
        'LogisticCode' => 'require|is_Number',  //物流单号
    ];

    protected $message = [
        'LogisticCode.require' => "物流单号不能为空",
        'LogisticCode.isPositiveInteger' => "物流单号必须是正整数",
        'ShipperCode.require' => "快递公司编号不能为空",
        'ShipperCode.checkIDs' => "快递公司编号只能为字母",

    ];


    public static function checkIDs($value){
        if(preg_match("/^([A-Za-z]*)$/", $value)){
            return true;
        }
        return false;
    }


    public static function is_Number($value, $rule = '', $date = '', $field = '')
    {
        if (is_numeric($value)) {
            return true;
        } else {
            return false;
        }

    }

}
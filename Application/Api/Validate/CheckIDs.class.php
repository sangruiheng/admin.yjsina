<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class CheckIDs extends BaseValidate
{
    protected $rule = [
        'product_parm' => 'require|checkIDs',
    ];

    protected $message = [
        'product_parm.require' => "id不能为空",
        'product_parm.checkIDs' => "不能出现除数字和,以外的字符"
    ];

    public function checkIDs($value){
        if(preg_match("/^([，,0-9]*)$/", $value)){
            return true;
        }
        return false;
    }

}
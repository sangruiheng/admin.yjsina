<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


use Api\Exception\ParameterException;
use Api\Exception\ProductException;

class ManyIDs extends BaseValidate
{

    protected $rule = [
        'ids' => 'checkIDs'
    ];

    protected $singleRule = [
        'id' => 'require|isPositiveInteger',
    ];





    public function checkIDs($values){
        if(empty($values)){
            $result = $returnData = (new ProductException([
                'errorCode' => '20001',
                'msg' => '商品列表不能为空'
            ]))->getException();

            echo json_encode($result,JSON_UNESCAPED_UNICODE);  die; //抛出异常
        }
        if(!is_array($values)){
            $result = $returnData = (new ParameterException([
                'msg' => '必须是数组'
            ]))->getException();

            echo json_encode($result,JSON_UNESCAPED_UNICODE);  die; //抛出异常
        }
        foreach ($values as $value){
            $this->checkID($value);
        }
        return true;
    }

    protected function checkID($value){
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if(!$result){
            $result = $returnData = (new ProductException([
                'errorCode' => '20002',
                'msg' => '商品列表参数错误'
            ]))->getException();

            echo json_encode($result,JSON_UNESCAPED_UNICODE);  die; //抛出异常
        }
    }



}
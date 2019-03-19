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

class OrderPlace extends BaseValidate
{


    /*{
    "products":[
    {
    "product_id":12,
    "count":1,
    "productvalue":[
    {
    "productvalue_id":19
    },
    {
        "productvalue_id":18
                    }
    ]
    },
    {
        "product_id":14,
                "count":2,
                "productvalue":[
                    {
                        "productvalue_id":9
                    },
                    {
                        "productvalue_id":10
                    }
                ]
            },
    {
        "product_id":16,
                "count":2,
                "productvalue":[
                    {
                        "productvalue_id":22
                    }
                ]
            }
    ]
    }*/


//    protected $products = [
//        [
//            'product_id' => 1,
//            'count' => 3
//        ],
//        [
//            'product_id' => 2,
//            'count' => 2
//        ],
//        [
//            'product_id' => 3,
//            'count' => 3
//        ],
//    ];

    protected $rule = [
        'products' => 'checkProducts'
    ];

    protected $singleRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger',
//        'productattr_id' => 'require|isNotEmpty|isPositiveInteger',
//        'productvalue_id' => 'require|isNotEmpty|isPositiveInteger'
    ];


    public function checkProducts($values)
    {
        if (empty($values)) {
            $result = $returnData = (new ProductException([
                'errorCode' => '20001',
                'msg' => '商品列表不能为空'
            ]))->getException();

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        if (!is_array($values)) {
            $result = $returnData = (new ParameterException([
                'msg' => '必须是数组'
            ]))->getException();

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
        foreach ($values as $value) {
            $this->checkProduct($value);
        }
        return true;
    }

    protected function checkProduct($value)
    {
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if (!$result) {
            $result = $returnData = (new ProductException([
                'errorCode' => '20002',
                'msg' => '商品列表参数错误'
            ]))->getException();

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            die; //抛出异常
        }
    }


}
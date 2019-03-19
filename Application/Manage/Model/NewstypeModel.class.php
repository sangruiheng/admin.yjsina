<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:57
 */

namespace Manage\Model;


use Think\Model\RelationModel;

class NewstypeModel extends RelationModel
{
    //form表单自动验证
    protected $_validate = array(
        array('typeName','require','类型不能为空'),
    );
}


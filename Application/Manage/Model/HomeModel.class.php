<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:57
 */

namespace Manage\Model;


use Think\Model\RelationModel;

class HomeModel extends RelationModel
{
    //form表单自动验证
    protected $_validate = array(
        array('title', 'require', '请输入标题'),
        array('project_name', 'require', '请输入项目名称'),
        array('enterprise_name', 'require', '请输入企业名称'),
        array('house_num', 'require', '请输入房屋套数！'),
        array('project_address', 'require', '请输入项目位置'),
        array('link', 'require', '请输入链接地址'),
        array('start_time', 'require', '请输入开始时间'),
        array('end_time', 'require', '请输入结束时间'),
    );
}
<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>无标题文档</title>
    <!--公共css-->
<link rel="stylesheet" type="text/css" href="/Public/Manage/tools/bootstrap-3.2.0-dist/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="/Public/Manage/css/fontIcon/css/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="/Public/Manage/css/common/common.css" />
<link rel="stylesheet" type="text/css" href="/Public/Manage/css/alert.css" />
<link rel="stylesheet" type="text/css" href="/Public/Manage/tools/iCheck/flat/blue.css" />
<link rel="stylesheet" type="text/css" href="/Public/Manage/js/tools/icpntDialog/css/icpntDialog.css" />
<!--公共js-->
<script type="text/javascript" src="/Public/Manage/js/common/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="/Public/Manage/js/common/jquery.form.js"></script>
<script type="text/javascript" src="/Public/Manage/tools/bootstrap-3.2.0-dist/js/bootstrap.js"></script>
<script type="text/javascript" src="/Public/Manage/tools/Validform_v5.3.2/Validform_v5.3.2_min.js"></script>
<script type="text/javascript" src="/Public/Manage/js/tools/icpntDialog/js/icpntDialog.js"></script>
<script type="text/javascript" src="/Public/Manage/tools/iCheck/icheck.js"></script>
<script type="text/javascript" src="/Public/Manage/js/common/common.js"></script>
<script type="text/javascript" src="/Public/Manage/postInfo/postInfo.js"></script>
<script>
//定义ThinkPHP模板常量，方便在js中使用
var APP = '/manage.php';
var PUBLIC = '/Public/Manage';
var URL = '/manage.php/Help';
var CONTROLLER_NAME = '<?php echo (CONTROLLER_NAME); ?>';
var ACTION_NAME = '<?php echo (ACTION_NAME); ?>';
var GROUPID = '<?php echo ($_SESSION['crm_rules']); ?>';
var AUTH_ADD_ID = '<?php echo C('AUTH_MODULE.auth_add_id');?>';
var AUTH_DEL_ID = '<?php echo C('AUTH_MODULE.auth_del_id');?>';
var AUTH_SAVE_ID = '<?php echo C('AUTH_MODULE.auth_save_id');?>';
var AUTH_USER_ID = '<?php echo C('AUTH_MODULE.auth_user_id');?>';
var AUTH_GROUP_ID = '<?php echo C('AUTH_MODULE.auth_group_id');?>';
var PAGE = '<?php echo ($_GET['p']); ?>';
var KEYWORD = '<?php echo ($_GET['keyWord']); ?>';
$(document).ready(function(e) {
    $('input[data-name=multi-select]').iCheck({
		checkboxClass: 'icheckbox_flat-blue',
		radioClass: 'iradio_flat-blue'
	});
	
});
</script>
    <link rel="stylesheet" type="text/css" href="/Public/Manage/css/common/rightCommon.css" />
    <script type="text/javascript" src="/Public/Manage/tools/ueditor1_4_3-utf8-php/ueditor.config.js"></script>
    <script type="text/javascript" src="/Public/Manage/tools/ueditor1_4_3-utf8-php/ueditor.all.min.js"></script>
    <script type="text/javascript" src="/Public/Manage/tools/ueditor1_4_3-utf8-php/lang/zh-cn/zh-cn.js"></script>
    <script>
        $(document).ready(function(e) {
            if($("#id").val() == ''){
                var ue = UE.getEditor('help_content');
            }
            getEditData(function(jdata){
                var ue = UE.getEditor('help_content');
            });


        });
    </script>
    <style>
        .uploadify-button {
            height: 30px;
            line-height: 30px;
            width: 120px;
            border-radius: inherit;
            background: #5cb85c;
            border: none;
            font-family: '宋体';
        }
        .lable_id{
            float: left;
            margin: auto 17px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #ccc;
            height: 34px;
            line-height: 34px;
            padding-left: 12px;
            padding-right: 12px;
            cursor: pointer;
        }
        .addForm tr td .on{
            background-color: #449d44;
            color:#fff;
        }
        .fenlei_id{
            float: left;
            margin: auto 17px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #ccc;
            height: 34px;
            line-height: 34px;
            padding-left: 12px;
            padding-right: 12px;
            cursor: pointer;
        }
    </style>
</head>

<body>
<!--alert弹窗Start  -->
<div id="top-alert" class="fixed alert alert-error" style="display:none;">
    <button class="close fixed" style="margin-top: 4px;">&times;</button>
    <div class="alert-content">这是内容</div>
</div>
<!--alert弹窗end  -->
<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <a class="navbar-brand" href="#"><i class="fa fa-plus" aria-hidden="true"></i> <span id="changeTitle">添加</span>帮助中心</a>
    </div>
</nav>

<div class="add-box">
    <form class="addForm ajax-fadein" id="form1" name="form1" method="post" action="/manage.php/Help/addHelpCenter/controller/Help/backUrl/helpCenter/table/help">
        <input name="id" type="hidden" id="id" value="<?php echo $helpCenter['id']?>" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>

            <tr>
                <td align="center">内容详情<font color="red">&nbsp;&nbsp;*</font></td>
                <td colspan="3"><textarea name="help_content" id="help_content" cols="45" rows="5" style="width:100%;height:200px; margin:10px 0px;"></textarea></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button class="btn btn-success ajax-post" type="submit"  id="saveButton" target-form="form-horizontal"><i class="fa fa-check" aria-hidden="true"></i> 添加</button>
                    <button type="button" class="btn btn-default" id="cancelButton"><i class="fa fa-times" aria-hidden="true"></i> 取消</button>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" src="/Public/Manage/js/uploadify/jquery.uploadify.min.js"></script>

</body>
</html>
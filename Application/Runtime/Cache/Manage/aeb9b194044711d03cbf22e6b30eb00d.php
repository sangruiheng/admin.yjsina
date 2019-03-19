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
var URL = '/manage.php/System';
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
<style>
.addForm tr td .on{background-color: #449d44;color:#fff;}
.select-all{float: left;margin: 6px 17px;border: 1px solid #ccc;border-radius: 5px;background: #ccc;height: 34px;line-height: 34px; padding-left: 12px;padding-right: 12px;cursor: pointer;}
</style>
<script>
$(document).ready(function(e) {
	getEditData(function(jData){
		$('input#password').attr('name','');
		$('input#password').attr('placeholder','如不修改密码，请留空');
		$('input#password').val('');
		
		//修改时选中群组
		var groupID = jData.groupID;
		$('div.select-all').each(function(index){
			if($('div.select-all:eq('+index+')').attr('data-id') == groupID){
				$('div.select-all:eq('+index+')').addClass("on");
			}
		});
	});
	$('#password').keyup(function(){
		if($(this).val() != ''){
			$(this).attr('name','password');
		}else{
			$(this).attr('name','');
		}
	});
	//用户群组点击事件
	$('div.add-box').on('click','div.select-all',function(){
		$(this).addClass('on').siblings().removeClass('on');
		$('input#groupID').val($(this).attr('data-id'));
	});
});
</script>
</head>

<body>
<!--alert弹窗Start  -->
<div id="top-alert" class="fixed alert alert-error" style="display:none;">
	<button class="close fixed" style="margin-top: -18px;margin-right: 7px;">&times;</button>
    <div class="alert-content">这是内容</div>
</div>
<!--alert弹窗end  -->
<nav class="navbar navbar-default" role="navigation">
<div class="navbar-header">
<a class="navbar-brand" href="#"><i class="fa fa-plus" aria-hidden="true"></i> <span id="changeTitle">添加</span>用户</a>
</div>
</nav>

<div class="add-box">
<form class="addForm ajax-fadein" id="form1" name="form1" method="post" action="/manage.php/System/addAuthData/controller/System/backUrl/adminuserList/table/Adminuser">
<input name="id" type="hidden" id="id" value="<?php echo ($_GET['id']); ?>" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
      <tr>
        <td align="center">用户名</td>
        <td><input type="text" name="username" id="username" class="form-control" datatype="*" nullmsg="请输入用户名" placeholder="请输入用户名" /></td>
        <td align="center">密码</td>
        <td><input type="password" name="password" id="password" class="form-control" datatype="*" nullmsg="请输入登录密码" placeholder="请输入登录密码" /></td>
      </tr>
      <tr>
        <td align="center">真实姓名</td>
        <td><input type="text" name="reName" id="reName" class="form-control" datatype="*" nullmsg="请输入真实姓名" placeholder="请输入真实姓名" /></td>
        <td align="center">手机号码</td>
        <td><input type="text" name="phone" id="phone" class="form-control" datatype="*" nullmsg="请输入手机号码" placeholder="请输入手机号码" /></td>
      </tr>
      
      <tr>
        <td align="center">电子邮件</td>
        <td colspan="3"><input type="text" name="email" id="email" class="form-control" datatype="*" nullmsg="请输入电子邮件" placeholder="请输入电子邮件" /></td>
      </tr>

      <tr>
        <td align="center">用户群组</td>
        <td colspan="3">
        <input type="hidden" name="groupID" id="groupID" class="form-control"/>
        <div class="selectList">
          <?php if(is_array($groupList)): $i = 0; $__LIST__ = $groupList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="select-all" data-id="<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        </td>
      </tr>
      
      <tr>
        <td>&nbsp;</td>
        <td colspan="3">
        <button type="submit" class="btn btn-success" id="saveButton"><i class="fa fa-check" aria-hidden="true"></i> 添加</button>
        <button type="button" class="btn btn-default" id="cancelButton"><i class="fa fa-times" aria-hidden="true"></i> 取消</button>
        </td>
      </tr>
    </tbody>
  </table>
</form>  
</div>

</body>
</html>
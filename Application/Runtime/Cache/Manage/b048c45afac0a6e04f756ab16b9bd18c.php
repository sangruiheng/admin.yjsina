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
</head>

<body>

<nav class="navbar navbar-default" role="navigation">
<div class="navbar-header">
<a class="navbar-brand" href="#">系统临时文件</a>
</div>
<div>
<button type="button" data-id="<?php echo C('AUTH_MODULE.auth_del_id');?>" class="btn btn-danger navbar-btn listButton" onclick="delLog();"><i class="fa fa-trash-o" aria-hidden="true"></i> 删除</button>
<form class="navbar-form navbar-right listSearch" role="search" method="get" action="/manage.php/System/tempList">
<div class="form-group">
</div>
</form>    
</div>
</nav>

<div class="list-box">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr>
<th>选择</th>
<th>文件名</th>
<th>文件路径</th>
</tr>
</thead>
<tbody>
<?php if(count($list) > 0): if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
<td><input type="checkbox" name="del_listID" id="del_listID" data-name="multi-select" value="<?php echo ($vo["id"]); ?>" /></td>
<td><?php echo ($vo["fileName"]); ?></td>
<td><?php echo ($vo["filePath"]); ?></td>
</tr><?php endforeach; endif; else: echo "" ;endif; ?>
<?php else: ?>
<tr>
<td colspan="4">Oh!暂无列表</td>
</tr><?php endif; ?>
</tbody>
</table>

</div>




<ul class="pagination">
<?php echo ($page); ?>
</ul>

</body>
<script type="text/javascript">
//删除系统临时文件
function delLog(){
	$.show({
		title:'提示',
		content:'确定要删除系统临时文件吗？注：文件删除不可恢复！',
		isConfirm:true,
		callback : function(){
			$.showLoading('正在删除……');
			//$.closeShow();
			$.post(APP+'/System/delLog','',function(ret){
				$.closeLoading();
				setTimeout('location_href();',1000);
			});
		}
	});
}
function location_href(){
	window.location.href = APP+'/'+CONTROLLER_NAME+'/'+ACTION_NAME;
}
</script>
</html>
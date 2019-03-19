<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo C('PROJECT_TITLE.ICON_TITLE');?>|v1.0</title>
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
var URL = '/manage.php/Index';
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
    <script type="text/javascript" src="/Public/Manage/js/main.js"></script>
</head>

<body>
<!--header begin-->
<div class="com-headerBox">
    <div class="com-headerTop com-bg">
        <div class="com-logo"><?php echo C('PROJECT_TITLE.LOGIN_TITLE');?>·管理系统</div>
        <div class="com-htRight">


            <ul class="com-heaMenu">
                <li><a href="/manage.php/Index/index" ><i class="fa fa-home"></i> 首页</a></li>
                <li><a class="toUnreadMessage" href="javascript:void(0);"><i class="fa fa-commenting-o"></i>未读订单<span class="badge"></span></a></li>
                <!--<button class="btn btn-primary" type="button">-->
                <!--Messages <span class="badge">4</span>-->
                <!--</button>-->
                <li><a href="http://www.icpnt.com/" target="view_window"><i class="fa fa-question-circle"></i> 帮助</a></li>
                <li><a class="topLogOut" href="javascript:void(0)"><i class="fa fa-power-off"></i> 退出</a></li>
            </ul>
        </div>
    </div>
    <div class="com-hederMenu">
        <div class="com-hideIcon" title="展开左侧菜单" data-state="hide"><i class="fa fa-bars"></i></div>
        <ul class="com-hmUl">
            <?php if(is_array($moduleTypeList)): $i = 0; $__LIST__ = $moduleTypeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a class="com-TopMenu" data-id="<?php echo ($vo["id"]); ?>" href="javascript:void(0)" ><i class="fa fa-<?php echo ($vo["moduleIcon"]); ?>"></i> <?php echo ($vo["moduleName"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</div>
<!--header end-->



<!--left begin-->
<div class="com-leftBox">
    <div class="com-lbLeftShadow"></div>
    <ul class="com-leftMenu">

    </ul>
</div>
<!--left begin-->

<!--right begin-->
<div class="com-rightBox">
    <iframe name="right" id="icpnt_iframe" frameborder="0" scrolling="auto" width="100%" height="100%" src="/manage.php/Index/welcome"></iframe>
</div>
<!--right end-->
</body>
</html>
<script>
    $('.toUnreadMessage').click(function(){
        var maxorderID = $(this).attr('maxorder-id');
        var src = '/Order/orderList';
        var iframeSrc = $('#icpnt_iframe').attr('src',src);
        window.right.location = APP+src;
    });

    setInterval(function(){
        $.post(APP+'/Index/unreadOrderMessage','',function(res){
            if(res.code == 200){
                $('.badge').html(res.order_count);
            }
            // console.log(res);
        });
    },1000);
</script>
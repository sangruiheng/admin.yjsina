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
.select-all{white-space: nowrap;float: left;margin: 6px 17px;border: 1px solid #ccc;border-radius: 5px;background: #ccc;height: 34px;line-height: 34px; padding-left: 12px;padding-right: 12px;cursor: pointer;}
.select-all-title>span{white-space: nowrap;font-size: 22px;cursor: pointer;border: 1px solid #ccc;border-radius: 5px;background: #ccc;height: 34px;line-height: 34px;padding: 5px 12px;margin: 6px 17px;}
</style>
    <script>
        $(document).ready(function(e) {
            getEditData(function(jdata){
                // console.log(jdata);
                var ruleslist = jdata.rules.split(',');
                $('div.select-all').each(function(index){
                    for(var i=0;i<ruleslist.length;i++){
                        if($('div.select-all:eq('+index+')').attr('data-id') == ruleslist[i]){
                            $('div.select-all:eq('+index+')').addClass("on");
                        }
                        if($('h1.select-all-title>span:eq('+index+')').attr('data-id') == ruleslist[i]){
                            $('h1.select-all-title>span:eq('+index+')').addClass("on");
                        }
                    }
                });


                $('input#twoRules').each(function(index, element) {
                    var thisVal = $(this).val();
                    var label = jdata.twoRules.split(",");
                    for(var i=0;i<label.length;i++){
                        if(thisVal == label[i]){
                            $(this).attr("checked","checked");
                            $(this).parents('div.icheckbox_flat-blue').addClass('checked');
                        }
                    }
                });

            });



            //规则权限点击事件
            $('div.select-all').click(function(){
                $(this).toggleClass('on');
                var thisID = $(this).attr('data-moduletypeID');
                var isCheck = 0;
                $('div.module-'+thisID).each(function(index){
                    if($(this).hasClass('on')){
                        isCheck = 1;
                    }
                });

                if(parseInt(isCheck) == 1){
                    $('span.moduletype-'+thisID).addClass("on");
                }else{
                    $('span.moduletype-'+thisID).removeClass("on");
                }


                var ids = '';
                $('.on').each(function(){
                    var id = $(this).attr('data-id');
                    ids += id+',';
                });
                var rules = ids.substr(0,ids.length-1);
                $('input#rules').val(rules);
            });

            //主模块点击事件
            $('h1.select-all-title>span').click(function(){
                $(this).toggleClass('on');
                var thisID = $(this).attr('data-id');
                if($(this).hasClass('on')){
                    $('div.module-'+thisID).each(function(){
                        $('div.module-'+thisID).addClass("on");
                    });
                }else{
                    $('div.module-'+thisID).each(function(){
                        $('div.module-'+thisID).removeClass("on");
                    });
                }
                var ids = '';
                $('.on').each(function(){
                    var id = $(this).attr('data-id');
                    ids += id+',';
                });
                var rules = ids.substr(0,ids.length-1);
                $('input#rules').val(rules);
            });
        });


        $(function(){
            //全选的实现
            $(".check-all").click(function(){
                $(".ids").prop("checked", this.checked);
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
<a class="navbar-brand" href="#"><i class="fa fa-plus" aria-hidden="true"></i> <span id="changeTitle">添加</span>用户群组</a>
</div>
</nav>

<div class="add-box">
<form class="addForm ajax-fadein" id="form1" name="form1" method="post" action="/manage.php/Common/addData/controller/System/backUrl/groupList/table/Group">
<input name="id" type="hidden" id="id" value="<?php echo ($_GET['id']); ?>" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
      <tr>
        <td align="center">群组名称</td>
        <td colspan="<?php echo ($moduleCount); ?>"><input type="text" class="form-control" name="title" id="title" placeholder="请输入群组名称" /></td>
      </tr>
      
      <tr>
        <td align="center">规则状态</td>
        <td colspan="<?php echo ($moduleCount); ?>"><select name="status" id="status" class="form-control">
          <option value="0" selected="selected">正常</option>
          <option value="1">禁用</option>
        </select></td>
      </tr>
      
      <tr style="vertical-align: top;">
      	<td align="center">模块列表</td>
      	<input type="hidden" name="rules" id="rules" class="form-control"/>
      	<?php if(is_array($moduleList)): $i = 0; $__LIST__ = $moduleList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><td>
      		<h1 class="select-all-title"><span class="moduletype-<?php echo ($vo["id"]); ?>" data-id="<?php echo ($vo["id"]); ?>"><?php echo ($vo["moduleName"]); ?></span></h1>
      		<div class="selectList">
      			<?php if(is_array($vo['list'])): $i = 0; $__LIST__ = $vo['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><div class="select-all module-<?php echo ($vo["id"]); ?>" data-id="<?php echo ($val["id"]); ?>" data-moduletypeID="<?php echo ($vo["id"]); ?>"><?php echo ($val["moduleName"]); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
	        </div>
		</td><?php endforeach; endif; else: echo "" ;endif; ?>
      </tr>

      <tr>
          <td align="center">群组规则</td>
          <td colspan="7">
              <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><label><input name="twoRules[]" type="checkbox" id="twoRules"  class="ids" value="<?php echo ($vo["id"]); ?>" style="width: 18px;height: 18px;margin-top: 0;vertical-align: middle;"/> <?php echo ($vo["title"]); ?> </label><?php endforeach; endif; else: echo "" ;endif; ?>
              <label><input class="check-all" type="checkbox" style="width: 18px;height: 18px;margin-top: 0;vertical-align: middle;"/>全选</label>
          </td>
      </tr>
      
      <tr>
        <td>&nbsp;</td>
        <td colspan="<?php echo ($moduleCount); ?>">
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
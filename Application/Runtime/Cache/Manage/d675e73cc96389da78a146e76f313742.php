<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
var URL = '/manage.php/Product';
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
    <script type="text/javascript" src="/Public/Manage/js/product/supplyProduct.js"></script>
    <link rel="stylesheet" type="text/css" href="/Public/Manage/css/common/rightCommon.css" />
    <script type="text/javascript" src="/Public/Manage/tools/ueditor1_4_3-utf8-php/ueditor.config.js"></script>
    <script type="text/javascript" src="/Public/Manage/tools/ueditor1_4_3-utf8-php/ueditor.all.min.js"></script>
    <script type="text/javascript" src="/Public/Manage/tools/ueditor1_4_3-utf8-php/lang/zh-cn/zh-cn.js"></script>
    <link rel="stylesheet" type="text/css" href="/Public/Manage/tools/webuploader-0.1.5/dist/webuploader.css" />
    <link rel="stylesheet" type="text/css" href="/Public/Manage/tools/webuploader-0.1.5/examples/image-upload/style.css" />

    <style media="screen">
        button{
            outline: none !important;
        }
        .pro-section{
            height: auto;
            overflow: hidden;
            padding-bottom: 8px;
        }
        .pro-section div{
            width: 22.5%;
            float: left;
            padding-right: 1%;
        }
        .pro-section button{
            width: 10%;
            float: left;
        }
        .uploadSimple,.uploadSimple label,.uploadSimple div,.uploadSimple input{
            width: 150px !important;
            height: 34px !important;
            line-height: 34px;
            margin: 0;
            padding: 0;
            float: left;
            margin-right: 6px;
        }
        .pro-imgView{
            cursor: pointer;
        }
    </style>
    <script type="text/javascript">
        var rowColor = 2;
        $(function(){
            getEditData(function(json){
                //获取限时购商品详情
                $.post('/manage.php/Product/getDiscount', {product_id:json.id}, function (res) {
                    if(res.code = 200){
                        $('#discount_starttime').val(res.data.discount_starttime);
                        $('#discount_endtime').val(res.data.discount_endtime);
                    }
                });

                //获取商品颜色
                $.post('/manage.php/Product/getProductColor', {product_id:json.id}, function(res) {
                    for (var i = 0; i < res.length; i++) {
                        var colorHtml = '';
                        colorHtml += '<tr>';
                        colorHtml += '<td class="pro-tdRow" rowspan="2">';
                        if (i == 0) {
                            colorHtml += '<button type="button" class="btn btn-info pro-addColor" style="width:100%;"><i class="fa fa-plus" aria-hidden="true"></i> 添加属性</button>';
                        } else {
                            colorHtml += '<button type="button" class="btn btn-danger pro-delColor" data-id="'+res[i].id+'" style="width:100%;"><i class="fa fa-minus" aria-hidden="true"></i> 删除属性</button>';
                        }
                        colorHtml += '</td>';
                        colorHtml += '<td>';
                        colorHtml += '<div class="input-group">';
                        colorHtml += '<span class="input-group-addon">属性</span>';
                        colorHtml += '<input type="hidden" name="colorID[]" value="'+res[i].id+'">';
                        colorHtml += '<input type="text" class="form-control" name="color_name_edit[]" placeholder="请输入属性" value="'+res[i].color_name+'">';
                        colorHtml += '</div>';
                        colorHtml += '</td>';
                        colorHtml += '<td colspan="3">';
                        colorHtml += '<div class="uploadSimple"><i class="fa fa-picture-o" aria-hidden="true"></i> 重新上传</div>';
                        colorHtml += '<input type="hidden" name="attr_img_eidt[]" value="'+res[i].attr_img+'">';
                        colorHtml += '<img class="pro-imgView" src="/Uploads/Manage/'+res[i].attr_img+'" width="34" height="34" alt=""><font color="#e61111">100*100</font>';
                        colorHtml += '</td>';
                        colorHtml += '</tr>';

                        colorHtml += '<tr class="pro-trBox">';
                        colorHtml += '<td colspan="4" style="padding-top:8px;">';
                        for (var k = 0; k < res[i].producttype1.length; k++) {
                            colorHtml += '<section class="pro-section">';
                            colorHtml += '<div class="input-group">';
                            colorHtml += '<span class="input-group-addon">规格</span>';
                            colorHtml += '<input type="hidden" name="atIDs[]" value="'+res[i].producttype1[k].id+'">';
                            colorHtml += '<input type="text" class="form-control" name="attr_name_edit[]" placeholder="请输入规格" value="'+res[i].producttype1[k].attr_name+'">';
                            colorHtml += '</div>';

                            colorHtml += '<div class="input-group">';
                            colorHtml += '<span class="input-group-addon">原价</span>';
                            colorHtml += '<input type="text" class="form-control" name="original_price_edit[]" placeholder="请输入原价" value="'+res[i].producttype1[k].original_price+'">';
                            colorHtml += '</div>';

                            colorHtml += '<div class="input-group">';
                            colorHtml += '<span class="input-group-addon">价格</span>';
                            colorHtml += '<input type="text" class="form-control" name="price_edit[]" placeholder="请输入价格" value="'+res[i].producttype1[k].price+'">';
                            colorHtml += '</div>';

                            colorHtml += '<div class="input-group">';
                            colorHtml += '<span class="input-group-addon">库存</span>';
                            colorHtml += '<input type="text" class="form-control" name="stock_edit[]" placeholder="请输入库存" value="'+res[i].producttype1[k].stock+'">';
                            colorHtml += '</div>';

                            if (k == 0) {
                                colorHtml += '<button type="button" class="btn btn-primary pro-addTr" color-id="'+res[i].id+'"><i class="fa fa-plus" aria-hidden="true"></i> 添加规格</button>';
                            } else {
                                colorHtml += '<button type="button" class="btn btn-danger pro-delSection" data-id="'+res[i].producttype1[k].id+'"><i class="fa fa-minus" aria-hidden="true"></i> 删除规格</button>';
                            }

                            //colorHtml += '<input type="hidden" name="attr_num[]" class="pro-attrNum" value="1">';
                            colorHtml += '</section>';
                        }

                        colorHtml += '</td>';
                        colorHtml += '</tr>';

                        rowColor = rowColor + 2;
                        $('td.pro-tdRowColor').attr('rowspan', rowColor);
                        $('tr.pro-trBox:last').after(colorHtml);
                    }
                    $.createUploader();
                });
            });

            //删除颜色
            $(document).on('click', 'button.pro-delColor', function() {
                var dataID = $(this).attr('data-id');
                var that = $(this);
                $.post('/manage.php/Product/delProductColor', {id:dataID}, function() {
                    rowColor = rowColor - 2;
                    $('td.pro-tdRowColor').attr('rowspan', rowColor);
                    that.parents('tr').next('tr').remove();
                    that.parents('tr').remove();
                });
            });

            //删除规格
            $(document).on('click', 'button.pro-delSection', function() {
                var dataID = $(this).attr('data-id');
                var that = $(this);
                $.post('/manage.php/Product/delProductAttr', {id:dataID}, function() {
                    that.parents('section.pro-section').remove();
                });
                // var attrNum = parseInt($(this).parents('td').find('input.pro-attrNum').val());
                // attrNum--;
                // $(this).parents('td').find('input.pro-attrNum').val(attrNum);
                // $(this).parents('section.pro-section').remove();
            });


        })
    </script>
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
        <a class="navbar-brand" href="#"><i class="fa fa-plus" aria-hidden="true"></i> <span id="changeTitle">添加</span>商品</a>
    </div>
</nav>

<div class="add-box">
    <form id="form1" class="addForm ajax-alert" name="form1" method="post" action="/manage.php/Product/addSupplyProduct/controller/Product/backUrl/supplyProductList/table/product">
        <input name="id" type="hidden" id="id" value="<?php echo ($_GET['id']); ?>" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>

            <tr>
                <td align="center">所属分类</td>
                <td colspan="5"><select name="category_id" id="category_id" class="form-control">
                    <option value="">--请选择--</option>
                    <?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option pid="<?php echo ($vo["navcate_pid"]); ?>" value="<?php echo ($vo["id"]); ?>"  > <?php if($vo['level'] != 0): ?>|<?php endif; ?> <?php echo str_repeat('-', $vo['level']*6)?>
                            <?php echo ($vo["navcate_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select></td>
            </tr>

            <tr>
                <td align="center">推荐商品</td>
                <td colspan="5">
                    <div id="recommendProduct">

                    </div>
                </td>
            </tr>

            <tr class="pro-nameBox">
                <td align="center">商品名称</td>
                <td colspan="5"><input type="text" name="product_name" id="product_name" class="form-control" placeholder="请输入商品名称" /></td>
            </tr>

            <tr>
                <td align="center">开始时间</td>
                <td colspan="5">
                    <input type="date" id="discount_starttime" class="form-control" name="discount_starttime" value="">
                </td>
            </tr>

            <tr>
                <td align="center">结束时间</td>
                <td colspan="5">
                    <input type="date" id="discount_endtime" class="form-control" name="discount_endtime" value="">
                </td>
            </tr>


            <tr>
                <td align="center">商家名称</td>
                <td colspan="5"><select name="business_id" id="business_id" class="form-control">
                    <option value="">--请选择--</option>
                    <?php if(is_array($business)): $i = 0; $__LIST__ = $business;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["business_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select></td>
            </tr>

            <tr class="pro-trBox">
                <td class="pro-tdRowColor" rowspan="2" align="center">商品属性</td>
                <?php if($_GET['id'] == '' ): ?><td class="pro-tdRow" rowspan="2">
                        <button type="button" class="btn btn-info pro-addColor" style="width:100%;"><i class="fa fa-plus" aria-hidden="true"></i> 添加属性</button>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">属性</span>
                            <input type="text" class="form-control" name="color_name[]" id="color_name" placeholder="请输入属性">
                        </div>
                    </td>
                    <td colspan="3">
                        <div class="uploadSimple"><i class="fa fa-picture-o" aria-hidden="true"></i> 上传图片</div>
                        <font color="#e61111">100*100</font>
                        <input type="hidden" name="attr_img[]" id="attr_img" value="">
                    </td><?php endif; ?>
            </tr>

            <tr class="pro-trBox">
                <?php if($_GET['id'] == '' ): ?><td colspan="4" style="padding-top:8px;">

                        <section class="pro-section">
                            <div class="input-group">
                                <span class="input-group-addon">规格</span>
                                <input type="text" class="form-control" name="attr_name[]" id="attr_name" placeholder="请输入规格">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">原价</span>
                                <input type="text" class="form-control" name="original_price[]" id="original_price" placeholder="请输入原价">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">价格</span>
                                <input type="text" class="form-control" name="price[]" id="price" placeholder="请输入价格">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">库存</span>
                                <input type="text" class="form-control" name="stock[]" id="stock" placeholder="请输入库存">
                            </div>

                            <button type="button" class="btn btn-primary pro-addTr"><i class="fa fa-plus" aria-hidden="true"></i> 添加规格</button>
                            <input type="hidden" name="attr_num[]" class="pro-attrNum" value="1">
                        </section>

                    </td><?php endif; ?>
            </tr>




            <tr class="pro-Bounds">
                <td align="center">最高积分额度</td>
                <td colspan="5"><input type="text" name="product_bounds" id="product_bounds" class="form-control" placeholder="请输入商品最高积分额度"/></td>
            </tr>

            <tr>
                <td align="center">品牌名称</td>
                <td colspan="5"><input type="text" name="product_brand" id="product_brand" class="form-control" placeholder="请输入品牌名称"/></td>
            </tr>

            <tr>
                <td align="center">商品服务<font color="red">&nbsp;&nbsp;*</font></td>
                <td colspan="5">
                    <?php if(is_array($productServe)): $i = 0; $__LIST__ = $productServe;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><input class="pserve" type="checkbox" name="productServeID[]"  value="<?php echo ($vo["id"]); ?>" /><?php echo ($vo["productserve_title"]); endforeach; endif; else: echo "" ;endif; ?>
                </td>
            </tr>



            <tr>
                <td align="center">商品轮播图<font color="red">&nbsp;&nbsp;*</font><br/><font color="#e61111" class="imgSize">750*750</font></td>
                <td colspan="5">
                    <div id="uploader">
                        <div class="queueList">
                            <div id="dndArea" class="placeholder">
                                <div id="filePickers"></div>
                                <p>或将照片拖到这里，单次最多可选300张</p>
                            </div>
                        </div>
                        <div class="statusBar" style="display:none;">
                            <div class="progress"><span class="text">0%</span><span class="percentage"></span></div>
                            <div class="info"></div>
                            <div class="btns">
                                <div id="filePickers1"></div>
                                <div class="uploadBtn">开始上传</div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td align="center">已上传头图</td>
                <td colspan="5">
                    <div id="imgShow">

                    </div>
                    <div id="imgShow1">

                    </div>
                    <input name="listCover" type="hidden" id="listCover" datatype="*" nullmsg="请上传图片并设置列表封面" value=""/>
                    <input name="bannerImg" type="hidden" id="bannerImg" datatype="*" nullmsg="请上传图片并设置Banner头图"/>
                </td>
            </tr>





            <tr>
                <td align="center">商品详情<font color="red">&nbsp;&nbsp;*</font></td>
                <td colspan="5"><textarea name="product_content" id="product_content" cols="45" rows="5"
                                          style="width:100%;height:200px; margin:10px 0px;"></textarea></td>
            </tr>



            <tr>
                <td>&nbsp;</td>
                <td colspan="5">
                    <button class="btn btn-success ajax-post" type="submit" id="saveButton" target-form="form-horizontal"><i class="fa fa-check" aria-hidden="true"></i> 添加
                    </button>
                    <button type="button" class="btn btn-default" id="cancelButton"><i class="fa fa-times" aria-hidden="true"></i> 取消
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" src="/Public/Manage/tools/webuploader-0.1.5/dist/webuploader.js"></script>
<script type="text/javascript" src="/Public/Manage/tools/webuploader-0.1.5/uploadImg.js"></script>
<script type="text/javascript" src="/Public/Manage/tools/webuploader-0.1.5/examples/image-upload/upload.js"></script>


<script>
    $(function() {
        $.createUploader();
        //添加颜色
        $(document).on('click', 'button.pro-addColor', function() {
            var colorHtml = '';
            colorHtml += '<tr class="pro-trBox">';
            colorHtml += '<td class="pro-tdRow" rowspan="2">';
            colorHtml += '<button type="button" class="btn btn-danger pro-removeColor" style="width:100%;"><i class="fa fa-minus" aria-hidden="true"></i> 移除属性</button>';
            colorHtml += '</td>';
            colorHtml += '<td>';
            colorHtml += '<div class="input-group">';
            colorHtml += '<span class="input-group-addon">属性</span>';
            colorHtml += '<input type="text" class="form-control" name="color_name[]" placeholder="请输入属性">';
            colorHtml += '</div>';
            colorHtml += '</td>';
            colorHtml += '<td colspan="3">';
            colorHtml += '<div class="uploadSimple"><i class="fa fa-picture-o" aria-hidden="true"></i> 上传图片</div>';
            colorHtml += '<input type="hidden" name="attr_img[]" value="">';
            colorHtml += '</td>';
            colorHtml += '</tr>';

            colorHtml += '<tr class="pro-trBox">';
            colorHtml += '<td colspan="4" style="padding-top:8px;">';
            colorHtml += '<section class="pro-section">';
            colorHtml += '<div class="input-group">';
            colorHtml += '<span class="input-group-addon">规格</span>';
            colorHtml += '<input type="text" class="form-control" name="attr_name[]" placeholder="请输入规格">';
            colorHtml += '</div>';

            colorHtml += '<div class="input-group">';
            colorHtml += '<span class="input-group-addon">原价</span>';
            colorHtml += '<input type="text" class="form-control" name="original_price[]" placeholder="请输入原价">';
            colorHtml += '</div>';

            colorHtml += '<div class="input-group">';
            colorHtml += '<span class="input-group-addon">价格</span>';
            colorHtml += '<input type="text" class="form-control" name="price[]" placeholder="请输入价格">';
            colorHtml += '</div>';

            colorHtml += '<div class="input-group">';
            colorHtml += '<span class="input-group-addon">库存</span>';
            colorHtml += '<input type="text" class="form-control" name="stock[]" placeholder="请输入库存">';
            colorHtml += '</div>';

            colorHtml += '<button type="button" class="btn btn-primary pro-addTr"><i class="fa fa-plus" aria-hidden="true"></i> 添加规格</button>';
            colorHtml += '<input type="hidden" name="attr_num[]" class="pro-attrNum" value="1">';
            colorHtml += '</section>';
            colorHtml += '</td>';
            colorHtml += '</tr>';

            rowColor = rowColor + 2;
            $('td.pro-tdRowColor').attr('rowspan', rowColor);
            $('tr.pro-trBox:last').after(colorHtml);
            $.createUploader();
        });

        //移除颜色
        $(document).on('click', 'button.pro-removeColor', function() {
            rowColor = rowColor - 2;
            $('td.pro-tdRowColor').attr('rowspan', rowColor);
            $(this).parents('tr').next('tr').remove();
            $(this).parents('tr').remove();
        });


        //添加规格
        $(document).on('click', 'button.pro-addTr', function() {
            var colorID = $(this).attr('color-id');
            var attrNum = parseInt($(this).siblings('input.pro-attrNum').val());
            attrNum++;
            var trHtml = '';
            trHtml += '<section class="pro-section">';
            if (typeof(colorID) != 'undefined') {
                trHtml += '<input type="hidden" name="edit_color_id[]" value="'+colorID+'">';
                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">规格</span>';
                trHtml += '<input type="text" class="form-control" name="attr_name_edit_add[]" placeholder="请输入规格">';
                trHtml += '</div>';

                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">原价</span>';
                trHtml += '<input type="text" class="form-control" name="original_price_edit_add[]" placeholder="请输入原价">';
                trHtml += '</div>';

                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">价格</span>';
                trHtml += '<input type="text" class="form-control" name="price_edit_add[]" placeholder="请输入价格">';
                trHtml += '</div>';

                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">库存</span>';
                trHtml += '<input type="text" class="form-control" name="stock_edit_add[]" placeholder="请输入库存">';
                trHtml += '</div>';
            }else{
                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">规格</span>';
                trHtml += '<input type="text" class="form-control" name="attr_name[]" placeholder="请输入规格">';
                trHtml += '</div>';

                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">原价</span>';
                trHtml += '<input type="text" class="form-control" name="original_price[]" placeholder="请输入原价">';
                trHtml += '</div>';

                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">价格</span>';
                trHtml += '<input type="text" class="form-control" name="price[]" placeholder="请输入价格">';
                trHtml += '</div>';

                trHtml += '<div class="input-group">';
                trHtml += '<span class="input-group-addon">库存</span>';
                trHtml += '<input type="text" class="form-control" name="stock[]" placeholder="请输入库存">';
                trHtml += '</div>';
            }
            trHtml += '<button type="button" class="btn btn-danger pro-removeSection"><i class="fa fa-minus" aria-hidden="true"></i> 移除规格</button>';
            trHtml += '</section>';
            $(this).parents('td').append(trHtml);
            $(this).siblings('input.pro-attrNum').val(attrNum);
        });

        //移除规格
        $(document).on('click', 'button.pro-removeSection', function() {
            var attrNum = parseInt($(this).parents('td').find('input.pro-attrNum').val());
            attrNum--;
            $(this).parents('td').find('input.pro-attrNum').val(attrNum);
            $(this).parents('section.pro-section').remove();
        });

        //预览图片
        $(document).on('click', 'img.pro-imgView', function(event) {
            var imgUrl = $(this).attr('src');
            $.show({
                title:'图片预览',
                content:'<img class="pro-imgView" src="'+imgUrl+'" width="100%" alt="">'
            });
        });
    })

    //多图上传时添加图片的回调
    function addCallback() {
        // var imgNameVal = $('#listCover').val();
        // var imgNameVal2 = $('#bannerImg').val();
        // if (imgNameVal != '' && imgNameVal2 != '') {
        //   alert('为了节省服务器资源，请先删除现有图片！');
        //   return false;
        // }
    }

    //单图上传成功回调 from uploadImg.js
    function uploadImgCallback(file,response){
        $('#rt_'+file.source.ruid).parents('div.uploadSimple').siblings('input').val(response.url);
        $('#rt_'+file.source.ruid).parents('div.uploadSimple').siblings('img.pro-imgView').remove();
        $('#rt_'+file.source.ruid).parents('div.uploadSimple').after('<img class="pro-imgView" src="/Uploads/Manage/'+response.url+'" width="34" height="34" alt="">')
    }

    //多图上传成功回调 from upload.js
    function uploadCallback(file,response) {

        var uploadHtml = '';
        uploadHtml += '<div class="upload-listDiv">';
        uploadHtml += '<img src="/Uploads/Manage/' + response.url + '" width="120" height="120">';
        // uploadHtml += '<input type="hidden" name="hid[]"  value="/Uploads/Manage/' + response.url + '">';
        uploadHtml += '<div class="upload-ldButton" data-url="' + response.url + '">';
        uploadHtml += '<a class="btn btn-defaule upload-select" onclick="javascript:setThumb(this)"  style="float:left"><i class="fa fa-book" aria-hidden="true">设为缩略图</i> <input type="hidden" class="isthumb" name="is_thumb[]" value="0"></a >';
        uploadHtml += '</div>';
        uploadHtml += '</div>';
        $('div#imgShow').append(uploadHtml);
        /* var uploadHtml = '';
         uploadHtml += '<div class="upload-listDiv">';
         uploadHtml += '< img src="/Upload/' + response.url + '" height="160">';
         uploadHtml += '<div class="upload-ldButton" data-url="' + response.url + '">';
         uploadHtml += '<button type="button" onclick="javascript:delImg(this)" title="删除图片" class="btn btn-default upload-delete"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
         uploadHtml += '<button type="button" title="设为列表封面" class="btn btn-default upload-listCover"><i class="fa fa-book" aria-hidden="true"></i></button>';
         uploadHtml += '<button type="button" title="设为详情页头图" class="btn btn-default upload-detailsImg"><i class="fa fa-picture-o" aria-hidden="true"></i></button>';
         uploadHtml += '<a class="btn btn-defaule upload-select" style="float:left"></a >';
         uploadHtml += '</div>';
         uploadHtml += '</div>';
         $('div#imgShow').append(uploadHtml);*/
        var UrlHtml = '<input type="hidden" name="hid[]"  value="/Uploads/Manage/' + response.url + '">';
        $('div#imgShow1').append(UrlHtml);
        // //设为列表封面
        // $('.upload-listCover').click(function() {
        //   var imgUrl = $(this).parent('div.upload-ldButton').attr('data-url');
        //   $(this).siblings('a.upload-select').html('<i class="fa fa-book" aria-hidden="true"></i> 列表封面');
        //   $('#listCover').val(imgUrl);
        // });
        // //设为详情页头图
        // $('.upload-detailsImg').click(function() {
        //   var imgUrl = $(this).parent('div.upload-ldButton').attr('data-url');
        //   $(this).siblings('a.upload-select').html('<i class="fa fa-picture-o" aria-hidden="true"></i> 详情页头图');
        //   $('#bannerImg').val(imgUrl);
        // });
    }

    //添加时设置缩略图
    function setThumb(obj) {
        var isthumb = $('.isthumb');
        $.each(isthumb, function (i, value) {
            $(value).attr('value',0);
        });
        $(".fa-book").text('设置为缩略图');
        $(".upload-ldButton .upload-select").css('color','');
        $(obj).css('color','red');
        $(obj).find('.fa-book').text('已设置为缩略图');
        var input = $(obj).find('input').attr('value',1);

        // console.log(input);

    }

    //删除轮播
    function delImg(obj) {
        var id = $(obj).attr('name');
        var imgPath = $(obj).parent().prev().attr('src');
        $.post(APP + '/Product/delImg', {id: id, imgPath: imgPath}, function (res) {
            if(res.code == 200){
                $(obj).parents('.upload-listDiv').remove();
            }
        });
    }

    //设置缩略图
    function thumb(obj) {
        var productImgID = $(obj).attr('name');
        var productID = $(obj).attr('productid');
        $.post(APP + '/Product/thumb', {productImgID: productImgID,productID:productID}, function (data) {
            $(".upload-ldButton a").text('设置为缩略图');
            $(".upload-ldButton a").css('color','');
            $(obj).css('color','red');
            $(obj).text('已设置为缩略图');
        });
    }


</script>



</body>

</html>
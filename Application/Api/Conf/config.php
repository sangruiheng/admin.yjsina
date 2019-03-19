<?php
return array(
    'URL_ROUTER_ON'   => true, //开启路由
    'URL_PATHINFO_DEPR' => '/', //PATHINFO URL分割符
    'URL_ROUTE_RULES' => array( //定义路由规则
//        'api/user/:id$'    => array('Api/User/getUser',array('method'=>'get')),
//        'api/home/all$'    => array('Api/Home/getAllHome',array('method'=>'get')),
        'api/news/all'    => array('Api/News/getAllNews',array('method'=>'post')),     //获取全部新闻
        'api/news/home'    => array('Api/News/getHomeNews',array('method'=>'post')),     //获取首页推荐新闻
        'api/getnews'    => array('Api/News/getNews',array('method'=>'post')),         //获取指定新闻内容

        'api/category/all'    => array('Api/Category/getAllCategory',array('method'=>'post')),  //获取全部分类
        'api/getcategory'    => array('Api/Category/getCategory',array('method'=>'post')),    //获取二级分类
        'api/topcategory'    => array('Api/Category/topCategory',array('method'=>'post')),    //获取推荐一级分类
        'api/levelcategory'    => array('Api/Category/levelCategory',array('method'=>'post')),    //分类
        'api/topallcategory'    => array('Api/Category/topAllCategory',array('method'=>'post')),    //获取全部一级分类

        'api/homeproduct'    => array('Api/Product/getHomeProduct',array('method'=>'post')),   //获取首页商品
        'api/pageproduct'    => array('Api/Product/getPageProduct',array('method'=>'post')),   //获取首页其他栏目商品
        'api/getproduct'    => array('Api/Product/getCateProduct',array('method'=>'post')),   //获取指定分类下的商品列表
        'api/getproductdetail'    => array('Api/Product/getProductDetail',array('method'=>'post')),  //获取商品详情
        'api/getproducttype'    => array('Api/Product/getProductType',array('method'=>'post')),  //获取商品的属性
        'api/getproductserve'    => array('Api/Product/getProductServe',array('method'=>'post')),  //获取商品的服务
        'api/getproductcomment'    => array('Api/Product/getProductComment',array('method'=>'post')),  //获取商品的评论
        'api/getproductpraise'    => array('Api/Product/getProductPraise',array('method'=>'post')),  //获取商品好评评论
        'api/getproductreview'    => array('Api/Product/getProductReview',array('method'=>'post')),  //获取商品中评评论
        'api/getproductnegative'    => array('Api/Product/getProductNegative',array('method'=>'post')),  //获取商品差评评论
        'api/getsupplyproduct'    => array('Api/Product/getSupplyProduct',array('method'=>'post')),  //获取限时购商品
        'api/getallproduct'    => array('Api/Product/getAllProduct',array('method'=>'post')),  //获取首页全部商品



        'api/banner/all'    => array('Api/Banner/getBanner',array('method'=>'post')),   //获取轮播图
        'api/banner/commercial'    => array('Api/Banner/getCommercialBanner',array('method'=>'post')),   //获取广告轮播图
        'api/banner/login'    => array('Api/Banner/getLoginBanner',array('method'=>'post')),   //获取登陆页轮播图
        'api/advertising'    => array('Api/Banner/getAdvertising',array('method'=>'post')),   //获取家电广告位
        'api/getserviceimg'    => array('Api/Banner/getServiceImg',array('method'=>'post')),   //获取家电广告位
        'api/getmembercard'    => array('Api/Membercard/getMemberCard',array('method'=>'post')),   //获取会员卡

        'api/search/hot'    => array('Api/Search/getHotSearch',array('method'=>'post')),   //获取热门搜索
        'api/search/integrate'    => array('Api/Search/integrateSearch',array('method'=>'post')),   //综合搜索
        'api/search/high'    => array('Api/Search/getProductHigh',array('method'=>'post')),   //价格从高到低
        'api/search/low'    => array('Api/Search/getProductLow',array('method'=>'post')),   //价格从低到高
        'api/search/category'    => array('Api/Search/getVagueCategory',array('method'=>'post')),   //根据输入显示下拉分类
        'api/search/selproduct'    => array('Api/Search/getSelectProduct',array('method'=>'post')),   //下拉搜索商品

        'api/user/getaddress'    => array('Api/Address/getUserAddress',array('method'=>'post')),   //获取用户地址
        'api/user/deladdress'    => array('Api/Address/delUserAddress',array('method'=>'post')),   //删除用户地址
        'api/user/createaddress'    => array('Api/Address/createUserAddress',array('method'=>'post')),   //新增用户地址
        'api/user/beforupdateaddress'    => array('Api/Address/beforUpdateAddress',array('method'=>'post')),   //修改前显示用户地址
        'api/user/updateaddress'    => array('Api/Address/updateUserAddress',array('method'=>'post')),   //修改用户地址

//        'api/user/adduser'    => array('Api/User/addUser',array('method'=>'post')),   //用户注册
        'api/user/login'    => array('Api/User/login',array('method'=>'post')),   //用户登录
        'api/user/wxlogin'    => array('Api/User/wxLogin',array('method'=>'post')),   //微信授权登录
        'api/user/getuserinfo'    => array('Api/User/getToken',array('method'=>'post')),   //微信授权跳转
        'api/user/updatepay'    => array('Api/User/updatePayPwd',array('method'=>'post')),   //修改支付密码
        'api/user/updatepwd'    => array('Api/User/updateUserPwd',array('method'=>'post')),   //修改用户密码
        'api/user/personal'    => array('Api/User/personalCenter',array('method'=>'post')),   //个人中心显示
        'api/user/smscode'    => array('Api/User/getSmsCode',array('method'=>'post')),   //签到
        'api/user/sign'    => array('Api/User/sign',array('method'=>'post')),   //获取验证码
        'api/user/boundscenter'    => array('Api/User/boundsCenter',array('method'=>'post')),   //积分中心
        'api/user/sharefriend'    => array('Api/User/shareFriend',array('method'=>'post')),   //邀请好友界面
        'api/user/wxshare'    => array('Api/User/wxShare',array('method'=>'post')),   //分享好友
        'api/user/wxshareproduct'    => array('Api/User/wxShareProduct',array('method'=>'post')),   //分享商品
        'api/user/smsorderdetail'    => array('Api/User/smsOrderDetail',array('method'=>'post')),   //短信订单详情
        'api/user/wxbindtel'    => array('Api/User/wxBindTel',array('method'=>'post')),   //微信绑定手机号
        'api/user/getbindtel'    => array('Api/User/getBindTel',array('method'=>'post')),   //显示绑定手机号页面
        'api/user/sendbindsms'    => array('Api/User/sendBindSms',array('method'=>'post')),   //更换绑定手机号 发送验证码
        'api/user/validateidentity'    => array('Api/User/validateIdentity',array('method'=>'post')),   //更换绑定手机号 验证身份

        'api/feed/addfeed'    => array('Api/Feed/addFeed',array('method'=>'post')),   //新增意见反馈

        'api/order/placeorder'    => array('Api/Order/placeOrder',array('method'=>'post')),   //创建订单
        'api/order/all'    => array('Api/Order/getAllOrder',array('method'=>'post')),   //获取全部订单
        'api/order/waitpay'    => array('Api/Order/getWaitOrder',array('method'=>'post')),   //获取待付款订单
        'api/order/paid'    => array('Api/Order/getPaidOrder',array('method'=>'post')),   //获取待发货订单
        'api/order/shipped'    => array('Api/Order/getShippedOrder',array('method'=>'post')),   //获取已发货订单
        'api/order/evaluated'    => array('Api/Order/getEvaluatedOrder',array('method'=>'post')),   //获取待评价订单
        'api/order/cencel'    => array('Api/Order/cencelOrder',array('method'=>'post')),   //取消订单
        'api/order/depositorder'    => array('Api/Order/depositOrder',array('method'=>'post')),   //订金商品下单


        'api/order/detail'    => array('Api/Order/getOrderDetail',array('method'=>'post')),   //获取订单详情
        'api/order/del'    => array('Api/Order/delOrder',array('method'=>'post')),   //删除订单
        'api/order/readyorder'    => array('Api/Order/getReadyOrder',array('method'=>'post')),   //下订单前的显示
        'api/order/readyaddress'    => array('Api/Order/orderReadyAddress',array('method'=>'post')),   //订单地址修改

        'api/cate/addcate'    => array('Api/Cate/addCate',array('method'=>'post')),   //加入购物车
        'api/cate/getusercate'    => array('Api/Cate/getUserCate',array('method'=>'post')),   //获取用户购物车列表
        'api/cate/delusercate'    => array('Api/Cate/delUserCate',array('method'=>'post')),   //删除用户购物车商品
        'api/cate/upcate'    => array('Api/Cate/upCate',array('method'=>'post')),   //增加购物车数量
        'api/cate/dncate'    => array('Api/Cate/dnCate',array('method'=>'post')),   //减少购物车数量
        'api/cate/countcate'    => array('Api/Cate/countCate',array('method'=>'post')),   //输入购物车数量


        'api/pay'    => array('Api/Pay/getPreOrder',array('method'=>'post')),   //支付
        'api/pay1'    => array('Api/Pay/orderList',array('method'=>'post')),   //微信支付显示页
        'api/notify'    => array('Api/Pay/notify',array('method'=>'post')),   //支付回调
        'api/h5pay'    => array('Api/Pay/h5Pay',array('method'=>'post')),    //H5支付
        'api/orderrefund'    => array('Api/Pay/orderRefund',array('method'=>'post')),    //已付款退款
        'api/productrefund'    => array('Api/Pay/productRefund',array('method'=>'post')),    //已发货退款
        'api/orderrefunddetail'    => array('Api/Pay/orderRefundDetail',array('method'=>'post')),    //已付款订单退款详情
        'api/productrefunddetail'    => array('Api/Pay/productRefundDetail',array('method'=>'post')),    //已发货订单退款详情
        'api/memberpay'    => array('Api/Membercard/memberCardPay',array('method'=>'post')),   //会员卡微信支付
        'api/h5memberpay'    => array('Api/Membercard/memberH5Pay',array('method'=>'post')),   //会员卡h5支付
        'api/membernotify'    => array('Api/Membercard/memberNotify',array('method'=>'post')),   //会员卡支付回调




        'api/Logistics'    => array('Api/Deliver/getSendInformation',array('method'=>'post')), //物流查询
        'api/deliveryproduct'    => array('Api/Deliver/getDeliveryProduct',array('method'=>'post')),  //查看已发货商品
        'api/evaluateproduct'    => array('Api/Deliver/getEvaluateProduct',array('method'=>'post')),  //查看待评价商品
        'api/evaproduct'    => array('Api/Deliver/evaProduct',array('method'=>'post')),  //评价商品
        'api/confirmproduct'    => array('Api/Deliver/confirmProduct',array('method'=>'post')),  //确认收货


        'api/help'    => array('Api/User/help',array('method'=>'post')),  //帮助中心
        'api/about'    => array('Api/User/about',array('method'=>'post')),  //关于我们
    ),
//    'TMPL_EXCEPTION_FILE' => APP_PATH.'/Public/exception.tpl',
    'DEFAULT_AJAX_RETURN' => 'JSON', // 默认AJAX 数据返回格式,可选JSON XML ...

);
?>









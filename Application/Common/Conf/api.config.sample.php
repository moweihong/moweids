<?php
return array(
    //第三方接口配置文件
    'kuaidi100key'                  => '499dfcd0ffdbc5fb',
    'kuaidi100url'                  =>'http://www.kuaidi100.com/query?type=>{{com}}&postid=>{{shipnumber}}',
    
    'javaapi_isRegister'            => '/unify_interface/user/isRegister.do',
    'javaapi_changePassword'        => '/unify_interface/user/setUsrpwd.do',
    'javaapi_changeMobile'          => '/unify_interface/user/setUsrphone.do',
    'javaapi_changeEmail'           => '/unify_interface/user/setUsremail.do',
    'javaapi_getUserId'             => '/unify_interface/user/getUsrid.do',
    'javaapi_getUserInfo'           => '/unify_interface/user/getUsrinf.do',
    'javaapi_get_recommend'         => '/unify_interface/user/getRecommend.do',
    'javaapi_get_useriniteintegral' => '/unify_interface/user/getUsrIniteIntegral.do',
    'javaapi_sub_useriniteintegral' => '/unify_interface/user/subUsrIniteIntegral.do',
    'javaapi_get_userlowerlist'     => '/unify_interface/user/getUsrLowerlist.do',
    
    'javaapi_get_repay'             => 'ccfax_background/store/getRepaymentList.do', 
    'javaapi_fabiao'                => 'allwood_background/store/setBiddingInf.do', 
    
    //查询授信额度接口
    'javaapi_get_credit_status'     => '/tsfkxt/user/getCreditStatus.do',
    
    //提交授信资料接口
    'javaapi_set_credit_info'       => '/tsfkxt/user/setCreditInf.do',
    
    //申请授信审批接口(提交资料后会经过多平台，授信状态只有最后返回到全木行为空才能判断
    //成功，此时才需要调用此接口向风控申请授信神品)
    'javaapi_set_credit_status'     => '/tsfkxt/user/setCreditStatus.do',
    
    //设置委托代扣回调
    //新浪回调的同步地址注册的是链金所，为了能回到全木行，需要传入一个地址，供链金所调用
    //在新浪密码设置成功，新浪回调到链金所后，链金所跳转回全木行
    'javaapi_set_sina_pay_pwd'      => '/allwood_background/store/setSinaPayPwd.do',
    
    //分期购利息计算接口
    'javaapi_get_interest_info'     => '/allwood_background/store/getInterestInf.do',
    
    //风控激活
    'javaapi_set_usr_activate'      => '/tsfkxt/user/setUsrActivate.do',
    
    /**************************正式环境api************************************/
    // 'javaapi'                       => 'http://182.254.131.15:8080/',
    // //全木行接口ip
    // 'allwood_url'                   => '115.159.208.43:8080/',
    
    // //风控接口ip
    // 'risk_control'                  => '182.254.131.15:8080/',
    
    // //聚信立接口
    // 'juxinliapi'                     => 'http://182.254.131.15:8080/',
    
    
    
    /**************************测试环境api************************************/
    'javaapi'                       => 'http://182.254.131.15:8080/',
    //全木行接口ip
    'allwood_url'                   => 'http://182.254.131.15:8080/',
    
    //风控接口ip
    'risk_control'                  => 'http://182.254.131.15:8080/',
    
    //聚信立接口
    'juxinliapi'                    => 'http://182.254.131.15:8080/',
)
?>
<?php

/*
 * 注册验证码
 */
function sendRegis($to){
    return send($to, '23905');
}

/*
 * 找回密码短信验证码
 */
function sendResetPwd($to){
    return send($to, '24060');
}

/*
 * 登录短信验证码
 */
function sendLogin($to){
    return send($to, '24760');
}


/*
 * 开店成功
 */
function sendOpenStoreSucc($to){
    return send($to, '24851');
}


/*
 * 开店失败
 */
function sendOpenStoreFail($to){
    return send($to, '24870');
}

/*
 * 卖家申请提现
 * 您正在全木行申请提现，验证码为{1}，{2}分钟内有效
 */
function sendWithdraw($to){
    return send($to, '27751');
}

/*
 * 发送短信方法
 */
function send($to, $templateId,$expire = 3){
        //初始化必填
        $options['accountsid']='6ce969bcdd0b7d4203a1e451db68cc57'; //填写自己的
        $options['token']='869ef63bea227639b2e127bb6aa4cf55'; //填写自己的
        //初始化 $options必填
        $ucpass = new \Org\Com\Ucpaas($options);
                
        //随机生成6位验证码
        srand((double)microtime()*1000000);//create a random number feed.
        //$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
        $ychar="0,1,2,3,4,5,6,7,8,9";
        $list=explode(",",$ychar);
        for($i=0;$i<6;$i++){
            $randnum=rand(0,9); // 10+26;
            $authnum.=$list[$randnum];
        }

        //短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
        $appId = "a3390ab96dc04728bbaa70ab4bf2e33f";  //填写自己的
        $to = $to;
        $templateId = $templateId;
        $param="$authnum,$expire";

        
        if(APP_DEBUG||(C('MODE') == DEBUG)||(C('MODE') == TEST)){
            //调试模式
            $_SESSION['ts_'.$to]['verify_code'] = 888888;
			$_SESSION['ts_'.$to]['verify_time'] = time();//时间
            return 1;
        }

        $arr=$ucpass->templateSMS($appId,$to,$templateId,$param);
        if (substr($arr,21,6) == 000000) {
			$_SESSION['ts_'.$to]['verify_code']=$authnum;
			$_SESSION['ts_'.$to]['verify_time'] = time();//时间
            return 1; 
        }else{
            //记录失败原因
            //TODO ....
            return 0;
        }
    } 

?>
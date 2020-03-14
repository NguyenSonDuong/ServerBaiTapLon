<?php
    require 'connect.php';
    $nickname = $_POST['nickname'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $sex = $_POST['sex'];
    if(!$_POST['nickname'] || !$_POST['password']){
        sendReponsive(404,array("message"=>"Vui lòng nhập đầy đủ thông tin"));
    }
    if(checkEmail($email)){
        sendReponsive(404,array("message"=>"Email đã tồn tại"));
    }
    $token = insertLoginInfor($nickname, md5($password));
    if(!empty($token)){
        $data = array(
            "status_code"=>200,
            "nickname"=>$nickname,
            "create_time"=>date_format(new DateTime("now"),"Y/m/d H:m:s")
        );
        $dataInser = array(
            'nickname'=>$nickname,
            'email'=>$email,
            'sex'=>$sex
        );
        $succ = insertInformationUser($dataInser);
        if(createTableVay($nickname)){
            $data['table_vay'] = "Thành công";
        }else{
            $data['table_vay'] = "Thất bại";
        };
        if(createTableChiTieu($nickname)){
            $data['table_chi_tieu'] = "Thành công";
        }else{
            $data['table_chi_tieu'] = "Thất bại";
        };
        sendReponsive(200,$data);
    }else{
        sendReponsive(300,array("message"=>"Token bị bỏ trống"));
    }




?>
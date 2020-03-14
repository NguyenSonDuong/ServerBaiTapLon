<?php
    require 'connect.php';
    $method = $_POST['method'];
    $token = $_POST['token'];
    if(!$method || !$token){
        sendReponsive(404,array('message'=>"Vui lòng diền đầy đủ thông tin"));
    }
    if($method == "EMAIL"){
        $email = $_POST['email'];
        if(!$email)
            sendReponsive(400,array("message"=>"email bị trống"));
        else
            if(updateInformationUserEmail($token,$email)){
                sendReponsive(200,sendDataSuccessful(200,"Cập nhật dữ liệu thành công"));
            }else{
                sendReponsive(400,array("message"=>"Cập nhật dữ liệu thất bại"));
            };
    }
    if($method == "BIRTHDAY"){
        $birthday = $_POST['birthday'];
        if(!$birthday)
            sendReponsive(400,array("message"=>"birthday bị trống"));
        else
            if(updateInformationUserBirthday($token,$email)){
                sendReponsive(200,sendDataSuccessful(200,"Cập nhật dữ liệu thành công"));
            }else{
                sendReponsive(400,array("message"=>"Cập nhật dữ liệu thất bại"));
            };
    }
    if($method == "SEX"){
        $sex = $_POST['sex'];
        if(!$sex)
            sendReponsive(400,array("message"=>"Sex bị trống"));
        else
            if(updateInformationUserSex($token,$email)){
                sendReponsive(200,sendDataSuccessful(200,"Cập nhật dữ liệu thành công"));
            }else{
                sendReponsive(400,array("message"=>"Cập nhật dữ liệu thất bại"));
            };
    }
    if($method == "ALL"){
        $email = $_POST['email'];
        if(!$email)
            sendReponsive(400,array("message"=>"email bị trống"));
        else
            if(updateInformationUserEmail($token,$email)){

            }else{
                sendReponsive(400,array("message"=>"Cập nhật dữ liệu thất bại"));
            };

        $birthday = $_POST['birthday'];
        if(!$birthday)
            sendReponsive(400,array("message"=>"birthday bị trống"));
        else
            if(updateInformationUserBirthday($token,$birthday)){

            }else{
                sendReponsive(400,array("message"=>"Cập nhật dữ liệu thất bại"));
            };

        $sex = $_POST['sex'];
        if(!$sex)
            sendReponsive(400,array("message"=>"Sex bị trống"));
        else
            if(updateInformationUserSex($token,$sex)){

            }else{
                sendReponsive(400,array("message"=>"Cập nhật dữ liệu thất bại"));
            };
        sendReponsive(200,sendDataSuccessful(200,"Cập nhật dữ liệu thành công"));
    }


<?php
    require 'connect.php';
    if(!isset($_POST['data'])){
        sendReponsive(400,array("message"=>"Vui lòng điền đầy đủ thông tin"));
    }
    $data = $_POST['data'];
    if(empty($data)){
        sendReponsive(400,array("message"=>"Vui lòng điền đầy đủ thông tin"));
    }
    $arrData = json_decode($data,true);
    backupVay($arrData);


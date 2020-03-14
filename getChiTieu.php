<?php
    require 'connect.php';
    if(!isset($_POST['token'])){
        sendReponsive(404,array("message"=>"Vui lòng điền đầy đủ thông tin"));
    }
    $token = $_POST['token'];
    getAllChiTiet($token);


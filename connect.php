<?php

/*
 * PHẦN NÀY LÀ CÁC HÀM ĐỂ KẾT NỐI VÀ NGẮT KẾT NỐI VỚI DATABASE
 */
// đây là biến toán cục
global $connect;
// hàm kết nối tớ database
function connect_db($databaseName){
    // gọi tới biến toàn cục
    global $connect;

    if(!$connect){
        // kết nối
        $connect = mysqli_connect("localhost","root","",$databaseName) or die("Lỗi kết nối");
        // kiểu mã hóa luồng dữ liệu gửi đi
        mysqli_set_charset($connect,"utf8");
    }

}
function getConnect($databaseName){
    if($databaseName){
        // kết nối
        $conn = mysqli_connect("localhost","root","",$databaseName) or die("Lỗi kết nối");
        // kiểu mã hóa luồng dữ liệu gửi đi
        return $conn;
    }
}
// hàm ngắt kết nối với database
function disconnect_db(){
    global $connect;
    // ngắt kết nói với database
    if($connect){
        mysqli_close($connect);
    }
}
//======================================================
/*
 * ĐÂY LÀ CÁC HÀM THAO TÁC VỚI BẢN LOGININFOR CỦA NGƯỜI DÙNG
 */
// hàm thêm thông tin đăng nhập của 1 người dùng ( Đăng ký )
function insertLoginInfor($nickname, $password){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $query = "INSERT INTO logininfor(nickname,pass,token,create_time) VALUES (?,?,?,?)";
    $stmt = mysqli_prepare($connect,$query);
    mysqli_stmt_bind_param($stmt,"ssss",$var2, $var3, $var4, $var5);
    $var2 = $nickname;
    $var3 = $password;
    $var4 = createToken($nickname,$password);
    $date = new DateTime("now");
    $var5 = date_format($date,"Y/m/d H:m:s");
    if(mysqli_stmt_execute($stmt)){
        return $var4;
    }else{
        sendReponsive(400,array("message"=>"Lỗi thêm dữ liệu (Tên đăng nhập đã tồn tại)"));
        return "";
    }
}
// hàm thanh đổi mật khẩu của người dùng
function changePassword($nickname,$oldPassword,$newPassword){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    if(checkPass($nickname,$oldPassword)){
        $query = "UPDATE logininfor SET password = ? WHERE nickname = ?";
        $stmt = mysqli_prepare($connect,$query);
        mysqli_stmt_bind_param($stmt,"ss",$newPassword,$nickname);
        $reponsive = mysqli_stmt_execute($stmt);
        if($reponsive){
            sendReponsive(200,sendDataSuccessful(200,"Cập nhật mật khẩu thành công"));
        }else{
            sendReponsive(400,array("message"=>"Lỗi cập nhật dữ liệu"));
        }
    }else{
        sendReponsive(400,array("message"=>"Tên đăng nhập hoặc mật khẩu không đúng"));
    }
}
// hàm kiểm tra mật khẩu của người dùng có đúng không
function checkPass($nickname,$password){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $queryCheckPassword = "SELECT logininfor.password FROM logininfor WHERE nickname = ? AND password = ?";
    $stmtCheckPassword = mysqli_prepare($connect,$queryCheckPassword);
    mysqli_stmt_bind_param($stmtCheckPassword,"ss",$nickname,$password);
    $reponsive = mysqli_stmt_execute($stmtCheckPassword);
    mysqli_stmt_bind_result($stmtCheckPassword,$oldPassword);
    if($reponsive){
        $resuft = array();
        while (mysqli_stmt_fetch($reponsive)){
            $resuft['password'] = $password;
        }
        if(count($reponsive)!=1){
            return false;
        }else
            return true;
    }else{
        return false;
    }
}
// hàm kiểm tra đăng nhập ( hoặc lấy token người dùng để đăng nhập )
function loginCheck($nickname, $passowrd){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $query = "SELECT * FROM logininfor WHERE nickname = ? AND pass= ?";
    $stmt = mysqli_prepare($connect,$query);
    mysqli_stmt_bind_param($stmt,"ss",$var1,$var2);
    $var1 = $nickname;
    $var2 = $passowrd;
    $re = mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $nickname1 ,$passowrd1,$token,$create_time);
    if($re){
        $reponsive = array();
        while (mysqli_stmt_fetch($stmt)){
            $repon = array(
                'nickname'=>$nickname1,
                'password'=>$passowrd1,
                'access_token'=>$token,
                'create_time'=>$create_time
            );
            return $repon;
        }
        sendReponsive(400,array("message"=>"Tên đăng nhập hoặc mật khẩu không đúng"));
    }else{
        sendReponsive(400,array("message"=>"Lỗi truy xuất dữ liệu người dùng"));
    }

}
//get nickname by token
function getNickname($token){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $queryCheckPassword = "SELECT logininfor.nickname FROM logininfor WHERE token = '$token'";
    $reponsive = mysqli_query($connect,$queryCheckPassword);
    if($reponsive){
        while ($row = mysqli_fetch_array($reponsive)){
            return $row['nickname'];
        }
        sendReponsive(400,array("message"=>"Access token lỗi"));
    }else{
        sendReponsive(400,array("message"=>"Lỗi truy xuất dữ liệu người dùng"));
    }
}
//check email
function checkEmail($email){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $queryCheckPassword = "SELECT InformationUser.Emailaddress FROM InformationUser WHERE  Emailaddress = '$email'";
    $data = mysqli_query($connect,$queryCheckPassword);
    if($data){
        $resuft = array();
        while ($row = mysqli_fetch_array($data)){
            $resuft[] = $row['Emailaddress'];
        }
        if(count($resuft)>0){
            return true;
        }else
            return false;
    }else{
        return false;
    }
}

//======================================================

/*
 * ĐÂY LÀ CÁC HÀM THAO TÁC VỚI BẢNG INFORUSER
 */
function insertInformationUser($data){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $nickname = $data['nickname'];
    $sex = $data['sex'];
    $email = $data['email'];
    $create_time = getTimeNow();
    $query = "INSERT INTO InformationUser(nickname,sex,Emailaddress,create_time) VALUES ( '$nickname',$sex,'$email','$create_time')";
    if(mysqli_query($connect,$query)){
        $arr = array(
            'nickname'=>$nickname,
            'sex'=>$sex,
            'email'=>$email,
            'create_time'=>$create_time
        );
         return $arr;
    }else{
        sendReponsive(400,array("message"=>"Lỗi thêm dữ liệu người dùng"));
    };

}
function updateInformationUserEmail($token,$email){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $nickname = getNickname($token);
    $query = "UPDATE InformationUser SET Emailaddress = '$email' WHERE nickname = '$nickname'" ;
    return mysqli_query($connect,$query);
}
function updateInformationUserBirthday($token,$birthday){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $nickname = getNickname($token);
    $query = "UPDATE InformationUser SET birthday = '$birthday' WHERE nickname = '$nickname'" ;
    return mysqli_query($connect,$query);
}
function updateInformationUserSex($token,$sex){
    global $connect;
    if(!$connect){
        connect_db("thongtinnguoidung");
    }
    $nickname = getNickname($token);
    $query = "UPDATE InformationUser SET sex = $sex WHERE nickname = '$nickname'" ;
    return mysqli_query($connect,$query);
}
//==================================================
/*
 * ĐÂY LÀ CÁC HÀM THAO TÁC VỚI BẢNG THÔNG TIN CHI TIÊU
 */
function createTableChiTieu($nickname){
    $conn = getConnect('thongtinchitieu');
    $query = "CREATE TABLE  $nickname (ID INTEGER PRIMARY KEY , sotien DOUBLE, loaigiaodich VARCHAR(200), ghichugiaodich VARCHAR(150), thoigiangiaodich DATETIME, diadiem VARCHAR(200), soluong INTEGER, backup_time DATETIME )";
    return mysqli_query($conn,$query);
}
function backupChiTieu($data){
    $connect = getConnect("thongtinchitieu");
    $error = array(
        'data'=> array(),
        'error'=>'Thông tin đã tồn tại',
        'create_time'=>getTimeNow()
    );
    $errorCount = 0;
    $nickname = getNickname($data['token']);

    foreach ($data['data'] as $vay){
        $id = $vay['id'];
        $soTien = $vay['sotien'];
        $loaiGiaoDich = $vay['loaigiaodich'];
        $ghiChuGiaoDich = $vay['ghichugiaodich'];
        $thoiGianGiaoDich = $vay['thoigiangiaodich'];
        $diaDiem = $vay['diadiem'];
        $soluong = $vay['soluong'];
        $backup = getTimeNow();
        $query = "INSERT INTO $nickname(ID,soTien,loaiGiaoDich,ghiChuGiaoDich,thoiGianGiaoDich,diaDiem,soluong,backup_time) VALUES 
                (
                $id,
                $soTien,
                '$loaiGiaoDich',
                '$ghiChuGiaoDich',
                '$thoiGianGiaoDich', 
                '$diaDiem',
                 $soluong,
                 '$backup')";
        if(!mysqli_query($connect,$query)){
            $error['data'][] = $vay;
            $errorCount++;
        }
    }
    if($errorCount>0){
        sendReponsive(200,$error);

    }else{
        sendReponsive(200,sendDataSuccessful(200,"Đã cập nhật thành công"));
    }

}
function updateChiTieu($data){
    $connect = getConnect("thongtinchitieu");
    $error = array(
        'data'=> array(),
        'error'=>'Thông tin đã tồn tại',
        'create_time'=>getTimeNow()
    );
    $errorCount = 0;
    $nickname = getNickname($data['token']);

    foreach ($data['data'] as $vay){
        $id = $vay['id'];
        $soTien = $vay['sotien'];
        $loaiGiaoDich = $vay['loaigiaodich'];
        $ghiChuGiaoDich = $vay['ghichugiaodich'];
        $thoiGianGiaoDich = $vay['thoigiangiaodich'];
        $diaDiem = $vay['diadiem'];
        $soluong = $vay['soluong'];
        $backup = getTimeNow();
        $query = "UPDATE $nickname SET soTien = $soTien,loaiGiaoDich = '$loaiGiaoDich', ghiChuGiaoDich = '$ghiChuGiaoDich',thoiGianGiaoDich = '$thoiGianGiaoDich',diaDiem = '$diaDiem',soluong = $soTien,backup_time = '$backup' WHERE  ID = '$id'";
        if(!mysqli_query($connect,$query)){
            $error['data'][] = $vay;
            $errorCount++;
        }
    }
    if($errorCount>0){
        sendReponsive(202,$error);
    }else{
        sendReponsive(200,sendDataSuccessful(200,"Đã cập nhật thành công"));
    }

}
function getAllChiTiet($token){
    $connect = getConnect("thongtinchitieu");
    $nickname = getNickname($token);
    $query = "SELECT * FROM $nickname ";
    $resulf = mysqli_query($connect,$query);
    $returnOut = array("data"=>array(),"create_time"=>getTimeNow());
    $arr = array();
    if($resulf){
        while ($row = mysqli_fetch_array($resulf)){
            $arr['ID'] = $row['ID'];
            $arr['sotien'] = $row['sotien'];
            $arr['loaigiaodich'] = $row['loaigiaodich'];
            $arr['ghichugiaodich'] = $row['ghichugiaodich'];
            $arr['thoigiangiaodich'] = $row['thoigiangiaodich'];
            $arr['diadiem'] = $row['diadiem'];
            $arr['soluong'] = $row['soluong'];
            $arr['backup_time'] = $row['backup_time'];
            $returnOut['data'][] = $arr;
        }
        sendReponsive(200,$returnOut);
    }else{
        sendReponsive(404,array("message"=>"Lỗi truy xuất dữu liệu"));
    }
}
//======================================================
function createTableVay($nickname){
    $conn = getConnect('thongtinvay') ;
    $query ="CREATE TABLE  $nickname (ID INTEGER PRIMARY KEY , sotienvay DOUBLE, sotiendatra DOUBLE,hantra DATETIME ,nguoigiaodich VARCHAR(100), loaigiaodich VARCHAR(200), ghichugiaodich VARCHAR(100),thoigiangiaodich DATETIME,laisuat FLOAT, trangthai VARCHAR(100) ,backup_time DATETIME)";
    return mysqli_query($conn,$query);
}
function backupVay($data){
    $connect = getConnect("thongtinvay");
    $nickname = getNickname($data['token']);
    $error = array(
        'data'=> array(),
        'error'=>'Các thông tin vay đã tồn tại',
        'create_time'=>getTimeNow()
    );
    $errorCount = 0;
    foreach ($data['data'] as $vay){
        $id = $vay['id'];
        $soTienVay = $vay['sotienvay'];
        $soTienDaTra= $vay['sotiendatra'];
        $hantra = $vay['hantra'];
        $nguoiGiaoDich = $vay['nguoigiaodich'];
        $loaiGiaoDich = $vay['loaigiaodich'];
        $ghiChuGiaoDich = $vay['ghichugiaodich'];
        $thoiGianGiaoDich=$vay['thoigiangiaodich'];
        $laiSuat = $vay['laisuat'];
        $trangThai = $vay['trangthai'];
        $backup = getTimeNow();
        $query = "INSERT INTO $nickname(ID,sotienvay,sotiendatra,hantra,nguoigiaodich,loaigiaodich,ghichugiaodich,thoigiangiaodich,laisuat,trangthai,backup_time) 
                VALUES (
                $id,
                 $soTienVay ,
                 $soTienDaTra ,
                 '$hantra',
                 '$nguoiGiaoDich', 
                 '$loaiGiaoDich',
                 '$ghiChuGiaoDich',
                 '$thoiGianGiaoDich',
                 $laiSuat,
                 '$trangThai',
                 '$backup'
                 )";
        //echo  $query;
        if(!mysqli_query($connect,$query)){
            $error['data'][] = $vay;
            $errorCount++;
        }
    }
    if($errorCount>0){
        sendReponsive(200,$error);
    }else{
        sendReponsive(200,sendDataSuccessful(200,"Đã cập nhật thành công"));
    }
}
function updateVay($data){
    $connect = getConnect("thongtinvay");
    $nickname = getNickname($data['token']);
    $error = array(
        'data'=> array(),
        'error'=>'Lỗi thêm thông tin vay',
        'create_time'=>getTimeNow()
    );
    $errorCount = 0;
    foreach ($data['data'] as $vay){
        $id = $vay['id'];
        $soTienVay = $vay['sotienvay'];
        $soTienDaTra= $vay['sotiendatra'];
        $hantra = $vay['hantra'];
        $nguoiGiaoDich = $vay['nguoigiaodich'];
        $loaiGiaoDich = $vay['loaigiaodich'];
        $ghiChuGiaoDich = $vay['ghichugiaodich'];
        $thoiGianGiaoDich=$vay['thoigiangiaodich'];
        $laiSuat = $vay['laisuat'];
        $trangThai = $vay['trangthai'];
        $backup = getTimeNow();
        $query = "UPDATE $nickname SET soTienVay = $soTienVay, soTienDaTra = $soTienDaTra , hantra = '$hantra' , nguoiGiaoDich = '$nguoiGiaoDich',loaiGiaoDich = '$loaiGiaoDich',ghiChuGiaoDich = '$ghiChuGiaoDich',thoiGianGiaoDich = '$thoiGianGiaoDich',laiSuat = $laiSuat,trangThai = '$trangThai', backup_time = '$backup' WHERE ID = '$id'";
        //echo  $query;
        if(!mysqli_query($connect,$query)){
            $error['data'][] = $vay;
            $errorCount++;
        }
    }
    if($errorCount>0){
        sendReponsive(202,$error);
    }else{
        sendReponsive(200,sendDataSuccessful(200,"Đã cập nhật thành công"));
    }
}
function getAllVay($token){
    $connect = getConnect("thongtinvay");
    $nickname = getNickname($token);
    $query = "SELECT * FROM $nickname ";
    $resulf = mysqli_query($connect,$query);
    $returnOut = array("data"=>array(),"create_time"=>getTimeNow());
    $arr = array();
    if($resulf){
        while ($row = mysqli_fetch_array($resulf)){
            $arr['ID'] = $row['ID'];
            $arr['sotienvay'] = $row['sotienvay'];
            $arr['sotiendatra'] = $row['sotiendatra'];
            $arr['hantra'] = $row['hantra'];
            $arr['nguoigiaodich'] = $row['nguoigiaodich'];
            $arr['loaigiaodich'] = $row['loaigiaodich'];
            $arr['ghichugiaodich'] = $row['ghichugiaodich'];
            $arr['thoigiangiaodich'] = $row['thoigiangiaodich'];
            $arr['laisuat'] = $row['laisuat'];
            $arr['trangthai'] = $row['trangthai'];
            $arr['backup_time'] = $row['backup_time'];
            $returnOut['data'][] = $arr;
        }
        sendReponsive(200,$returnOut);
    }else{
        sendReponsive(404,array("message"=>"Lỗi truy xuất dữu liệu"));
    }
}

/*
 * ĐÂY LÀ CÁC HÀM ĐỂ MÃ HÓA
 */
// hàm tạo token đăng nhập
function createToken($nickname, $password){
    return encodeStringByMe(md5($nickname)."|".md5($password)."//".md5(time()));
}
// hàm mã hóa token theo kiểu mã hóa DT1200
function encodeStringByMe($str){
    $arr_str =explode(" ","a b c d e f g h i j k l m n o p q r s t u v w x y z");
    $arr_encode = explode(" ","21 22 23 31 32 33 41 42 43 51 52 53 61 62 63 71 72 73 74 81 82 83 91 92 93 94");
    $a = $str;
    for($i=0;$i<26;$i++){
        $item1= $arr_str[$i];
        $item2 = $arr_encode[$i];
        $a = str_replace($item1,$item2,$a);
    }
    return $a;
}
//======================================================
/*
 * ĐÂY LÀ CÁC HÀM LẤY ĐỮ LIỆU GỬI VỀ USER
 */
// hàm lấy ra Message của luỗ từ status code
function getStatusCodeMeeage($status){
    $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );
    return (isset($codes[$status])) ? $codes[$status] : ”;
}
// hàm lấy lỗi cụ thể
function getError($status){
    $mess = array(
        1001=>"Vui lòng nhập đủ dữ liệu",
        1010=>"Tên đăng nhập hoặc tài khoản không đúng",
        1100=>"Tên đăng nhập đã tồn tại",
        1002=>"Access token không chính xác vui lòng thử lại",
        1020=>"Lỗi truy cập dữ liệu vui lòng thử lại",
        1200=>"Lỗi thêm dữ liệu vui lòng thử lại",
        1201=>"Dữ liệu rỗng"
    );
    return $mess[$status];
}
// hàm gửi dữ liệu cho người dùng
function sendReponsive($status,$data){
    $status_header = "HTTP/1.1 $status ". getStatusCodeMeeage($status);
    header($status_header);
    exit(json_encode($data));
}
// hàm cài đặt data gửi đi thành công
function sendDataSuccessful($status,$data){
    $da = new DateTime('now');
    $re = array(
        'status_code'=>$status,
        'status_message'=>"ok",
        'output'=>$data,
        'time'=>date_format($da,"Y/m/d H:m:s")
    );
    return $re;
}
// hàm gửi đi thông báo lỗi
function jsonReponsiveError($status){
    $date = getTimeNow();
    $error = array(
        "status_code"=>$status,
        "message"=>getError($status),
        "time"=> $date
    );
    return $error;
}
function jsonReponsiveErrorMess($status,$mess){
    $date = getTimeNow();
    $error = array(
        "status_code"=>$status,
        "message"=>$mess,
        "time"=> $date
    );
    return $error;
}
// lấy ngày giwof hiện tại
function getTimeNow(){
    $date = new DateTime('now');
    $st = date_format($date,"Y/m/d H:m:s");
    return $st;
}
//======================================================


?>


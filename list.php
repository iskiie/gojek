<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
include "method.php";

$file  = fopen("token.txt", "r") or die("File tidak ada...");
$total = count(file("token.txt"));
for ($i=0; $i < $total; $i++) { 
    $token = fgets($file);
    echo color("while", "[EXE] ");
    echo color("yellow", "Mengecek akun dengan token : $token");
    $mydata      = request("/wallet/profile/detailed", $token);
    $myname      = fetch_value($mydata,'"name":"','",');
    $mynumber    = fetch_value($mydata,'"mobile":"','",');
    $mybalance   = fetch_value($mydata,'"balance":',',');
    echo color("while", "[ - ] ");
    echo color("yellow", "Nama : $myname\n");
    echo color("while", "[ - ] ");
    echo color("yellow", "Nomor telfon : $mynumber\n");
    echo color("while", "[ - ] ");
    echo color("yellow", "Saldo gopay : $mybalance\n");

    $cekvoucher  = request('/gopoints/v3/wallet/vouchers?limit=10&page=1', $token);
    $totalvocher = fetch_value($cekvoucher,'"total_vouchers":',',');
    $real        = $totalvocher-1;
    if ($real == "-1") {
        $valvoc = "0";
    } else {
        $valvoc = $real;
    }

    echo color("green","[SUC] Total vocher yang di dapat : $valvoc\n");

    if ($valvoc == "0") {
        sleep(1);
        echo color("blue","[GET] ");
        echo color("blue","Mencoba mendapatkan vouchers...\n");
        request('/go-promotions/v1/promotions/enrollments', $token, '{"promo_code":"PESANGOFOOD2107"}');
        sleep(10);
        request('/go-promotions/v1/promotions/enrollments', $token, '{"promo_code":"COBAGOFOOD2107"}');
        $cekvoucher  = request('/gopoints/v3/wallet/vouchers?limit=10&page=1', $token);
    }

    for($j=0; $j<$totalvocher; $j++){
        $voucher = getStr1('"title":"','",',$cekvoucher, $j);
        if ($j == 0) { } else {
            echo color("white","[ $j ] ");
            echo color("green","$voucher\n");
        }
        sleep(1);
    }
    sleep(3);
} 
fclose($file);
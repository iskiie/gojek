<?php
date_default_timezone_set('Asia/Jakarta');
include "method.php";
ulang:
// function change(){
$nama  = nama();
$email = str_replace(" ", "", $nama) . mt_rand(100, 999);
echo color("white", "[EXE] ");
echo color("blue", "Input nomor : ");
// $no = trim(fgets(STDIN));
$nohp = trim(fgets(STDIN));
$nohp = str_replace("62", "62", $nohp);
$nohp = str_replace("(", "", $nohp);
$nohp = str_replace(")", "", $nohp);
$nohp = str_replace("-", "", $nohp);
$nohp = str_replace(" ", "", $nohp);

if (!preg_match('/[^+0-9]/', trim($nohp))) {
    if (substr(trim($nohp), 0, 3) == '62') {
        $hp = trim($nohp);
    } else if (substr(trim($nohp), 0, 1) == '0') {
        $hp = '62' . substr(trim($nohp), 1);
    } elseif (substr(trim($nohp), 0, 2) == '62') {
        $hp = '6' . substr(trim($nohp), 1);
    } else {
        $hp = '1' . substr(trim($nohp), 0, 13);
    }
}

$data     = '{"email":"' . $email . '@gmail.com","name":"' . $nama . '","phone":"+' . $hp . '","signed_up_country":"ID"}';
$register = request("/v5/customers", null, $data);
if (strpos($register, '"otp_token"')) {
    $otptoken = getStr('"otp_token":"', '"', $register);
    echo color("green", "[SUC] Kode OTP telah di kirim...\n");
    sleep(1);
	otp:
	echo color("white", "[EXE] ");
    echo color("blue", "Masukkan OTP 4 digit : ");
    $otp   = trim(fgets(STDIN));
    $data1 = '{"client_name":"gojek:cons:android","data":{"otp":"' . $otp . '","otp_token":"' . $otptoken . '"},"client_secret":"83415d06-ec4e-11e6-a41b-6c40088ab51e"}';
    $verif = request("/v5/customers/phone/verify", null, $data1);
    if (strpos($verif, '"access_token"')) {
    	echo color("green", "[SUC] Berhasil mendaftar\n");
        $token = getStr('"access_token":"', '"', $verif);
        $uuid  = getStr('"resource_owner_id":', ',', $verif);
        echo color("white", "[EXE] ");
        echo color("yellow", "Kode access token kamu : " . $token . "\n");
        save("token.txt", $token);
        cobalagi:
        sleep(10);
        $code1   = request('/go-promotions/v1/promotions/enrollments', $token, '{"promo_code":"PESANGOFOOD2107"}');
        $message = fetch_value($code1, '"message":"', '"');
        if (strpos($code1, 'Promo kamu sudah bisa dipakai')) {
            echo color("green", "[SUC] ");
	        echo color("yellow", $message."\n");
            $cekvoucher  = request('/gopoints/v3/wallet/vouchers?limit=10&page=1', $token);
            $totalvocher = fetch_value($cekvoucher,'"total_vouchers":',',');
            $real        = $totalvocher-1;
            echo color("green","[SUC] Total vocher yang di dapat : $real\n");
            for($j=0; $j<$totalvocher; $j++){
            	$voucher = getStr1('"title":"','",',$cekvoucher, $j);
                if ($j == 0) { } else {
		            echo color("white","[ $j ] ");
		            echo color("green","$voucher\n");
		        }
		        sleep(1);
            }
            goto setpin;
        } else {
        	echo color("white", "[EXE] ");
	        echo color("yellow", "Mencoba mendapatkan voucher\n");
	        sleep(10);
        	$code2   = request('/go-promotions/v1/promotions/enrollments', $token, '{"promo_code":"COBAGOFOOD2107"}');
        	$message = fetch_value($code2, '"message":"', '"');
        	if (strpos($code2, 'Promo kamu sudah bisa dipakai')) {
	            echo color("green", "[SUC] ");
	            echo color("yellow", $message."\n");
	            sleep(1);
	            $cekvoucher = request('/gopoints/v3/wallet/vouchers?limit=10&page=1', $token);
	            $totalvocher = fetch_value($cekvoucher,'"total_vouchers":',',');
	            $real        = $totalvocher-1;
	            echo color("green","[SUC] Total vocher yang di dapat : $real\n");
	            for($j=0; $j<$totalvocher; $j++){
	            	$voucher = getStr1('"title":"','",',$cekvoucher, $j);
	                if ($j == 0) { } else {
			            echo color("white","[ $j ] ");
			            echo color("green","$voucher\n");
			        }
			        sleep(1);
	            }
	            goto setpin;
        	} else {
        		echo color("red", "[ERR] Sebentar, kita coba lagi...\n");
        		goto cobalagi;
        	}
        }
    } else {
    	$pesannya  = fetch_value($verif,'"message":"','",');
    	if ($pesannya == "Kodenya salah lagi, nih. Coba lagi setelah 24 jam, ya.") {
    		echo color("red", "[ERR] $pesannya\n");
    		sleep(1);
        	goto ulang;
    	} else {
    		echo color("red", "[ERR] $pesannya\n");
    		sleep(1);
        	goto otp;
    	}
    }
} else {
	$message = fetch_value($register, '"message":"', '"');
    echo color("red", "[ERR] $message\n");
    sleep(1);
    goto ulang;
}

setpin:
echo color("white", "[EXE] ");
echo color("blue", "Setting PIN gojek? : [y/n]  ");
$pilih1 = trim(fgets(STDIN));
if ($pilih1 == "y" || $pilih1 == "Y") {
    pinulang:
	echo color("white", "[EXE] ");
    echo color("blue", "Masukkan 6 angka sebagai PIN gojek kamu : ");
    $pinsaya 	  = trim(fgets(STDIN));
    $data2        = '{"pin":"'.$pinsaya.'"}';
    $getotpsetpin = request("/wallet/pin", $token, $data2, null, null, $uuid);
    //echo $getotpsetpin; //==================================
    $lapor = fetch_value($getotpsetpin,'"message":"','",');

    if ($lapor == "The pin you provided is invalid, please try again with a new value.") {
        echo color("white", "[EXE] ");
        echo color("yellow", "Pin yang kamu masukkan tidak berlaku, masukkan pin yang lain.\n");
        sleep(1);
        goto pinulang;
    } elseif ($lapor == "Permintaan kamu belum dapat diproses karena PIN kamu sudah aktif.") {
    	echo color("white", "[EXE] ");
        echo color("yellow", "$lapor\n");
        sleep(1);
        goto lanjut;
    } else {
        echo color("white", "[EXE] ");
        echo color("yellow", "$lapor\n");
    }

    setpinotp:
    echo color("white", "[EXE] ");
    echo color("blue", "Masukkan kode OTP 6 digit : ");
    $otpsetpin      = trim(fgets(STDIN));
    $verifotpsetpin = request("/wallet/pin", $token, $data2, null, $otpsetpin, $uuid);
    //echo $verifotpsetpin; //==================================
    $laporan  = fetch_value($verifotpsetpin,'"message_title":"','",');
    $laporan1 = fetch_value($verifotpsetpin,'"message":"','",');
    if ($laporan == "Masukkan OTP") {
        echo color("white", "[EXE] ");
        echo color("yellow", "$laporan1\n");
        sleep(1);
        goto setpinotp;
    } elseif ($laporan == "OTP tidak berlaku") {
        echo color("white", "[EXE] ");
        echo color("yellow", "$laporan1\n");
        sleep(1);
        goto setpinotp;
    } elseif ($laporan == "Masa berlaku OTP sudah habis") {
        echo color("white", "[EXE] ");
        echo color("yellow", "$laporan1\n");
        sleep(1);
        goto setpin;
    } else {
        echo color("green", "[SUC] Kode pin sukses di set.\n");
        sleep(1);
        goto lanjut;
    }
    // ======================================================================== mau kemana?
} else if ($pilih1 == "n" || $pilih1 == "N") {
    goto lanjut;
} else {
	echo color("red", "[ERR] Pilihannya cuma y/n goblok, enggak ada $pilih1...\n");
	goto setpin;
}

lanjut:
echo color("white", "[EXE] ");
echo color("blue", "Lanjut input nomor? : [y/n] ");
$pilih1 = trim(fgets(STDIN));
if ($pilih1 == "y" || $pilih1 == "Y") {
	goto ulang;
} elseif ($pilih1 == "n" || $pilih1 == "N") {
	die();
} else {
	echo color("red", "[ERR] Pilihannya cuma y/n goblok, enggak ada pilihan $pilih1...\n");
	goto lanjut;
}

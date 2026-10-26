<title>latihan jam parkir p6</title>
<?php
    $jam = 49; //ganti jam yg ini coy
    $sisa_jam = $jam % 24;
    $inap = ($jam - $sisa_jam) / 24;
    
    //motor
    $motor_inap = $inap * 20000; 
    $motor_sisa = 0;
    if ($sisa_jam == 1) {
        $motor_sisa = 3000; 
    } elseif ($sisa_jam > 1) {
        $motor_sisa = 3000 + (($sisa_jam - 1) * 2000); 
        if ($motor_sisa > 15000) {
            $motor_sisa = 15000;
        }
    }
    $motor = $motor_inap + $motor_sisa;

    //mobil
    $mobil_inap = $inap * 50000; 
    $mobil_sisa = 0;
    if ($sisa_jam == 1) {
        $mobil_sisa = 5000; 
    } elseif ($sisa_jam > 1) {
        $mobil_sisa = 5000 + (($sisa_jam - 1) * 3000);    
        if ($mobil_sisa > 30000) {
            $mobil_sisa = 30000;
        }
    }
    $mobil = $mobil_inap + $mobil_sisa;

    echo "Parkir Anda : $jam jam <br>";
    echo "Bayaran Motor Anda Rp $motor dan Mobil Rp $mobil";
?>
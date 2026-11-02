<html>
<head>
    <title>Latihan Pertemuan 7 - for each</title>
</head>
<body>
    <br>
    <?php
        $data = [
        ["17.3A.03", "17250049", "Adriyan Sahpitran"],
        ["17.3A.03", "17250072", "Widya Putri Rahayu"], 
        ["17.3A.03", "17250199", "Ridho Firmansyah"],
        ["17.3A.03", "17250206", "Rangga Aji Pramudya"],
        ["17.3A.03", "17250232", "Fernando Hasudungan Siregar"],
        ["17.3A.03", "17250278", "Duta Arrasyid"],
        ["17.3A.03", "17250323", "Glen Justin Maringan Simarmata"],
        ["17.3A.03", "17250313", "Renal Ali Syahid"],
        ["17.3A.03", "17250371", "Arla Maulana Eza Nugraha"],
        ["17.3A.03", "17250374", "Kevin Nabil Noor Rahman"],
        ["17.3A.03", "17250400", "Ahmad Fauzan Rizki"],
        ["17.3A.03", "17250439", "Ahmad Abu Jihad Bilhaq"],
        ["17.3A.03", "17250561", "Intan Purnamasari"],
        ["17.3A.03", "17250571", "Abdillah"],
        ["17.3A.03", "17250513", "Sefti Jikas Mu'afari"],
        ["17.3A.03", "17250654", "Ali Muslih Alfauzan"]
        ];
        
        foreach ($data as $nama) {
            echo "Kelas: " . $nama[0] . " | NIM: " . $nama[1] . " | Nama: " . $nama[2] . "<br>";
        }
    ?>
</body>
</html>
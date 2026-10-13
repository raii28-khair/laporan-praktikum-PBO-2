<?php
// ============================================
// PROGRAM TRANSAKSI SEDERHANA - LAUNDRY RAI
// Input data melalui form web (method POST)
// ============================================

// ---------- FUNCTION BUATAN SENDIRI ----------

// Format angka menjadi rupiah
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Hitung diskon berdasarkan total belanja
function hitungDiskon($total)
{
    if ($total >= 50000) {
        return $total * 0.10;   // diskon 10%
    } elseif ($total >= 25000) {
        return $total * 0.05;   // diskon 5%
    } else {
        return 0;               // tidak ada diskon
    }
}

// Amankan teks sebelum ditampilkan di HTML
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

// ---------- VARIABEL (TIPE DATA BERBEDA) ----------

$namaToko     = "Laundry Rai";   // String
$nomorStruk   = 1001;                          // Integer
$persenPajak  = 0.11;                          // Float (PPN 11%)
$memberAktif  = false;                         // Boolean
$jumlahBaris  = 5;                             // Integer (jumlah baris input barang)
$keranjang    = [];                            // Array
$namaKasir    = "";
$namaPelanggan = "";
$error        = "";

$sudahSubmit = ($_SERVER["REQUEST_METHOD"] === "POST");

// ---------- PROSES DATA DARI FORM ----------

if ($sudahSubmit) {
    $namaKasir     = trim($_POST["kasir"] ?? "");
    $namaPelanggan = trim($_POST["pelanggan"] ?? "");
    $memberAktif   = isset($_POST["member"]);

    // PERULANGAN: membaca setiap baris barang dari form
    for ($i = 0; $i < $jumlahBaris; $i++) {
        $nama   = trim($_POST["nama"][$i] ?? "");
        $harga  = (float) ($_POST["harga"][$i] ?? 0);
        $jumlah = (float) ($_POST["jumlah"][$i] ?? 0);   // boleh desimal (kg)

        // Hanya barang yang lengkap dan valid yang dimasukkan
        if ($nama !== "" && $harga > 0 && $jumlah > 0) {
            $keranjang[] = [
                "nama"   => $nama,
                "harga"  => $harga,
                "jumlah" => $jumlah,
            ];
        }
    }

    // PERCABANGAN: validasi input
    if ($namaKasir === "") {
        $error = "Nama kasir wajib diisi.";
    } elseif (count($keranjang) === 0) {
        $error = "Isi minimal 1 barang dengan harga dan jumlah yang valid.";
    }
}

// ---------- HITUNG TOTAL (jika data valid) ----------

$tampilkanStruk = ($sudahSubmit && $error === "");

if ($tampilkanStruk) {
    $totalBelanja = 0;
    foreach ($keranjang as $item) {
        $totalBelanja += $item["harga"] * $item["jumlah"];
    }

    $diskon = hitungDiskon($totalBelanja);

    // PERCABANGAN: bonus diskon untuk member
    if ($memberAktif) {
        $diskonMember = $totalBelanja * 0.02;
        $statusMember = "Member (bonus diskon 2%)";
    } else {
        $diskonMember = 0;
        $statusMember = "Non-Member";
    }

    $setelahDiskon = $totalBelanja - $diskon - $diskonMember;
    $pajak         = $setelahDiskon * $persenPajak;
    $totalAkhir    = $setelahDiskon + $pajak;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Laundry Rai</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #000000;
            margin: 0;
            padding: 24px;
            color: #222;
        }
        .container { max-width: 760px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        h1 { margin-top: 0; font-size: 22px; color: #1f4e79; }
        h2 { margin-top: 0; font-size: 18px; color: #1f4e79; }
        label { display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px; }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #c4c9d2;
            border-radius: 6px;
            font-size: 14px;
        }
        .row { display: flex; gap: 12px; margin-bottom: 14px; }
        .row > div { flex: 1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; font-size: 14px; }
        th { background: #1f4e79; color: #fff; }
        .barang td { padding: 4px; }
        .checkbox { display: flex; align-items: center; gap: 8px; margin: 14px 0; }
        .checkbox label { margin: 0; }
        button {
            background: #1f4e79;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }
        button:hover { background: #163a5a; }
        .error {
            background: #fde8e8;
            color: #9b1c1c;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 14px;
        }
        .struk td { border-bottom: 1px solid #e3e6ec; }
        .struk .kanan { text-align: right; }
        .ringkasan td { border: none; padding: 4px 8px; }
        .total td { font-weight: bold; font-size: 16px; border-top: 2px solid #1f4e79; }
        .center { text-align: center; color: #555; }
    </style>
</head>
<body>
<div class="container">

    <!-- ================= FORM INPUT ================= -->
    <div class="card">
        <h1><?= e($namaToko) ?></h1>
        <p style="margin-top:-8px;color:#555;">Form Transaksi Layanan Laundry</p>

        <?php if ($error !== ""): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="row">
                <div>
                    <label for="kasir">Nama Kasir</label>
                    <input type="text" id="kasir" name="kasir"
                           value="<?= e($namaKasir) ?>" placeholder="Contoh: Budi">
                </div>
                <div>
                    <label for="pelanggan">Nama Pelanggan (opsional)</label>
                    <input type="text" id="pelanggan" name="pelanggan"
                           value="<?= e($namaPelanggan) ?>" placeholder="Contoh: Siti">
                </div>
            </div>

            <label>Daftar Layanan Laundry</label>
            <table class="barang">
                <tr>
                    <th>Jenis Layanan</th>
                    <th>Harga per Kg/Pcs (Rp)</th>
                    <th>Berat/Jumlah (kg/pcs)</th>
                </tr>
                <?php for ($i = 0; $i < $jumlahBaris; $i++): ?>
                    <tr>
                        <td>
                            <input type="text" name="nama[]"
                                   value="<?= e($_POST["nama"][$i] ?? "") ?>"
                                   placeholder="Contoh: Cuci + Setrika Kiloan">
                        </td>
                        <td>
                            <input type="number" name="harga[]" min="0"
                                   value="<?= e($_POST["harga"][$i] ?? "") ?>"
                                   placeholder="7000">
                        </td>
                        <td>
                            <input type="number" name="jumlah[]" min="0" step="0.1"
                                   value="<?= e($_POST["jumlah"][$i] ?? "") ?>"
                                   placeholder="1">
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

            <div class="checkbox">
                <input type="checkbox" id="member" name="member"
                       <?= $memberAktif ? "checked" : "" ?>>
                <label for="member">Pelanggan adalah member (bonus diskon 2%)</label>
            </div>

            <button type="submit">Proses Transaksi</button>
        </form>
    </div>

    <!-- ================= STRUK ================= -->
    <?php if ($tampilkanStruk): ?>
        <div class="card">
            <h2>Struk Pembelian</h2>
            <p>
                No. Struk : <strong><?= e($nomorStruk) ?></strong><br>
                Kasir     : <strong><?= e($namaKasir) ?></strong><br>
                Pelanggan : <strong><?= e($namaPelanggan !== "" ? $namaPelanggan : "-") ?></strong><br>
                Tanggal   : <strong><?= date("d-m-Y H:i") ?></strong>
            </p>

            <table class="struk">
                <tr>
                    <th>No</th>
                    <th>Layanan</th>
                    <th class="kanan">Harga</th>
                    <th class="kanan">Qty</th>
                    <th class="kanan">Subtotal</th>
                </tr>
                <?php $no = 1; ?>
                <?php foreach ($keranjang as $item): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= e($item["nama"]) ?></td>
                        <td class="kanan"><?= formatRupiah($item["harga"]) ?></td>
                        <td class="kanan"><?= $item["jumlah"] ?></td>
                        <td class="kanan"><?= formatRupiah($item["harga"] * $item["jumlah"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <br>
            <table class="ringkasan">
                <tr><td>Total Belanja</td><td class="kanan"><?= formatRupiah($totalBelanja) ?></td></tr>
                <tr><td>Diskon</td><td class="kanan">- <?= formatRupiah($diskon) ?></td></tr>
                <tr><td>Diskon Member</td><td class="kanan">- <?= formatRupiah($diskonMember) ?></td></tr>
                <tr><td>PPN (<?= $persenPajak * 100 ?>%)</td><td class="kanan"><?= formatRupiah($pajak) ?></td></tr>
                <tr class="total"><td>TOTAL BAYAR</td><td class="kanan"><?= formatRupiah($totalAkhir) ?></td></tr>
            </table>

            <p>Status: <strong><?= e($statusMember) ?></strong></p>
            <p class="center">Terima kasih sudah mencuci di Laundry Rai!</p>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
<?php
$hasil = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama_produk"] ?? "");
    $jumlah = filter_var($_POST["jumlah"] ?? "", FILTER_VALIDATE_INT);
    $modal = filter_var($_POST["modal"] ?? "", FILTER_VALIDATE_FLOAT);
    $jual = filter_var($_POST["jual"] ?? "", FILTER_VALIDATE_FLOAT);

    if ($nama === "") {
        $error = "Nama produk harus diisi.";
    } elseif ($jumlah === false || $jumlah < 1) {
        $error = "Jumlah produk minimal 1.";
    } elseif ($modal === false || $modal < 0 || $jual === false || $jual < 0) {
        $error = "Modal dan harga jual harus berupa angka yang tidak negatif.";
    } else {
        $totalModal = $jumlah * $modal;
        $omzet = $jumlah * $jual;
        $untung = $omzet - $totalModal;
        $margin = $omzet > 0 ? ($untung / $omzet) * 100 : 0;

        $hasil = [
            "nama" => $nama,
            "jumlah" => $jumlah,
            "modal_satuan" => $modal,
            "jual_satuan" => $jual,
            "total_modal" => $totalModal,
            "omzet" => $omzet,
            "untung" => $untung,
            "margin" => $margin
        ];
    }
}

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HPP Studio — Hitung Modal & Keuntungan</title>
    <style>
        :root {
            --bg: #090b09;
            --panel: #141814;
            --panel2: #1d241e;
            --green: #1ed760;
            --green-dark: #169c45;
            --white: #f6f8f6;
            --muted: #a7b0a8;
            --line: #2b342c;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: linear-gradient(150deg, #18251a 0, var(--bg) 36%, #080908 100%);
            color: var(--white);
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
        }
        .layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 230px; flex-shrink: 0; padding: 28px 18px;
            background: rgba(5, 7, 5, .94); border-right: 1px solid #202620;
        }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 21px; font-weight: 800; margin: 0 8px 38px; }
        .brand-icon {
            width: 34px; height: 34px; display: grid; place-items: center;
            border-radius: 50%; background: var(--green); color: #061108; font-size: 20px;
        }
        .nav-label { color: #778178; font-size: 11px; font-weight: bold; letter-spacing: 1.5px; margin: 0 10px 12px; }
        .nav-item {
            display: flex; align-items: center; gap: 13px; padding: 13px 12px;
            margin-bottom: 5px; border-radius: 8px; color: var(--muted); text-decoration: none; font-size: 14px;
        }
        .nav-item.active, .nav-item:hover { background: #1b241c; color: white; }
        .nav-item.active { font-weight: bold; }
        .nav-icon { width: 22px; text-align: center; font-size: 18px; }
        .side-note { margin: 38px 10px 0; color: #768077; font-size: 12px; line-height: 1.7; }
        main { flex: 1; min-width: 0; padding: 25px clamp(18px, 4vw, 58px) 40px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 38px; }
        .back-buttons { display: flex; gap: 8px; }
        .circle-btn { width: 34px; height: 34px; border: 0; border-radius: 50%; background: #070907; color: white; font-size: 18px; }
        .profile { display: flex; align-items: center; gap: 9px; background: #090b09; padding: 6px 12px 6px 6px; border-radius: 30px; font-size: 13px; }
        .avatar { display: grid; place-items: center; background: var(--green); color: #061108; font-weight: bold; border-radius: 50%; width: 28px; height: 28px; }
        .hero {
            border-radius: 18px; padding: clamp(24px, 5vw, 48px);
            background: linear-gradient(120deg, #24492d 0%, #162b1b 48%, #111713 100%);
            border: 1px solid #315239; overflow: hidden; position: relative;
        }
        .eyebrow { color: #c3f5ce; text-transform: uppercase; letter-spacing: 2px; font-size: 11px; font-weight: bold; }
        h1 { font-size: clamp(30px, 5vw, 48px); line-height: 1.08; margin: 15px 0 14px; letter-spacing: -1.4px; }
        h1 span { color: var(--green); }
        .hero p { color: #c6d3c8; line-height: 1.7; max-width: 570px; margin: 0 0 24px; font-size: 14px; }
        .hero-mark { position: absolute; right: 5%; top: 15%; font-size: clamp(70px, 12vw, 140px); opacity: .12; transform: rotate(-12deg); }
        .hero-link { display: inline-block; padding: 13px 22px; border-radius: 28px; color: #061108; background: var(--green); text-decoration: none; font-weight: bold; font-size: 13px; }
        .hero-link:hover, .submit:hover { background: #53eb82; transform: scale(1.02); }
        .section-head { display: flex; justify-content: space-between; align-items: end; gap: 12px; margin: 32px 0 17px; }
        h2 { font-size: 23px; margin: 0 0 7px; letter-spacing: -.5px; }
        .section-head p { color: var(--muted); font-size: 13px; margin: 0; }
        .badge { color: var(--green); font-size: 11px; background: #17271a; border: 1px solid #29452e; padding: 7px 10px; border-radius: 20px; white-space: nowrap; }
        .work-grid { display: grid; grid-template-columns: minmax(280px, .95fr) minmax(300px, 1.05fr); gap: 18px; align-items: start; }
        .panel { background: rgba(20, 24, 20, .96); border: 1px solid var(--line); border-radius: 15px; padding: 24px; }
        .panel h3 { margin: 0 0 7px; font-size: 18px; }
        .panel-sub { color: var(--muted); font-size: 12px; line-height: 1.6; margin: 0 0 23px; }
        label { display: block; font-size: 12px; color: #d8dfd9; margin: 16px 0 8px; }
        input {
            width: 100%; background: #0a0d0a; color: white; border: 1px solid #354036;
            border-radius: 9px; padding: 13px 14px; font-size: 14px; outline: none;
        }
        input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(30,215,96,.1); }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .hint { color: #849087; font-size: 11px; margin-top: 7px; }
        .submit { width: 100%; border: 0; background: var(--green); color: #071009; border-radius: 30px; padding: 15px; font-weight: 800; font-size: 14px; margin-top: 23px; cursor: pointer; transition: .2s; }
        .error { background: #3a1b1b; color: #ffc7c7; border: 1px solid #6d3030; padding: 12px; border-radius: 9px; font-size: 13px; margin-bottom: 14px; }
        .empty { min-height: 330px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 25px; }
        .empty-icon { width: 66px; height: 66px; border-radius: 50%; background: #203324; color: var(--green); display: grid; place-items: center; font-size: 30px; margin-bottom: 18px; }
        .empty h3 { margin: 0 0 8px; }
        .empty p { color: var(--muted); font-size: 13px; line-height: 1.7; max-width: 270px; margin: 0; }
        .result-top { background: linear-gradient(120deg, #21452b, #17231a); padding: 21px; border-radius: 12px; margin-bottom: 13px; }
        .result-top .small { color: #c4d8c8; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        .product-name { font-size: 23px; font-weight: 800; margin: 8px 0 13px; overflow-wrap: anywhere; }
        .big-number { color: var(--green); font-size: clamp(26px, 4vw, 35px); font-weight: 800; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 11px; }
        .stat { background: #0c0f0c; border: 1px solid #222a23; border-radius: 11px; padding: 16px; min-width: 0; }
        .stat-label { color: var(--muted); font-size: 12px; margin-bottom: 9px; }
        .stat-value { font-size: clamp(15px, 2vw, 19px); font-weight: bold; overflow-wrap: anywhere; }
        .positive { color: var(--green); } .negative { color: #ff8d8d; }
        .explain { color: #8f9b91; font-size: 11px; line-height: 1.7; margin: 16px 2px 0; }
        .tips { display: grid; grid-template-columns: repeat(3, 1fr); gap: 13px; margin-top: 18px; }
        .tip { background: #111611; border: 1px solid #242d25; border-radius: 12px; padding: 17px; }
        .tip-icon { color: var(--green); font-size: 20px; margin-bottom: 10px; }
        .tip strong { display: block; font-size: 13px; margin-bottom: 7px; }
        .tip p { color: var(--muted); font-size: 12px; line-height: 1.6; margin: 0; }
        footer { border-top: 1px solid #202620; margin-top: 35px; padding-top: 19px; color: #788279; font-size: 11px; display: flex; justify-content: space-between; gap: 12px; }
        @media (max-width: 900px) {
            .sidebar { width: 76px; padding: 22px 10px; }
            .brand { margin: 0 9px 35px; } .brand-text, .nav-text, .nav-label, .side-note { display: none; }
            .nav-item { justify-content: center; padding: 13px 8px; } .nav-icon { font-size: 20px; }
            .work-grid { grid-template-columns: 1fr; }
            .empty { min-height: 220px; }
        }
        @media (max-width: 560px) {
            .sidebar { display: none; }
            main { padding: 18px 14px 28px; }
            .topbar { margin-bottom: 20px; }
            .hero { padding: 25px 21px; }
            .hero-mark { right: 1%; top: 20%; }
            .panel { padding: 18px; }
            .two-col { gap: 9px; }
            .tips { grid-template-columns: 1fr; }
            .section-head { align-items: start; flex-direction: column; }
            footer { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand"><span class="brand-icon">♫</span><span class="brand-text">HPP Studio</span></div>
        <div class="nav-label">MENU UTAMA</div>
        <a class="nav-item active" href="#beranda"><span class="nav-icon">⌂</span><span class="nav-text">Beranda</span></a>
        <a class="nav-item" href="#hitung"><span class="nav-icon">▦</span><span class="nav-text">Hitung HPP</span></a>
        <a class="nav-item" href="#panduan"><span class="nav-icon">ⓘ</span><span class="nav-text">Panduan</span></a>
        <div class="side-note">Kelola modal dan harga jual produkmu dengan lebih mudah.</div>
    </aside>

    <main>
        <div class="topbar">
            <div class="back-buttons"><button class="circle-btn" aria-label="Kembali" onclick="history.back()">‹</button><button class="circle-btn" aria-label="Lanjut" onclick="history.forward()">›</button></div>
            <div class="profile"><span class="avatar">H</span><span>Pengguna</span></div>
        </div>

        <section class="hero" id="beranda">
            <div class="hero-mark">♫</div>
            <div class="eyebrow">Teman hitung usahamu</div>
            <h1>Hitung modal.<br><span>Kenali keuntungan.</span></h1>
            <p>Catat biaya produk, tentukan harga jual, lalu lihat hasil perhitungan HPP, omzet, keuntungan, dan margin dalam satu halaman.</p>
            <a class="hero-link" href="#hitung">MULAI MENGHITUNG ↗</a>
        </section>

        <div class="section-head" id="hitung">
            <div><h2>Kalkulator HPP</h2><p>Masukkan data produk untuk menghitung hasilnya.</p></div>
            <span class="badge">● SIAP DIGUNAKAN</span>
        </div>

        <div class="work-grid">
            <section class="panel">
                <h3>Data Produk</h3>
                <p class="panel-sub">Isi semua kolom sesuai data produk yang ingin kamu hitung.</p>
                <?php if ($error !== ""): ?>
                    <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
                <?php endif; ?>
                <form method="POST" action="#hasil">
                    <label for="nama_produk">Nama Produk</label>
                    <input id="nama_produk" name="nama_produk" type="text" placeholder="Contoh: Kopi Susu" value="<?= htmlspecialchars($_POST["nama_produk"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>

                    <label for="jumlah">Jumlah Produk (pcs)</label>
                    <input id="jumlah" name="jumlah" type="number" min="1" step="1" placeholder="Contoh: 20" value="<?= htmlspecialchars($_POST["jumlah"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>

                    <div class="two-col">
                        <div>
                            <label for="modal">Modal per Produk (Rp)</label>
                            <input id="modal" name="modal" type="number" min="0" step="any" placeholder="Contoh: 13000" value="<?= htmlspecialchars($_POST["modal"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>
                        </div>
                        <div>
                            <label for="jual">Harga Jual / Produk (Rp)</label>
                            <input id="jual" name="jual" type="number" min="0" step="any" placeholder="Contoh: 20000" value="<?= htmlspecialchars($_POST["jual"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>
                        </div>
                    </div>
                    <div class="hint">Modal dan harga jual diisi untuk satu produk, bukan total semuanya.</div>
                    <button class="submit" type="submit">♫ &nbsp; HITUNG SEKARANG</button>
                </form>
            </section>

            <section class="panel" id="hasil">
                <?php if ($hasil !== null): ?>
                    <div class="result-top">
                        <div class="small">Hasil perhitungan untuk</div>
                        <div class="product-name"><?= htmlspecialchars($hasil["nama"], ENT_QUOTES, "UTF-8") ?></div>
                        <div class="small">HPP / modal per produk</div>
                        <div class="big-number"><?= rupiah($hasil["modal_satuan"]) ?></div>
                    </div>
                    <div class="stat-grid">
                        <div class="stat"><div class="stat-label">Total Modal</div><div class="stat-value"><?= rupiah($hasil["total_modal"]) ?></div></div>
                        <div class="stat"><div class="stat-label">Omzet</div><div class="stat-value"><?= rupiah($hasil["omzet"]) ?></div></div>
                        <div class="stat"><div class="stat-label"><?= $hasil["untung"] >= 0 ? "Keuntungan" : "Kerugian" ?></div><div class="stat-value <?= $hasil["untung"] >= 0 ? "positive" : "negative" ?>"><?= rupiah(abs($hasil["untung"])) ?><?= $hasil["untung"] < 0 ? " (rugi)" : "" ?></div></div>
                        <div class="stat"><div class="stat-label">Margin</div><div class="stat-value <?= $hasil["margin"] >= 0 ? "positive" : "negative" ?>"><?= number_format($hasil["margin"], 1, ",", ".") ?>%</div></div>
                        <div class="stat"><div class="stat-label">Jumlah</div><div class="stat-value"><?= number_format($hasil["jumlah"], 0, ",", ".") ?> pcs</div></div>
                        <div class="stat"><div class="stat-label">Harga Jual / Produk</div><div class="stat-value"><?= rupiah($hasil["jual_satuan"]) ?></div></div>
                    </div>
                    <p class="explain">Keuntungan dihitung dari omzet dikurangi total modal. Margin dihitung dari keuntungan dibagi omzet, lalu dikali 100%.</p>
                <?php else: ?>
                    <div class="empty">
                        <div class="empty-icon">▦</div>
                        <h3>Hasilmu akan muncul di sini</h3>
                        <p>Isi nama produk, jumlah, modal per produk, dan harga jual. Tekan tombol hitung untuk melihat ringkasan keuanganmu.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <section id="panduan">
            <div class="section-head"><div><h2>Kenali hasil hitunganmu</h2><p>Penjelasan singkat setiap bagian.</p></div></div>
            <div class="tips">
                <div class="tip"><div class="tip-icon">▤</div><strong>HPP / Modal Produk</strong><p>Biaya modal untuk membuat atau mendapatkan satu produk.</p></div>
                <div class="tip"><div class="tip-icon">↗</div><strong>Omzet</strong><p>Total uang penjualan, yaitu jumlah produk dikali harga jual.</p></div>
                <div class="tip"><div class="tip-icon">✦</div><strong>Keuntungan</strong><p>Omzet dikurangi total modal. Jika hasilnya negatif, berarti masih rugi.</p></div>
            </div>
        </section>
        <footer><span>♫ HPP Studio · Aplikasi Perhitungan HPP</span><span>Belajar mengelola usaha dengan lebih teratur.</span></footer>
    </main>
</div>
</body>
</html>

<?php
session_start();

include '../config/db.php';

if (!isset($_SESSION['kasir'])) {
    header("Location: ../auth/login.php");
    exit;
}
$transaksi = null;
$detail = [];
$total = 0;

// Variabel Member & Diskon
$member = null;
$diskon = 0;
$total_akhir = 0;
$pesan_member = '';
$kasir = $_SESSION['kasir'];

// Cari Transaksi
if (isset($_GET['kode'])) {
    $kode = $_GET['kode'];

    // ambil transaksi
    $qTransaksi = mysqli_query(
        $koneksi,
        "SELECT * FROM transaksi WHERE kode_transaksi='$kode' AND status='pending'"
    );
    $transaksi = mysqli_fetch_assoc($qTransaksi);

    if ($transaksi) {
        // ambil detail
        $qDetail = mysqli_query(
            $koneksi,
            "SELECT dt.*, b.nama_barang 
            FROM detail_transaksi dt
            JOIN barang b ON dt.id_barang = b.id_barang
            WHERE dt.kode_transaksi='$kode'"
        );

        while ($row = mysqli_fetch_assoc($qDetail)) {
            $detail[] = $row;
            $total += $row['subtotal'];
        }

        // LOGIKA CEK MEMBER
        if (!empty($_GET['kode_member'])) {
            $kode_member = mysqli_real_escape_string($koneksi, $_GET['kode_member']);
            $qMember = mysqli_query(
                $koneksi,
                "SELECT * FROM member
                WHERE (kode_member='$kode_member'
                OR no_hp='$kode_member')
                AND status='aktif'
                LIMIT 1"
            );
            $member = mysqli_fetch_assoc($qMember);

            if ($member) {
                $diskon = $total * 0.05; // Potongan 5%
                $pesan_member = "<div class='text-success fw-bold small'>✓ Member aktif: {$member['nama_member']} (Diskon 5%)</div>";
            } else {
                $pesan_member = "<div class='text-danger fw-bold small'>✗ Member tidak ditemukan!</div>";
            }
        }
        $total_akhir = $total - $diskon;
    }
}

// --- LOGIKA OTOMATIS GENERATE ID MEMBER KUSTOM (MB001) ---
$next_kode_member = "MB001";

$qCekUrutan = mysqli_query(
    $koneksi,
    "SELECT kode_member
    FROM member
    ORDER BY id_member DESC
    LIMIT 1"
);

if (mysqli_num_rows($qCekUrutan) > 0) {
    $dataMax = mysqli_fetch_assoc($qCekUrutan);
    $angkaUrut = (int) substr(
        $dataMax['kode_member'],
        2
    );
    $angkaUrut++;
    $next_kode_member =
        "MB" . sprintf("%03d", $angkaUrut);
}
?>

<?php
if (isset($_SESSION['success'])) :
    // durasi 5 detik
    $expire = 5;
    if (time() - $_SESSION['success']['time'] > $expire) {
        unset($_SESSION['success']);
    } else {
?>
<div id="alertSuccess" class="alert alert-success mt-3 text-center fw-semibold">
    <?= $_SESSION['success']['message']; ?><br>
    Kembalian: Rp <?= number_format($_SESSION['success']['kembalian']); ?>
</div>
<?php } endif; ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../image/logo-website.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Kasir Point Of Sale</title>

    <style>
    #memberList {
        display: none;
    }
    </style>
</head>

<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">🧾 Kasir (Point Of Sale)</span>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white fs-5">
                    <?= $kasir['nama']; ?>
                </span>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- MAIN -->
    <div class="container-fluid py-3">
        <!-- SEARCH TRANSAKSI -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-10">
                        <input type="text" name="kode" class="form-control form-control-lg"
                            placeholder="🔎 Masukkan kode transaksi" value="<?= $_GET['kode'] ?? ''; ?>" required>
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-primary btn-lg">Cari</button>
                    </div>
                </form>
            </div>
        </div>
        <?php if ($transaksi) : ?>
        <div class="row g-3">
            <!-- LEFT: DETAIL TRANSAKSI -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            🧾 <?= $transaksi['kode_transaksi']; ?>
                        </h5>

                        <!-- WRAPPER SCROLL -->
                        <div style="max-height: 320px; overflow-y: auto; padding-right: 5px;">
                            <div class="list-group mb-3">
                                <?php foreach ($detail as $item) : ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?= $item['nama_barang']; ?></div>
                                        <small class="text-muted">
                                            <?= $item['qty']; ?> x Rp <?= number_format($item['harga']); ?>
                                        </small>
                                    </div>
                                    <span class="fw-bold">
                                        Rp <?= number_format($item['subtotal']); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- TOTAL -->
                        <div class="p-3 bg-light rounded-3">
                            <div class="d-flex justify-content-between">
                                <span>Total</span>
                                <strong>Rp <?= number_format($total); ?></strong>
                            </div>
                            <?php if ($diskon > 0) : ?>
                            <div class="d-flex justify-content-between text-danger">
                                <span>Diskon</span>
                                <span>- Rp <?= number_format($diskon); ?></span>
                            </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between fs-5">
                                <strong>Total Akhir</strong>
                                <strong class="text-primary">
                                    Rp <?= number_format($total_akhir); ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: MEMBER + BAYAR -->
            <div class="col-12 col-lg-5">
                <!-- MEMBER -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">👤 Member</h6>
                        <form method="GET">
                            <input type="hidden" name="kode" value="<?= $kode; ?>">
                            <div class="mb-2">
                                <input type="text" name="kode_member" id="kodeMemberInput"
                                    class="form-control text-uppercase w-100" placeholder="Ketik Kode / Nama / No HP"
                                    autocomplete="off"> <small id="memberStatusMsg"
                                    class="text-danger fw-semibold d-block mt-1"></small>
                                <!-- DROPDOWN -->
                                <div id="memberList"
                                    class="list-group position-absolute w-100 bg-white border shadow-sm"
                                    style="z-index:9999; max-height:250px; overflow-y:auto; display:none;">
                                </div>
                            </div>
                            <div></div>
                            <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#modalDaftarMember">
                                + Daftar Member
                            </button>
                        </form>
                        <div class="mt-2">
                            <?= $pesan_member; ?>
                        </div>
                    </div>
                </div>

                <!-- PAYMENT -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">💳 Pembayaran</h6>
                        <form method="POST" action="../proses/kasir/proses_bayar.php">
                            <input type="hidden" name="kode_transaksi" value="<?= $transaksi['kode_transaksi']; ?>">
                            <input type="hidden" name="total" value="<?= $total_akhir; ?>">
                            <input type="hidden" name="id_member" value="<?= $member['id_member'] ?? ''; ?>">
                            <input type="hidden" name="diskon" value="<?= $diskon; ?>">
                            <div class="mb-3">
                                <label class="form-label">Metode</label>
                                <select name="metode_pembayaran" id="metodePembayaran" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="mb-3" id="inputBayar">
                                <label class="form-label">Uang Bayar</label>
                                <input type="text" name="bayar" id="bayarInput" class="form-control" placeholder="0">
                                <small id="errorBayar" class="text-danger fw-bold"></small>
                            </div>
                            <button class="btn btn-success w-100 btn-lg">
                                💰 Proses Bayar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif (isset($_GET['kode'])) : ?>
        <div class="alert alert-danger text-center">
            ❌ Transaksi tidak ditemukan atau sudah dibayar
        </div>
        <?php endif; ?>
    </div>

    <!-- modal atau popup daftar member -->
    <div class="modal fade" id="modalDaftarMember" tabindex="-1" aria-labelledby="modalDaftarMemberLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="../proses/kasir/proses_daftar_member.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDaftarMemberLabel">
                            👤 Daftar Member Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Kode Member -->
                        <div class="mb-3">
                            <label class="form-label">
                                Kode Member
                            </label>
                            <input disabled type="text" class="form-control" value="<?= $next_kode_member; ?>" readonly>
                        </div>
                        <!-- Nama Member -->
                        <div class="mb-3">
                            <label class="form-label">
                                Nama Member
                            </label>
                            <input type="text" name="nama_member" id="namaMemberInput" class="form-control" required>
                        </div>
                        <!-- No HP -->
                        <div class="mb-3">
                            <label class="form-label">
                                No HP
                            </label>
                            <input type="text" name="no_hp" id="noHpInput" class="form-control" maxlength="12"
                                inputmode="numeric" placeholder="081234567890" required>
                            <small id="infoNoHp" class="text-danger d-none">
                                Nomor HP harus tepat 12 digit.
                            </small>
                        </div>
                        <input type="hidden" name="status" value="aktif">
                    </div>

                    <!-- Agar saat penambahan member baru, transaksi nya tidak tiba tiba selesai -->
                    <input type="hidden" name="redirect_kode" value="<?= $kode; ?>">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" id="btnDaftarMember" class="btn btn-primary" disabled>
                            Daftar Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    const noHpInput = document.getElementById("noHpInput");
    const namaMemberInput = document.getElementById("namaMemberInput");
    const btnDaftarMember = document.getElementById("btnDaftarMember");
    const infoNoHp = document.getElementById("infoNoHp");

    // Nama hanya huruf dan spasi
    namaMemberInput.addEventListener("input", function() {
        this.value = this.value.replace(/[^a-zA-Z ]/g, '');
    });

    // No HP hanya angka
    noHpInput.addEventListener("input", function() {

        this.value = this.value.replace(/[^0-9]/g, '');

        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }

        if (this.value.length === 12) {
            btnDaftarMember.disabled = false;
            infoNoHp.classList.add("d-none");
        } else {
            btnDaftarMember.disabled = true;
            infoNoHp.classList.remove("d-none");
        }
    });
    </script>

    <!-- Member Auto digunakan -->
    <script>
    const input = document.getElementById("kodeMemberInput");
    const list = document.getElementById("memberList");
    let timeout;
    input.addEventListener("input", function() {
        const keyword = this.value.trim().toUpperCase();
        clearTimeout(timeout);
        if (keyword.length === 0) {
            list.style.display = "none";
            list.innerHTML = "";
            return;
        }
        timeout = setTimeout(() => {
            fetch("../proses/kasir/search_member.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "q=" + encodeURIComponent(keyword)
                })
                .then(res => res.json())
                .then(data => {
                    list.innerHTML = "";
                    if (!data || data.length === 0) {
                        list.innerHTML =
                            `<div class="list-group-item text-muted">Tidak ditemukan</div>`;
                        list.style.display = "block";
                        return;
                    }
                    data.forEach(m => {
                        const item = document.createElement("div");
                        item.className = "list-group-item list-group-item-action";
                        item.style.cursor = "pointer";
                        item.innerHTML = `
                            <b>${m.kode_member}</b>
                            ${m.status === 'aktif' 
                                ? `<span class="badge bg-success ms-2">Aktif</span>` 
                                : `<span class="badge bg-danger ms-2">Nonaktif</span>`
                            }<br>
                            ${m.nama_member} - ${m.no_hp}
                        `;
                        item.addEventListener("click", () => {
                            input.value = m.kode_member;
                            list.style.display = "none";
                            list.innerHTML = "";
                            const statusMsg = document.getElementById(
                                "memberStatusMsg");
                            if (m.status !== 'aktif') {
                                statusMsg.textContent = "Member tidak aktif!";
                                return;
                            } else {
                                statusMsg.textContent = "";
                            }
                            // lanjut proses kalau aktif
                            window.location.href =
                                window.location.pathname +
                                "?kode=" + new URLSearchParams(window.location
                                    .search).get("kode") +
                                "&kode_member=" + m.kode_member;
                        });
                        list.appendChild(item);
                    });
                    list.style.display = "block";
                });
        }, 200);
    });
    document.addEventListener("click", function(e) {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            list.style.display = "none";
        }
    });
    </script>

    <script>
    // --- Elemen Validasi Pembayaran ---
    const metode = document.getElementById("metodePembayaran");
    const inputBayar = document.getElementById("inputBayar");
    const bayarInput = document.getElementById("bayarInput");
    const errorBayar = document.getElementById("errorBayar");
    const formBayar = bayarInput.closest("form");

    // --- Elemen Validasi Member Baru ---
    const noHpField = document.getElementById("noHpInput");
    const namaField = document.querySelector("input[name='nama_member']");

    const totalAkhir = parseInt("<?= $total_akhir; ?>") || 0;

    // Set default required karena metode awal adalah 'cash'
    bayarInput.setAttribute("required", true);

    // 1. Event Handler saat Metode Pembayaran Berubah
    metode.addEventListener("change", function() {
        if (this.value === "online") {
            inputBayar.style.display = "none";
            bayarInput.removeAttribute("required");
            errorBayar.style.display = "none";
            bayarInput.value = "";
        } else {
            inputBayar.style.display = "block";
            bayarInput.setAttribute("required", true);
        }
    });

    // 2. Validasi Real-time saat mengetik uang bayar & Pengunci Karakter Non-Angka
    bayarInput.addEventListener("input", function() {
        // Blokir instan jika ada huruf/simbol yang masuk ke input bayar
        this.value = this.value.replace(/[^0-9]/g, '');
        if (metode.value === "cash") {
            const uangBayar = parseInt(this.value) || 0;
            if (uangBayar < totalAkhir && this.value !== "") {
                errorBayar.style.display = "block";
            } else {
                errorBayar.style.display = "none";
            }
        }
    });

    // 3. Validasi saat Form Pembayaran akan dikirim (Mencegah submit jika uang kurang)
    formBayar.addEventListener("submit", function(e) {
        if (metode.value === "cash") {
            const uangBayar = parseInt(bayarInput.value) || 0;
            if (uangBayar < totalAkhir) {
                e.preventDefault(); // Menggagalkan reload/kirim data
                errorBayar.style.display = "block";
                bayarInput.focus();
            }
        }
    });

    // 4. Khusus Kolom Nama Member: Hanya boleh huruf abjad dan spasi
    namaField.addEventListener("input", function(e) {
        this.value = this.value.replace(/[^a-zA-Z ]/g, '');
    });

    // 5. Khusus Kolom No HP Member: Hanya boleh angka & Maksimal 12 Digit
    noHpField.addEventListener("input", function(e) {
        // Buang karakter non-angka
        this.value = this.value.replace(/[^0-9]/g, '');
        // Potong paksa jika nekat mengetik digit ke-13
        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('pesan_error') === 'id_sudah_ada') {
            const modalElement = document.getElementById('modalDaftarMember');
            if (modalElement) {
                const modalMember = new bootstrap.Modal(modalElement);
                modalMember.show();
                const errorMember = document.getElementById("errorMember");
                if (errorMember) {
                    errorMember.style.display = "block";
                }
            }
        }
    });
    </script>

    <script>
    // SCRIPT PENGUNCIAN INSTAN NON-NUMERIK & PEMBATASAN 12 DIGIT
    const bayarField = document.getElementById("bayarInput");
    const noHpField = document.getElementById("noHpInput");

    function bersihkanNonAngka(e) {
        // 1. Hapus instan semua teks selain angka 0-9
        this.value = this.value.replace(/[^0-9]/g, '');
    }

    // Khusus untuk No HP, tambahkan pembatasan maksimal 12 digit
    noHpField.addEventListener("input", function(e) {
        // Jalankan pembersihan non-angka terlebih dahulu
        this.value = this.value.replace(/[^0-9]/g, '');
        // Jika panjang karakter lebih dari 12, potong otomatis
        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }
    });

    // Tetapkan listener untuk field bayar (hanya bersihkan angka saja, tidak dibatasi 12 digit)
    bayarField.addEventListener("input", bersihkanNonAngka);
    </script>

    <script>
    const alertBox = document.getElementById("alertSuccess");

    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "0.5s";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 2000); // 3 detik tampil
    }
    </script>
</body>

</html>
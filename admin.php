<?php
session_start();

// Simple admin login and orders viewer
$host = "localhost"; $user = "root"; $pass = "280902"; $db = "dbomah_ne_gedhang";
$adminPassword = 'bismillah'; // provided by owner

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout'){
    session_destroy();
    header('Location: index.php');
    exit;
}

// Clear logs (admin-only)
if (isset($_GET['clear_logs']) && $_GET['clear_logs'] === '1'){
    $ld = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    $files = ['proses_errors.log','proses_incoming.log','feedback.log','orders.log'];
    foreach($files as $f){ $p = $ld.$f; if (is_file($p)) { @file_put_contents($p, ''); } }
    header('Location: admin.php'); exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])){
    if ($_POST['password'] === $adminPassword){
        $_SESSION['is_owner'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Password salah.';
    }
}

// If not logged in show login form
if (empty($_SESSION['is_owner'])){
    ?>
    <!doctype html>
    <html>
    <head><meta charset="utf-8"><title>Login Admin</title><style>body{font-family:Arial,Helvetica,sans-serif;background:#0a0a0a;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh} .box{background:#0f0f0f;padding:28px;border-radius:12px;min-width:320px} input{width:100%;padding:10px;margin-top:8px;border-radius:8px;border:1px solid #333;background:#0b0b0b;color:#fff}</style></head>
    <body>
        <div class="box">
            <h2>Admin Login</h2>
            <?php if(!empty($error)) echo '<div style="color:#ff6b6b;margin-bottom:8px">'.htmlspecialchars($error).'</div>'; ?>
            <form method="post">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
                <div style="margin-top:12px"><button type="submit" style="padding:10px 14px;background:#ff8c00;border:none;color:#000;font-weight:700;border-radius:8px">Masuk</button></div>
            </form>
            <p style="font-size:12px;color:#aaa;margin-top:12px">Gunakan password yang diberikan untuk mengakses data pesanan.</p>
            <p style="font-size:12px;color:#666;margin-top:8px"><a href="index.php" style="color:#888">Kembali ke toko</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logged-in: show simple orders viewer from DB (falls back to local file)
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - Orders</title>
    <style>
        body{font-family:Inter,Arial,Helvetica,sans-serif;background:#080808;color:#fff;padding:28px}
        .container{max-width:1200px;margin:0 auto}
        h2{color:#ff8c00;margin:0}
        table{width:100%;border-collapse:collapse;background:#0b0b0b;border:1px solid #1a1a1a;border-radius:8px;overflow:hidden}
        th,td{border-bottom:1px solid #171717;padding:10px 12px;text-align:left;vertical-align:top}
        thead th{background:#060606;color:#ff8c00;font-weight:700;text-transform:uppercase;font-size:13px}
        tbody tr:nth-child(odd){background:linear-gradient(180deg, rgba(255,255,255,0.01), transparent)}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
        .btn{display:inline-block;padding:8px 10px;border-radius:8px;border:none;cursor:pointer}
        .btn-primary{background:#ff8c00;color:#000;font-weight:700}
        .btn-ghost{background:transparent;color:#888;border:1px solid #222}
        .status-badge{display:inline-block;padding:6px 10px;border-radius:999px;font-weight:700}
        .status-new{background:#ffedcc;color:#663c00}
        .status-processing{background:#fff4e6;color:#333}
        .status-done{background:#e6fff0;color:#0a6b2f}
        form.inline{display:inline-flex;gap:8px;align-items:center}
        select{background:#0b0b0b;border:1px solid #222;color:#fff;padding:6px;border-radius:6px}
        textarea, input[type=text]{background:#070707;border:1px solid #222;color:#fff;padding:8px;border-radius:8px}
    </style>
</head>
<body>
    <div class="topbar"><h2>Admin Panel — Orders</h2><div><a href="admin.php?action=logout" style="color:#ff8c00;margin-right:12px">Logout</a><a href="index.php" style="color:#888">View Shop</a></div></div>
    <?php
    // Debug panel: show last lines from logs for quick troubleshooting (admin-only)
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    function tail_lines($path, $n = 80){ if(!is_file($path)) return ''; $lines = array_filter(explode(PHP_EOL, trim(file_get_contents($path)))); return implode(PHP_EOL, array_slice($lines, max(0, count($lines)-$n))); }
    $errs = tail_lines($logDir . DIRECTORY_SEPARATOR . 'proses_errors.log', 120);
    $incoming = tail_lines($logDir . DIRECTORY_SEPARATOR . 'proses_incoming.log', 120);
    $feedback_log = tail_lines($logDir . DIRECTORY_SEPARATOR . 'feedback.log', 120);
    echo '<div class="debug-panel" style="margin-top:16px;background:#020202;border:1px solid #111;padding:12px;border-radius:8px">';
    echo '<div style="display:flex;gap:12px;flex-wrap:wrap">';
    echo '<div style="flex:1;min-width:260px"><strong style="color:#ff8c00">proses_errors.log</strong><pre style="background:#000;color:#f88;padding:8px;border-radius:6px;max-height:180px;overflow:auto">'.htmlspecialchars($errs).'</pre></div>';
    echo '<div style="flex:1;min-width:260px"><strong style="color:#ff8c00">proses_incoming.log</strong><pre style="background:#000;color:#fff;padding:8px;border-radius:6px;max-height:180px;overflow:auto">'.htmlspecialchars($incoming).'</pre></div>';
    echo '</div>';
    echo '<div style="margin-top:8px"><strong style="color:#ff8c00">feedback.log (fallback)</strong><pre style="background:#000;color:#fff;padding:8px;border-radius:6px;max-height:120px;overflow:auto">'.htmlspecialchars($feedback_log).'</pre></div>';
    echo '<div style="margin-top:8px;text-align:right"><a href="admin.php?clear_logs=1" style="color:#ff8c00">Clear logs</a></div>';
    echo '</div>';

    $conn = new mysqli($host,$user,$pass,$db);
    if ($conn->connect_error) {
        echo '<div style="color:#ff6b6b">Koneksi DB gagal: '.htmlspecialchars($conn->connect_error).'</div>';
        // fallback: show local orders log if present
        $log = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'orders.log';
        if (is_file($log)){
            echo '<h3>Orders from file</h3><pre style="background:#000;padding:12px;border-radius:8px;max-height:400px;overflow:auto">'.htmlspecialchars(file_get_contents($log)).'</pre>';
        }
        exit;
    }

    // Handle POST actions (status update)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_order_id']) && isset($_POST['update_status'])){
        $uid = intval($_POST['update_order_id']);
        $newStatus = substr(trim($_POST['update_status']), 0, 32);
        $allowed = ['pending','processing','done'];
        if (!in_array($newStatus, $allowed)) $newStatus = 'pending';
        $stmt = $conn->prepare("UPDATE `pesanan` SET status = ? WHERE id = ?");
        if ($stmt){ $stmt->bind_param('si', $newStatus, $uid); $stmt->execute(); $stmt->close(); }
        header('Location: admin.php'); exit;
    }

    // Ensure reviews table exists and has a waktu column
    $conn->query("CREATE TABLE IF NOT EXISTS `reviews` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(191) NOT NULL,
        `message` TEXT NOT NULL,
        `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    // if for some reason `waktu` missing, add it
    $rv_check = $conn->query("SHOW COLUMNS FROM `reviews` LIKE 'waktu'");
    if ($rv_check && $rv_check->num_rows === 0){ $conn->query("ALTER TABLE `reviews` ADD COLUMN `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); }

    // try common table names
    $tablesToTry = ['pesanan','orders','orders_log','orders','simpan_pesanan'];
    $foundTable = null;
    foreach($tablesToTry as $t){
        $res = $conn->query("SHOW TABLES LIKE '".$conn->real_escape_string($t)."'");
        if ($res && $res->num_rows > 0){ $foundTable = $t; break; }
    }

    if (!$foundTable){
        echo '<div style="color:#ffcf6b">Tabel pesanan tidak ditemukan di DB. Mencoba membuat tabel baru &hellip;</div>';
        $createSQL = "CREATE TABLE IF NOT EXISTS `pesanan` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `nama_pembeli` VARCHAR(191) NOT NULL,
            `nomor_hp` VARCHAR(32) NOT NULL,
            `alamat` TEXT NOT NULL,
            `detail_pesanan` TEXT NOT NULL,
            `total_harga` DECIMAL(12,2) NOT NULL DEFAULT 0,
            `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
            `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if ($conn->query($createSQL) === TRUE){
            echo '<div style="color:#7ed957">Tabel `pesanan` berhasil dibuat.</div>';
            $foundTable = 'pesanan';
        } else {
            echo '<div style="color:#ff6b6b">Gagal membuat tabel: '.htmlspecialchars($conn->error).'</div>';
            $log = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'orders.log';
            if (is_file($log)){
                echo '<h3>Orders from file</h3><pre style="background:#000;padding:12px;border-radius:8px;max-height:400px;overflow:auto">'.htmlspecialchars(file_get_contents($log)).'</pre>';
            } else {
                echo '<div style="color:#888;margin-top:12px">Tidak ada data pesanan.</div>';
            }
            exit;
        }
    }

    // Ensure `status` column is flexible (not strict ENUM) to avoid truncation errors
    $col = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($foundTable)."` LIKE 'status'");
    if ($col && $col->num_rows > 0){ $cinfo = $col->fetch_assoc(); $type = strtolower($cinfo['Type'] ?? ''); if (strpos($type,'enum(') !== false || (preg_match('/varchar\((\d+)\)/', $type, $m) && intval($m[1]) < 32)){
        $conn->query("ALTER TABLE `".$conn->real_escape_string($foundTable)."` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'pending'");
    } }

    // Fetch orders in a stable column order and display nicely
    $q = "SELECT id, nama_pembeli, nomor_hp, alamat, detail_pesanan, total_harga, status, waktu FROM `".$conn->real_escape_string($foundTable)."` ORDER BY id DESC LIMIT 200";
    $res = $conn->query($q);
    if (!$res){ echo '<div style="color:#ff6b6b">Query gagal: '.htmlspecialchars($conn->error).'</div>'; exit; }

    echo '<h3>Pesanan Terbaru</h3>';
    echo '<table><thead><tr><th>id</th><th>Nama</th><th>Nomor HP</th><th>Alamat</th><th>Detail Pesanan</th><th>Total</th><th>Status</th><th>Waktu</th></tr></thead><tbody>';
    while ($row = $res->fetch_assoc()){
        $hp = $row['nomor_hp'] ?? ($row['hp'] ?? '');
        $total = is_numeric($row['total_harga'] ?? $row['total']) ? number_format(floatval($row['total_harga'] ?? $row['total']), 0, ',', '.') : htmlspecialchars($row['total_harga'] ?? $row['total'] ?? '0');
        $statusRaw = strtolower(trim($row['status'] ?? 'pending'));
        if (in_array($statusRaw, ['pending','baru','new'])) $statusLabel = 'Baru';
        else if (in_array($statusRaw, ['processing','diproses'])) $statusLabel = 'Diproses';
        else if (in_array($statusRaw, ['done','selesai','completed'])) $statusLabel = 'Selesai';
        else $statusLabel = ucfirst($row['status'] ?? '');
        echo '<tr>';
        echo '<td>'.htmlspecialchars($row['id']).'</td>';
        echo '<td>'.htmlspecialchars($row['nama_pembeli'] ?? '').'</td>';
        echo '<td>'.htmlspecialchars($hp).'</td>';
        echo '<td>'.htmlspecialchars($row['alamat'] ?? '').'</td>';
        echo '<td>'.nl2br(htmlspecialchars($row['detail_pesanan'] ?? $row['detail'] ?? '')).'</td>';
        echo '<td>Rp '. $total .'</td>';
        // status update form
        echo '<td>';
        echo '<form method="post" class="inline">';
        echo '<input type="hidden" name="update_order_id" value="'.intval($row['id']).'">';
        echo '<select name="update_status">';
        $opts = ['pending'=>'Baru','processing'=>'Diproses','done'=>'Selesai'];
        foreach($opts as $sv=>$label){ echo '<option value="'.htmlspecialchars($sv).'"'.($statusRaw===$sv?' selected':'').'>'.htmlspecialchars($label).'</option>'; }
        echo '</select>';
        echo '<button type="submit" class="btn btn-primary">Update</button>';
        echo '</form>';
        echo '</td>';
        echo '<td>'.htmlspecialchars($row['waktu'] ?? $row['created_at'] ?? '').'</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Show reviews from `reviews` table (create if needed)
    echo '<h3 style="margin-top:28px">Feedback Pelanggan</h3>';
    $rv = $conn->query("SHOW TABLES LIKE 'reviews'");
    if ($rv && $rv->num_rows === 0){
        $create = "CREATE TABLE IF NOT EXISTS `reviews` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(191) NOT NULL,
            `message` TEXT NOT NULL,
            `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($create);
    }
    $rres = $conn->query("SELECT id, name, message, waktu FROM `reviews` ORDER BY id DESC LIMIT 200");
    if ($rres && $rres->num_rows > 0){
        echo '<table style="margin-top:10px"><thead><tr><th>id</th><th>Nama</th><th>Pesan</th><th>Waktu</th></tr></thead><tbody>';
        while ($r = $rres->fetch_assoc()){
            echo '<tr>';
            echo '<td>'.htmlspecialchars($r['id']).'</td>';
            echo '<td>'.htmlspecialchars($r['name']).'</td>';
            echo '<td>'.nl2br(htmlspecialchars($r['message'])).'</td>';
            echo '<td>'.htmlspecialchars($r['waktu']).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div style="color:#888;margin-top:8px">Belum ada feedback tersimpan.</div>';
    }

    $conn->close();
    ?>
</body>
</html>
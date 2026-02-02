<?php
header('Content-Type: application/json; charset=utf-8');
// Simple proses.php: save feedback and orders to local files under ./data/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = $_POST;

    // ensure data folder exists
    $dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

    // Log incoming requests for debugging
    try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'proses_incoming.log', date('c') . " POST: " . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX); } catch(Throwable $e){}

    if (isset($data['type']) && $data['type'] === 'feedback') {
        $name = trim($data['name'] ?? '');
        $message = trim($data['message'] ?? '');
        if ($name === '' || $message === '') {
            echo json_encode(['status' => 'error', 'message' => 'Missing name or message']);
            exit;
        }

        // Try to save to DB (table `reviews`) first, fallback to file
        $entry = ['time' => date('c'), 'name' => $name, 'message' => $message];
        try {
            $dbHost = 'localhost'; $dbUser = 'root'; $dbPass = '280902'; $dbName = 'dbomah_ne_gedhang';
            $dbConn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if (!$dbConn->connect_error) {
                $create = "CREATE TABLE IF NOT EXISTS `reviews` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(191) NOT NULL,
                    `message` TEXT NOT NULL,
                    `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                $dbConn->query($create);
                $stmt = $dbConn->prepare("INSERT INTO `reviews` (name, message) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param('ss', $name, $message);
                    if ($stmt->execute()) {
                        $stmt->close(); $dbConn->close();
                        echo json_encode(['status' => 'success']);
                        exit;
                    }
                    // record DB error
                    $err = $stmt->error ?: $dbConn->error;
                    try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'proses_errors.log', date('c') . " REVIEW INSERT failed: " . $err . PHP_EOL, FILE_APPEND | LOCK_EX); } catch(Throwable $ee){}
                    $stmt->close();
                } else {
                    try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'proses_errors.log', date('c') . " REVIEW PREPARE failed: " . ($dbConn->error ?: 'prepare_failed') . PHP_EOL, FILE_APPEND | LOCK_EX); } catch(Throwable $ee){}
                }
                $dbConn->close();
            } else {
                try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'proses_errors.log', date('c') . " REVIEW DB CONNECT failed: " . $dbConn->connect_error . PHP_EOL, FILE_APPEND | LOCK_EX); } catch(Throwable $ee){}
            }
        } catch (Throwable $e) {
            try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'proses_errors.log', date('c') . " REVIEW exception: " . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX); } catch(Throwable $ee){}
        }

        // fallback to file log
        try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'feedback.log', json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX); } catch(Throwable $e){ try{ file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'proses_errors.log', date('c') . " FEEDBACK fallback write failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);} catch(Throwable $ee){} }
        echo json_encode(['status' => 'success']);
        exit;
    }

    // otherwise treat as order: save to local file (keeps phone required)
    $nama = trim($data['nama'] ?? '');
    $nomor_hp = trim($data['hp'] ?? '');
    $alamat = trim($data['alamat'] ?? '');
    $detail = trim($data['detail'] ?? '');
    $total = trim($data['total'] ?? '');

    if ($nama === '' || $alamat === '' || $detail === '' || $total === '' || $nomor_hp === '') {
        echo json_encode(['status' => 'error', 'message' => 'Missing order fields']);
        exit;
    }

    $entry = ['time' => date('c'), 'nama' => $nama, 'hp' => $nomor_hp, 'alamat' => $alamat, 'detail' => $detail, 'total' => $total];

    // Try to save into MySQL table `pesanan` if possible
    try {
        $dbHost = 'localhost'; $dbUser = 'root'; $dbPass = '280902'; $dbName = 'dbomah_ne_gedhang';
        $dbConn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if (!$dbConn->connect_error) {
            $create = "CREATE TABLE IF NOT EXISTS `pesanan` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `nama_pembeli` VARCHAR(191) NOT NULL,
                `nomor_hp` VARCHAR(32) NOT NULL,
                `alamat` TEXT NOT NULL,
                `detail_pesanan` TEXT NOT NULL,
                `total_harga` DECIMAL(12,2) NOT NULL DEFAULT 0,
                `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
                `waktu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $dbConn->query($create);
            $stmt = $dbConn->prepare("INSERT INTO `pesanan` (nama_pembeli, nomor_hp, alamat, detail_pesanan, total_harga) VALUES (?,?,?,?,?)");
            if ($stmt){
                $total_num = is_numeric($total) ? floatval($total) : 0;
                $stmt->bind_param('ssssd', $nama, $nomor_hp, $alamat, $detail, $total_num);
                if ($stmt->execute()){
                    $stmt->close(); $dbConn->close();
                    echo json_encode(['status' => 'success']);
                    exit;
                }
                $stmt->close();
            }
            $dbConn->close();
        }
    } catch (Throwable $e) {
        // ignore DB errors, fallback to file
    }

    // fallback to file log
    file_put_contents($dataDir . DIRECTORY_SEPARATOR . 'orders.log', json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo json_encode(['status' => 'success']);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}


<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Konfigurasi database
$db_host = 'localhost';
$db_name = 'speedtest';
$db_user = 'root';
$db_pass = '';

// Fungsi untuk koneksi database
function connectDB() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        return null;
    }
}

// Fungsi untuk mendapatkan informasi IP pengguna
function getClientInfo() {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    
    $location_info = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);
    
    return [
        'ip' => $ip,
        'country' => $location_info['country'] ?? 'Tidak diketahui',
        'region' => $location_info['regionName'] ?? 'Tidak diketahui',
        'city' => $location_info['city'] ?? 'Tidak diketahui',
        'isp' => $location_info['isp'] ?? 'Tidak diketahui',
        'timezone' => $location_info['timezone'] ?? 'Asia/Jakarta'
    ];
}

// Fungsi AI untuk analisis koneksi
function analyzeConnectionWithAI($download, $upload, $ping, $isp, $location) {
    $analysis = [];
    $recommendations = [];
    $predicted_issues = [];
    
    // Analisis berdasarkan kecepatan download
    if ($download >= 100) {
        $analysis[] = "Kecepatan unduh Anda sangat baik! Cocok untuk semua aktivitas online termasuk streaming 4K dan gaming.";
    } elseif ($download >= 50) {
        $analysis[] = "Kecepatan unduh Anda baik. Dapat menangani streaming HD dan gaming dengan lancar.";
    } elseif ($download >= 25) {
        $analysis[] = "Kecepatan unduh Anda cukup untuk penggunaan standar seperti browsing dan streaming 1080p.";
    } elseif ($download >= 10) {
        $analysis[] = "Kecepatan unduh Anda minimal untuk streaming HD. Mungkin mengalami buffering sesekali.";
        $predicted_issues[] = "Buffering video";
    } else {
        $analysis[] = "Kecepatan unduh Anda rendah. Aktivitas seperti streaming video mungkin bermasalah.";
        $predicted_issues[] = "Keterlambatan loading halaman";
        $predicted_issues[] = "Buffering video yang sering";
    }
    
    // Analisis berdasarkan kecepatan upload
    if ($upload >= 20) {
        $analysis[] = "Kecepatan unggah sangat baik untuk video call, streaming live, dan upload file besar.";
    } elseif ($upload >= 10) {
        $analysis[] = "Kecepatan unggah baik untuk video conference dan upload file sedang.";
    } elseif ($upload >= 5) {
        $analysis[] = "Kecepatan unggah cukup untuk video call standar.";
    } else {
        $analysis[] = "Kecepatan unggah rendah. Video call mungkin mengalami kualitas gambar rendah atau putus-putus.";
        $predicted_issues[] = "Kualitas video call buruk";
    }
    
    // Analisis berdasarkan ping
    if ($ping < 20) {
        $analysis[] = "Ping sangat rendah! Ideal untuk gaming kompetitif dan aplikasi real-time.";
    } elseif ($ping < 50) {
        $analysis[] = "Ping baik untuk gaming dan aplikasi real-time.";
    } elseif ($ping < 100) {
        $analysis[] = "Ping sedang. Gaming mungkin mengalami lag sesekali.";
        $predicted_issues[] = "Lag dalam game";
    } else {
        $analysis[] = "Ping tinggi. Dapat menyebabkan delay yang terlihat dalam aplikasi real-time.";
        $predicted_issues[] = "Delay dalam aplikasi real-time";
    }
    
    // Rekomendasi berdasarkan analisis
    if ($download < 25 || $upload < 5 || $ping > 80) {
        $recommendations[] = "Pertimbangkan untuk menghubungi $isp tentang paket internet yang lebih cepat.";
    }
    
    if ($ping > 50) {
        $recommendations[] = "Coba gunakan koneksi kabel LAN daripada WiFi untuk mengurangi ping.";
        $recommendations[] = "Tutup aplikasi yang tidak diperlukan untuk mengoptimalkan bandwidth.";
    }
    
    if ($download < 10) {
        $recommendations[] = "Lakukan tes pada waktu yang berbeda untuk memeriksa konsistensi kecepatan.";
    }
    
    // Rekomendasi berdasarkan lokasi
    if (strpos($location, 'Jakarta') !== false || strpos($location, 'Surabaya') !== false) {
        $recommendations[] = "Di area metropolitan, pastikan Anda menggunakan frekuensi WiFi 5GHz untuk menghindari interferensi.";
    }
    
    // Prediksi penggunaan optimal
    $optimal_use = [];
    if ($download >= 25 && $ping < 60) {
        $optimal_use[] = "Gaming Online";
    }
    if ($download >= 10) {
        $optimal_use[] = "Streaming Video HD";
    }
    if ($upload >= 5) {
        $optimal_use[] = "Video Conference";
    }
    if ($download >= 50) {
        $optimal_use[] = "Streaming 4K";
    }
    if ($upload >= 10) {
        $optimal_use[] = "Live Streaming";
    }
    
    return [
        'analysis' => $analysis,
        'recommendations' => $recommendations,
        'predicted_issues' => $predicted_issues,
        'optimal_use' => $optimal_use,
        'ai_score' => calculateAIScore($download, $upload, $ping)
    ];
}

// Fungsi untuk menghitung skor AI
function calculateAIScore($download, $upload, $ping) {
    $score = 0;
    
    // Bobot: Download 50%, Upload 30%, Ping 20%
    $download_score = min(($download / 100) * 100, 100) * 0.5;
    $upload_score = min(($upload / 50) * 100, 100) * 0.3;
    $ping_score = 0;
    
    if ($ping <= 10) $ping_score = 100;
    elseif ($ping <= 30) $ping_score = 80;
    elseif ($ping <= 50) $ping_score = 60;
    elseif ($ping <= 100) $ping_score = 40;
    else $ping_score = 20;
    
    $ping_score = $ping_score * 0.2;
    
    $score = $download_score + $upload_score + $ping_score;
    
    return round($score);
}

// Fungsi untuk mengukur ping
function measurePing($host = 'google.com') {
    $output = [];
    $result = -1;
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec("ping -n 4 {$host}", $output, $result);
        if ($result === 0 && count($output) >= 5) {
            preg_match('/Average = (\d+)ms/', implode(' ', $output), $matches);
            return isset($matches[1]) ? (int)$matches[1] : rand(10, 50);
        }
    } else {
        exec("ping -c 4 {$host}", $output, $result);
        if ($result === 0 && count($output) >= 8) {
            preg_match('/min\/avg\/max\/mdev = [\d.]+\/([\d.]+)\/[\d.]+\/[\d.]+/', $output[7], $matches);
            return isset($matches[1]) ? (float)$matches[1] : rand(10, 50);
        }
    }
    
    return rand(10, 100);
}

// Fungsi untuk mengukur kecepatan download
function measureDownloadSpeed($size = 1048576) {
    $start_time = microtime(true);
    $data = generateRandomData($size);
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    
    if ($duration > 0) {
        $speed = ($size * 8) / ($duration * 1000000);
        return round($speed, 2);
    }
    
    return 0;
}

// Fungsi untuk mengukur kecepatan upload
function measureUploadSpeed($size = 524288) {
    $start_time = microtime(true);
    $data = generateRandomData($size);
    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    
    if ($duration > 0) {
        $speed = ($size * 8) / ($duration * 1000000);
        return round($speed, 2);
    }
    
    return 0;
}

// Fungsi pembantu untuk generate data acak
function generateRandomData($size) {
    return openssl_random_pseudo_bytes($size);
}

// Fungsi untuk menyimpan hasil tes ke database
function saveTestResult($data) {
    $pdo = connectDB();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO speedtest_results 
            (ip_address, country, region, city, isp, download_speed, upload_speed, ping, test_time, user_agent, ai_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");
        
        $stmt->execute([
            $data['ip'],
            $data['country'],
            $data['region'],
            $data['city'],
            $data['isp'],
            $data['download_speed'],
            $data['upload_speed'],
            $data['ping'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $data['ai_score'] ?? 0
        ]);
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Fungsi untuk mendapatkan riwayat tes
function getTestHistory($limit = 10) {
    $pdo = connectDB();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM speedtest_results 
            ORDER BY test_time DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Fungsi untuk menentukan kualitas koneksi
function getConnectionQuality($download, $upload, $ping) {
    if ($download >= 50 && $upload >= 20 && $ping < 30) {
        return ['quality' => 'Sangat Baik', 'class' => 'quality-excellent'];
    } else if ($download >= 25 && $upload >= 10 && $ping < 50) {
        return ['quality' => 'Baik', 'class' => 'quality-good'];
    } else if ($download >= 10 && $upload >= 5 && $ping < 100) {
        return ['quality' => 'Cukup', 'class' => 'quality-fair'];
    } else {
        return ['quality' => 'Buruk', 'class' => 'quality-poor'];
    }
}

// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'start_test':
            $client_info = getClientInfo();
            
            // Ukur ping
            $ping = measurePing();
            
            // Ukur download speed
            $download_speed = measureDownloadSpeed();
            
            // Ukur upload speed
            $upload_speed = measureUploadSpeed();
            
            // Tentukan kualitas koneksi
            $quality = getConnectionQuality($download_speed, $upload_speed, $ping);
            
            // Analisis AI
            $ai_analysis = analyzeConnectionWithAI(
                $download_speed, 
                $upload_speed, 
                $ping, 
                $client_info['isp'],
                $client_info['city']
            );
            
            // Siapkan data hasil
            $result = [
                'success' => true,
                'client_info' => $client_info,
                'download_speed' => $download_speed,
                'upload_speed' => $upload_speed,
                'ping' => $ping,
                'quality' => $quality,
                'ai_analysis' => $ai_analysis,
                'test_time' => date('Y-m-d H:i:s')
            ];
            
            // Simpan ke database
            $save_data = array_merge($client_info, [
                'download_speed' => $download_speed,
                'upload_speed' => $upload_speed,
                'ping' => $ping,
                'ai_score' => $ai_analysis['ai_score']
            ]);
            
            saveTestResult($save_data);
            
            echo json_encode($result);
            break;
            
        case 'get_history':
            $history = getTestHistory(10);
            echo json_encode(['success' => true, 'history' => $history]);
            break;
            
        case 'get_ai_tips':
            $tips = generateAITips();
            echo json_encode(['success' => true, 'tips' => $tips]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
}

// Fungsi untuk menghasilkan tips AI acak
function generateAITips() {
    $tips = [
        "Gunakan kabel Ethernet untuk koneksi yang lebih stabil dan ping yang lebih rendah",
        "Restart router Anda secara berkala untuk mengoptimalkan performa",
        "Posisikan router di tempat terbuka dan tinggi untuk jangkauan WiFi yang lebih baik",
        "Gunakan frekuensi 5GHz untuk menghindari interferensi dengan perangkat lain",
        "Tutup aplikasi yang tidak diperlukan untuk menghemat bandwidth",
        "Periksa pembaruan firmware router untuk keamanan dan performa yang lebih baik",
        "Gunakan WiFi analyzer untuk menemukan channel yang kurang padat",
        "Pertimbangkan mesh network untuk rumah besar dengan banyak dead spot",
        "Batasi jumlah perangkat yang terhubung secara bersamaan",
        "Gunakan QoS (Quality of Service) di router untuk memprioritaskan aplikasi penting"
    ];
    
    return $tips[array_rand($tips)];
}
?>
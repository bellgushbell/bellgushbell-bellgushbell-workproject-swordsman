<?php
session_start();
require __DIR__ . '/../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ค้นหาผู้ใช้ในฐานข้อมูล
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();


    if ($user) {
        // ตรวจสอบรหัสผ่าน (ใช้ password_verify หากรหัสผ่านถูกเข้ารหัส)
        echo password_verify($password, $user['password']);
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            header("Location: ../../page/admin/admin.php");
            exit();
        } else {
            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $_SESSION['error'] = "ชื่อผู้ใช้ไม่ถูกต้อง";
    }

    // กลับไปหน้าล็อกอินถ้าผิดพลาด
    header("Location: ../../page/admin/login.php");
    exit();
}


//ไว้เช็ค password_verify
// $new_hashed_password = password_hash("123456", PASSWORD_DEFAULT);

// echo "ผลลัพธ์ของ password_verify(): " . (password_verify($password, $user['password']) ? "ผ่าน" : "ไม่ผ่าน") . "<br>" . $new_hashed_password;

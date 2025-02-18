<?php
require_once __DIR__ . '/../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start(); // ตรวจสอบว่าเริ่ม session หรือยัง
    $admin_id = $_SESSION['id'];
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description =  $_POST['description'];
    $timestamp = date("Y-m-d H:i:s");


    // กำหนดโฟลเดอร์ปลายทาง
    $target_dir = "../../images/news/";
    $file_name = NULL; // ตั้งค่าเริ่มต้น

    // ตรวจสอบว่ามีไฟล์อัปโหลดหรือไม่
    if (!empty($_FILES["upload_title"]["name"])) {
        $file_ext = pathinfo($_FILES["upload_title"]["name"], PATHINFO_EXTENSION); // ดึงนามสกุลไฟล์
        $allowed_ext = ["jpg", "jpeg", "png", "gif"]; // นามสกุลที่อนุญาต

        // ตรวจสอบนามสกุลไฟล์
        if (!in_array(strtolower($file_ext), $allowed_ext)) {
            echo "<script>alert('ประเภทไฟล์ไม่ถูกต้อง! อนุญาตเฉพาะ JPG, JPEG, PNG, GIF');</script>";
            exit();
        }

        // สร้างชื่อไฟล์ใหม่ให้ไม่ซ้ำ
        $file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $file_name;

        // ตรวจสอบและย้ายไฟล์
        if (move_uploaded_file($_FILES["upload_title"]["tmp_name"], $target_file)) {
            // ไฟล์ถูกอัปโหลดสำเร็จ
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์!');</script>";
            exit();
        }
    }

    // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
    $stmt_title = $conn->prepare("INSERT INTO title (type, title, image, created_at, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt_title->bind_param("ssssi", $type, $title, $file_name, $timestamp, $admin_id);

    if ($stmt_title->execute()) {
        // Get the last inserted id_title
        $id_title = $conn->insert_id;


        // Insert into the description table
        $stmt_description = $conn->prepare("INSERT INTO description (id_title, description, created_at, created_by) VALUES (?, ?, ?, ?)");
        $stmt_description->bind_param("issi", $id_title, $description, $timestamp, $admin_id);

        if ($stmt_description->execute()) {

            // เปลี่ยนไปยังหน้า content_management.php พร้อมส่ง parameter สำเร็จ
            header("Location: ../../page/admin/content_management.php?success=1");
            exit();
        } else {
            echo "Error: " . $stmt_description->error;
        }

        $stmt_description->close();
    } else {
        echo "Error: " . $stmt_title->error;
    }

    $stmt_title->close();
    $conn->close();
}

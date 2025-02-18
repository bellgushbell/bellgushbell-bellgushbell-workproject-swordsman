<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // ตรวจสอบการเข้าสู่ระบบ
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }

    // ดึงข้อมูลจาก session ที่ชื่อว่า edit_data ถ้ามี
    if (isset($_SESSION['edit_data'])) {
        $data = $_SESSION['edit_data'];
    }

    // รับค่าจาก URL หากมี edit_id
    if (isset($_GET['edit_id'])) {
        $edit = "Edit Information";
    } else {
        $edit = "Create New Entry";  // ถ้าไม่มี edit_id ก็ให้เป็น NULL
    }
}
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Admin </title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons & Tooltip -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../../css/admin/style.css">
    <link rel="stylesheet" href="../../css/admin/responsive.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Include the Quill library -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Include Quill stylesheet -->
    <link
        href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css"
        rel="stylesheet" />

</head>

<body>

    <div class="wrapper">
        <?php include('navbar.php'); ?>
        <main class="content">
            <div class="d-flex justify-content-between align-items-center mb-1 p-3 rounded shadow-sm"
                style="background-color: rgba(255, 255, 255, 0.5); backdrop-filter: blur(8px); border-radius: 10px;">
                <h3><?php echo $edit; ?></h3>
                <button class="btn btn-outline-secondary" onclick="window.location.href='content_management.php'">Cancel</button>
            </div>
            <!-- Main Content -->


            <div class="content" style="background-color: rgba(255, 255, 255, 0.5); backdrop-filter: blur(8px); border-radius: 10px;">

                <div class="card-body">
                    <!-- <form action="../../database/admin/content_create.php" method="POST" enctype="multipart/form-data"> -->
                    <form action="<?php echo isset($_GET['edit_id']) ? '../../database/admin/content_update.php?edit_id=' . $_GET['edit_id'] : '../../database/admin/content_create.php'; ?>" method="POST" enctype="multipart/form-data">

                        <div class="row mb-3">

                            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                                <div class="form-group d-flex align-items-center" style="width: 100%; max-width: 500px;">
                                    <label for="role" style="flex: 0 0 20%;">Type:</label>
                                    <select class="form-control" id="type" name="type" required style="flex: 1;">
                                        <!-- ตัวเลือก "เลือกประเภท" ที่จะเป็นค่าเริ่มต้นเมื่อไม่มี edit_id -->
                                        <option value="" disabled <?php echo !isset($_GET['edit_id']) ? 'selected' : ''; ?>>เลือกประเภท</option>

                                        <!-- ตัวเลือกที่เลือกแล้วตามค่าของ $data['type'] -->
                                        <option value="ข่าว" <?php echo isset($data['type']) && $data['type'] == 'ข่าว' ? 'selected' : ''; ?>>ข่าว</option>
                                        <option value="กิจกรรม" <?php echo isset($data['type']) && $data['type'] == 'กิจกรรม' ? 'selected' : ''; ?>>กิจกรรม</option>
                                        <option value="โปรโมชั่น" <?php echo isset($data['type']) && $data['type'] == 'โปรโมชั่น' ? 'selected' : ''; ?>>โปรโมชั่น</option>
                                    </select>
                                </div>

                                <div class="form-group d-flex align-items-center mt-3" style="width: 100%; max-width: 500px;">
                                    <label for="name" style="flex: 0 0 20%;">Subject:</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo isset($data['title']) ? $data['title'] : ''; ?>" required style="flex: 1;">
                                </div>

                                <!-- Upload File Section -->

                                <!-- รูปภาพแสดงตัวอย่าง และกรอบ (แสดงอยู่เหนือช่อง Upload) -->
                                <div class="mt-3">
                                    <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                                        <img id="preview" class="img-fluid"
                                            style="max-width: 280px; max-height: 220px; width: 280px; height: 220px;
                border: 2px dashed #ccc; padding: 10px; background-color: #f8f9fa;">
                                        <span id="preview-text"
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                color: #aaa; font-size: 14px; pointer-events: none;">ยังไม่ได้เลือกรูป</span>
                                    </div>
                                </div>

                                <!-- แสดงชื่อไฟล์ที่เลือก -->
                                <?php
                                $imageName = isset($data['image']) ? $data['image'] : '';
                                ?>
                                <div id="file-name" style="margin-top: 10px; color: #555; display: flex; align-items: center; gap: 2px;">
                                    <input type="text" class="form-control" id="file-name-text"
                                        <?php echo $imageName ? $imageName : 'ยังไม่ได้เลือกไฟล์'; ?> style="flex: 1;" readonly>
                                    <!-- ส่งค่า imageName ไปกับฟอร์ม -->
                                    <?php if (!empty($imageName)) : ?>
                                        <input type="hidden" name="old_image" value="<?php echo $imageName; ?>">
                                    <?php endif; ?>


                                    <div class="form-group d-flex align-items-center" id="upload-container" style="<?php echo $imageName ? 'display: none;' : ''; ?>">
                                        <button type="button" class="btn btn-primary" id="file-upload-btn" style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0;">
                                            <i class="bi bi-file-earmark-arrow-up"></i>
                                        </button>
                                        <input type="file" class="form-control" id="upload" name="upload_title" style="display: none;">
                                    </div>

                                    <button type="button" id="removeImage" class="btn btn-danger btn-sm" style="display: none; width: 38px; height: 38px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>



                            <div class="form-group mb-3 mt-3">
                                <div id="description-editor" class="form-control" style="height: 280px;">
                                </div>
                                <input type="hidden" id="description" name="description">
                            </div>


                            <!-- Action Buttons -->
                            <div class="form-actions d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </main>

    </div>

    <?php include('footer_content_admin.php'); ?>
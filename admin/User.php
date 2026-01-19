<?php
// SỬA 1: Chỉ bắt đầu session nếu nó chưa được bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../config/db.php";

// --- BẢO VỆ TRANG ADMIN ---
if (!isset($_SESSION['admin']) || $_SESSION['admin']['roles'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// *** SỬA LỖI LINK (QUAN TRỌNG) ***
$current_page = htmlspecialchars($_SERVER['PHP_SELF']);
$query_params = $_GET;

// 1. Tạo link TRANG DANH SÁCH (loại bỏ action, id, search)
$list_query_params = $query_params;
unset($list_query_params['action']);
unset($list_query_params['id']);
unset($list_query_params['search']); // Đã sửa lỗi "Hiển thị tất cả"
$list_link = $current_page . '?' . http_build_query($list_query_params);

// 2. Tạo link THÊM MỚI (dựa trên link danh sách)
$add_query_params = $list_query_params;
$add_query_params['action'] = 'add';
$add_link = $current_page . '?' . http_build_query($add_query_params);
// *************************************


// Lấy hành động từ URL, mặc định là 'list' (hiển thị danh sách)
$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? null; // Lấy ID cho hành động Sửa/Xóa
$search_term = trim($_GET['search'] ?? '');


// Biến cho các thông báo
$error_msg = "";
$success_msg = "";

// Lấy và xóa các thông báo từ session (Flash Messages)
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}


// ------------------------------------------------------------------
// --- XỬ LÝ LOGIC (POST VÀ DELETE) ---
// ------------------------------------------------------------------

// --- 1. HÀNH ĐỘNG: XÓA ---
if ($action === 'delete' && $user_id) {

    if ($user_id == $_SESSION['admin']['ID_user']) {
        $_SESSION['error_msg'] = "Không thể tự xóa tài khoản admin đang đăng nhập!";
    } else {
        try {
            // Tạm thời tắt kiểm tra khóa ngoại
            $conn->exec("SET FOREIGN_KEY_CHECKS=0");

            $stmt = $conn->prepare("DELETE FROM users WHERE ID_user = ?");
            $stmt->execute([$user_id]);

            // Bật lại kiểm tra khóa ngoại
            $conn->exec("SET FOREIGN_KEY_CHECKS=1");

            $_SESSION['success_msg'] = "Đã xóa người dùng thành công.";
        } catch (PDOException $e) {
            $conn->exec("SET FOREIGN_KEY_CHECKS=1");
            $_SESSION['error_msg'] = "Lỗi CSDL: " . $e->getMessage();
        }
    }
    header("Location: $list_link");
    exit();
}

// --- 2. HÀNH ĐỘNG: THÊM MỚI (ĐÃ KHÔI PHỤC) ---
if ($action === 'add' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $roles = trim($_POST['roles']);

    // --- VALIDATION CHO THÊM MỚI ---
    if (empty($username) || empty($password) || empty($name) || empty($email) || empty($phone)) {
        $error_msg = "Vui lòng nhập đầy đủ các trường bắt buộc.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "❌ Định dạng Email không hợp lệ. (ví dụ: example@gmail.com)";
    } else if (!preg_match('/^0\d{9}$/', $phone)) {
        $error_msg = "❌ Định dạng Số điện thoại không hợp lệ. (Yêu cầu 10 số, bắt đầu bằng 0)";
    } else {
        try {
            // Kiểm tra username/email trùng lặp
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE Username = ? OR Email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = "Tên đăng nhập hoặc Email đã tồn tại.";
            } else {
                // Thêm người dùng mới
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (Username, Password, Name, Email, Phone, roles) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $name, $email, $phone, $roles]);

                $_SESSION['success_msg'] = "Đã thêm người dùng '{$username}' thành công!";
                header("Location: $list_link");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}

// --- 3. HÀNH ĐỘNG: SỬA (Khi submit form 'edit') ---
if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] === "POST" && $user_id) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $roles = trim($_POST['roles']);

    // *** THÊM LẠI LOGIC SỬA MẬT KHẨU ***
    $password = trim($_POST['password']);

    // --- CẬP NHẬT VALIDATION ---
    if (empty($name) || empty($email) || empty($phone)) {
        $error_msg = "Họ tên, Email và SĐT không được để trống.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "❌ Định dạng Email không hợp lệ. (ví dụ: example@gmail.com)";
    } else if (!preg_match('/^0\d{9}$/', $phone)) {
        $error_msg = "❌ Định dạng Số điện thoại không hợp lệ. (Yêu cầu 10 số, bắt đầu bằng 0)";
    } else {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE Email = ? AND ID_user != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $error_msg = "Email này đã được sử dụng bởi một tài khoản khác.";
            } else {

                // *** THÊM LẠI LOGIC SỬA MẬT KHẨU ***
                if (!empty($password)) {
                    // Nếu CÓ mật khẩu mới, băm và cập nhật
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET Name = ?, Email = ?, Phone = ?, roles = ?, Password = ? WHERE ID_user = ?");
                    $stmt->execute([$name, $email, $phone, $roles, $hashed_password, $user_id]);
                } else {
                    // Nếu KHÔNG có mật khẩu mới, giữ nguyên mật khẩu cũ
                    $stmt = $conn->prepare("UPDATE users SET Name = ?, Email = ?, Phone = ?, roles = ? WHERE ID_user = ?");
                    $stmt->execute([$name, $email, $phone, $roles, $user_id]);
                }
                // **********************************

                $_SESSION['success_msg'] = "Cập nhật người dùng thành công!";
                header("Location: $list_link");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}

// ------------------------------------------------------------------
// --- LẤY DỮ LIỆU ĐỂ HIỂN THỊ (CHO LIST VÀ EDIT) ---
// ------------------------------------------------------------------

$users = []; // Cho action 'list'
$user_to_edit = null; // Cho action 'edit'

if ($action === 'list') {
    try {
        // Thêm lại cột Password vào SELECT
        $sql = "SELECT ID_user, Username, Name, Email, Phone, Password, roles FROM users";
        $params = [];
        if (!empty($search_term)) {
            $sql .= " WHERE (Username LIKE ? OR Name LIKE ? OR Email LIKE ? OR Phone LIKE ?)";
            $like_term = "%" . $search_term . "%";
            $params = [$like_term, $like_term, $like_term, $like_term];
        }
        $sql .= " ORDER BY ID_user ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_msg = "Lỗi truy vấn CSDL: " . $e->getMessage();
    }
}

if ($action === 'edit' && $user_id) {
    try {
        $stmt = $conn->prepare("SELECT Username, Name, Email, Phone, roles FROM users WHERE ID_user = ?");
        $stmt->execute([$user_id]);
        $user_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user_to_edit) {
            $_SESSION['error_msg'] = "Không tìm thấy người dùng.";
            header("Location: $list_link");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Lỗi CSDL: " . $e->getMessage();
        header("Location: $list_link");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        // Thêm lại tiêu đề 'Thêm Người Dùng'
        if ($action === 'add') echo 'Thêm Người Dùng';
        else if ($action === 'edit') echo 'Sửa Người Dùng';
        else echo 'Quản Lý Người Dùng';
        ?>
    </title>
    <style>
        /* (CSS không đổi) */
        

        h1 {
            color: #D90429;
            text-align: center;
            border-bottom: 2px solid #D90429;
            padding-bottom: 10px;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .success {
            color: green;
            background: #e0ffe0;
            border: 1px solid green;
        }

        .error {
            color: red;
            background: #ffe0e0;
            border: 1px solid red;
        }

        .add-button {
            display: inline-block;
            background-color: #D90429;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .add-button:hover {
            background-color: #b00321;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #D90429;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .action-links {
            text-align: center;
        }

        .action-links a {
            display: inline-block;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
            margin-right: 8px;
            margin-bottom: 5px;
        }

        .action-links a:last-child {
            margin-right: 0;
        }

        .action-edit {
            background-color: #007bff;
        }

        .action-delete {
            background-color: #D90429;
        }

        .hashed-password {
            max-width: 200px;
            word-break: break-all;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9em;
            color: #555;
            background: #eee;
            padding: 5px;
            border-radius: 4px;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group input[disabled] {
            background-color: #eee;
            cursor: not-allowed;
        }

        .button-group {
            margin-top: 20px;
            text-align: right;
        }

        .button-group button {
            background-color: #D90429;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .button-group button:hover {
            background-color: #b00321;
        }

        .button-group a {
            display: inline-block;
            margin-right: 10px;
            color: #555;
            text-decoration: none;
        }

        .search-form {
            margin: 15px 0;
            display: flex;
            gap: 10px;
        }

        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-form button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #0056b3;
        }

        .search-form .clear-search {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }

        .search-form .clear-search:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container">

        <?php if ($success_msg): ?>
            <p class="message success" id="auto-hide-alert"><?php echo htmlspecialchars($success_msg); ?></p>
        <?php endif; ?>
        <?php if ($error_msg && $action === 'list'): ?>
            <p class="message error"><?php echo htmlspecialchars($error_msg); ?></p>
        <?php endif; ?>


        <?php
        switch ($action):

                // --- GIAO DIỆN: THÊM MỚI (ĐÃ KHÔI PHỤC) ---
            case 'add':
        ?>
                <div class="form-container">
                    

                    <?php if ($error_msg): ?>
                        <p class="message error"><?php echo htmlspecialchars($error_msg); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Tên đăng nhập (*)</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Mật khẩu (*)</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Họ và Tên (*)</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email (*)</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại (*)</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="roles">Quyền (Roles)</label>
                            <select id="roles" name="roles">
                                <option value="user" <?php if (($_POST['roles'] ?? 'user') == 'user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if (($_POST['roles'] ?? 'user') == 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="button-group">
                            <a href="<?php echo $list_link; ?>">Hủy bỏ</a>
                            <button type="submit">Thêm Mới</button>
                        </div>
                    </form>
                </div>
            <?php
                break;

            // --- GIAO DIỆN: SỬA ---
            case 'edit':
            ?>
                <div class="form-container">
                    <h1>Sửa Người Dùng: <?php echo htmlspecialchars($user_to_edit['Username']); ?></h1>

                    <?php if ($error_msg): ?>
                        <p class="message error"><?php echo htmlspecialchars($error_msg); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Tên đăng nhập</label>
                            <input type="text" value="<?php echo htmlspecialchars($user_to_edit['Username']); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="password">Mật khẩu mới</label>
                            <input type="password" id="password" name="password" placeholder="Bỏ trống nếu không muốn thay đổi">
                        </div>
                        <div class="form-group">
                            <label for="name">Họ và Tên (*)</label>
                            <input type="text" id="name" name="name"
                                value="<?php echo htmlspecialchars($_POST['name'] ?? $user_to_edit['Name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email (*)</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? $user_to_edit['Email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại (*)</label>
                            <input type="text" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? $user_to_edit['Phone']); ?>" required>
                        </div>

            <?php
                break;

            // --- GIAO DIỆN: DANH SÁCH (MẶC ĐỊNH) ---
            case 'list':
            default:
            ?>
                <h1>Quản Lý Người Dùng</h1>


                <form action="<?php echo $current_page; ?>" method="GET" class="search-form">
                    <?php foreach ($list_query_params as $key => $value): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endforeach; ?>

                    <input type="text" name="search" placeholder="Tìm theo tên, username, email, SĐT..."
                        value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit">Tìm Kiếm</button>

                    <?php if (!empty($search_term)): ?>
                        <a href="<?php echo $list_link; ?>" class="clear-search">Hiển thị tất cả</a>
                    <?php endif; ?>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ và Tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Mật khẩu</th>
                            <th>Quyền (Roles)</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">
                                    <?php if (!empty($search_term)): ?>
                                        Không tìm thấy người dùng nào khớp với "<?php echo htmlspecialchars($search_term); ?>".
                                    <?php else: ?>
                                        Không tìm thấy người dùng nào.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['ID_user']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Phone']); ?></td>

                                    <td>
                                        <div class="hashed-password">
                                            <?php echo htmlspecialchars($user['Password']); ?>
                                        </div>
                                    </td>

                                    <td><?php echo htmlspecialchars($user['roles']); ?></td>
                                    <td class="action-links">
                                        <?php
                                        // Tạo link Sửa

                                        // Tạo link Xóa
                                        $delete_query_params = $list_query_params;
                                        $delete_query_params['action'] = 'delete';
                                        $delete_query_params['id'] = $user['ID_user'];
                                        $delete_link = $current_page . '?' . http_build_query($delete_query_params);
                                        ?>
                                        <a href="<?php echo $delete_link; ?>" class="action-delete"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
        <?php
                break;

        endswitch;
        ?>

    </div>

    <script>
        const alertElement = document.getElementById('auto-hide-alert');
        if (alertElement) {
            setTimeout(() => {
                alertElement.style.transition = 'opacity 0.5s ease';
                alertElement.style.opacity = '0';
                setTimeout(() => {
                    alertElement.style.display = 'none';
                }, 500);
            }, 1000); // 1 giây
        }
    </script>
</body>

</html>
<?php
require_once "config/database.php";

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

// Handle login
if ($_POST && isset($_POST["action"]) && $_POST["action"] === "login") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_email"] = $user["email"];
        $_SESSION["user_role"] = $user["role"];
        $_SESSION["user_function"] = $user["function_role"];
        header("Location: ?page=dashboard");
        exit;
    } else {
        $login_error = "Onjuiste inloggegevens";
    }
}

// Handle logout
if (isset($_GET["action"]) && $_GET["action"] === "logout") {
    session_destroy();
    header("Location: ?page=login");
    exit;
}

// Handle password change by admin
if ($_POST && isset($_POST["action"]) && $_POST["action"] === "change_password" && $_SESSION["user_role"] === "admin") {
    $user_id = $_POST["user_id"];
    $new_password = $_POST["new_password"];
    
    if (strlen($new_password) >= 6) {
        $pdo = getDB();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user_id])) {
            $_SESSION["success_message"] = "Wachtwoord succesvol gewijzigd";
        } else {
            $_SESSION["error_message"] = "Fout bij wijzigen wachtwoord";
        }
    } else {
        $_SESSION["error_message"] = "Wachtwoord moet minimaal 6 karakters zijn";
    }
    
    header("Location: ?page=users");
    exit;
}
?>
<?php
require_once "demo_db.php";

// Handle login
if ($_POST && isset($_POST["action"]) && $_POST["action"] === "login") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $db = getDemoDB();
    $user = $db->getUserByEmail($email);
    
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
        $db = getDemoDB();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        if ($db->updateUserPassword($user_id, $hashed_password)) {
            $_SESSION["success_message"] = "Wachtwoord succesvol gewijzigd voor gebruiker!";
        } else {
            $_SESSION["error_message"] = "Fout bij wijzigen wachtwoord";
        }
    } else {
        $_SESSION["error_message"] = "Wachtwoord moet minimaal 6 karakters zijn";
    }
    
    header("Location: ?page=users");
    exit;
}

// Handle user creation
if ($_POST && isset($_POST["action"]) && $_POST["action"] === "create_user" && $_SESSION["user_role"] === "admin") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $function_role = $_POST["function_role"] ?? null;
    
    if (strlen($password) >= 6) {
        $db = getDemoDB();
        
        // Check if email already exists
        $existingUser = $db->getUserByEmail($email);
        if (!$existingUser) {
            $userData = [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'function_role' => $function_role,
                'is_active' => true
            ];
            
            $db->createUser($userData);
            $_SESSION["success_message"] = "Gebruiker '$name' succesvol aangemaakt!";
        } else {
            $_SESSION["error_message"] = "E-mailadres bestaat al";
        }
    } else {
        $_SESSION["error_message"] = "Wachtwoord moet minimaal 6 karakters zijn";
    }
    
    header("Location: ?page=users");
    exit;
}

// Handle user deletion
if (isset($_GET["delete_user"]) && $_SESSION["user_role"] === "admin") {
    $user_id = $_GET["delete_user"];
    if ($user_id !== $_SESSION["user_id"]) { // Can't delete yourself
        $db = getDemoDB();
        $db->deactivateUser($user_id);
        $_SESSION["success_message"] = "Gebruiker succesvol gedeactiveerd";
    }
    header("Location: ?page=users");
    exit;
}
?>
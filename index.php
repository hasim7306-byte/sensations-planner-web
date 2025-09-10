<?php
session_start();
require_once "config/database.php";

// Simple routing
$page = $_GET["page"] ?? "dashboard";

if (!isset($_SESSION["user_id"]) && $page !== "login") {
    $page = "login";
}

include "auth.php";

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensations To Go Planner v2.0</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🔴</text></svg>">
</head>
<body>
    <div id="app">
        <?php if (isset($_SESSION["user_id"])): ?>
            <nav class="navbar">
                <div class="nav-brand">
                    <span class="logo">S</span>
                    <span class="brand-text">Sensations To Go <span class="version-badge">v2.0</span></span>
                </div>
                <div class="nav-menu" id="navMenu">
                    <a href="?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        🏠 Dashboard
                    </a>
                    <a href="?page=schedule" class="nav-link <?php echo $page === 'schedule' ? 'active' : ''; ?>">
                        📅 Roosters
                    </a>
                    <a href="?page=time" class="nav-link <?php echo $page === 'time' ? 'active' : ''; ?>">
                        ⏰ Tijd
                    </a>
                    <a href="?page=leave" class="nav-link <?php echo $page === 'leave' ? 'active' : ''; ?>">
                        🏖️ Verlof
                    </a>
                    <a href="?page=chat" class="nav-link <?php echo $page === 'chat' ? 'active' : ''; ?>">
                        💬 Chat
                    </a>
                    <?php if ($_SESSION["user_role"] === "admin"): ?>
                        <a href="?page=users" class="nav-link <?php echo $page === 'users' ? 'active' : ''; ?>">
                            👥 Gebruikers
                        </a>
                    <?php endif; ?>
                    <div class="nav-user">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
                        <span class="user-role"><?php echo ucfirst($_SESSION["user_role"]); ?></span>
                        <a href="?action=logout" class="nav-link logout">Uitloggen</a>
                    </div>
                </div>
                <div class="mobile-toggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        <?php endif; ?>
        
        <main class="main-content">
            <?php
            switch ($page) {
                case "login":
                    include "login.php";
                    break;
                case "dashboard":
                    include "dashboard.php";
                    break;
                case "schedule":
                    include "schedule.php";
                    break;
                case "time":
                    include "time.php";
                    break;
                case "leave":
                    include "leave.php";
                    break;
                case "chat":
                    include "chat.php";
                    break;
                case "users":
                    if ($_SESSION["user_role"] === "admin") {
                        include "users.php";
                    } else {
                        echo "<h1>Geen toegang</h1>";
                    }
                    break;
                default:
                    include "dashboard.php";
            }
            ?>
        </main>
    </div>
    
    <script src="assets/js/app.js"></script>
</body>
</html>
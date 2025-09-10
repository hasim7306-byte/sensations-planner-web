<?php
session_start();

// Simple routing
$page = $_GET["page"] ?? "dashboard";

if (!isset($_SESSION["user_id"]) && $page !== "login") {
    $page = "login";
}

include "demo_auth.php";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensations To Go Planner v2.0 - LIVE DEMO</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🔴</text></svg>">
    <style>
        .demo-banner {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            color: white;
            text-align: center;
            padding: 0.75rem;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1001;
            animation: pulse 3s infinite;
        }
        .demo-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="demo-banner">
        🎉 LIVE DEMO - Enhanced v2.0 met Admin Wachtwoord Beheer
        <span class="demo-badge">WERKENDE VERSIE</span>
    </div>
    
    <div id="app">
        <?php if (isset($_SESSION["user_id"])): ?>
            <nav class="navbar">
                <div class="nav-brand">
                    <span class="logo">S</span>
                    <span class="brand-text">Sensations To Go <span class="version-badge">DEMO v2.0</span></span>
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
                    include "demo_login.php";
                    break;
                case "dashboard":
                    include "demo_dashboard.php";
                    break;
                case "users":
                    if ($_SESSION["user_role"] === "admin") {
                        include "demo_users.php";
                    } else {
                        echo "<h1>🚫 Geen toegang - Alleen voor Admins</h1>";
                    }
                    break;
                case "schedule":
                    echo "<div class='coming-soon'><h1>📅 Roosters</h1><p>Demo functionaliteit - Enhanced scheduling met employee functions komt hier</p></div>";
                    break;
                case "time":
                    echo "<div class='coming-soon'><h1>⏰ Tijdregistratie</h1><p>Demo functionaliteit - Geïntegreerde tijd tracking komt hier</p></div>";
                    break;
                case "leave":
                    echo "<div class='coming-soon'><h1>🏖️ Verlofbeheer</h1><p>Demo functionaliteit - Leave management met approvals komt hier</p></div>";
                    break;
                case "chat":
                    echo "<div class='coming-soon'><h1>💬 Team Chat</h1><p>Demo functionaliteit - Real-time team communicatie komt hier</p></div>";
                    break;
                default:
                    include "demo_dashboard.php";
            }
            ?>
        </main>
    </div>
    
    <script src="assets/js/app.js"></script>
    
    <style>
    .coming-soon {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin: 2rem auto;
        max-width: 600px;
    }
    .coming-soon h1 {
        color: #1f2937;
        margin-bottom: 1rem;
        font-size: 2.5rem;
    }
    .coming-soon p {
        color: #6b7280;
        font-size: 1.125rem;
    }
    </style>
</body>
</html>
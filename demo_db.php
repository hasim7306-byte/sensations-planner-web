<?php
/**
 * Demo Database Simulator - File-based storage for demo purposes
 */

class DemoDatabase {
    private $dataDir = 'demo_data';
    
    public function __construct() {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
        $this->initializeDemoData();
    }
    
    private function initializeDemoData() {
        $usersFile = $this->dataDir . '/users.json';
        if (!file_exists($usersFile)) {
            $users = [
                [
                    'id' => 'admin-001',
                    'email' => 'admin@sensationstogo.nl',
                    'name' => 'System Administrator',
                    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'function_role' => null,
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 'emp-001',
                    'email' => 'jan@sensationstogo.nl',
                    'name' => 'Jan de Vries',
                    'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
                    'role' => 'employee',
                    'function_role' => 'bezorger_fiets',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 'emp-002',
                    'email' => 'lisa@sensationstogo.nl',
                    'name' => 'Lisa Peters',
                    'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
                    'role' => 'employee',
                    'function_role' => 'bezorger_auto',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 'emp-003',
                    'email' => 'mohammed@sensationstogo.nl',
                    'name' => 'Mohammed Ali',
                    'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
                    'role' => 'employee',
                    'function_role' => 'keuken',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 'emp-004',
                    'email' => 'sarah@sensationstogo.nl',
                    'name' => 'Sarah van Dam',
                    'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
                    'role' => 'employee',
                    'function_role' => 'balie_medewerker',
                    'is_active' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        }
        
        // Initialize other demo data
        $this->initializeShifts();
        $this->initializeLeaveRequests();
        $this->initializeChatMessages();
    }
    
    private function initializeShifts() {
        $shiftsFile = $this->dataDir . '/shifts.json';
        if (!file_exists($shiftsFile)) {
            $shifts = [
                [
                    'id' => 'shift-001',
                    'employee_id' => 'emp-001',
                    'date' => date('Y-m-d'),
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'break_duration' => 30,
                    'function_required' => 'bezorger_fiets',
                    'status' => 'scheduled',
                    'created_by' => 'admin-001',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 'shift-002',
                    'employee_id' => 'emp-002',
                    'date' => date('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time' => '18:00:00',
                    'break_duration' => 30,
                    'function_required' => 'bezorger_auto',
                    'status' => 'scheduled',
                    'created_by' => 'admin-001',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            file_put_contents($shiftsFile, json_encode($shifts, JSON_PRETTY_PRINT));
        }
    }
    
    private function initializeLeaveRequests() {
        $leaveFile = $this->dataDir . '/leave_requests.json';
        if (!file_exists($leaveFile)) {
            $leaves = [
                [
                    'id' => 'leave-001',
                    'employee_id' => 'emp-003',
                    'leave_type' => 'vacation',
                    'start_date' => date('Y-m-d', strtotime('+7 days')),
                    'end_date' => date('Y-m-d', strtotime('+10 days')),
                    'reason' => 'Vakantie met familie',
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            file_put_contents($leaveFile, json_encode($leaves, JSON_PRETTY_PRINT));
        }
    }
    
    private function initializeChatMessages() {
        $chatFile = $this->dataDir . '/chat_messages.json';
        if (!file_exists($chatFile)) {
            $messages = [
                [
                    'id' => 'msg-001',
                    'sender_id' => 'admin-001',
                    'sender_name' => 'Systeem',
                    'message' => '🎉 Welkom bij Sensations To Go Planner Enhanced v2.0! Nieuwe features: Employee Functions & Admin Wachtwoord Beheer!',
                    'is_system' => true,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            file_put_contents($chatFile, json_encode($messages, JSON_PRETTY_PRINT));
        }
    }
    
    public function getUserByEmail($email) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['is_active']) {
                return $user;
            }
        }
        return null;
    }
    
    public function getUsers() {
        $usersFile = $this->dataDir . '/users.json';
        if (!file_exists($usersFile)) return [];
        return json_decode(file_get_contents($usersFile), true);
    }
    
    public function updateUserPassword($userId, $newPasswordHash) {
        $users = $this->getUsers();
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['password_hash'] = $newPasswordHash;
                break;
            }
        }
        file_put_contents($this->dataDir . '/users.json', json_encode($users, JSON_PRETTY_PRINT));
        return true;
    }
    
    public function createUser($userData) {
        $users = $this->getUsers();
        $userData['id'] = 'user-' . uniqid();
        $userData['created_at'] = date('Y-m-d H:i:s');
        $users[] = $userData;
        file_put_contents($this->dataDir . '/users.json', json_encode($users, JSON_PRETTY_PRINT));
        return $userData['id'];
    }
    
    public function deactivateUser($userId) {
        $users = $this->getUsers();
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['is_active'] = false;
                break;
            }
        }
        file_put_contents($this->dataDir . '/users.json', json_encode($users, JSON_PRETTY_PRINT));
        return true;
    }
    
    public function getActiveUsers() {
        $users = $this->getUsers();
        return array_filter($users, function($user) {
            return $user['is_active'];
        });
    }
    
    public function getTodayShifts() {
        $shiftsFile = $this->dataDir . '/shifts.json';
        if (!file_exists($shiftsFile)) return [];
        $shifts = json_decode(file_get_contents($shiftsFile), true);
        return array_filter($shifts, function($shift) {
            return $shift['date'] === date('Y-m-d');
        });
    }
    
    public function getPendingLeaveRequests() {
        $leaveFile = $this->dataDir . '/leave_requests.json';
        if (!file_exists($leaveFile)) return [];
        $leaves = json_decode(file_get_contents($leaveFile), true);
        return array_filter($leaves, function($leave) {
            return $leave['status'] === 'pending';
        });
    }
    
    public function getFunctionStats() {
        $users = $this->getActiveUsers();
        $stats = [];
        foreach ($users as $user) {
            if ($user['function_role']) {
                $stats[$user['function_role']] = ($stats[$user['function_role']] ?? 0) + 1;
            }
        }
        return $stats;
    }
}

// Global demo database instance
$GLOBALS['demo_db'] = new DemoDatabase();

function getDemoDB() {
    return $GLOBALS['demo_db'];
}
?>
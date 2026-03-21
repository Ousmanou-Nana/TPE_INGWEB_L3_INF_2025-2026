<?php

class AdminController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->requireAdmin();
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    public function dashboard(): void {
        $stats = [
            'teachers' => $this->db->fetchOne('SELECT COUNT(*) as c FROM teachers')['c'],
            'subjects' => $this->db->fetchOne('SELECT COUNT(*) as c FROM subjects')['c'],
            'classes'  => $this->db->fetchOne('SELECT COUNT(*) as c FROM classes')['c'],
            'rooms'    => $this->db->fetchOne('SELECT COUNT(*) as c FROM rooms')['c'],
            'generations' => $this->db->fetchOne('SELECT COUNT(*) as c FROM timetable_generations')['c'],
        ];
        $recent = $this->db->fetchAll(
            'SELECT * FROM timetable_generations ORDER BY created_at DESC LIMIT 5'
        );
        require __DIR__ . '/../views/admin/dashboard.php';
    }

}

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

    public function teachers(): void {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $nom   = trim($_POST['nom'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $pass  = $_POST['password'] ?? 'password';
                if ($nom && $email) {
                    $hash = password_hash($pass, PASSWORD_BCRYPT);
                    $uid  = $this->db->insert(
                        'INSERT INTO users (nom, email, mot_de_passe, role) VALUES (?,?,?,?)',
                        [$nom, $email, $hash, 'teacher']
                    );
                    $this->db->insert('INSERT INTO teachers (user_id) VALUES (?)', [$uid]);
                    $message = 'Enseignant créé avec succès.';
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                // Get user_id first
                $t = $this->db->fetchOne('SELECT user_id FROM teachers WHERE id=?', [$id]);
                if ($t) {
                    $this->db->execute('DELETE FROM users WHERE id=?', [$t['user_id']]);
                }
                $message = 'Enseignant supprimé.';
            } elseif ($action === 'edit') {
                $uid = (int)($_POST['user_id'] ?? 0);
                $nom = trim($_POST['nom'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $this->db->execute('UPDATE users SET nom=?, email=? WHERE id=?', [$nom, $email, $uid]);
                $message = 'Enseignant modifié.';
            }
        }
        $teachers = $this->db->fetchAll(
            'SELECT t.id, u.id as user_id, u.nom, u.email FROM teachers t JOIN users u ON t.user_id=u.id ORDER BY u.nom'
        );
        require __DIR__ . '/../views/admin/teachers.php';
    }

}

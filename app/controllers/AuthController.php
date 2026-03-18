<?php
// /app/controllers/AuthController.php

class AuthController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Veuillez remplir tous les champs.';
                require __DIR__ . '/../views/auth/login.php';
                return;
            }

            $user = $this->db->fetchOne(
                'SELECT * FROM users WHERE email = ?',
                [$email]
            );

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_nom']  = $user['nom'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: /admin/dashboard');
                } else {
                    // Get teacher_id
                    $teacher = $this->db->fetchOne(
                        'SELECT id FROM teachers WHERE user_id = ?',
                        [$user['id']]
                    );
                    $_SESSION['teacher_id'] = $teacher['id'] ?? null;
                    header('Location: /teacher/preferences');
                }
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void {
        session_destroy();
        header('Location: /login');
        exit;
    }
}

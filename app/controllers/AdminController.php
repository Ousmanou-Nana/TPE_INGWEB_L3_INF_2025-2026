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

   public function subjects(): void {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $nom    = trim($_POST['nom'] ?? '');
                $color  = $_POST['couleur'] ?? '#4A90E2';
                $heures = (int)($_POST['heures'] ?? 2);
                if ($nom) {
                    $this->db->insert('INSERT INTO subjects (nom, couleur, heures_par_semaine) VALUES (?,?,?)', [$nom, $color, $heures]);
                    $message = 'Matière créée.';
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                $this->db->execute('DELETE FROM subjects WHERE id=?', [$id]);
                $message = 'Matière supprimée.';
            } elseif ($action === 'edit') {
                $id     = (int)($_POST['id'] ?? 0);
                $nom    = trim($_POST['nom'] ?? '');
                $color  = $_POST['couleur'] ?? '#4A90E2';
                $heures = (int)($_POST['heures'] ?? 2);
                $this->db->execute('UPDATE subjects SET nom=?, couleur=?, heures_par_semaine=? WHERE id=?', [$nom, $color, $heures, $id]);
                $message = 'Matière modifiée.';
            }
        }
        $subjects = $this->db->fetchAll('SELECT * FROM subjects ORDER BY nom');
        require __DIR__ . '/../views/admin/subjects.php';
    }

   public function classes(): void {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $nom = trim($_POST['nom'] ?? '');
                $eff = (int)($_POST['effectif'] ?? 30);
                if ($nom) {
                    $this->db->insert('INSERT INTO classes (nom, effectif) VALUES (?,?)', [$nom, $eff]);
                    $message = 'Classe créée.';
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                $this->db->execute('DELETE FROM classes WHERE id=?', [$id]);
                $message = 'Classe supprimée.';
            } elseif ($action === 'edit') {
                $id  = (int)($_POST['id'] ?? 0);
                $nom = trim($_POST['nom'] ?? '');
                $eff = (int)($_POST['effectif'] ?? 30);
                $this->db->execute('UPDATE classes SET nom=?, effectif=? WHERE id=?', [$nom, $eff, $id]);
                $message = 'Classe modifiée.';
            }
        }
        $classes = $this->db->fetchAll('SELECT * FROM classes ORDER BY nom');
        require __DIR__ . '/../views/admin/classes.php';
    }

   public function rooms(): void {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $nom = trim($_POST['nom'] ?? '');
                $cap = (int)($_POST['capacite'] ?? 30);
                if ($nom) {
                    $this->db->insert('INSERT INTO rooms (nom, capacite) VALUES (?,?)', [$nom, $cap]);
                    $message = 'Salle créée.';
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                $this->db->execute('DELETE FROM rooms WHERE id=?', [$id]);
                $message = 'Salle supprimée.';
            } elseif ($action === 'edit') {
                $id  = (int)($_POST['id'] ?? 0);
                $nom = trim($_POST['nom'] ?? '');
                $cap = (int)($_POST['capacite'] ?? 30);
                $this->db->execute('UPDATE rooms SET nom=?, capacite=? WHERE id=?', [$nom, $cap, $id]);
                $message = 'Salle modifiée.';
            }
        }
        $rooms = $this->db->fetchAll('SELECT * FROM rooms ORDER BY nom');
        require __DIR__ . '/../views/admin/rooms.php';
    }
  public function assignments(): void {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'assign_teacher') {
                $tid = (int)($_POST['teacher_id'] ?? 0);
                $sid = (int)($_POST['subject_id'] ?? 0);
                if ($tid && $sid) {
                    try {
                        $this->db->insert('INSERT IGNORE INTO teacher_subject VALUES (?,?)', [$tid, $sid]);
                        $message = 'Assignation enseignant-matière effectuée.';
                    } catch (Exception $e) {
                        $message = 'Erreur: ' . $e->getMessage();
                    }
                }
            } elseif ($action === 'remove_teacher') {
                $tid = (int)($_POST['teacher_id'] ?? 0);
                $sid = (int)($_POST['subject_id'] ?? 0);
                $this->db->execute('DELETE FROM teacher_subject WHERE teacher_id=? AND subject_id=?', [$tid, $sid]);
                $message = 'Assignation supprimée.';
            } elseif ($action === 'assign_class') {
                $cid    = (int)($_POST['class_id'] ?? 0);
                $sid    = (int)($_POST['subject_id'] ?? 0);
                $heures = (int)($_POST['heures'] ?? 2);
                if ($cid && $sid) {
                    $this->db->insert(
                        'INSERT INTO class_subject (class_id, subject_id, heures_par_semaine) VALUES (?,?,?) ON DUPLICATE KEY UPDATE heures_par_semaine=?',
                        [$cid, $sid, $heures, $heures]
                    );
                    $message = 'Assignation classe-matière effectuée.';
                }
            } elseif ($action === 'remove_class') {
                $cid = (int)($_POST['class_id'] ?? 0);
                $sid = (int)($_POST['subject_id'] ?? 0);
                $this->db->execute('DELETE FROM class_subject WHERE class_id=? AND subject_id=?', [$cid, $sid]);
                $message = 'Assignation supprimée.';
            }
        }

        $teachers = $this->db->fetchAll('SELECT t.id, u.nom FROM teachers t JOIN users u ON t.user_id=u.id ORDER BY u.nom');
        $subjects = $this->db->fetchAll('SELECT * FROM subjects ORDER BY nom');
        $classes  = $this->db->fetchAll('SELECT * FROM classes ORDER BY nom');

        $teacher_subjects = $this->db->fetchAll(
            'SELECT ts.teacher_id, ts.subject_id, u.nom as teacher_nom, s.nom as subject_nom
             FROM teacher_subject ts
             JOIN teachers t ON ts.teacher_id=t.id
             JOIN users u ON t.user_id=u.id
             JOIN subjects s ON ts.subject_id=s.id
             ORDER BY u.nom'
        );
        $class_subjects = $this->db->fetchAll(
            'SELECT cs.class_id, cs.subject_id, cs.heures_par_semaine, c.nom as class_nom, s.nom as subject_nom
             FROM class_subject cs
             JOIN classes c ON cs.class_id=c.id
             JOIN subjects s ON cs.subject_id=s.id
             ORDER BY c.nom'
        );

        require __DIR__ . '/../views/admin/assignments.php';
    }

    public function timetable(): void {
        $generations = $this->db->fetchAll(
            'SELECT * FROM timetable_generations ORDER BY created_at DESC'
        );
        $gen_id  = (int)($_GET['gen'] ?? 0);
        $class_id = (int)($_GET['class'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_gen') {
            $gid = (int)($_POST['gen_id'] ?? 0);
            $this->db->execute('DELETE FROM timetable WHERE generation_id=?', [$gid]);
            $this->db->execute('DELETE FROM timetable_generations WHERE id=?', [$gid]);
            header('Location: /admin/timetable');
            exit;
        }

        $timetable_data = [];
        $classes = $this->db->fetchAll('SELECT * FROM classes ORDER BY nom');

        if ($gen_id && $class_id) {
            $rows = $this->db->fetchAll(
                'SELECT tt.*, s.nom as subject_nom, s.couleur, u.nom as teacher_nom, r.nom as room_nom
                 FROM timetable tt
                 JOIN subjects s ON tt.subject_id=s.id
                 JOIN teachers t ON tt.teacher_id=t.id
                 JOIN users u ON t.user_id=u.id
                 JOIN rooms r ON tt.room_id=r.id
                 WHERE tt.generation_id=? AND tt.class_id=?
                 ORDER BY tt.jour, tt.periode',
                [$gen_id, $class_id]
            );
            foreach ($rows as $row) {
                $timetable_data[$row['jour']][$row['periode']] = $row;
            }
        }

        require __DIR__ . '/../views/admin/timetable.php';
    }

   public function generate(): void {
        require __DIR__ . '/../views/admin/generate.php';
    }

    public function runGeneration(): void {
        header('Content-Type: application/json');
        $nom = trim($_POST['nom'] ?? 'Emploi du temps ' . date('d/m/Y H:i'));

        require_once __DIR__ . '/../models/TimetableGenerator.php';
        $generator = new TimetableGenerator($this->db);
        $result = $generator->generate($nom);

        echo json_encode($result);
    }
}

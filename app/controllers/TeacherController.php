<?php


class TeacherController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->requireTeacher();
    }

    private function requireTeacher(): void {
        if (!isset($_SESSION['user_role'])) {
            header('Location: /login');
            exit;
        }
    }

    public function preferences(): void {
        $teacher_id = $_SESSION['teacher_id'] ?? 0;
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           
            $this->db->execute('DELETE FROM preferences WHERE teacher_id=?', [$teacher_id]);

            for ($jour = 1; $jour <= 5; $jour++) {
                for ($periode = 1; $periode <= 6; $periode++) {
                    $key   = "score_{$jour}_{$periode}";
                    $score = isset($_POST[$key]) ? max(-5, min(5, (int)$_POST[$key])) : 0;
                    $this->db->insert(
                        'INSERT INTO preferences (teacher_id, jour, periode, score) VALUES (?,?,?,?)',
                        [$teacher_id, $jour, $periode, $score]
                    );
                }
            }
            $message = 'Préférences sauvegardées avec succès !';
        }

   
        $prefs_raw = $this->db->fetchAll(
            'SELECT jour, periode, score FROM preferences WHERE teacher_id=?',
            [$teacher_id]
        );
        $prefs = [];
        foreach ($prefs_raw as $p) {
            $prefs[$p['jour']][$p['periode']] = $p['score'];
        }

        
        $active_gen = $this->db->fetchOne(
            'SELECT id FROM timetable_generations WHERE is_active=1 ORDER BY created_at DESC LIMIT 1'
        );
        $timetable_data = [];
        if ($active_gen) {
            $rows = $this->db->fetchAll(
                'SELECT tt.*, s.nom as subject_nom, s.couleur, c.nom as class_nom, r.nom as room_nom
                 FROM timetable tt
                 JOIN subjects s ON tt.subject_id=s.id
                 JOIN classes c ON tt.class_id=c.id
                 JOIN rooms r ON tt.room_id=r.id
                 WHERE tt.generation_id=? AND tt.teacher_id=?
                 ORDER BY tt.jour, tt.periode',
                [$active_gen['id'], $teacher_id]
            );
            foreach ($rows as $row) {
                $timetable_data[$row['jour']][$row['periode']] = $row;
            }
        }

        require __DIR__ . '/../views/teacher/preferences.php';
    }
}

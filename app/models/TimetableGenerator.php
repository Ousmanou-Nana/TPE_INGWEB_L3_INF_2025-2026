<?php


class TimetableGenerator
{
    private Database $db;

    private const DAYS    = [1, 2, 3, 4, 5];
    private const PERIODS = [1, 2, 3, 4, 5, 6];

    private const SA_INITIAL_TEMP = 10000.0;
    private const SA_COOLING_RATE = 0.995;
    private const SA_MIN_TEMP     = 0.1;
    private const SA_RESTARTS     = 50;

    private array $classes          = [];
    private array $rooms            = [];
    private array $teachers         = [];
    private array $subjects         = [];
    private array $class_subjects   = [];
    private array $teacher_subjects = [];
    private array $preferences      = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }


    public function generate(string $nom): array
    {
        $this->loadData();

        if (empty($this->classes) || empty($this->rooms) || empty($this->class_subjects)) {
            return ['success' => false, 'error' => 'Données insuffisantes. Vérifiez classes, salles et assignations.'];
        }

        $slots = $this->buildSlots();

        if (empty($slots)) {
            return ['success' => false, 'error' => 'Aucun cours à planifier. Vérifiez les assignations matière-classe et enseignant-matière.'];
        }
        $best_solution = $this->runSA($slots);
        $best_score    = $this->scoreTotal($best_solution);
        $conflicts     = $this->countConflicts($best_solution);
        $new_id = $this->db->insert(
            'INSERT INTO timetable_generations (nom, score_total, nb_conflits, is_active) VALUES (?,?,?,0)',
            [$nom, $best_score, $conflicts]
        );

        $last_score = $this->db->fetchOne('SELECT score_total,nb_conflits,id FROM timetable_generations  WHERE is_active = 1');

        if ($last_score['score_total'] < $best_score) {
            $gen_id = $new_id;
        } elseif ($last_score['score_total'] == $best_score) {
            if ($last_score['nb_conflits'] > $conflicts) {
                $gen_id = $new_id;
            } else {
                $gen_id = $last_score['id'];
            }
        } else {
            $gen_id = $last_score['id'];
        }


        $this->db->execute('UPDATE timetable_generations SET is_active=0 WHERE id != ?', [$gen_id]);
        $this->db->execute('UPDATE timetable_generations SET is_active=1 WHERE id = ?', [$gen_id]);

        $inserted = 0;
        foreach ($best_solution as $entry) {
            if ($entry['teacher_id'] && $entry['room_id']) {
                $this->db->insert(
                    'INSERT INTO timetable (generation_id, class_id, subject_id, teacher_id, room_id, jour, periode) VALUES (?,?,?,?,?,?,?)',
                    [$gen_id, $entry['class_id'], $entry['subject_id'], $entry['teacher_id'], $entry['room_id'], $entry['jour'], $entry['periode']]
                );
                $inserted++;
            }
        }

        return [
            'success'   => true,
            'gen_id'    => $gen_id,
            'score'     => $best_score,
            'conflicts' => $conflicts,
            'inserted'  => $inserted,
            'total'     => count($slots),
            'message'   => "Emploi du temps généré ! Score: {$best_score}, Conflits: {$conflicts}, Cours placés: {$inserted}/" . count($slots),
        ];
    }



    private function runSA(array $slots): array
    {
        $best_ever       = null;
        $best_ever_score = PHP_INT_MIN;

        for ($restart = 0; $restart < self::SA_RESTARTS; $restart++) {

            $current       = $this->generateInitial($slots);
            $current_score = $this->scoreTotal($current);

            $local_best       = $current;
            $local_best_score = $current_score;

            $temp = self::SA_INITIAL_TEMP;

            while ($temp > self::SA_MIN_TEMP) {

                $neighbour       = $this->perturb($current);
                $neighbour_score = $this->scoreTotal($neighbour);
                $delta           = $neighbour_score - $current_score;

                if ($delta > 0) {

                    $current       = $neighbour;
                    $current_score = $neighbour_score;
                } else {
                    if ((mt_rand() / mt_getrandmax()) < exp($delta / $temp)) {
                        $current       = $neighbour;
                        $current_score = $neighbour_score;
                    }
                }

                if ($current_score > $local_best_score) {
                    $local_best       = $current;
                    $local_best_score = $current_score;
                }

                $temp *= self::SA_COOLING_RATE;

                if ($local_best_score > 0 && $this->countConflicts($local_best) === 0) {
                    break;
                }
            }

            if ($local_best_score > $best_ever_score) {
                $best_ever_score = $local_best_score;
                $best_ever       = $local_best;
            }
        }

        return $best_ever;
    }


    private function perturb(array $solution): array
    {
        if (empty($solution)) return $solution;

        $n    = count($solution);
        $type = rand(0, 2);

        if ($type === 0) {
            $i = rand(0, $n - 1);
            $solution[$i]['jour']    = rand(1, 5);
            $solution[$i]['periode'] = rand(1, 6);
        } elseif ($type === 1 && $n >= 2) {
            $i = rand(0, $n - 1);
            do {
                $j = rand(0, $n - 1);
            } while ($j === $i);
            [$solution[$i]['jour'],    $solution[$j]['jour']]    = [$solution[$j]['jour'],    $solution[$i]['jour']];
            [$solution[$i]['periode'], $solution[$j]['periode']] = [$solution[$j]['periode'], $solution[$i]['periode']];
        } else {
            $i          = rand(0, $n - 1);
            $subject_id = $solution[$i]['subject_id'];
            $possible   = [];
            foreach ($this->teacher_subjects as $tid => $sids) {
                if (in_array($subject_id, $sids)) $possible[] = $tid;
            }
            if (count($possible) > 1) {
                $others = array_values(array_filter($possible, fn($tid) => $tid !== $solution[$i]['teacher_id']));
                if (!empty($others)) {
                    $solution[$i]['teacher_id'] = $others[array_rand($others)];
                }
            }
        }

        return $solution;
    }


    private function loadData(): void
    {
        $this->classes  = $this->db->fetchAll('SELECT * FROM classes');
        $this->rooms    = $this->db->fetchAll('SELECT * FROM rooms');
        $this->subjects = $this->db->fetchAll('SELECT * FROM subjects');

        $teachers_raw = $this->db->fetchAll('SELECT t.id, u.nom FROM teachers t JOIN users u ON t.user_id=u.id');
        foreach ($teachers_raw as $t) {
            $this->teachers[$t['id']] = $t;
        }

        $cs_raw = $this->db->fetchAll('SELECT class_id, subject_id, heures_par_semaine FROM class_subject');
        foreach ($cs_raw as $cs) {
            $this->class_subjects[$cs['class_id']][$cs['subject_id']] = $cs['heures_par_semaine'];
        }

        $ts_raw = $this->db->fetchAll('SELECT teacher_id, subject_id FROM teacher_subject');
        foreach ($ts_raw as $ts) {
            $this->teacher_subjects[$ts['teacher_id']][] = $ts['subject_id'];
        }

        $prefs_raw = $this->db->fetchAll('SELECT teacher_id, jour, periode, score FROM preferences');
        foreach ($prefs_raw as $p) {
            $this->preferences[$p['teacher_id']][$p['jour']][$p['periode']] = (int)$p['score'];
        }
    }


    private function buildSlots(): array
    {
        $slots = [];
        foreach ($this->class_subjects as $class_id => $subjects) {
            foreach ($subjects as $subject_id => $heures) {
                $teachers_for_subject = [];
                foreach ($this->teacher_subjects as $tid => $sids) {
                    if (in_array($subject_id, $sids)) $teachers_for_subject[] = $tid;
                }
                if (empty($teachers_for_subject)) continue;

                for ($h = 0; $h < $heures; $h++) {
                    $slots[] = [
                        'class_id'          => $class_id,
                        'subject_id'        => $subject_id,
                        'possible_teachers' => $teachers_for_subject,
                    ];
                }
            }
        }
        return $slots;
    }


    private function generateInitial(array $slots): array
    {
        $solution  = [];
        $all_slots = [];
        foreach (self::DAYS as $d) {
            foreach (self::PERIODS as $p) {
                $all_slots[] = ['jour' => $d, 'periode' => $p];
            }
        }

        shuffle($slots);

        foreach ($slots as $slot) {
            $shuffled_times = $all_slots;
            shuffle($shuffled_times);

            $placed = false;
            foreach ($shuffled_times as $time) {
                $teachers   = $slot['possible_teachers'];
                shuffle($teachers);
                $teacher_id = $teachers[0];

                $room = $this->pickRoom($slot['class_id']);
                if (!$room) continue;

                $solution[] = [
                    'class_id'   => $slot['class_id'],
                    'subject_id' => $slot['subject_id'],
                    'teacher_id' => $teacher_id,
                    'room_id'    => $room['id'],
                    'jour'       => $time['jour'],
                    'periode'    => $time['periode'],
                ];
                $placed = true;
                break;
            }

            if (!$placed) {
                $time       = $all_slots[array_rand($all_slots)];
                $solution[] = [
                    'class_id'   => $slot['class_id'],
                    'subject_id' => $slot['subject_id'],
                    'teacher_id' => $slot['possible_teachers'][0],
                    'room_id'    => $this->rooms[0]['id'] ?? 1,
                    'jour'       => $time['jour'],
                    'periode'    => $time['periode'],
                ];
            }
        }

        return $solution;
    }

    private function pickRoom(int $class_id): ?array
    {
        $class = null;
        foreach ($this->classes as $c) {
            if ($c['id'] == $class_id) {
                $class = $c;
                break;
            }
        }
        $effectif = $class['effectif'] ?? 30;

        $suitable = array_filter($this->rooms, fn($r) => $r['capacite'] >= $effectif);
        if (empty($suitable)) $suitable = $this->rooms;
        if (empty($suitable)) return null;

        $suitable = array_values($suitable);
        return $suitable[array_rand($suitable)];
    }


    private function scoreTotal(array $solution): int
    {
        $score         = 0;
        $teacher_slots = [];
        $room_slots    = [];
        $class_slots   = [];

        foreach ($solution as $entry) {
            $t = $entry['teacher_id'];
            $r = $entry['room_id'];
            $c = $entry['class_id'];
            $j = $entry['jour'];
            $p = $entry['periode'];

            $pref  = $this->preferences[$t][$j][$p] ?? 0;
            $score += $pref;
            if ($pref > 0) $score += 3;

            if (isset($teacher_slots[$t][$j][$p])) $score -= 1000;
            if (isset($room_slots[$r][$j][$p]))    $score -= 1000;
            if (isset($class_slots[$c][$j][$p]))   $score -= 1000;

            $teacher_slots[$t][$j][$p] = true;
            $room_slots[$r][$j][$p]    = true;
            $class_slots[$c][$j][$p]   = true;
        }

        foreach ($class_slots as $c => $days) {
            foreach ($days as $j => $periods) {
                $used = array_keys($periods);
                sort($used);
                if (count($used) >= 2) {
                    $score -= ((max($used) - min($used) + 1) - count($used)) * 10;
                }
            }
        }

        foreach ($class_slots as $c => $days) {
            $counts = array_map('count', $days);
            if (!empty($counts)) {
                $avg      = array_sum($counts) / count($counts);
                $variance = 0;
                foreach ($counts as $cnt) {
                    $variance += ($cnt - $avg) ** 2;
                }
                $variance /= count($counts);
                if ($variance < 2) $score += 5;
            }
        }

        return $score;
    }

    private function countConflicts(array $solution): int
    {
        $conflicts     = 0;
        $teacher_slots = [];
        $room_slots    = [];
        $class_slots   = [];

        foreach ($solution as $entry) {
            $t = $entry['teacher_id'];
            $r = $entry['room_id'];
            $c = $entry['class_id'];
            $j = $entry['jour'];
            $p = $entry['periode'];

            if (isset($teacher_slots[$t][$j][$p])) $conflicts++;
            if (isset($room_slots[$r][$j][$p]))    $conflicts++;
            if (isset($class_slots[$c][$j][$p]))   $conflicts++;

            $teacher_slots[$t][$j][$p] = true;
            $room_slots[$r][$j][$p]    = true;
            $class_slots[$c][$j][$p]   = true;
        }
        return $conflicts;
    }
}

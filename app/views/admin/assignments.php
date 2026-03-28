<?php
$page_title = 'Assignations';
$active_nav = 'assignments';
require __DIR__ . '/../layout_top.php';
$days_fr = ['','Lundi','Mardi','Mercredi','Jeudi','Vendredi'];
?>
<?php if (!empty($message)): ?><div class="alert alert-success"> <?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="grid-2 mb-6">
    <!-- Teacher <-> Subject -->
    <div class="card">
        <div class="card-header"><h3> Enseignant ↔ Matière</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="assign_teacher">
                <div class="form-row">
                    <div class="form-group">
                        <label>Enseignant</label>
                        <select name="teacher_id" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Matière</label>
                        <select name="subject_id" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:0">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Assigner</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Enseignant</th><th>Matière</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($teacher_subjects as $ts): ?>
                <tr>
                    <td><?= htmlspecialchars($ts['teacher_nom']) ?></td>
                    <td><?= htmlspecialchars($ts['subject_nom']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="action" value="remove_teacher">
                            <input type="hidden" name="teacher_id" value="<?= $ts['teacher_id'] ?>">
                            <input type="hidden" name="subject_id" value="<?= $ts['subject_id'] ?>">
                            <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Class <-> Subject -->
    <div class="card">
        <div class="card-header"><h3> Classe ↔ Matière</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="assign_class">
                <div class="form-row">
                    <div class="form-group">
                        <label>Classe</label>
                        <select name="class_id" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Matière</label>
                        <select name="subject_id" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="max-width:80px">
                        <label>H/sem.</label>
                        <input type="number" name="heures" value="2" min="1" max="10">
                    </div>
                    <div class="form-group" style="flex:0">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Assigner</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Classe</th><th>Matière</th><th>H/sem.</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($class_subjects as $cs): ?>
                <tr>
                    <td><?= htmlspecialchars($cs['class_nom']) ?></td>
                    <td><?= htmlspecialchars($cs['subject_nom']) ?></td>
                    <td><?= $cs['heures_par_semaine'] ?>h</td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="action" value="remove_class">
                            <input type="hidden" name="class_id" value="<?= $cs['class_id'] ?>">
                            <input type="hidden" name="subject_id" value="<?= $cs['subject_id'] ?>">
                            <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout_bottom.php'; ?>

<?php
$page_title = 'Emplois du temps';
$active_nav = 'timetable';
require __DIR__ . '/../layout_top.php';

$days_fr    = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
$periods_fr = ['', '8h-9h', '9h-10h', '10h-11h', '11h-12h', '14h-15h', '15h-16h'];
?>

<!-- Controls -->
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="/admin/timetable">
            <div class="form-row">
                <div class="form-group">
                    <label>Génération</label>
                    <select name="gen" onchange="this.form.submit()">
                        <option value="">-- Choisir une génération --</option>
                        <?php foreach ($generations as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $gen_id == $g['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nom']) ?>
                            (Score: <?= $g['score_total'] ?>, Conflits: <?= $g['nb_conflits'] ?>)
                            <?= $g['is_active'] ? ' ★ Actif' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Classe</label>
                    <select name="class" onchange="this.form.submit()">
                        <option value="">-- Choisir une classe --</option>
                        <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $class_id == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($gen_id && $class_id): ?>
                <div class="form-group" style="flex:0">
                    <label>&nbsp;</label>
                    <button type="button" onclick="window.print()" class="btn btn-primary">🖨️ Imprimer</button>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($gen_id && $class_id): ?>

<!-- Timetable grid -->
<div class="card mb-6" id="printable">
    <div class="card-header">
        <h3> Emploi du temps — <?= htmlspecialchars($classes[array_search($class_id, array_column($classes, 'id'))]['nom'] ?? '') ?></h3>
        <span class="text-muted text-sm">
            Génération : <?= htmlspecialchars($generations[array_search($gen_id, array_column($generations, 'id'))]['nom'] ?? '') ?>
        </span>
    </div>
    <div class="card-body">
        <div class="timetable-grid">
            <!-- Header row -->
            <div class="tt-header" style="background:transparent;border:none"></div>
            <?php foreach ([1,2,3,4,5] as $d): ?>
            <div class="tt-header"><?= $days_fr[$d] ?></div>
            <?php endforeach; ?>

            <?php for ($p = 1; $p <= 6; $p++): ?>
            <!-- Period label -->
            <div class="tt-period-label">
                <span style="font-size:.65rem;color:var(--text-muted)"><?= $p ?></span>
                <span style="font-size:.68rem"><?= $periods_fr[$p] ?></span>
            </div>

            <?php foreach ([1,2,3,4,5] as $d): ?>
            <?php $cell = $timetable_data[$d][$p] ?? null; ?>
            <?php if ($cell): ?>
            <div class="tt-cell filled" style="background:<?= htmlspecialchars($cell['couleur']) ?>">
                <div class="subject-name"><?= htmlspecialchars($cell['subject_nom']) ?></div>
                <div class="teacher-name"> <?= htmlspecialchars($cell['teacher_nom']) ?></div>
                <div class="room-name"> <?= htmlspecialchars($cell['room_nom']) ?></div>
            </div>
            <?php else: ?>
            <div class="tt-cell empty"></div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>

<?php elseif (empty($generations)): ?>
<div class="alert alert-info"> Aucun emploi du temps généré. <a href="/admin/generate" style="color:var(--primary);font-weight:600">Générer maintenant →</a></div>
<?php else: ?>
<div class="alert alert-info"> Sélectionnez une génération et une classe pour afficher l'emploi du temps.</div>
<?php endif; ?>

<!-- Generations list -->
<?php if (!empty($generations)): ?>
<div class="card">
    <div class="card-header"><h3> Toutes les générations</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nom</th><th>Score</th><th>Conflits</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($generations as $g): ?>
            <tr>
                <td style="font-weight:600"><?= htmlspecialchars($g['nom']) ?></td>
                <td><?= number_format($g['score_total']) ?></td>
                <td>
                    <?php if ($g['nb_conflits'] == 0): ?>
                    <span class="badge badge-success">✓ Aucun</span>
                    <?php else: ?>
                    <span class="badge badge-danger"><?= $g['nb_conflits'] ?> conflit(s)</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted text-sm"><?= date('d/m/Y H:i', strtotime($g['created_at'])) ?></td>
                <td>
                    <?php if ($g['is_active']): ?>
                    <span class="badge badge-success">★ Actif</span>
                    <?php else: ?>
                    <span class="badge" style="background:#f1f5f9;color:#64748b">Archivé</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="flex gap-2">
                        <a href="/admin/timetable?gen=<?= $g['id'] ?>&class=<?= $class_id ?>" class="btn btn-sm btn-primary"> Voir</a>
                        <form method="POST" onsubmit="return confirm('Supprimer cette génération ?')">
                            <input type="hidden" name="action" value="delete_gen">
                            <input type="hidden" name="gen_id" value="<?= $g['id'] ?>">
                            <button class="btn btn-sm btn-danger">supp</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
@media print {
    .sidebar, .page-header, form, .card:not(#printable) { display: none !important; }
    .main-content { margin-left: 0; }
    .page-body { padding: 0; }
    #printable { box-shadow: none; border: none; }
    .tt-cell.filled { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<?php require __DIR__ . '/../layout_bottom.php'; ?>

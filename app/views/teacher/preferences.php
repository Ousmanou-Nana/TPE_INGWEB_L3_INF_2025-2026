<?php
$page_title = 'Mes préférences';
$active_nav = 'preferences';
require __DIR__ . '/../layout_top.php';

$days_fr    = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
$periods_fr = ['', '8h-9h', '9h-10h', '10h-11h', '11h-12h', '14h-15h', '15h-16h'];
?>

<?php if (!empty($message)): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="card mb-6">
    <div class="card-header">
        <h3> Définir mes disponibilités</h3>
        <span class="text-muted text-sm">−5 = refuse fortement &nbsp;|&nbsp; 0 = neutre &nbsp;|&nbsp; +5 = préfère fortement</span>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="pref-grid">
               
                <div class="pref-header" style="background:transparent;border:none"></div>
                <?php foreach ([1,2,3,4,5] as $d): ?>
                <div class="pref-header"><?= $days_fr[$d] ?></div>
                <?php endforeach; ?>

                <?php for ($p = 1; $p <= 6; $p++): ?>
                <div class="pref-period-label"><?= $periods_fr[$p] ?></div>

                <?php foreach ([1,2,3,4,5] as $d): ?>
                <?php $score = $prefs[$d][$p] ?? 0; ?>
                <div class="pref-cell" style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:10px">
                    <div class="score-display <?= $score > 0 ? 'score-pos' : ($score < 0 ? 'score-neg' : 'score-neu') ?>"
                         id="disp_<?= $d ?>_<?= $p ?>"><?= $score > 0 ? '+' . $score : $score ?></div>
                    <input type="range"
                           name="score_<?= $d ?>_<?= $p ?>"
                           id="range_<?= $d ?>_<?= $p ?>"
                           min="-5" max="5" step="1"
                           value="<?= $score ?>"
                           oninput="updateScore(<?= $d ?>, <?= $p ?>, this.value)">
                </div>
                <?php endforeach; ?>
                <?php endfor; ?>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px;align-items:center">
                <button type="submit" class="btn btn-accent">💾 Sauvegarder mes préférences</button>
                <button type="button" onclick="resetAll()" class="btn btn-primary">↺ Tout remettre à 0</button>
            </div>
        </form>
    </div>
</div>


<?php if (!empty($timetable_data)): ?>
<div class="card">
    <div class="card-header"><h3> Mon emploi du temps (génération active)</h3></div>
    <div class="card-body">
        <div class="timetable-grid">
            <div class="tt-header" style="background:transparent;border:none"></div>
            <?php foreach ([1,2,3,4,5] as $d): ?>
            <div class="tt-header"><?= $days_fr[$d] ?></div>
            <?php endforeach; ?>

            <?php for ($p = 1; $p <= 6; $p++): ?>
            <div class="tt-period-label">
                <span style="font-size:.65rem"><?= $p ?></span>
                <span style="font-size:.68rem"><?= $periods_fr[$p] ?></span>
            </div>
            <?php foreach ([1,2,3,4,5] as $d): ?>
            <?php $cell = $timetable_data[$d][$p] ?? null; ?>
            <?php if ($cell): ?>
            <div class="tt-cell filled" style="background:<?= htmlspecialchars($cell['couleur']) ?>">
                <div class="subject-name"><?= htmlspecialchars($cell['subject_nom']) ?></div>
                <div class="teacher-name"> <?= htmlspecialchars($cell['class_nom']) ?></div>
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
<?php else: ?>
<div class="alert alert-info"> Aucun emploi du temps actif. L'administrateur doit en générer un.</div>
<?php endif; ?>

<script>
function updateScore(d, p, val) {
    val = parseInt(val);
    const disp = document.getElementById('disp_' + d + '_' + p);
    disp.textContent = val > 0 ? '+' + val : val;
    disp.className = 'score-display ' + (val > 0 ? 'score-pos' : val < 0 ? 'score-neg' : 'score-neu');
    // Color the background
    const cell = disp.parentElement;
    const intensity = Math.abs(val) / 5;
    if (val > 0) {
        cell.style.background = `rgba(46,204,113,${intensity * 0.25})`;
    } else if (val < 0) {
        cell.style.background = `rgba(231,76,60,${intensity * 0.25})`;
    } else {
        cell.style.background = 'var(--surface)';
    }
}
function resetAll() {
    document.querySelectorAll('input[type="range"]').forEach(r => {
        r.value = 0;
        const parts = r.id.replace('range_','').split('_');
        updateScore(parts[0], parts[1], 0);
    });
}
// Initialize colors on load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="range"]').forEach(r => {
        const parts = r.id.replace('range_','').split('_');
        updateScore(parts[0], parts[1], r.value);
    });
});
</script>

<?php require __DIR__ . '/../layout_bottom.php'; ?>

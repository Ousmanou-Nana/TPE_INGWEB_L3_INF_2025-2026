<?php
$page_title = 'Emplois du temps';
$active_nav = 'timetable';
require __DIR__ . '/../layout_top.php';

$days_fr    = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
$periods_fr = ['', '8h-9h', '9h-10h', '10h-11h', '11h-12h', '14h-15h', '15h-16h'];

function hexToRgb(string $hex): array {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

function darkenRgb(array $rgb, float $factor): string {
    return implode(',', [
        (int)round($rgb[0] * (1 - $factor)),
        (int)round($rgb[1] * (1 - $factor)),
        (int)round($rgb[2] * (1 - $factor)),
    ]);
}

function lightenRgb(array $rgb, float $factor): string {
    return implode(',', [
        (int)round($rgb[0] + (255 - $rgb[0]) * $factor),
        (int)round($rgb[1] + (255 - $rgb[1]) * $factor),
        (int)round($rgb[2] + (255 - $rgb[2]) * $factor),
    ]);
}
?>


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
                    <button type="button" onclick="window.print()" class="btn btn-primary">Imprimer</button>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($gen_id && $class_id): ?>

<div class="card mb-6" style="padding:14px 20px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">

        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:.75rem;font-weight:600">
            <span style="color:var(--text-muted);margin-right:4px">Préférence :</span>
            <?php
            
            $demo = [
                [-5, 'lighten', 0.55, '−5'],
                [-3, 'lighten', 0.35, '−3'],
                [-1, 'lighten', 0.15, '−1'],
                [ 0, 'none',    0,    ' 0'],
                [ 1, 'darken',  0.15, '+1'],
                [ 3, 'darken',  0.35, '+3'],
                [ 5, 'darken',  0.55, '+5'],
            ];
            $base = [100, 130, 200];
            foreach ($demo as [$score, $dir, $f, $lbl]):
                if ($dir === 'darken')      $bg = 'rgb(' . darkenRgb($base, $f) . ')';
                elseif ($dir === 'lighten') $bg = 'rgb(' . lightenRgb($base, $f) . ')';
                else                        $bg = 'rgb(100,130,200)';
            ?>
            <div style="
                display:inline-flex;align-items:center;justify-content:center;
                width:38px;height:24px;border-radius:5px;
                background:<?= $bg ?>;color:#fff;
                font-size:.7rem;font-weight:700;text-shadow:0 1px 2px rgba(0,0,0,.4);
            "><?= $lbl ?></div>
            <?php endforeach; ?>
        </div>

      
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.82rem;font-weight:600;color:var(--text-muted);user-select:none">
            <div style="position:relative;width:40px;height:22px">
                <input type="checkbox" id="pref-toggle" checked style="opacity:0;width:0;height:0;position:absolute">
                <span id="toggle-track" style="position:absolute;inset:0;border-radius:11px;background:var(--primary);transition:background .2s"></span>
                <span id="toggle-thumb" style="position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .2s;transform:translateX(18px)"></span>
            </div>
            Afficher les préférences
        </label>
    </div>
</div>

<div class="card mb-6" id="printable">
    <div class="card-header">
        <h3>Emploi du temps — <?= htmlspecialchars($classes[array_search($class_id, array_column($classes, 'id'))]['nom'] ?? '') ?></h3>
        <span class="text-muted text-sm">
            Génération : <?= htmlspecialchars($generations[array_search($gen_id, array_column($generations, 'id'))]['nom'] ?? '') ?>
        </span>
    </div>
    <div class="card-body">
        <div class="timetable-grid">
            <div class="tt-header" style="background:transparent;border:none"></div>
            <?php foreach ([1,2,3,4,5] as $d): ?>
            <div class="tt-header"><?= $days_fr[$d] ?></div>
            <?php endforeach; ?>

            <?php for ($p = 1; $p <= 6; $p++): ?>
            <div class="tt-period-label">
                <span style="font-size:.65rem;color:var(--text-muted)"><?= $p ?></span>
                <span style="font-size:.68rem"><?= $periods_fr[$p] ?></span>
            </div>

            <?php foreach ([1,2,3,4,5] as $d): ?>
            <?php $cell = $timetable_data[$d][$p] ?? null; ?>
            <?php if ($cell):
                $pref_score = (int)($cell['pref_score'] ?? 0);
                $intensity  = abs($pref_score) / 5;  // 0.0 → 1.0
                $shift      = $intensity * 0.55;

                $base_color = $cell['couleur'] ?? '#6b7280';
                $rgb        = hexToRgb($base_color);

                // REVERSED scale:
                // +5 → darkest  (most satisfied = richest/deepest color)
                // -5 → lightest (least satisfied = washed out)
                if ($pref_score > 0) {
                    $pref_rgb_str = darkenRgb($rgb, $shift);
                    $pref_bg      = "rgb({$pref_rgb_str})";
                    $border_rgb   = darkenRgb($rgb, min($shift + 0.2, 1.0));
                    $pref_border  = "2px solid rgb({$border_rgb})";
                } elseif ($pref_score < 0) {
                    $pref_rgb_str = lightenRgb($rgb, $shift);
                    $pref_bg      = "rgb({$pref_rgb_str})";
                    $border_rgb   = lightenRgb($rgb, min($shift + 0.15, 1.0));
                    $pref_border  = "2px solid rgb({$border_rgb})";
                } else {
                    $pref_bg     = $base_color;
                    $pref_border = 'none';
                }

                $pref_label = $pref_score > 0 ? "+{$pref_score}" : (string)$pref_score;
            ?>
            <div class="tt-cell filled pref-cell-wrap"
                 style="position:relative;overflow:hidden;background:<?= htmlspecialchars($base_color) ?>;">

                <div class="pref-overlay" style="
                    position:absolute;inset:0;
                    background:<?= $pref_bg ?>;
                    <?= $pref_score !== 0 ? "border:{$pref_border};" : '' ?>
                    border-radius:inherit;
                    pointer-events:none;transition:opacity .25s;opacity:<?= $pref_score !== 0 ? '1' : '0' ?>;"></div>

                <?php if ($pref_score !== 0): ?>
                <div class="pref-badge" style="
                    position:absolute;top:4px;right:4px;z-index:2;
                    font-size:.6rem;font-weight:700;
                    padding:1px 5px;border-radius:4px;
                    background:rgba(0,0,0,0.4);color:#fff;
                    pointer-events:none;
                    transition:opacity .25s;
                "><?= $pref_label ?></div>
                <?php endif; ?>

                <div style="position:relative;z-index:1">
                    <div class="subject-name"><?= htmlspecialchars($cell['subject_nom']) ?></div>
                    <div class="teacher-name"><?= htmlspecialchars($cell['teacher_nom']) ?></div>
                    <div class="room-name"><?= htmlspecialchars($cell['room_nom']) ?></div>
                </div>
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
<div class="alert alert-info">Aucun emploi du temps généré. <a href="/admin/generate" style="color:var(--primary);font-weight:600">Générer maintenant</a></div>
<?php else: ?>
<div class="alert alert-info">Sélectionnez une génération et une classe pour afficher l'emploi du temps.</div>
<?php endif; ?>


<?php if (!empty($generations)): ?>
<div class="card">
    <div class="card-header"><h3>Toutes les générations</h3></div>
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
                    <span class="badge badge-success">Aucun</span>
                    <?php else: ?>
                    <span class="badge badge-danger"><?= $g['nb_conflits'] ?> conflit(s)</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted text-sm"><?= date('d/m/Y H:i', strtotime($g['created_at'])) ?></td>
                <td>
                    <?php if ($g['is_active']): ?>
                    <span class="badge badge-success">Actif</span>
                    <?php else: ?>
                    <span class="badge" style="background:#f1f5f9;color:#64748b">Archivé</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="flex gap-2">
                        <a href="/admin/timetable?gen=<?= $g['id'] ?>&class=<?= $class_id ?>" class="btn btn-sm btn-primary">Voir</a>
                        <form method="POST" onsubmit="return confirm('Supprimer cette génération ?')">
                            <input type="hidden" name="action" value="delete_gen">
                            <input type="hidden" name="gen_id" value="<?= $g['id'] ?>">
                            <button class="btn btn-sm btn-danger">Supprimer</button>
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
    .pref-overlay   { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<script>
const toggle   = document.getElementById('pref-toggle');
const track    = document.getElementById('toggle-track');
const thumb    = document.getElementById('toggle-thumb');
const overlays = document.querySelectorAll('.pref-overlay');
const badges   = document.querySelectorAll('.pref-badge');

function applyToggle(on) {
    overlays.forEach(el => { el.style.opacity = on ? '1' : '0'; });
    badges.forEach(el   => { el.style.opacity = on ? '1' : '0'; });
    track.style.background = on ? 'var(--primary)' : '#cbd5e1';
    thumb.style.transform  = on ? 'translateX(18px)' : 'translateX(0)';
}

if (toggle) {
    toggle.addEventListener('change', () => applyToggle(toggle.checked));
    applyToggle(true);
}
</script>

<?php require __DIR__ . '/../layout_bottom.php'; ?>
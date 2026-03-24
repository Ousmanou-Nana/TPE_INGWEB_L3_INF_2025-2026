<?php
$page_title = 'Classes';
$active_nav = 'classes';
require __DIR__ . '/../layout_top.php';
?>
<?php if (!empty($message)): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Ajouter une classe</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group mb-4"><label>Nom</label><input type="text" name="nom" placeholder="ex: 6ème A" required></div>
                <div class="form-group mb-4"><label>Effectif</label><input type="number" name="effectif" value="30" min="1" max="50"></div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3> Classes (<?= count($classes) ?>)</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Effectif</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($classes as $c): ?>
                <tr>
                    <td style="font-weight:600"><?= htmlspecialchars($c['nom']) ?></td>
                    <td><?= $c['effectif'] ?> élèves</td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="openCls(<?= $c['id'] ?>, '<?= addslashes($c['nom']) ?>', <?= $c['effectif'] ?>)" class="btn btn-sm btn-primary"></button>
                            <form method="POST" onsubmit="return confirm('Supprimer ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
</div>

<div id="clsModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:380px">
        <h3 style="margin-bottom:20px"> Modifier la classe</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="cls_id">
            <div class="form-group mb-4"><label>Nom</label><input type="text" name="nom" id="cls_nom" required></div>
            <div class="form-group mb-4"><label>Effectif</label><input type="number" name="effectif" id="cls_eff" min="1"></div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="button" onclick="document.getElementById('clsModal').style.display='none'" class="btn btn-danger">Annuler</button>
            </div>
        </form>
    </div>
</div>
<script>
function openCls(id, nom, eff) {
    document.getElementById('cls_id').value = id;
    document.getElementById('cls_nom').value = nom;
    document.getElementById('cls_eff').value = eff;
    document.getElementById('clsModal').style.display = 'flex';
}
</script>
<?php require __DIR__ . '/../layout_bottom.php'; ?>

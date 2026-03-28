<?php
$page_title = 'Matières';
$active_nav = 'subjects';
require __DIR__ . '/../layout_top.php';
?>

<?php if (!empty($message)): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Ajouter une matière</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group mb-4">
                    <label>Nom de la matière</label>
                    <input type="text" name="nom" placeholder="ex: Mathématiques" required>
                </div>
                <div class="form-row mb-4">
                    <div class="form-group">
                        <label>Couleur</label>
                        <input type="color" name="couleur" value="#4A90E2">
                    </div>
                    <div class="form-group">
                        <label>Heures/semaine</label>
                        <input type="number" name="heures" value="2" min="1" max="10">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3> Matières (<?= count($subjects) ?>)</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Couleur</th><th>Nom</th><th>H/sem.</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($subjects as $s): ?>
                <tr>
                    <td><span style="display:inline-block;width:20px;height:20px;border-radius:4px;background:<?= htmlspecialchars($s['couleur']) ?>"></span></td>
                    <td style="font-weight:600"><?= htmlspecialchars($s['nom']) ?></td>
                    <td><?= $s['heures_par_semaine'] ?>h</td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="openSubjEdit(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['nom'])) ?>', '<?= $s['couleur'] ?>', <?= $s['heures_par_semaine'] ?>)"
                                    class="btn btn-sm btn-primary"></button>
                            <form method="POST" onsubmit="return confirm('Supprimer ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
</div>


<div id="subjModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:400px">
        <h3 style="margin-bottom:20px"> Modifier la matière</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="subj_id">
            <div class="form-group mb-4"><label>Nom</label><input type="text" name="nom" id="subj_nom" required></div>
            <div class="form-row mb-4">
                <div class="form-group"><label>Couleur</label><input type="color" name="couleur" id="subj_couleur"></div>
                <div class="form-group"><label>H/sem.</label><input type="number" name="heures" id="subj_heures" min="1" max="10"></div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="button" onclick="document.getElementById('subjModal').style.display='none'" class="btn btn-danger">Annuler</button>
            </div>
        </form>
    </div>
</div>
<script>
function openSubjEdit(id, nom, couleur, heures) {
    document.getElementById('subj_id').value = id;
    document.getElementById('subj_nom').value = nom;
    document.getElementById('subj_couleur').value = couleur;
    document.getElementById('subj_heures').value = heures;
    document.getElementById('subjModal').style.display = 'flex';
}
</script>
<?php require __DIR__ . '/../layout_bottom.php'; ?>

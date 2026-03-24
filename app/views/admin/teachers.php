<?php
$page_title = 'Enseignants';
$active_nav = 'teachers';
require __DIR__ . '/../layout_top.php';
?>

<?php if (!empty($message)): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Add form -->
<div class="card mb-6">
    <div class="card-header"><h3>Ajouter un enseignant</h3></div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="nom" placeholder="Prénom Nom" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="prenom.nom@ecole.fr" required>
                </div>
                <div class="form-group" style="max-width:180px">
                    <label>Mot de passe initial</label>
                    <input type="text" name="password" value="password" required>
                </div>
                <div class="form-group" style="flex:0">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- List -->
<div class="card">
    <div class="card-header">
        <h3> Liste des enseignants (<?= count($teachers) ?>)</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($teachers)): ?>
                <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted)">Aucun enseignant</td></tr>
            <?php else: ?>
                <?php foreach ($teachers as $t): ?>
                <tr>
                    <td class="text-muted"><?= $t['id'] ?></td>
                    <td style="font-weight:600"><?= htmlspecialchars($t['nom']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($t['email']) ?></td>
                    <td>
                        <div class="flex gap-2">
                            <!-- Edit modal trigger -->
                            <button onclick="openEdit(<?= $t['id'] ?>, <?= $t['user_id'] ?>, '<?= htmlspecialchars(addslashes($t['nom'])) ?>', '<?= htmlspecialchars(addslashes($t['email'])) ?>')"
                                    class="btn btn-sm btn-primary"> Modifier</button>
                            <form method="POST" onsubmit="return confirm('Supprimer cet enseignant ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">supp Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1000;display:none;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:440px;box-shadow:0 24px 80px rgba(0,0,0,.2)">
        <h3 style="margin-bottom:20px"> Modifier l'enseignant</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-group mb-4">
                <label>Nom</label>
                <input type="text" name="nom" id="edit_nom" required>
            </div>
            <div class="form-group mb-4">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="button" onclick="closeEdit()" class="btn btn-danger">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, uid, nom, email) {
    document.getElementById('edit_user_id').value = uid;
    document.getElementById('edit_nom').value = nom;
    document.getElementById('edit_email').value = email;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../layout_bottom.php'; ?>

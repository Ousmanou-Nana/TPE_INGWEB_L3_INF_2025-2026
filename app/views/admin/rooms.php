<?php
$page_title = 'Salles';
$active_nav = 'rooms';
require __DIR__ . '/../layout_top.php';
?>
<?php if (!empty($message)): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Ajouter une salle</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group mb-4"><label>Nom</label><input type="text" name="nom" placeholder="ex: Salle 101" required></div>
                <div class="form-group mb-4"><label>Capacité</label><input type="number" name="capacite" value="30" min="1"></div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3> Salles (<?= count($rooms) ?>)</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Capacité</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rooms as $r): ?>
                <tr>
                    <td style="font-weight:600"><?= htmlspecialchars($r['nom']) ?></td>
                    <td><?= $r['capacite'] ?> places</td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="openRoom(<?= $r['id'] ?>, '<?= addslashes($r['nom']) ?>', <?= $r['capacite'] ?>)" class="btn btn-sm btn-primary"></button>
                            <form method="POST" onsubmit="return confirm('Supprimer ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
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

<div id="roomModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:100%;max-width:380px">
        <h3 style="margin-bottom:20px"> Modifier la salle</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="room_id">
            <div class="form-group mb-4"><label>Nom</label><input type="text" name="nom" id="room_nom" required></div>
            <div class="form-group mb-4"><label>Capacité</label><input type="number" name="capacite" id="room_cap" min="1"></div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="button" onclick="document.getElementById('roomModal').style.display='none'" class="btn btn-danger">Annuler</button>
            </div>
        </form>
    </div>
</div>
<script>
function openRoom(id, nom, cap) {
    document.getElementById('room_id').value = id;
    document.getElementById('room_nom').value = nom;
    document.getElementById('room_cap').value = cap;
    document.getElementById('roomModal').style.display = 'flex';
}
</script>
<?php require __DIR__ . '/../layout_bottom.php'; ?>

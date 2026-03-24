<?php
$page_title = 'Tableau de bord';
$active_nav = 'dashboard';
require __DIR__ . '/../layout_top.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        
        <div class="stat-value"><?= $stats['teachers'] ?></div>
        <div class="stat-label">Enseignants</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-value"><?= $stats['subjects'] ?></div>
        <div class="stat-label">Matières</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-value"><?= $stats['classes'] ?></div>
        <div class="stat-label">Classes</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-value"><?= $stats['rooms'] ?></div>
        <div class="stat-label">Salles</div>
    </div>
    <div class="stat-card">
        
        <div class="stat-value"><?= $stats['generations'] ?></div>
        <div class="stat-label">EDT générés</div>
    </div>
</div>

<div class="grid-2">
    
    <div class="card">
        <div class="card-header"><h3>Actions rapides</h3></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
            <a href="/admin/generate" class="btn btn-accent">Générer un emploi du temps</a>
            <a href="/admin/teachers" class="btn btn-primary">Ajouter un enseignant</a>
            <a href="/admin/assignments" class="btn btn-primary"> Gérer les assignations</a>
            <a href="/admin/timetable" class="btn btn-primary"> Voir les emplois du temps</a>
        </div>
    </div>

   
    <div class="card">
        <div class="card-header"><h3> Dernières générations</h3></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Score</th>
                        <th>Conflits</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="4" class="text-muted" style="text-align:center;padding:20px">Aucune génération</td></tr>
                <?php else: ?>
                    <?php foreach ($recent as $gen): ?>
                    <tr>
                        <td>
                            <a href="/admin/timetable?gen=<?= $gen['id'] ?>" style="color:var(--primary);font-weight:500">
                                <?= htmlspecialchars($gen['nom']) ?>
                            </a>
                            <?php if ($gen['is_active']): ?>
                            <span class="badge badge-success" style="margin-left:4px">Actif</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:600"><?= number_format($gen['score_total']) ?></td>
                        <td>
                            <?php if ($gen['nb_conflits'] == 0): ?>
                            <span class="badge badge-success">✓ Aucun</span>
                            <?php else: ?>
                            <span class="badge badge-danger"><?= $gen['nb_conflits'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted text-sm"><?= date('d/m/Y H:i', strtotime($gen['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout_bottom.php'; ?>

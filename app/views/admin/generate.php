<?php
$page_title = 'Générer un emploi du temps';
$active_nav = 'generate';
require __DIR__ . '/../layout_top.php';
?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Lancer la génération</h3></div>
        <div class="card-body">
            <p class="text-muted text-sm mb-4">
                L'algorithme de <strong>recuit simulé (Simulated Annealing)</strong> va générer un emploi du temps optimal
                en maximisant la satisfaction des enseignants et en minimisant les conflits.
            </p>
            <div class="form-group mb-4">
                <label>Nom de cette génération</label>
                <input type="text" id="gen_nom" value="EDT <?= date('d/m/Y H:i') ?>" placeholder="Nom de l'emploi du temps">
            </div>
            <button class="btn btn-accent" id="btn-generate" onclick="startGeneration()">
                 Générer maintenant
            </button>

            <div class="generate-progress mt-4" id="progress">
                <div class="spinner"></div>
                <p style="font-weight:600;color:var(--primary)">Génération en cours...</p>
                <p class="text-muted text-sm">Le recuit simulé optimise l'emploi du temps.<br>Cela peut prendre quelques secondes.</p>
            </div>

            <div class="result-box" id="result-box"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Comment ça fonctionne</h3></div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:16px">

                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:36px;height:36px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">1</div>
                    <div>
                        <div style="font-weight:600;margin-bottom:4px">Génération initiale aléatoire</div>
                        <div class="text-muted text-sm">Placement aléatoire de tous les cours en respectant les assignations matière-classe.</div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:36px;height:36px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">2</div>
                    <div>
                        <div style="font-weight:600;margin-bottom:4px">Calcul du score (fitness)</div>
                        <div class="text-muted text-sm">Score = préférences enseignants − pénalités conflits − trous + bonus répartition.</div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:36px;height:36px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">3</div>
                    <div>
                        <div style="font-weight:600;margin-bottom:4px">Recuit simulé × 5 redémarrages</div>
                        <div class="text-muted text-sm">
                            À haute température, l'algorithme accepte parfois des solutions moins bonnes pour
                            <strong>échapper aux optima locaux</strong>. La température refroidit progressivement
                            (×0.995 par étape, de 1000 jusqu'à 0.1), puis converge vers la meilleure solution trouvée.
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:36px;height:36px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">4</div>
                    <div>
                        <div style="font-weight:600;margin-bottom:4px">Comparaison avec l'EDT actif</div>
                        <div class="text-muted text-sm">
                            Le nouveau résultat est comparé à l'emploi du temps actif.
                            L'EDT avec le meilleur score (et le moins de conflits en cas d'égalité) est automatiquement activé.
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:36px;height:36px;background:var(--accent);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">✓</div>
                    <div>
                        <div style="font-weight:600;margin-bottom:4px">Sauvegarde du meilleur</div>
                        <div class="text-muted text-sm">L'EDT optimal est sauvegardé et activé automatiquement.</div>
                    </div>
                </div>

            </div>

            <div style="margin-top:20px;padding:14px;background:var(--bg);border-radius:8px;font-size:.8rem">
                <strong>Règles de scoring :</strong><br>
                +score préférence enseignant (−5 à +5)<br>
                +3 bonus si enseignant satisfait<br>
                −1000 par conflit (enseignant / salle / classe)<br>
                −10 par trou dans la journée<br>
                +5 si répartition équilibrée
            </div>

            <div style="margin-top:12px;padding:14px;background:var(--bg);border-radius:8px;font-size:.8rem">
                <strong>Paramètres du recuit simulé :</strong><br>
                Température initiale : <strong>1 000</strong><br>
                Taux de refroidissement : <strong>×0.995 / étape</strong><br>
                Température minimale : <strong>0.1</strong><br>
                Redémarrages indépendants : <strong>5</strong>
            </div>
        </div>
    </div>
</div>

<script>
function startGeneration() {
    const nom = document.getElementById('gen_nom').value.trim() || 'EDT sans nom';
    document.getElementById('btn-generate').disabled = true;
    document.getElementById('progress').style.display = 'block';
    document.getElementById('result-box').style.display = 'none';

    fetch('/admin/run-generation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'nom=' + encodeURIComponent(nom)
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('progress').style.display = 'none';
        document.getElementById('btn-generate').disabled = false;
        const box = document.getElementById('result-box');
        box.style.display = 'block';

        if (data.success) {
            box.style.background = '#ecfdf5';
            box.style.border = '1px solid #a7f3d0';
            box.innerHTML = `
                
                <div style="font-weight:700;color:#065f46;font-size:1rem;margin-bottom:8px">Génération réussie !</div>
                <div style="font-size:.85rem;color:#065f46">
                    Score total : <strong>${data.score}</strong><br>
                    Conflits : <strong>${data.conflicts}</strong><br>
                    Cours placés : <strong>${data.inserted} / ${data.total}</strong>
                </div>
                <a href="/admin/timetable?gen=${data.gen_id}" style="display:inline-block;margin-top:14px" class="btn btn-success btn-sm">
                    Voir l'emploi du temps →
                </a>
            `;
        } else {
            box.style.background = '#fef2f2';
            box.style.border = '1px solid #fecaca';
            box.innerHTML = `<div style="color:#991b1b"><strong> Erreur :</strong> ${data.error}</div>`;
        }
    })
    .catch(err => {
        document.getElementById('progress').style.display = 'none';
        document.getElementById('btn-generate').disabled = false;
        const box = document.getElementById('result-box');
        box.style.display = 'block';
        box.style.background = '#fef2f2';
        box.innerHTML = '<div style="color:#991b1b"> Erreur réseau : ' + err + '</div>';
    });
}
</script>

<?php require __DIR__ . '/../layout_bottom.php'; ?>
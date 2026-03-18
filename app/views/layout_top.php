<?php

$role   = $_SESSION['user_role'] ?? 'guest';
$nom    = $_SESSION['user_nom'] ?? 'Utilisateur';
$avatar = strtoupper(substr($nom, 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title ?? 'SchoolTime') ?> SchoolTime</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="app-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2> SchoolTime</h2>
            <p>Gestion des emplois du temps</p>
        </div>
        <nav class="sidebar-nav">
            <?php if ($role === 'admin'): ?>
            <div class="nav-label">Administration</div>
            <a href="/admin/dashboard"    class="nav-link <?= ($active_nav??'')==='dashboard'    ? 'active':'' ?>">Tableau de bord</a>
            <a href="/admin/generate"     class="nav-link <?= ($active_nav??'')==='generate'     ? 'active':'' ?>"> Générer EDT</a>
            <a href="/admin/timetable"    class="nav-link <?= ($active_nav??'')==='timetable'    ? 'active':'' ?>"> Voir les EDT</a>

            <div class="nav-label" style="margin-top:12px">Gestion</div>
            <a href="/admin/teachers"     class="nav-link <?= ($active_nav??'')==='teachers'     ? 'active':'' ?>"> Enseignants</a>
            <a href="/admin/subjects"     class="nav-link <?= ($active_nav??'')==='subjects'     ? 'active':'' ?>"> Matières</a>
            <a href="/admin/classes"      class="nav-link <?= ($active_nav??'')==='classes'      ? 'active':'' ?>"> Classes</a>
            <a href="/admin/rooms"        class="nav-link <?= ($active_nav??'')==='rooms'        ? 'active':'' ?>"> Salles</a>
            <a href="/admin/assignments"  class="nav-link <?= ($active_nav??'')==='assignments'  ? 'active':'' ?>"> Assignations</a>
            <?php else: ?>
            <div class="nav-label">Enseignant</div>
            <a href="/teacher/preferences" class="nav-link <?= ($active_nav??'')==='preferences' ? 'active':'' ?>"> Mes préférences</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="/logout" class="nav-link"> Déconnexion</a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <header class="page-header">
            <h1><?= htmlspecialchars($page_title ?? '') ?></h1>
            <div class="user-badge">
                <div class="avatar"><?= $avatar ?></div>
                <div>
                    <div style="font-weight:600;color:var(--text)"><?= htmlspecialchars($nom) ?></div>
                    <div style="font-size:.72rem"><?= $role === 'admin' ? 'Administrateur' : 'Enseignant' ?></div>
                </div>
            </div>
        </header>
        <div class="page-body">

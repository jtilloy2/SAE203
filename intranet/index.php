<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil Intranet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h1 class="text-center">Bienvenue, <?= htmlspecialchars($_SESSION['user']['nom']) ?> !</h1>
            <p class="text-center text-muted">Rôle : <?= htmlspecialchars($_SESSION['user']['role']) ?></p>
            <hr>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="clients.php" class="btn btn-primary btn-lg w-100 p-4">👥 Annuaire Clients</a>
                </div>
                <div class="col-md-6">
                    <a href="partenaires_admin.php" class="btn btn-info btn-lg w-100 p-4 text-white">🤝 Partenaires</a>
                </div>
                
                <div class="col-md-6">
                    <a href="annuaire.php" class="btn btn-success btn-lg w-100 p-4">🧑‍💼 Annuaire Salariés</a>
                </div>
                <div class="col-md-6">
                    <a href="fichiers.php" class="btn btn-warning btn-lg w-100 p-4 text-dark">📁 Fichiers Partagés</a>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="logout.php" class="btn btn-link text-danger">Se déconnecter</a>
            </div>
        </div>
    </div>
</body>
</html>

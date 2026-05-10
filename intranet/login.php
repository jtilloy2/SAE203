<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_saisi = trim($_POST['login'] ?? '');
    $mdp_saisi = trim($_POST['password'] ?? '');

    $chemin_json = __DIR__ . '/data/users.json';

    if (file_exists($chemin_json)) {
        $contenu = file_get_contents($chemin_json);
        $utilisateurs = json_decode($contenu, true);

        if ($utilisateurs) {
            $trouve = false;
            foreach ($utilisateurs as $user) {
                if ($user['login'] === $login_saisi) {
                    $trouve = true;
                    $hash_stocke = trim($user['password']);

                    // Vérification hashée OU mot de passe de secours
                    if (password_verify($mdp_saisi, $hash_stocke) || $mdp_saisi === "admin2026") {
                        $_SESSION['user'] = [
                            'nom' => $user['login'],
                            'role' => $user['role']
                        ];
                        header('Location: index.php');
                        exit;
                    } else {
                        $erreur = "Mot de passe incorrect.";
                    }
                }
            }
            if (!$trouve) $erreur = "Identifiant inconnu.";
        } else {
            $erreur = "Fichier JSON vide ou mal formé.";
        }
    } else {
        $erreur = "Fichier de données introuvable.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Intranet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 card shadow p-4">
                <h2 class="text-center mb-4">Connexion</h2>
                <?php if ($erreur): ?>
                    <div class="alert alert-danger text-center"><?= $erreur ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Identifiant</label>
                        <input type="text" name="login" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrer</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

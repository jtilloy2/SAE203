<?php
session_start();

// Si l'utilisateur est déjà connecté, on l'envoie direct sur l'accueil
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$erreur = null;

// On intercepte la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_saisi = $_POST['login'] ?? '';
    $mdp_saisi = $_POST['password'] ?? '';

    // Chemin vers les données de Julien T.[cite: 7]
    $chemin_json = __DIR__ . '/data/users.json';

    if (file_exists($chemin_json)) {
        $contenu = file_get_contents($chemin_json); //
        $utilisateurs = json_decode($contenu, true); //

        foreach ($utilisateurs as $user) {
            // password_verify est crucial ici pour les mots de passe hashés
            if ($user['login'] === $login_saisi && password_verify($mdp_saisi, $user['password'])) {
                // Succès ! On stocke les infos utiles en session
                $_SESSION['user'] = [
                    'nom' => $user['login'],
                    'role' => $user['role'] // ex: admin, salarie
                ];
                header('Location: index.php');
                exit;
            }
        }
        $erreur = "Identifiants invalides. Vérifie tes majuscules !";
    } else {
        $erreur = "Fichier de configuration introuvable. Contactez Julien T.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Entreprise JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Le fichier d'Antonin -->
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 card shadow p-4">
                <h2 class="text-center mb-4">Intranet JOSSEL</h2>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger text-center"><?= $erreur ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Identifiant</label>
                        <input type="text" name="login" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="../wordpress" class="text-muted small">Retour au site vitrine</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

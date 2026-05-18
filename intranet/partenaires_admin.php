<?php
session_start();

// Si l'utilisateur est déjà connecté, on le redirige
if (isset($_SESSION['user'])) {
    header('Location: partenaires.php');
    exit;
}

$erreur = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_saisi = $_POST['login'] ?? '';
    $password_saisi = $_POST['password'] ?? '';

    // Chemin vers ton fichier JSON
    $chemin_json = __DIR__ . '/data/user.json';

    if (file_exists($chemin_json)) {
        // On lit et on décode le JSON
        $utilisateurs = json_decode(file_get_contents($chemin_json), true);
        $authentifie = false;

        // On parcourt les utilisateurs
        foreach ($utilisateurs as $user) {
            // On vérifie le login ET le mot de passe haché
            if ($user['login'] === $login_saisi && password_verify($password_saisi, $user['password'])) {
                // Succès ! On crée la session
                $_SESSION['user'] = $user['login'];
                $_SESSION['role'] = $user['role']; // <-- Le rôle est pris du JSON !
                
                $authentifie = true;
                header('Location: partenaires.php');
                exit;
            }
        }

        if (!$authentifie) {
            $erreur = "Identifiant ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Erreur système : Fichier user.json introuvable.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Intranet Vélomat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F9F9F9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1A1A1A;
        }
        .login-card {
            background: #FFFFFF;
            border: 1px solid #E0E0E0;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        .brand-title {
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .brand-title span { color: #0056b3; }
        
        .form-control {
            border: 1px solid #E0E0E0;
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }
        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.25rem rgba(0, 86, 179, 0.1);
        }
        .btn-primary {
            background-color: #1A1A1A;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-title">
            <i class="bi bi-bicycle"></i> Intranet <span>Vélomat</span>
        </div>
        
        <p class="text-center text-muted mb-4 small">Veuillez vous identifier pour accéder à votre espace collaborateur.</p>

        <?php if ($erreur): ?>
            <div class="alert alert-danger py-2 small text-center rounded-3">
                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Identifiant</label>
                <input type="text" name="login" class="form-control" placeholder="ex: jtilloy" required autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-semibold">Mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                Se connecter <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>
    </div>

</body>
</html>

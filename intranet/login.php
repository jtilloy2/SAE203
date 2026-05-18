<?php
session_start();

// Si l'utilisateur est déjà connecté, on le redirige vers l'accueil
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
                // Correspondance exacte sur l'identifiant
                if ($user['login'] === $login_saisi) {
                    $trouve = true;
                    
                    // Récupération du hash
                    $hash_stocke = trim($user['password'] ?? '');
                    
                    // SÉCURITÉ : Vérification du mot de passe via le hash officiel
                    if (password_verify($mdp_saisi, $hash_stocke)) {
                        
                        // ON FIXE LA SESSION EN UTILISANT LES BONNES CLÉS (role et nom)
                        $_SESSION['user'] = [
                            'login'  => $user['login'],
                            'role'   => $user['role'] ?? 'salarie',
                            'nom'    => ucfirst($user['login']) // Met la 1ère lettre en majuscule pour l'affichage
                        ];
                        
                        header('Location: index.php');
                        exit;
                    } else {
                        $erreur = "Mot de passe incorrect.";
                    }
                }
            }
            if (!$trouve) {
                $erreur = "Identifiant inconnu.";
            }
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
    <title>Connexion - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CHARTE GRAPHIQUE SOBRE (Lot 2 - Antonin) */
        :root {
            --c-fond: #FFFFFF;
            --c-texte: #1A1A1A;
            --c-structurant: #E0E0E0;
            --c-action: #0056b3;
        }

        body {
            background-color: #f4f6f8; 
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        .login-card {
            background-color: var(--c-fond);
            border: 1px solid var(--c-structurant);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--c-texte);
            text-align: center;
            margin-bottom: 1.5rem;
            letter-spacing: -0.5px;
        }

        .btn-action {
            background-color: var(--c-action);
            color: var(--c-fond);
            border: none;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn-action:hover {
            background-color: #004494;
            color: var(--c-fond);
        }

        .form-control:focus {
            border-color: var(--c-action);
            box-shadow: 0 0 0 0.25rem rgba(0, 86, 179, 0.25);
        }
    </style>
</head>
<body class="d-flex align-items-center vh-100">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card p-4 p-md-5">
                    
                    <div class="brand-logo">
                        Intranet JOSSEL
                    </div>
                    
                    <?php if ($erreur): ?>
                        <div class="alert alert-danger text-center small p-2"><?= htmlspecialchars($erreur) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Identifiant</label>
                            <input type="text" name="login" class="form-control" placeholder="ex: directeur" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted fw-bold">Mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-action w-100 py-2">Se connecter</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

</body>
</html>

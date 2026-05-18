<?php
session_start();

// Protection : Accès réservé. Si pas de session, retour au login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Vérification du rôle : Seul l'Admin (ou Direction/Manager selon vos specs) doit être ici
$role = isset($_SESSION['user']['groupe']) ? $_SESSION['user']['groupe'] : 'Salarié';
$login = isset($_SESSION['user']['login']) ? $_SESSION['user']['login'] : 'Utilisateur';

// Sécurité supplémentaire : si un simple salarié tente de forcer l'URL
if (strtolower($role) !== 'admin' && strtolower($role) !== 'administrateur') {
    // Redirection vers la page publique ou affichage d'une erreur propre
    header('Location: partenaires.php');
    exit;
}

$chemin_csv = __DIR__ . '/data/partenaires.csv';
$partenaires = [];

// Lecture du fichier CSV (Zéro SQL respecté)
if (file_exists($chemin_csv)) {
    if (($handle = fopen($chemin_csv, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ","); // Saut de l'entête
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 4) {
                $partenaires[] = [
                    'nom'         => trim($data[0]),
                    'logo'        => trim($data[1]),
                    'description' => trim($data[2]),
                    'site'        => trim($data[3])
                ];
            }
        }
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Console Admin - Partenaires JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* INTÉGRATION DE LA CHARTE GRAPHIQUE SOBRE (Lot 2 - Antonin) */
        :root {
            --c-fond: #FFFFFF;            /* Blanc Pur */
            --c-texte: #1A1A1A;           /* Noir Intense */
            --c-structurant: #E0E0E0;     /* Gris Clair */
            --c-action: #0056b3;          /* Accent / Bleu Tech */
            --c-secondaire: #4A4A4A;      /* Gris Anthracite */
            --c-danger: #dc3545;          /* Rouge Alerte */
        }

        body {
            background-color: var(--c-fond);
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        /* Navbar épurée */
        .navbar-custom {
            background-color: var(--c-fond);
            border-bottom: 1px solid var(--c-structurant);
        }
        .navbar-custom .navbar-brand {
            color: var(--c-texte);
            font-weight: 700;
        }

        /* Titres & Textes */
        h2 { color: var(--c-texte); letter-spacing: -0.5px; }
        .text-muted { color: var(--c-secondaire) !important; }

        /* Cartes Style Haut de Gamme / Minimaliste */
        .partner-card {
            border: 1px solid var(--c-structurant);
            background-color: var(--c-fond);
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .partner-card:hover {
            border-color: var(--c-texte);
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
        }

        /* Zone Logo */
        .card-img-container {
            height: 140px;
            padding: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--c-structurant);
            background-color: #FAFAFA;
            border-radius: 6px 6px 0 0;
        }
        .card-img-container img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        /* Boutons d'Action Admin */
        .btn-action {
            background-color: var(--c-action);
            color: var(--c-fond);
            border: none;
            font-weight: 500;
        }
        .btn-action:hover {
            background-color: #004494;
            color: var(--c-fond);
        }
        
        .btn-structurant {
            border: 1px solid var(--c-structurant);
            color: var(--c-texte);
            background: transparent;
        }
        .btn-structurant:hover {
            background-color: var(--c-structurant);
        }

        .btn-danger-custom {
            border: 1px solid var(--c-structurant);
            color: var(--c-danger);
            background: transparent;
        }
        .btn-danger-custom:hover {
            background-color: var(--c-danger);
            color: var(--c-fond);
            border-color: var(--c-danger);
        }

        .badge-admin {
            background-color: var(--c-texte);
            color: var(--c-fond);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand">JOSSEL <span class="fw-light text-muted">| Console Admin</span></span>
            <div class="d-flex align-items-center">
                <span class="me-4 text-muted small">
                    <?= htmlspecialchars($login) ?> 
                    <span class="badge badge-admin ms-2"><?= htmlspecialchars($role) ?></span>
                </span>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold m-0">Gestion des Partenaires</h2>
                <p class="text-muted small m-0 mt-1">Espace de configuration des liaisons et annuaires partenaires (Format CSV)</p>
            </div>
            <button class="btn btn-action btn-sm px-3">+ Ajouter un partenaire</button>
        </div>

        <?php if (!empty($partenaires)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                <?php foreach ($partenaires as $p): ?>
                    <div class="col">
                        <div class="card h-100 partner-card">
                            <div class="card-img-container">
                                <img src="<?= htmlspecialchars($p['logo']) ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>">
                            </div>
                            
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($p['nom']) ?></h5>
                                <p class="card-text small text-muted flex-grow-1 mb-4">
                                    <?= htmlspecialchars($p['description']) ?>
                                </p>
                                
                                <div class="row g-2 pt-3 border-top mt-auto">
                                    <div class="col-6">
                                        <button class="btn btn-structurant btn-sm w-100">Modifier</button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-danger-custom btn-sm w-100">Supprimer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-center p-5">
                <p class="mb-0 text-muted">Aucun partenaire enregistré dans le fichier CSV.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

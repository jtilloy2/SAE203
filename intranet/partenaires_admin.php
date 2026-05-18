<?php
session_start();

// Protection : si pas de session, on renvoie au login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// RAPPEL POUR JULIEN A. (Lot 5) : 
// Assure-toi que login.php stocke bien le groupe dans la session comme ceci : $_SESSION['user']['groupe']
$role = isset($_SESSION['user']['groupe']) ? $_SESSION['user']['groupe'] : 'Salarié';
$login = isset($_SESSION['user']['login']) ? $_SESSION['user']['login'] : 'Utilisateur';

$chemin_csv = __DIR__ . '/data/partenaires.csv';
$partenaires = [];

// On lit le CSV proprement avec sécurisation des index
if (file_exists($chemin_csv)) {
    if (($handle = fopen($chemin_csv, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ","); // On saute l'entête
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Sécurité : on vérifie que la ligne a bien 4 colonnes pour éviter les erreurs "Undefined offset"
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
    <title>Partenaires - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* * INTÉGRATION DE LA CHARTE GRAPHIQUE (Lot 2 - Antonin) 
         * Utilisation stricte des variables CSS pour écraser le Bootstrap par défaut
         */
        :root {
            --c-fond: #FFFFFF;            /* Fond (Neutre) - Blanc Pur */
            --c-texte: #1A1A1A;           /* Texte (Principal) - Noir Intense */
            --c-structurant: #E0E0E0;     /* Élément Structurant - Gris Clair */
            --c-action: #0056b3;          /* Accent (Action) - Bleu "Tech" */
            --c-secondaire: #4A4A4A;      /* Texte Secondaire - Gris Anthracite */
        }

        body {
            background-color: var(--c-fond);
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        /* Navbar sobre */
        .navbar-custom {
            background-color: var(--c-fond);
            border-bottom: 1px solid var(--c-structurant);
        }
        .navbar-custom .navbar-brand {
            color: var(--c-texte);
            font-weight: 700;
        }

        /* Typographie */
        h1, h2, h3, h4, h5, h6 { color: var(--c-texte); }
        .text-muted { color: var(--c-secondaire) !important; }

        /* Cartes Partenaires */
        .partner-card {
            border: 1px solid var(--c-structurant);
            background-color: var(--c-fond);
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .partner-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.05);
        }

        /* Conteneur logo pour rendu épuré */
        .card-img-container {
            height: 160px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--c-structurant);
            background-color: #FAFAFA; /* Contraste très léger avec le blanc pur */
            border-radius: 8px 8px 0 0;
        }
        .card-img-container img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        /* Boutons personnalisés selon la charte */
        .btn-action {
            background-color: var(--c-action);
            color: var(--c-fond);
            border: none;
            font-weight: 500;
        }
        .btn-action:hover {
            background-color: #004494; /* Bleu Tech légèrement assombri */
            color: var(--c-fond);
        }
        .btn-structurant {
            border: 1px solid var(--c-structurant);
            color: var(--c-texte);
        }
        .btn-structurant:hover {
            background-color: var(--c-structurant);
        }

        /* Badges */
        .badge-role {
            background-color: var(--c-structurant);
            color: var(--c-secondaire);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand">Intranet JOSSEL</span>
            <div class="d-flex align-items-center">
                <span class="me-4 text-muted small">
                    <?= htmlspecialchars($login) ?> 
                    <span class="badge badge-role ms-2"><?= htmlspecialchars($role) ?></span>
                </span>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">Nos Partenaires</h2>
            <span class="badge bg-secondary rounded-pill fs-6 fw-normal"><?= count($partenaires) ?> au total</span>
        </div>

        <?php if (!empty($partenaires)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($partenaires as $p): ?>
                    <div class="col">
                        <div class="card h-100 partner-card">
                            <div class="card-img-container">
                                <img src="<?= htmlspecialchars($p['logo']) ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>">
                            </div>
                            
                            <div class="card-body d-flex flex-column p-4">
                                <h5 class="card-title fw-bold mb-3"><?= htmlspecialchars($p['nom']) ?></h5>
                                <p class="card-text small flex-grow-1">
                                    <?= htmlspecialchars($p['description']) ?>
                                </p>
                                <div class="mt-4">
                                    <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" class="btn btn-action w-100">
                                        Visiter le site
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-center p-5">
                <p class="mb-0 text-muted">Aucun partenaire trouvé dans l'annuaire CSV.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

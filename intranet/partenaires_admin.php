<?php
session_start();

// Protection : si pas de session, on dégage au login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$chemin_csv = __DIR__ . '/data/partenaires.csv';
$partenaires = [];

// On lit le CSV
if (file_exists($chemin_csv)) {
    if (($handle = fopen($chemin_csv, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ","); // On saute l'entête
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $partenaires[] = [
                'nom'         => $data[0],
                'logo'        => $data[1],
                'description' => $data[2],
                'site'        => $data[3]
            ];
        }
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partenaires - Intranet Vélomat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        /* =========================================
           APPLICATION STRICTE DE LA CHARTE GRAPHIQUE
           ========================================= */
        :root {
            --c-fond-neutre: #FFFFFF;      /* Fond de page */
            --c-texte-principal: #1A1A1A;  /* Titres, paragraphes importants */
            --c-structurant: #E0E0E0;      /* Séparateurs, bordures, zones de fond légères */
            --c-accent: #0056b3;           /* Boutons, liens, éléments interactifs */
            --c-texte-secondaire: #4A4A4A; /* Texte secondaire, sous-titres */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--c-fond-neutre);
            color: var(--c-texte-principal);
        }

        /* Navbar */
        .navbar-jossel {
            background-color: var(--c-fond-neutre);
            border-bottom: 1px solid var(--c-structurant);
        }
        .navbar-brand {
            color: var(--c-accent) !important;
            font-weight: 700;
        }
        
        /* Boutons de la navbar */
        .btn-nav {
            background-color: var(--c-fond-neutre);
            color: var(--c-texte-principal);
            border: 1px solid var(--c-structurant);
            transition: 0.2s;
        }
        .btn-nav:hover {
            background-color: var(--c-structurant);
            color: var(--c-texte-principal);
        }

        /* Bannière d'en-tête (Hero) - Utilisation du "Gris Clair" pour la zone de fond légère */
        .hero-header {
            background-color: var(--c-structurant);
            padding: 3rem 0;
            margin-bottom: 3rem;
            border-bottom: 1px solid var(--c-structurant);
        }
        .hero-header h1 {
            color: var(--c-texte-principal);
        }
        .hero-header p {
            color: var(--c-texte-secondaire);
        }

        /* Badge compteur */
        .badge-jossel {
            background-color: var(--c-accent);
            color: var(--c-fond-neutre);
            font-size: 0.9rem;
            padding: 0.6em 1.2em;
            border-radius: 50px;
        }

        /* Design des cartes partenaires */
        .partner-card {
            border: 1px solid var(--c-structurant);
            border-radius: 8px;
            background: var(--c-fond-neutre);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .partner-card:hover {
            border-color: var(--c-accent);
            box-shadow: 0 8px 24px rgba(0, 86, 179, 0.08); /* Ombre très légère basée sur l'accent */
            transform: translateY(-4px);
        }

        .card-img-wrapper {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--c-structurant);
            background-color: var(--c-fond-neutre);
        }

        .card-img-top {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .partner-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .card-title {
            color: var(--c-texte-principal);
        }
        .card-text {
            color: var(--c-texte-secondaire);
        }

        /* Bouton Action "Bleu Tech" */
        .btn-action {
            background-color: var(--c-accent);
            color: var(--c-fond-neutre);
            border: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background-color: #004494; /* Variante d'interaction du Bleu Tech */
            color: var(--c-fond-neutre);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-jossel sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-bicycle me-2"></i> Intranet Vélomat
            </a>
            <div class="d-flex align-items-center">
                <span style="color: var(--c-texte-secondaire);" class="me-3 small d-none d-sm-inline">
                    <i class="bi bi-person-circle"></i> Connecté
                </span>
                <a href="index.php" class="btn btn-sm btn-nav me-2"><i class="bi bi-house-door"></i> Accueil</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="hero-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold mb-2">Réseau de Partenaires</h1>
                    <p class="mb-0">Consultez l'annuaire des acteurs de confiance qui accompagnent le groupe.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge badge-jossel">
                        <i class="bi bi-building"></i> <?= count($partenaires) ?> partenaires
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <?php if (!empty($partenaires)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($partenaires as $p): ?>
                    <div class="col">
                        <div class="card h-100 partner-card">
                            
                            <div class="card-img-wrapper">
                                <img src="<?= htmlspecialchars($p['logo']) ?>" class="card-img-top" alt="Logo <?= htmlspecialchars($p['nom']) ?>" loading="lazy">
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($p['nom']) ?></h5>
                                <p class="card-text small flex-grow-1">
                                    <?= htmlspecialchars($p['description']) ?>
                                </p>
                            </div>
                            
                            <div class="card-footer bg-transparent border-0 pb-4 pt-0">
                                <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" class="btn btn-action btn-sm w-100 py-2">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Visiter le site
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="display-1 mb-3" style="color: var(--c-structurant);"><i class="bi bi-folder-x"></i></div>
                <h4 style="color: var(--c-texte-principal);">Aucun partenaire trouvé</h4>
                <p style="color: var(--c-texte-secondaire);" class="small">Vérifiez le contenu du fichier <code>data/partenaires.csv</code>.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

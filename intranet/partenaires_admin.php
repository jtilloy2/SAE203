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
    <title>Nos Partenaires - Intranet JOSSEL (Vélomat)</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* =========================================
           APPLICATION STRICTE DE LA CHARTE GRAPHIQUE
           ========================================= */
        :root {
            --c-fond-neutre: #FFFFFF;      /* Fond de page pur */
            --c-texte-principal: #1A1A1A;  /* Titres, paragraphes importants */
            --c-structurant: #E0E0E0;      /* Zones de fond, navbar, bordures */
            --c-accent: #0056b3;           /* Boutons, liens actifs */
            --c-texte-secondaire: #4A4A4A; /* Textes secondaires */
        }

        body {
            background-color: var(--c-fond-neutre);
            color: var(--c-texte-principal);
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- En-tête --- */
        .jossel-header {
            background-color: var(--c-fond-neutre);
            border-bottom: 1px solid var(--c-structurant);
        }

        /* --- Navigations --- */
        .navbar-main {
            background-color: var(--c-structurant); /* Zone de fond légère */
        }
        .navbar-sub {
            background-color: var(--c-fond-neutre);
            border-bottom: 2px solid var(--c-structurant);
        }
        .nav-link {
            color: var(--c-texte-secondaire) !important;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: var(--c-texte-principal) !important;
        }
        .nav-link.active {
            color: var(--c-accent) !important;
            font-weight: 700;
        }

        /* --- Section Hero --- */
        .hero-section {
            background-color: var(--c-structurant); /* Zone de fond légère */
            padding: 3rem 1rem;
            border-radius: 8px;
            margin-bottom: 3rem;
        }

        /* --- Boutons --- */
        .btn-accent {
            background-color: var(--c-accent);
            color: var(--c-fond-neutre);
            border: none;
            font-weight: 600;
        }
        .btn-accent:hover {
            background-color: #004494;
            color: var(--c-fond-neutre);
        }
        .btn-outline-struct {
            background-color: var(--c-fond-neutre);
            color: var(--c-texte-principal);
            border: 1px solid var(--c-texte-secondaire);
            font-weight: 600;
        }
        .btn-outline-struct:hover {
            background-color: var(--c-structurant);
            color: var(--c-texte-principal);
        }

        /* --- Cartes Partenaires --- */
        .partner-card {
            border: 1px solid var(--c-structurant);
            transition: transform 0.2s, box-shadow 0.2s;
            background-color: var(--c-fond-neutre);
        }
        .partner-card:hover {
            transform: translateY(-5px);
            border-color: var(--c-accent);
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.1);
        }
        .partner-img-container {
            height: 200px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--c-structurant);
        }
        .partner-img-container img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        /* --- Footer --- */
        .jossel-footer {
            background-color: var(--c-structurant);
            color: var(--c-texte-principal);
            border-top: 1px solid var(--c-structurant);
        }
    </style>
</head>
<body>

    <header class="jossel-header p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span style="display:inline-block; width:120px; height:60px; background:var(--c-structurant); text-align:center; line-height:60px; border-radius:4px; font-weight:bold; color:var(--c-texte-secondaire);">[LOGO]</span>
            </div>
            <div class="text-center flex-grow-1">
                <h1 class="mb-0 display-6 fw-bold" style="color: var(--c-accent);">INTRANET VÉLOMAT</h1>
            </div>
            <div style="width: 120px; text-align:right;">
                <span class="badge" style="background-color: var(--c-accent);"><?= $_SESSION['user'] ?? 'Connecté' ?></span>
            </div>
        </div>
    </header>

    <nav class="navbar navbar-expand-lg navbar-main sticky-top">
        <div class="container">
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav gap-2">
                    <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link active" href="partenaires.php">Annuaires</a></li>
                    <li class="nav-item"><a class="nav-link" href="fichiers.php">Fichiers Partagés</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <nav class="navbar navbar-expand-lg navbar-sub mt-2 mb-4">
        <div class="container">
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav2">
                <ul class="navbar-nav gap-4 text-center">
                    <li class="nav-item"><a class="nav-link" href="clients.php">Annuaire Clients</a></li>
                    <li class="nav-item"><a class="nav-link" href="employes.php">Annuaire Employés</a></li>
                    <li class="nav-item"><a class="nav-link active" href="partenaires.php">Nos Partenaires</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4 flex-grow-1">
        
        <div class="hero-section text-center">
            <h2 class="display-5 fw-bold" style="color: var(--c-texte-principal);">Gestion des Partenaires</h2>
            <p class="lead mt-2 mb-4" style="color: var(--c-texte-secondaire);">
                Consultez et gérez les entreprises de confiance qui soutiennent le développement de Vélomat. Les données d'ici alimentent le site Vitrine public.
            </p>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <button class="btn btn-accent btn-lg px-4">Ajouter un partenaire</button>
                <a href="data/partenaires.csv" class="btn btn-outline-struct btn-lg px-4">Télécharger le CSV</a>
            </div>
        </div>

        <?php if (!empty($partenaires)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                <?php foreach ($partenaires as $p): ?>
                    <div class="col">
                        <div class="card h-100 text-center partner-card rounded-3">
                            <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" class="text-decoration-none">
                                <div class="partner-img-container">
                                    <img src="<?= htmlspecialchars($p['logo']) ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title fw-bold" style="color: var(--c-texte-principal);"><?= htmlspecialchars($p['nom']) ?></h5>
                                    <p class="card-text small" style="color: var(--c-texte-secondaire);"><?= htmlspecialchars($p['description']) ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert text-center" style="background-color: var(--c-structurant); color: var(--c-texte-principal);">
                Aucun partenaire n'est actuellement enregistré dans l'annuaire.
            </div>
        <?php endif; ?>
    </main>

    <footer class="jossel-footer text-center p-4 mt-auto">
        <div class="container">
            <p class="mb-1" style="color: var(--c-texte-secondaire);">
                &copy; 2026 Groupe JOSSEL (Vélomat) - Intranet Collaborateur
            </p>
            <p class="mb-0 small">
                <a href="../wiki.html" class="text-decoration-none" style="color: var(--c-accent);">Wiki du projet</a> | 
                <a href="../infos.html" class="text-decoration-none" style="color: var(--c-accent);">Page Infos</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

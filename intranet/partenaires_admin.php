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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        /* =========================================
           CHARTE DE COULEURS STRICTE & DESIGN MODERNE
           ========================================= */
        :root {
            --c-pure-white: #FFFFFF;       /* Fond de page principal */
            --c-dark-charcoal: #1A1A1A;    /* Titres et boutons principaux */
            --c-slate-gray: #4A4A4A;       /* Descriptions et textes secondaires */
            --c-fine-border: #E0E0E0;      /* Lignes de structure et contours */
            --c-tech-blue: #0056b3;        /* Accentuation interactive au survol */
            --c-soft-gray: #F9F9F9;        /* Fond léger pour les logos */
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--c-pure-white);
            color: var(--c-dark-charcoal);
            letter-spacing: -0.02em;
            -webkit-font-smoothing: antialiased;
        }

        /* Barre de navigation épurée */
        .custom-navbar {
            background-color: var(--c-pure-white);
            border-bottom: 1px solid var(--c-fine-border);
            padding: 1.25rem 0;
        }
        .navbar-brand-custom {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--c-dark-charcoal);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-brand-custom span {
            color: var(--c-tech-blue);
        }

        /* Boutons de navigation discrets */
        .btn-minimal {
            background: transparent;
            border: 1px solid var(--c-fine-border);
            color: var(--c-slate-gray);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-minimal:hover {
            border-color: var(--c-dark-charcoal);
            color: var(--c-dark-charcoal);
        }
        .btn-logout {
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        .btn-logout:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        /* En-tête minimaliste (sans bloc gris lourd) */
        .header-minimal {
            padding: 5rem 0 3rem 0;
        }
        .header-title {
            font-size: 2.75rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--c-dark-charcoal);
            margin-bottom: 0.5rem;
        }
        .header-subtitle {
            font-size: 1.1rem;
            color: var(--c-slate-gray);
            max-width: 600px;
        }

        /* Badge compteur haut de gamme */
        .modern-badge {
            background-color: var(--c-soft-gray);
            border: 1px solid var(--c-fine-border);
            color: var(--c-dark-charcoal);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Grille de cartes haut de gamme */
        .modern-partner-card {
            background: var(--c-pure-white);
            border: 1px solid var(--c-fine-border);
            border-radius: 16px;
            padding: 1.75rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .modern-partner-card:hover {
            border-color: var(--c-dark-charcoal);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.03);
        }

        /* Conteneur de logo optimisé */
        .logo-wrapper {
            background-color: var(--c-soft-gray);
            border-radius: 12px;
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: background-color 0.3s ease;
        }
        
        /* Astuce Modernité : Filtre pour harmoniser les logos disparates */
        .logo-wrapper img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            filter: grayscale(100%);
            opacity: 0.75;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Au survol de la carte, le logo reprend vie */
        .modern-partner-card:hover .logo-wrapper img {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.03);
        }
        .modern-partner-card:hover .logo-wrapper {
            background-color: var(--c-pure-white);
        }

        .partner-heading {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--c-dark-charcoal);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .partner-text {
            font-size: 0.95rem;
            color: var(--c-slate-gray);
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 1.75rem;
        }

        /* Bouton d'action chic */
        .btn-premium {
            background-color: var(--c-dark-charcoal);
            color: var(--c-pure-white);
            border: 1px solid var(--c-dark-charcoal);
            padding: 0.65rem 1.2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-premium:hover {
            background-color: var(--c-tech-blue);
            border-color: var(--c-tech-blue);
            color: var(--c-pure-white);
        }
    </style>
</head>
<body>

    <nav class="custom-navbar sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand-custom" href="#">
                <i class="bi bi-bicycle"></i> Intranet <span>Vélomat</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php" class="btn-minimal"><i class="bi bi-house"></i> Accueil</a>
                <a href="logout.php" class="btn-minimal btn-logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
            </div>
        </div>
    </nav>

    <header class="header-minimal">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                <div>
                    <h1 class="header-title">Réseau partenaires</h1>
                    <p class="header-subtitle mb-0">L'écosystème et les acteurs de confiance qui collaborent au quotidien avec le groupe JOSSEL.</p>
                </div>
                <div>
                    <span class="modern-badge">
                        <i class="bi bi-shield-check"></i> <?= count($partenaires) ?> certifiés
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="container pb-5">
        <?php if (!empty($partenaires)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($partenaires as $p): ?>
                    <div class="col">
                        <div class="modern-partner-card">
                            
                            <div class="logo-wrapper">
                                <img src="<?= htmlspecialchars($p['logo']) ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>" loading="lazy">
                            </div>
                            
                            <h2 class="partner-heading"><?= htmlspecialchars($p['nom']) ?></h2>
                            <p class="partner-text">
                                <?= htmlspecialchars($p['description']) ?>
                            </p>
                            
                            <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" class="btn-premium">
                                Visiter le site <i class="bi bi-arrow-up-right-short"></i>
                            </a>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 my-5">
                <div class="modern-badge mb-3" style="padding: 1rem;"><i class="bi bi-folder-x fs-4"></i></div>
                <h3 class="fw-bold">Aucun partenaire enregistré</h3>
                <p class="text-muted small">Le fichier <code>data/partenaires.csv</code> est actuellement vide ou introuvable.</p>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

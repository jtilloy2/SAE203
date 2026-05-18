<?php
session_start();

// Protection : si pas de session, on dégage au login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ---------------------------------------------------------
// GESTION DU RÔLE DEPUIS LA SESSION (user.json / users.json)
// ---------------------------------------------------------
$role_utilisateur = $_SESSION['role'] ?? 'salarie'; 

// Sécurité supplémentaire : si le login est 'admin', on s'assure que le rôle est 'admin'
if (strtolower($_SESSION['user']) === 'admin') {
    $role_utilisateur = 'admin';
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
    <title>Nos Partenaires - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        /* =========================================
           CHARTE GRAPHIQUE MODERNE JOSSEL
           ========================================= */
        :root {
            --c-pure-white: #FFFFFF;
            --c-dark-charcoal: #1A1A1A;
            --c-slate-gray: #4A4A4A;
            --c-fine-border: #E0E0E0;
            --c-tech-blue: #0056b3;
            --c-soft-gray: #F9F9F9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--c-pure-white);
            color: var(--c-dark-charcoal);
            letter-spacing: -0.02em;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- Navigation --- */
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
        .navbar-brand-custom span { color: var(--c-tech-blue); }

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
            background: var(--c-soft-gray);
        }

        /* --- En-tête --- */
        .header-minimal { padding: 4rem 0 2rem 0; }
        .header-title { font-size: 2.75rem; font-weight: 800; letter-spacing: -0.04em; }
        .header-subtitle { font-size: 1.1rem; color: var(--c-slate-gray); max-width: 600px; }

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
        
        .role-badge {
            background-color: var(--c-tech-blue);
            color: var(--c-pure-white);
            border-color: var(--c-tech-blue);
        }

        /* --- Cartes --- */
        .modern-partner-card {
            background: var(--c-pure-white);
            border: 1px solid var(--c-fine-border);
            border-radius: 16px;
            padding: 1.75rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.4s ease;
        }
        .modern-partner-card:hover {
            border-color: var(--c-dark-charcoal);
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
        }

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
        .logo-wrapper img {
            max-height: 100%; max-width: 100%;
            object-fit: contain; filter: grayscale(100%); opacity: 0.75;
            transition: all 0.3s ease;
        }
        .modern-partner-card:hover .logo-wrapper img {
            filter: grayscale(0%); opacity: 1; transform: scale(1.05);
        }

        .partner-heading { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .partner-text { font-size: 0.95rem; color: var(--c-slate-gray); flex-grow: 1; }

        /* --- Boutons --- */
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
        .btn-premium:hover { background-color: var(--c-tech-blue); border-color: var(--c-tech-blue); color: white;}
    </style>
</head>
<body>

    <nav class="custom-navbar sticky-top shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand-custom" href="#">
                <i class="bi bi-bicycle"></i> Intranet <span>JOSSEL</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="modern-badge role-badge me-3 d-none d-md-flex text-uppercase" style="letter-spacing: 1px; font-size: 0.75rem;">
                    <i class="bi bi-person-badge"></i> Profil : <?= htmlspecialchars($role_utilisateur) ?>
                </span>
                <a href="index.php" class="btn-minimal"><i class="bi bi-house"></i> Accueil</a>
                <a href="logout.php" class="btn-minimal text-danger border-danger"><i class="bi bi-power"></i></a>
            </div>
        </div>
    </nav>

    <header class="header-minimal">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h1 class="header-title">Réseau Partenaires</h1>
                    <p class="header-subtitle mb-4">Gérez l'écosystème et les acteurs de confiance qui collaborent au quotidien avec le groupe Vélomat.</p>
                    
                    <div class="d-flex flex-wrap gap-3">
                        <?php if (in_array($role_utilisateur, ['admin', 'direction', 'manager'])): ?>
                            <a href="data/partenaires.csv" download class="btn-minimal" style="background: var(--c-soft-gray);">
                                <i class="bi bi-download"></i> Télécharger le CSV
                            </a>
                        <?php endif; ?>

                        <?php if (in_array($role_utilisateur, ['admin', 'direction'])): ?>
                            <button class="btn-premium">
                                <i class="bi bi-plus-lg"></i> Ajouter un partenaire
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4 text-lg-end">
                    <span class="modern-badge fs-6 px-4 py-2">
                        <i class="bi bi-building-check text-success"></i> <?= count($partenaires) ?> Partenaires
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="container pb-5 flex-grow-1">
        <?php if (!empty($partenaires)): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($partenaires as $p): ?>
                    <div class="col">
                        <div class="modern-partner-card">
                            
                            <div class="logo-wrapper">
                                <img src="<?= htmlspecialchars($p['logo']) ?>" alt="Logo <?= htmlspecialchars($p['nom']) ?>" loading="lazy">
                            </div>
                            
                            <h2 class="partner-heading"><?= htmlspecialchars($p['nom']) ?></h2>
                            <p class="partner-text"><?= htmlspecialchars($p['description']) ?></p>
                            
                            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" class="btn-premium flex-grow-1" style="font-size: 0.8rem;">
                                    Site web <i class="bi bi-arrow-up-right"></i>
                                </a>

                                <?php if (in_array($role_utilisateur, ['admin', 'direction'])): ?>
                                    <button class="btn-minimal px-2" title="Modifier"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn-minimal px-2 text-danger" title="Supprimer"><i class="bi bi-trash3"></i></button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 my-5 border rounded-4 bg-light">
                <i class="bi bi-folder-x display-4 text-muted mb-3 d-block"></i>
                <h3 class="fw-bold">Aucun partenaire enregistré</h3>
                <p class="text-muted">Le fichier CSV est actuellement vide.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="mt-auto py-4 border-top text-center text-muted small">
        <div class="container">
            &copy; 2026 Intranet JOSSEL - Accès sécurisé.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

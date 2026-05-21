<?php
session_start();

// Protection : si pas de session, on dégage directement au login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Récupération uniforme des informations de session du Lot 5
$role = $_SESSION['user']['role'] ?? 'salarie';
$login = $_SESSION['user']['login'] ?? 'Utilisateur';

// Normalisation du rôle pour l'affichage et les vérifications
$role_affichage = ucfirst(strtolower($role));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CHARTE GRAPHIQUE STRICTE (Lot 2 - Antonin Chenais) */
        :root {
            --c-fond: #FFFFFF;            /* Blanc Pur */
            --c-texte: #1A1A1A;           /* Noir Intense */
            --c-structurant: #E0E0E0;     /* Gris Clair */
            --c-action: #0056b3;          /* Bleu Tech */
            --c-secondaire: #4A4A4A;      /* Gris Anthracite */
        }

        body {
            background-color: var(--c-fond);
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        /* Navbar identique à la page partenaires */
        .navbar-custom {
            background-color: var(--c-fond);
            border-bottom: 1px solid var(--c-structurant);
        }
        .navbar-custom .navbar-brand {
            color: var(--c-texte);
            font-weight: 700;
        }

        /* Cartes des Modules - Style Minimaliste Haut de Gamme */
        .module-card {
            border: 1px solid var(--c-structurant);
            background-color: var(--c-fond);
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none !important;
            color: var(--c-texte) !important;
        }
        .module-card:hover {
            border-color: var(--c-texte);
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
        }

        /* Zone Icône reprenant le design des logos partenaires */
        .card-icon-container {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--c-structurant);
            background-color: #FAFAFA;
            border-radius: 6px 6px 0 0;
            font-size: 2.5rem;
        }

        /* Boutons d'Action */
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

        .badge-role {
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
            <span class="navbar-brand">Intranet JOSSEL</span>
            <div class="d-flex align-items-center">
                <span class="me-4 text-muted small">
                    <?= htmlspecialchars($login) ?> 
                    <span class="badge badge-role ms-2"><?= htmlspecialchars($role_affichage) ?></span>
                </span>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="mb-5 border-bottom pb-4">
            <h2 class="fw-bold m-0 tracking-tight">Bienvenue sur votre portail</h2>
            <p class="text-muted small m-0 mt-1">Sélectionnez une application ou un annuaire pour commencer votre session de travail.</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            
            <div class="col">
                <a href="clients.php" class="card h-100 module-card">
                    <div class="card-icon-container">
                        📁
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-2">Annuaire Clients</h5>
                        <p class="text-muted small mb-0">Base de données et fiches clients de l'entreprise.</p>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="partenaires.php" class="card h-100 module-card">
                    <div class="card-icon-container">
                        🤝
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-2">Partenaires</h5>
                        <p class="text-muted small mb-0">Gestion et consultation des liaisons fournisseurs externes.</p>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="annuaire.php" class="card h-100 module-card">
                    <div class="card-icon-container">
                        👥
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-2">Annuaire Salariés</h5>
                        <p class="text-muted small mb-0">Trombinoscope et profils des collaborateurs JOSSEL.</p>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="fichiers.php" class="card h-100 module-card">
                    <div class="card-icon-container">
                        ☁️
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-2">Fichiers Partagés</h5>
                        <p class="text-muted small mb-0">Espace d'échange et de stockage de documents plats.</p>
                    </div>
                </a>
            </div>

        </div>

    </div>

</body>
</html>

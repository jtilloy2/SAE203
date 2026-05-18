<?php
session_start();

// 1. SÉCURITÉ : Vérification de la session
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 2. GESTION DES PERMISSIONS
$role = strtolower($_SESSION['user']['role'] ?? $_SESSION['user']['groupe'] ?? 'salarie');
$nom_user_connecte = $_SESSION['user']['nom'] ?? $_SESSION['user']['login'] ?? 'Utilisateur';

// On donne le droit de supprimer aux rôles à responsabilité
$peut_supprimer = in_array($role, ['admin', 'administrateur', 'direction', 'manager']);

// 3. LOGIQUE MÉTIER (Fichiers systèmes)
$dossier_uploads = __DIR__ . '/uploads/';
$message_succes = '';
$message_erreur = '';

// Sécurité : on crée le dossier s'il n'existe pas
if (!is_dir($dossier_uploads)) {
    mkdir($dossier_uploads, 0775, true);
}

// TRAITEMENT DE L'UPLOAD (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_upload'])) {
    $fichier = $_FILES['fichier_upload'];
    $nom_fichier = basename($fichier['name']);
    
    // Extraction et vérification de l'extension (Sécurité max !)
    $extension = strtolower(pathinfo($nom_fichier, PATHINFO_EXTENSION));
    
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $message_erreur = "Une erreur est survenue lors du téléchargement.";
    } elseif (!in_array($extension, ['txt', 'csv'])) {
        $message_erreur = "Sécurité : Seuls les fichiers .txt et .csv sont autorisés !";
    } else {
        $chemin_destination = $dossier_uploads . $nom_fichier;
        // On déplace le fichier du dossier temporaire vers notre dossier uploads
        if (move_uploaded_file($fichier['tmp_name'], $chemin_destination)) {
            $message_succes = "Le fichier '$nom_fichier' a été partagé avec succès.";
        } else {
            $message_erreur = "Erreur de permission système lors de l'écriture du fichier.";
        }
    }
}

// TRAITEMENT DE LA SUPPRESSION (GET)
if (isset($_GET['supprimer']) && $peut_supprimer) {
    // Utilisation de basename() pour éviter les failles de type "Directory Traversal" (ex: ../../)
    $fichier_a_supprimer = basename($_GET['supprimer']);
    $chemin_fichier = $dossier_uploads . $fichier_a_supprimer;
    
    if (file_exists($chemin_fichier) && is_file($chemin_fichier)) {
        if (unlink($chemin_fichier)) {
            $message_succes = "Le fichier '$fichier_a_supprimer' a été supprimé.";
        } else {
            $message_erreur = "Impossible de supprimer le fichier.";
        }
    }
}

// LECTURE DU DOSSIER POUR AFFICHAGE
// scandir() liste tout, on utilise array_diff pour retirer '.' et '..'
$fichiers_partages = array_diff(scandir($dossier_uploads), ['.', '..']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fichiers Partagés - Intranet JOSSEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CHARTE GRAPHIQUE UNIFIÉE JOSSEL */
        :root {
            --c-fond: #FFFFFF;            
            --c-texte: #1A1A1A;           
            --c-structurant: #E0E0E0;     
            --c-action: #0056b3;          
            --c-secondaire: #4A4A4A;      
        }

        body {
            background-color: var(--c-fond);
            color: var(--c-texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        .navbar-custom { background-color: var(--c-fond); border-bottom: 1px solid var(--c-structurant); }
        .navbar-custom .navbar-brand { color: var(--c-texte); font-weight: 700; }
        .text-muted { color: var(--c-secondaire) !important; }

        .btn-action { background-color: var(--c-action); color: var(--c-fond); border: none; font-weight: 500; }
        .btn-action:hover { background-color: #004494; color: var(--c-fond); }
        
        .btn-structurant { border: 1px solid var(--c-structurant); color: var(--c-texte); background: transparent; }
        .btn-structurant:hover { background-color: var(--c-structurant); }

        .badge-role { background-color: var(--c-texte); color: var(--c-fond); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }

        .card-custom { border: 1px solid var(--c-structurant); border-radius: 6px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .table thead th { background-color: #FAFAFA; border-bottom: 1px solid var(--c-structurant); color: var(--c-secondaire); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 1rem; border-top: none; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid var(--c-structurant); }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #FAFAFA; }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-custom mb-5 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand">JOSSEL <span class="fw-light text-muted">| Espace Partagé</span></span>
            <div class="d-flex align-items-center">
                <span class="me-4 text-muted small">
                    <?= htmlspecialchars($nom_user_connecte) ?> 
                    <span class="badge badge-role ms-2"><?= htmlspecialchars($role) ?></span>
                </span>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="annuaire.php" class="btn btn-structurant btn-sm me-2">Annuaire</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <?php if ($message_succes): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message_succes) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($message_erreur): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message_erreur) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold m-0">Documents & Fichiers</h2>
                <p class="text-muted small m-0 mt-1">Plateforme d'échange (Formats autorisés : .TXT, .CSV)</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3">Partager un fichier</h5>
                    <form action="fichiers.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Sélectionner un fichier</label>
                            <input class="form-control" type="file" name="fichier_upload" accept=".txt,.csv" required>
                        </div>
                        <button type="submit" class="btn btn-action w-100">Uploader le document</button>
                    </form>
                    <div class="mt-3 text-muted" style="font-size: 0.8rem;">
                        ⚠️ Rappel de sécurité : L'envoi de scripts PHP ou d'exécutables est formellement interdit et bloqué par le système.
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom p-0 overflow-hidden">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Nom du fichier</th>
                                <th>Format</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($fichiers_partages)): ?>
                                <?php foreach ($fichiers_partages as $fichier): 
                                    // On vérifie que c'est bien un fichier et pas un dossier caché
                                    if (is_file($dossier_uploads . $fichier)):
                                        $ext = strtoupper(pathinfo($fichier, PATHINFO_EXTENSION));
                                        // Couleur du badge selon le format
                                        $badge_class = ($ext === 'CSV') ? 'bg-success' : 'bg-secondary';
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold" style="color: var(--c-texte);">
                                        📄 <?= htmlspecialchars($fichier) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge_class ?>"><?= $ext ?></span>
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <a href="uploads/<?= rawurlencode($fichier) ?>" download class="btn btn-structurant btn-sm me-2">
                                            Télécharger
                                        </a>
                                        
                                        <?php if ($peut_supprimer): ?>
                                            <a href="fichiers.php?supprimer=<?= urlencode($fichier) ?>" 
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier pour tout le monde ?');">
                                                Supprimer
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center p-5 text-muted">
                                        Aucun fichier n'est actuellement partagé sur le réseau.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

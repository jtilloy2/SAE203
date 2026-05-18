<?php
session_start();

// 1. PROTECTION DES ACCÈS
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['user']['role'] ?? 'salarie';
$login = $_SESSION['user']['login'] ?? 'Utilisateur';

$role_verif = strtolower($role);
if ($role_verif !== 'admin' && $role_verif !== 'administrateur' && $role_verif !== 'direction') {
    header('Location: partenaires.php');
    exit;
}

$chemin_csv = __DIR__ . '/data/partenaires.csv';
$message_success = null;
$message_error = null;

// 2. TRAITEMENT POST : AJOUTER UN PARTENAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = trim($_POST['nom'] ?? '');
    $logo = trim($_POST['logo'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $site = trim($_POST['site'] ?? '');

    if (!empty($nom) && !empty($logo) && !empty($description) && !empty($site)) {
        if (($handle = fopen($chemin_csv, 'a')) !== FALSE) {
            fputcsv($handle, [$nom, $logo, $description, $site]);
            fclose($handle);
            $message_success = "Partenaire ajouté avec succès !";
        } else {
            $message_error = "Erreur d'écriture dans le fichier CSV.";
        }
    } else {
        $message_error = "Tous les champs sont obligatoires pour l'ajout.";
    }
}

// 3. TRAITEMENT POST : ENREGISTRER LA MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_enregistrer') {
    $id_a_modifier = (int)($_POST['id'] ?? -1);
    $nom = trim($_POST['nom'] ?? '');
    $logo = trim($_POST['logo'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $site = trim($_POST['site'] ?? '');

    if ($id_a_modifier >= 0 && !empty($nom) && !empty($logo) && !empty($description) && !empty($site)) {
        $lignes_mises_a_jour = [];
        $entete = null;

        if (file_exists($chemin_csv) && ($handle = fopen($chemin_csv, "r")) !== FALSE) {
            $entete = fgetcsv($handle, 1000, ",");
            $index = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($index === $id_a_modifier) {
                    // On remplace par les nouvelles données saisies
                    $lignes_mises_a_jour[] = [$nom, $logo, $description, $site];
                } else {
                    $lignes_mises_a_jour[] = $data;
                }
                $index++;
            }
            fclose($handle);
        }

        // Réécriture complète du CSV mis à jour
        if (($handle = fopen($chemin_csv, "w")) !== FALSE) {
            fputcsv($handle, $entete);
            foreach ($lignes_mises_a_jour as $ligne) {
                fputcsv($handle, $ligne);
            }
            fclose($handle);
            $message_success = "Modification enregistrée avec succès !";
        }
    } else {
        $message_error = "Erreur lors de la modification. Vérifiez vos champs.";
    }
}

// 4. TRAITEMENT GET : SUPPRIMER UN PARTENAIRE
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_a_supprimer = (int)$_GET['id'];
    $lignes_restantes = [];
    $entete = null;

    if (file_exists($chemin_csv) && ($handle = fopen($chemin_csv, "r")) !== FALSE) {
        $entete = fgetcsv($handle, 1000, ",");
        $index = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($index !== $id_a_supprimer) {
                $lignes_restantes[] = $data;
            }
            $index++;
        }
        fclose($handle);
    }

    if (($handle = fopen($chemin_csv, "w")) !== FALSE) {
        fputcsv($handle, $entete);
        foreach ($lignes_restantes as $ligne) {
            fputcsv($handle, $ligne);
        }
        fclose($handle);
        $message_success = "Le partenaire a été supprimé de l'annuaire.";
    }
}

// 5. LECTURE DE L'ANNUAIRE POUR L'AFFICHAGE
$partenaires = [];
if (file_exists($chemin_csv)) {
    if (($handle = fopen($chemin_csv, "r")) !== FALSE) {
        fgetcsv($handle, 1000, ","); // Saut de l'entête
        $index = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 4) {
                $partenaires[] = [
                    'id'          => $index,
                    'nom'         => trim($data[0]),
                    'logo'        => trim($data[1]),
                    'description' => trim($data[2]),
                    'site'        => trim($data[3])
                ];
            }
            $index++;
        }
        fclose($handle);
    }
}

// 6. PRÉPARATION DES DONNÉES SI ON DEMANDE UNE MODIFICATION
$partenaire_a_modifier = null;
if (isset($_GET['action']) && $_GET['action'] === 'modifier' && isset($_GET['id'])) {
    $id_cible = (int)$_GET['id'];
    foreach ($partenaires as $p) {
        if ($p['id'] === $id_cible) {
            $partenaire_a_modifier = $p;
            break;
        }
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
        :root {
            --c-fond: #FFFFFF;
            --c-texte: #1A1A1A;
            --c-structurant: #E0E0E0;
            --c-action: #0056b3;
            --c-secondaire: #4A4A4A;
            --c-danger: #dc3545;
        }
        body { background-color: var(--c-fond); color: var(--c-texte); font-family: system-ui, -apple-system, sans-serif; }
        .navbar-custom { background-color: var(--c-fond); border-bottom: 1px solid var(--c-structurant); }
        .navbar-custom .navbar-brand { color: var(--c-texte); font-weight: 700; }
        .partner-card { border: 1px solid var(--c-structurant); background-color: var(--c-fond); border-radius: 6px; transition: all 0.2s ease; }
        .partner-card:hover { border-color: var(--c-texte); box-shadow: 0 10px 20px rgba(0,0,0,0.03); }
        .card-img-container { height: 140px; padding: 1.2rem; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--c-structurant); background-color: #FAFAFA; border-radius: 6px 6px 0 0; }
        .card-img-container img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .btn-action { background-color: var(--c-action); color: var(--c-fond); border: none; font-weight: 500; }
        .btn-action:hover { background-color: #004494; color: var(--c-fond); }
        .btn-structurant { border: 1px solid var(--c-structurant); color: var(--c-texte); background: transparent; text-decoration: none; display: inline-block; text-align: center; }
        .btn-structurant:hover { background-color: var(--c-structurant); color: var(--c-texte); }
        .btn-danger-custom { border: 1px solid var(--c-structurant); color: var(--c-danger); background: transparent; text-decoration: none; display: inline-block; text-align: center; }
        .btn-danger-custom:hover { background-color: var(--c-danger); color: var(--c-fond); border-color: var(--c-danger); }
        .badge-admin { background-color: var(--c-texte); color: var(--c-fond); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
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
                <a href="partenaires.php" class="btn btn-structurant btn-sm me-2">Voir le site</a>
                <a href="index.php" class="btn btn-structurant btn-sm me-2">Accueil</a>
                <a href="logout.php" class="btn btn-action btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <?php if ($message_success): ?>
            <div class="alert alert-success small py-2 mb-4"><?= $message_success ?></div>
        <?php endif; ?>
        <?php if ($message_error): ?>
            <div class="alert alert-danger small py-2 mb-4"><?= $message_error ?></div>
        <?php endif; ?>

        <?php if ($partenaire_a_modifier): ?>
            <div class="card border border-dark mb-5 p-4 bg-light shadow-sm">
                <h4 class="fw-bold mb-3">✏️ Modifier le partenaire : <?= htmlspecialchars($partenaire_a_modifier['nom']) ?></h4>
                <form method="POST" action="partenaires_admin.php">
                    <input type="hidden" name="action" value="modifier_enregistrer">
                    <input type="hidden" name="id" value="<?= $partenaire_a_modifier['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Nom de l'entreprise</label>
                            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($partenaire_a_modifier['nom']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">URL du Logo</label>
                            <input type="url" name="logo" class="form-control" value="<?= htmlspecialchars($partenaire_a_modifier['logo']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Site Internet</label>
                            <input type="url" name="site" class="form-control" value="<?= htmlspecialchars($partenaire_a_modifier['site']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Description</label>
                            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($partenaire_a_modifier['description']) ?>" required>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <a href="partenaires_admin.php" class="btn btn-secondary btn-sm me-2">Annuler</a>
                        <button type="submit" class="btn btn-action btn-sm px-4">Sauvegarder les modifications</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
            <div>
                <h2 class="fw-bold m-0">Gestion des Partenaires</h2>
                <p class="text-muted small m-0 mt-1">Espace de configuration (Format CSV)</p>
            </div>
            <button class="btn btn-action btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalAjout">+ Ajouter un partenaire</button>
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
                                        <a href="partenaires_admin.php?action=modifier&id=<?= $p['id'] ?>" class="btn btn-structurant btn-sm w-100">Modifier</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="partenaires_admin.php?action=supprimer&id=<?= $p['id'] ?>" class="btn btn-danger-custom btn-sm w-100" onclick="return confirm('Supprimer ce partenaire ?')">Supprimer</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-center p-5">
                <p class="mb-0 text-muted">Aucun partenaire enregistré.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="modalAjout" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Nouveau Partenaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="partenaires_admin.php">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Nom de l'entreprise</label>
                            <input type="text" name="nom" class="form-control" required placeholder="ex: TelecomPro">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Lien URL du logo</label>
                            <input type="url" name="logo" class="form-control" required placeholder="https://site.com/logo.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Lien vers le site web</label>
                            <input type="url" name="site" class="form-control" required placeholder="https://site.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Description de l'activité</label>
                            <textarea name="description" class="form-control" rows="3" required placeholder="Description courte..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-action btn-sm px-4">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

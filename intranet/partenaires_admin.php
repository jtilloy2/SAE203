<?php

session_start();



// Protection : si pas de session, on dégage au login

if (!isset($_SESSION['user'])) {

    header('Location: login.php');

    exit;

}



$chemin_csv = __DIR__ . '/data/partenaires.csv';

$partenaires = [];



// On lit le CSV (comme tout à l'heure)

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

    <title>Partenaires - Intranet JOSSEL</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        /* Un peu de style basique pour que les logos soient bien cadrés */

        .card-img-top {

            height: 150px;

            object-fit: contain; /* Évite de déformer les logos */

            padding: 20px;

            background-color: #fff;

        }

        .partner-card {

            transition: transform 0.2s;

            border: none;

            box-shadow: 0 4px 6px rgba(0,0,0,0.1);

        }

        .partner-card:hover {

            transform: translateY(-5px);

        }

    </style>

</head>

<body class="bg-light">



    <nav class="navbar navbar-dark bg-dark mb-4">

        <div class="container">

            <span class="navbar-brand">Intranet JOSSEL</span>

            <div>

                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Accueil</a>

                <a href="logout.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>

            </div>

        </div>

    </nav>



    <div class="container pb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2>🤝 Nos Partenaires</h2>

            <span class="badge bg-dark"><?= count($partenaires) ?> au total</span>

        </div>



        <?php if (!empty($partenaires)): ?>

            <div class="row row-cols-1 row-cols-md-3 g-4">

                <?php foreach ($partenaires as $p): ?>

                    <div class="col">

                        <div class="card h-100 partner-card text-center">

                            <img src="<?= htmlspecialchars($p['logo']) ?>" class="card-img-top" alt="Logo <?= htmlspecialchars($p['nom']) ?>">

                            

                            <div class="card-body d-flex flex-column">

                                <h5 class="card-title fw-bold"><?= htmlspecialchars($p['nom']) ?></h5>

                                <p class="card-text text-muted small flex-grow-1">

                                    <?= htmlspecialchars($p['description']) ?>

                                </p>

                                <div class="mt-3">

                                    <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" class="btn btn-dark btn-sm w-100">

                                        Visiter le site

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="alert alert-warning text-center">

                Aucun partenaire n'a été trouvé dans le fichier CSV.

            </div>

        <?php endif; ?>

    </div>



</body>

</html>

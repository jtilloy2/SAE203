<?php
/**
 * Plugin Name: Partenaires JOSSEL
 * Description: Affiche les partenaires de l'entreprise à partir des données CSV de l'intranet.
 * Version: 1.0
 * Author: Leny (Lot 4)
 */

// Sécurité : Empêcher l'accès direct au fichier PHP
if (!defined('ABSPATH')) exit;

function shortcode_liste_partenaires() {
    // 1. Définition des chemins (CSV sur le serveur et URL pour les images)
    $csv_file = ABSPATH . '../intranet/data/partenaires.csv';
    $base_url_images = "/SAE203/intranet/"; // URL du serveur de production
    
    // Injection du CSS pour forcer 3 éléments par ligne
    $output = '<style>
        .grille-partenaires {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Force exactement 3 colonnes de taille égale */
            gap: 20px; /* Espace entre les logos */
            padding: 20px 0;
        }
        .card-partenaire {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Petit effet d\'ombre sympa */
        }
        .card-partenaire img {
            max-height: 80px;
            max-width: 100%;
            margin-bottom: 10px;
        }
        /* Mode téléphone : 1 élément par ligne pour que ça reste beau */
        @media (max-width: 768px) {
            .grille-partenaires {
                grid-template-columns: 1fr;
            }
        }
    </style>';

    $output .= '<div class="grille-partenaires">';

    // 2. Vérification de l'existence du fichier
    if (file_exists($csv_file)) {
        // 3. Ouverture du fichier via fopen (Obligatoire)
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            
            // On saute la première ligne (les titres des colonnes)
            fgetcsv($handle, 1000, ",");

            // 4. Lecture ligne par ligne avec fgetcsv (Obligatoire)
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $nom = htmlspecialchars($data[0]);
                $logo_path = htmlspecialchars($data[1]); // contient "img/partenaires/fichier.png"
                $description = htmlspecialchars($data[2]);
                $site_web = htmlspecialchars($data[3]);

                // 5. Generation du HTML
                $output .= '
                <div class="card-partenaire">
                    <img src="' . $base_url_images . $logo_path . '" alt="Logo ' . $nom . '">
                    <h4 style="margin: 10px 0; color: #1a5c8a;">' . $nom . '</h4>
                    <p style="font-size: 0.9em; color: #666; height: 60px; overflow: hidden;">' . $description . '</p>
                    <a href="' . $site_web . '" target="_blank" style="display: inline-block; background: #0073aa; color: #fff; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.8em;">Visiter le site</a>
                </div>';
            }
            fclose($handle);
        }
    } else {
        // Gestion de l'erreur si le fichier CSV n'est pas trouvé
        $output .= '<p style="color: red; text-align: center;">Erreur : Impossible de charger les données des partenaires.</p>';
    }

    $output .= '</div>';
    return $output;
}

// 6. Enregistrement du shortcode
add_shortcode('afficher_partenaires', 'shortcode_liste_partenaires');
?>

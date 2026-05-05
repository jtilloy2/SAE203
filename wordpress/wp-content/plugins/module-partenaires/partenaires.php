<?php
/**
 * Plugin Name: Partenaires JOSSEL
 * Description: Affiche les partenaires de l'entreprise à partir des données CSV de l'intranet.
 * Version: 1.0
 * Author: Leny (Lot 4)
 */

<<<<<<< HEAD
// Sécurité : Empêcher l'accès direct au fichier PHP
if (!defined('ABSPATH')) exit;
=======
function rendu_partenaires_jossel() {
    // Chemin absolu vers le fichier de données géré par l'intranet
    $file_path = '/var/www/html/SAE203/intranet/data/partenaires.csv';
    $output = "<h2>Nos Partenaires</h2>";
>>>>>>> 357adb8ea30abd721e76e4706e6429186e285945

function shortcode_liste_partenaires() {
    // 1. Définition des chemins (CSV sur le serveur et URL pour les images)
    $csv_file = ABSPATH . '../intranet/data/partenaires.csv';
    $base_url_images = "http://172.18.203.79/intranet/"; // URL du serveur de production
    
    $output = '<div class="row" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; padding: 20px;">';

    // 2. Vérification de l'existence du fichier
    if (file_exists($csv_file)) {
        // 3. Ouverture du fichier via fopen (Obligatoire)
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            
            // On saute la première ligne (les titres des colonnes)
            fgetcsv($handle, 1000, ",");

            // 4. Lecture ligne par ligne avec fgetcsv (Obligatoire)
            // Structure CSV détectée : Nom[0], Logo[1], Description
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $nom = htmlspecialchars($data[0]);
                $logo_path = htmlspecialchars($data[1]); // contient "img/partenaires/fichier.png"
                $description = htmlspecialchars($data[2]);
                $site_web = htmlspecialchars($data[3]);

                // 5. Generation du HTML
                $output .= '
                <div class="card-partenaire" style="width: 250px; border: 1px solid #ddd; padding: 15px; text-align: center; border-radius: 8px;">
                    <img src="' . $base_url_images . $logo_path . '" alt="Logo ' . $nom . '" style="max-height: 80px; margin-bottom: 10px;">
                    <h4 style="margin: 10px 0;">' . $nom . '</h4>
                    <p style="font-size: 0.9em; color: #666; height: 60px; overflow: hidden;">' . $description . '</p>
                    <a href="' . $site_web . '" target="_blank" style="display: inline-block; background: #0073aa; color: #fff; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.8em;">Visiter le site</a>
                </div>';
            }
            fclose($handle);
        }
<<<<<<< HEAD
    } else {
        $output .= '<p>Erreur : Impossible de charger les données des partenaires.</p>';
=======
        fclose($handle);
>>>>>>> 357adb8ea30abd721e76e4706e6429186e285945
    }

    $output .= '</div>';
    return $output;
}

// 6. Enregistrement du shortcode
add_shortcode('afficher_partenaires', 'shortcode_liste_partenaires');

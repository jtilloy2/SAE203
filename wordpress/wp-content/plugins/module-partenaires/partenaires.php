<?php
/*
Plugin Name: JOSSEL - Partenaires Officiels
Description: Module d'affichage dynamique via partenaires.csv (Lot 4).
Author: Leny Chopis
Version: 1.2
*/

function rendu_partenaires_jossel() {
    // Chemin absolu vers le fichier de données géré par l'intranet
    $file_path = '/var/www/html/SAE203/intranet/data/partenaires.csv';
    $output = "<h2>Nos Partenaires</h2>";

    if (!file_exists($file_path)) {
        return $output . "<p>Fichier partenaires.csv introuvable dans l'intranet.</p>";
    }

    $output .= '<div class="partners-grid" style="display:flex; flex-wrap:wrap; gap:20px;">';
    
    // Utilisation des fonctions PHP obligatoires pour le CSV
    if (($handle = fopen($file_path, "r")) !== FALSE) {
        // Lecture de l'en-tête (Nom, Logo, Description)[cite: 2]
        fgetcsv($handle, 1000, ","); 

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $nom  = htmlspecialchars($data[0]);
            $logo = htmlspecialchars($data[1]);
            $desc = htmlspecialchars($data[2]);

            $output .= "
            <div class='partner-card' style='border:1px solid #ddd; padding:10px; width:180px; text-align:center;'>
                <img src='{$logo}' alt='Logo {$nom}' style='max-width:100px; height:auto;'>
                <h4>{$nom}</h4>
                <p style='font-size:0.85em;'>{$desc}</p>
            </div>";
        }
        fclose($handle);
    }

    $output .= '</div>';
    return $output;
}

// Shortcode pour Lillian (Lot 3) : [jossel_partners]
add_shortcode('jossel_partners', 'rendu_partenaires_jossel');

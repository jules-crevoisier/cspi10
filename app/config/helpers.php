<?php

/**
 * Fonction d'aide pour inclure des fichiers de manière sécurisée
 */
function include_file($path) {
    $fullPath = __DIR__ . '/../../public/' . $path;
    if (file_exists($fullPath)) {
        include $fullPath;
    } else {
        error_log("Fichier non trouvé : $fullPath");
    }
}

/**
 * Fonction pour formater une date en français
 */
function formatDateFrench($date) {
    $mois = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
    ];
    
    $timestamp = strtotime($date);
    $jour = date('j', $timestamp);
    $mois_num = date('n', $timestamp);
    $annee = date('Y', $timestamp);
    
    return $jour . ' ' . $mois[$mois_num] . ' ' . $annee;
}

/**
 * Fonction pour détecter le type d'images et déterminer la taille appropriée du carousel
 * @param array $images - Tableau des images
 * @return array - Informations sur le type d'images et la taille recommandée
 */
function analyzeImagesForCarousel($images) {
    if (empty($images)) {
        return [
            'type' => 'default',
            'height' => '500px',
            'class' => 'swiper-default'
        ];
    }
    
    $documentImages = 0;
    $totalImages = count($images);
    $maxWidth = 0;
    $maxHeight = 0;
    $aspectRatios = [];
    
    foreach ($images as $image) {
        $imagePath = __DIR__ . '/../../public/' . $image['url'];
        
        if (file_exists($imagePath)) {
            $imageInfo = getimagesize($imagePath);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                $aspectRatio = $width / $height;
                
                $maxWidth = max($maxWidth, $width);
                $maxHeight = max($maxHeight, $height);
                $aspectRatios[] = $aspectRatio;
                
                // Détection des images de type document (PDF/PowerPoint convertis)
                // Critères : ratio proche de A4 (0.707) ou 4:3 (1.33) ou 16:9 (1.78) avec haute résolution
                if (($aspectRatio > 0.6 && $aspectRatio < 0.8) || // Format portrait A4-like
                    ($aspectRatio > 1.2 && $aspectRatio < 1.4) ||  // Format 4:3 (présentation)
                    ($aspectRatio > 1.6 && $aspectRatio < 1.9)) {  // Format 16:9 (présentation)
                    
                    // Vérifier si c'est une image haute résolution (potentiellement convertie)
                    if ($width > 1500 || $height > 1500) {
                        $documentImages++;
                    }
                }
            }
        }
    }
    
    // Déterminer le type dominant
    $documentPercentage = $documentImages / $totalImages;
    
    if ($documentPercentage >= 0.7) {
        // Majorité d'images de type document
        $avgAspectRatio = array_sum($aspectRatios) / count($aspectRatios);
        
        if ($avgAspectRatio < 1) {
            // Format portrait
            return [
                'type' => 'document-portrait',
                'height' => '700px',
                'class' => 'swiper-document-portrait'
            ];
        } else {
            // Format paysage
            return [
                'type' => 'document-landscape',
                'height' => '600px',
                'class' => 'swiper-document-landscape'
            ];
        }
    } else {
        // Images normales (photos)
        return [
            'type' => 'photo',
            'height' => '500px',
            'class' => 'swiper-photo'
        ];
    }
} 
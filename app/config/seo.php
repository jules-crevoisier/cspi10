<?php

/**
 * Configuration SEO pour CSPI10
 * Chambre Syndicale des Propriétaires Immobiliers de l'Aube
 */

// Informations générales du site
define('SITE_NAME', 'CSPI10 - Chambre Syndicale des Propriétaires Immobiliers de l\'Aube');
define('SITE_DESCRIPTION', 'Chambre Syndicale des Propriétaires Immobiliers de l\'Aube (CSPI10). Défense, conseil et accompagnement des propriétaires immobiliers dans l\'Aube depuis 1895.');
if (!defined('SITE_URL')) {
    define('SITE_URL', APP_URL);
}
define('SITE_KEYWORDS', 'propriétaires immobiliers, Aube, Troyes, chambre syndicale, CSPI10, gestion locative, conseil immobilier, défense propriétaires, fiscalité immobilière, copropriété');

// Configuration SEO - maintenant intégrée dans les fonctions pour éviter les problèmes de portée

/**
 * Fonction pour obtenir les données SEO d'une page
 */
function getSeoData($page = 'home') {
    $seo_config = [
        'home' => [
            'title' => 'CSPI10 - Chambre Syndicale des Propriétaires Immobiliers de l\'Aube',
            'description' => 'Chambre Syndicale des Propriétaires Immobiliers de l\'Aube (CSPI10). Association créée en 1895, nous défendons et conseillons les propriétaires immobiliers dans l\'Aube.',
            'keywords' => 'CSPI10, propriétaires immobiliers Aube, Troyes, chambre syndicale, conseil immobilier, gestion locative, défense propriétaires',
            'canonical' => '/',
            'type' => 'website'
        ],
        'actualites' => [
            'title' => 'Actualités immobilières - CSPI10 Aube',
            'description' => 'Suivez toute l\'actualité immobilière dans l\'Aube avec CSPI10. Informations, conseils et nouveautés législatives pour les propriétaires immobiliers.',
            'keywords' => 'actualités immobilières, Aube, législation immobilière, DPE, fiscalité, propriétaires',
            'canonical' => '/actualites',
            'type' => 'website'
        ],
        'biens' => [
            'title' => 'Biens immobiliers - Vente & Location dans l\'Aube - CSPI10',
            'description' => 'Découvrez notre sélection de biens immobiliers dans l\'Aube : ventes, locations et locations étudiantes. Trouvez votre propriété idéale avec CSPI10.',
            'keywords' => 'biens immobiliers Aube, vente maison Troyes, location appartement, immobilier étudiant, propriétés Aube',
            'canonical' => '/biens',
            'type' => 'website'
        ],
        'adhesion' => [
            'title' => 'Adhérer à CSPI10 - Chambre Syndicale Propriétaires Aube',
            'description' => 'Rejoignez CSPI10 et bénéficiez de l\'accompagnement expert pour la gestion de votre patrimoine immobilier dans l\'Aube. Adhésion propriétaires immobiliers.',
            'keywords' => 'adhésion CSPI10, devenir membre, propriétaires Aube, conseil immobilier, défense propriétaires',
            'canonical' => '/adhesion',
            'type' => 'website'
        ],
        'contact' => [
            'title' => 'Contact CSPI10 - Chambre Syndicale Propriétaires Aube',
            'description' => 'Contactez CSPI10 pour tous vos besoins en tant que propriétaire immobilier dans l\'Aube. Conseil, accompagnement et défense de vos intérêts.',
            'keywords' => 'contact CSPI10, conseil propriétaires Aube, Troyes, accompagnement immobilier',
            'canonical' => '/contact',
            'type' => 'website'
        ],
        'partenaires' => [
            'title' => 'Nos Partenaires - CSPI10 Aube',
            'description' => 'Découvrez les partenaires de CSPI10 : notaires, avocats, experts immobiliers et professionnels du secteur dans l\'Aube.',
            'keywords' => 'partenaires CSPI10, notaires Aube, avocats immobilier, experts, professionnels immobiliers',
            'canonical' => '/partenaires',
            'type' => 'website'
        ],
        'mentions-legales' => [
            'title' => 'Mentions Légales - CSPI10',
            'description' => 'Mentions légales du site CSPI10 - Chambre Syndicale des Propriétaires Immobiliers de l\'Aube.',
            'keywords' => 'mentions légales, CSPI10, juridique',
            'canonical' => '/mentions-legales',
            'type' => 'website'
        ]
    ];
    
    if (isset($seo_config[$page])) {
        return $seo_config[$page];
    }
    
    return $seo_config['home']; // Fallback vers la page d'accueil
}

/**
 * Génère les balises meta SEO
 */
function generateSeoMeta($page = 'home', $custom_data = []) {
    $seo = getSeoData($page);
    
    // Vérification de sécurité
    if (empty($seo) || !is_array($seo)) {
        $seo = getSeoData('home');
    }
    
    // Override avec des données personnalisées si fournies
    if (!empty($custom_data)) {
        $seo = array_merge($seo, $custom_data);
    }
    
    $meta = '';
    
    // Title
    $meta .= '<title>' . htmlspecialchars($seo['title'] ?? SITE_NAME) . '</title>' . "\n";
    
    // Meta description
    $meta .= '<meta name="description" content="' . htmlspecialchars($seo['description'] ?? SITE_DESCRIPTION) . '">' . "\n";
    
    // Meta keywords
    $meta .= '<meta name="keywords" content="' . htmlspecialchars($seo['keywords'] ?? SITE_KEYWORDS) . '">' . "\n";
    
    // Canonical URL
    $meta .= '<link rel="canonical" href="' . SITE_URL . ($seo['canonical'] ?? '/') . '">' . "\n";
    
    // Open Graph
    $meta .= '<meta property="og:title" content="' . htmlspecialchars($seo['title'] ?? SITE_NAME) . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . htmlspecialchars($seo['description'] ?? SITE_DESCRIPTION) . '">' . "\n";
    $meta .= '<meta property="og:type" content="' . ($seo['type'] ?? 'website') . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . SITE_URL . ($seo['canonical'] ?? '/') . '">' . "\n";
    $meta .= '<meta property="og:site_name" content="' . SITE_NAME . '">' . "\n";
    $meta .= '<meta property="og:locale" content="fr_FR">' . "\n";
    $meta .= '<meta property="og:image" content="' . SITE_URL . '/asset/img/logo.png">' . "\n";
    
    // Twitter Card
    $meta .= '<meta name="twitter:card" content="summary">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . htmlspecialchars($seo['title'] ?? SITE_NAME) . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . htmlspecialchars($seo['description'] ?? SITE_DESCRIPTION) . '">' . "\n";
    $meta .= '<meta name="twitter:image" content="' . SITE_URL . '/asset/img/logo.png">' . "\n";
    
    // Autres balises importantes
    $meta .= '<meta name="robots" content="index, follow">' . "\n";
    $meta .= '<meta name="author" content="CSPI10">' . "\n";
    $meta .= '<meta name="geo.region" content="FR-10">' . "\n";
    $meta .= '<meta name="geo.placename" content="Troyes, Aube">' . "\n";
    $meta .= '<meta name="geo.position" content="48.2973;4.0744">' . "\n";
    $meta .= '<meta name="ICBM" content="48.2973, 4.0744">' . "\n";
    
    return $meta;
}

/**
 * Génère le JSON-LD pour les données structurées
 */
function generateJsonLd($page = 'home', $custom_data = []) {
    $organization = [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => "CSPI10 - Chambre Syndicale des Propriétaires Immobiliers de l'Aube",
        "alternateName" => "CSPI10",
        "url" => SITE_URL,
        "logo" => SITE_URL . "/asset/img/logo.png",
        "description" => SITE_DESCRIPTION,
        "foundingDate" => "1895",
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "20 Rue Général de Gaulle",
            "addressLocality" => "Troyes",
            "postalCode" => "10000",
            "addressCountry" => "FR",
            "addressRegion" => "Grand Est"
        ],
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => "+33-3-25-73-01-19",
            "contactType" => "Customer Service",
            "email" => "chambredesproprietaires10@gmail.com",
            "availableLanguage" => "French"
        ],
        "geo" => [
            "@type" => "GeoCoordinates",
            "latitude" => "48.2973",
            "longitude" => "4.0744"
        ],
        "areaServed" => [
            "@type" => "AdministrativeArea",
            "name" => "Aube"
        ],
        "knowsAbout" => [
            "Gestion immobilière",
            "Fiscalité immobilière", 
            "Droit immobilier",
            "Copropriété",
            "Location immobilière"
        ]
    ];
    
    if ($page === 'home') {
        $website = [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => SITE_NAME,
            "url" => SITE_URL,
            "description" => SITE_DESCRIPTION,
            "publisher" => [
                "@type" => "Organization",
                "name" => "CSPI10"
            ],
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => SITE_URL . "/biens?q={search_term_string}",
                "query-input" => "required name=search_term_string"
            ]
        ];
        
        return '<script type="application/ld+json">' . json_encode([$organization, $website], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    return '<script type="application/ld+json">' . json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
} 
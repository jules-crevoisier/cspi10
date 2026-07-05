<?php
namespace App\Controller;

use App\Models\Bien;
use App\Models\BienImage;
use PDOException;

class AdminBienController
{
    private $adminController;

    public function __construct()
    {
        $this->adminController = new AdminController();
    }

    public function index(): void
    {
        try {
            error_log("Début de la récupération de la liste des biens");
            $biens = Bien::getAll();
            error_log("Nombre de biens trouvés: " . count($biens));
            error_log("Liste des biens: " . print_r($biens, true));
            
            // S'assurer que la variable est disponible dans la vue
            $GLOBALS['biens'] = $biens;
            
            include_once __DIR__ . '/../../resources/admin/biens/liste_biens.php';
        } catch (\Exception $e) {
            error_log("Erreur dans index: " . $e->getMessage());
            $GLOBALS['biens'] = [];
            include_once __DIR__ . '/../../resources/admin/biens/liste_biens.php';
        }
    }

    public function create(): void
    {
        error_log("Méthode create() appelée");
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                error_log("Début de la création d'un bien");
                
                // Vérification des données
                if (empty($_POST['titre'])) {
                    throw new \Exception('Le titre est requis');
                }

                if (empty($_POST['type'])) {
                    throw new \Exception('Le type est requis');
                }

                $data = [
                    'titre' => $_POST['titre'],
                    'type' => $_POST['type'],
                    'adresse' => $_POST['adresse'] ?? '',
                    'surface_m2' => $_POST['surface_m2'] ? (int)$_POST['surface_m2'] : null,
                    'chambres' => $_POST['chambres'] ? (int)$_POST['chambres'] : null,
                    'salles_eau' => $_POST['salles_eau'] ? (int)$_POST['salles_eau'] : null,
                    'prix' => $_POST['prix'] ? number_format((float)$_POST['prix'], 2, '.', '') : null,
                    'description' => $_POST['description'] ?? ''
                ];

                error_log("Données préparées pour l'insertion: " . print_r($data, true));

                // Création du bien
                $bienId = Bien::create($data);
                error_log("Bien créé avec l'ID: " . $bienId);

                if (!$bienId) {
                    throw new \Exception('Erreur lors de la création du bien');
                }

                // Gestion des images
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    error_log("Traitement des images: " . print_r($_FILES['images'], true));
                    $this->handleImages($bienId, $_FILES['images']);
                }

                error_log("Redirection vers la liste des biens");
                header('Location: /admin/biens/liste_biens');
                exit;
            } catch (\Exception $e) {
                error_log("Erreur dans create: " . $e->getMessage());
                $error = 'Une erreur est survenue lors de la création du bien: ' . $e->getMessage();
                include_once __DIR__ . '/../../resources/admin/biens/form.php';
            }
        } else {
            error_log("Affichage du formulaire de création");
            include_once __DIR__ . '/../../resources/admin/biens/form.php';
        }
    }

    public function edit(int $id): void
    {
        try {
            $bien = Bien::getById($id);
            if (!$bien) {
                header('Location: /admin/biens/liste_biens');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'titre' => $_POST['titre'],
                    'type' => $_POST['type'],
                    'adresse' => $_POST['adresse'] ?? '',
                    'surface_m2' => $_POST['surface_m2'] ? (int)$_POST['surface_m2'] : null,
                    'chambres' => $_POST['chambres'] ? (int)$_POST['chambres'] : null,
                    'salles_eau' => $_POST['salles_eau'] ? (int)$_POST['salles_eau'] : null,
                    'prix' => $_POST['prix'] ? number_format((float)$_POST['prix'], 2, '.', '') : null,
                    'description' => $_POST['description'] ?? ''
                ];

                Bien::update($id, $data);

                // Gestion des images
                if (isset($_FILES['images'])) {
                    $this->handleImages($id, $_FILES['images']);
                }

                header('Location: /admin/biens/liste_biens');
                exit;
            }

            $images = BienImage::listByBien($id);
            include_once __DIR__ . '/../../resources/admin/biens/form.php';
        } catch (\Exception $e) {
            $error = 'Une erreur est survenue lors de la modification du bien';
            include_once __DIR__ . '/../../resources/admin/biens/form.php';
        }
    }

    public function delete(int $id): void
    {
        try {
            error_log("Début de la suppression du bien ID: " . $id);
            
            // Récupérer le bien avant de le supprimer
            $bien = Bien::getById($id);
            if (!$bien) {
                error_log("Bien non trouvé avec l'ID: " . $id);
                $_SESSION['error'] = 'Bien non trouvé.';
                header('Location: /admin/biens');
                exit;
            }

            // Si c'est une confirmation de suppression
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
                error_log("Confirmation de suppression reçue pour le bien ID: " . $id);
                
                // Supprimer le bien (les images seront supprimées automatiquement grâce à ON DELETE CASCADE)
                if (Bien::delete($id)) {
                    error_log("Bien supprimé avec succès");
                    $_SESSION['success'] = 'Le bien a été supprimé avec succès.';
                } else {
                    error_log("Échec de la suppression du bien");
                    $_SESSION['error'] = 'Erreur lors de la suppression du bien.';
                }
                
                header('Location: /admin/biens');
                exit;
            }

            // Sinon, afficher le formulaire de confirmation
            error_log("Affichage du formulaire de confirmation de suppression");
            include_once __DIR__ . '/../../resources/admin/biens/delete_confirm.php';
            
        } catch (\Exception $e) {
            error_log("Exception lors de la suppression: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Erreur lors de la suppression : ' . $e->getMessage();
            header('Location: /admin/biens');
            exit;
        }
    }

    private function handleImages(int $bienId, array $files): void
    {
        try {
            $uploadDir = PUBLIC_PATH . 'uploads/biens/';
            if (!file_exists($uploadDir)) {
                error_log("Le répertoire d'upload n'existe pas. Il devrait être créé par Docker: " . $uploadDir);
                throw new \Exception('Le dossier d\'upload n\'existe pas');
            }

            if (!is_writable($uploadDir)) {
                error_log("Le répertoire d'upload n'est pas accessible en écriture: " . $uploadDir);
                throw new \Exception('Le dossier d\'upload n\'est pas accessible en écriture');
            }

            foreach ($files['tmp_name'] as $key => $tmp_name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    // Validation du type de fichier
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mimeType, $allowedTypes)) {
                        error_log("Type de fichier non autorisé: " . $mimeType);
                        throw new \Exception('Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP');
                    }

                    // Validation de la taille (max 10MB)
                    if ($files['size'][$key] > 10 * 1024 * 1024) {
                        throw new \Exception('L\'image est trop volumineuse. Taille maximum : 10MB');
                    }

                    $fileName = uniqid() . '_' . basename($files['name'][$key]);
                    $uploadFile = $uploadDir . $fileName;

                    error_log("Tentative d'upload de l'image: " . $uploadFile);

                    if (move_uploaded_file($tmp_name, $uploadFile)) {
                        $isPrimary = isset($_POST['is_primary']) && $_POST['is_primary'] == $key;
                        $imageId = BienImage::add(
                            $bienId,
                            'uploads/biens/' . $fileName,
                            $isPrimary,
                            $key
                        );
                        error_log("Image uploadée avec succès. ID: " . $imageId);
                    } else {
                        error_log("Échec de l'upload de l'image: " . $uploadFile);
                        throw new \Exception('Erreur lors de l\'upload de l\'image');
                    }
                } else if ($files['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'L\'image dépasse la taille maximale autorisée par PHP',
                        UPLOAD_ERR_FORM_SIZE => 'L\'image dépasse la taille maximale autorisée par le formulaire',
                        UPLOAD_ERR_PARTIAL => 'L\'image n\'a été que partiellement uploadée',
                        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                        UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
                        UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload'
                    ];
                    $errorMessage = $errorMessages[$files['error'][$key]] ?? 'Erreur inconnue lors de l\'upload';
                    error_log("Erreur lors de l'upload: " . $errorMessage);
                    throw new \Exception($errorMessage);
                }
            }
        } catch (\Exception $e) {
            error_log("Erreur dans handleImages: " . $e->getMessage());
            throw $e;
        }
    }

    public function setPrimaryImage(int $bienId, int $imageId): void
    {
        try {
            BienImage::setPrimary($bienId, $imageId);
            error_log("Image principale mise à jour avec succès. Bien ID: $bienId, Image ID: $imageId");
        } catch (PDOException $e) {
            error_log("Erreur setPrimaryImage: " . $e->getMessage());
            throw new \Exception("Erreur lors de la mise à jour de l'image principale: " . $e->getMessage());
        }
    }

    public function deleteImage(int $bienId, int $imageId): void
    {
        // Récupérer les détails de l'image avant suppression
        $image = BienImage::getById($imageId);
        
        if (!$image || $image['bien_id'] != $bienId) {
            throw new \Exception('Image non trouvée');
        }

        // Supprimer le fichier physique
        $filePath = PUBLIC_PATH . $image['url'];
        error_log("Tentative de suppression du fichier: " . $filePath);
        
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                error_log("Fichier supprimé avec succès: " . $filePath);
            } else {
                error_log("ERREUR: Impossible de supprimer le fichier: " . $filePath);
                throw new \Exception('Impossible de supprimer le fichier physique');
            }
        } else {
            error_log("ATTENTION: Fichier non trouvé: " . $filePath);
        }

        // Supprimer l'entrée dans la base de données
        if (BienImage::delete($imageId)) {
            error_log("Image supprimée de la base de données. ID: " . $imageId);
            
            // Si c'était l'image principale, définir une nouvelle image principale
            if ($image['is_primary']) {
                $otherImages = BienImage::listByBien($bienId);
                if (!empty($otherImages)) {
                    BienImage::setPrimary($bienId, $otherImages[0]['id']);
                    error_log("Nouvelle image principale définie: " . $otherImages[0]['id']);
                }
            }
        } else {
            throw new \Exception('Erreur lors de la suppression de l\'image en base de données');
        }
    }

    public function view($id)
    {
        try {
            error_log("Début de la méthode view() pour le bien ID: " . $id);
            
            // Initialiser la connexion à la base de données
            // Récupérer le bien
            $bien = Bien::getById($id);
            error_log("Bien récupéré: " . print_r($bien, true));

            if (!$bien) {
                error_log("Bien non trouvé avec l'ID: " . $id);
                $_SESSION['error'] = 'Bien non trouvé.';
                header('Location: /admin/biens');
                exit;
            }

            // Récupérer les images du bien
            $images = BienImage::listByBien($id);
            error_log("Images récupérées: " . print_r($images, true));

            // Afficher la page de détails
            include_once __DIR__ . '/../../resources/admin/biens/view.php';
            
        } catch (\Exception $e) {
            error_log("Exception dans view(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Erreur lors de l\'affichage des détails du bien : ' . $e->getMessage();
            header('Location: /admin/biens');
            exit;
        }
    }
} 
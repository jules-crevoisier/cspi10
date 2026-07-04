<?php
namespace App\Controller;

use App\Models\Actualite;
use App\Models\ActualiteImage;
use PDOException;

class AdminActualiteController extends AdminController
{
    private $uploadDir = 'uploads/actualites/';

    public function __construct()
    {
        parent::__construct();
        // Check if upload directory exists but don't try to create it here
        // It should be created by Docker during container build
        if (!file_exists(PUBLIC_PATH . $this->uploadDir)) {
            error_log("Warning: Upload directory does not exist: " . PUBLIC_PATH . $this->uploadDir);
            // Don't try to create it here - this should be handled by Docker
        }
    }

    public function index()
    {
        error_log("Appel de AdminActualiteController::index()");
        return Actualite::getAll();
    }

    public function create()
    {
        error_log("Début de la méthode create()");
        error_log("Méthode de requête: " . $_SERVER['REQUEST_METHOD']);
        
        // Initialize DB connection
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Traitement du formulaire POST pour créer une actualité");
            error_log("Contenu de \$_POST: " . print_r($_POST, true));
            error_log("Contenu de \$_FILES: " . print_r($_FILES, true));
            
            try {
                $data = [
                    'titre' => $_POST['titre'] ?? '',
                    'categorie' => $_POST['categorie'] ?? '',
                    'contenu' => $_POST['contenu'] ?? '',
                    'publie_le' => $_POST['publie_le'] ?? date('Y-m-d')
                ];
                
                error_log("Données initiales: " . print_r($data, true));

                // Création de l'actualité
                $actualiteId = Actualite::create($data);
                error_log("Actualité créée avec l'ID: " . $actualiteId);

                if (!$actualiteId) {
                    throw new \Exception('Erreur lors de la création de l\'actualité');
                }

                // Gestion des images
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    error_log("Traitement des images: " . print_r($_FILES['images'], true));
                    $this->handleImages($actualiteId, $_FILES['images']);
                }

                error_log("Création réussie, redirection vers la liste des actualités");
                header('Location: /index.php/admin/actualites');
                exit;
            } catch (\Exception $e) {
                error_log("Erreur dans create: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $_SESSION['error'] = 'Une erreur est survenue lors de la création de l\'actualité: ' . $e->getMessage();
            }
        }
    }

    public function edit($id)
    {
        error_log("Début de la méthode edit() avec ID: " . $id);
        
        $actualite = Actualite::getById($id);
        
        if (!$actualite) {
            error_log("Actualité avec ID " . $id . " non trouvée");
            header('Location: /index.php/admin/actualites');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Traitement du formulaire POST pour éditer l'actualité ID " . $id);
            error_log("Contenu de \$_POST: " . print_r($_POST, true));
            error_log("Contenu de \$_FILES: " . print_r($_FILES, true));
            
            try {
                $data = [
                    'titre' => $_POST['titre'] ?? '',
                    'categorie' => $_POST['categorie'] ?? '',
                    'contenu' => $_POST['contenu'] ?? '',
                    'publie_le' => $_POST['publie_le'] ?? date('Y-m-d')
                ];
                
                error_log("Données initiales: " . print_r($data, true));

                // Mise à jour de l'actualité
                if (Actualite::update($id, $data)) {
                    // Gestion des images
                    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                        $this->handleImages($id, $_FILES['images']);
                    }

                    error_log("Mise à jour réussie, redirection vers la liste des actualités");
                    header('Location: /index.php/admin/actualites');
                    exit;
                } else {
                    throw new \Exception('Erreur lors de la mise à jour de l\'actualité');
                }
            } catch (\Exception $e) {
                error_log("Erreur dans edit: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $_SESSION['error'] = 'Une erreur est survenue lors de la modification de l\'actualité: ' . $e->getMessage();
            }
        }

        return $actualite;
    }

    public function delete($id)
    {
        error_log("Début de la méthode delete() avec ID: " . $id);
        
        $actualite = Actualite::getById($id);

        if (!$actualite) {
            error_log("Actualité avec ID " . $id . " non trouvée");
            $_SESSION['error'] = 'Actualité non trouvée.';
            header('Location: /index.php/admin/actualites');
            exit;
        }

        // Si c'est une confirmation de suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
            error_log("Tentative de suppression de l'actualité ID: " . $id);
            
            try {
                if (Actualite::delete($id)) {
                    // Les images seront supprimées automatiquement grâce à ON DELETE CASCADE
                    error_log("Actualité supprimée avec succès");
                    $_SESSION['success'] = 'L\'actualité a été supprimée avec succès.';
                } else {
                    error_log("Échec de la suppression de l'actualité");
                    $_SESSION['error'] = 'Erreur lors de la suppression de l\'actualité.';
                }
            } catch (\Exception $e) {
                error_log("Erreur lors de la suppression: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $_SESSION['error'] = 'Une erreur est survenue lors de la suppression de l\'actualité: ' . $e->getMessage();
            }
            
            header('Location: /index.php/admin/actualites');
            exit;
        }

        // Sinon, afficher le formulaire de confirmation
        include_once __DIR__ . '/../../public/admin/actualites/delete_confirm.php';
    }

    private function handleImages($actualiteId, $files)
    {
        try {
            $uploadDir = PUBLIC_PATH . $this->uploadDir;
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (!is_writable($uploadDir)) {
                throw new \Exception('Le dossier d\'upload n\'est pas accessible en écriture');
            }

            // Vérifier si c'est la première image de l'actualité
            $existingImages = ActualiteImage::listByActualite($actualiteId);
            $isFirstImage = empty($existingImages);

            foreach ($files['tmp_name'] as $key => $tmp_name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    // Validation du type de fichier
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($mimeType, $allowedTypes)) {
                        throw new \Exception('Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF');
                    }

                    // Validation de la taille (max 5MB)
                    if ($files['size'][$key] > 5 * 1024 * 1024) {
                        throw new \Exception('L\'image est trop volumineuse. Taille maximum : 5MB');
                    }

                    $fileName = uniqid() . '_' . basename($files['name'][$key]);
                    $uploadFile = $uploadDir . $fileName;

                    if (move_uploaded_file($tmp_name, $uploadFile)) {
                        // La première image est automatiquement définie comme principale
                        $isPrimary = $isFirstImage || (isset($_POST['is_primary']) && $_POST['is_primary'] == $key);
                        
                        $imageId = ActualiteImage::add(
                            $actualiteId,
                            $this->uploadDir . $fileName,
                            $isPrimary,
                            $key
                        );

                        if ($isPrimary) {
                            ActualiteImage::setPrimary($actualiteId, $imageId);
                        }

                        error_log("Image uploadée avec succès. ID: " . $imageId);
                    } else {
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
                    throw new \Exception($errorMessage);
                }
            }
        } catch (\Exception $e) {
            error_log("Erreur dans handleImages: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteImage($actualiteId, $imageId)
    {
        try {
            $image = ActualiteImage::getById($imageId);
            if (!$image || $image['actualite_id'] != $actualiteId) {
                throw new \Exception('Image non trouvée');
            }

            // Supprimer le fichier physique
            $filePath = PUBLIC_PATH . $image['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Supprimer l'entrée dans la base de données
            ActualiteImage::delete($imageId);

            // Si c'était l'image principale, définir une nouvelle image principale
            if ($image['is_primary']) {
                $otherImages = ActualiteImage::listByActualite($actualiteId);
                if (!empty($otherImages)) {
                    ActualiteImage::setPrimary($actualiteId, $otherImages[0]['id']);
                }
            }

            $_SESSION['success'] = 'Image supprimée avec succès';
        } catch (\Exception $e) {
            error_log("Erreur lors de la suppression de l'image: " . $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de la suppression de l\'image: ' . $e->getMessage();
        }
    }

    public function setPrimaryImage($actualiteId, $imageId)
    {
        try {
            $image = ActualiteImage::getById($imageId);
            if (!$image || $image['actualite_id'] != $actualiteId) {
                throw new \Exception('Image non trouvée');
            }

            ActualiteImage::setPrimary($actualiteId, $imageId);
            $_SESSION['success'] = 'Image principale mise à jour';
        } catch (\Exception $e) {
            error_log("Erreur lors de la mise à jour de l'image principale: " . $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de la mise à jour de l\'image principale: ' . $e->getMessage();
        }
    }

    public function getActualite($id) {
        error_log("Récupération de l'actualité ID: " . $id);
        try {
            $actualite = Actualite::getById($id);
            if ($actualite) {
                error_log("Actualité trouvée");
                return $actualite;
            }
            error_log("Actualité non trouvée");
            return null;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de l'actualité: " . $e->getMessage());
            return null;
        }
    }
} 
<?php

namespace App\Controller;

use App\Models\Partenaire;

class AdminPartenaireController extends AdminController
{
    private $uploadDir = 'uploads/partenaires/';

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
        error_log("Appel de AdminPartenaireController::index()");
        error_log("Vérification de la classe Partenaire: " . (class_exists(Partenaire::class) ? "existe" : "n'existe pas"));
        error_log("Vérification de la méthode getAll: " . (method_exists(Partenaire::class, 'getAll') ? "existe" : "n'existe pas"));
        
        // Make sure the database connection is initialized
        return Partenaire::getAll();
    }

    public function create()
    {
        error_log("Début de la méthode create()");
        error_log("Méthode de requête: " . $_SERVER['REQUEST_METHOD']);
        
        // Initialize DB connection
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Traitement du formulaire POST pour créer un partenaire");
            error_log("Contenu de \$_FILES: " . print_r($_FILES, true));
            
            $data = [
                'nom' => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'site_url' => $_POST['site_url'] ?? ''
            ];
            
            error_log("Données initiales: " . print_r($data, true));

            // Handle logo upload
            $logoPath = $this->handleLogoUpload();
            if ($logoPath) {
                error_log("Logo uploadé: " . $logoPath);
                $data['logo_url'] = $logoPath;
            }
            
            error_log("Données finales avant création: " . print_r($data, true));

            if (Partenaire::create($data)) {
                error_log("Création réussie, redirection vers la liste des partenaires");
                header('Location: /index.php/admin/partenaires');
                exit;
            } else {
                error_log("Échec de la création du partenaire");
            }
        }
    }

    public function edit($id)
    {
        error_log("Début de la méthode edit() avec ID: " . $id);
        
        $partenaire = Partenaire::getById($id);
        
        if (!$partenaire) {
            error_log("Partenaire avec ID " . $id . " non trouvé");
            header('Location: /index.php/admin/partenaires');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Traitement du formulaire POST pour éditer le partenaire ID " . $id);
            error_log("Contenu de \$_FILES: " . print_r($_FILES, true));
            error_log("Contenu de \$_POST: " . print_r($_POST, true));
            
            $data = [
                'nom' => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'site_url' => $_POST['site_url'] ?? ''
            ];
            
            error_log("Données initiales: " . print_r($data, true));

            // Handle logo upload
            $logoPath = $this->handleLogoUpload();
            if ($logoPath) {
                error_log("Nouveau logo uploadé: " . $logoPath);
                
                // Remove old logo if exists
                if (!empty($partenaire['logo_url']) && file_exists(PUBLIC_PATH . $partenaire['logo_url'])) {
                    error_log("Suppression de l'ancien logo: " . $partenaire['logo_url']);
                    unlink(PUBLIC_PATH . $partenaire['logo_url']);
                }
                
                $data['logo_url'] = $logoPath;
            } else {
                // Keep the existing logo_url if no new file was uploaded
                $data['logo_url'] = $_POST['existing_logo'] ?? $partenaire['logo_url'] ?? '';
                error_log("Pas de nouveau logo, conservation de l'ancien: " . $data['logo_url']);
            }
            
            error_log("Données finales avant mise à jour: " . print_r($data, true));

            if (Partenaire::update($id, $data)) {
                error_log("Mise à jour réussie, redirection vers la liste des partenaires");
                header('Location: /index.php/admin/partenaires');
                exit;
            } else {
                error_log("Échec de la mise à jour du partenaire");
            }
        }

        return $partenaire;
    }

    public function delete($id)
    {
        $partenaire = Partenaire::getById($id);

        if (!$partenaire) {
            $_SESSION['error'] = 'Partenaire non trouvé.';
            header('Location: /index.php/admin/partenaires');
            exit;
        }

        // Si c'est une confirmation de suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
            if (Partenaire::delete($id)) {
                // Delete logo file if exists
                if (!empty($partenaire['logo_url']) && file_exists(PUBLIC_PATH . $partenaire['logo_url'])) {
                    unlink(PUBLIC_PATH . $partenaire['logo_url']);
                }
                $_SESSION['success'] = 'Le partenaire a été supprimé avec succès.';
            } else {
                $_SESSION['error'] = 'Erreur lors de la suppression du partenaire.';
            }
            header('Location: /index.php/admin/partenaires');
            exit;
        }

        // Sinon, afficher le formulaire de confirmation
        include_once __DIR__ . '/../../public/admin/partenaires/delete_confirm.php';
    }

    /**
     * Handle logo file upload
     * @return string|null File path if successful, null otherwise
     */
    private function handleLogoUpload()
    {
        error_log("Début du traitement d'upload de logo");
        
        // Debug the $_FILES array
        error_log("Contenu de \$_FILES: " . print_r($_FILES, true));
        
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            error_log("Aucun fichier n'a été uploadé");
            return null;
        }

        $file = $_FILES['logo'];
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("Erreur lors de l'upload: " . $this->getUploadErrorMessage($file['error']));
            return null;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        
        error_log("Type de fichier détecté: " . $fileType);
        
        if (!in_array($fileType, $allowedTypes)) {
            error_log("Type de fichier non autorisé: " . $fileType);
            return null;
        }

        // Check if upload directory exists and is writable
        $fullUploadDir = PUBLIC_PATH . $this->uploadDir;
        if (!file_exists($fullUploadDir)) {
            error_log("Tentative de création du répertoire d'upload: " . $fullUploadDir);
            // Use @ to suppress warnings, we'll handle errors ourselves
            if (!@mkdir($fullUploadDir, 0777, true)) {
                $error = error_get_last();
                error_log("Erreur lors de la création du répertoire: " . ($error ? $error['message'] : 'Erreur inconnue'));
                error_log("Vérification des permissions du parent: " . substr(sprintf('%o', fileperms(dirname($fullUploadDir))), -4));
                return null;
            }
            // Set proper permissions after creating
            chmod($fullUploadDir, 0777);
        }
        
        if (!is_writable($fullUploadDir)) {
            error_log("Le répertoire d'upload n'est pas accessible en écriture: " . $fullUploadDir);
            error_log("Tentative de modification des permissions");
            chmod($fullUploadDir, 0777);
            
            if (!is_writable($fullUploadDir)) {
                error_log("Impossible de rendre le répertoire accessible en écriture même après chmod");
                return null;
            }
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('logo_') . '.' . $extension;
        $destination = $this->uploadDir . $filename;
        $fullDestination = PUBLIC_PATH . $destination;
        
        error_log("Tentative de déplacement du fichier vers: " . $fullDestination);

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fullDestination)) {
            error_log("Fichier uploadé avec succès: " . $destination);
            return $destination;
        }

        error_log("Échec du déplacement du fichier uploadé");
        return null;
    }
    
    /**
     * Get human-readable upload error message
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "Le fichier dépasse la taille maximale définie dans php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "Le fichier dépasse la taille maximale définie dans le formulaire HTML";
            case UPLOAD_ERR_PARTIAL:
                return "Le fichier n'a été que partiellement uploadé";
            case UPLOAD_ERR_NO_FILE:
                return "Aucun fichier n'a été uploadé";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Le dossier temporaire est manquant";
            case UPLOAD_ERR_CANT_WRITE:
                return "Échec d'écriture du fichier sur le disque";
            case UPLOAD_ERR_EXTENSION:
                return "Upload arrêté par une extension PHP";
            default:
                return "Erreur d'upload inconnue: " . $errorCode;
        }
    }
} 
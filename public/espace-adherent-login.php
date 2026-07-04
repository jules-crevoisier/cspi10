<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Security;

Security::startSession();

$adherentPassword = ESPACE_ADHERENT_PASSWORD;
if ($adherentPassword === '') {
    http_response_code(503);
    exit('Espace adhérent non configuré. Contactez l\'administrateur.');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($password === $adherentPassword) {
        Security::regenerateSession();
        $_SESSION['adherent_logged_in'] = true;
        $_SESSION['adherent_login_time'] = time();
        header('Location: espace-adherent.php');
        exit;
    }
    $message = 'Mot de passe incorrect. Vérifiez votre saisie ou contactez la CSPI10.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Espace Adhérent - FDPCI</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Open Sans', sans-serif;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }
        
        .login-container {
            background: var(--white);
            padding: 3rem 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .login-title {
            margin-bottom: 2rem;
        }
        
        .login-title i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: block;
        }
        
        .login-title h2 {
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.8rem;
        }
        
        .login-title p {
            color: #666;
            margin: 0;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .back-link {
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: var(--primary-color);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #c62828;
            text-align: left;
        }
        
        .info-box {
            font-size: 0.85rem;
            color: #666;
            text-align: center;
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        @media (max-width: 768px) {
            .login-wrapper {
                padding: 1rem;
            }
            
            .login-container {
                padding: 2rem 1.5rem;
            }
            
            .login-title h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
                <div class="login-title">
                    <i class="fas fa-users"></i>
                    <h2>Espace Adhérent</h2>
                    <p>Accès réservé aux membres FDPCI</p>
                </div>
                
            <?php if ($message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>
            
            <div class="back-link">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Retour au site
                </a>
            </div>
        </div>
    </div>
</body>
</html> 
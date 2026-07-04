<?php include_file('include/header.php'); ?>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Contactez-nous</h1>
                    <p>Nous sommes à votre écoute pour répondre à toutes vos questions</p>
                </div>
            </div>
        </section>

        <section class="contact-section">
            <div class="container">
                <div class="contact-container">
                    <div class="contact-info">
                        <h3>Informations de contact</h3>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Adresse</h4>
                                <p>20 Rue Général de Gaulle, 10000 Troyes</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Téléphone</h4>
                                <p>03 25 73 01 19</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>chambredesproprietaires10@gmail.com</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Horaires</h4>
                                                <p>Lundi, mardi, jeudi, vendredi: 14h00 - 18h00</p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form">
                        <h3>Envoyez-nous un message</h3>
                        <div id="contactAlert" style="display: none; margin-bottom: 1rem;"></div>
                        <form id="contactForm">
                            <div class="form-group">
                                <label for="name">Nom complet</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="subject">Sujet</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span id="btnText">Envoyer le message</span>
                                <span id="btnLoader" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Envoi en cours...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="map-section">
                    <h3>Notre localisation</h3>
                    <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4463.767001343858!2d4.072492698240987!3d48.299548344640215!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47ee98f6339c0da7%3A0x8e47e12c38c26ed1!2sChambre%20Syndicale%20des%20Propri%C3%A9taires%20de%20l&#39;Aube!5e0!3m2!1sfr!2sfr!4v1750236900000!5m2!1sfr!2sfr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php include_file('include/footer.php'); ?>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');
            const alertDiv = document.getElementById('contactAlert');
            
            // Désactiver le bouton et afficher le loader
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline';
            
            // Préparer les données du formulaire
            const formData = new FormData(form);
            
            // Envoyer la requête AJAX
            fetch('/contact', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'error', data.message);
                
                if (data.success) {
                    form.reset(); // Vider le formulaire en cas de succès
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('error', 'Une erreur inattendue s\'est produite. Veuillez réessayer.');
            })
            .finally(() => {
                // Réactiver le bouton
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoader.style.display = 'none';
            });
        });
        
        function showAlert(type, message) {
            const alertDiv = document.getElementById('contactAlert');
            alertDiv.className = type === 'success' ? 'alert alert-success' : 'alert alert-error';
            alertDiv.textContent = message;
            alertDiv.style.display = 'block';
            
            // Faire défiler vers l'alerte
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Masquer l'alerte après 5 secondes
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }
    </script>
    
    <style>
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            font-weight: 500;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        #submitBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</rewritten_file> 
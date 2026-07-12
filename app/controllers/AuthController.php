<?php
require_once __DIR__ . '/../core/Controleur.php';
require_once __DIR__ . '/../models/ApprenantModel.php';

class AuthController extends Controleur
{
    private const MOT_DE_PASSE_MIN = 8;

    private ApprenantModel $apprenantModel;

    public function __construct()
    {
        $this->apprenantModel = new ApprenantModel();
        $this->apprenantModel->initialiserDonneesDemo();
    }

    public function afficherLogin(): void
    {
        if (SessionManager::has('utilisateur')) {
            $this->redirigerVersEspace(SessionManager::get('utilisateur')['role']);
        }
        $this->render('auth/login');
    }

    public function traiterLogin(): void
    {
        $email = $_POST['email'] ?? '';
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $utilisateur = $this->apprenantModel->getParEmail($email);

        if (!$utilisateur || !$utilisateur['actif'] || !password_verify($motDePasse, $utilisateur['motDePasse'])) {
            $this->setFlash('erreur', 'Identifiants incorrects. Veuillez réessayer.');
            $this->rediriger('/login');
        }

        // Protection contre la fixation de session : nouvel ID après authentification
        SessionManager::regenererId();

        // On ne stocke jamais le mot de passe (même haché) dans la session applicative
        unset($utilisateur['motDePasse']);
        SessionManager::set('utilisateur', $utilisateur);

        $this->redirigerVersEspace($utilisateur['role']);
    }

    public function afficherInscription(): void
    {
        $this->render('auth/inscription');
    }

    public function traiterInscription(): void
    {
        $prenom = $this->nettoyer($_POST['prenom'] ?? '');
        $nom = $this->nettoyer($_POST['nom'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';

        $erreur = $this->validerInscription($prenom, $nom, $email, $motDePasse, $confirmation);
        if ($erreur) {
            $this->setFlash('erreur', $erreur);
            $this->rediriger('/inscription');
        }

        $nouvel = $this->apprenantModel->creer($prenom, $nom, $email, $motDePasse, 'apprenant');

        SessionManager::regenererId();
        unset($nouvel['motDePasse']);
        SessionManager::set('utilisateur', $nouvel);

        $this->rediriger('/apprenant/dashboard');
    }

    public function deconnexion(): void
    {
        SessionManager::detruire();
        $this->rediriger('/login');
    }

    /** Centralise toutes les règles de validation du formulaire d'inscription */
    private function validerInscription(string $prenom, string $nom, ?string $email, string $motDePasse, string $confirmation): ?string
    {
        if (empty($prenom) || empty($nom)) {
            return 'Le prénom et le nom sont obligatoires.';
        }
        if (!$email) {
            return 'Adresse email invalide.';
        }
        if (strlen($motDePasse) < self::MOT_DE_PASSE_MIN) {
            return 'Le mot de passe doit contenir au moins ' . self::MOT_DE_PASSE_MIN . ' caractères.';
        }
        if ($motDePasse !== $confirmation) {
            return 'La confirmation du mot de passe ne correspond pas.';
        }
        if ($this->apprenantModel->getParEmail($email)) {
            return 'Un compte existe déjà avec cet email.';
        }
        return null;
    }

    private function redirigerVersEspace(string $role): void
    {
        match ($role) {
            'gerant' => $this->rediriger('/gerant/dashboard'),
            'coach'  => $this->rediriger('/coach/dashboard'),
            default  => $this->rediriger('/apprenant/dashboard'),
        };
    }
}

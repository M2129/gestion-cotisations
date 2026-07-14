<?php
require_once __DIR__ . '/../core/procedural_core.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../models/apprenant.php';

function afficherLogin(): void
{
    if (getData('utilisateur')) {
        redirigerVersEspace(getData('utilisateur')['role']);
    }
    render('auth/login');
}

function traiterLogin(): void
{
    apprenant_initialiserDonneesDemo();

    $email = $_POST['email'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $utilisateur = apprenant_getParEmail($email);

    if (!$utilisateur || !$utilisateur['actif'] || !password_verify($motDePasse, $utilisateur['motDePasse'])) {
        setFlash('erreur', 'Identifiants incorrects. Veuillez réessayer.');
        rediriger('/login');
    }

    regenerateSessionId();

    unset($utilisateur['motDePasse']);
    save('utilisateur', $utilisateur);

    redirigerVersEspace($utilisateur['role']);
}

function afficherInscription(): void
{
    render('auth/inscription');
}

function traiterInscription(): void
{
    apprenant_initialiserDonneesDemo();

    $prenom = nettoyer($_POST['prenom'] ?? '');
    $nom = nettoyer($_POST['nom'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    $erreur = validerInscription($prenom, $nom, $email, $motDePasse, $confirmation);
    if ($erreur) {
        setFlash('erreur', $erreur);
        rediriger('/inscription');
    }

    $nouvel = apprenant_creer($prenom, $nom, $email, $motDePasse, 'apprenant');

    regenerateSessionId();
    unset($nouvel['motDePasse']);
    save('utilisateur', $nouvel);

    rediriger('/apprenant/dashboard');
}

function deconnexion(): void
{
    destroySession();
    rediriger('/login');
}

function validerInscription(string $prenom, string $nom, ?string $email, string $motDePasse, string $confirmation): ?string
{
    if (!required($prenom) || !required($nom)) {
        return 'Le prénom et le nom sont obligatoires.';
    }
    if (!$email || !isEmail($email)) {
        return 'Adresse email invalide.';
    }
    if (!isPassword($motDePasse, 8)) {
        return 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($motDePasse !== $confirmation) {
        return 'La confirmation du mot de passe ne correspond pas.';
    }
    if (!unique($email, fn(string $value) => (bool) apprenant_getParEmail($value))) {
        return 'Un compte existe déjà avec cet email.';
    }
    return null;
}

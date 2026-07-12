<?php
require_once __DIR__ . '/../app/core/SessionManager.php';
require_once __DIR__ . '/../app/core/Routeur.php';

SessionManager::demarrer();

$routeur = new Routeur();

// Authentification
$routeur->ajouter('GET',  '/login',       'AuthController', 'afficherLogin');
$routeur->ajouter('POST', '/login',       'AuthController', 'traiterLogin');
$routeur->ajouter('GET',  '/inscription', 'AuthController', 'afficherInscription');
$routeur->ajouter('POST', '/inscription', 'AuthController', 'traiterInscription');
$routeur->ajouter('GET',  '/logout',      'AuthController', 'deconnexion');

// Gérant (Incrément 1)
$routeur->ajouter('GET',  '/gerant/dashboard',        'GerantController', 'dashboard');
$routeur->ajouter('GET',  '/gerant/paiements/create',  'GerantController', 'afficherFormulairePaiement');
$routeur->ajouter('POST', '/gerant/paiements/create',  'GerantController', 'enregistrerPaiement');
$routeur->ajouter('GET',  '/gerant/campagnes/create',  'GerantController', 'afficherFormulaireCampagne');
$routeur->ajouter('POST', '/gerant/campagnes/create',  'GerantController', 'enregistrerCampagne');
$routeur->ajouter('GET',  '/gerant/apprenants',        'GerantController', 'apprenants');
$routeur->ajouter('POST', '/gerant/apprenants',        'GerantController', 'apprenants');

// Apprenant et Coach : à brancher en Incréments 2 et 3
// $routeur->ajouter('GET', '/apprenant/dashboard', 'ApprenantController', 'dashboard');
// $routeur->ajouter('GET', '/coach/dashboard',     'CoachController', 'dashboard');

$routeur->dispatch();

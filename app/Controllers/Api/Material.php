<?php

namespace App\Controllers\Api;

use App\Models\MaterialModel;
use App\Models\UserModel;
use App\Models\BlacklistModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use CodeIgniter\RESTful\ResourceController;

class Material extends ResourceController
{

    public function getAllMaterials() {
        $token = $this->request->getHeaderLine('Authorization');
        $data = $this->request->getGet();

        if ($token && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $userId = validateToken($matches[1]);

            if ($userId) {
                $userModel = Model('UserModel');
                $at = Model('ApiTokenModel');

                // Vérifier si l'utilisateur existe
                $user = $userModel->find($userId);
                $apiToken = $at->where('id_user', $userId)->first();

                // Vérifier si le token existe et si le compteur est à 0
                if (!$apiToken || $apiToken['counter'] <= 0) {
                    return $this->respond(['error' => 'Limite atteinte'], 403);
                }

                if (!$userId) {
                    return $this->respond(['error' => 'Token invalide'], 401);
                }

                // Vérifier si l'utilisateur est en blacklist
                $bm = Model('BlacklistModel');
                $isBlacklisted = $bm->where('id_user', $userId)->first();

                if ($isBlacklisted) {
                    return $this->respond(['error' => 'Utilisateur bloqué'], 403);
                }
                $mm = Model('MaterialModel');
                $materialsData = $mm->getAllMaterials(); // Assurez-vous que cette méthode récupère les bons champs

                // Modifier la structure de la réponse pour inclure les champs demandés
                $materials = [];
                foreach ($materialsData as $material) {
                    $materials[] = [
                        'id' => $material['id'],           // Remplacez 'id' par le nom réel de votre colonne ID
                        'reference' => $material['reference'], // Remplacez 'reference'
                        'nserie' => $material['nserie'],     // Remplacez 'nserie'
                        'badge' => $material['badge'],       // Remplacez 'badge'
                    ];
                }

                return $this->respond([
                    'message' => "Liste des matériaux",
                    'materials' => $mm->getAllMaterials(),
                ], 200);
            }
        }
        return $this->respond(['error' => 'Invalid token'], 401);
    }


    public function getmaterial($id = null)
    {
        if (empty($id)) {
            return $this->respond(['message' => "Erreur , id introuvable"], 200);
        } else {
            $mm = Model('MaterialModel');
            $material = $mm->find($id);
            if (!$material) {
                return $this->respond(['message' => "Erreur , id introuvable"], 200);
            }
            return $this->respond($material, 200);
        }
    }

    public function getmateriel($id = null)
    {
        $data = $this->request->getGet();
        if (empty($data)) {
            return $this->respond(['message' => "Erreur , pas de données reçus"], 500);
        }
        if (!isset($data['id'])) {
            return $this->respond(['message' => "Erreur , il faut un id"], 500);
        }
        $mm = Model('MaterialModel');
        $material = $mm->find($data['id']);
        if (!$material) {
            return $this->respond(['message' => "Erreur , id introuvable"], 200);
        }
        return $this->respond($material, 200);
    }


    public function gettoken($userId = null) {
        $data = $this->request->getGet();

        if (!isset($data['force_regenerate'])) {
            $data['force_regenerate'] = false;
        }


        if ($userId !== null) {
            $um = Model('UserModel');
            $user = $um->find($userId);
        }
        // Sinon, utiliser mail + password pour vérifier l'utilisateur
        else if (isset($data['email']) && isset($data['password'])) {
            $um = Model('UserModel');
            $user = $um->verifyLogin($data['email'], $data['password']);
        } else {
            return $this->respond(["message" => "Erreur, identifiant ou mot de passe manquant"], 400);
        }

        // Vérifier que l'utilisateur existe
        if (!isset($user['id'])) {
            return $this->respond(["message" => "Erreur, identifiant ou mot de passe incorrect"], 401);
        }

        // Vérifier si l'utilisateur est blacklisté
        $bm = Model('BlacklistModel');
        $isBlacklisted = $bm->where('id_user', $user['id'])->first();
        if ($isBlacklisted) {
            return $this->respond(["message" => "Utilisateur bloqué, impossible de générer un jeton"], 403);
        }

        // Supprimer l'ancien token s'il existe
        $atm = Model('ApiTokenModel');
        $atm->where('id_user', $user['id'])->delete();

        // Générer un nouveau token
        $token = generateToken($user['id'], $data['force_regenerate']);

        return $this->respond(['token' => $token], 200);
    }


    public function getindex()
    {
        return $this->respond(['message' => "Test index"], 200);
    }

    public
    function gettest()
    {
        return $this->respond(['message' => "Test GET"], 200);
    }

    public
    function puttest()
    {
        return $this->respond(['message' => "Test PUT"], 200);
    }

    public
    function deletetest()
    {
        return $this->respond(['message' => "Test DELETE"], 200);
    }


    public function postlogin()
    {
        $userModel = new UserModel();
        $blacklistModel = new BlacklistModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Récupération de l'utilisateur
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON(['message' => 'Identifiant ou mot de passe incorrect'])->setStatusCode(401);
        }

        // Vérifier si l'utilisateur est en blacklist
        $isBlacklisted = $blacklistModel->where('id_user', $user['id'])->first();
        if ($isBlacklisted) {
            return $this->response->setJSON(['message' => 'Compte bloqué'])->setStatusCode(403);
        }

        // Vérification du mot de passe
        if (!password_verify($password, $user['password'])) {

            $userModel->decrementCounterUser($user['id']);
            // Recharger l'utilisateur après mise à jour du compteur
            $user = $userModel->find($user['id']); // Recharger les données après mise à jour

            // Vérification si le compteur atteint 0
            if ($user['counter_user'] <= 0) {
                // Ajouter l'utilisateur à la table blacklist avec created_at
                $blacklistModel->addToBlacklist($user['id']);

                return $this->response->setJSON(['message' => 'Votre compte est bloqué'])->setStatusCode(403);
            }

            return $this->response->setJSON(['message' => 'Identifiant ou mot de passe incorrect'])->setStatusCode(401);
        }

        // Réinitialisation du compteur après une connexion réussie
        $userModel->resetCounter($email);

        // Vérifier si un token existe déjà pour cet utilisateur
        $atm = Model('ApiTokenModel');
        $apiToken = $atm->where('id_user', $user['id'])->first();

        if ($apiToken) {
            // Token déjà existant, le renvoyer
            return $this->response->setJSON(['token' => $apiToken['token']]);
        } else {
            // Si aucun token n'existe, utiliser la fonction gettoken pour le générer
            return $this->gettoken($user['id']);
        }
    }
}

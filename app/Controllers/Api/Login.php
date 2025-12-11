<?php

namespace App\Controllers\Api;

use App\Models\ApiTokenModel;
use App\Models\BlacklistModel;
use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class Login extends ResourceController
{
    public function postsetAllRequestLimits()
    {
        $input = $this->request->getJSON();
        $limit = $input->limit ?? null;

        if ($limit !== null) {
            $tournamentOptionsModel = Model('MaterialOptionsModel');
            $apiTokenModel = new \App\Models\ApiTokenModel();

            $result = $tournamentOptionsModel->updateOrInsertLimit('api_request_limit', $limit);

            if ($result) {
                // Mise à jour du 'counter' dans api_token
                $newCounter = ($limit === 'infinite') ? 10000 : (int)$limit;
                $apiTokenModel->updateAllCounters($newCounter);

                return $this->response->setJSON(['success' => true, 'message' => 'Limite et compteurs mis à jour.']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
            }
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Paramètre manquant.']);
    }
    public function postsetRequestLimit()
    {
        $input = $this->request->getJSON();
        $limit = $input->limit ?? null;
        $id = $input->id ?? null;

        if ($limit !== null && $id !== null) {
            $apiTokenModel = new \App\Models\ApiTokenModel();

            // Mise à jour de la limite du token
            $updateSuccess = $apiTokenModel->updateLimit($id, $limit);

            if ($updateSuccess) {
                return $this->response->setJSON(['success' => true]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Erreur lors de la mise à jour de la limite']);
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Paramètres manquants']);
    }

    public function postregister()
    {
        $userModel = new UserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $job = $this->request->getPost('id_job');
        $firstname = $this->request->getPost('firstname');
        $lastname = $this->request->getPost('lastname');

        // Vérifier si l'email existe déjà
        if ($userModel->where('email', $email)->first()) {
            return $this->response->setJSON(['message' => 'Cet email est déjà utilisé'])->setStatusCode(400);
        }

        // Hash du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $data = [
            'email' => $email,
            'password' => $hashedPassword,
            'id_job' => $job,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'id_permission' => 3,
            'counter_user' => 3
        ];

        if (!$userModel->insert($data)) {
            return $this->response->setJSON(['errors' => $userModel->errors()])->setStatusCode(400);
        }

        // Récupérer l'utilisateur nouvellement inscrit
        $user = $userModel->where('email', $email)->first();

        // Générer un token pour l'utilisateur
        return $this->gettoken($user['id']);
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
            $user = $userModel->find($user['id']);
            if ($user['counter_user'] <= 0) {
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
            // Token déjà existant, vérifier s'il est expiré et régénérer si nécessaire
            if ($apiToken['expires_at'] < date('Y-m-d H:i:s')) {
                // Token expiré, le régénérer
                $this->gettoken($user['id']);
                $apiToken = $atm->where('id_user', $user['id'])->first(); // Récupérer le nouveau token
            }

            // Renvoyer le token avec les infos utilisateur
            $response = [
                'token' => $apiToken['token'],
                'id_user' => $user['id'],
                'email' => $user['email'],
                'id_permission' => (int)$user['id_permission'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname']
            ];
            return $this->response->setJSON($response);
        } else {
            // Si aucun token n'existe, utiliser la fonction gettoken pour le générer et le renvoyer avec les infos utilisateur
            $tokenResponse = $this->gettoken($user['id']);
            $tokenData = json_decode($tokenResponse->getBody(), true);

            if (isset($tokenData['token'])) {
                $response = [
                    'token' => $tokenData['token'],
                    'id_user' => $user['id'],
                    'email' => $user['email'],
                    'id_permission' => (int)$user['id_permission'],
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname']
                ];
                return $this->response->setJSON($response);
            } else {
                return $tokenResponse; // Renvoyer l'erreur de gettoken si nécessaire
            }
        }
    }

    public function gettoken($userId = null)
    {
        $data = $this->request->getGet(); // Récupère les paramètres GET de l'URL

        // Récupérer userId depuis GET si non passé en paramètre direct
        if ($userId === null && isset($data['userId'])) {
            $userId = (int) $data['userId'];
        }

        // Par défaut, pas de régénération forcée
        $forceRegen = isset($data['force_regenerate']) ? filter_var($data['force_regenerate'], FILTER_VALIDATE_BOOLEAN) : false;

        $user = null;

        // Si userId est fourni, on tente de récupérer l'utilisateur par son ID
        if ($userId !== null) {
            $um = Model('UserModel');
            $user = $um->find($userId);
        } else if (isset($data['email']) && isset($data['password'])) {
            $um = Model('UserModel');
            $user = $um->verifyLogin($data['email'], $data['password']);
        } else {
            return $this->respond(["message" => "Erreur, identifiant ou mot de passe manquant"], 400);
        }

        if (!isset($user['id'])) {
            return $this->respond(["message" => "Erreur, identifiant ou mot de passe incorrect"], 401);
        }

        // Vérifie si l'utilisateur est blacklisté
        $bm = Model('BlacklistModel');
        if ($bm->where('id_user', $user['id'])->first()) {
            return $this->respond(["message" => "Utilisateur bloqué, impossible de générer un jeton"], 403);
        }

        // Supprimer l'ancien token si présent
        $atm = Model('ApiTokenModel');
        $atm->where('id_user', $user['id'])->delete();

        // Générer un nouveau token
        $token = generateToken($user['id'], $forceRegen);

        return $this->respond(['token' => $token], 200);
    }
}
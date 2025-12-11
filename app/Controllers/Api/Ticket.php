<?php

namespace App\Controllers\Api;

use App\Models\TicketModel;
use App\Models\UserModel;
use App\Models\BlacklistModel;
use CodeIgniter\RESTful\ResourceController;

class Ticket extends ResourceController
{
    public function getAllTickets() {
        $token = $this->request->getHeaderLine('Authorization');

        if ($token && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $userId = validateToken($matches[1]);

            if (!$userId) {
                return $this->respond(['error' => 'Token invalide'], 401);
            }

            $userModel = Model('UserModel');
            $user = $userModel->find($userId);

            if (!$user) {
                return $this->respond(['error' => 'Utilisateur introuvable'], 404);
            }

            $blacklistModel = Model('BlacklistModel');
            if ($blacklistModel->where('id_user', $userId)->first()) {
                return $this->respond(['error' => 'Utilisateur bloqué'], 403);
            }

            $tokenModel = Model('ApiTokenModel');
            $apiToken = $tokenModel->where('id_user', $userId)->first();

            if (!$apiToken || $apiToken['counter'] <= 0) {
                return $this->respond(['error' => 'Limite atteinte'], 403);
            }

            // Récupération des tickets
            $ticketModel = Model('TicketModel');
            $tickets = $ticketModel->findAll();

            return $this->respond(['tickets' => $tickets], 200);
        }

        return $this->respond(['error' => 'Token manquant ou invalide'], 401);
    }

    public function getTicket($id = null)
    {
        if (empty($id)) {
            return $this->respond(['message' => "ID requis"], 400);
        }

        $ticketModel = model('TicketModel');
        $ticket = $ticketModel->find($id);

        if (!$ticket) {
            return $this->respond(['message' => "Ticket introuvable"], 404);
        }

        return $this->respond($ticket, 200);
    }

    public function create()
    {
        $token = $this->request->getHeaderLine('Authorization');

        if ($token && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $userId = validateToken($matches[1]);

            if (!$userId) {
                return $this->respond(['error' => 'Token invalide'], 401);
            }

            $ticketModel = new TicketModel();

            $data = $this->request->getJSON(true);

            if (
                empty($data['id_ticketcategory']) ||
                empty($data['title']) ||
                empty($data['description']) ||
                empty($data['id_priority'])
            ) {
                return $this->respond(['error' => 'Tous les champs sont requis'], 400);
            }

            $newTicket = [
                'id_user'          => $userId,
                'id_ticketcategory'=> $data['id_ticketcategory'],
                'title'            => $data['title'],
                'description'      => $data['description'],
                'id_priority'      => $data['id_priority']
            ];

            $ticketModel->insert($newTicket);

            return $this->respond(['message' => 'Ticket créé avec succès'], 201, 'application/json');
        }

        return $this->respond(['error' => 'Token manquant ou invalide'], 401);
    }

    public function getAllTicketCategories()
    {
        $token = $this->request->getHeaderLine('Authorization');
        if ($token && preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $userId = validateToken($matches[1]);

            if (!$userId) return $this->respond(['error' => 'Token invalide'], 401);

            $blacklistModel = model('BlacklistModel');
            if ($blacklistModel->where('id_user', $userId)->first()) {
                return $this->respond(['error' => 'Utilisateur bloqué'], 403);
            }

            $model = model('TicketCategoryModel');
            $categories = $model->findAll();

            return $this->respond(['categories' => $categories], 200);
        }

        return $this->respond(['error' => 'Token manquant ou invalide'], 401);
    }

    public function getTicketCategory($id = null)
    {
        if (empty($id)) {
            return $this->respond(['message' => "ID requis"], 400);
        }

        $model = model('TicketCategoryModel');
        $category = $model->find($id);

        if (!$category) {
            return $this->respond(['message' => "Catégorie introuvable"], 404);
        }

        return $this->respond($category, 200);
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
            // Token déjà existant, le renvoyer avec les infos utilisateur
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
}

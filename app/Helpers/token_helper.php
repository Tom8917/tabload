<?php

use App\Models\ApiTokenModel;
use App\Models\UserModel;
use App\Models\BlacklistModel;
use CodeIgniter\I18n\Time;

function generateToken($userId, $force_regen = false, $never_expired = false)
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = $never_expired ? null : Time::now()->addHours(24);

    $apiTokenModel = new ApiTokenModel();
    $userModel = new UserModel();
    $blacklistModel = new BlacklistModel();

    $tokenData = $apiTokenModel->where('id_user', $userId)->first();
    $isBlacklisted = $blacklistModel->where('id_user', $userId)->first();

    if ($isBlacklisted) {
        return null;
    }

    if ($tokenData && ($tokenData['expires_at'] < Time::now() || $force_regen)) {
        $apiTokenModel->update($tokenData['id'], [
            'token' => $token,
            'created_at' => Time::now(),
            'expires_at' => $expiresAt,
        ]);

        $tokenId = $tokenData['id']; // On conserve l’ID existant
    } else if (!$tokenData) {
        $apiTokenModel->insert([
            'id_user' => $userId,
            'token' => $token,
            'created_at' => Time::now(),
            'expires_at' => $expiresAt,
        ]);
        $tokenId = $apiTokenModel->getInsertID(); // Récupération de l’ID nouvellement inséré
    } else {
        // Token encore valide, on ne le régénère pas
        return $tokenData['token'];
    }

    // Mettre à jour le champ id_api_tokens dans la table user
    $userModel->update($userId, ['id_api_tokens' => $tokenId]);

    return $token;
}

function validateToken($token)
{
    $apiTokenModel = new ApiTokenModel();

    $tokenData = $apiTokenModel->where('token', $token)->first();

    if (!$tokenData) {
        return null; // Token introuvable
    }

    // Vérifie expiration
    if ($tokenData['expires_at'] !== null && $tokenData['expires_at'] <= Time::now()) {
        return null; // Token expiré
    }

    // Vérifie quota
    if ($tokenData['counter'] <= 0) {
        return null; // Plus de quota, mais on ne supprime rien
    }

    // Token valide : décrémenter le compteur et autoriser
    $apiTokenModel->decrementCounter($tokenData['id_user']);
    return $tokenData['id_user'];
}

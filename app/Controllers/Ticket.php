<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\TeamTicketCategoryModel;
use App\Models\UserModel;
use App\Models\TicketCategoryModel;
use App\Models\PriorityModel;
use App\Models\StatusModel;
use App\Models\TeamModel;

class Ticket extends BaseController
{
    protected $require_auth = true;

    public function getindex($id = null)
    {
        // Chargement des modèles
        $ticketModel = new TicketModel();
        $categoryModel = new TicketCategoryModel();
        $priorityModel = new PriorityModel();
        $statusModel = new StatusModel();
        $teamModel = new TeamModel();

        // Récupérer l'utilisateur depuis la session
        $user = session()->get('user');
        if (!$user) {
            session()->setFlashdata('error', "Utilisateur non connecté.");
            return redirect()->to('/login');
        }

        $categories = $categoryModel->findAll();  // Récupérer toutes les catégories
        $statuses = $statusModel->findAll();
        $priorities = $priorityModel->findAll();

        // Si un ID est passé, on affiche la page d'édition
        if ($id !== null) {
            if ($id === "new") {
                // Afficher le formulaire pour créer un ticket
                return $this->view("/front/ticket/ticket", compact('categories', 'statuses', 'priorities'), true);
            }

            // Vérification et affichage de l'édition du ticket si l'ID existe
            $ticket = $ticketModel->find($id);
            if (!$ticket || $ticket['id_user'] != $user->id) {
                session()->setFlashdata('error', "Ticket introuvable ou accès non autorisé.");
                return redirect()->to('/ticket');
            }

            // Renvoyer la vue avec les informations du ticket à éditer
            return $this->view("/front/ticket/ticket", [
                'ticket' => $ticket,
                'categories' => $categories,
                'statuses' => $statuses,
                'priorities' => $priorities
            ], true);
        }

        // Affichage de tous les tickets de l'utilisateur
        $tickets = $ticketModel->getTicketsWithUsers();
        $tickets = array_filter($tickets, function($ticket) use ($user) {
            return $ticket['id_user'] == $user->id;
        });

        return $this->view("/front/ticket/index", compact('tickets', 'categories', 'statuses', 'priorities'), true);
    }

    public function postcreate()
    {
        $ticketModel = new TicketModel();
        $categoryModel = new TicketCategoryModel(); // Initialisation du modèle TicketCategoryModel
        $teamTicketCategoryModel = new TeamTicketCategoryModel(); // Modèle pour la table de liaison

        // Récupérer l'utilisateur depuis la session
        $user = session()->get('user');
        if (!$user) {
            session()->setFlashdata('error', "Utilisateur non connecté.");
            return redirect()->to('/login');
        }

        $data = $this->request->getPost();
        $data['id_user'] = $user->id;

        // Validation des champs requis
        if (!$this->validate([
            'title' => 'required|min_length[3]|max_length[100]',
            'description' => 'required|min_length[10]',
            'id_ticketcategory' => 'required'
        ])) {
            session()->setFlashdata('error', "Tous les champs obligatoires doivent être remplis.");
            return redirect()->back()->withInput();
        }

        // Vérifier si l'ID de la catégorie existe
        $categoryId = $data['id_ticketcategory'];
        $category = $categoryModel->find($categoryId);
        if (!$category) {
            session()->setFlashdata('error', "Catégorie de ticket introuvable.");
            return redirect()->back();
        }

        // Lier automatiquement l'équipe à partir de la catégorie
        $teamUserModel = new TeamModel();  // Ou TeamUserModel si tu l'as
        $team = $teamUserModel->getTeamByCategory($categoryId);

        if (!$team) {
            session()->setFlashdata('error', "Aucune équipe associée à cette catégorie.");
            return redirect()->back();
        }

        $data['id_team'] = $team['id_team']; // Associer l'équipe au ticket

        // Créer le ticket
        if ($ticketModel->createTicket($data)) {
            session()->setFlashdata('success', "Ticket créé avec succès !");
            return redirect()->to(base_url('/ticket'));
        }

        session()->setFlashdata('error', "Erreur lors de la création.");
        return redirect()->to(base_url('/ticket'));
    }

    // Fonction pour obtenir l'ID de l'équipe associée à la catégorie
    private function getTeamIdByCategory($categoryId)
    {
        $teamTicketCategoryModel = new TeamTicketCategoryModel();
        $team = $teamTicketCategoryModel
            ->where('id_ticketcategory', $categoryId)
            ->first();

        return $team ? $team['id_team'] : null;
    }

    public function postupdate($id)
    {
        $ticketModel = new TicketModel();
        $categoryModel = new TicketCategoryModel();
        $teamModel = new TeamModel();

        // Trouver le ticket avec l'ID
        $ticket = $ticketModel->find($id);
        if (!$ticket) {
            session()->setFlashdata('error', "Ticket introuvable.");
            return redirect()->to('/ticket');
        }

        // Récupérer les données du formulaire
        $data = $this->request->getPost();

        // Validation des champs
        if (!$this->validate([
            'title' => 'required|min_length[3]|max_length[100]',
            'description' => 'required|min_length[10]',
            'id_ticketcategory' => 'required'
        ])) {
            session()->setFlashdata('error', "Tous les champs obligatoires doivent être remplis.");
            return redirect()->back()->withInput();
        }

        // Vérifier si la catégorie existe avant de mettre à jour
        $categoryId = $data['id_ticketcategory'];
        $category = $categoryModel->find($categoryId);
        if (!$category) {
            session()->setFlashdata('error', "Catégorie de ticket introuvable.");
            return redirect()->back();
        }

        $team = $teamModel->getTeamByCategory($categoryId);
        $data['id_team'] = $team ? $team['id_team'] : null;

        // Mise à jour du ticket
        if ($ticketModel->updateTicket($id, $data)) {
            session()->setFlashdata('success', "Ticket mis à jour avec succès !");
            return redirect()->to(base_url("/ticket"));
        } else {
            session()->setFlashdata('error', "Erreur lors de la mise à jour.");
            return redirect()->to(base_url("/ticket/{$id}"));
        }
    }

    public function getdelete($id)
    {
        $ticketModel = new TicketModel();

        if ($ticketModel->deleteTicket($id)) {
            session()->setFlashdata('success', "Ticket supprimé.");
        } else {
            session()->setFlashdata('error', "Erreur lors de la suppression.");
        }

        return redirect()->to(base_url('/ticket'));
    }
}

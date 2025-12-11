<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SubtaskModel;
use App\Models\TicketModel;
use App\Models\TeamUserModel;

class Subtask extends BaseController
{
    public function postCreate()
    {
        $data = $this->request->getPost();
        $userId = session()->get('user')->id ?? null;

        $ticketModel = new \App\Models\TicketModel();
        $ticket = $ticketModel->find($data['id_ticket']);

        if (!$ticket) {
            return redirect()->back()->with('error', 'Ticket introuvable.');
        }

        if (!$this->userHasAccessToTeamCategory($userId, $ticket['id_team'], $ticket['id_ticketcategory'])) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à ajouter une sous-tâche.');
        }

        $subtaskModel = new \App\Models\SubtaskModel();
        $inserted = $subtaskModel->insert([
            'title' => $data['title'],
            'description' => $data['description'],
            'id_ticket' => $data['id_ticket'],
            'id_user' => $data['id_user'],
            'status' => 'A faire',
        ]);

        if (!$inserted) {
            return redirect()->back()->with('error', 'Échec de l\'ajout de la sous-tâche.');
        }

        $ticketModel->updateGlobalStatusFromSubtasks($ticket['id']);

        return redirect()->back()->with('success', 'Sous-tâche ajoutée avec succès.');
    }

    public function postUpdate($id)
    {
        $userId = session()->get('user')->id ?? null;
        $subtaskModel = new SubtaskModel();
        $subtask = $subtaskModel->find($id);

        if (!$subtask) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Sous-tâche introuvable.']);
        }

        if ($subtask['id_user'] != $userId && !$this->userIsAdmin($userId)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Non autorisé.']);
        }

        $status = $this->request->getPost('status');
        $subtaskModel->update($id, ['status' => $status]);

        $ticketModel = new TicketModel();
        $ticketModel->updateGlobalStatusFromSubtasks($subtask['id_ticket']);

        return $this->response->setJSON(['success' => true]);
    }

    public function getdelete($id)
    {
        $subtaskModel = new \App\Models\SubtaskModel();
        $subtask = $subtaskModel->find($id);

        if (!$subtask) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Sous-tâche introuvable']);
        }

        if (!$subtaskModel->delete($id)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Impossible de supprimer la sous-tâche']);
        }

        // Mise à jour du ticket après suppression
        $ticketModel = new \App\Models\TicketModel();
        $ticketModel->updateGlobalStatusFromSubtasks($subtask['id_ticket']);

        return $this->response->setJSON(['success' => true]);
    }


    private function userIsAdmin($userId)
    {
        $user = model('UserModel')->find($userId);
        return isset($user['id_permission']) && $user['id_permission'] == 1;
    }

    private function userHasAccessToTeamCategory($userId, $teamId, $categoryId)
    {
        return model('TeamUserModel')
                ->where('id_user', $userId)
                ->where('id_team', $teamId)
                ->where('id_ticketcategory', $categoryId)
                ->countAllResults() > 0;
    }
}

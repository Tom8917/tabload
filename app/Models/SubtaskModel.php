<?php

namespace App\Models;

use CodeIgniter\Model;

class SubtaskModel extends Model
{
    protected $table = 'subtask';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_ticket', 'title', 'description', 'status', 'id_user'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    public function getSubtasksByTicket($ticketId)
    {
        return $this->where('id_ticket', $ticketId)->findAll();
    }

    public function getAssignedSubtasks($userId)
    {
        return $this->where('id_user', $userId)->findAll();
    }

    public function deleteSubtask($id)
    {
        return $this->delete($id);
    }

    public function userHasAccessToTeamCategory($userId, $teamId, $categoryId)
    {
        return $this->where([
                'id_user' => $userId,
                'id_team' => $teamId,
                'id_ticketcategory' => $categoryId
            ])->countAllResults() > 0;
    }

}

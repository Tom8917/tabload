<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Task extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title' => 'Tâche 1',
                'description' => 'test',
                'limit_time' => '2026-01-01',
                'Status' => 'À faire',
            ],
            [
                'title' => 'Tâche 2',
                'description' => 'test',
                'limit_time' => '2026-01-01',
                'Status' => 'À faire',
            ],
            [
                'title' => 'Tâche 3',
                'description' => 'test',
                'limit_time' => null,
                'Status' => 'À faire',
            ],
            [
                'title' => 'Tâche 4',
                'description' => 'test',
                'limit_time' => null,
                'Status' => 'À faire',
            ],
            [
                'title' => 'Tâche 5',
                'description' => 'test',
                'limit_time' => null,
                'Status' => 'À faire',
            ],
        ];
        $this->db->table('task')->insertBatch($data);
    }
}
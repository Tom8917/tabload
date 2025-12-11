<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run()
    {
        $this->call('Task');
        $this->call('Team');
        $this->call('Team_User');
        $this->call('Ticket');
    }
}
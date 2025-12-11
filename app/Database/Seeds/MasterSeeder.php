<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run()
    {
        $this->call('Center');
        $this->call('Task');
        $this->call('Team');
        $this->call('Team_User');
        $this->call('Material');
        $this->call('MaterialMedical');
        $this->call('Ticket');
        $this->call('StockTypes');
    }
}
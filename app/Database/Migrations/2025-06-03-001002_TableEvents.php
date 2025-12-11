<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class TableEvents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'title'      => ['type'=>'VARCHAR','constraint'=>180],
            'course_id'  => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'type'       => ['type'=>'VARCHAR','constraint'=>40,'null'=>true],
            'starts_at'  => ['type'=>'DATETIME'],
            'ends_at'    => ['type'=>'DATETIME','null'=>true],
            'all_day'    => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'location'   => ['type'=>'VARCHAR','constraint'=>180,'null'=>true],
            'notes'      => ['type'=>'TEXT','null'=>true],
            'color'      => ['type'=>'VARCHAR','constraint'=>20,'null'=>true],
            'created_at' => ['type'=>'DATETIME','null'=>true],
            'updated_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('starts_at');
        $this->forge->createTable('events');
    }
    public function down() { $this->forge->dropTable('events'); }
}

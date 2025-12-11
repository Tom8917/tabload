<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Material extends Entity
{

    // Définir les propriétés accessibles
    protected $attributes = [
        'id' => null,
        'id_center' => null,
        'id_materialtype' => null,
        'id_materialbrand' => null,
        'id_material_operational_system' => null,
        'id_material_vulnerability' => null,
        'reference' => null,
        'nserie' => null,
        'id_user' => null,
        'id_job' => null,
        'start_warranty' => null,
        'end_warranty' => null,
        'id_warranty' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    // Cast des types
    protected $casts = [
        'id' => 'integer',
        'id_center' => 'integer',
        'id_materialtype' => 'integer',
        'id_materialbrand' => 'integer',
        'id_material_operational_system' => 'integer',
        'id_material_vulnerability' => 'integer',
        'id_user' => 'integer',
        'id_job' => 'integer',
        'id_warranty' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getJobs(): string
    {
        return $this->getJobType();
    }
}
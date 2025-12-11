<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table = 'job';
    protected $primaryKey = 'id';

    // Champs permis pour les opérations d'insertion et de mise à jour
    protected $allowedFields = ['type','diminutif','slug'];

    // Validation
    protected $validationRules = [
        'type' => 'required|min_length[3]|max_length[100]',
    ];

    protected $validationMessages = [
        'type' => [
            'required'   => 'Le nom du métier est requis.',
            'min_length' => 'Le nom du métier doit comporter au moins 3 caractères.',
            'max_length' => 'Le nom du métier ne doit pas dépasser 100 caractères.',
        ],
    ];


    public function createJob($data)
    {
        if (isset($data['type'])) {
            // Générer et vérifier le slug unique
            $data['slug'] = $this->generateUniqueSlug($data['type']);
        }

        return $this->insert($data);
    }

    public function updateJob($id, $data)
    {
        if (isset($data['type'])) {
            // Générer et vérifier le slug unique
            $data['slug'] = $this->generateUniqueSlug($data['type']);
        }

        return $this->update($id, $data);
    }

    private function generateUniqueSlug($type)
    {
        $slug = generateSlug($type); // Utilisez la fonction du helper pour générer le slug de base
        $builder = $this->builder();

        // Vérifiez si le slug existe déjà
        $count = $builder->where('slug', $slug)->countAllResults();

        if ($count === 0) {
            return $slug;
        }

        // Si le slug existe, ajoutez un suffixe numérique pour le rendre unique
        $i = 1;
        while ($count > 0) {
            $newSlug = $slug . '-' . $i;
            $count = $builder->where('slug', $newSlug)->countAllResults();
            $i++;
        }

        return $newSlug;
    }

    public function getUsersByJob($jobId)
    {
        return $this->join('TableUser', 'TableJob.id = TableUser.id_job')
            ->where('TableUserJob.id', $jobId)
            ->select('TableUser.*, TableJob.type as job_type')
            ->select('TableUser.*, TableJob.diminutif as job_diminutif')
            ->findAll();
    }

    public function getAllJobs()
    {
        return $this->findAll(); // Récupère tous les jobs de la table 'job'
    }

    public function getJobById($id)
    {
        return $this->find($id);
    }

    public function deleteJob($id)
    {
        return $this->delete($id);
    }

    public function getPaginatedJob($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder();
        // Recherche
        if ($searchValue != null) {
            $builder->like('type', $searchValue);
        }

        // Tri
        if ($orderColumnName && $orderDirection) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }

        $builder->limit($length, $start);

        return $builder->get()->getResultArray();
    }

    public function getTotalJob()
    {
        $builder = $this->builder();
        return $builder->countAllResults();
    }

    public function getFilteredJob($searchValue)
    {
        $builder = $this->builder();
        // @phpstan-ignore-next-line
        if (!empty($searchValue)) {
            $builder->like('type', $searchValue);
        }

        return $builder->countAllResults();
    }
}
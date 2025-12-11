<?php
namespace App\Models;

use CodeIgniter\Model;

class PageModel extends Model
{
    protected $table      = 'pages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'title','slug','description','content','image',
        'created_at','updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'title'       => 'required|min_length[3]|max_length[150]',
        'description' => 'permit_empty',
        'content'     => 'permit_empty',
        'image'       => 'permit_empty|max_length[255]',
    ];

    public function makeSlug(string $title): string
    {
        helper(['text','url']);
        $base = url_title(convert_accented_characters($title), '-', true);
        if ($base === '') $base = uniqid('page-');
        $slug = $base; $i=1;

        // 👇 Vérifie en DB que le slug n’existe pas déjà
        while ($this->where('slug',$slug)->first()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}

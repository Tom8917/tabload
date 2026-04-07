<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaBlobModel extends Model
{
    protected $table      = 'media_blob';
    protected $primaryKey = 'media_id';
    protected $returnType = 'array';

    protected $allowedFields = ['media_id', 'data'];

    protected $protectFields = true;

    public function upsertBlob(int $mediaId, string $data): bool
    {
        $builder = $this->db->table($this->table);

        $exists = $builder->select('media_id')
            ->where('media_id', $mediaId)
            ->get()
            ->getRowArray();

        if ($exists) {
            $ok = $builder->where('media_id', $mediaId)->update([
                'data' => $data,
            ]);
        } else {
            $ok = $builder->insert([
                'media_id' => $mediaId,
                'data'     => $data,
            ]);
        }

        if (!$ok) {
            $err = $this->db->error();
            log_message('error', 'MediaBlobModel upsertBlob FAILED media_id=' . $mediaId . ' err=' . json_encode($err));
        }

        return (bool) $ok;
    }

    public function getBlob(int $mediaId): ?string
    {
        $row = $this->db->table($this->table)
            ->select('data')
            ->where('media_id', $mediaId)
            ->get()
            ->getRowArray();

        if (!$row) return null;

        $b = $row['data'] ?? null;
        return is_string($b) ? $b : null;
    }
}
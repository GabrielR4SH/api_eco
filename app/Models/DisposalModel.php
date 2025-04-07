<?php

namespace App\Models;

use CodeIgniter\Model;

class DisposalModel extends Model
{
    protected $table = 'disposals';
    protected $primaryKey = 'id';
    
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'user_id', 'point_id', 'material', 'weight_kg', 'points_earned'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;
    
    protected $validationRules = [
        'user_id' => 'required|integer',
        'point_id' => 'required|integer',
        'material' => 'required',
        'weight_kg' => 'required|numeric|greater_than[0]'
    ];
}
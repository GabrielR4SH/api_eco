<?php

namespace App\Models;

use CodeIgniter\Model;

class CollectionModel extends Model
{
    protected $table = 'collections';
    protected $primaryKey = 'id';
    
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'point_id', 'material', 'weight_kg', 
        'destination', 'co2_saved', 'notes'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;
    
    protected $validationRules = [
        'point_id' => 'required|integer',
        'material' => 'required',
        'weight_kg' => 'required|numeric',
        'destination' => 'required'
    ];
}
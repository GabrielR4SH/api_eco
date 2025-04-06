<?php

namespace App\Models;

use CodeIgniter\Model;

class PartnerModel extends Model
{
    protected $table = 'partners';
    protected $primaryKey = 'id';
    
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'name', 'email', 'password', 'api_key', 'active'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'email' => 'required|valid_email|is_unique[partners.email,id,{id}]',
        'password' => 'required|min_length[8]'
    ];
}
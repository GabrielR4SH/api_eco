<?php

namespace App\Models;

use CodeIgniter\Model;

class CollectionPointModel extends Model
{
    protected $table = 'collection_points';
    protected $primaryKey = 'id';
    
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'name', 'latitude', 'longitude', 'materials',
        'partner_id', 'address', 'city', 'state', 'zipcode',
        'operation_hours'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Encontra pontos de coleta próximos a uma coordenada geográfica
     */
    public function findNearby($latitude, $longitude, $radius = 5, $material = null)
    {
        // Fórmula de Haversine para buscar pontos dentro do raio (em km)
        $earthRadius = 6371; // Raio da Terra em km
        
        $haversine = "($earthRadius * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude))))";
        
        $builder = $this->builder();
        $builder->select("*, $haversine AS distance", false);
        
        // Filtrar por material, se especificado
        if ($material) {
            $builder->where("JSON_CONTAINS(materials, '\"$material\"')");
        }
        
        $builder->having('distance <=', $radius);
        $builder->orderBy('distance', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}
<?php

namespace App\Controllers;

use App\Models\CollectionModel;
use App\Models\CollectionPointModel;
use App\Models\PartnerModel;
use CodeIgniter\API\ResponseTrait;

class ReportController extends BaseController
{
    use ResponseTrait;

    protected $user;

    public function __construct()
    {
        // Obtém o usuário do request (adicionado pelo JwtFilter)
        $this->user = $this->request->user ?? null;
    }

    /**
     * Gera relatório de impacto ambiental
     */
    public function environmentalImpact()
    {
        $collectionModel = new CollectionModel();
        
        // Filtros
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01'); // Primeiro dia do mês atual
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t'); // Último dia do mês atual
        $partnerId = $this->request->getGet('partner_id');
        $material = $this->request->getGet('material');
        
        $builder = $collectionModel->builder();
        $builder->select('material, SUM(weight_kg) as total_weight, SUM(co2_saved) as total_co2_saved');
        
        // Aplicar filtros de data
        $builder->where('created_at >=', $startDate . ' 00:00:00');
        $builder->where('created_at <=', $endDate . ' 23:59:59');
        
        // Se for parceiro ou se um parceiro específico foi solicitado
        if ($this->user->type === 'partner' || $partnerId) {
            $id = $partnerId ?? $this->user->id;
            
            $pointModel = new CollectionPointModel();
            $points = $pointModel->where('partner_id', $id)->findAll();
            $pointIds = array_column($points, 'id');
            
            if (!empty($pointIds)) {
                $builder->whereIn('point_id', $pointIds);
            } else {
                return $this->respond([
                    'materials' => [],
                    'total' => [
                        'weight_kg' => 0,
                        'co2_saved' => 0
                    ]
                ]);
            }
        }
        
        // Filtrar por material
        if ($material) {
            $builder->where('material', $material);
        }
        
        $builder->groupBy('material');
        $results = $builder->get()->getResultArray();
        
        // Calcular totais
        $totalWeight = 0;
        $totalCO2 = 0;
        
        foreach ($results as $result) {
            $totalWeight += $result['total_weight'];
            $totalCO2 += $result['total_co2_saved'];
        }
        
        return $this->respond([
            'materials' => $results,
            'total' => [
                'weight_kg' => $totalWeight,
                'co2_saved' => $totalCO2
            ],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
    
    /**
     * Gera relatório de atividade dos parceiros
     * Disponível apenas para admins
     */
    public function partnerActivity()
    {
        // Verificar se é admin
        if ($this->user->type !== 'admin') {
            return $this->failForbidden('Acesso negado. Apenas administradores podem acessar este relatório.');
        }
        
        $collectionModel = new CollectionModel();
        $partnerModel = new PartnerModel();
        
        // Filtros
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        // Buscar todos os parceiros
        $partners = $partnerModel->findAll();
        $result = [];
        
        foreach ($partners as $partner) {
            $pointModel = new CollectionPointModel();
            $points = $pointModel->where('partner_id', $partner['id'])->findAll();
            $pointIds = array_column($points, 'id');
            
            if (!empty($pointIds)) {
                $builder = $collectionModel->builder();
                $builder->select('COUNT(*) as collection_count, SUM(weight_kg) as total_weight, SUM(co2_saved) as total_co2_saved');
                $builder->whereIn('point_id', $pointIds);
                $builder->where('created_at >=', $startDate . ' 00:00:00');
                $builder->where('created_at <=', $endDate . ' 23:59:59');
                
                $stats = $builder->get()->getRowArray();
                
                $result[] = [
                    'partner_id' => $partner['id'],
                    'partner_name' => $partner['name'],
                    'point_count' => count($pointIds),
                    'collection_count' => (int)$stats['collection_count'],
                    'total_weight_kg' => (float)$stats['total_weight'],
                    'total_co2_saved' => (float)$stats['total_co2_saved']
                ];
            } else {
                $result[] = [
                    'partner_id' => $partner['id'],
                    'partner_name' => $partner['name'],
                    'point_count' => 0,
                    'collection_count' => 0,
                    'total_weight_kg' => 0,
                    'total_co2_saved' => 0
                ];
            }
        }
        
        // Ordenar por impacto ambiental (CO2 salvo)
        usort($result, function($a, $b) {
            return $b['total_co2_saved'] <=> $a['total_co2_saved'];
        });
        
        return $this->respond([
            'partners' => $result,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
}
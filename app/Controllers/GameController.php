<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DisposalModel;
use App\Models\PartnerModel;
use App\Models\CollectionPointModel;
use CodeIgniter\API\ResponseTrait;

class GameController extends BaseController
{
    use ResponseTrait;

    protected $user;

    public function __construct()
    {
        $this->user = $this->request->user ?? null;
    }

    /**
     * Retorna o ranking de usuários ou parceiros com base em pontos/impacto
     */
    public function leaderboard()
    {
        $type = $this->request->getGet('type') ?? 'users';
        $period = $this->request->getGet('period') ?? 'monthly';
        $limit = (int)($this->request->getGet('limit') ?? 10);
        
        if ($type === 'users') {
            return $this->userLeaderboard($period, $limit);
        } else {
            return $this->partnerLeaderboard($period, $limit);
        }
    }
    
    /**
     * Leaderboard de usuários baseado em pontos
     */
    private function userLeaderboard($period, $limit)
    {
        $userModel = new UserModel();
        $disposalModel = new DisposalModel();
        
        if ($period === 'all-time') {
            // Ranking de todos os tempos
            $users = $userModel->select('id, name, points')
                              ->orderBy('points', 'DESC')
                              ->limit($limit)
                              ->find();
                              
            return $this->respond([
                'period' => 'all-time',
                'users' => $users
            ]);
        } else {
            // Definir período
            $startDate = null;
            
            switch ($period) {
                case 'weekly':
                    $startDate = date('Y-m-d', strtotime('-1 week'));
                    break;
                case 'monthly':
                    $startDate = date('Y-m-d', strtotime('-1 month'));
                    break;
                case 'yearly':
                    $startDate = date('Y-m-d', strtotime('-1 year'));
                    break;
                default:
                    $startDate = date('Y-m-d', strtotime('-1 month'));
            }
            
            // Consulta agrupada por usuário com soma de pontos no período
            $builder = $disposalModel->builder();
            $builder->select('user_id, SUM(points_earned) as period_points');
            $builder->where('created_at >=', $startDate . ' 00:00:00');
            $builder->groupBy('user_id');
            $builder->orderBy('period_points', 'DESC');
            $builder->limit($limit);
            
            $results = $builder->get()->getResultArray();
            
            // Obter dados completos dos usuários
            $usersData = [];
            foreach ($results as $result) {
                $user = $userModel->select('id, name, points')
                                 ->find($result['user_id']);
                                 
                if ($user) {
                    $user['period_points'] = (int)$result['period_points'];
                    $usersData[] = $user;
                }
            }
            
            return $this->respond([
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => date('Y-m-d'),
                'users' => $usersData
            ]);
        }
    }
    
    /**
     * Leaderboard de parceiros baseado em impacto ambiental
     */
    private function partnerLeaderboard($period, $limit)
    {
        $collectionModel = new \App\Models\CollectionModel();
        $pointModel = new CollectionPointModel();
        $partnerModel = new PartnerModel();
        
        // Definir período
        $startDate = null;
        
        switch ($period) {
            case 'weekly':
                $startDate = date('Y-m-d', strtotime('-1 week'));
                break;
            case 'monthly':
                $startDate = date('Y-m-d', strtotime('-1 month'));
                break;
            case 'yearly':
                $startDate = date('Y-m-d', strtotime('-1 year'));
                break;
            case 'all-time':
                $startDate = '2000-01-01'; // Uma data bem antiga
                break;
            default:
                $startDate = date('Y-m-d', strtotime('-1 month'));
        }
        
        // Buscar todos os pontos agrupados por parceiro
        $partners = $partnerModel->findAll();
        $results = [];
        
        foreach ($partners as $partner) {
            $points = $pointModel->where('partner_id', $partner['id'])->findAll();
            $pointIds = array_column($points, 'id');
            
            if (!empty($pointIds)) {
                $builder = $collectionModel->builder();
                $builder->select('SUM(co2_saved) as co2_impact');
                $builder->whereIn('point_id', $pointIds);
                $builder->where('created_at >=', $startDate . ' 00:00:00');
                
                $impact = $builder->get()->getRowArray();
                
                $results[] = [
                    'id' => $partner['id'],
                    'name' => $partner['name'],
                    'co2_impact' => (float)($impact['co2_impact'] ?? 0)
                ];
            }
        }
        
        // Ordenar por impacto
        usort($results, function($a, $b) {
            return $b['co2_impact'] <=> $a['co2_impact'];
        });
        
        // Limitar resultados
        $results = array_slice($results, 0, $limit);
        
        return $this->respond([
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d'),
            'partners' => $results
        ]);
    }
}
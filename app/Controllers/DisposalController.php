<?php

namespace App\Controllers;

use App\Models\DisposalModel;
use App\Models\UserModel;
use App\Models\CollectionPointModel;
use CodeIgniter\API\ResponseTrait;

class DisposalController extends BaseController
{
    use ResponseTrait;

    protected $user;

    public function __construct()
    {
        $this->user = $this->request->user ?? null;
    }

    /**
     * Registra um descarte realizado por um usuário
     */
    public function register()
    {
        // Apenas usuários comuns podem registrar descartes
        if ($this->user->type !== 'user') {
            return $this->failForbidden('Apenas usuários podem registrar descartes');
        }
        
        $data = $this->request->getJSON(true);
        
        // Validação
        $rules = [
            'point_id' => 'required|integer',
            'material' => 'required',
            'weight_kg' => 'required|numeric|greater_than[0]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }
        
        // Verificar se o ponto existe
        $pointModel = new CollectionPointModel();
        $point = $pointModel->find($data['point_id']);
        
        if (!$point) {
            return $this->failNotFound('Ponto de coleta não encontrado');
        }
        
        // Adiciona o user_id do token
        $data['user_id'] = $this->user->id;
        
        // Calcular pontos baseado no material e peso
        $points = $this->calculatePoints($data['material'], $data['weight_kg']);
        $data['points_earned'] = $points;
        
        // Registrar o descarte
        $model = new DisposalModel();
        
        if ($model->insert($data)) {
            // Atualizar pontuação do usuário
            $userModel = new UserModel();
            $user = $userModel->find($this->user->id);
            
            $userModel->update($this->user->id, [
                'points' => ($user['points'] ?? 0) + $points
            ]);
            
            return $this->respondCreated([
                'message' => 'Descarte registrado com sucesso',
                'points_earned' => $points,
                'total_points' => ($user['points'] ?? 0) + $points
            ]);
        }
        
        return $this->fail($model->errors());
    }
    
    /**
     * Calcula pontos por tipo de material e peso
     */
    private function calculatePoints($material, $weight)
    {
        $pointsPerKg = [
            'plastico' => 5,
            'papel' => 3,
            'vidro' => 4,
            'metal' => 7,
            'eletronico' => 10,
            'organico' => 2,
            'oleo' => 8
        ];
        
        $material = strtolower($material);
        $factor = $pointsPerKg[$material] ?? 1;
        
        return round($weight * $factor);
    }
}
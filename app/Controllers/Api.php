<?php

namespace App\Controllers;

use App\Models\CollectionPointModel;
use App\Models\CollectionModel;
use CodeIgniter\API\ResponseTrait;

class Api extends BaseController
{
    use ResponseTrait;

    protected $user;

    public function __construct()
    {
        // Obtém o usuário do request (adicionado pelo JwtFilter)
        $this->user = $this->request->user ?? null;
    }

    // Endpoint 1: Cadastrar ponto de coleta
    public function registerPoint()
    {
        // Verificar se é um parceiro
        if ($this->user->type !== 'partner') {
            return $this->failForbidden('Apenas parceiros podem cadastrar pontos de coleta');
        }

        $model = new CollectionPointModel();
        $data = $this->request->getJSON(true);
        
        // Adiciona o partner_id do token
        $data['partner_id'] = $this->user->id;
        
        // Validação
        $rules = [
            'name' => 'required|min_length[3]',
            'latitude' => 'required|decimal',
            'longitude' => 'required|decimal',
            'materials' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        if ($model->save($data)) {
            return $this->respondCreated([
                'id' => $model->getInsertID(),
                'message' => 'Ponto de coleta cadastrado com sucesso'
            ]);
        }

        return $this->fail($model->errors());
    }

    // Endpoint 2: Listar pontos por geolocalização 
    public function nearbyPoints()
    {
        $latitude = $this->request->getGet('lat');
        $longitude = $this->request->getGet('lng');
        $radius = $this->request->getGet('radius') ?? 5;
        $material = $this->request->getGet('material');
        
        if (!$latitude || !$longitude) {
            return $this->fail('Latitude e longitude são obrigatórios');
        }
        
        $model = new CollectionPointModel();
        $points = $model->findNearby($latitude, $longitude, $radius, $material);
        
        return $this->respond($points);
    }
    
    // Versão pública do endpoint nearbyPoints (sem autenticação)
    public function publicNearbyPoints()
    {
        return $this->nearbyPoints();
    }

    // Obter um ponto específico
    public function getPoint($id = null)
    {
        $model = new CollectionPointModel();
        $point = $model->find($id);
        
        if (!$point) {
            return $this->failNotFound('Ponto de coleta não encontrado');
        }
        
        // Se for um parceiro, verifica se o ponto pertence a ele
        if ($this->user->type === 'partner' && $point['partner_id'] !== $this->user->id) {
            return $this->failForbidden('Acesso negado a este ponto de coleta');
        }
        
        return $this->respond($point);
    }

    // Atualizar um ponto
    public function updatePoint($id = null)
    {
        $model = new CollectionPointModel();
        $point = $model->find($id);
        
        if (!$point) {
            return $this->failNotFound('Ponto de coleta não encontrado');
        }
        
        // Verificar permissão
        if ($this->user->type !== 'partner' || $point['partner_id'] !== $this->user->id) {
            return $this->failForbidden('Você não tem permissão para atualizar este ponto');
        }
        
        $data = $this->request->getJSON(true);
        $data['id'] = $id;
        
        if ($model->save($data)) {
            return $this->respond([
                'message' => 'Ponto de coleta atualizado com sucesso'
            ]);
        }
        
        return $this->fail($model->errors());
    }

    // Excluir um ponto
    public function deletePoint($id = null)
    {
        $model = new CollectionPointModel();
        $point = $model->find($id);
        
        if (!$point) {
            return $this->failNotFound('Ponto de coleta não encontrado');
        }
        
        // Verificar permissão
        if ($this->user->type !== 'partner' || $point['partner_id'] !== $this->user->id) {
            return $this->failForbidden('Você não tem permissão para excluir este ponto');
        }
        
        if ($model->delete($id)) {
            return $this->respondDeleted(['message' => 'Ponto de coleta excluído com sucesso']);
        }
        
        return $this->fail('Erro ao excluir ponto de coleta');
    }

    // Listar todos os pontos (para admin ou do próprio parceiro)
    public function listPoints()
    {
        $model = new CollectionPointModel();
        
        // Se for parceiro, mostrar apenas seus pontos
        if ($this->user->type === 'partner') {
            $points = $model->where('partner_id', $this->user->id)->findAll();
        } else {
            $points = $model->findAll();
        }
        
        return $this->respond($points);
    }

    // Endpoint 3: Registrar coleta com cálculo de CO2
    public function registerCollection()
    {
        $model = new CollectionModel();
        $data = $this->request->getJSON(true);
        
        // Validação
        $rules = [
            'point_id' => 'required|integer',
            'material' => 'required',
            'weight_kg' => 'required|numeric',
            'destination' => 'required'
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
        
        // Verificar permissão (apenas parceiros donos do ponto ou admin)
        if ($this->user->type === 'partner' && $point['partner_id'] !== $this->user->id) {
            return $this->failForbidden('Você não tem permissão para registrar coletas neste ponto');
        }
        
        // Calcular CO2 evitado baseado no material e peso
        $data['co2_saved'] = $this->calculateCO2Savings($data['material'], $data['weight_kg']);
        
        if ($model->save($data)) {
            return $this->respondCreated([
                'collection_id' => $model->getInsertID(),
                'co2_saved' => $data['co2_saved']
            ]);
        }

        return $this->fail($model->errors());
    }
    
    // Listar coletas
    public function listCollections()
    {
        $model = new CollectionModel();
        $pointModel = new CollectionPointModel();
        
        // Filtros
        $pointId = $this->request->getGet('point_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $builder = $model->builder();
        $builder->select('collections.*, collection_points.name as point_name');
        $builder->join('collection_points', 'collections.point_id = collection_points.id');
        
        // Aplicar filtros
        if ($pointId) {
            $builder->where('collections.point_id', $pointId);
        }
        
        if ($startDate) {
            $builder->where('collections.created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('collections.created_at <=', $endDate);
        }
        
        // Se for parceiro, mostrar apenas coletas dos seus pontos
        if ($this->user->type === 'partner') {
            $points = $pointModel->where('partner_id', $this->user->id)->findAll();
            $pointIds = array_column($points, 'id');
            
            if (!empty($pointIds)) {
                $builder->whereIn('collections.point_id', $pointIds);
            } else {
                return $this->respond([]);
            }
        }
        
        $collections = $builder->get()->getResultArray();
        
        return $this->respond($collections);
    }
    
    // Obter uma coleta específica
    public function getCollection($id = null)
    {
        $model = new CollectionModel();
        $collection = $model->find($id);
        
        if (!$collection) {
            return $this->failNotFound('Coleta não encontrada');
        }
        
        // Verificar permissão
        if ($this->user->type === 'partner') {
            $pointModel = new CollectionPointModel();
            $point = $pointModel->find($collection['point_id']);
            
            if ($point['partner_id'] !== $this->user->id) {
                return $this->failForbidden('Acesso negado a esta coleta');
            }
        }
        
        return $this->respond($collection);
    }
    
    /**
     * Calcula a quantidade de CO2 economizada com base no material e peso
     * Valores aproximados para fins de demonstração
     */
    private function calculateCO2Savings($material, $weightKg)
    {
        $co2FactorPerKg = [
            'plastico' => 2.5,
            'papel' => 1.8,
            'vidro' => 0.9,
            'metal' => 3.2,
            'eletronico' => 5.0,
            'organico' => 0.5,
            'oleo' => 3.0
        ];
        
        $material = strtolower($material);
        $factor = $co2FactorPerKg[$material] ?? 1.0; // Valor padrão se o material não for reconhecido
        
        return round($weightKg * $factor, 2);
    }
}
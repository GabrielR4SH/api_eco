<?php

namespace App\Controllers;

use App\Models\CollectionPointModel;
use App\Models\CollectionModel;
use CodeIgniter\API\ResponseTrait;

class Api extends BaseController {
    use ResponseTrait;

    // Endpoint 1: Cadastrar ponto de coleta
    public function registerPoint() {
        $model = new CollectionPointModel();
        $data = $this->request->getJSON();

        if ($model->save($data)) {
            return $this->respondCreated(['id' => $model->getInsertID()]);
        }

        return $this->fail($model->errors());
    }

    // Endpoint 2: Listar pontos por geolocalização (simplificado)
    public function listPoints() {
        $model = new CollectionPointModel();
        $points = $model->findAll();
        return $this->respond($points);
    }

    // Endpoint 3: Registrar coleta
    public function registerCollection() {
        $model = new CollectionModel();
        $data = $this->request->getJSON();

        if ($model->save($data)) {
            return $this->respondCreated(['collection_id' => $model->getInsertID()]);
        }

        return $this->fail($model->errors());
    }
}

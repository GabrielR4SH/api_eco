<?php

namespace App\Controllers;

use App\Models\PartnerModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;

class AuthController extends ResourceController
{
    use ResponseTrait;

    /**
     * Registra um novo parceiro
     */
    public function register()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[partners.email]',
            'password' => 'required|min_length[8]',
            'type' => 'required|in_list[partner,user]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = $this->request->getJSON();
        $type = $data->type;
        
        // Remove o campo type
        unset($data->type);
        
        // Criptografar a senha
        $data->password = password_hash($data->password, PASSWORD_BCRYPT);
        
        if ($type === 'partner') {
            $model = new PartnerModel();
        } else {
            $model = new UserModel();
        }
        
        if ($model->save($data)) {
            return $this->respondCreated([
                'message' => 'Registro realizado com sucesso',
                'id' => $model->getInsertID()
            ]);
        }

        return $this->fail($model->errors());
    }

    /**
     * Realiza login e retorna token JWT
     */
    public function login()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
            'type' => 'required|in_list[partner,user]'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = $this->request->getJSON();
        
        if ($data->type === 'partner') {
            $model = new PartnerModel();
        } else {
            $model = new UserModel();
        }
        
        $user = $model->where('email', $data->email)->first();
        
        if (!$user || !password_verify($data->password, $user['password'])) {
            return $this->failUnauthorized('Email ou senha inválidos');
        }

        $key = getenv('JWT_SECRET_KEY');
        $time = time();
        
        $payload = [
            'iat' => $time,
            'exp' => $time + 3600 * 24, // Token válido por 24 horas
            'id' => $user['id'],
            'email' => $user['email'],
            'type' => $data->type
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->respond([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }
}
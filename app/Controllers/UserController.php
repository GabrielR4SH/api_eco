<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    protected $user;

    public function __construct()
    {
        $this->user = $this->request->user ?? null;
    }

    /**
     * Obtém perfil do usuário logado
     */
    public function profile()
    {
        if ($this->user->type === 'user') {
            $model = new UserModel();
        } else {
            $model = new \App\Models\PartnerModel();
        }
        
        $profile = $model->find($this->user->id);
        
        if (!$profile) {
            return $this->failNotFound('Perfil não encontrado');
        }
        
        // Remover senha antes de retornar
        unset($profile['password']);
        
        return $this->respond($profile);
    }

    /**
     * Atualiza perfil do usuário logado
     */
    public function updateProfile()
    {
        if ($this->user->type === 'user') {
            $model = new UserModel();
        } else {
            $model = new \App\Models\PartnerModel();
        }
        
        $data = $this->request->getJSON(true);
        $data['id'] = $this->user->id;
        
        // Se tiver senha, faz hash
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']); // Não atualiza a senha se estiver vazia
        }
        
        // Impedir atualização de email para um já existente
        if (isset($data['email'])) {
            $existingUser = $model->where('email', $data['email'])
                                 ->where('id !=', $this->user->id)
                                 ->first();
            
            if ($existingUser) {
                return $this->fail(['email' => 'Este email já está em uso']);
            }
        }
        
        if ($model->save($data)) {
            $updatedProfile = $model->find($this->user->id);
            unset($updatedProfile['password']);
            
            return $this->respond([
                'message' => 'Perfil atualizado com sucesso',
                'user' => $updatedProfile
            ]);
        }
        
        return $this->fail($model->errors());
    }
}
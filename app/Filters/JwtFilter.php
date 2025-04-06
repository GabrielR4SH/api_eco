<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtFilter implements FilterInterface
{
    use ResponseTrait;

    /**
     * Verifica se o token JWT é válido
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        
        try {
            if (!$header) {
                return Services::response()
                    ->setJSON(['error' => 'Token não fornecido'])
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            }
            
            $token = explode(' ', $header)[1];
            
            $key = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // Adiciona o usuário decodificado ao request para uso posterior
            $request->user = $decoded;
            
            return $request;
        } catch (Exception $e) {
            return Services::response()
                ->setJSON(['error' => 'Token inválido ou expirado'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não fazemos nada depois
        return $response;
    }
}
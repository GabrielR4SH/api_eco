# 🌱 EcoAssist API

API para gestão de logística reversa e sustentabilidade, desenvolvida em CodeIgniter 4. Conecta parceiros de coleta, usuários finais e gera relatórios de impacto ambiental.

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

## ✨ Funcionalidades

- **Autenticação JWT** para parceiros e usuários
- **Geolocalização** de pontos de coleta
- Registro de **coletas** com cálculo de CO₂ economizado
- **Relatórios** de impacto ambiental
- Sistema de **gamificação** com leaderboard
- API RESTful completa

## 🚀 Começando

### Pré-requisitos

- PHP 7.4+
- MySQL 5.7+
- Composer
- Postman (para testes)

### Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/ecoassist-api.git
cd ecoassist-api
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o ambiente:
```bash
cp env .env
```

Edite o `.env` com suas credenciais do MySQL:
```ini
database.default.hostname = localhost
database.default.database = ecoassist_db
database.default.username = root
database.default.password = sua_senha
JWT_SECRET_KEY = sua_chave_secreta_aqui
```

4. Execute as migrations e seeders:
```bash
php spark migrate
php spark db:seed UserSeeder
php spark db:seed PartnerSeeder
php spark db:seed CollectionPointSeeder
```

5. Inicie o servidor:
```bash
php spark serve
```

## 🔍 Endpoints Principais

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/register` | Registro de novo usuário/parceiro |
| POST | `/api/auth/login` | Login para obter token JWT |

### Pontos de Coleta

```http
GET /api/points/nearby?lat=-23.563&lng=-46.652
Authorization: Bearer <token>
```

**Exemplo de resposta:**
```json
[
  {
    "id": 1,
    "name": "Ponto Paulista",
    "distance": "0.8 km",
    "materials": ["plástico", "vidro"]
  }
]
```

### Coletas

```http
POST /api/collections
Headers: 
  Authorization: Bearer <token_parceiro>
Body:
{
  "point_id": 1,
  "material": "plástico",
  "weight_kg": 5.0
}
```

### Relatórios

```http
GET /api/reports/impact?start_date=2024-01-01
Headers:
  Authorization: Bearer <token_admin>
```

### Gamificação

```http
GET /api/leaderboard
Headers:
  Authorization: Bearer <token>
```

## Documentação Completa da API

## 🛠 Testando com Postman

1. Importe a coleção:
   * Baixe o arquivo `EcoAssist_API.postman_collection.json`
   * No Postman: File → Import → Selecionar arquivo
2. Configure as variáveis de ambiente:
   * `base_url`: `http://localhost:8080`
   * `token` (será preenchido automaticamente após login)
3. Fluxo de teste sugerido:
   1. `Register Partner`
   2. `Login Partner`
   3. `Create Collection Point`
   4. `Register Collection`
   5. `Get Leaderboard`

## 🌟 Gamificação

Usuários ganham pontos por descarte sustentável:
* 10 pontos por kg de plástico
* 15 pontos por kg de eletrônicos
* 5 pontos por kg de vidro

**Exemplo de pontuação:**
```json
{
  "user_id": 3,
  "name": "Eco Warrior",
  "total_points": 450,
  "total_kg": 30.5
}
```

## ⚙️ Variáveis de Ambiente

| Chave | Exemplo | Descrição |
|-------|---------|-----------|
| `JWT_SECRET_KEY` | `sua_chave_secreta` | Chave para tokens JWT |
| `database.default` | Configuração do MySQL | Credenciais do banco |



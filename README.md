# JetPay API

<div align="left">
  <img src="https://img.shields.io/badge/Status-Work In Progress-orange" alt="Status">
  <img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12.0-red" alt="Laravel">
  <img src="https://img.shields.io/badge/Pest-4.4-yellow" alt="Pest">
</div>

API de gateway de pagamento desenvolvida em Laravel 12 com suporte a múltiplos gateways.

## Requisitos

- Docker

## Opcional (instalação local)

- PHP 8.2+
- Laravel 12+
- PostgreSQL 16+
- Composer

## Docker

```bash
# Build and run the application containers
docker compose up --build -d

# Set up the .env file and database and seed initial data
docker compose exec app /bin/bash -c \
'cp .env.example .env && php artisan key:generate && php artisan migrate --seed'
```

## Requisições

Basta importar a coleção Postman `jetpay_multi-gateway_api.postman_collection.json` disponível no repositório para testar as rotas da API.

## Swagger

Após iniciar os containers você pode acessar a [documentação aqui](http://127.0.0.1:8000/api/documentation#/).

## Entidades

Após a instalação, as seguintes entidades estarão disponíveis no banco de dados:

### Users

```json
{
        {
            "id": 1,
            "name": "Admin User",
            "email": "admin@jetpay.com",
            "role": "ADMIN"
        },
        {
            "id": 2,
            "name": "Finance User",
            "email": "finance@jetpay.com",
            "role": "FINANCE"
        },
        {
            "id": 3,
            "name": "Manager User",
            "email": "manager@jetpay.com",
            "role": "MANAGER"
        }
 }
```

Você pode usar o Admin ou o Manager para criar novos usuários.

### Products

```json
{
        {
            "id": 1,
            "name": "Product 1",
            "amount": 1000
        },
        {
            "id": 2,
            "name": "Product 2",
            "amount": 2500
        },
        {
            "id": 3,
            "name": "Product 3",
            "amount": 5000
        }
 }
```

### Clients

```json
{
        {
            "name": "Client One",
            "email": "client1@example.com"
        },
        {
            "name": "Client Two",
            "email": "client2@example.com"
        },
        {
            "name": "Client Three",
            "email": "client3@example.com"
        }
 }
```

## Autenticação

A API utiliza Laravel Sanctum com autenticação via Bearer token.

**Registro:**

```

POST /api/register

```

```json
{
    "name": "John Doe",
    "email": "johndoe@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Login:**

```
POST /api/login
```

```json
{
    "email": "johndoe@example.com",
    "password": "password"
}
```

O token retornado deve ser enviado no header `Authorization: Bearer {token}` em todas as rotas autenticadas.

## Criando uma Transação

Você pode criar uma transação seguindo o modelo abaixo.

```
POST /api/transactions
```

```json
{
    "client_id": 1,
    "name": "Client One",
    "email": "client1@example.com",
    "card_number": "5569000000006063",
    "cvv": "010",
    "cart": [
        { "product_id": 1, "quantity": 2 },
        { "product_id": 2, "quantity": 6 },
        { "product_id": 3, "quantity": 7 }
    ]
}
```

O valor total (`amount`) é calculado automaticamente com base nos produtos e quantidades informados. O gateway é selecionado automaticamente pelo sistema com base na prioridade e disponibilidade.

## Testes

```bash
./vendor/bin/pest
./vendor/bin/pest --coverage
```

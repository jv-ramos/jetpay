# JetPay API

API de gateway de pagamento desenvolvida em Laravel 12 com suporte a múltiplos gateways.

## Requisitos

- PHP 8.5+
- PostgreSQL 16+
- Composer
- Docker (opcional)

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Docker

```bash
docker compose up --build -d
docker compose exec app php artisan migrate --seed
```

## Entidades

Apos a instalação, as seguintes entidades estarão disponíveis no banco de dados:

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

## Entidades

### Users

Usuários do sistema com controle de acesso por role.

| Campo    | Tipo   | Descrição                             |
| -------- | ------ | ------------------------------------- |
| id       | bigint | Identificador único                   |
| name     | string | Nome do usuário                       |
| email    | string | E-mail único                          |
| password | string | Senha criptografada                   |
| role     | enum   | `ADMIN`, `MANAGER`, `FINANCE`, `USER` |

### Clients

Clientes que realizam transações.

| Campo | Tipo   | Descrição           |
| ----- | ------ | ------------------- |
| id    | bigint | Identificador único |
| name  | string | Nome do cliente     |
| email | string | E-mail único        |

### Products

Produtos disponíveis para compra.

| Campo  | Tipo            | Descrição           |
| ------ | --------------- | ------------------- |
| id     | bigint          | Identificador único |
| name   | string          | Nome do produto     |
| amount | unsignedInteger | Preço em centavos   |

### Gateways

Gateways de pagamento disponíveis.

| Campo     | Tipo        | Descrição                                      |
| --------- | ----------- | ---------------------------------------------- |
| id        | bigint      | Identificador único                            |
| name      | string      | Nome do gateway                                |
| is_active | boolean     | Se o gateway está ativo                        |
| priority  | tinyInteger | Ordem de prioridade (menor = maior prioridade) |

### Transactions

Transações financeiras processadas pelos gateways.

| Campo             | Tipo            | Descrição                          |
| ----------------- | --------------- | ---------------------------------- |
| id                | bigint          | Identificador único                |
| client_id         | FK              | Referência ao cliente              |
| gateway_id        | FK              | Referência ao gateway utilizado    |
| external_id       | string          | ID da transação no gateway externo |
| status            | string          | Status retornado pelo gateway      |
| amount            | unsignedInteger | Valor total em centavos            |
| card_last_numbers | string(4)       | Últimos 4 dígitos do cartão        |

### Product Transaction (pivot)

Tabela auxiliar que relaciona produtos a transações.

| Campo          | Tipo            | Descrição                          |
| -------------- | --------------- | ---------------------------------- |
| product_id     | FK              | Referência ao produto              |
| transaction_id | FK              | Referência à transação             |
| quantity       | unsignedInteger | Quantidade do produto na transação |

## Rotas

### Autenticação

| Metódo | Rota            | Descrição                             |
| ------ | --------------- | ------------------------------------- |
| POST   | `/api/register` | Registrar um usuário (ADMIN, MANAGER) |
| POST   | `/api/login`    | Login não requer autenticação         |
| GET    | `/api/user`     | Detalhes do usuário autenticado       |
| GET    | `/api/users`    | Listar usuários (ADMIN, MANAGER)      |

### Products

| Método | Rota                 | Descrição           | Role          |
| ------ | -------------------- | ------------------- | ------------- |
| GET    | `/api/products`      | Listar produtos     | Público       |
| POST   | `/api/products`      | Criar produto       | ADMIN MANAGER |
| GET    | `/api/products/{id}` | Detalhes do produto | ADMIN MANAGER |
| PUT    | `/api/products/{id}` | Atualizar produto   | ADMIN MANAGER |
| DELETE | `/api/products/{id}` | Remover produto     | ADMIN MANAGER |

### Gateways

| Método | Rota                          | Descrição          | Role        |
| ------ | ----------------------------- | ------------------ | ----------- |
| PATCH  | `/api/gateways/{id}/toggle`   | Ativar/desativar   | Autenticado |
| PATCH  | `/api/gateways/{id}/priority` | Alterar prioridade | Autenticado |

### Transactions

| Método | Rota                            | Descrição             | Role          |
| ------ | ------------------------------- | --------------------- | ------------- |
| GET    | `/api/transactions`             | Listar transações     | Autenticado   |
| POST   | `/api/transactions`             | Criar transação       | Autenticado   |
| GET    | `/api/transactions/{id}`        | Detalhes da transação | Autenticado   |
| POST   | `/api/transactions/{id}/refund` | Estornar transação    | ADMIN FINANCE |

## Criando uma Transação

```
POST /api/transactions
```

```json
{
    "client_id": 1,
    "name": "John Doe",
    "email": "johndoe@example.com",
    "card_number": "5569000000006063",
    "cvv": "010",
    "cart": [
        { "product_id": 1, "quantity": 2 },
        { "product_id": 2, "quantity": 1 }
    ]
}
```

O valor total (`amount`) é calculado automaticamente com base nos produtos e quantidades informados. O gateway é selecionado automaticamente pelo sistema com base na prioridade e disponibilidade.

## Testes

```bash
./vendor/bin/pest
./vendor/bin/pest --coverage
```

# UEMS Labs — API PHP

Camada intermediária entre o frontend (Quasar) e o Postgres do Supabase. O
Supabase Auth continua cuidando só do login; toda regra de negócio (reservas,
problemas, denúncias, dashboard do técnico) passa por aqui.

```
Quasar (fetch) -> PHP/Apache (PDO) -> Postgres (Supabase)
```

## Configuração (fazer uma vez por máquina)

1. **Habilitar as extensões do PHP.** Abra `C:\xampp\php\php.ini` e
   descomente (tire o `;` da frente) estas duas linhas:
   ```
   extension=pdo_pgsql
   extension=pgsql
   ```
2. **Apontar o Apache para esta pasta.** Em
   `C:\xampp\apache\conf\extra\httpd-xampp.conf`, adicione (ajustando o
   caminho para onde o repositório está na SUA máquina):
   ```apache
   Alias /uems-api "CAMINHO/DO/SEU/REPOSITORIO/uems_labs/uems_labs_api"
   <Directory "CAMINHO/DO/SEU/REPOSITORIO/uems_labs/uems_labs_api">
       Options -Indexes +FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```
3. **Criar o `.env` desta pasta** (`uems_labs_api/.env`, nunca commitar):
   copie `.env.example` e preencha `CONNECTION_STRING` (pegue em
   *Project Settings → Database → Connection string* no painel do Supabase),
   `SUPABASE_URL` e `SUPABASE_ANON_KEY` (os mesmos já usados no
   `uems_labs/.env` do frontend — não são secretos).
4. **Reiniciar o Apache** (XAMPP Control Panel → Stop/Start, ou
   `apache_stop.bat` + `apache_start.bat`).
5. **Testar**: abra `http://localhost/uems-api/` no navegador. Deve
   responder `{"status":"ok", ...}`. Se dor erro de conexão com o banco,
   confira a senha no `CONNECTION_STRING`.

## Autenticação

Todo endpoint (exceto o health-check da raiz) exige o header:

```
Authorization: Bearer <access_token>
```

O `access_token` é o mesmo da sessão do Supabase Auth no frontend. Para
testar manualmente, com o app rodando e você logado, abra o DevTools (F12) →
aba **Application** → **Local Storage** → `http://localhost:9000` e procure
a chave `sb-<project_ref>-auth-token`. O `access_token` está dentro do JSON
guardado ali.

O PHP valida esse token chamando o próprio Supabase (`/auth/v1/user`) e
depois busca o perfil (nome, rgm, perfil) na tabela `usuarios`. Cada endpoint
recebe esse usuário já validado:

```php
$usuario = require_auth();
// $usuario = ['id' => ..., 'rgm' => ..., 'nome' => ..., 'perfil' => 'aluno'|'professor'|'tecnico']

// Se o endpoint for só do técnico:
require_perfil($usuario, ['tecnico']);
```

## Regras de negócio já prontas no banco

**Choque de horário (RF002/RF003):** o banco tem uma constraint
(`reservas_sem_choque`) que rejeita qualquer INSERT/UPDATE em `reservas` que
sobreponha horário com outra reserva **ativa** do mesmo `recurso_id` — vale
tanto pra reserva normal quanto pra fixa, automaticamente. Em
`api/reservas/criar.php`, envolva o `INSERT` num `try/catch (PDOException)` e,
se o `getCode()` for `23P01` (violação de exclusion constraint), responda
`json_error('Esse horário já está reservado.', 409)`.

**Reserva fixa (RF003):** não faça o INSERT manualmente — chame a função
`criar_reserva_fixa` já pronta no banco, que gera uma reserva por semana entre
duas datas (mesmo dia da semana de `data_inicio`) e desfaz tudo se qualquer
semana colidir com outra reserva:

```php
$stmt = $pdo->prepare('SELECT * FROM criar_reserva_fixa(:usuario_id, :recurso_id, :hora_inicio, :hora_fim, :proposito, :data_inicio, :data_fim)');
$stmt->execute([
    'usuario_id'   => $usuario['id'],
    'recurso_id'   => $body['recurso_id'],
    'hora_inicio'  => $body['hora_inicio'],
    'hora_fim'     => $body['hora_fim'],
    'proposito'    => $body['proposito'],
    'data_inicio'  => $body['data_inicio'], // primeira ocorrência
    'data_fim'     => $body['data_fim'],    // até quando repete (ex: fim do semestre)
]);
$reservas = $stmt->fetchAll();
```

**Resolver problema (RF010):** `api/problemas/resolver.php` só precisa dar um
`UPDATE problemas_relatados SET status = 'resolvido' WHERE id = :id` — um
trigger no banco já libera o `recurso` (volta pra `disponivel`) sozinho,
nenhuma lógica extra necessária no PHP.

## Padrão de pastas e arquivos

```
api/<dominio>/<acao>.php
```

Copie `api/_EXEMPLO.php` como ponto de partida — ele já traz CORS, validação
do método HTTP, autenticação e leitura do corpo da requisição no padrão
esperado.

## Contrato de endpoints

| Rota | Método | Responsável | Status |
|---|---|---|---|
| `/api/laboratorios/listar.php` | GET | Backend (pronto) | ✅ feito |
| `/api/reservas/criar.php` | POST | Integrante A | a fazer |
| `/api/reservas/listar.php` | GET | Integrante B | a fazer |
| `/api/reservas/cancelar.php` | POST | Integrante B | a fazer |
| `/api/problemas/relatar.php` | POST | Integrante C | a fazer |
| `/api/denuncias/criar.php` | POST | Integrante C | a fazer |
| `/api/tecnico/dashboard.php` | GET | Integrante D | a fazer |
| `/api/problemas/resolver.php` | POST | Integrante D | a fazer |

Resposta de sucesso: JSON direto (objeto ou array), status HTTP 200/201.
Resposta de erro: `{"error": "mensagem"}`, com status HTTP apropriado
(400/401/403/404/422/500) — é o que `json_error()` já faz.

## Arquivos de infraestrutura (prontos, não precisam ser alterados)

- `config/env.php` — carrega o `.env`.
- `config/database.php` — conexão PDO com o Postgres (`get_db_connection()`).
- `helpers/response.php` — `json_response()`, `json_error()`, `json_body()`.
- `helpers/cors.php` — libera CORS e responde o preflight `OPTIONS`.
- `middleware/auth.php` — `require_auth()`, `require_perfil()`.

# UEMS Labs

Sistema de reserva de laboratórios de computação da UEMS. Projeto da
disciplina de PW — 5 integrantes, todos codando.

```
Quasar (Vue 3)  --fetch-->  PHP/Apache local (PDO)  -->  Postgres (Supabase)
     |
     +-- Supabase Auth cuida só do login
```

- **Frontend**: `src/` (Quasar/Vue 3) — este repositório.
- **Backend**: `uems_labs_api/` (PHP puro) — intermediário entre o frontend e o banco.
- **Divisão de tarefas**: `docs/divisao-de-tarefas.docx`.

## Como começar (cada integrante, uma vez)

1. **Clonar o repositório** e trocar para a sua branch:
   ```bash
   git clone https://github.com/jppippus/uems_labs.git
   cd uems_labs
   git checkout <sua-branch>   # ver tabela abaixo
   ```
2. **Pedir pro responsável do backend** (fora do git, nunca por aqui):
   - a connection string do Postgres (vai no seu `uems_labs_api/.env`, local);
   - uma conta de teste no Supabase Auth (rgm/nome/perfil).
3. **Frontend**:
   ```bash
   npm install
   cp .env.example .env   # se não existir; preencha QCLI_SUPABASE_URL/KEY (não são secretos)
   quasar dev
   ```
4. **Backend local (XAMPP)**: siga `uems_labs_api/README.md` — habilitar
   `pdo_pgsql`/`pgsql` no `php.ini`, Alias do Apache, criar seu `.env`.
5. **Conferir que está tudo de pé**: abra `http://localhost:9000`, faça
   login, e confirme que `http://localhost/uems-api/` responde
   `{"status":"ok"}`.

## Sua tarefa

| Branch | Integrante | Requisitos | Endpoints PHP | Página(s) Vue |
|---|---|---|---|---|
| `integrante-a-reservas` | A | RF002, RF003 | `api/reservas/criar.php` | `ReservaPage.vue` |
| `integrante-b-minhas-reservas` | B | RF004, RF008 | `api/reservas/listar.php`, `api/reservas/cancelar.php` | `MinhasReservasPage.vue` (nova) |
| `integrante-c-problema-denuncia` | C | RF005, RF007 | `api/problemas/relatar.php`, `api/denuncias/criar.php` | `RelatarProblemaPage.vue`, `DenunciaPage.vue` (novas) |
| `integrante-d-tecnico` | D | RF009, RF010 | `api/tecnico/dashboard.php`, `api/problemas/resolver.php` | `TecnicoDashboardPage.vue`, `TecnicoProblemasPage.vue` (novas) |

Detalhes de cada tarefa, requisitos completos e critérios: ver
`docs/divisao-de-tarefas.docx`.

## Como chamar o backend a partir do Vue

Use a composable `src/composables/UseApi.js` — ela já injeta o token da
sessão do Supabase Auth no header `Authorization` e trata erro. Crie sua
própria composable de domínio em cima dela (não edite a dos outros):

```js
// src/composables/UseReservasApi.js (exemplo pro Integrante A)
import useApi from './UseApi'

export default function useReservasApi() {
  const { apiGet, apiPost } = useApi()

  const criarReserva = (payload) => apiPost('/reservas/criar.php', payload)

  return { criarReserva }
}
```

No PHP, copie `uems_labs_api/api/_EXEMPLO.php` como ponto de partida do seu
endpoint. Contrato completo (rotas, método, regras de negócio já prontas no
banco): `uems_labs_api/README.md`.

## Fluxo de Git

- Cada um trabalha só na própria branch e nos próprios arquivos (não editem
  arquivo de outro integrante — evita conflito de merge).
- Commits pequenos e frequentes.
- Quando a sua parte estiver funcionando: abra um Pull Request pra `main`.
- Nunca commitar `.env` (já está no `.gitignore`).

## Install the dependencies

```bash
npm install
```

### Start the app in development mode (HMR, error reporting, etc.)

```bash
quasar dev
```

### Build the app for production

```bash
quasar build
```

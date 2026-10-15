# 05. API e app mobile

Implementado na fase 5 (15/10/2026). Antes dela, o app Flutter não falava com o servidor ([doc 01](01-diagnostico-atual.md#2-app-flutter-app)). Este documento descreve como os dois lados funcionam juntos com o multi-tenant.

## 1. Sanctum e convenções da API

- Instalado com `php artisan install:api` (`laravel/sanctum` 4.3). A API fica em `routes/api.php`, com prefixo `/api/v1` e nomes `api.v1.*`. Respostas por Resource. O `shouldRenderJsonWhen` devolve JSON em todo erro de `api/*`.
- Controllers em `App\Http\Controllers\Api\V1`, requests em `App\Http\Requests\Api\V1` e resources em `App\Http\Resources\Api\V1`.
- O `sanctum` está em `audit.user.guards`, então as auditorias da API registram o usuário do token.

## 2. Token atrelado à empresa

- **Coluna `personal_access_tokens.tenant_id`:** FK com `cascadeOnDelete`. O model `App\Models\PersonalAccessToken` é registrado com `Sanctum::usePersonalAccessTokenModel()`.
- **Vale para uma empresa só:** cada token só abre a empresa em que foi emitido. Um token vazado não abre as outras empresas da mesma pessoa.
- **Validade de 30 dias** (`AuthTokenService::TOKEN_LIFETIME_DAYS`). O agendador roda `sanctum:prune-expired --hours=24` todo dia.
- **Sem escopo de empresa no model:** o `PersonalAccessToken` tem `tenant_id`, mas o Sanctum busca o token antes de existir empresa no contexto. É a única exceção registrada no teste de arquitetura (`MODELS_WITHOUT_TENANT_SCOPE`).

### Login: `POST /api/v1/auth/token`

Corpo: `{ email, password, device_name, tenant_id? }`. Limite de 5 tentativas por minuto por e-mail e IP (`throttle:api-login`).

| Caso | Resposta |
|---|---|
| Credenciais inválidas | 422, `errors.email = "E-mail ou senha incorretos"` |
| Uma empresa ativa, ou `tenant_id` de uma empresa ativa | 201, `data: { token, token_type, expires_at, user, tenant, permissions }` |
| Várias empresas e sem `tenant_id` | 409, `{ message, tenants: [{ id, name }] }`. O app mostra a lista e repete o login |
| `tenant_id` sem vínculo ativo | 403, "Você não tem acesso a esta empresa." |
| Nenhuma empresa ativa | 403, "Você não participa de nenhuma empresa ativa." |
| Empresa sem acesso (`allowsAccess()` falso) | 423, com o motivo (`Tenant::blockedReason()`) |

As respostas 409 e 423 vêm de `TenantSelectionRequiredException` e `TenantUnavailableException`, que já sabem se renderizar.

### Demais rotas

Todas com `auth:sanctum`, `tenant.token` e `throttle:api` (120 por minuto por token).

| Rota | O quê |
|---|---|
| `DELETE /api/v1/auth/token` | Revoga só o token em uso (204). Os outros aparelhos continuam conectados |
| `GET /api/v1/me` | Sessão do token: usuário, empresa e permissões. Não devolve o token |
| `GET /api/v1/trips` | Rotas da própria pessoa, das mais recentes para as mais antigas, com `points_count` |
| `PUT /api/v1/trips/{uuid}` | Cria (201) ou atualiza (200) a rota. A empresa e o usuário vêm do token, nunca do corpo |
| `POST /api/v1/trips/{uuid}/points` | Lote de até 500 pontos. Ignora os que já existem e responde `{ received, stored }` |

### Middleware `tenant.token` (`SetTenantFromToken`)

Equivale ao `tenant` da web e também roda antes do `SubstituteBindings`.

| Situação | Resposta |
|---|---|
| Token sem empresa, ou a pessoa saiu da empresa | 401, "Sua sessão nesta empresa foi encerrada.", e o token é apagado |
| Empresa suspensa, cancelada ou com teste vencido | 423. O token **não** é apagado, porque o acesso pode voltar |
| Tudo certo | Define o `TenantContext` pela empresa do token. A `current_tenant_id` da web não interfere |

## 3. Rotas de campo: `trips` e `trip_points`

O app chama o trajeto do técnico de "rota". No backend, o nome é `Trip` para não confundir com as rotas HTTP do Laravel (`Route`). A interface continua chamando de "Rotas".

- **Id gerado no aparelho:** as duas tabelas usam UUID (v7) gerado no app, o que torna a sincronização idempotente.
- **Separação por empresa:** `trip_points` tem chave estrangeira composta `(tenant_id, trip_id)` para `trips`.
- **Permissão:** `trips.record`, que todos os papéis têm. Cada pessoa só envia e altera as próprias rotas (`TripPolicy`), e rota de outra pessoa da mesma empresa dá 403.
- **Id que já existe em outra empresa:** o escopo esconde a linha e o `INSERT` bate na chave primária. A API responde 409 (`SyncConflictException`) e nunca altera a linha alheia.
- **Pontos entram em lote:** `insertOrIgnore` por fora dos eventos de model, com a empresa tirada da própria rota, já validada. `trip_points` não tem timestamps nem auditoria por linha, porque é a maior tabela do sistema.
- **Datas:** o app envia em UTC (ISO 8601 com `Z`).

## 4. App Flutter (`../APP`)

| Peça | Arquivo | O que faz |
|---|---|---|
| Configuração | `lib/config.dart`, `config.example.json` | `API_BASE_URL` por `--dart-define-from-file`. O padrão é `http://10.0.2.2:8000` (emulador Android) |
| Cliente da API | `lib/api/cliente_api.dart` | `http`, com tempo limite de 20 s e erros tipados em `lib/api/erros_api.dart` (`EscolhaDeEmpresa`, `SessaoEncerrada`, `EmpresaBloqueada`, `FalhaDeConexao`...) |
| Sessão | `lib/api/sessao.dart`, `lib/sessao/` | Token, usuário, empresa e permissões em `flutter_secure_storage` (Keychain/Keystore), nunca no SQLite. `ControleSessao` faz login, logout e a retomada ao abrir o app |
| Login | `lib/sessao/entrar_page.dart` | E-mail e senha. Quem está em várias empresas escolhe uma num diálogo |
| Navegação | `lib/router.dart` | Sem sessão, tudo leva a `/entrar`; com sessão, `/entrar` leva ao painel |
| Banco local | `lib/dados/banco_local.dart` | Versão 3: colunas `uuid` em `rotas` e `pontos_rota`, `sincronizada_em` em `rotas`. **Um arquivo por empresa e usuário** (`vistoria_solar_{empresa}_{usuario}.db`): trocar de empresa ou de conta abre outro arquivo |
| Rotas antigas | `lib/dados/importacao_rotas_antigas.dart` | Rotas gravadas antes do login (`vistoria_solar.db`). Depois do primeiro login, a pessoa escolhe entre enviar para a empresa, apagar do aparelho ou decidir depois |
| Sincronização | `lib/sincronizacao/sincronizador_rotas.dart` | Envia as rotas finalizadas pendentes: PUT da rota, pontos em lotes de 500, marca `sincronizada_em` |

### Quando o app sincroniza

- Ao entrar e ao abrir o app com sessão guardada.
- Ao finalizar uma rota.
- Ao voltar para o app (`AppLifecycleListener`).
- Pelo botão "Enviar rotas agora" no Histórico.

### Quando algo dá errado

| Erro | O que o app faz |
|---|---|
| Sem rede ou servidor fora | Nova tentativa automática em 30 s, 1 min, 2 min... até 15 min |
| 401 | Encerra a sessão e volta ao login. As rotas pendentes ficam no banco da empresa e saem no próximo login |
| 403, 409 ou 422 numa rota | A rota fica pendente, aparece no resultado e as outras continuam sendo enviadas |

### Outros detalhes

- **Histórico:** cada rota mostra "Enviada" ou "Aguardando envio". Excluir uma rota já enviada só a tira do aparelho.
- **Sair:** fica no menu de conta do Painel. Com uma rota gravando, o app pede para finalizar antes.
- **Android em debug:** `usesCleartextTraffic` está ligado só no manifest de debug, para falar com o `php artisan serve` local, que responde em HTTP.

## 5. Segurança da API

- `tenant_id` e `user_id` **nunca** vêm do corpo da requisição. O teste de sincronização manda os dois e confere que são ignorados.
- A remoção de um membro derruba o token dele na próxima requisição.
- As respostas são sempre Resources, nunca o model inteiro.

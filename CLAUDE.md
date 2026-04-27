# Projeto 01 — URL Shortener em Hyperf

## Para o Claude Code: contexto deste projeto

Este é o primeiro projeto da série: um encurtador de URLs em Hyperf 3.x. O objetivo pedagógico é **aquecer com Hyperf sem complexidade de domínio** — a regra de negócio é trivial, o foco é o framework e as ferramentas.

## Tópicos da vaga exercitados

- **PHP 8+** moderno (readonly, enums, match, attributes)
- **Hyperf 3.x + Swoole** (HTTP Server, DI, AOP, Coroutines, Pool)
- **MySQL** (migrations, índices)
- **Redis** (cache + rate limiter)
- **Docker** (compose com app + mysql + redis)
- **TDD** (testes unitários e de feature)
- **Qualidade de código** (SOLID, exceções tipadas, VO/enum)
- **Git** (commits semânticos)

## Princípios que devem guiar seu código

1. **Domínio puro sem framework**: classes em `app/Domain/` **não importam** nada do Hyperf.
2. **Controller fino**: só orquestra HTTP ↔ Service. Zero lógica de negócio.
3. **Service fino também**: delega decisões pra Domain e persistência pra Model/Repository.
4. **Testes em todo commit**: não faça commit que deixe `composer test` vermelho.
5. **Sem over-engineering**: é projeto pedagógico. Não invente CQRS, Event Sourcing, DDD pesado. Se o domínio é simples, o código é simples.
6. **Comentários explicam "por quê", não "o quê"**: se precisa comentar "o quê", o nome tá ruim.
7. **Hyperf é long-running**: nada de estado em propriedade de singleton. Use `Context` quando precisar de estado por request.

## Convenções de código

- PHP 8.2+ — use `readonly`, `enum`, `match`, constructor property promotion.
- `declare(strict_types=1);` em **todo** arquivo PHP.
- PSR-12 para formatação.
- Nomes em **português** para domínio de negócio (`Link`, `Slug`, `EstatisticaAcesso`) — opcional, mas no Brasil é comum e legítimo. Se preferir inglês, mantenha consistência.
- Testes nomeados em português no estilo BDD: `test_redireciona_para_url_original_quando_slug_existe`.
- SIGA A DOCUMENTAÇÃO DO HYPERF A RISCA

## Convenções de commit

Conventional Commits:

```
feat(slug): cria endpoint POST /urls
test(slug): adiciona teste de criação com slug duplicado
refactor(cache): extrai SlugCache para camada dedicada
chore(docker): adiciona healthcheck no mysql
```

Mantenha PR pequeno — 1 TASK = 1 PR idealmente.

## Estrutura esperada do projeto ao final

```
.
├── app/
│   ├── Controller/
│   │   ├── LinkController.php
│   │   └── HealthController.php
│   ├── Domain/
│   │   └── Link/
│   │       ├── Slug.php           # VO
│   │       ├── SlugGenerator.php  # interface + impl
│   │       └── LinkStatus.php     # enum
│   ├── Middleware/
│   │   ├── RateLimitMiddleware.php
│   │   └── RequestIdMiddleware.php
│   ├── Model/
│   │   └── Link.php
│   ├── Service/
│   │   └── LinkService.php
│   └── Exception/
│       └── SlugAlreadyExistsException.php
├── config/
│   ├── autoload/
│   │   ├── databases.php
│   │   ├── redis.php
│   │   └── jwt.php
│   └── routes.php
├── migrations/
├── tests/
│   ├── Unit/
│   │   └── Domain/
│   └── Feature/
├── docker-compose.yml
├── composer.json
└── README.md
```

## TASKs deste projeto

Execute na ordem:

| # | Título | Tempo estimado |
|---|--------|----------------|
| 01 | Setup inicial com Docker Compose | 1h |
| 02 | Domínio puro: Slug + SlugGenerator | 2h |
| 03 | Model + migration + Repository | 1h |
| 04 | Endpoint POST /urls (TDD) | 2h |
| 05 | Endpoint GET /{slug} com cache Redis | 2h |
| 06 | Contador assíncrono via AsyncQueue | 1h |
| 07 | Endpoint GET /urls/{slug}/stats | 1h |
| 08 | Rate limiting por IP com Redis ZSet | 2h |
| 09 | JWT middleware (auth nos endpoints de escrita) | 2h |
| 10 | README definitivo + diagrama | 1h |

**Total:** ~15h. Tempo real tende a ser 1,5x isso na primeira vez.

## Como você (Claude Code) deve se portar

- **Nunca pule a fase de teste.** Se a TASK exige TDD, você escreve o teste ANTES da implementação e mostra o ciclo red → green → refactor.
- **Explique decisões arquiteturais curtas no commit**.
- **Se perceber que uma TASK não faz sentido ou está mal definida**, avise antes de implementar.
- **Use linguagem técnica direta**, sem floreio.
- **Não invente dependências** — se precisa de lib nova, justifique.

# AGENTS.md

## Objetivo

Este repositório é um sistema de biblioteca em Laravel 12 com Inertia + React, Jetstream, Fortify, Sanctum e PostgreSQL.

O objetivo de quem atuar aqui, seja pessoa ou agente, é:

- manter a `main` estável;
- trabalhar uma mudança por branch;
- fazer commits pequenos e coerentes;
- validar o comportamento antes de concluir;
- evitar alterações acidentais de permissão, configuração local ou arquivos temporários.

## Stack

- Backend: PHP 8.2+, Laravel 12
- Frontend: Inertia.js + React + Vite
- Auth: Fortify + Jetstream + Sanctum
- Permissões: Spatie Laravel Permission
- Banco: PostgreSQL
- Ambiente local preferencial: Laravel Sail / Docker Compose

## Estrutura importante

- `app/` lógica da aplicação
- `app/Actions/Fortify/` ações do fluxo de autenticação
- `app/Providers/` providers de auth e bootstrap
- `resources/js/` páginas, layouts e componentes React
- `resources/views/` casca Blade do Inertia e views auxiliares
- `routes/web.php` rotas web Inertia
- `routes/api.php` rotas API
- `routes/jetstream.php` rotas de recursos do Jetstream
- `tests/` testes automatizados

## Regras de trabalho

1. Nunca trabalhar direto na `main` para features, refactors ou bugfixes.
2. Criar uma branch por assunto.
3. Executar uma etapa por vez.
4. Ao concluir uma etapa, parar, revisar o resultado e só então seguir para a próxima.
5. Evitar commits grandes com temas misturados.
6. Não commitar `.env`, arquivos locais temporários ou lixo de editor.
7. Não commitar `.codex` sem necessidade explícita.
8. Não alterar permissão de arquivos PHP para executável.
9. Manter `artisan` como executável.
10. Antes de finalizar, revisar `git diff` e `git status`.

## Convenção de branches

Usar nomes curtos e claros:

- `feat/<tema>`
- `fix/<tema>`
- `refactor/<tema>`
- `test/<tema>`
- `chore/<tema>`

Exemplos:

- `fix/auth-flow`
- `fix/jetstream-delete-user`
- `feat/loans-flow`
- `test/auth-feature-tests`
- `chore/file-permissions`

## Convenção de commits

Preferir mensagens no padrão:

- `feat: ...`
- `fix: ...`
- `refactor: ...`
- `test: ...`
- `docs: ...`
- `chore: ...`

Exemplos:

- `feat: adiciona fluxo de empréstimos`
- `fix: corrige redirecionamento após login`
- `test: adiciona testes do fluxo de autenticação`
- `chore: remove permissão de execução de arquivos PHP`

## Fluxo recomendado

### Início de trabalho

```bash
git switch main
git pull
git switch -c fix/auth-flow
```

### Durante o trabalho

```bash
git status
git diff
git diff --staged
git add -p
git commit -m "fix: corrige fluxo de autenticação"
```

### Revisão antes de concluir

```bash
git status
git log --oneline --decorate -n 10
git diff main...HEAD
```

## Comandos úteis do projeto

### Ambiente local com Composer/NPM

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
composer test
npm run build
```

### Ambiente com Sail

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer test
./vendor/bin/sail npm run build
```

## Checklist de validação

Antes de concluir uma branch, validar pelo menos o que foi afetado.

### Para mudanças de autenticação

- registro
- login
- logout
- reset de senha
- acesso a rota autenticada
- redirecionamentos corretos

### Para mudanças de domínio

- regra de negócio funcionando
- relação entre models sem erro
- rotas respondendo corretamente
- feedback de erro adequado

### Para mudanças de frontend

- página abre sem erro
- navegação Inertia funciona
- formulário envia corretamente
- mensagens de erro aparecem

## Cuidados específicos deste projeto

### 1. Jetstream

Se mexer em Jetstream, revisar:

- `app/Providers/JetstreamServiceProvider.php`
- `routes/jetstream.php`
- recursos realmente habilitados em `config/jetstream.php`

Não assumir que todo scaffold instalado está completo. Validar fluxo real.

### 2. User e regras de empréstimo

Se mexer no model `User`, revisar impacto em:

- autenticação
- papéis/permissões
- relação com empréstimos
- regra `canBorrow()`

### 3. Permissões de arquivo

Se aparecer `100755` em arquivos PHP comuns, corrigir antes de concluir:

```bash
chmod 644 caminho/do/arquivo.php
```

## O que um agente deve evitar

- editar arquivos não relacionados ao objetivo da branch;
- misturar refactor com feature no mesmo commit;
- adicionar dependência sem necessidade clara;
- mudar configuração de ambiente sem documentar;
- reverter trabalho do usuário sem pedido explícito;
- fazer commits automáticos sem revisão do diff.

## Próximos trabalhos sugeridos

- `fix/auth-flow`
- `fix/jetstream-delete-user`
- `test/auth-feature-tests`
- `feat/books-crud`
- `feat/loans-flow`
- `feat/user-roles-permissions`

## Definição de pronto

Uma tarefa está pronta quando:

- o escopo da branch está claro;
- os arquivos alterados fazem sentido para aquele escopo;
- os commits contam uma história coerente;
- o comportamento foi validado;
- não há mudanças acidentais sobrando no `git status`.

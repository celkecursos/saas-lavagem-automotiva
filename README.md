# Celke Wash Club

SaaS de clube de assinatura para lavagem automotiva, com módulo
independente de gerenciador de estacionamento.

## Stack

Laravel 13 (sempre a última versão estável do framework — não
fixar em versões anteriores por hábito de outros projetos). Antes
de implementar qualquer funcionalidade, consultar a documentação
oficial (laravel.com/docs na versão correspondente, e a
documentação do pacote específico quando for algo de terceiros)
em vez de confiar só em conhecimento prévio.

## Requisitos

- Docker (+ Docker Compose)
- VSCode (ou editor similar), conectado à sua conta do GitHub

Nenhuma outra ferramenta precisa ser instalada na máquina — PHP,
Composer, Node, MySQL, Redis etc. rodam todos via Docker (Laravel
Sail).

## Como rodar

```
git clone https://github.com/celkecursos/saas-lavagem-automotiva.git
cd saas-lavagem-automotiva
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

Acesse http://localhost.

## Usuários de teste (seed)

Rodar `./vendor/bin/sail artisan db:seed` cria os usuários abaixo
(senha igual pra todos — SÓ pra ambiente de desenvolvimento/
demonstração, nunca usar em produção):

| E-mail | Senha | Papel |
|---|---|---|
| cesar@celke.com.br | 123456A#b | Superadmin |
| kelly@celke.com.br | 123456A#b | Administrador |
| jessica@celke.com.br | 123456A#b | Lava-rápido (estacionamento) |
| gabrielly@celke.com.br | 123456A#b | Assinante |

Detalhe completo de como o seeder monta cada papel/permissão em
[`task/task-23-gerenciador-permissoes-seed-usuarios.txt`](./task/task-23-gerenciador-permissoes-seed-usuarios.txt).

## Documentação completa

A pasta [`task/`](./task) tem a especificação completa do
projeto: `orientacao.txt` é o ponto de partida (pré-requisitos e
ordem de execução), e cada `task-N-*.txt` detalha uma
funcionalidade (schema, rotas, testes, commits sugeridos).

## Autor

Desenvolvido por [Cesar Szpak](https://celke.com.br) — [Celke
Cursos](https://github.com/celkecursos).

## Licença

MIT — veja o arquivo [LICENSE](LICENSE.txt) para detalhes.

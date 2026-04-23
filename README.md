<div align="center">

# 🏋️ Corpo e Mente — SaaS de Gestão de Academias

**Sistema completo de gerenciamento de academia com interface web, API REST em PHP e banco de dados MySQL.**






</div>

***

## 📋 Sobre o Projeto

O **Corpo e Mente** é um sistema SaaS de gestão de academias desenvolvido para centralizar as operações do administrador e eliminar o uso de planilhas manuais. O projeto foi criado com base em **Diagramas UML** (Casos de Uso e Classes), garantindo total aderência às regras de negócio definidas.

### ✨ Funcionalidades

- 📊 **Dashboard** com KPIs em tempo real (alunos, matrículas ativas, treinos, aparelhos)
- 👥 **Gestão de Alunos** — cadastro com Nome, CPF, Data de Nascimento e Telefone
- 💳 **Gestão de Planos** — criação e edição com nome, valor e duração
- 🏋️ **Gestão de Aparelhos** — controle de equipamentos com situação de conservação
- 👨‍🏫 **Gestão de Professores** — cadastro da equipe
- 📋 **Gestão de Treinos** — criação com aparelhos (séries, repetições e carga) e vinculação a alunos
- 📝 **Gestão de Matrículas** — com regra de negócio: **apenas 1 plano ativo por aluno**
- 🌙 **Modo escuro / claro**
- 📱 **Interface responsiva** (mobile-first)

***

## 🗂️ Estrutura do Projeto

```
corpo-e-mente/
├── 📄 index.html              # Interface principal (Single Page App)
├── 🗃️  banco.sql               # Script de criação do banco de dados
├── 📖 README.md
├── includes/
│   └── 🔌 conexao.php         # Conexão PDO e funções auxiliares
└── api/
    ├── 👤 alunos.php           # CRUD de Alunos
    ├── 💳 planos.php           # CRUD de Planos
    ├── 🏋️  aparelhos.php        # CRUD de Aparelhos
    ├── 👨‍🏫 professores.php      # CRUD de Professores
    ├── 📋 treinos.php          # CRUD de Treinos
    ├── 📝 matriculas.php       # CRUD de Matrículas
    ├── ➕ itens_treino.php      # CRUD de Itens do Treino
    └── 🔗 aluno_treino.php     # Vínculo N:N Aluno ↔ Treino
```

***

## 🗄️ Diagrama do Banco de Dados

```
aluno ──────────── matricula ──────── plano
  │                                    
  └──── aluno_treino ──── treino ──── item_treino ──── aparelho
                            │
                          professor
```

### Tabelas

| Tabela | Descrição |
|--------|-----------|
| `aluno` | Dados cadastrais do aluno |
| `plano` | Planos disponíveis (mensal, trimestral etc.) |
| `matricula` | Vínculo aluno → plano (máx. 1 ativo por aluno) |
| `aparelho` | Equipamentos da academia |
| `professor` | Profissionais da academia |
| `treino` | Fichas de treino |
| `item_treino` | Aparelhos de cada treino (séries, reps, carga) |
| `aluno_treino` | Relacionamento N:N entre alunos e treinos |

***

## ⚙️ Requisitos

- **PHP** 8.0 ou superior (com extensão PDO/MySQL)
- **MySQL** 8.0+ ou **MariaDB** 10.5+
- Servidor web: **Apache** (XAMPP/WAMP) ou **Nginx**

***

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/corpo-e-mente.git
```

### 2. Mova para o servidor local

```
# XAMPP (Windows)
C:\xampp\htdocs\corpo-e-mente\

# XAMPP (Linux/Mac)
/opt/lampp/htdocs/corpo-e-mente/
```

### 3. Crie o banco de dados

**Via phpMyAdmin:**
1. Acesse `http://localhost/phpmyadmin`
2. Clique em **Importar**
3. Selecione o arquivo `banco.sql`
4. Clique em **Executar**

**Via terminal:**
```bash
mysql -u root -p < banco.sql
```

### 4. Configure a conexão

Edite o arquivo `includes/conexao.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // seu usuário MySQL
define('DB_PASS', '');          // sua senha MySQL
define('DB_NAME', 'corpo_e_mente');
define('DB_PORT', 3306);
```

### 5. Acesse o sistema

```
http://localhost/corpo-e-mente/index.html
```

***

## 🔗 Endpoints da API REST

Todos os endpoints recebem e retornam **JSON**.

### Alunos
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/api/alunos.php` | Listar todos |
| `GET` | `/api/alunos.php?id=1` | Buscar por ID |
| `GET` | `/api/alunos.php?search=João` | Buscar por nome/CPF |
| `POST` | `/api/alunos.php` | Cadastrar |
| `PUT` | `/api/alunos.php?id=1` | Atualizar |
| `DELETE` | `/api/alunos.php?id=1` | Remover |

### Planos
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/api/planos.php` | Listar todos |
| `GET` | `/api/planos.php?ativos` | Apenas ativos |
| `POST` | `/api/planos.php` | Criar |
| `PUT` | `/api/planos.php?id=1` | Editar |
| `DELETE` | `/api/planos.php?id=1` | Desativar |

### Matrículas
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/api/matriculas.php` | Listar todas |
| `GET` | `/api/matriculas.php?aluno=1` | Matrículas de um aluno |
| `POST` | `/api/matriculas.php` | Matricular aluno |
| `PUT` | `/api/matriculas.php?id=1` | Ativar/desativar |
| `DELETE` | `/api/matriculas.php?id=1` | Remover |

> Os demais endpoints (aparelhos, professores, treinos, itens, vínculos) seguem o mesmo padrão REST.

***

## 📐 Regras de Negócio

> Implementadas diretamente no banco de dados e na API.

1. **1 plano ativo por aluno** — Garantido por **Trigger MySQL** (`trg_matricula_unica`). Tentar matricular um aluno que já possui plano ativo retorna erro HTTP 409.
2. **Vínculo único aluno-treino** — Constraint `UNIQUE KEY` na tabela `aluno_treino` impede duplicatas.
3. **Um aluno pode ter múltiplos treinos** — Relação N:N via tabela `aluno_treino`.
4. **Data fim calculada automaticamente** — Com base na duração em meses do plano escolhido.
5. **Exclusão em cascata** — Remover um aluno exclui automaticamente suas matrículas e vínculos de treino (`ON DELETE CASCADE`).

***

## 🛡️ Segurança

- Todas as queries utilizam **PDO com Prepared Statements** (proteção contra SQL Injection)
- Validação de campos obrigatórios em todos os endpoints
- Respostas com códigos HTTP semânticos (`201`, `404`, `409`, `422`...)
- Headers CORS configurados nas APIs

***

## 🧪 Exemplo de Uso da API

**Cadastrar um aluno:**
```bash
curl -X POST http://localhost/corpo-e-mente/api/alunos.php \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João da Silva",
    "cpf": "12345678901",
    "dataNascimento": "1995-03-15",
    "telefone": "(11) 99999-8888"
  }'
```

**Resposta:**
```json
{
  "mensagem": "Aluno cadastrado com sucesso!",
  "id": 1
}
```

***

## 🤝 Como Contribuir

1. Faça um **fork** do projeto
2. Crie uma branch: `git checkout -b feature/minha-feature`
3. Commit suas mudanças: `git commit -m 'feat: adiciona minha feature'`
4. Push para a branch: `git push origin feature/minha-feature`
5. Abra um **Pull Request**

***

## 📄 Licença

Este projeto está sob a licença **MIT**. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

***

<div align="center">
  Desenvolvido com base em diagramas UML (Casos de Uso e Classes)
</div>

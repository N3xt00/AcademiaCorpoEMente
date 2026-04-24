# 🏋️ SaaS Gestão de Academias — "Corpo e Mente"

Sistema completo de gerenciamento de academia com interface HTML, CRUD em PHP e banco MySQL.

---

## 📁 Estrutura do Projeto

```
corpo-e-mente/
├── index.html              ← Interface principal (Single Page App)
├── banco.sql               ← Script SQL para criar o banco
├── README.md               ← Este arquivo
├── includes/
│   └── conexao.php         ← Configuração PDO + helpers
└── api/
    ├── alunos.php          ← CRUD de Alunos
    ├── planos.php          ← CRUD de Planos
    ├── aparelhos.php       ← CRUD de Aparelhos
    ├── professores.php     ← CRUD de Professores
    ├── treinos.php         ← CRUD de Treinos
    ├── matriculas.php      ← CRUD de Matrículas
    ├── itens_treino.php    ← CRUD de Itens do Treino
    └── aluno_treino.php    ← Vínculo Aluno ↔ Treino
```

---

## ⚙️ Requisitos

- PHP 8.0+ com extensão PDO/MySQL
- MySQL 8.0+ ou MariaDB 10.5+
- Servidor web: Apache (XAMPP/WAMP) ou Nginx

---

## 🚀 Instalação (XAMPP)

### 1. Copiar os arquivos
```bash
# Cole a pasta dentro do htdocs do XAMPP
C:\xampp\htdocs\corpo-e-mente\
```

### 2. Criar o banco de dados
1. Acesse **http://localhost/phpmyadmin**
2. Clique em **"Importar"**
3. Selecione o arquivo `banco.sql`
4. Clique em **"Executar"**

### 3. Configurar a conexão
Edite `includes/conexao.php` se necessário:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // ← seu usuário MySQL
define('DB_PASS', '');          // ← sua senha MySQL
define('DB_NAME', 'corpo_e_mente');
define('DB_PORT', 3306);
```

### 4. Acessar o sistema
Abra o navegador e acesse:
```
http://localhost/corpo-e-mente/index.html
```

---

## 🔗 Endpoints da API (REST)

Todos os endpoints aceitam JSON e retornam JSON.

| Método | URL                          | Ação                        |
|--------|------------------------------|-----------------------------|
| GET    | api/alunos.php               | Listar todos os alunos      |
| GET    | api/alunos.php?id=1          | Buscar aluno por ID         |
| GET    | api/alunos.php?search=João   | Buscar aluno por nome/CPF   |
| POST   | api/alunos.php               | Cadastrar novo aluno        |
| PUT    | api/alunos.php?id=1          | Atualizar aluno             |
| DELETE | api/alunos.php?id=1          | Remover aluno               |
| GET    | api/planos.php               | Listar planos               |
| GET    | api/planos.php?ativos        | Listar apenas planos ativos |
| POST   | api/planos.php               | Criar plano                 |
| PUT    | api/planos.php?id=1          | Editar plano                |
| DELETE | api/planos.php?id=1          | Desativar plano             |
| GET    | api/matriculas.php           | Listar matrículas           |
| GET    | api/matriculas.php?aluno=1   | Matrículas de um aluno      |
| POST   | api/matriculas.php           | Matricular aluno            |
| PUT    | api/matriculas.php?id=1      | Ativar/desativar matrícula  |
| DELETE | api/matriculas.php?id=1      | Remover matrícula           |
| GET    | api/aparelhos.php            | Listar aparelhos            |
| POST   | api/aparelhos.php            | Cadastrar aparelho          |
| PUT    | api/aparelhos.php?id=1       | Editar aparelho             |
| DELETE | api/aparelhos.php?id=1       | Remover aparelho            |
| GET    | api/treinos.php              | Listar treinos              |
| GET    | api/treinos.php?id=1         | Treino com itens e alunos   |
| POST   | api/treinos.php              | Criar treino                |
| PUT    | api/treinos.php?id=1         | Editar treino               |
| DELETE | api/treinos.php?id=1         | Remover treino              |
| POST   | api/itens_treino.php         | Adicionar aparelho a treino |
| DELETE | api/itens_treino.php?id=1    | Remover item do treino      |
| POST   | api/aluno_treino.php         | Vincular aluno a treino     |
| DELETE | api/aluno_treino.php?id=1    | Desvincular aluno           |
| GET    | api/professores.php          | Listar professores          |
| POST   | api/professores.php          | Criar professor             |
| PUT    | api/professores.php?id=1     | Editar professor            |
| DELETE | api/professores.php?id=1     | Remover professor           |

---

## 📋 Regras de Negócio Implementadas

1. **1 plano ativo por aluno** — Trigger MySQL impede matrícula dupla
2. **Vínculo único aluno-treino** — Constraint UNIQUE evita duplicatas
3. **Um aluno pode ter múltiplos treinos** — Tabela N:N aluno_treino
4. **Data fim calculada automaticamente** — Com base na duração do plano
5. **Exclusão em cascata** — Deletar aluno remove matrículas e vínculos

---

## 🎨 Funcionalidades da Interface

- ✅ Dashboard com KPIs e resumos
- ✅ CRUD completo de Alunos, Planos, Aparelhos, Professores
- ✅ Criação de Treinos com aparelhos (séries, repetições, carga)
- ✅ Vinculação de treinos a alunos (múltiplos treinos por aluno)
- ✅ Gestão de Matrículas com regra de 1 ativo por aluno
- ✅ Modo escuro / claro
- ✅ Responsivo (mobile-first)
- ✅ Toasts de feedback
- ✅ Busca e filtros


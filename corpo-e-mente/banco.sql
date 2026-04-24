-- ============================================================
-- Banco de Dados: SaaS Gestão de Academias "Corpo e Mente"
-- ============================================================

CREATE DATABASE IF NOT EXISTS corpo_e_mente
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE corpo_e_mente;

-- ------------------------------------------------------------
-- Tabela: Plano
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS plano (
  idPlano       INT AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(100) NOT NULL,
  valor         DECIMAL(10,2) NOT NULL,
  duracaoMeses  INT NOT NULL,
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  criadoEm      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: Aluno
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno (
  idAluno        INT AUTO_INCREMENT PRIMARY KEY,
  nome           VARCHAR(150) NOT NULL,
  cpf            VARCHAR(14) NOT NULL UNIQUE,
  dataNascimento DATE NOT NULL,
  telefone       VARCHAR(20),
  criadoEm       DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: Matricula (apenas 1 plano ativo por aluno)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS matricula (
  idMatricula  INT AUTO_INCREMENT PRIMARY KEY,
  idAluno      INT NOT NULL,
  idPlano      INT NOT NULL,
  dataInicio   DATE NOT NULL,
  dataFim      DATE NOT NULL,
  ativo        TINYINT(1) NOT NULL DEFAULT 1,
  criadoEm     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idAluno) REFERENCES aluno(idAluno) ON DELETE CASCADE,
  FOREIGN KEY (idPlano) REFERENCES plano(idPlano) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ✅ Trigger garante apenas 1 matrícula ativa por aluno
DELIMITER $$
CREATE TRIGGER trg_matricula_unica
BEFORE INSERT ON matricula
FOR EACH ROW
BEGIN
  DECLARE cnt INT;
  SELECT COUNT(*) INTO cnt
    FROM matricula
   WHERE idAluno = NEW.idAluno AND ativo = 1;
  IF cnt > 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Aluno já possui um plano ativo.';
  END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------------
-- Tabela: Aparelho
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aparelho (
  idAparelho          INT AUTO_INCREMENT PRIMARY KEY,
  nome                VARCHAR(100) NOT NULL,
  situacaoConservacao VARCHAR(50) NOT NULL DEFAULT 'Bom',
  criadoEm            DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: Professor
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS professor (
  idProfessor INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(150) NOT NULL,
  criadoEm    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: Treino
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS treino (
  idTreino    INT AUTO_INCREMENT PRIMARY KEY,
  nomeTreino  VARCHAR(100) NOT NULL,
  idProfessor INT,
  dataCriacao DATE NOT NULL DEFAULT (CURRENT_DATE),
  criadoEm    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idProfessor) REFERENCES professor(idProfessor) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: ItemTreino (aparelhos vinculados ao treino)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS item_treino (
  idItemTreino INT AUTO_INCREMENT PRIMARY KEY,
  idTreino     INT NOT NULL,
  idAparelho   INT NOT NULL,
  series       INT NOT NULL DEFAULT 3,
  repeticoes   INT NOT NULL DEFAULT 12,
  carga        VARCHAR(20),
  FOREIGN KEY (idTreino)   REFERENCES treino(idTreino) ON DELETE CASCADE,
  FOREIGN KEY (idAparelho) REFERENCES aparelho(idAparelho) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabela: aluno_treino (vincula treino ao aluno — N:N)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_treino (
  idAlunoTreino INT AUTO_INCREMENT PRIMARY KEY,
  idAluno       INT NOT NULL,
  idTreino      INT NOT NULL,
  vinculadoEm   DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_aluno_treino (idAluno, idTreino),
  FOREIGN KEY (idAluno)  REFERENCES aluno(idAluno) ON DELETE CASCADE,
  FOREIGN KEY (idTreino) REFERENCES treino(idTreino) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dados de exemplo
-- ------------------------------------------------------------
INSERT INTO plano (nome, valor, duracaoMeses) VALUES
  ('Mensal',    89.90,  1),
  ('Trimestral',229.90, 3),
  ('Semestral', 399.90, 6),
  ('Anual',     699.90, 12);

INSERT INTO professor (nome) VALUES
  ('Prof. Carlos Silva'),
  ('Prof. Ana Souza');

INSERT INTO aparelho (nome, situacaoConservacao) VALUES
  ('Supino Reto',      'Bom'),
  ('Leg Press',        'Ótimo'),
  ('Puxador Frente',   'Bom'),
  ('Esteira',          'Manutenção'),
  ('Bicicleta Ergométrica', 'Ótimo'),
  ('Crossover',        'Bom');

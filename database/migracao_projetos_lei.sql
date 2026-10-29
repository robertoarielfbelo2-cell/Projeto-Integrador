-- VOX POPULIS | Migracao dos projetos de lei
-- Para quem JA importou o vox_populis_banco.sql e quer manter os dados.
-- Quem for importar o vox_populis_banco.sql do zero NAO precisa rodar este arquivo.
-- Pode ser executado mais de uma vez sem erro.

USE `projetointegrador`;
SET NAMES utf8mb4;

-- 1. Situacoes fixas da tramitacao
CREATE TABLE IF NOT EXISTS `situacao_projeto` (
 `id_situacao` INT NOT NULL AUTO_INCREMENT,
 `nome_da_situacao` VARCHAR(30) NOT NULL,
 `descricao` VARCHAR(200) DEFAULT NULL,
 PRIMARY KEY (`id_situacao`),
 UNIQUE KEY `uq_situacao_nome` (`nome_da_situacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `situacao_projeto` (`id_situacao`, `nome_da_situacao`, `descricao`) VALUES
 (1, 'Em tramitação', 'Projeto protocolado e em discussao na Camara'),
 (2, 'Aprovado', 'Projeto aprovado em plenario'),
 (3, 'Rejeitado', 'Projeto rejeitado em plenario'),
 (4, 'Arquivado', 'Projeto arquivado sem votacao');

-- 2. Projetos de lei publicados pelos vereadores
CREATE TABLE IF NOT EXISTS `projeto_lei` (
 `id_projeto` INT NOT NULL AUTO_INCREMENT,
 `titulo` VARCHAR(150) NOT NULL,
 `ementa` VARCHAR(500) NOT NULL,
 `texto` TEXT NOT NULL,
 `anexo_pdf` VARCHAR(255) DEFAULT NULL,
 `data_publicacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `id_situacao` INT NOT NULL DEFAULT 1,
 `id_vereador` INT NOT NULL,
 PRIMARY KEY (`id_projeto`),
 KEY `idx_projeto_situacao` (`id_situacao`),
 KEY `idx_projeto_vereador` (`id_vereador`),
 KEY `idx_projeto_data` (`data_publicacao`),
 CONSTRAINT `fk_projeto_situacao` FOREIGN KEY (`id_situacao`) REFERENCES `situacao_projeto` (`id_situacao`),
 CONSTRAINT `fk_projeto_vereador` FOREIGN KEY (`id_vereador`) REFERENCES `vereador` (`id_vereador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Apoio dos cidadaos aos projetos (um apoio por cidadao por projeto)
CREATE TABLE IF NOT EXISTS `apoio_projeto` (
 `id_cidadao` INT NOT NULL,
 `id_projeto` INT NOT NULL,
 `data_apoio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id_cidadao`, `id_projeto`),
 KEY `idx_apoio_projeto_projeto` (`id_projeto`),
 CONSTRAINT `fk_apoio_projeto_cidadao` FOREIGN KEY (`id_cidadao`) REFERENCES `cidadao` (`id_cidadao`) ON DELETE CASCADE,
 CONSTRAINT `fk_apoio_projeto_projeto` FOREIGN KEY (`id_projeto`) REFERENCES `projeto_lei` (`id_projeto`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

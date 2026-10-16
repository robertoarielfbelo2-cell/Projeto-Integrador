-- VOX POPULIS | Migracao do modulo do vereador
-- Para quem JA importou o vox_populis_banco.sql e quer manter os dados.
-- Quem for importar o vox_populis_banco.sql do zero NAO precisa rodar este arquivo.
-- Pode ser executado mais de uma vez sem erro.

USE `projetointegrador`;

-- 1. Credenciais de acesso do vereador (definidas pelo administrador)
ALTER TABLE `vereador`
  ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT NULL AFTER `senha`,
  ADD UNIQUE KEY IF NOT EXISTS `uq_vereador_email` (`email`);

-- 2. Respostas do vereador as indicacoes encaminhadas a ele
CREATE TABLE IF NOT EXISTS `resposta_indicacao` (
 `id_resposta` INT NOT NULL AUTO_INCREMENT,
 `texto` VARCHAR(1000) NOT NULL,
 `data_resposta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `id_indicacao` INT NOT NULL,
 `id_vereador` INT NOT NULL,
 PRIMARY KEY (`id_resposta`),
 KEY `idx_resposta_indicacao` (`id_indicacao`),
 KEY `idx_resposta_vereador` (`id_vereador`),
 CONSTRAINT `fk_resposta_indicacao` FOREIGN KEY (`id_indicacao`) REFERENCES `indicacao` (`id_indicacao`) ON DELETE CASCADE,
 CONSTRAINT `fk_resposta_vereador` FOREIGN KEY (`id_vereador`) REFERENCES `vereador` (`id_vereador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

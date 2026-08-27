CREATE TABLE partido (
    id_partido INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(5) NOT NULL UNIQUE,
    numero INTEGER NOT NULL UNIQUE
);

INSERT INTO partido (nome, sigla, numero) VALUES
('Movimento Democrático Brasileiro', 'MDB', 15),
('Partido Democrático Trabalhista', 'PDT', 12),
('Partido Liberal', 'PL', 22),
('Podemos', 'PODE', 20),
('Progressistas', 'PP', 11),
('Partido Socialista Brasileiro', 'PSB', 40),
('Partido Social Democrático', 'PSD', 55),
('Partido Socialismo e Liberdade', 'PSOL', 50),
('Partido dos Trabalhadores', 'PT', 13),
('Republicanos', 'REPUBLICANOS', 10),
('Solidariedade', 'SD', 77),
('União Brasil', 'UNIÃO', 44),
('Agir', 'AGIR', 36);
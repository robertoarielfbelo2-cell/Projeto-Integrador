-- 1. TABELA: PARTIDO
CREATE TABLE partido (
    id_partido INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    sigla TEXT NOT NULL,
    numero INTEGER
);

-- 2. TABELA: STATUS_INDICACAO
CREATE TABLE status_indicacao (
    id_status INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_status TEXT NOT NULL,
    descricao TEXT
);

-- 3. TABELA: CIDADAO
CREATE TABLE cidadao (
    id_cidadao INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cpf TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    telefone TEXT,
    data_nascimento TEXT,
    senha TEXT NOT NULL
);
-- 4. TABELA: POLITICO
CREATE TABLE politico (
    id_politico INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cargo TEXT NOT NULL,
    id_partido INTEGER NOT NULL,
    FOREIGN KEY (id_partido) REFERENCES partido (id_partido)
);

-- 5. TABELA: INDICACAO
CREATE TABLE indicacao (
    id_indicacao INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    descricao TEXT NOT NULL,
    data_criacao TEXT DEFAULT CURRENT_TIMESTAMP,
    id_cidadao INTEGER NOT NULL,
    id_status INTEGER NOT NULL,
    id_politico INTEGER,
    FOREIGN KEY (id_cidadao) REFERENCES cidadao (id_cidadao),
    FOREIGN KEY (id_status) REFERENCES status_indicacao (id_status),
    FOREIGN KEY (id_politico) REFERENCES politico (id_politico)
);

-- 6. TABELA: APOIO (Relacionamento N:N entre Cidadão e Indicação)
CREATE TABLE apoio (
    id_cidadao INTEGER NOT NULL,
    id_indicacao INTEGER NOT NULL,
    data_apoio TEXT DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cidadao, id_indicacao),
    FOREIGN KEY (id_cidadao) REFERENCES cidadao (id_cidadao),
    FOREIGN KEY (id_indicacao) REFERENCES indicacao (id_indicacao)
);
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 09/10/2026 às 15:38
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projetointegrador`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `id_administrador` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `senha` varchar(100) NOT NULL,
  `nivel_de_acesso` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `apoio`
--

CREATE TABLE `apoio` (
  `id_cidadao` int(11) NOT NULL,
  `id_indicacao` int(11) NOT NULL,
  `data_` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cidadao`
--

CREATE TABLE `cidadao` (
  `id_cidadao` int(11) NOT NULL,
  `cpf` char(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` char(11) DEFAULT NULL,
  `senha` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cidadao`
--

INSERT INTO `cidadao` (`id_cidadao`, `cpf`, `email`, `telefone`, `senha`, `nome`, `data_nascimento`) VALUES
(1, '06140881196', 'robertoariel@email.com', '123456789', 'sua_senha_aqui', 'Roberto', '2008-02-14'),
(2, '11111111111', 'email@gmail.com', '12345678910', 'senha_porra', 'Fulano', '0000-00-00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentario`
--

CREATE TABLE `comentario` (
  `id_comentario` int(11) NOT NULL,
  `data_` datetime NOT NULL DEFAULT current_timestamp(),
  `texto` varchar(500) NOT NULL,
  `id_cidadao` int(11) NOT NULL,
  `id_indicacao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `indicacao`
--

CREATE TABLE `indicacao` (
  `id_indicacao` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` varchar(1000) NOT NULL,
  `foto_ou_anexo` varchar(255) DEFAULT NULL,
  `data_de_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `id_localizacao` int(11) NOT NULL,
  `id_administrador` int(11) DEFAULT NULL,
  `id_status` int(11) NOT NULL,
  `id_cidadao` int(11) NOT NULL,
  `id_vereador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `localizacao`
--

CREATE TABLE `localizacao` (
  `id_localizacao` int(11) NOT NULL,
  `cep` char(8) NOT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `estado` varchar(2) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `rua` varchar(150) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `partido`
--

CREATE TABLE `partido` (
  `id_partido` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sigla` varchar(20) DEFAULT NULL,
  `numero` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `partido`
--

INSERT INTO `partido` (`id_partido`, `nome`, `sigla`, `numero`) VALUES
(4, 'Partido dos Trabalhadores', 'PT', 13),
(5, 'Agir', 'AGIR', 36),
(6, 'Movimento Democrático Brasileiro', 'MDB', 15),
(7, 'Partido Novo', 'NOVO', 30),
(9, ' Partido Democrático Trabalhista', 'PDT', 12),
(10, 'Partido Liberal', 'PL', 22),
(12, 'Podemos', 'PODE', 20),
(13, 'Progressistas', 'PP', 11),
(14, 'Partido Socialista Brasileiro', 'PSB', 40),
(15, 'Partido Social Democrático', 'PSD', 55),
(17, 'Partido Socialismo e Liberdade', 'PSOL', 50),
(18, 'Republicanos', 'REPUBLICANOS', 10),
(19, 'Solidariedade', 'SD', 77),
(20, 'União Brasil', 'UNIÃO', 44),
(21, 'Teste', 'T', 100);

-- --------------------------------------------------------

--
-- Estrutura para tabela `status_indicacao`
--

CREATE TABLE `status_indicacao` (
  `id_status` int(11) NOT NULL,
  `nome_do_status` enum('Em andamento','Em analise','Recusado','Aprovado','Arquivado') NOT NULL DEFAULT 'Em analise',
  `descricao` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vereador`
--

CREATE TABLE `vereador` (
  `id_vereador` int(11) NOT NULL,
  `nome_parlamentar` varchar(100) NOT NULL,
  `data_inicio_mandato` date NOT NULL,
  `data_termino_mandato` date NOT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `foto` varchar(500) DEFAULT NULL,
  `id_partido` int(11) NOT NULL,
  `senha` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vereador`
--

INSERT INTO `vereador` (`id_vereador`, `nome_parlamentar`, `data_inicio_mandato`, `data_termino_mandato`, `descricao`, `foto`, `id_partido`, `senha`) VALUES
(2, 'Amália Tortato', '2025-01-01', '2028-01-01', 'Natural de Telêmaco Borba (PR), Amália Tortato nasceu em 25 de outubro de 1984. Formada em Engenharia Mecânica pela Universidade Federal do Paraná (UFPR), trabalhou como comissária de voo e na iniciativa privada antes de ingressar na política. Foi eleita vereadora de Curitiba em 2020 e reeleita em 2024 pelo partido Novo, com 6.206 votos, mais que o dobro da votação obtida na primeira eleição, quando recebeu 3.092 votos.', 'https://www.cmc.pr.gov.br/vereadores/amalia-tortato/@@images/foto-180-aead84b3d5c126c3b9c16ba8941f757d.png', 7, NULL),
(3, 'Andressa Bianchessi', '2025-01-01', '2028-01-01', 'Natural de Curitiba (PR), Andressa Eli Bianchessi de Oliveira nasceu no dia 10 de janeiro de 1983. É casada, tem dois filhos e três pets. Ativista da causa animal, é formada em Jornalismo pela Uniandrade. “Braço direito” do deputado federal Matheus Laiola (União-PR), foi chefe de gabinete do parlamentar. É coordenadora da iniciativa “Paraná Contra Maus-Tratos”.', 'https://www.cmc.pr.gov.br/vereadores/andressa-bianchessi/@@images/foto-180-1d74014e3cad95ad8cb360877b20d136.jpeg', 20, NULL),
(4, 'Angelo Vanhoni', '2025-01-01', '0000-00-00', 'Angelo Carlos Vanhoni é natural de Paranaguá (PR) e começou sua trajetória política como vereador de Curitiba em 1989, sendo reeleito para um segundo mandato antes de deixar a Câmara Municipal, em 1995, para se tornar deputado estadual. Após três mandatos consecutivos na Assembleia Legislativa do Paraná (Alep), de 1995 a 2007, Vanhoni foi eleito deputado federal, cargo que ocupou por duas legislaturas, de 2007 a 2015.', 'https://www.cmc.pr.gov.br/vereadores/angelo-vanhoni/@@images/foto-180-7f4839f989770ac718a152d88c4dc380.jpeg', 4, NULL),
(5, 'Beto Moraes', '2025-01-01', '2028-01-01', 'Conhecido pelo trabalho de assessoria que prestou por mais de 10 anos ao deputado estadual Mauro Moraes (PSDB), Gilberto Pires dos Santos, conhecido como Beto Moraes, foi reeleito em 2024 para o sexto mandato consecutivo como vereador de Curitiba, com 10.142 votos. Nascido em Assis Chateaubriand (PR), sua família mudou-se para a capital quando ele era criança, passando a morar no bairro Xaxim.', 'https://www.cmc.pr.gov.br/vereadores/beto-moraes/@@images/foto-180-d6f2c0ed273c875f8793777a2e2e4208.png', 15, NULL),
(6, 'Bruno Rossi', '0000-00-00', '2028-01-01', 'Natural de Curitiba (PR), Bruno Pellegrino da Rocha Rossi foi eleito vereador da capital ao disputar sua primeira eleição. Advogado, é casado e tem uma filha. É o único vereador do partido Agir na 19ª Legislatura, obtendo 3.824 votos nas Eleições 2024. Durante a campanha, declarou-se o escolhido número um do influenciador Pablo Marçal em Curitiba e “defensor da liberdade”.', 'https://www.cmc.pr.gov.br/vereadores/bruno-rossi/@@images/foto-180-afd1acd345a67a41e6b142bcd6513fc3.jpeg', 5, NULL),
(7, ' Camilla Gonda', '2025-01-01', '2028-01-01', 'Natural de Curitiba (PR), Camilla de Moraes Gonda nasceu em 14 de outubro de 2000, foi criada no bairro Capão Raso, na região sul, onde reside até hoje. Em seu primeiro mandato, é a vereadora mais jovem da 19ª Legislatura, tendo tomado posse aos 24 anos de idade. É formada em Direito pela PUC-PR (Pontifícia Universidade Católica do Paraná) e mestranda em Ciência Política pela UFPR (Universidade Federal do Paraná), onde também atua como pesquisadora.', 'https://www.cmc.pr.gov.br/vereadores/camilla-gonda/@@images/foto-180-a6b967f771458e52314dbec8445de774.jpeg', 14, NULL),
(8, 'Carlise Kwiatkowski', '2025-01-01', '2028-01-01', 'Carlise Aparecida Kwiatkowski nasceu em Cascavel (PR), no dia 11 de outubro de 1975. Viúva, ela soma mais de 20 anos de trabalhos dedicados às causas sociais e defende uma política voltada para “Deus, Pátria, Família e Liberdade”. Foi eleita em 2024 para seu primeiro mandato na Câmara de Curitiba com 6.384 votos pelo PL (Partido Liberal).\n\nBacharel em Direito pela Universidade Tuiuti do Paraná, tem especialização em Business Study Tour em Management & Leadership pela ESIC Business & Marketing Sc', 'https://www.cmc.pr.gov.br/vereadores/carlise-kwiatkowski/@@images/foto-180-cf4e942bebc220510c661c974a1a244f.jpeg', 10, NULL),
(9, 'Gustavo Silveira da Costa ', '2025-01-01', '2028-01-01', 'Gustavo Silveira da Costa nasceu em Curitiba (PR) em 24 de janeiro de 1990. É cristão, casado e pai de três filhos. Influenciador digital, foi eleito vereador pelo União Brasil em 2024 para o seu primeiro mandato. Filiou-se ao Podemos em janeiro de 2026. Com 15.014 votos, foi o terceiro vereador mais votado naquele pleito. Antes de ser proprietário do canal no YouTube \"Perdeu Piá”, que possui 820 mil inscritos, foi policial militar por cerca de sete anos (entre 2014 e 2021).', 'https://www.cmc.pr.gov.br/vereadores/vereador-da-costa/@@images/foto-180-dc5205be45d3053e3d03fe138cac1d1f.jpeg', 12, NULL),
(10, 'Dalton Borba', '2025-01-01', '2028-01-01', 'Dalton José Borba soma 21 anos de docência na área de Direito Constitucional. Ativista e defensor da educação desde a graduação no curso de Direito na Universidade Federal do Paraná (UFPR), em 1987, acredita que a democratização da educação é um agente de transformação social. Mestre em Direito do Estado pela UFPR, atualmente leciona no Centro Universitário Curitiba (UniCuritiba). Além de dar aulas, coordena grupos de pesquisa e extensão nas áreas de Filosofia Política, Teorias da Justiça, Direi', 'https://www.cmc.pr.gov.br/vereadores/dalton-borba/@@images/foto-180-c5c653bbab084fdd0aa32c4ab7cedc45.png', 19, NULL),
(11, 'Delegada Tathiana Guzella', '2025-01-01', '2028-01-01', 'Tathiana Laiz Guzella é natural de Caçador (SC). Casada com o deputado estadual Delegado Tito Barichello (União), tem dois filhos. Membro da Polícia Civil, ela possui mestrado em Direito Penal e tem dedicado sua carreira às pautas da segurança pública. Durante sua campanha, defendeu a adoção de equipamentos de reconhecimento facial nas escolas de Curitiba.', 'https://www.cmc.pr.gov.br/vereadores/delegada-tathiana-guzella/@@images/foto-180-5d4d147ee41552bda95aa2f4a1a805a8.jpeg', 10, NULL),
(12, 'Eder Borges', '2025-01-01', '2028-01-01', 'Curitibano, Eder Fabiano Borges Adão é graduando em Gestão Pública pela Faculdade Estácio e cantor profissional. Autodeclarado de direita, seu ativismo político começou em 2014, “ao perceber o avanço do comunismo no Brasil, que corria sério risco de chegar ao estado que chegou a Venezuela, por exemplo”. Foi no mesmo ano que ele fundou o Direita Curitiba, um dos primeiros movimentos conservadores do Brasil, reunindo apoiadores e promovendo manifestações pelo impeachment da então presidente Dilma.', 'https://www.cmc.pr.gov.br/vereadores/eder-borges/@@images/foto-180-9b76d5a49e4ccc78c93f3fb5654e0189.png', 7, NULL),
(13, 'Fernando Klinger', '2025-01-01', '2028-01-01', 'Natural de Curitiba (PR), Fernando Henrique Ferreira Klinger é casado e tem dois filhos. Foi eleito vereador ao participar da sua primeira disputa eleitoral, em 2024, quando recebeu 6.288 votos pelo Partido Liberal (PL). É publicitário, formado em Comunicação Social - Publicidade e Propaganda pela PUC-PR (Pontifícia Universidade Católica do Paraná).\n\nMembro da Igreja do Evangelho Quadrangular, é pastor, gestor público e se declara de direita. Antes de ocupar o cargo na CMC, foi assessor parlamen', 'https://www.cmc.pr.gov.br/vereadores/fernando-klinger/@@images/foto-180-ae53ae1a3d299228e428f8ff8068f508.jpeg', 10, NULL),
(14, 'Giorgia Tais Xavier Prates', '2025-01-01', '2028-01-01', 'Natural da Zona Leste de São Paulo (SP), Giorgia Tais Xavier Prates é casada e tem 10 bichinhos de estimação. A vereadora se autodefine como mulher preta, lésbica e periférica. Ela também se coloca como ativista dos direitos humanos e da causa animal. Fotojornalista, é formada em Fotografia pela Universidade Tuiuti do Paraná. Sua atuação na área lhe rendeu prêmios em festivais renomados, como o Festival de Cannes (França) com o case “O Uniforme Que Nunca Existiu”.', 'https://www.cmc.pr.gov.br/vereadores/giorgia-prates-mandata-preta/@@images/foto-180-377a20aa58c8982fd63851c8179f6415.jpeg', 4, NULL),
(15, 'Guilherme Kilter', '2025-01-01', '2028-01-01', 'Natural de São Paulo (SP), Guilherme Ferreira Kilter Lira vive em Curitiba desde os 2 anos de idade. Ele é o vereador mais jovem da 19º Legislatura e está em seu primeiro mandato. Formado em Relações Internacionais pela UniCuritiba, é empresário, líder da escola de formação política RenovaBR e presidente da Juventude do Partido Novo no Paraná.\n\nKilter foi eleito em 2024 pelo Partido Novo com 16.664 votos, sendo o segundo vereador mais votado. Na campanha eleitoral, contou com o apoio do ex-deput', 'https://www.cmc.pr.gov.br/vereadores/guilherme-kilter/@@images/foto-180-fe11b6dfbb8625f226f131bcc03edaf5.jpeg', 7, NULL),
(16, 'Hernani da Silva', '2025-01-01', '2028-01-01', 'Natural de São Paulo (SP), Hernani da Silva é casado. Ele se mudou jovem para Curitiba, crescendo no Bairro Alto, onde reside há quase 60 anos. Atuou como liderança de pequenos agricultores da Região Metropolitana de Curitiba até criar o Feirão do Produtor em 1996. Empresário, é atuante no trabalho social e foi um dos fundadores do Grupo Voluntário Anjos do Bairro, em 2000.\n\nEm 2024 foi reeleito para seu segundo mandato na Câmara de Curitiba com 6.267 votos pelo Republicanos. Nos últimos quatro ', 'https://www.cmc.pr.gov.br/vereadores/hernani/@@images/foto-180-58c664c6cd4e0bae8860bfe91976fffc.png', 18, NULL),
(17, 'Indiara Barbosa', '2025-01-01', '2028-01-01', 'Vereadora mais votada em 2020, com 12.147 votos, e a quarta mulher mais votada na história de Curitiba, Indiara Barbosa Custódio foi reconduzida à Câmara Municipal em 2024 com 9.106 votos pelo Partido Novo. Ela é natural de Umuarama (PR), casada e tem dois filhos.\n\nFormada em Administração e Contabilidade pela Universidade Federal do Paraná (UFPR), ela também tem MBA em Gestão Estratégica pela Fundação Getúlio Vargas (FGV). Trabalhou como auditora contábil em uma empresa multinacional até se tor', 'https://www.cmc.pr.gov.br/vereadores/indiara-barbosa/@@images/foto-180-40dc30e07d80e88c10457cb5b9e27425.png', 7, NULL),
(18, 'Jasson Goulart', '2025-01-01', '2028-01-01', 'Com uma das maiores votações da história da capital do Paraná, Jasson Goulart recebeu 21.684 votos em 2024 pelo Partido Republicanos. Ele é nascido em Curitiba (PR), filho de policial militar e cresceu no bairro Boqueirão, onde vive até hoje. É casado, pai de duas filhas e avô de dois netos.\n\nGraduado em Jornalismo pela Universidade Tuiuti do Paraná, o jornalista e radialista iniciou sua carreira na comunicação em 1991, na Rádio Paraná, como apresentador esportivo. Com 30 anos de carreira, foi a', 'https://www.cmc.pr.gov.br/vereadores/jasson-goulart/@@images/foto-180-c5a9944ee2041c4feb53cc2d8a326d68.jpeg', 18, NULL),
(19, 'João Bettega', '2025-01-01', '2028-01-01', 'Nascido na capital paranaense, João Victor Mattos Leão Bettega, conhecido como João Bettega, é empresário, solteiro e formado em Direito pela Pontifícia Universidade Católica do Paraná (PUC-PR), onde presidiu o centro acadêmico de sua faculdade.\n\nCriador de conteúdo político nas redes sociais, Bettega foi eleito pelo partido União Brasil com 12.346 votos nas Eleições 2024, sendo o quinto mais votado e o terceiro mais jovem da legislatura. Influenciador e membro declarado do Movimento Brasil Livr', 'https://www.cmc.pr.gov.br/vereadores/joao-bettega/@@images/foto-180-9499bcce6c92619785f73bc820f78f25.png', 10, NULL),
(20, 'Laís Leão', '2025-01-01', '2028-01-01', 'Eleita vereadora de Curitiba com 6.954 votos pelo PDT para seu primeiro mandato na Câmara de Curitiba, Laís Rocha Leão é natural da capital paranaense. É arquiteta e urbanista, graduada pela Universidade Tecnológica Federal do Paraná (UTFPR), mestre em Gestão Urbana pela Pontifícia Universidade Católica do Paraná (PUC-PR) e doutoranda em Gestão Urbana pela mesma instituição.', 'https://www.cmc.pr.gov.br/vereadores/lais-leao/@@images/foto-180-738d2a4469b0101cc182b9c4a863740f.jpeg', 9, NULL),
(21, 'Leonidas Dias', '2025-01-01', '2028-01-01', 'Reeleito para o segundo mandato como vereador de Curitiba pelo partido Podemos, Leonidas Nery Dias havia sido eleito pela primeira vez em 2020, com 2.704 votos, quando foi o parlamentar mais novo daquela eleição. Nas Eleições 2024, ele quase quadruplicou seu desempenho eleitoral, atingindo 9.835 votos, que foi o maior crescimento eleitoral registrado entre os 18 parlamentares reeleitos.', 'https://www.cmc.pr.gov.br/vereadores/leonidas-dias/@@images/foto-180-490705a5b821887fe8fda354a89a9a83.png', 12, NULL),
(22, 'Luciano Carvalho', '2025-01-01', '2028-01-01', 'Luciano Cruz Carvalho nasceu em Curitiba em 15/09/1978. Graduado em Gestão Pública, com MBA em Contabilidade Pública e Responsabilidade Fiscal, também possui pós-graduações em Políticas Públicas, Direitos Sociais, Marketing Político e Ciência Política. Carvalho afirma residir há mais de 40 no Moradias Augusta, na Cidade Industrial de Curitiba, bairro em que atua como líder comunitário há décadas.', 'https://www.cmc.pr.gov.br/vereadores/luciano-carvalho/@@images/foto-180-dec9544d97940c4e2efb007e60f1e050.png', 13, NULL),
(23, 'Marcos Vieira', '2025-01-01', '2028-01-01', 'Engajado nas lutas sociais dos bairros periféricos e morador da região sul da capital paranaense há mais de 30 anos, Marcos Antônio Vieira despontou como representante da região em 2016, quando conquistou seu primeiro mandato na Câmara de Curitiba. O parlamentar foi reeleito em 2024 para seu terceiro mandato consecutivo, com 10.405 votos, sendo que, nas eleições de 2020, havia sido eleito com 5.826 votos.', 'https://www.cmc.pr.gov.br/vereadores/marcos-vieira/@@images/foto-180-637b8841b3dcf500f2d9a618eaca3394.png', 9, NULL),
(24, 'Matteus Henrique', '2025-01-01', '2028-01-01', 'Matteus Henrique é natural de Curitiba e foi criado entre os bairros CIC, Pinheirinho e Capão Raso. Filho de professora, ele destaca ser fruto da educação pública municipal. É graduado em Direito pela Universidade Federal do Paraná (UFPR), mestre em Direito pela Universidade de Brasília (UnB) e doutorando pela UFPR, onde pesquisa a história negra do Paraná.\n\nAo longo da trajetória acadêmica e do movimento estudantil, foi coordenador-geral e secretário-geral do Diretório Central dos Estudantes da', 'https://www.cmc.pr.gov.br/vereadores/matteus-henrique/@@images/foto-180-5ac6ace6fd7b5cd406e1f6b3e0a566ba.png', 4, NULL),
(25, 'Mauro Bobato', '2025-01-01', '2028-01-01', 'Nascido e criado no bairro Umbará, Carlos Mauro Bobato é filho de Joana Ilandir Bobato e do ex-vereador Geraldo Bobato, conhecido por seu trabalho nas olarias da cidade. Sua esposa, Silvane, é professora. É pai de Thatasha e Maria Valentina. Formado em técnico de Contabilidade, tem experiência na área de construção civil.\n\nO vereador é conhecedor de várias funções dentro do segmento cerâmico (olarias). Também trabalhou como motorista, operador nos serviços gerais e na área administrativa, no ram', 'https://www.cmc.pr.gov.br/vereadores/mauro-bobato/@@images/foto-180-72b0c8b2b4bd562a5a941bb4fb2f948d.png', 13, NULL),
(26, 'Meri Martins', '2025-01-01', '2028-01-01', 'Natural do Rio de Janeiro (RJ), Meri Martins foi eleita para o primeiro mandato nas Eleições 2024, com 7.938 votos pelo partido Republicanos. É esposa do vice-presidente do Republicanos no Paraná e representante da Igreja Universal, Aroldo Martins. Tem dois filhos e uma neta.\n\nEla é graduada em Serviço Social, com pós-graduação em Políticas Sociais e Saúde da Família. A parlamentar trabalha com voluntariado há mais de 40 anos, com missões em países de diversos continentes, sendo eles Honduras,', 'https://www.cmc.pr.gov.br/vereadores/meri-martins/@@images/foto-180-642e274c899e67419860e1f733d074ac.jpeg', 18, NULL),
(27, 'Nori Seto', '2025-01-01', '2028-01-01', 'Com o apoio da comunidade nipônica em Curitiba, Noriyassu Kawahara Seto Takeguma foi reeleito vereador da Câmara de Curitiba em 2024 com 9.329 votos pelo Partido Progressista (PP), dobrando sua votação em relação a 2020, quando recebeu 4.085 votos. Advogado, formado em Direito pela Pontifícia Universidade Católica (PUC-PR), é ex-presidente da Associação Cultural e Beneficente Nipo-Brasileira em Curitiba (Nikkei Curitiba).\n\nNatural de Curitiba (PR), o parlamentar é casado e tem duas filhas. Na ju', 'https://www.cmc.pr.gov.br/vereadores/nori-seto/@@images/foto-180-4f372077433e7387d5cc714b4c599407.png', 13, NULL),
(28, 'Olimpio Araujo Junior', '2025-01-01', '2028-01-01', 'Empresário e criador de conteúdo digital, Olimpio Pereira de Araujo Junior é fundador do canal Mundo Polarizado, no YouTube, que aborda temas como política, geopolítica, negócios e economia. Natural da capital paranaense, ele é casado e tem duas filhas. É graduado em Geografia pela Universidade Estadual de Ponta Grossa (UEPG) e também atua como palestrante, professor e escritor.\n\nOlimpio Araujo Junior tem MBA em Gestão Empresarial com ênfase em Comunicação Integrada de Marketing e MBA em Gestão ', 'https://www.cmc.pr.gov.br/vereadores/olimpio-araujo-junior/@@images/foto-180-abdb956c560f6456ddc83fedffaa5263.jpeg', 10, NULL),
(29, 'Pier Petruzziello', '2025-01-01', '2028-01-01', 'Pierpaolo Petruzziello está no quarto mandato na Câmara de Curitiba. Foi reeleito em 2024 com 8.218 votos pelo Partido Progressistas (PP). Reconhecido pela dedicação à área da pessoa com deficiência, é autor de mais de 110 leis municipais, entre elas a que tornou obrigatória a realização de dois exames clínicos para o diagnóstico precoce do autismo (lei 14.913/2016) e a que criou a Semana de Conscientização sobre o Autismo (lei 14.809/2016).\n\nNatural de Curitiba (PR), é divorciado e tem dois fil', 'https://www.cmc.pr.gov.br/vereadores/pier-petruzziello/@@images/foto-180-0c5855463e239797ba643abcb00b5748.png', 13, NULL),
(30, 'Professor Euler', '2025-01-01', '2028-01-01', 'Natural de São Paulo (SP), Professor Euler nasceu no dia 19 de dezembro de 1973. Tendo conquistado 7.257 votos pelo MDB, Euler de Freitas Silva Junior segue para seu terceiro mandato na Câmara de Curitiba. Ele veio para a capital paranaense em 1995, quando foi convidado pelo Grupo Positivo para ocupar a cadeira de professor titular de Física do curso pré-vestibular. Nesse período, mais de 150 mil dos seus alunos ingressaram no curso superior. Nos primeiros oito anos de mandato parlamentar, Euler', 'https://www.cmc.pr.gov.br/vereadores/professor-euler/@@images/foto-180-473ababad6257d199fce40513029e320.png', 6, NULL),
(31, 'Professora Angela', '2025-01-01', '2028-01-01', 'Angela Alves Machado é natural de São José dos Pinhais (PR). Eleita em 2024 com 6.294 votos, ela é a primeira vereadora do PSOL (Partido Socialismo e Liberdade) da história de Curitiba. É formada em História pela Universidade Tuiuti do Paraná (UTP) e professora da rede pública estadual licenciada.\n\nMãe de três filhos, a vereadora defende pautas ligadas à educação e aparece na icônica fotografia de 29 de abril de 2015, em frente à Polícia Militar, nos protestos do Centro Cívico. Há quatro anos, e', 'https://www.cmc.pr.gov.br/vereadores/professora-angela/@@images/foto-180-897f14e7c8ab47f9d069fac6c7387524.png', 17, NULL),
(32, 'Rafaela Lupion', '2025-01-01', '2028-01-01', 'Ex-administradora da Regional Matriz da Prefeitura de Curitiba, Rafaela Marchiorato Lupion Melo Cantergiani é natural da capital paranaense e está no seu primeiro mandato. Em 2024, foi eleita com 7.259 votos pelo Partido Social Democrático (PSD). Casada, tem uma filha, e representa na CMC uma das famílias mais tradicionais da política paranaense: é bisneta do ex-governador Moisés Lupion, sobrinha do ex-deputado federal Abelardo Lupion e prima do deputado federal Pedro Lupion (PP-PR).\n\nA vereador', 'https://www.cmc.pr.gov.br/vereadores/rafaela-lupion/@@images/foto-180-f1bd70bb3727434ae4dbe4f4c0e4b6ce.jpeg', 15, NULL),
(33, 'Renan Ceschin', '2025-01-01', '2028-01-01', 'Marcos Renan de Mattos Ceschin foi atleta profissional de futebol com passagens pelo Coritiba e pelo Paraná Clube. Com dois mandatos como vereador em Pinhais, na Região Metropolitana de Curitiba, já tem vasta experiência política. Foi eleito primeiramente para a legislatura 2017-2020 e depois, como vereador mais votado da história de Pinhais, para o mandato de 2021-2024. Além disso, concorreu ao cargo de deputado estadual em 2022, contabilizando mais de 20 mil votos no pleito daquele ano.\n\nNas e', 'https://www.cmc.pr.gov.br/vereadores/renan-ceschin/@@images/foto-180-91ae4ac39bf681b65a0e434c41cddc4a.jpeg', 12, NULL),
(34, 'Tânia Guerreiro', '2025-01-01', '2028-01-01', 'Tânia Mara Abrão Guerreiro nasceu em Sapopema (PR) e é da reserva da Polícia Militar do Paraná. Formada em Pedagogia, com especialização em Metodologia para o Enfrentamento à Violência Contra Crianças e Adolescentes pela Pontifícia Universidade Católica do Paraná (PUC-PR), a parlamentar tem mais de 30 anos de atuação no combate à pedofilia e ao abuso infantil. É autoria do livro “O que você não sabia sobre a pedofilia” e de quatro manuais sobre a prevenção de abuso sexual de menores, e se destac', 'https://www.cmc.pr.gov.br/vereadores/sargento-tania-guerreiro/@@images/foto-180-b22e676606d23c5c542e4cccefcd1b6d.jpeg', 12, NULL),
(35, 'Sergio Renato Bueno Balaguer', '2025-01-01', '2028-01-01', 'Antes de ocupar o cargo de vereador, Serginho do Posto já trabalhou como gerente, auxiliar de escritório e auxiliar de tráfego. Ele já recebeu a Comenda Municipal da Ordem da Luz dos Pinhais de Curitiba (2018), a Medalha Coronel Sarmento, da Polícia Militar do Paraná (2011) e a Medalha de Gratidão Ouro, da União dos Escoteiros do Brasil (2008). Foi fundador e vice-presidente da Associação Comercial e Empresarial do Cajuru e Região (2002).\n\nO vereador já comandou a Mesa Diretora do Legislativo, n', 'https://www.cmc.pr.gov.br/vereadores/serginho-do-posto/@@images/foto-180-377d18a0bf28294d2ce69dd6666b8afb.png', 15, NULL),
(36, 'Tiago Zeglin', '2025-01-01', '2028-01-01', 'Natural de Curitiba (PR), Tiago Zeglin é casado e tem dois filhos. Ele é filho do ex-vereador Tito Zeglin, radialista e parlamentar decano da última legislatura, durante seu nono e último mandato. Nas Eleições 2024, Tiago defendeu pautas ligadas à Regional Pinheirinho, obras nos bairros e políticas de assistência social, e obteve 5.562 votos pelo Movimento Democrático Brasileiro (MDB).\n\nDe família católica, ele sempre foi atuante nos movimentos cristãos de sua comunidade paroquial. É graduado em', 'https://www.cmc.pr.gov.br/vereadores/tiago-zeglin/@@images/foto-180-087ec2eea112dbcd66d1422942123fd4.jpeg', 6, NULL),
(37, 'Tico Kuzma', '2025-01-01', '2028-01-01', 'eonidas Edson Kuzma é natural de Curitiba (PR). Casado, suas raízes estão fincadas na região sul da capital: seus avós moraram, durante a maior parte da vida, nos bairros Capão Raso e Pinheirinho. Graduado em Administração de Empresas pela UniSantaCruz (Centro Universitário Santa Cruz), o vereador já trabalhou como representante comercial, fiscal federal do Crea-PR (Conselho Regional de Engenharia e Agronomia do Paraná), técnico de manutenção eletrônica e motorista de entregas com caminhão. Além', 'https://www.cmc.pr.gov.br/vereadores/tico-kuzma/@@images/foto-180-7f01acc64df068d31652b731fb404104.png', 15, NULL),
(40, 'Vanda de Assis', '2025-01-01', '2028-01-01', 'Natural de Campos Sales (CE), Antonia Vandecia de Assis é assistente social, educadora popular, freiriana, promotora legal popular e servidora pública licenciada. Militante dos movimentos populares e feministas, ela reside em Curitiba há 30 anos, desde quando migrou do Nordeste. Já atuou como membro da Pastoral da Juventude.\n\nNas Eleições 2024, Vanda de Assis, como é conhecida, foi eleita para seu primeiro mandato como vereadora da capital pelo Partido dos Trabalhadores (PT), com 5.006 votos. Na', 'https://www.cmc.pr.gov.br/vereadores/vanda-de-assis/@@images/foto-180-98d0e9eb5f5794e66570dc5a69cf252e.jpeg', 4, NULL),
(41, 'Zezinho Sabará', '2025-01-01', '2028-01-01', 'Natural de Roncador (PR), José Ortiz Lins reside no bairro Cidade Industrial de Curitiba (CIC) há mais de 35 anos. Conhecido como Zezinho Sabará, ele foi reeleito para seu quarto mandato como vereador da capital do Paraná, obtendo 7.740 votos pelo Partido Social Democrático (PSD). Casado, é pai de três filhos e avô de três netos.\n\nAo longo de sua vida, desempenhou diversas funções, incluindo agricultor, metalúrgico, comerciante e líder comunitário. Foi dirigente de igreja, presidente de grêmio e', 'https://www.cmc.pr.gov.br/vereadores/zezinho-sabara/@@images/foto-180-49dd9326a063753b4596822b99c2aaef.png', 15, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `apoio`
--
ALTER TABLE `apoio`
  ADD PRIMARY KEY (`id_cidadao`,`id_indicacao`),
  ADD KEY `id_indicacao` (`id_indicacao`);

--
-- Índices de tabela `cidadao`
--
ALTER TABLE `cidadao`
  ADD PRIMARY KEY (`id_cidadao`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_cidadao` (`id_cidadao`),
  ADD KEY `id_indicacao` (`id_indicacao`);

--
-- Índices de tabela `indicacao`
--
ALTER TABLE `indicacao`
  ADD PRIMARY KEY (`id_indicacao`),
  ADD KEY `id_localizacao` (`id_localizacao`),
  ADD KEY `id_administrador` (`id_administrador`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `id_cidadao` (`id_cidadao`),
  ADD KEY `id_vereador` (`id_vereador`);

--
-- Índices de tabela `localizacao`
--
ALTER TABLE `localizacao`
  ADD PRIMARY KEY (`id_localizacao`);

--
-- Índices de tabela `partido`
--
ALTER TABLE `partido`
  ADD PRIMARY KEY (`id_partido`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `sigla` (`sigla`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Índices de tabela `status_indicacao`
--
ALTER TABLE `status_indicacao`
  ADD PRIMARY KEY (`id_status`);

--
-- Índices de tabela `vereador`
--
ALTER TABLE `vereador`
  ADD PRIMARY KEY (`id_vereador`),
  ADD KEY `id_partido` (`id_partido`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cidadao`
--
ALTER TABLE `cidadao`
  MODIFY `id_cidadao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `comentario`
--
ALTER TABLE `comentario`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `indicacao`
--
ALTER TABLE `indicacao`
  MODIFY `id_indicacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `localizacao`
--
ALTER TABLE `localizacao`
  MODIFY `id_localizacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `partido`
--
ALTER TABLE `partido`
  MODIFY `id_partido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `status_indicacao`
--
ALTER TABLE `status_indicacao`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vereador`
--
ALTER TABLE `vereador`
  MODIFY `id_vereador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `apoio`
--
ALTER TABLE `apoio`
  ADD CONSTRAINT `apoio_ibfk_1` FOREIGN KEY (`id_cidadao`) REFERENCES `cidadao` (`id_cidadao`) ON DELETE CASCADE,
  ADD CONSTRAINT `apoio_ibfk_2` FOREIGN KEY (`id_indicacao`) REFERENCES `indicacao` (`id_indicacao`) ON DELETE CASCADE;

--
-- Restrições para tabelas `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `comentario_ibfk_1` FOREIGN KEY (`id_cidadao`) REFERENCES `cidadao` (`id_cidadao`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentario_ibfk_2` FOREIGN KEY (`id_indicacao`) REFERENCES `indicacao` (`id_indicacao`) ON DELETE CASCADE;

--
-- Restrições para tabelas `indicacao`
--
ALTER TABLE `indicacao`
  ADD CONSTRAINT `indicacao_ibfk_1` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacao` (`id_localizacao`),
  ADD CONSTRAINT `indicacao_ibfk_2` FOREIGN KEY (`id_administrador`) REFERENCES `administrador` (`id_administrador`),
  ADD CONSTRAINT `indicacao_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status_indicacao` (`id_status`),
  ADD CONSTRAINT `indicacao_ibfk_4` FOREIGN KEY (`id_cidadao`) REFERENCES `cidadao` (`id_cidadao`),
  ADD CONSTRAINT `indicacao_ibfk_5` FOREIGN KEY (`id_vereador`) REFERENCES `vereador` (`id_vereador`);

--
-- Restrições para tabelas `vereador`
--
ALTER TABLE `vereador`
  ADD CONSTRAINT `vereador_ibfk_1` FOREIGN KEY (`id_partido`) REFERENCES `partido` (`id_partido`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

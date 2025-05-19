-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19-Maio-2025 às 04:59
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `selliport`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `morada`, `telefone`) VALUES
(66, 'Ana Barreto', 'Fazenda, Praia', '9807060'),
(67, 'Vanessa Tavares', 'Ponta D\'Água', '9708060'),
(68, 'Luis Alberto', 'Prainha', '9203010'),
(69, 'N/R', 'N/R', '0000000'),
(70, 'Estefania Lopes', 'Achadinha, Praia', '9506070'),
(71, 'Estevao Silva', 'Rua Tronco, ASA - Praia', '9756066');

-- --------------------------------------------------------

--
-- Estrutura da tabela `despesas`
--

CREATE TABLE `despesas` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `data_despesa` datetime DEFAULT current_timestamp(),
  `forma_pagamento` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `despesas`
--

INSERT INTO `despesas` (`id`, `descricao`, `valor`, `categoria`, `data_despesa`, `forma_pagamento`) VALUES
(1, 'Envio de Encomenda', 390.00, NULL, '2025-10-10 00:00:00', NULL),
(2, 'Levantamento do produto', 200.00, NULL, '2025-05-19 00:00:00', 'Dinheiro'),
(3, 'Moto', 70000.00, NULL, '2025-05-15 00:00:00', 'Transferência'),
(4, 'Pagamento ao Estevão Silva', 18000.00, NULL, '2025-05-16 00:00:00', 'Transferência');

-- --------------------------------------------------------

--
-- Estrutura da tabela `movimentacoes_estoque`
--

CREATE TABLE `movimentacoes_estoque` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `movimentacoes_estoque`
--

INSERT INTO `movimentacoes_estoque` (`id`, `produto_id`, `tipo`, `quantidade`, `data_movimentacao`) VALUES
(4, 33, 'entrada', 3, '2025-05-18 15:19:25'),
(5, 31, 'saida', 1, '2025-05-18 15:32:28'),
(6, 31, 'saida', 1, '2025-05-18 15:33:33'),
(7, 34, 'entrada', 5, '2025-05-18 15:34:59'),
(8, 31, 'saida', 2, '2025-05-18 15:35:49'),
(9, 31, 'saida', 1, '2025-05-18 15:36:10'),
(10, 35, 'entrada', 1, '2025-05-18 15:49:54'),
(11, 36, 'entrada', 2, '2025-05-18 15:54:39'),
(12, 22, 'saida', 1, '2025-05-18 15:56:06'),
(13, 37, 'entrada', 3, '2025-05-18 16:05:43'),
(14, 37, 'saida', 3, '2025-05-18 16:07:03'),
(15, 36, 'saida', 1, '2025-05-18 16:13:27'),
(16, 37, 'entrada', 5, '2025-05-18 19:37:22'),
(17, 31, 'entrada', 5, '2025-05-18 19:37:38'),
(18, 21, 'entrada', 5, '2025-05-18 19:37:46'),
(19, 37, 'saida', 5, '2025-05-18 19:48:19'),
(20, 36, 'entrada', 2, '2025-05-18 19:54:05'),
(21, 37, 'entrada', 10, '2025-05-18 19:58:43'),
(22, 29, 'saida', 1, '2025-05-18 20:43:49'),
(23, 29, 'saida', 1, '2025-05-18 21:00:08'),
(24, 29, 'saida', 1, '2025-05-18 21:33:40'),
(25, 21, 'saida', 1, '2025-05-18 21:50:17'),
(26, 34, 'saida', 1, '2025-05-19 00:44:07'),
(27, 37, 'saida', 2, '2025-05-19 00:44:07'),
(28, 32, 'saida', 1, '2025-05-19 00:45:58'),
(29, 21, 'saida', 1, '2025-05-19 01:57:56'),
(30, 32, 'entrada', 2, '2025-05-19 02:09:30'),
(31, 36, 'entrada', 3, '2025-05-19 02:13:10');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_movimento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `preco`, `quantidade`, `data_movimento`) VALUES
(20, 'Zeblaze BTalk Lite Preto', 3500.00, 5, '2025-05-18 13:55:11'),
(21, 'Zeblaze GTS 3 Pro', 4500.00, 3, '2025-05-18 18:37:46'),
(22, 'Capotraste de Violão', 900.00, 4, '2025-05-18 13:55:11'),
(25, 'Colmi P31', 3000.00, 5, '2025-05-18 13:55:11'),
(29, 'Colmi P41', 3500.00, 2, '2025-05-18 13:55:11'),
(31, 'Zeblaze B-Lite 2', 4000.00, 5, '2025-05-18 18:37:38'),
(32, 'Relógio Crrju - Dourado', 3500.00, 2, '2025-05-19 01:09:30'),
(33, 'Relógio Curren Preto', 3000.00, 3, '2025-05-18 14:19:25'),
(34, 'Capa de Iphone 13', 1500.00, 4, '2025-05-18 14:34:59'),
(35, 'Suporte de Telemóvel', 1300.00, 1, '2025-05-18 14:49:54'),
(36, 'Iphone 13 Pro Max', 67000.00, 6, '2025-05-19 01:13:10'),
(37, 'Kit de Unhas', 900.00, 8, '2025-05-18 18:58:43');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','usuario') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `tipo`) VALUES
(1, 'Administrador', 'admin', '$2y$10$GaS7a/CLaMNfXuCULGa9W.xZ1.VY1.ea9ed6yei8SyMVFxyYeaQvK', 'admin');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `data_venda` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vendas`
--

INSERT INTO `vendas` (`id`, `id_cliente`, `valor_total`, `data_venda`) VALUES
(6, 66, 3500.00, '2025-05-18 04:03:34'),
(7, 66, 3500.00, '2025-05-18 06:37:20'),
(8, 67, 4500.00, '2025-05-18 06:42:38'),
(9, 68, 900.00, '2025-05-18 07:08:07'),
(10, 66, 1800.00, '2025-05-18 07:08:34'),
(11, 67, 1800.00, '2025-05-18 07:08:42'),
(12, 68, 4500.00, '2025-05-18 07:36:59'),
(13, 68, 4500.00, '2025-05-18 07:36:59'),
(14, 69, 4000.00, '2025-05-18 16:49:12'),
(15, 69, 3500.00, '2025-05-18 16:59:02'),
(16, 66, 900.00, '2025-05-18 17:23:00'),
(17, 68, 4000.00, '2025-05-18 17:32:28'),
(18, 68, 4000.00, '2025-05-18 17:33:33'),
(19, 69, 8000.00, '2025-05-18 17:35:49'),
(20, 67, 4000.00, '2025-05-18 17:36:09'),
(21, 66, 900.00, '2025-05-18 17:56:06'),
(22, 66, 2700.00, '2025-05-18 18:07:03'),
(23, 68, 67000.00, '2025-05-18 18:13:27'),
(24, 66, 4500.00, '2025-05-18 21:48:19'),
(25, 70, 3500.00, '2025-05-18 22:43:49'),
(26, 70, 3500.00, '2025-05-18 23:00:08'),
(27, 70, 3500.00, '2025-05-18 23:33:40'),
(28, 68, 4500.00, '2025-05-18 23:50:17'),
(29, 66, 3300.00, '2025-05-19 02:44:07'),
(30, 70, 3500.00, '2025-05-19 02:45:58'),
(31, 71, 4500.00, '2025-05-19 03:57:56');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas_detalhes`
--

CREATE TABLE `vendas_detalhes` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas_itens`
--

CREATE TABLE `vendas_itens` (
  `id` int(11) NOT NULL,
  `id_venda` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vendas_itens`
--

INSERT INTO `vendas_itens` (`id`, `id_venda`, `id_produto`, `quantidade`) VALUES
(11, 6, 20, 1),
(12, 7, 20, 1),
(13, 8, 21, 1),
(14, 9, 22, 1),
(15, 10, 22, 2),
(16, 11, 22, 2),
(17, 12, 21, 1),
(18, 13, 21, 1),
(19, 14, 31, 1),
(20, 15, 32, 1),
(21, 16, 22, 1),
(22, 17, 31, 1),
(23, 18, 31, 1),
(24, 19, 31, 2),
(25, 20, 31, 1),
(26, 21, 22, 1),
(27, 22, 37, 3),
(28, 23, 36, 1),
(29, 24, 37, 5),
(30, 25, 29, 1),
(31, 26, 29, 1),
(32, 27, 29, 1),
(33, 28, 21, 1),
(34, 29, 34, 1),
(35, 29, 37, 2),
(36, 30, 32, 1),
(37, 31, 21, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `despesas`
--
ALTER TABLE `despesas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Índices para tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices para tabela `vendas_detalhes`
--
ALTER TABLE `vendas_detalhes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venda` (`id_venda`),
  ADD KEY `id_produto` (`id_produto`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `vendas_detalhes`
--
ALTER TABLE `vendas_detalhes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `movimentacoes_estoque`
--
ALTER TABLE `movimentacoes_estoque`
  ADD CONSTRAINT `movimentacoes_estoque_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Limitadores para a tabela `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`);

--
-- Limitadores para a tabela `vendas_detalhes`
--
ALTER TABLE `vendas_detalhes`
  ADD CONSTRAINT `vendas_detalhes_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  ADD CONSTRAINT `vendas_detalhes_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Limitadores para a tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  ADD CONSTRAINT `vendas_itens_ibfk_1` FOREIGN KEY (`id_venda`) REFERENCES `vendas` (`id`),
  ADD CONSTRAINT `vendas_itens_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

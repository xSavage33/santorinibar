-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generaci�n: 06-06-2026 a las 17:11:17
-- Versi�n del servidor: 10.4.32-MariaDB
-- Versi�n de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;

--
-- Base de datos: `santorini`
--
CREATE DATABASE IF NOT EXISTS `santorini` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `santorini`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--
-- Creaci�n: 20-04-2026 a las 03:17:57
--

DROP TABLE IF EXISTS `administradores`;
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- RELACIONES PARA LA TABLA `administradores`:
--

--
-- Volcado de datos para la tabla `administradores`
--

INSERT DELAYED IGNORE INTO `administradores` (`id`, `usuario`, `password`, `nombre`, `created_at`) VALUES
(1, 'admin', 'santorini@777', 'Administrador', '2026-01-31 20:35:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--
-- Creaci�n: 20-04-2026 a las 04:49:39
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_menu` enum('licores','comidas') DEFAULT 'licores',
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- RELACIONES PARA LA TABLA `categorias`:
--

--
-- Volcado de datos para la tabla `categorias`
--

INSERT DELAYED IGNORE INTO `categorias` (`id`, `nombre`, `descripcion`, `tipo_menu`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Licores', 'Nuestra seleccion de licores premium', 'licores', 1, 1, '2026-01-31 20:35:23', '2026-01-31 20:35:23'),
(2, 'Cocteles', 'Cocteles de la casa y clasicos', 'licores', 2, 0, '2026-01-31 20:35:23', '2026-02-01 00:32:04'),
(3, 'Bebidas', 'Bebidas, refrescantes y variadas para todos los gustos.', 'licores', 3, 1, '2026-01-31 20:35:23', '2026-02-01 00:20:46'),
(4, 'Snacks', 'Acompana tu bebida', 'licores', 4, 0, '2026-01-31 20:35:23', '2026-02-01 00:33:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--
-- Creaci�n: 20-04-2026 a las 03:17:57
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategoria_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subcategoria_id` (`subcategoria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- RELACIONES PARA LA TABLA `productos`:
--   `subcategoria_id`
--       `subcategorias` -> `id`
--

--
-- Volcado de datos para la tabla `productos`
--

INSERT DELAYED IGNORE INTO `productos` (`id`, `subcategoria_id`, `nombre`, `descripcion`, `precio`, `imagen`, `activo`, `destacado`, `orden`, `created_at`, `updated_at`) VALUES
(8, 7, 'Cerveza costeñita - 175 ML', 'Cerveza Costeñita 175 ml, ligera, refrescante y fácil de disfrutar.', 3500.00, 'prod_697e9d4d73a4c.png', 1, 1, 1, '2026-01-31 20:35:23', '2026-02-01 00:30:40'),
(9, 7, 'Cerveza poker - 250 ML', 'Cerveza Poker 250 ml, refrescante, suave y bien balanceada.', 3500.00, 'prod_697e9d8f3881f.png', 1, 0, 4, '2026-01-31 20:35:23', '2026-02-01 00:31:13'),
(10, 7, 'Cerveza aguila negra - 330 ML', 'Cerveza Águila Negra 330 ml, intensa, oscura y con carácter.', 4000.00, 'prod_697e9dc0f3f54.png', 1, 0, 6, '2026-01-31 20:35:23', '2026-02-01 01:47:07'),
(11, 7, 'Cerveza coronita - 210 ML', 'Cerveza Coronita 210 ml, ligera, refrescante y suave.', 4500.00, 'prod_697e9dfe52d16.png', 1, 1, 0, '2026-01-31 20:35:23', '2026-02-01 00:27:42'),
(14, 11, 'Aguardiente sin azucar 24° - 375 ML', 'Aguardiente sin azúcar, suave y auténtico, 24°.', 40000.00, 'prod_697e6a8261125.png', 1, 0, 3, '2026-01-31 20:48:02', '2026-02-03 22:50:51'),
(15, 11, 'Aguardiente sin azucar 24° - 750 ML', 'Aguardiente sin azúcar, sabor limpio y balanceado, 24°.', 85000.00, 'prod_697e6ad91dce0.png', 1, 1, 4, '2026-01-31 20:49:29', '2026-02-03 22:46:50'),
(16, 11, 'Aguardiente sin azucar 24° - 1000 ML', 'Aguardiente sin azúcar, sabor tradicional y suave, 24°.', 110000.00, 'prod_697e6b1902b7e.png', 1, 0, 5, '2026-01-31 20:50:33', '2026-02-03 22:47:21'),
(17, 11, 'Aguardiente sin azucar 24° - 1750 ML', 'Aguardiente sin azúcar, suave y rendidor, 24°.', 180000.00, 'prod_697e6b5ccb4fc.png', 1, 0, 6, '2026-01-31 20:51:40', '2026-02-03 22:47:28'),
(18, 2, 'Ron medellín 29° - 375 ML', 'Ron Medellín 29°, suave, aromático y bien balanceado.', 45000.00, 'prod_697e96828d057.png', 1, 0, 9, '2026-01-31 23:55:46', '2026-02-03 23:02:01'),
(19, 2, 'Ron medellín 29° - 750 ML', 'Ron Medellín 29°, sabor suave y carácter tradicional.', 85000.00, 'prod_697e96ae37cfb.png', 1, 0, 10, '2026-01-31 23:56:30', '2026-02-03 23:02:10'),
(20, 2, 'Ron medellín 12 años - 750 ML', 'Ron Medellín 12 años, añejado, suave y de gran carácter.', 160000.00, 'prod_697e96fd8af10.png', 1, 0, 6, '2026-01-31 23:57:49', '2026-02-03 23:00:59'),
(21, 2, 'Ron medellín 18 años - 750 ML', 'Ron Medellín 18 años, añejado premium, intenso y elegante.', 450000.00, 'prod_697e973b18a0e.png', 1, 0, 7, '2026-01-31 23:58:51', '2026-02-03 23:01:25'),
(22, 2, 'Ron medellín 19 años - 750 ML', 'Ron Medellín 19 años, añejado excepcional, profundo y refinado.', 380000.00, 'prod_697e975f2b95d.png', 1, 0, 8, '2026-01-31 23:59:27', '2026-02-03 23:01:53'),
(23, 2, 'Ron medellín 3 años - 1000 ML', 'Ron Medellín 3 años, joven, suave y balanceado.', 100000.00, 'prod_697e978c90097.png', 1, 0, 3, '2026-02-01 00:00:12', '2026-02-03 22:58:56'),
(24, 2, 'Ron medellín 3 años - 750 ML', 'Ron Medellín 3 años, joven, suave y fácil de disfrutar.', 85000.00, 'prod_697e97e39ad56.png', 1, 0, 1, '2026-02-01 00:01:39', '2026-02-03 22:58:44'),
(25, 2, 'Ron medellín 3 años - 375 ML', 'Ron Medellín 3 años, ligero, suave y listo para disfrutar.', 45000.00, 'prod_697e980f97cd5.png', 1, 0, 0, '2026-02-01 00:02:23', '2026-02-01 01:51:21'),
(26, 2, 'Ron medellín 5 años - 750 ML', 'Ron Medellín 5 años, suave, equilibrado y con carácter ligero.', 100000.00, 'prod_697e983697d0e.png', 1, 0, 3, '2026-02-01 00:03:02', '2026-02-04 19:15:29'),
(27, 2, 'Ron medellín 8 años - 750 ML', 'Ron Medellín 8 años, añejado, suave y con sabor complejo.', 130000.00, 'prod_697e987dede17.png', 1, 0, 5, '2026-02-01 00:04:13', '2026-02-03 22:59:50'),
(28, 2, 'Ron medellín 8 años - 375 ML', 'Ron Medellín 8 años, añejado, suave y lleno de carácter.', 80000.00, 'prod_697e98c2a679f.png', 1, 0, 4, '2026-02-01 00:05:22', '2026-02-03 22:59:37'),
(29, 7, 'Cerveza club colombia - 330 ML', 'Cerveza Club Colombia 330 ml, equilibrada, suave y auténtica.', 5000.00, 'prod_697e9e342f8e6.png', 1, 0, 3, '2026-02-01 00:28:36', '2026-02-07 16:21:59'),
(30, 7, 'Cerveza budweiser - 250 ML', 'Cerveza Budweiser 250 ml, suave, ligera y refrescante.', 4000.00, 'prod_697e9e64b5630.png', 1, 0, 7, '2026-02-01 00:29:24', '2026-02-01 00:31:43'),
(31, 7, 'Cerveza BBC - 330 ML', 'Cerveza BBC 330 ml, artesanal, con sabor único y equilibrado.', 5000.00, 'prod_697e9e8688947.png', 1, 0, 5, '2026-02-01 00:29:58', '2026-02-01 00:31:27'),
(32, 7, 'Cerveza heineken - 330 ML', 'Cerveza Heineken 330 ml, refrescante, ligera y clásica.', 4000.00, 'prod_697ea017081f4.png', 1, 0, 8, '2026-02-01 00:36:39', '2026-02-01 00:36:39'),
(33, 1, 'Old parr 12 años - 750 ML', 'Old Parr 12 años 750 ml, whisky suave, añejado y con carácter.', 240000.00, 'prod_697ea91e18a02.png', 1, 0, 1, '2026-02-01 01:15:10', '2026-02-03 23:21:49'),
(34, 1, 'Old parr 12 años - 500 ML', 'Old Parr 12 años 500 ml, whisky suave, añejado y refinado.', 180000.00, 'prod_697ea94a221af.png', 1, 0, 0, '2026-02-01 01:15:54', '2026-02-01 01:44:03'),
(35, 1, 'Buchanans master - 750 ML', 'Buchanans Master 750 ml, whisky premium, intenso y sofisticado.', 260000.00, 'prod_697ea9868df5f.png', 1, 0, 6, '2026-02-01 01:16:54', '2026-02-03 23:23:48'),
(36, 1, 'Buchanans deluxe - 750 ML', 'Buchanans Deluxe 750 ml, whisky suave, elegante y bien equilibrado.', 240000.00, 'prod_697ea9af69f8c.png', 1, 0, 4, '2026-02-01 01:17:35', '2026-02-03 23:23:01'),
(37, 1, 'Buchanans deluxe - 375 ML', 'Buchanans Deluxe 375 ml, whisky suave, elegante y fácil de disfrutar.', 160000.00, 'prod_697ea9d1855f5.png', 1, 0, 3, '2026-02-01 01:18:09', '2026-02-03 23:22:48'),
(38, 13, 'JP. Chenet rosado - 750 ML', 'JP. Chenet 750 ml, vino suave, afrutado y fácil de disfrutar.', 85000.00, 'prod_697eaa4fd9951.png', 1, 0, 0, '2026-02-01 01:20:15', '2026-02-03 22:42:13'),
(40, 8, 'Soda saborizada - 415 ML', 'Soda saborizada 415 ml, refrescante, dulce y burbujeante.', 13000.00, 'prod_697eab03c1778.png', 1, 0, 0, '2026-02-01 01:23:15', '2026-02-03 23:11:07'),
(41, 12, 'Agua pool - 600 ML', 'Agua Pool 600 ml, pura, fresca y natural.', 3000.00, 'prod_697eab35515f5.png', 1, 0, 0, '2026-02-01 01:24:05', '2026-02-01 01:47:29'),
(42, 12, 'Bretaña - 300 ML', 'Bretaña 300 ml, licor suave, dulce y aromático.', 6000.00, 'prod_697eab5678cb1.png', 1, 0, 0, '2026-02-01 01:24:38', '2026-02-01 01:47:41'),
(43, 12, 'Coca cola - 400 ML', 'Coca-Cola 400 ml, clásica, refrescante y con burbujas.', 4000.00, 'prod_697eab70b626f.png', 1, 0, 0, '2026-02-01 01:25:04', '2026-02-01 01:47:52'),
(44, 14, 'Electrolit - 625 ML', 'Electrolit 625 ml, isotónico, hidratante y reponedor de minerales.', 14000.00, 'prod_697eabc3b633a.png', 1, 1, 0, '2026-02-01 01:26:27', '2026-02-01 01:48:07'),
(45, 14, 'Gatorade - 500 ML', 'Gatorade 500 ml, isotónico, refrescante y rehidratante.', 6000.00, 'prod_697eabe06ba97.png', 1, 0, 1, '2026-02-01 01:26:56', '2026-02-01 01:48:17'),
(46, 3, 'Smirnoff tamarindo - 750 ML', 'Vodka Smirnoff Tamarindo 750 ml, dulce y ácido', 90000.00, 'prod_698271c743392.png', 1, 0, 0, '2026-02-03 22:08:07', '2026-02-03 22:08:07'),
(47, 4, 'Don julio blanco - 700 ML', 'Tequila Don Julio Blanco 700 ml, suave y puro', 430000.00, 'prod_698272115ecd5.png', 1, 0, 1, '2026-02-03 22:09:21', '2026-02-03 23:03:05'),
(48, 4, 'Don julio 70 cristalino - 700 ML', 'Tequila Don Julio 70 Cristalino 700 ml, suave y elegante', 550000.00, 'prod_69827365a10d8.png', 1, 1, 0, '2026-02-03 22:15:01', '2026-02-03 22:15:01'),
(49, 4, '1800 Reposado - 750 ML', 'Tequila 1800 Reposado 750 ml, suave y balanceado', 420000.00, 'prod_698273a159413.png', 1, 0, 4, '2026-02-03 22:16:01', '2026-02-03 23:05:42'),
(50, 4, '1800 Blanco - 750 ML', 'Tequila 1800 Blanco 750 ml, limpio y suave', 380000.00, 'prod_698273dcb0619.png', 1, 0, 5, '2026-02-03 22:17:00', '2026-02-03 23:06:39'),
(51, 4, '1800 Cristalino - 750 ML', 'Tequila 1800 Cristalino 750 ml, suave y refinado', 500000.00, 'prod_69827411125b7.png', 1, 0, 2, '2026-02-03 22:17:53', '2026-02-03 23:04:13'),
(52, 4, '1800 Añejo - 750 ML', 'Tequila 1800 Añejo 750 ml, intenso y suave', 450000.00, 'prod_69827460dea2b.png', 1, 0, 3, '2026-02-03 22:19:12', '2026-02-03 23:05:54'),
(53, 4, 'Jose cuervo plata - 750 ML', 'Tequila José Cuervo Plata 750 ml, limpio y suave', 170000.00, 'prod_6982748454bc9.png', 1, 0, 7, '2026-02-03 22:19:48', '2026-02-03 23:07:59'),
(54, 4, 'Jose cuervo reposado - 750 ML', 'Tequila José Cuervo Reposado 750 ml, suave y equilibrado', 170000.00, 'prod_698274a775aea.png', 1, 0, 8, '2026-02-03 22:20:23', '2026-02-03 23:08:07'),
(55, 4, 'Jose cuervo tradicional - 750 ML', 'Tequila José Cuervo Tradicional 750 ml, auténtico y suave', 220000.00, 'prod_698274cea4671.png', 1, 0, 6, '2026-02-03 22:21:02', '2026-02-03 23:07:50'),
(56, 1, 'Old parr 12 años - 1 LT', 'Whisky Old Parr 12 años 1 L, suave y con carácter.', 270000.00, 'prod_6982751728feb.png', 1, 0, 2, '2026-02-03 22:22:15', '2026-02-03 23:22:02'),
(57, 1, 'Buchanans deluxe - 1 LT', 'Whisky Buchanans Deluxe 1 L, suave y refinado.', 280000.00, 'prod_698275895285f.png', 1, 0, 5, '2026-02-03 22:24:09', '2026-02-03 23:23:11'),
(58, 1, 'Buchanans master - 1 LT', 'Whisky Buchanans Master 1 L, premium y sofisticado.', 300000.00, 'prod_698275b8539ca.png', 1, 0, 7, '2026-02-03 22:24:56', '2026-02-03 23:23:58'),
(59, 1, 'Black label - 750 ML', 'Whisky Johnnie Walker Black Label 750 ml, suave y ahumado.', 230000.00, 'prod_698275f7e98ab.png', 1, 0, 9, '2026-02-03 22:25:59', '2026-02-03 23:24:38'),
(60, 1, 'Doble black - 750 ML', 'Whisky Johnnie Walker Double Black 750 ml, intenso y ahumado.', 260000.00, 'prod_6982761c70982.png', 1, 0, 10, '2026-02-03 22:26:36', '2026-02-03 23:24:45'),
(61, 1, 'Famous grouse - 750 ML', 'Whisky Famous Grouse 750 ml, suave y equilibrado.', 130000.00, 'prod_6982764f7bbbf.png', 1, 0, 8, '2026-02-03 22:27:27', '2026-02-03 23:24:26'),
(62, 15, 'Ron caldas tradicional - 750 ML', 'Ron Caldas Tradicional 750 ml, suave y auténtico.', 59000.00, 'prod_6982770b2a084.png', 1, 0, 13, '2026-02-03 22:30:35', '2026-02-03 22:56:10'),
(63, 15, 'Ron caldas tradicional - 1 LT', 'Ron Caldas Tradicional 1 L, suave y auténtico.', 110000.00, 'prod_69827732520eb.png', 1, 0, 14, '2026-02-03 22:31:14', '2026-02-04 19:19:38'),
(64, 15, 'Ron caldas esencial - 750 ML', 'Ron Caldas Esencial 750 ml, suave y equilibrado.', 69000.00, 'prod_69827762d8076.png', 1, 0, 11, '2026-02-03 22:32:02', '2026-02-03 22:55:51'),
(65, 15, 'Ron caldas esencial - 1 LT', 'Ron Caldas Esencial 1 L, suave y equilibrado.', 89000.00, 'prod_69827786daa13.png', 1, 0, 12, '2026-02-03 22:32:38', '2026-02-03 22:55:56'),
(66, 15, 'Juan de la cruz - 750 ML', 'Ron Caldas Juan de la Cruz 750 ml, intenso y sofisticado.', 65000.00, 'prod_698277daf3e83.png', 1, 0, 15, '2026-02-03 22:34:02', '2026-02-03 22:56:21'),
(67, 15, 'Ron caldas 8 años - 375 ML', 'Ron Caldas 8 Años 375 ml, añejo y suave.', 85000.00, 'prod_698278131325a.png', 1, 0, 9, '2026-02-03 22:34:59', '2026-02-03 22:54:39'),
(68, 15, 'Ron caldas 8 años - 750 ML', 'Ron Caldas 8 Años 750 ml, añejo y suave.', 130000.00, 'prod_698278491ca80.png', 1, 0, 10, '2026-02-03 22:35:53', '2026-02-03 22:54:55'),
(69, 11, 'Aguardiente antioqueño - 375 ML', 'Aguardiente Antioqueño 375 ml, clásico y suave.', 44000.00, 'prod_6982798067739.png', 1, 0, 7, '2026-02-03 22:41:04', '2026-02-03 22:54:08'),
(70, 11, 'Aguardiente antioqueño - 750 ML', 'Aguardiente Antioqueño 750 ml, clásico y suave.', 87000.00, 'prod_698279a1984d0.png', 1, 0, 8, '2026-02-03 22:41:37', '2026-02-03 22:54:16'),
(71, 13, 'JP. Chenet blanco - 750 ML', 'Vino JP. Chenet Blanco 750 ml, fresco y afrutado.', 85000.00, 'prod_698279e3c958b.png', 1, 0, 0, '2026-02-03 22:42:43', '2026-02-03 22:42:43'),
(72, 11, 'Amarillo de manzanares - 375 ML', 'Amarillo de Manzanares 375 ml, dulce y tradicional.', 50000.00, 'prod_69827a180501d.png', 1, 0, 0, '2026-02-03 22:43:36', '2026-02-03 22:43:36'),
(73, 11, 'Amarillo de manzanares - 750 ML', 'Amarillo de Manzanares 750 ml, dulce y tradicional.', 85000.00, 'prod_69827a3565222.png', 1, 0, 1, '2026-02-03 22:44:05', '2026-02-03 22:45:15'),
(74, 11, 'Amarillo de manzanares - 1 LT', 'Amarillo de Manzanares 1 L, dulce y tradicional.', 110000.00, 'prod_69827a58a45a5.png', 1, 0, 2, '2026-02-03 22:44:40', '2026-02-03 22:45:25'),
(75, 7, 'Servicio de michelada - 415 ML', 'Servicio de Michelada 415 ml, listo para disfrutar.', 3000.00, 'prod_6982824729306.png', 1, 1, 2, '2026-02-03 23:18:31', '2026-02-03 23:19:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--
-- Creaci�n: 20-04-2026 a las 03:17:57
--

DROP TABLE IF EXISTS `subcategorias`;
CREATE TABLE IF NOT EXISTS `subcategorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- RELACIONES PARA LA TABLA `subcategorias`:
--   `categoria_id`
--       `categorias` -> `id`
--

--
-- Volcado de datos para la tabla `subcategorias`
--

INSERT DELAYED IGNORE INTO `subcategorias` (`id`, `categoria_id`, `nombre`, `descripcion`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Whisky', '', 4, 1, '2026-01-31 20:35:23', '2026-02-03 22:57:24'),
(2, 1, 'Ron Medellin', '', 1, 1, '2026-01-31 20:35:23', '2026-02-03 22:29:26'),
(3, 1, 'Vodka', '', 5, 1, '2026-01-31 20:35:23', '2026-02-03 22:57:34'),
(4, 1, 'Tequila', '', 3, 1, '2026-01-31 20:35:23', '2026-02-01 01:28:01'),
(5, 2, 'Cocteles Clasicos', NULL, 1, 1, '2026-01-31 20:35:23', '2026-01-31 20:35:23'),
(6, 2, 'Cocteles de la Casa', NULL, 2, 1, '2026-01-31 20:35:23', '2026-01-31 20:35:23'),
(7, 3, 'Cervezas', '', 1, 1, '2026-01-31 20:35:23', '2026-02-01 00:21:04'),
(8, 3, 'Sodas saborizadas', '', 3, 1, '2026-01-31 20:35:23', '2026-02-01 01:21:49'),
(9, 4, 'Picadas', NULL, 1, 1, '2026-01-31 20:35:23', '2026-01-31 20:35:23'),
(10, 4, 'Porciones', NULL, 2, 1, '2026-01-31 20:35:23', '2026-01-31 20:35:23'),
(11, 1, 'Aguardientes', 'Aguardiente, licor tradicional, fuerte y auténtico.', 0, 1, '2026-02-01 00:06:33', '2026-02-03 22:01:34'),
(12, 3, 'Aguas y refrescos', '', 2, 1, '2026-02-01 00:22:42', '2026-02-01 01:21:56'),
(13, 1, 'Vino', 'Vino, bebida elegante y versátil, ideal para acompañar comidas.', 6, 1, '2026-02-01 00:40:36', '2026-02-03 22:57:40'),
(14, 3, 'Energizantes e isotópicos', 'Energizantes e isotópicos, bebidas para energía y recuperación rápida.', 4, 1, '2026-02-01 01:25:52', '2026-02-01 01:27:33'),
(15, 1, 'Ron Caldas', '', 2, 1, '2026-02-03 22:29:41', '2026-02-03 22:56:53');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`subcategoria_id`) REFERENCES `subcategorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `subcategorias_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;


--
-- Metadatos
--
USE `phpmyadmin`;

--
-- Metadatos para la tabla administradores
--
-- Error leyendo datos de la tabla phpmyadmin.pma__table_uiprefs: #1100 - Tabla &#039;pma__table_uiprefs&#039; no fue trabada con LOCK TABLES
-- Error leyendo datos de la tabla phpmyadmin.pma__tracking: #1100 - Tabla &#039;pma__tracking&#039; no fue trabada con LOCK TABLES

--
-- Metadatos para la tabla categorias
--
-- Error leyendo datos de la tabla phpmyadmin.pma__table_uiprefs: #1100 - Tabla &#039;pma__table_uiprefs&#039; no fue trabada con LOCK TABLES
-- Error leyendo datos de la tabla phpmyadmin.pma__tracking: #1100 - Tabla &#039;pma__tracking&#039; no fue trabada con LOCK TABLES

--
-- Metadatos para la tabla productos
--
-- Error leyendo datos de la tabla phpmyadmin.pma__table_uiprefs: #1100 - Tabla &#039;pma__table_uiprefs&#039; no fue trabada con LOCK TABLES
-- Error leyendo datos de la tabla phpmyadmin.pma__tracking: #1100 - Tabla &#039;pma__tracking&#039; no fue trabada con LOCK TABLES

--
-- Metadatos para la tabla subcategorias
--
-- Error leyendo datos de la tabla phpmyadmin.pma__table_uiprefs: #1100 - Tabla &#039;pma__table_uiprefs&#039; no fue trabada con LOCK TABLES
-- Error leyendo datos de la tabla phpmyadmin.pma__tracking: #1100 - Tabla &#039;pma__tracking&#039; no fue trabada con LOCK TABLES

--
-- Metadatos para la base de datos santorini
--
-- Error leyendo datos de la tabla phpmyadmin.pma__relation: #1100 - Tabla &#039;pma__relation&#039; no fue trabada con LOCK TABLES
-- Error leyendo datos de la tabla phpmyadmin.pma__savedsearches: #1100 - Tabla &#039;pma__savedsearches&#039; no fue trabada con LOCK TABLES
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

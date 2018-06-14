-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-06-2018 a las 08:08:22
-- Versión del servidor: 10.1.32-MariaDB
-- Versión de PHP: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `id3900810_ubimark`
--
CREATE DATABASE IF NOT EXISTS `id3900810_ubimark` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `id3900810_ubimark`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `busquedas`
--

CREATE TABLE `busquedas` (
  `Id_busqueda` int(11) NOT NULL,
  `busqueda` varchar(40) COLLATE utf16_spanish2_ci NOT NULL,
  `coords` varchar(50) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf16_spanish2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `folio_carrito` int(10) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `Id_empresa` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `nombre_empresa` varchar(50) COLLATE utf16_spanish2_ci NOT NULL,
  `RFC` varchar(50) COLLATE utf16_spanish2_ci NOT NULL,
  `email` varchar(40) COLLATE utf16_spanish2_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf16_spanish2_ci NOT NULL,
  `coordenadas` varchar(50) COLLATE utf16_spanish2_ci NOT NULL,
  `calificacion` varchar(3) COLLATE utf16_spanish2_ci NOT NULL,
  `pais` varchar(10) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `estado` varchar(20) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `ciudad` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `delegacion` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `colonia` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `cp` int(6) DEFAULT NULL,
  `calle` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `numinterior` varchar(3) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  `logo` text COLLATE utf16_spanish2_ci,
  `portada` text COLLATE utf16_spanish2_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagen_prod`
--

CREATE TABLE `imagen_prod` (
  `Id_imagen` int(11) NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  `Id_producto` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `info_bancaria`
--

CREATE TABLE `info_bancaria` (
  `Id_registro` int(11) NOT NULL,
  `nu_tarjeta` varchar(20) COLLATE utf16_spanish2_ci NOT NULL,
  `banco` varchar(20) COLLATE utf16_spanish2_ci NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `paypal` varchar(20) COLLATE utf16_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `map_tag`
--

CREATE TABLE `map_tag` (
  `Id_producto` int(11) NOT NULL,
  `Id_tag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `Id_notificacion` int(11) NOT NULL,
  `tipo` set('VENTA','PREGUNTA','RESPUESTA','PEDIDO_ENVIADO','PEDIDO_ENTREGADO') COLLATE utf8_unicode_ci NOT NULL,
  `remitente` int(11) NOT NULL,
  `origen` int(11) NOT NULL,
  `destino` int(11) NOT NULL,
  `tipo_destino` enum('EMPRESA','PERSONAL') COLLATE utf8_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL,
  `estado` set('NO_LEIDO','LEIDO','COMPLETADO') COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `Id_pedido` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_producto` int(11) NOT NULL,
  `cantidad` tinyint(4) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(20) COLLATE utf16_spanish2_ci NOT NULL,
  `fecha_entrega` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `Id_pregunta` int(11) NOT NULL,
  `Id_producto` int(11) NOT NULL,
  `pregunta` text COLLATE utf8_unicode_ci NOT NULL,
  `Id_cliente` int(11) NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_empresa`
--

CREATE TABLE `preguntas_empresa` (
  `Id_pregunta` int(11) NOT NULL,
  `Id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_personal`
--

CREATE TABLE `preguntas_personal` (
  `Id_pregunta` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `Id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(30) COLLATE utf16_spanish2_ci NOT NULL,
  `precio` decimal(10,0) NOT NULL,
  `descripcion` varchar(150) COLLATE utf16_spanish2_ci NOT NULL,
  `existencias` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `tipo_cuenta` set('EMPRESA','PERSONAL') COLLATE utf16_spanish2_ci NOT NULL,
  `Id_empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena`
--

CREATE TABLE `resena` (
  `Id_resena` int(11) NOT NULL,
  `Id_producto` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `comentario` varchar(150) COLLATE utf16_spanish2_ci NOT NULL,
  `calificacion` varchar(3) COLLATE utf16_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `Id_respuesta` int(11) NOT NULL,
  `Id_pregunta` int(11) NOT NULL,
  `respuesta` text COLLATE utf8_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_activas`
--

CREATE TABLE `sesiones_activas` (
  `token` text COLLATE utf8_unicode_ci NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `expira` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `Id_tag` int(11) NOT NULL,
  `tag` varchar(30) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `Id_usuario` int(11) NOT NULL,
  `nombre` varchar(30) COLLATE utf16_spanish2_ci NOT NULL,
  `apellidos` varchar(30) COLLATE utf16_spanish2_ci NOT NULL,
  `nacimiento` date DEFAULT NULL,
  `sexo` varchar(10) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `correo` varchar(40) COLLATE utf16_spanish2_ci NOT NULL,
  `correo_back` varchar(40) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `venta_activada` char(1) COLLATE utf16_spanish2_ci NOT NULL DEFAULT 'N',
  `trabaja_en` int(11) DEFAULT NULL,
  `contrasena` text COLLATE utf16_spanish2_ci NOT NULL,
  `estado` varchar(20) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `delegacion` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `colonia` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `cp` int(6) DEFAULT NULL,
  `calle` varchar(30) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  `numinterior` varchar(3) COLLATE utf16_spanish2_ci DEFAULT NULL,
  `calificacion` varchar(3) COLLATE utf16_spanish2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `busquedas`
--
ALTER TABLE `busquedas`
  ADD PRIMARY KEY (`Id_busqueda`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`folio_carrito`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`Id_empresa`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `imagen_prod`
--
ALTER TABLE `imagen_prod`
  ADD PRIMARY KEY (`Id_imagen`),
  ADD KEY `Id_producto` (`Id_producto`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `info_bancaria`
--
ALTER TABLE `info_bancaria`
  ADD PRIMARY KEY (`Id_registro`);

--
-- Indices de la tabla `map_tag`
--
ALTER TABLE `map_tag`
  ADD KEY `ID_producto` (`Id_producto`),
  ADD KEY `ID_tag` (`Id_tag`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`Id_notificacion`),
  ADD KEY `origen` (`origen`),
  ADD KEY `destino` (`destino`),
  ADD KEY `remitente` (`remitente`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`Id_pedido`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`Id_pregunta`),
  ADD KEY `Id_cliente` (`Id_cliente`),
  ADD KEY `Id_producto` (`Id_producto`);

--
-- Indices de la tabla `preguntas_empresa`
--
ALTER TABLE `preguntas_empresa`
  ADD KEY `Id_empresa` (`Id_empresa`),
  ADD KEY `Id_pregunta` (`Id_pregunta`);

--
-- Indices de la tabla `preguntas_personal`
--
ALTER TABLE `preguntas_personal`
  ADD KEY `Id_pregunta` (`Id_pregunta`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`Id_producto`),
  ADD KEY `Id_empresa` (`Id_empresa`),
  ADD KEY `productos_ibfk_2` (`Id_usuario`);

--
-- Indices de la tabla `resena`
--
ALTER TABLE `resena`
  ADD PRIMARY KEY (`Id_resena`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`Id_respuesta`),
  ADD KEY `Id_pregunta` (`Id_pregunta`),
  ADD KEY `Id_pregunta_2` (`Id_pregunta`);

--
-- Indices de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`Id_tag`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Id_usuario`),
  ADD KEY `trabaja_en` (`trabaja_en`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `busquedas`
--
ALTER TABLE `busquedas`
  MODIFY `Id_busqueda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `folio_carrito` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `Id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `imagen_prod`
--
ALTER TABLE `imagen_prod`
  MODIFY `Id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `info_bancaria`
--
ALTER TABLE `info_bancaria`
  MODIFY `Id_registro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `Id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `Id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `Id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `Id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `resena`
--
ALTER TABLE `resena`
  MODIFY `Id_resena` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `Id_respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `tags`
--
ALTER TABLE `tags`
  MODIFY `Id_tag` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `Id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `imagen_prod`
--
ALTER TABLE `imagen_prod`
  ADD CONSTRAINT `imagen_prod_ibfk_1` FOREIGN KEY (`Id_producto`) REFERENCES `productos` (`Id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `imagen_prod_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `map_tag`
--
ALTER TABLE `map_tag`
  ADD CONSTRAINT `map_tag_ibfk_1` FOREIGN KEY (`Id_producto`) REFERENCES `productos` (`Id_producto`),
  ADD CONSTRAINT `map_tag_ibfk_2` FOREIGN KEY (`Id_tag`) REFERENCES `tags` (`Id_tag`);

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`Id_cliente`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `preguntas_ibfk_4` FOREIGN KEY (`Id_producto`) REFERENCES `productos` (`Id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_empresa`
--
ALTER TABLE `preguntas_empresa`
  ADD CONSTRAINT `preguntas_empresa_ibfk_1` FOREIGN KEY (`Id_empresa`) REFERENCES `empresa` (`Id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `preguntas_empresa_ibfk_2` FOREIGN KEY (`Id_pregunta`) REFERENCES `preguntas` (`Id_pregunta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_personal`
--
ALTER TABLE `preguntas_personal`
  ADD CONSTRAINT `preguntas_personal_ibfk_1` FOREIGN KEY (`Id_pregunta`) REFERENCES `preguntas` (`Id_pregunta`),
  ADD CONSTRAINT `preguntas_personal_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`Id_empresa`) REFERENCES `empresa` (`Id_empresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `respuestas_ibfk_1` FOREIGN KEY (`Id_pregunta`) REFERENCES `preguntas` (`Id_pregunta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD CONSTRAINT `sesiones_activas_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`trabaja_en`) REFERENCES `empresa` (`Id_empresa`) ON DELETE SET NULL ON UPDATE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-10-2025 a las 21:43:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id_autor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id_autor`, `nombre`, `apellido`) VALUES
(1, 'Gabriel', 'García Márquez'),
(2, 'Julio', 'Cortázar'),
(3, 'Jorge Luis', 'Borges'),
(4, 'Isabel', 'Allende'),
(5, 'Mario', 'Vargas LLosa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--

CREATE TABLE `editoriales` (
  `id_editorial` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id_editorial`, `nombre`) VALUES
(1, 'Planeta'),
(2, 'Alfaguara'),
(3, 'Anagrama'),
(4, 'Santillana'),
(5, 'Siglo XXI');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejemplares`
--

CREATE TABLE `ejemplares` (
  `id_ejemplar` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `disponibe` tinyint(1) NOT NULL,
  `estado` enum('bajo','activo','','') NOT NULL,
  `observacion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejemplares`
--

INSERT INTO `ejemplares` (`id_ejemplar`, `isbn`, `disponible`, `disponibe`, `estado`, `observacion`) VALUES
(1, '978-84-376-0494-9', 1, 1, 'activo', 'Sin observaciones'),
(2, '978-950-511-308-7', 1, 1, 'activo', 'Copia en perfecto estado'),
(3, '978-987-1138-37-1', 1, 0, 'bajo', 'Cubierta desgastada'),
(4, '978-950-07-2253-5', 1, 1, 'activo', 'Nuevo ingreso'),
(5, '978-84-375-1000-1', 1, 1, 'activo', 'Páginas sueltas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `generos`
--

CREATE TABLE `generos` (
  `id_genero` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `generos`
--

INSERT INTO `generos` (`id_genero`, `nombre`, `descripcion`) VALUES
(1, 'Novela', 'Narrativa extensa en prosa'),
(2, 'Cuento', 'Relato Breve de ficción'),
(3, 'Poesía', 'Expresiones literariasen verso'),
(4, 'Ensayo', 'Obra en prosa de carácter analítico'),
(5, 'Teatro', 'Obra escrita para ser representada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `fecha_prestamo` date NOT NULL,
  `id_ejemplar` int(11) DEFAULT NULL,
  `fecha_devolucion` date NOT NULL,
  `devuelto` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `dni`, `fecha_prestamo`, `id_ejemplar`, `fecha_devolucion`, `devuelto`) VALUES
(1, '12345678', '2025-09-01', 1, '2025-09-15', 1),
(2, '22334455', '2025-09-05', 2, '2025-09-19', 0),
(3, '44556677', '2025-09-10', 3, '2025-09-24', 0),
(4, '55667788', '2025-09-12', 4, '2025-09-26', 1),
(5, '33445566', '2025-09-15', 5, '2025-09-29', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `dni` varchar(15) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `domicilio` varchar(200) NOT NULL,
  `vigente` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`dni`, `nombre`, `apellido`, `telefono`, `correo`, `domicilio`, `vigente`) VALUES
('12345678', 'Ana', 'Pérez', '11123456789', 'ana.perez@gmail.com', 'Calle 1', 1),
('22334455', 'Luis', 'Gomez', '1122334455', 'luis.gomez@gmail.com', 'Calle 2', 1),
('33445566', 'Maria', 'Fernández', '1133445566', 'maria.fernandez@gmail.com', 'Calle 3', 0),
('44556677', 'Carlos', 'Ruiz', '1144556677', 'carlos.ruiz@gmail.com', 'Calle 4', 1),
('55667788', 'Laura', 'Martínez', '1155667788', 'laura.martinez@gmail.com', 'Calle 5', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `titulos`
--

CREATE TABLE `titulos` (
  `isbn` varchar(20) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `resumen` text NOT NULL,
  `anio_publicacion` year(4) NOT NULL,
  `editorial_id` int(11) DEFAULT NULL,
  `genero_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `edicion` varchar(50) NOT NULL,
  `encuadernacion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `titulos`
--

INSERT INTO `titulos` (`isbn`, `nombre`, `descripcion`, `resumen`, `anio_publicacion`, `editorial_id`, `genero_id`, `cantidad`, `edicion`, `encuadernacion`) VALUES
('978-84-375-1000-1', 'La ciudad y los perros', 'Novela realista', 'Vida militar en un colegio de Lima', '1963', 5, 1, 3, '1ra', 'Rústica'),
('978-84-376-0494-9', 'Cien años de soledad', 'Novela emblemática del realismo mágico', 'La historia de la familia Buendía en macondo', '1967', 1, 1, 5, '1era', 'Tapa dura'),
('978-950-07-2253-5', 'La casa de los espíritus', 'Novela familiar y política', 'La historia de la familia Trueba', '1982', 4, 1, 7, '4ta', 'Tapa dura'),
('978-950-511-308-7', 'Rayuela', 'Novela experimental', 'Una historia que puede leerse de múltiples maneras', '1963', 2, 1, 4, '2da', 'Rústica'),
('978-987-1138-37-1', 'Ficciones', 'Colección de cuentos', 'Cuentos fantásticos y filosóficos', '1944', 3, 2, 6, '3ra', 'Tapa dura');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `titulosyautores`
--

CREATE TABLE `titulosyautores` (
  `id_titulo` int(11) NOT NULL,
  `autor_id` int(11) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `titulosyautores`
--

INSERT INTO `titulosyautores` (`id_titulo`, `autor_id`, `isbn`) VALUES
(1, 1, '978-84-376-0494-9'),
(2, 2, '978-950-511-308-7'),
(3, 3, '978-987-1138-37-1'),
(4, 4, '978-950-07-2253-5'),
(5, 5, '978-84-375-1000-1');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id_autor`);

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id_editorial`);

--
-- Indices de la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD PRIMARY KEY (`id_ejemplar`),
  ADD KEY `isbn` (`isbn`);

--
-- Indices de la tabla `generos`
--
ALTER TABLE `generos`
  ADD PRIMARY KEY (`id_genero`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `dni` (`dni`),
  ADD KEY `id_ejemplar` (`id_ejemplar`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`dni`);

--
-- Indices de la tabla `titulos`
--
ALTER TABLE `titulos`
  ADD PRIMARY KEY (`isbn`(11)),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `editorial_id` (`editorial_id`),
  ADD KEY `genero_id` (`genero_id`);

--
-- Indices de la tabla `titulosyautores`
--
ALTER TABLE `titulosyautores`
  ADD PRIMARY KEY (`id_titulo`),
  ADD KEY `titulosyautores_ibfk_1` (`autor_id`),
  ADD KEY `isbn` (`isbn`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD CONSTRAINT `ejemplares_ibfk_1` FOREIGN KEY (`isbn`) REFERENCES `titulos` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`dni`) REFERENCES `socios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`id_ejemplar`) REFERENCES `ejemplares` (`id_ejemplar`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `titulos`
--
ALTER TABLE `titulos`
  ADD CONSTRAINT `titulos_ibfk_1` FOREIGN KEY (`editorial_id`) REFERENCES `editoriales` (`id_editorial`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `titulos_ibfk_2` FOREIGN KEY (`genero_id`) REFERENCES `generos` (`id_genero`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `titulosyautores`
--
ALTER TABLE `titulosyautores`
  ADD CONSTRAINT `titulosyautores_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id_autor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `titulosyautores_ibfk_2` FOREIGN KEY (`isbn`) REFERENCES `titulos` (`isbn`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

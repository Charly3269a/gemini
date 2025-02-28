-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 27-02-2025 a las 14:38:08
-- Versión del servidor: 10.11.10-MariaDB
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u369746653_bolt`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `maps_url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clients`
--

INSERT INTO `clients` (`id`, `name`, `address`, `phone`, `maps_url`, `created_at`, `updated_at`) VALUES
(12, 'Dario letard', 'Franklin Villanueva 1890, M5593 Maipú, Mendoza, Argentina', '2612413488', 'https://www.google.com/maps/embed/v1/place?key=AIzaSyBZ3fEAKDdmwuLWi2zCSreGprNG3BsVFLE&q=Franklin%20Villanueva%201890%20maipu', '2025-02-17 12:09:08', '2025-02-17 12:09:08'),
(13, 'Mariel', 'Ugarte 531, Luján de Cuyo, Mendoza, Argentina', '2614169484', 'https://www.google.com/maps/embed/v1/place?key=AIzaSyBZ3fEAKDdmwuLWi2zCSreGprNG3BsVFLE&q=Ugarte%20531%2C%20Luj%C3%A1n%20de%20Cuyo', '2025-02-17 12:09:57', '2025-02-18 20:08:46'),
(14, 'Francisco Weber', 'Chuquisaca 1010 Godoy cruz Barrio solanas d san telmo', '2613437879', 'https://www.google.com/maps/embed/v1/place?key=AIzaSyBZ3fEAKDdmwuLWi2zCSreGprNG3BsVFLE&q=Solanas%20de%20San%20Telmo%2C%20Chuquisaca', '2025-02-17 14:03:33', '2025-02-17 14:03:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subtasks`
--

CREATE TABLE `subtasks` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subtasks`
--

INSERT INTO `subtasks` (`id`, `task_id`, `description`, `completed`, `created_at`, `updated_at`) VALUES
(37, 12, '11', 0, '2025-02-18 20:22:38', '2025-02-18 20:22:38'),
(38, 12, '222', 0, '2025-02-18 20:22:38', '2025-02-18 20:22:38'),
(39, 12, '333', 0, '2025-02-18 20:22:38', '2025-02-18 20:22:38'),
(40, 13, '11', 0, '2025-02-18 22:51:44', '2025-02-18 22:51:44'),
(41, 13, '22', 0, '2025-02-18 22:51:44', '2025-02-18 22:51:44'),
(42, 13, '33', 0, '2025-02-18 22:51:44', '2025-02-18 22:51:44'),
(43, 14, '2222', 1, '2025-02-18 23:09:42', '2025-02-19 15:57:58'),
(44, 14, '3333', 1, '2025-02-18 23:09:42', '2025-02-19 17:15:36'),
(46, 16, '2222', 1, '2025-02-18 23:50:42', '2025-02-20 14:29:35'),
(119, 30, '111', 0, '2025-02-27 14:17:52', '2025-02-27 14:18:01'),
(120, 30, '222', 0, '2025-02-27 14:17:52', '2025-02-27 14:18:01'),
(121, 30, '333', 0, '2025-02-27 14:17:52', '2025-02-27 14:17:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `schedule_time` time NOT NULL,
  `schedule_date` date NOT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','problems','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL,
  `foto_anterior` varchar(255) DEFAULT NULL,
  `foto_despues` varchar(255) DEFAULT NULL,
  `before_photo` varchar(255) DEFAULT NULL,
  `after_photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tasks`
--

INSERT INTO `tasks` (`id`, `client_id`, `description`, `schedule_time`, `schedule_date`, `value`, `expenses`, `status`, `created_at`, `updated_at`, `archived_at`, `foto_anterior`, `foto_despues`, `before_photo`, `after_photo`, `notes`, `user_id`) VALUES
(10, 12, 'Arreglo filtrado de piscina', '12:00:00', '2025-02-17', 190000.00, 0.00, 'completed', '2025-02-17 12:10:39', '2025-02-19 03:29:40', '2025-02-18 22:48:44', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 14, 'Presupuesto cesped jardin', '11:00:00', '2025-02-17', 0.00, 0.00, 'completed', '2025-02-17 14:04:14', '2025-02-18 21:52:17', '2025-02-18 21:52:17', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 13, 'ssss', '23:00:00', '2025-02-18', 2222.00, 11.00, 'completed', '2025-02-18 22:51:44', '2025-02-19 16:08:47', '2025-02-19 16:08:47', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 14, 'ddddd', '22:22:00', '2025-02-18', 22222.00, 11111.00, 'completed', '2025-02-18 23:09:42', '2025-02-19 23:15:13', '2025-02-19 23:11:28', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 13, '12222', '22:22:00', '2025-02-18', 11111.00, 22.00, 'completed', '2025-02-18 23:50:42', '2025-02-20 18:05:50', '2025-02-20 17:53:43', NULL, NULL, NULL, NULL, NULL, NULL),
(19, 13, '12321321', '03:33:00', '2025-02-19', 334.00, 33.00, 'completed', '2025-02-19 03:33:35', '2025-02-19 23:56:12', '2025-02-19 23:15:57', NULL, NULL, NULL, NULL, NULL, NULL),
(25, 14, 'ssss', '22:33:00', '2025-02-24', 22222.00, 10000.00, 'pending', '2025-02-26 15:49:25', '2025-02-26 15:50:54', NULL, NULL, NULL, NULL, NULL, NULL, 15),
(26, 14, '2222', '22:33:00', '2025-02-25', 22333.00, 33.00, 'pending', '2025-02-26 15:49:44', '2025-02-26 15:49:44', NULL, NULL, NULL, NULL, NULL, NULL, 12),
(30, 13, '2222', '22:22:00', '2025-02-27', 22222.00, 2222.00, 'pending', '2025-02-27 14:17:52', '2025-02-27 14:27:55', NULL, NULL, NULL, '/bolt/assets/task_images/task_photo_67c076677bafd_1740666471.jpg', NULL, '', 15),
(31, 12, 'dasdsd', '22:22:00', '2025-02-27', 22222.00, 333.00, 'pending', '2025-02-27 14:30:17', '2025-02-27 14:30:17', NULL, NULL, NULL, NULL, NULL, NULL, 15),
(32, 13, 'sssss', '22:02:00', '2025-02-27', 2333.00, 333.00, 'pending', '2025-02-27 14:33:04', '2025-02-27 14:33:04', NULL, NULL, NULL, NULL, NULL, NULL, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `document_number` varchar(255) DEFAULT NULL,
  `document_front_photo` varchar(255) DEFAULT NULL,
  `document_back_photo` varchar(255) DEFAULT NULL,
  `areas_of_expertise` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `document_front` varchar(255) DEFAULT NULL,
  `document_back` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `updated_at`, `role`, `document_number`, `document_front_photo`, `document_back_photo`, `areas_of_expertise`, `address`, `document_front`, `document_back`, `skills`) VALUES
(7, 'admin', '$2y$10$vV4/PnobAAAXTw/Myxsq6.86DJRwBEztlXfO9el60pOyXlxh8abmi', '2025-02-20 16:24:31', '2025-02-20 16:24:31', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'carlos', '$2y$10$eg5xuTU/7yEkk0/Ly1f2xOI.vJcLSJEAJaJp1D0/xLiani0lTAzO.', '2025-02-20 17:52:49', '2025-02-20 17:52:49', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'andres', '$2y$10$S6hrkvc8L5SmhcAqWZqEAeYY6j0kE32WAug42w0bh7jIBF1CEUiaa', '2025-02-21 15:30:34', '2025-02-21 15:30:34', 'admin', '336665588', NULL, NULL, NULL, 'martinez de rosas 1763', '/bolt/assets/user_documents/user_doc_67b89c1aebd3b_1740151834_front.jpg', '/bolt/assets/user_documents/user_doc_67b89c1aebef0_1740151834_back.jpg', 'mantenimiento de piscinas y jardines.'),
(15, 'aaaaaaaaaaaa', '$2y$10$YrlA7YIYmctsUVFlfJP9wO8aLP5mvpj29jLwA1sTA/3ffEwjb.sYu', '2025-02-21 16:29:51', '2025-02-21 16:29:51', 'user', 'ddddddddddd', NULL, NULL, NULL, '', '/bolt/assets/user_documents/user_doc_67b8a9ff027ea_1740155391_front.jpg', NULL, '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_name` (`name`);

--
-- Indices de la tabla `subtasks`
--
ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indices de la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `idx_task_date` (`schedule_date`),
  ADD KEY `idx_task_status` (`status`),
  ADD KEY `idx_task_archived` (`archived_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `subtasks`
--
ALTER TABLE `subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT de la tabla `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `subtasks`
--
ALTER TABLE `subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 28 2025 г., 15:56
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `Авиабилеты`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Пассажиры`
--

CREATE TABLE `Пассажиры` (
  `id` int NOT NULL,
  `passengerNum` varchar(6) NOT NULL,
  `name` varchar(30) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `age` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Пассажиры`
--

INSERT INTO `Пассажиры` (`id`, `passengerNum`, `name`, `surname`, `age`) VALUES
(1, 'AA1111', 'Франц', 'Бонапарта', 66),
(2, 'AA1112', 'Фридрих', 'Гриммер', 28),
(3, 'AA1113', 'Анна', 'Либерт', 20),
(4, 'AA1114', 'Тэнма', 'Кендзо', 30),
(5, 'AA1115', 'Брайант', 'Коби', 27),
(14, 'UU7777', 'Test', 'Test', 99),
(15, 'EE4444', 'Testw', 'Testw', 100);

-- --------------------------------------------------------

--
-- Структура таблицы `Полеты`
--

CREATE TABLE `Полеты` (
  `id` int NOT NULL,
  `flightDate` date NOT NULL,
  `passenger_id` int NOT NULL,
  `flight_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Полеты`
--

INSERT INTO `Полеты` (`id`, `flightDate`, `passenger_id`, `flight_id`) VALUES
(2, '2022-10-01', 1, 1),
(4, '2022-12-06', 2, 1),
(6, '2021-03-21', 4, 4),
(7, '2024-07-05', 5, 5),
(8, '2024-09-11', 2, 2),
(11, '2020-01-11', 1, 3),
(18, '2024-11-30', 1, 1),
(19, '2024-10-29', 1, 1),
(20, '2024-10-01', 1, 1),
(21, '2024-10-03', 1, 1),
(22, '2024-10-04', 1, 1),
(23, '2020-09-13', 1, 1),
(24, '2025-01-22', 14, 13);

-- --------------------------------------------------------

--
-- Структура таблицы `Рейсы`
--

CREATE TABLE `Рейсы` (
  `id` int NOT NULL,
  `flightNum` varchar(6) NOT NULL,
  `price` float NOT NULL,
  `planeSerialNum` varchar(10) NOT NULL,
  `airport` varchar(40) NOT NULL,
  `city` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Рейсы`
--

INSERT INTO `Рейсы` (`id`, `flightNum`, `price`, `planeSerialNum`, `airport`, `city`) VALUES
(1, 'EX0001', 15000, 'DBS-015015', 'Рощино', 'Тюмень'),
(2, 'EX0002', 45000, 'DBS-012345', 'Меджународный аеропорт Рига', 'Рига'),
(3, 'EX0003', 12300, 'DBS-016615', 'Шереметьево', 'Москва'),
(4, 'EX0004', 69000, 'DBS-015115', 'Цюриха', 'Клотен'),
(5, 'EX0005', 55000, 'DBS-010000', 'Шуанлю', 'Ченду'),
(11, 'EX0006', 8001, 'DBS-015015', 'аэропорт', 'город'),
(13, 'EX0056', 3941.88, 'DBS-015015', 'Тест', 'Тест');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Пассажиры`
--
ALTER TABLE `Пассажиры`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Полеты`
--
ALTER TABLE `Полеты`
  ADD PRIMARY KEY (`id`),
  ADD KEY `полеты_ibfk_1` (`flight_id`),
  ADD KEY `полеты_ibfk_2` (`passenger_id`);

--
-- Индексы таблицы `Рейсы`
--
ALTER TABLE `Рейсы`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Пассажиры`
--
ALTER TABLE `Пассажиры`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `Полеты`
--
ALTER TABLE `Полеты`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `Рейсы`
--
ALTER TABLE `Рейсы`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Полеты`
--
ALTER TABLE `Полеты`
  ADD CONSTRAINT `полеты_ibfk_1` FOREIGN KEY (`flight_id`) REFERENCES `Рейсы` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `полеты_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `Пассажиры` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

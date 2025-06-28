<?php

try {
    $con = new PDO('mysql:host=localhost;charset=utf8;', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}
$con->exec('CREATE DATABASE IF NOT EXISTS Авиабилеты');
$con->exec('USE Авиабилеты');
$flag = FALSE;
if($flag == TRUE){
try {
    $con->exec('CREATE TABLE IF NOT EXISTS Рейсы(
                id int AUTO_INCREMENT PRIMARY KEY,
                flightNum varchar(6) NOT NULL,
                price float NOT NULL,
                planeSerialNum varchar(10) NOT NULL,
                airport varchar(40) NOT NULL,
                city varchar(40) NOT NULL);

                CREATE TABLE IF NOT EXISTS Пассажиры(
                id int AUTO_INCREMENT PRIMARY KEY,
                passengerNum varchar(6) NOT NULL,
                name varchar(30) NOT NULL,
                surname varchar(30) NOT NULL,
                age tinyint NOT NULL);
                
                CREATE TABLE IF NOT EXISTS Полеты(
                id int AUTO_INCREMENT PRIMARY KEY,
                flightDate date NOT NULL,
                passenger_id int NOT NULL,
                flight_id int NOT NULL,
                FOREIGN KEY (flight_id) REFERENCES Рейсы(id) ON DELETE CASCADE,
                FOREIGN KEY (passenger_id) REFERENCES Пассажиры(id) ON DELETE CASCADE);
                
                INSERT INTO Рейсы (flightNum, price, planeSerialNum, airport, city) VALUES
                ("EX0001","15000","DBS-015015","Рощино","Тюмень"),
                ("EX0002","45000","DBS-012345","Меджународный аеропорт Рига","Рига"),
                ("EX0003","12300","DBS-016615","Шереметьево","Москва"),
                ("EX0004","69000","DBS-015115","Цюриха","Клотен"),
                ("EX0005","55000","DBS-010000","Шуанлю","Ченду");
                
                INSERT INTO Пассажиры (passengerNum, name, surname, age) VALUES
                ("AA1111","Франц","Бонапарта","66"),
                ("AA1112","Фридрих","Гриммер","28"),
                ("AA1113","Анна","Либерт","20"),
                ("AA1114","Тэнма","Кендзо","30"),
                ("AA1115","Брайант","Коби","27");
                
                INSERT INTO Полеты (flightDate, passenger_id, flight_id) VALUES
                ("2024.11.11","1","2"),
                ("2022.10.01","1","1"),
                ("2020.01.11","1","3"),
                ("2022.12.06","2","1"),
                ("2020.10.31","3","3"),
                ("2021.03.21","4","4"),
                ("2024.07.05","5","5"),
                ("2024.09.11","2","2");');
} catch(PDOException $e) {
    die('Ошибка при создании или заполнении таблиц:' . $e->getMessage());
}
}
?>

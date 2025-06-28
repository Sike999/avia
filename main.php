<head>
    <meta charset="UTF-8">
    <title>Запросы</title>
    <style>
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        font-size: 16px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    th {
        background-color: black;
        color: white;
        font-weight: bold;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    th, td {
        border: 1px solid #ddd;
    }

    table {
        border-radius: 8px;
        overflow: hidden;
    }

    tr:last-child td {
        border-bottom: 2px solid #ddd;
    }

    table {
        overflow-x: auto;
    }
</style>
</head>
<body>
<marquee><img src="plane.png" ></marquee>


<?php
try {
    $con = new PDO('mysql:host=localhost;charset=utf8;', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

$con->exec("USE Авиабилеты");

$stmt = $con->query("SELECT DISTINCT airport FROM Рейсы");
$airp = $stmt->fetchAll();
$stmt = $con->query("SELECT DISTINCT city FROM Рейсы");
$cit = $stmt->fetchAll();

function query1($con) {
    $stmt = $con->query("
        SELECT airport, AVG(price) AS avg_price, COUNT(Рейсы.id) AS total_flights
        FROM Рейсы
        JOIN Полеты ON Рейсы.id = Полеты.flight_id
        GROUP BY airport;
    ");
    return $stmt->fetchAll();
}

function query2($con) {
    $stmt = $con->query("
        SELECT DISTINCT Рейсы.airport
        FROM Полеты
        JOIN Рейсы ON Полеты.flight_id = Рейсы.id;
    ");
    return $stmt->fetchAll();
}

function query3($con, $year) {
    $stmt = $con->prepare("
        SELECT * 
        FROM Пассажиры
        WHERE id NOT IN (
            SELECT DISTINCT passenger_id
            FROM Полеты
            WHERE YEAR(flightDate) = ?
        );
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll();
}

function query4($con) {
    $stmt = $con->query("
        SELECT Пассажиры.*, COUNT(Полеты.id) AS flight_count
        FROM Пассажиры
        LEFT JOIN Полеты ON Пассажиры.id = Полеты.passenger_id
        GROUP BY Пассажиры.id
        HAVING COUNT(Полеты.id) > 0;
    ");
    return $stmt->fetchAll();
}

function query5($con) {
    $stmt = $con->query("
        SELECT Пассажиры.name, Пассажиры.surname, Рейсы.airport, COUNT(*) AS flight_count
        FROM Полеты
        JOIN Пассажиры ON Полеты.passenger_id = Пассажиры.id
        JOIN Рейсы ON Полеты.flight_id = Рейсы.id
        GROUP BY Пассажиры.id, Рейсы.airport;
    ");
    return $stmt->fetchAll();
}

function query6($con, $airport) {
    $stmt = $con->prepare("
        SELECT Пассажиры.*, COUNT(*) AS flight_count
        FROM Полеты
        JOIN Пассажиры ON Полеты.passenger_id = Пассажиры.id
        JOIN Рейсы ON Полеты.flight_id = Рейсы.id
        WHERE Рейсы.airport = ?
        GROUP BY Пассажиры.id
        HAVING flight_count > 5;
    ");
    $stmt->execute([$airport]);
    return $stmt->fetchAll();
}

function query7($con) {
    $stmt = $con->query("
        SELECT Пассажиры.*, 
               COALESCE(COUNT(Полеты.id), 0) AS flight_count
        FROM Пассажиры
        LEFT JOIN Полеты ON Пассажиры.id = Полеты.passenger_id
        GROUP BY Пассажиры.id;
    ");
    return $stmt->fetchAll();
}

function query8($con) {
    $stmt = $con->query("
        SELECT Рейсы.airport, COUNT(DISTINCT Полеты.passenger_id) AS unique_passenger_count
        FROM Полеты
        JOIN Рейсы ON Полеты.flight_id = Рейсы.id
        GROUP BY Рейсы.airport;
    ");
    return $stmt->fetchAll();
}

function query9($con) {
    $stmt = $con->query("
        SELECT Пассажиры.*, Рейсы.airport, COUNT(*) AS flight_count
        FROM Полеты
        JOIN Пассажиры ON Полеты.passenger_id = Пассажиры.id
        JOIN Рейсы ON Полеты.flight_id = Рейсы.id
        GROUP BY Пассажиры.id, Рейсы.airport
        HAVING flight_count > 5;
    ");
    return $stmt->fetchAll();
}

function query10($con, $city) {
    $stmt = $con->prepare("
        UPDATE Рейсы
        SET price = price * 0.9
        WHERE city = ?;
    ");
    $stmt->execute([$city]);
    $stmt=$con->query("SELECT * FROM Рейсы");
    return $stmt->fetchAll();
}

function displayTable($data) {
    if (empty($data)) {
        echo "Нет данных для отображения.";
        return;
    }

    else { ?>
    <table border='1'>
    <tr>
    <?php
    foreach (array_keys($data[0]) as $header) {
        ?>
        <th><?=$header?></th>
        <?php
    }
    ?>
    </tr>
    <?php
    foreach ($data as $row) {
    ?>
        <tr>
            <?php
        foreach ($row as $value) { ?>
            <td><?=$value?></td>
        <?php
        }
        ?>
        </tr>
        <?php
    }
    ?>
    </table>
    <?php
    }
}


?>
<h3>1. Средняя цена авиабилета и количество рейсов:</h3>
<?php
displayTable(query1($con));
?>
<h3>2. Список аэропортов (без повторов):</h3>
<?php
displayTable(query2($con));
?>
<h3>3. Пассажиры, не совершавшие полетов в 2022 году:</h3>
<?php
displayTable(query3($con, 2022));
?>
<h3>4. Список пассажиров с информацией о количестве совершенных полетов:</h3>
<?php
displayTable(query4($con));
?>
<h3>5. Пассажиры с аэропортами и количеством полетов:</h3>
<?php
displayTable(query5($con));
if(isset($_POST['airport']) && isset($_POST['search']))
{
    if(!empty($_POST['airport']) && preg_match('/^[A-Za-zА-Яа-яЁё]+((\s|-)[A-Za-zА-Яа-яЁё]+)*$/u', $_POST['airport'])  && strlen($_POST['airport']) <= 40)
    {
        $airport = trim($_POST['airport']);
        ?>
        <h3>6.Список пассажиров, совершивших более 5 полетов до заданного аэропорта:</h3>
        <?php
        displayTable(query6($con,$airport));
    }
}
?>
<h3>7. Список пассажиров с полем, содержащим количество совершенных полетов:</h3>
<?php
displayTable(query7($con));
?>
<h3>8. Аэропорты с количеством пассажиров:</h3>
<?php
displayTable(query8($con));
?>
<h3>9. Пассажиры, летавшие в один аэропорт более 5 раз:</h3>
<?php
displayTable(query9($con));
if(isset($_POST['city']) && isset($_POST['refresh']))
{
    if(!empty($_POST['city']) && preg_match('/^[A-Za-zА-Яа-яЁё]+((\s|-)[A-Za-zА-Яа-яЁё]+)*$/u', $_POST['city']) && strlen($_POST['city']) <= 40)
    {
        $city = trim($_POST['city']);
        ?>
        <h3>10.Снижение цен на 10% для заданного города:</h3>
        <?php
        displayTable(query10($con,$city));
    }
    
}
?>
<form method="post">
    <h3>Введите аэропорт для поиска пассажиров, совершивших более 5 полетов:</h3>
    <input type="text" name="airport" value="<?= isset($_POST['airport']) ? htmlspecialchars($_POST['airport']) : ''  ?>">
    <button type="submit" name ="search">Поиск</button>

    <h3>Введите город для снижения цен на 10%:</h3>
    <input type="text" name="city" value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''  ?>">
    <button type="submit" name ="refresh">Обновить цены</button>
</form>
</body>

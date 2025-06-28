<?php
require_once 'initiate.php';
function validateData($type, $data, $con, $edit) {
    $errors=[];

    switch ($type) {
        case 'flight':
            if (!preg_match('/^[A-Z]{2}\d{4}$/', $data['flightNum'])) {
                $errors[] = "Ошибка валидации. Код должен быть в формате 'XX0000'.";
            } else {
                $stmt = $con->prepare("SELECT COUNT(*) FROM Рейсы WHERE flightNum = ?");
                $stmt->execute([$data['flightNum']]);
                if ($stmt->fetchColumn() > 0 && $edit === FALSE) {
                    $errors[] = "Код полета уже существует.";
                }
            }

            if (!isset($data['price']) || empty($data['price'])) {
                $errors[] = "Цена не может быть пустой.";
            } else {
                $price = $data['price'];
                if (!is_numeric($price) || !preg_match('/^\d+(\.?\d{0,2})?$/', $price) || $price < 1000 || $price > 9999999 ) {
                    $errors[] = "Содержатся некорректные символы, либо задана нереалистичная цена. Цена не может иметь больше двух знаков после запятой, > 1000 и < 9999999";
                }
            }

            if (!preg_match('/^[A-Z]{3}-\d{6}$/', $data['planeSerialNum'])) {
                $errors[] = "Ошибка валидации. Код самолета должен быть в формате 'XXX-000000'.";
            }

            if (!isset($data['airport']) || empty($data['airport'])) {
                $errors[] = "Название аэропорта не может быть пустым.";
            } else {
                if (!preg_match('/^[A-Za-zА-Яа-яЁё]+((\s|-)[A-Za-zА-Яа-яЁё]+)*$/u', $data['airport']) || strlen($data['airport']) > 40) {
                    $errors[] = "Название может содержать только буквы (латиница или кириллица), пробел или дефис.";
                }
            }

            if (!isset($data['city']) || empty($data['city'])) {
                $errors[] = "Название города не может быть пустым.";
            } else {
                if (!preg_match('/^[A-Za-zА-Яа-яЁё]+((\s|-)[A-Za-zА-Яа-яЁё]+)*$/u', $data['city']) || strlen($data['city']) > 40) {
                    $errors[] = "Название может содержать только буквы (латиница или кириллица), пробел или дефис.";
                }
            }
            break;

        case 'passenger':

            if (!preg_match('/^[A-Z]{2}\d{4}$/', $data['passengerNum'])) {
                $errors[] = "Ошибка валидации. Код должен быть в формате 'XX0000'.";
            } else {
                $stmt = $con->prepare("SELECT COUNT(*) FROM Пассажиры WHERE passengerNum = ?");
                $stmt->execute([$data['passengerNum']]);
                if ($stmt->fetchColumn() > 0 && $edit === FALSE) {
                    $errors[] = "Код уже существует.";
                }
            }

            if (!isset($data['name']) || empty($data['name'])) {
                $errors[] = "Имя не может быть пустым.";
            } else {
                if (!preg_match('/^[A-Za-zА-Яа-яЁё]+((\s|-)[A-Za-zА-Яа-яЁё]+)*$/u', $data['name']) || strlen($data['name']) > 30) {
                    $errors[] = "Имя может содержать только буквы (латиница или кириллица), пробел или дефис.";
                }
            }

            if (!isset($data['surname']) || empty($data['surname'])) {
                $errors[] = "Фамилия не может быть пустой.";
            } else {
                if (!preg_match('/^[A-Za-zА-Яа-яЁё]+((\s|-)[A-Za-zА-Яа-яЁё]+)*$/u', $data['surname']) || strlen($data['surname']) > 30) {
                    $errors[] = "Фамилия может содержать только буквы (латиница или кириллица), пробел или дефис.";
                }
            }
            
            if (!preg_match('/^\d{1,3}$/', $data['age']) || $data['age'] < 1 || !isset($data['age']) || $data['age'] > 126) {
                $errors[] = "Поле с возрастом должно содержать только неотрицательные числа и быть больше нуля.";
            }
        
            break;

        case 'fly':
            $time = new DateTime('2026-01-01');
            $time = $time -> format('Y/m/d');
            if (empty($data['flightDate']) || preg_match('/^(19|20)\d\d-02-(30|31)$/',$data['flightDate']) || !preg_match('/^(19|20)\d\d-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/',$data['flightDate'])){
                $errors[] = "Дата неверна.";
            } else {
                if ($time < ((new DateTime($data['flightDate']))->format('Y/m/d'))) {
                $errors[] = "Дата полета не может быть больше 1 янв. 2026";
            }
        }
            break;
    }

    return $errors;
}
$errors = [];
$flightData = $passengerData = $flyData = [];

$flights = $con->query("SELECT * FROM Рейсы")->fetchAll(PDO::FETCH_ASSOC);

$passengers = $con->query("SELECT * FROM Пассажиры")->fetchAll(PDO::FETCH_ASSOC);

$flys = $con->query("SELECT Полеты.id, Полеты.flightDate, Пассажиры.passengerNum AS passenger_code, Рейсы.flightNum AS flight_code FROM Полеты
                       JOIN Пассажиры ON Полеты.passenger_id = Пассажиры.id 
                       JOIN Рейсы ON Полеты.flight_id = Рейсы.id")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['addFlight']) && isset($_POST['flightNum']) && isset($_POST['price']) && isset($_POST['planeSerialNum']) && isset($_POST['airport']) && isset($_POST['city'])) {
    $flightData = [
        'flightNum' => trim($_POST['flightNum']),
        'price' => trim($_POST['price']),
        'planeSerialNum' => trim($_POST['planeSerialNum']),
        'airport' => trim($_POST['airport']),
        'city' => trim($_POST['city'])
    ];

    $errors = validateData('flight', $flightData, $con, FALSE);

    if (empty($errors)) {
        $stmt = $con->prepare("INSERT INTO Рейсы (flightNum, price, planeSerialNum, airport, city) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$flightData['flightNum'], $flightData['price'], $flightData['planeSerialNum'], $flightData['airport'], $flightData['city']]);
        header("Location:http://localhost/lab1313/CRUD.php");
    }
}
if (isset($_POST['addPass']) && isset($_POST['passengerNum']) && isset($_POST['surname']) && isset($_POST['name']) && isset($_POST['age'])) {
    $passengerData = [
        'passengerNum' => trim($_POST['passengerNum']),
        'surname' => trim($_POST['surname']),
        'name' => trim($_POST['name']),
        'age' => trim($_POST['age']),
    ];

    $errors = validateData('passenger', $passengerData, $con, FALSE);

    if (empty($errors)) {
        $stmt = $con->prepare("INSERT INTO Пассажиры (passengerNum, name, surname, age) VALUES (?, ?, ?, ?)");
        $stmt->execute([$passengerData['passengerNum'], $passengerData['name'], $passengerData['surname'], $passengerData['age']]);
        header("Location:http://localhost/lab1313/CRUD.php");
    }
}
if (isset($_POST['addFly']) && isset($_POST['flightDate']) && isset($_POST['fly_passenger_id']) && isset($_POST['fly_flight_id'])) {
    $flyData = [
        'flightDate' => $_POST['flightDate'],
        'passenger_id' => $_POST['fly_passenger_id'],
        'flight_id' => $_POST['fly_flight_id'],
    ];

    $errors = validateData('fly', $flyData, $con, FALSE);

    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Пассажиры WHERE id=?");
    $stmt->execute([$_POST['fly_passenger_id']]);
    $res = $stmt->fetchAll();

    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Рейсы WHERE id=?");
    $stmt->execute([$_POST['fly_flight_id']]);
    $res2 = $stmt->fetchAll();

    if(!empty($errors)) {
        ?><script>alert("Неверные данные!")</script><?php $kostil=0;
    }
    else {
    if ( $res[0]['count']!=0 && $res2[0]['count']!=0) {
        $stmt = $con->prepare("INSERT INTO Полеты (flightDate, passenger_id, flight_id) VALUES (?, ?, ?)");
        $stmt->execute([$flyData['flightDate'], $flyData['passenger_id'], $flyData['flight_id']]);
        header("Location:http://localhost/lab1313/CRUD.php");
    }
    else {
        header("Location:http://localhost/lab1313/CRUD.php");
    }
}
}

if (isset($_POST['editFlight']) && isset($_POST['flight_num']) && isset($_POST['flight_price']) && isset($_POST['flight_planeSerialNum']) && isset($_POST['flight_airport']) && isset($_POST['flight_city'])) {
    $i = $_POST['flight_id'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Рейсы WHERE id=?");
    $stmt->execute([$i]);
    $res = $stmt->fetchAll();


    $check = $_POST['flight_num'];
    $stmt = $con->prepare("SELECT * FROM Рейсы WHERE flightNum=? AND id != ?");
    $stmt->execute([$check,$i]);
    $res2 = $stmt->fetchAll();
    
    if(!empty($res2)) {
        ?><script>alert("Код уже существует!")</script><?php $kostil=0;
    }
    else{

    $flightData = [
        'flightNum' => $_POST['flight_num'],
        'price' => $_POST['flight_price'],
        'planeSerialNum' => $_POST['flight_planeSerialNum'],
        'airport' => $_POST['flight_airport'],
        'city' => $_POST['flight_city']
    ];

    $errors = validateData('flight', $flightData, $con, TRUE);

    if (!empty($errors)) {
        ?><script>alert("Неверные значения!")</script><?php $kostil=0;
    }
    else{
    if (is_numeric($i) && is_int($i * 1) && $res[0]['count']!=0) {
        $id = $i;
        $stmt = $con->prepare("UPDATE Рейсы SET flightNum = ?, price = ?, planeSerialNum = ?, airport = ?, city = ?  WHERE id = ?");
        $stmt->execute([$flightData['flightNum'], $flightData['price'], $flightData['planeSerialNum'], $flightData['airport'], $flightData['city'], $id]);
        header("Location:http://localhost/lab1313/CRUD.php");
    }
    else {
        ?><script>alert("Неверное значение id!")</script><?php $kostil=0;
    }
    }
}
}

if (isset($_POST['editPassenger']) && isset($_POST['passenger_code']) && isset($_POST['passenger_surname']) && isset($_POST['passenger_name']) && isset($_POST['passenger_age'])) {
    $i = $_POST['passenger_id'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Пассажиры WHERE id=?");
    $stmt->execute([$i]);
    $res = $stmt->fetchAll();

    $check = $_POST['passenger_code'];
    $stmt = $con->prepare("SELECT * FROM Пассажиры WHERE passengerNum=? AND id != ?");
    $stmt->execute([$check,$i]);
    $res2 = $stmt->fetchAll();

    if(!empty($res2)) {
        ?><script>alert("Код уже существует!")</script><?php $kostil=0;
    }

    else{

    $passengerData = [
        'passengerNum' => $_POST['passenger_code'],
        'surname' => $_POST['passenger_surname'],
        'name' => $_POST['passenger_name'],
        'age' => $_POST['passenger_age']
    ];

    $errors = validateData('passenger', $passengerData, $con, TRUE);

    if (!empty($errors)) {
        ?><script>alert("Неверные значения!")</script><?php $kostil=0;
    }
    else{
    if (is_numeric($i) && is_int($i * 1) && $res[0]['count']!=0) {
        $id = $i;
        $stmt = $con->prepare("UPDATE Пассажиры SET passengerNum = ?, name = ?, surname = ?, age = ? WHERE id = ?");
        $stmt->execute([$passengerData['passengerNum'], $passengerData['name'], $passengerData['surname'], $passengerData['age'], $id]);
        header("Location:http://localhost/lab1313/CRUD.php");
    }
    else {
        ?><script>alert("Неверное значение id!")</script><?php $kostil=0;
    }
}
}
}

if (isset($_POST['editFly']) && isset($_POST['flyDate']) && isset($_POST['Pselect']) && isset($_POST['Fselect'])) {

    $i = $_POST['fly_id'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Полеты WHERE id=?");
    $stmt->execute([$i]);
    $res = $stmt->fetchAll();

    $j = $_POST['Pselect'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Пассажиры WHERE id=?");
    $stmt->execute([$j]);
    $res2 = $stmt->fetchAll();

    $k = $_POST['Fselect'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Рейсы WHERE id=?");
    $stmt->execute([$k]);
    $res3 = $stmt->fetchAll();
    $fl = ['flightDate' => $_POST['flyDate']];

    if(!preg_match('/^[A-Z]{2}\d{4}$/', $_POST['Pselect']) || !preg_match('/^[A-Z]{2}\d{4}$/', $_POST['Fselect'])) {
        $errors = ['Ошибка валидации. Код должен быть в формате "XX0000".'];
    }

    $errors = validateData('fly', $fl, $con, TRUE);

if (!empty($errors)) {
        ?><script>alert("Неверные значения!")</script><?php $kostil=0;
    }
    else{

    if (is_numeric($k) && is_int($k * 1) && is_numeric($j) && is_int($j * 1) && is_numeric($i) && is_int($i * 1) && $res[0]['count']!=0 && $res2[0]['count']!=0 && $res3[0]['count']!=0) {
        $id = $i;
        $stmt = $con->prepare("UPDATE Полеты SET flightDate = ? WHERE id = ?");
        $stmt->execute([$fl['flightDate'], $id]);

        $id2 = $j;
        $stmt = $con->prepare("UPDATE Полеты SET passenger_id = ? WHERE id = ?");
        $stmt->execute([$id2,$id]);

        $id3 = $k;
        $stmt = $con->prepare("UPDATE Полеты SET flight_id = ? WHERE id = ?");
        $stmt->execute([$id3,$id]);
        header("Location:http://localhost/lab1313/CRUD.php");
    }
    else {
        ?><script>alert("Неверное значение id!")</script><?php $kostil=0;
    }
}
}

if(isset($_GET['delete_flight'])){
    $i = $_GET['delete_flight'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Рейсы WHERE id=?");
    $stmt->execute([$i]);
    $res = $stmt->fetchAll();
if (is_numeric($i) && is_int($i * 1) && $res[0]['count']!=0) {
    $id = $i;

    $stmt = $con->prepare("SELECT COUNT(*) as checkk FROM Полеты WHERE flight_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result['checkk'] > 0)
    {
        die('Невозможно удалить запись. Она является внешним ключем.');
    }

    $stmt = $con->prepare("DELETE FROM Рейсы WHERE id = ?");
    $stmt->execute([$id]);
    header("Location:http://localhost/lab1313/CRUD.php");
}
    else {
        header("Location:http://localhost/lab1313/CRUD.php");
        ?><script>alert("Неверное значение id!")</script><?php $kostil=0;
        
    }
}

if(isset($_GET['delete_passenger'])){
    $i = $_GET['delete_passenger'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Пассажиры WHERE id=?");
    $stmt->execute([$i]);
    $res = $stmt->fetchAll();
if (is_numeric($i) && is_int($i * 1) && $res[0]['count']!=0) {
    $id = $i;

    $stmt = $con->prepare("SELECT COUNT(*) as checkk FROM Полеты WHERE passenger_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result['checkk'] > 0)
    {
        die('Невозможно удалить запись. Она является внешним ключем.');
        header("Location:http://localhost/lab1313/CRUD.php");
    }

    $stmt = $con->prepare("DELETE FROM Пассажиры WHERE id = ?");
    $stmt->execute([$id]);
    header("Location:http://localhost/lab1313/CRUD.php");
}
    else {
        ?><script>alert("Неверное значение id!")</script><?php $kostil=0;
        header("Location:http://localhost/lab1313/CRUD.php");
    }
}

if(isset($_GET['delete_fly'])){
    $i = $_GET['delete_fly'];
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM Полеты WHERE id=?");
    $stmt->execute([$i]);
    $res = $stmt->fetchAll();
if (is_numeric($i) && is_int($i * 1) && $res[0]['count']!=0) {
    $id = $i;
    $stmt = $con->prepare("DELETE FROM Полеты WHERE id = ?");
    $stmt->execute([$id]);
    header("Location:http://localhost/lab1313/CRUD.php");
}
    else {
        ?><script>alert("Неверное значение id!")</script><?php $kostil=0;
        header("Location:http://localhost/lab1313/CRUD.php");
    }
}



?>
<head>
    <meta charset="utf-8">
    <title>Авиабилеты</title>
    <style>
        .edit-form { display: none; }
    </style>
</head>

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

<body>
<h2>Добавить рейс</h2>
<form method="POST">
    <input type="text" name="flightNum" value="<?= isset($_POST['flightNum']) ? htmlspecialchars($_POST['flightNum']) : '' ?>" placeholder="Код рейса" required>
    <input type="text" name="price" value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>" placeholder="Цена за билет" required>
    <input type="text" name="planeSerialNum" value="<?= isset($_POST['planeSerialNum']) ? htmlspecialchars($_POST['planeSerialNum']) : '' ?>" placeholder="Серийный номер самолета" required>
    <input type="text" name="airport" value="<?= isset($_POST['airport']) ? htmlspecialchars($_POST['airport']) : '' ?>" placeholder="Аэропорт" required>
    <input type="text" name="city" value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '' ?>" placeholder="Город" required>
    <button type="submit" name="addFlight">Добавить</button>
</form>
<h2>Добавить пассажира</h2>
<form method="POST">
    <input type="text" name="passengerNum" value="<?= isset($_POST['passengerNum']) ? htmlspecialchars($_POST['passengerNum']) : '' ?>" placeholder="Код пассажира" required>
    <input type="text" name="surname" value="<?= isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : '' ?>" placeholder="Фамилия" required>
    <input type="text" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" placeholder="Имя" required>
    <input type="text" name="age" value="<?= isset($_POST['age']) ? htmlspecialchars($_POST['age']) : '' ?>" placeholder="Возраст" required>
    <button type="submit" name="addPass">Добавить</button>
</form>
<h2>Добавить полет</h2>
<form method="POST">
    <input type="date" name="flightDate" value="<?= isset($_POST['flightDate']) ? htmlspecialchars($_POST['flightDate']) : '' ?>" required>
    <select name="fly_flight_id" required>
        <?php foreach ($flights as $flight): ?>
            <option value="<?= $flight['id'] ?>" <?= isset($flyData['flight_id']) && $flyData['flight_id'] == $flight['id'] ? 'selected' : '' ?>><?= htmlspecialchars($flight['flightNum']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="fly_passenger_id" required>
        <?php foreach ($passengers as $passenger): ?>
            <option value="<?= $passenger['id'] ?>" <?= isset($flyData['passenger_id']) && $flyData['passenger_id'] == $passenger['id'] ? 'selected' : '' ?>><?= htmlspecialchars($passenger['passengerNum']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="addFly">Добавить</button>
</form>

<?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Рейсы</h2>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Код рейса</th>
            <th>Цена</th>
            <th>Серийный номер самолета</th>
            <th>Аэропорт</th>
            <th>Город</th>
            <th>Редактировать</th>
            <th>Удалить</th>
        </tr>
</thead>
    <tbody>
        <?php foreach ($flights as $flight): ?>
            <tr>
                <td><?= htmlspecialchars($flight['id']) ?></td>
                <td><?= htmlspecialchars($flight['flightNum']) ?></td>
                <td><?= htmlspecialchars($flight['price']) ?></td>
                <td><?= htmlspecialchars($flight['planeSerialNum']) ?></td>
                <td><?= htmlspecialchars($flight['airport']) ?></td>
                <td><?= htmlspecialchars($flight['city']) ?></td>
                <td>
                    <button onclick="toggleEditForm('flight', <?= $flight['id'] ?>)">Редактировать</button>
                    <form id="edit-form-flight-<?= $flight['id'] ?>" method="POST" class="edit-form" style="display: none;">
                        <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                        <input type="text" name="flight_num" value="<?= htmlspecialchars($flight['flightNum']) ?>" required>
                        <input type="text" name="flight_price" value="<?= htmlspecialchars($flight['price']) ?>" required>
                        <input type="text" name="flight_planeSerialNum" value="<?= htmlspecialchars($flight['planeSerialNum']) ?>" required>
                        <input type="text" name="flight_airport" value="<?= htmlspecialchars($flight['airport']) ?>" required>
                        <input type="text" name="flight_city" value="<?= htmlspecialchars($flight['city']) ?>" required>
                        <button type="submit" name="editFlight">Сохранить</button>
                    </form>
                </td>
                <td>
                    <a href="?delete_flight=<?= $flight['id'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <h2>Список клиентов</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Код пассажира</th>
                <th>Имя</th>
                <th>Фамилия</th>
                <th>Возраст</th>
                <th>Редактировать</th>
                <th>Удалить</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($passengers as $passenger): ?>
                <tr>
                    <td><?= htmlspecialchars($passenger['id']) ?></td>
                    <td><?= htmlspecialchars($passenger['passengerNum']) ?></td>
                    <td><?= htmlspecialchars($passenger['surname']) ?></td>
                    <td><?= htmlspecialchars($passenger['name']) ?></td>
                    <td><?= htmlspecialchars($passenger['age']) ?></td>
                    <td>
                        <button onclick="toggleEditForm('passenger', <?= $passenger['id'] ?>)">Редактировать</button>
                        <form id="edit-form-passenger-<?= $passenger['id'] ?>" method="POST" class="edit-form">
                            <input type="hidden" name="passenger_id" value="<?= $passenger['id'] ?>">
                            <input type="text" name="passenger_code" value="<?= htmlspecialchars($passenger['passengerNum']) ?>" required>
                            <input type="text" name="passenger_surname" value="<?= htmlspecialchars($passenger['surname']) ?>" required>
                            <input type="text" name="passenger_name" value="<?= htmlspecialchars($passenger['name']) ?>" required>
                            <input type="text" name="passenger_age" value="<?= htmlspecialchars($passenger['age']) ?>" required>
                            <button type="submit" name="editPassenger">Сохранить</button>
                        </form>
                    </td>
                    <td><a href="?delete_passenger=<?= $passenger['id'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Список полетов</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата вылета</th>
                <th>Код пассажира</th>
                <th>Код рейса</th>
                <th>Редактировать</th>
                <th>Удалить</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($flys as $fly): ?>
                <tr>
                    <td><?= htmlspecialchars($fly['id']) ?></td>
                    <td><?= htmlspecialchars($fly['flightDate']) ?></td>
                    <td><?= htmlspecialchars($fly['passenger_code']) ?></td>
                    <td><?= htmlspecialchars($fly['flight_code']) ?></td>
                    <td>
                        <button onclick="toggleEditForm('fly', <?= $fly['id'] ?>)">Редактировать</button>
                        <form id="edit-form-fly-<?= $fly['id'] ?>" method="POST" class="edit-form">
                            <input type="hidden" name="fly_id" value="<?= $fly['id'] ?>">
                            <input type="date" name="flyDate" value="<?= htmlspecialchars($fly['flightDate']) ?>" required>

                            <select name="Pselect" required>
                            <?php foreach ($passengers as $passenger):?>
                                <option value="<?= $passenger['id'] ?>">
                                    <?= $passenger['passengerNum'] ?>
                                </option>
                            <?php endforeach; ?>
                            </select>

                            <select name="Fselect" required>
                            <?php foreach ($flights as $flight):?>
                                <option value="<?= $flight['id'] ?>">
                                    <?= $flight['flightNum'] ?>
                                </option>
                            <?php endforeach; ?>
                            </select>

                            <button type="submit" name="editFly">Сохранить</button>
                        </form>
                    </td>
                    <td><a href="?delete_fly=<?= $fly['id'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function toggleEditForm(type, id) {
            var form = document.getElementById("edit-form-" + type + "-" + id);
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>

</body>

<?php
// Подключение к базе данных
require_once('../config.php');
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных о посещаемости из POST запроса
$attendanceData = json_decode($_POST['attendanceData'], true); // Массив данных о посещаемости
$groupId = $_POST['groupId']; // ID группы
$disciplineId = $_POST['disciplineId']; // ID дисциплины
$classId = $_POST['classId']; // ID номера пары
// Преобразование даты из формата datepicker в формат для базы данных
$date = date('Y-m-d', strtotime($_POST['date']));
$typeId = $_POST['typeId'];

// Подготовка SQL-запроса для вставки данных о посещаемости
$sql = "INSERT INTO attandance.attandance (StudentID, DisciplineID, ClassID, Date, Pass, TypeID) VALUES (?, ?, ?, ?, ?, ?)";

// Подготовка выражения SQL
$stmt = $conn->prepare($sql);

// Проход по массиву данных о посещаемости и вставка каждой записи в базу данных
foreach ($attendanceData as $attendance) {
    $fio = $attendance['FIO']; // ФИО студента
    $pass = $attendance['pass']; // Пропуски

    // Получение ID студента по его ФИО и ID группы
    $studentIdSql = "SELECT StudentID FROM attandance.students WHERE FIO = ? AND GroupID = ?";
    $studentIdStmt = $conn->prepare($studentIdSql);
    $studentIdStmt->bind_param("si", $fio, $groupId);
    $studentIdStmt->execute();
    $studentIdResult = $studentIdStmt->get_result();
    $studentIdRow = $studentIdResult->fetch_assoc();
    $studentId = $studentIdRow['StudentID'];

    // Проверка, существует ли уже запись с такими же значениями StudentID, DisciplineID, ClassID и Date
    $checkIfExistsSql = "SELECT * FROM attandance.attandance WHERE StudentID = ? AND DisciplineID = ? AND ClassID = ? AND Date = ? AND TypeID = ?";
    $checkIfExistsStmt = $conn->prepare($checkIfExistsSql);
    $checkIfExistsStmt->bind_param("iiisi", $studentId, $disciplineId, $classId, $date, $typeId);
    $checkIfExistsStmt->execute();
    $checkIfExistsResult = $checkIfExistsStmt->get_result();

    // Если такая запись уже существует, выдаем сообщение об ошибке
    if ($checkIfExistsResult->num_rows > 0) {
        echo json_encode(['errors' => 'Данные о посещаемости для выбранной группы в конкретную дату уже существуют в базе данных. Внести изменения невозможно, обратитесь в деканат.']);
        // Закрываем подготовленное выражение и выходим из скрипта
        $checkIfExistsStmt->close();
        exit;
    }
    // Привязка параметров и выполнение SQL-запроса
    $stmt->bind_param("iiisii", $studentId, $disciplineId, $classId, $date, $pass, $typeId);
    $stmt->execute();
}

// Проверка выполнения запроса
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => 'Данные о посещаемости успешно добавлены в базу данных.']);
}

// Закрытие подготовленного выражения и соединения с базой данных
$stmt->close();
$conn->close();
?>

<?php
// Подключение к базе данных
require_once('../config.php');
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Получение данных о посещаемости из POST запроса
$ratingData = json_decode($_POST['ratingData'], true); // Массив данных о посещаемости
$groupId = $_POST['groupId']; // ID группы
$termId = $_POST['termId']; // ID группы
$disciplineId = $_POST['disciplineId']; // ID дисциплины
$classId = $_POST['classId']; // ID номера пары
$date = $_POST['date']; // Дата
$typeId = $_POST['typeId'];
// Преобразование даты из формата datepicker в формат для базы данных
$date = date('Y-m-d', strtotime($_POST['date']));

$userid=$_SESSION['userid'];
// Получаем ID преподавателя
$sqlTID = "SELECT teachers.TeacherID FROM attandance.teachers WHERE teachers.UserID = '$userid'";
$resultTID = $conn->query($sqlTID);
$rowTID = $resultTID->fetch_assoc();
$teacherId = $rowTID['TeacherID'];

// Получаем DgtID
$sqlDGT = "SELECT discgroupteacher.DgtID FROM discgroupteacher 
INNER JOIN disciplinedirections ON discgroupteacher.DiscDirectionID = disciplinedirections.DiscDirectionID 
INNER JOIN `groups` ON discgroupteacher.GroupID = `groups`.GroupID
WHERE `groups`.GroupID = '$groupId' AND disciplinedirections.TermID = '$termId' AND 
disciplinedirections.DisciplineID = '$disciplineId' AND discgroupteacher.TeacherID = '$teacherId'";
$resultDGT = $conn->query($sqlDGT);
$rowDGT = $resultDGT->fetch_assoc();
$dgtId = $rowDGT['DgtID'];

// Подготовка SQL-запроса для вставки данных о посещаемости
$sql = "INSERT INTO attandance.rating (StudentID, DgtID, `Value`, `Date`, ClassID, TypeID) VALUES (?, ?, ?, ?, ?, ?)";

// Подготовка выражения SQL
$stmt = $conn->prepare($sql);

// Проход по массиву данных о посещаемости и вставка каждой записи в базу данных
foreach ($ratingData as $rating) {
    $fio = $rating['FIO']; // ФИО студента
    $rat_value = $rating['rat_value']; // Рейтинг

    // Получение ID студента по его ФИО и ID группы
    $studentIdSql = "SELECT StudentID FROM attandance.students WHERE students.FIO = ? AND students.GroupID = ? AND students.Expelled = 0;";
    $studentIdStmt = $conn->prepare($studentIdSql);
    $studentIdStmt->bind_param("si", $fio, $groupId);
    $studentIdStmt->execute();
    $studentIdResult = $studentIdStmt->get_result();
    $studentIdRow = $studentIdResult->fetch_assoc();
    $studentId = $studentIdRow['StudentID'];

    // Проверка, существует ли уже запись с такими же значениями StudentID, DisciplineID, ClassID и Date
    $checkIfExistsSql = "SELECT * FROM attandance.rating WHERE StudentID = ? AND DgtID = ? AND ClassID = ? AND `Date` = ? AND TypeID = ?";
    $checkIfExistsStmt = $conn->prepare($checkIfExistsSql);
    $checkIfExistsStmt->bind_param("iiisi", $studentId, $dgtId, $classId, $date, $typeId);
    $checkIfExistsStmt->execute();
    $checkIfExistsResult = $checkIfExistsStmt->get_result();

    // Если такая запись уже существует, выдаем сообщение об ошибке
    if ($checkIfExistsResult->num_rows > 0) {
        echo json_encode(['errors' => 'Данные о рейтинге для выбранной группы в конкретную дату уже существуют в базе данных. Внести изменения невозможно, обратитесь в деканат.']);
        // Закрываем подготовленное выражение и выходим из скрипта
        $checkIfExistsStmt->close();
        exit;
    }
    // Привязка параметров и выполнение SQL-запроса
    $stmt->bind_param("iiisii", $studentId, $dgtId, $rat_value, $date, $classId, $typeId);
    $stmt->execute();
}

// Проверка выполнения запроса
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => 'Данные о рейтинге студентов успешно добавлены в базу данных.']);
} else {
    echo "Ошибка при добавлении данных: " . $conn->error;
}

// Закрытие подготовленного выражения и соединения с базой данных
$stmt->close();
$conn->close();
?>

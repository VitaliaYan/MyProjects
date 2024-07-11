<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
require '../vendor/autoload.php'; // Подключение автозагрузчика PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

$groupId = $_GET['groupId'];
$disciplineId = $_GET['disciplineId'];
$starttimeId = date('Y-m-d', strtotime($_GET['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_GET['endtimeId']));
$termId = $_GET['termId'];

// Получаем номер группы
$sqlGroup = "SELECT `groups`.Groups FROM `groups` WHERE `groups`.GroupID = '$groupId';";
$resultGroup = $conn->query($sqlGroup);
$rowGroup = $resultGroup->fetch_assoc();
$groupName = $rowGroup['Groups'];

// Получаем название дисциплины
$sqlDiscipline = "SELECT disciplines.Discipline FROM disciplines WHERE disciplines.DisciplineID = '$disciplineId'";
$resultDiscipline = $conn->query($sqlDiscipline);
$rowDiscipline = $resultDiscipline->fetch_assoc();
$disciplineName = $rowDiscipline['Discipline'];

$userid=$_SESSION['userid'];
// Получаем ID преподавателя
$sqlFIO = "SELECT * FROM attandance.teachers WHERE teachers.UserID = '$userid'";
$resultFIO = $conn->query($sqlFIO);
$rowFIO = $resultFIO->fetch_assoc();
$teacherFIO = $rowFIO['FIO'];
$teacherId= $rowFIO['TeacherID'];

// Получаем DgtID
$sqlDGT = "SELECT discgroupteacher.DgtID FROM discgroupteacher 
INNER JOIN disciplinedirections ON discgroupteacher.DiscDirectionID = disciplinedirections.DiscDirectionID 
INNER JOIN `groups` ON discgroupteacher.GroupID = `groups`.GroupID
WHERE `groups`.GroupID = '$groupId' AND disciplinedirections.TermID = '$termId' AND 
disciplinedirections.DisciplineID = '$disciplineId' AND discgroupteacher.TeacherID = '$teacherId'";
$resultDGT = $conn->query($sqlDGT);
$rowDGT = $resultDGT->fetch_assoc();

if ($rowDGT !== null) {
    $dgtId = $rowDGT['DgtID'];
} else {
    exit;
}

// Запрос для получения данных ФИО и суммарного количества пропусков по данной дисциплине
$sql = "SELECT students.FIO, SUM(rating.`Value`) AS TotalValue FROM students 
        LEFT JOIN rating ON students.StudentID = rating.StudentID 
        WHERE students.GroupID = '$groupId' AND rating.DgtID = '$dgtId'
        AND rating.`Date` >= '$starttimeId' AND rating.`Date` <= '$endtimeId' AND students.Expelled = 0
        GROUP BY students.StudentID
        ORDER BY TotalValue DESC"; // Сортировка в порядке убывания, чтобы сначала были наибольшие значения

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Создаем новый объект Spreadsheet
    $spreadsheet = new Spreadsheet();

    // Получаем активный лист
    $sheet = $spreadsheet->getActiveSheet();

    // Добавляем заголовок
    $starttimeId = date('d.m.y', strtotime($_GET['starttimeId']));
    $endtimeId = date('d.m.y', strtotime($_GET['endtimeId']));
    $header = 'Группа ' . $groupName . '. Дисциплина: ' . $disciplineName . '. C: ' . $starttimeId . '. По: ' . $endtimeId;
    $sheet->setCellValue('A1', $header);

    // Добавляем заголовки столбцов
    $sheet->setCellValue('A2', 'ФИО');
    $sheet->setCellValue('B2', 'Рейтинг');

    $rowIndex = 3; // Индекс строки для данных

    // Заполняем данные из запроса
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowIndex, $row["FIO"]);
        $sheet->setCellValue('B' . $rowIndex, $row["TotalValue"]);
        $rowIndex++;
    }

    // Создаем объект Writer
    $writer = new Xlsx($spreadsheet);

    // Устанавливаем заголовки для скачивания
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $filename = $groupName . '_' . str_replace(' ', '_', $disciplineName) . '.xlsx';
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Выводим содержимое файла в браузер
    $writer->save('php://output');

    exit(); // Останавливаем выполнение скрипта после отправки файла
}

$conn->close();
?>

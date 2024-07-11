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

$groupId = $_GET['groupId'];
$disciplineId = $_GET['disciplineId'];
$starttimeId = date('Y-m-d', strtotime($_GET['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_GET['endtimeId']));

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

// Запрос для получения данных ФИО и суммарного количества пропусков по данной дисциплине
$sql = "SELECT students.FIO, SUM(attandance.Pass) AS TotalPass FROM students 
        LEFT JOIN attandance ON students.StudentID = attandance.StudentID 
        WHERE students.GroupID = '$groupId' AND attandance.DisciplineID = '$disciplineId'
        AND attandance.Date >= '$starttimeId' AND attandance.Date <= '$endtimeId' AND students.Expelled = 0
        GROUP BY students.StudentID";

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
    $sheet->setCellValue('B2', 'Количество пропусков');

    $rowIndex = 3; // Индекс строки для данных

    // Заполняем данные из запроса
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowIndex, $row["FIO"]);
        $sheet->setCellValue('B' . $rowIndex, $row["TotalPass"]);
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

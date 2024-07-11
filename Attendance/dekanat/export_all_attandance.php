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
$starttimeId = date('Y-m-d', strtotime($_GET['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_GET['endtimeId']));

// Получаем номер группы
$sqlGroup = "SELECT `groups`.Groups FROM `groups` WHERE `groups`.GroupID = '$groupId';";
$resultGroup = $conn->query($sqlGroup);
$rowGroup = $resultGroup->fetch_assoc();
$groupName = $rowGroup['Groups'];

// Получаем номер группы
$sqlStudID = "SELECT * FROM attandance.`students` WHERE `students`.GroupID = '$groupId' LIMIT 1;";
$resultStudID = $conn->query($sqlStudID);
$rowStud = $resultStudID->fetch_assoc();
$studID = $rowStud['StudentID'];

// Получаем название дисциплины
$sqlDisciplines = "SELECT `attandance`.DisciplineID FROM attandance.`attandance` WHERE `attandance`.StudentID='$studID' AND attandance.Date >= '$starttimeId' AND attandance.Date <= '$endtimeId';";
$resultDisciplines = $conn->query($sqlDisciplines);
$disciplines = array(); // Создаем пустой массив для хранения названий дисциплин
// Перебираем каждую строку результата запроса
while ($rowDisciplines = $resultDisciplines->fetch_assoc()) {
    // Добавляем название дисциплины из текущей строки в массив
    $disciplines[] = $rowDisciplines['DisciplineID'];
}

if (!empty($disciplines)){
    // Создаем массив для хранения данных о студентах и их баллах по дисциплинам
    $studentScores = array();

    // Получаем список студентов в группе
    $sqlStudents = "SELECT * FROM attandance.students WHERE students.GroupID = '$groupId' AND students.Expelled = 0;";
    $resultStudents = $conn->query($sqlStudents);

    // Перебираем каждого студента
    while ($rowStudent = $resultStudents->fetch_assoc()) {
        $studentID = $rowStudent['StudentID'];
        $FIO = $rowStudent['FIO'];

        // Создаем массив для хранения суммарных баллов по дисциплинам для текущего студента
        $studentScores[$FIO] = array();

        // Перебираем каждую дисциплину из списка
        foreach ($disciplines as $disciplineID) {
            // Получаем суммарное количество баллов для текущего студента и дисциплины за указанный период
            $sqlScore = "SELECT SUM(`Pass`) AS totalScore FROM `attandance` WHERE `StudentID`='$studentID' AND `DisciplineID`='$disciplineID' AND `Date` BETWEEN '$starttimeId' AND '$endtimeId'";
            $resultScore = $conn->query($sqlScore);
            $rowScore = $resultScore->fetch_assoc();

            // Сохраняем суммарное количество баллов для текущей дисциплины студента
            $totalScore = $rowScore['totalScore'];
            $studentScores[$FIO][$disciplineID] = $totalScore;
        }
    }
}
if (!empty($disciplines) && !empty($studentScores)) {
    // Создаем новый объект Spreadsheet
    $spreadsheet = new Spreadsheet();

    // Получаем активный лист
    $sheet = $spreadsheet->getActiveSheet();

    // Добавляем заголовок
    $starttimeId = date('d.m.y', strtotime($_GET['starttimeId']));
    $endtimeId = date('d.m.y', strtotime($_GET['endtimeId']));
    $header = 'Группа ' . $groupName . '. C: ' . $starttimeId . '. По: ' . $endtimeId;
    $sheet->setCellValue('A1', $header);

    // Добавляем заголовки столбцов
    $sheet->setCellValue('A2', 'ФИО');

    // Добавляем заголовки дисциплин
    $columnIndex = 'B'; // Индекс столбца для записи названий дисциплин
    foreach ($disciplines as $disciplineID) {
        // Получаем название дисциплины
        $sqlDiscipline = "SELECT Discipline FROM disciplines WHERE DisciplineID = '$disciplineID'";
        $resultDiscipline = $conn->query($sqlDiscipline);
        $rowDiscipline = $resultDiscipline->fetch_assoc();
        $disciplineName = $rowDiscipline['Discipline'];

        // Записываем название дисциплины в соответствующий столбец
        $sheet->setCellValue($columnIndex . '2', $disciplineName);

        // Увеличиваем индекс столбца для следующей дисциплины
        $columnIndex++;
    }

    // Индекс строки для данных
    $rowIndex = 3;

    // Перебираем каждого студента
    foreach ($studentScores as $FIO => $scores) {
        // Записываем ФИО студента в первый столбец
        $sheet->setCellValue('A' . $rowIndex, $FIO);

        // Индекс столбца для записи баллов по дисциплинам
        $columnIndex = 'B';
        foreach ($disciplines as $disciplineID) {
            // Записываем баллы студента по текущей дисциплине в соответствующий столбец
            $sheet->setCellValue($columnIndex . $rowIndex, isset($scores[$disciplineID]) ? $scores[$disciplineID] : 0);

            // Увеличиваем индекс столбца для следующей дисциплины
            $columnIndex++;
        }

        // Увеличиваем индекс строки для следующего студента
        $rowIndex++;
    }
    // Создаем объект Writer
    $writer = new Xlsx($spreadsheet);

    // Устанавливаем заголовки для скачивания
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $filename = $groupName . '.xlsx';
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Выводим содержимое файла в браузер
    $writer->save('php://output');

    exit(); // Останавливаем выполнение скрипта после отправки файла
}

$conn->close();
?>

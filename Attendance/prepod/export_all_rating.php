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
$starttimeId = date('Y-m-d', strtotime($_GET['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_GET['endtimeId']));

// Получаем номер группы
$sqlGroup = "SELECT `groups`.Groups FROM `groups` WHERE `groups`.GroupID = '$groupId';";
$resultGroup = $conn->query($sqlGroup);
$rowGroup = $resultGroup->fetch_assoc();
$groupName = $rowGroup['Groups'];

$userid=$_SESSION['userid'];
// Получаем ID преподавателя
$sqlFIO = "SELECT * FROM attandance.teachers WHERE teachers.UserID = '$userid'";
$resultFIO = $conn->query($sqlFIO);
$rowFIO = $resultFIO->fetch_assoc();
$teacherFIO = $rowFIO['FIO'];
$teacherId= $rowFIO['TeacherID'];

// Получаем DgtID
$sqlDGT = "SELECT discgroupteacher.DgtID FROM discgroupteacher
WHERE discgroupteacher.GroupID = '$groupId' AND discgroupteacher.TeacherID = '$teacherId'";
$resultDGT = $conn->query($sqlDGT);

// Создаем пустой массив для хранения уникальных DgtID
$dgtIds = array();

// Проверяем, есть ли результаты запроса
if ($resultDGT->num_rows > 0) {
    // Перебираем каждую строку результата запроса
    while ($rowDGT = $resultDGT->fetch_assoc()) {
        // Получаем DgtID из текущей строки
        $dgtId = $rowDGT['DgtID'];

        // Добавляем DgtID в массив, если его еще нет там
        if (!in_array($dgtId, $dgtIds)) {
            $dgtIds[] = $dgtId;
        }
    }
} else {
    exit;
}

// Создаем пустой массив для хранения пар DgtID и названий дисциплин
$dgtDisciplinePairs = array();

// Перебираем каждый уникальный DgtID
foreach ($dgtIds as $dgtId) {
    // Получаем названия дисциплин для текущего DgtID
    $sqlDisciplines = "SELECT DISTINCT disciplines.Discipline FROM rating 
        INNER JOIN discgroupteacher ON rating.DgtID = discgroupteacher.DgtID
        INNER JOIN disciplinedirections ON discgroupteacher.DiscDirectionID = disciplinedirections.DiscDirectionID
        INNER JOIN disciplines ON disciplinedirections.DisciplineID = disciplines.DisciplineID
        WHERE rating.DgtID = '$dgtId' 
        AND rating.`Date` >= '$starttimeId' 
        AND rating.`Date` <= '$endtimeId'";
    $resultDisciplines = $conn->query($sqlDisciplines);

    // Создаем массив для хранения названий дисциплин для текущего DgtID
    $disciplinesForDgt = array();

    // Перебираем результаты запроса и добавляем названия дисциплин в массив
    while ($rowDiscipline = $resultDisciplines->fetch_assoc()) {
        $disciplinesForDgt[] = $rowDiscipline['Discipline'];
    }

    // Добавляем пару DgtID и названий дисциплин в массив
    $dgtDisciplinePairs[$dgtId] = $disciplinesForDgt;
}

if (!empty($dgtDisciplinePairs)){
    // Создаем массив для хранения данных о студентах и их баллах по дисциплинам
    $studentScores = array();

    // Получаем список студентов в группе
    $sqlStudents = "SELECT * FROM `students` WHERE `GroupID` = '$groupId'";
    $resultStudents = $conn->query($sqlStudents);

    // Перебираем каждого студента
    while ($rowStudent = $resultStudents->fetch_assoc()) {
        $studentId = $rowStudent['StudentID'];
        $FIO = $rowStudent['FIO'];

        // Создаем массив для хранения суммарных баллов по дисциплинам для текущего студента
        $studentScores[$FIO] = array();

        // Перебираем каждую пару DgtID и названий дисциплин
        foreach ($dgtDisciplinePairs as $dgtId => $disciplinesForDgt) {
            // Перебираем каждую дисциплину из списка для текущего DgtID
            foreach ($disciplinesForDgt as $discipline) {
                // Получаем суммарное количество баллов для текущего студента и дисциплины за указанный период
                $sqlScore = "SELECT students.FIO, SUM(rating.`Value`) AS TotalValue FROM students 
                    LEFT JOIN rating ON students.StudentID = rating.StudentID 
                    WHERE students.StudentID = '$studentId' AND rating.DgtID = '$dgtId'
                    AND rating.`Date` >= '$starttimeId' AND rating.`Date` <= '$endtimeId' AND students.Expelled = 0
                    GROUP BY students.StudentID";
                $resultScore = $conn->query($sqlScore);
                $rowScore = $resultScore->fetch_assoc();

                // Сохраняем суммарное количество баллов для текущей дисциплины студента
                $totalScore = $rowScore['TotalValue'];
                $studentScores[$FIO][$discipline] = $totalScore;
            }
        }
    }

    if (!empty($studentScores)) {
        // Создаем пустой массив для хранения данных о студентах и их суммарных рейтингах
        $studentTotalScores = array();

        // Перебираем каждого студента
        foreach ($studentScores as $FIO => $scores) {
            $totalScore = 0; // Инициализируем переменную для хранения суммарного рейтинга студента

            foreach ($scores as $discipline => $disciplineScore) {
                $totalScore += $disciplineScore; // Добавляем рейтинг по текущей дисциплине к общему рейтингу студента
            }

            // Добавляем суммарный рейтинг студента в массив
            $studentTotalScores[$FIO] = $totalScore;
        }
        // Сортируем студентов по суммарному рейтингу в убывающем порядке
        arsort($studentTotalScores);
        
        // Создаем пустой массив для хранения всех дисциплин
        $allDisciplines = array();

        // Перебираем каждую пару DgtID и названий дисциплин
        foreach ($dgtDisciplinePairs as $dgtId => $disciplinesForDgt) {
            // Добавляем все дисциплины в общий массив
            $allDisciplines = array_merge($allDisciplines, $disciplinesForDgt);
        }

        // Убираем дубликаты дисциплин
        $allDisciplines = array_unique($allDisciplines);
        
        // Создаем новый объект Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Получаем активный лист
        $sheet = $spreadsheet->getActiveSheet();

        // Добавляем заголовок
        $starttimeId = date('d.m.y', strtotime($_GET['starttimeId']));
        $endtimeId = date('d.m.y', strtotime($_GET['endtimeId']));
        $header = 'Группа ' . $groupName . '. С ' . $starttimeId . ' по ' . $endtimeId;
        $sheet->setCellValue('A1', $header);

        // Добавляем заголовки столбцов
        $sheet->setCellValue('A2', 'ФИО');
        foreach ($allDisciplines as $index => $discipline) {
            $columnIndex = $index + 2; // Индекс столбца начиная с 2 (A - ФИО)
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . '2', $discipline);
        }

        // Добавляем столбец для суммарного рейтинга
        $lastColumnIndex = count($allDisciplines) + 2; // Индекс последнего столбца
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex) . '2', 'Суммарный рейтинг');

        // Заполняем данные о студентах
        $rowIndex = 3; // Начинаем со строки 3
        foreach ($studentTotalScores as $FIO => $totalScore) {
            $sheet->setCellValue('A' . $rowIndex, $FIO);

            // Заполняем оценки по дисциплинам для текущего студента
            foreach ($allDisciplines as $index => $discipline) {
                $columnIndex = $index + 2; // Индекс столбца начиная с 2 (A - ФИО)
                $score = isset($studentScores[$FIO][$discipline]) ? $studentScores[$FIO][$discipline] : 0;
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($columnLetter . $rowIndex, $score);

            }

            // Добавляем суммарный рейтинг
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex) . $rowIndex, $totalScore);

            $rowIndex++; // Переходим к следующей строке
        }

        // Создаем объект Writer
        $writer = new Xlsx($spreadsheet);

        // Устанавливаем заголовки для скачивания
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $filename = $groupName . '_Рейтинг.xlsx';
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Выводим содержимое файла в браузер
        $writer->save('php://output');

        exit(); // Останавливаем выполнение скрипта после отправки файла
    }
}

$conn->close();
?>


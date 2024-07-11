<?php

function nextColumn($col) {
    // Переход к следующему столбцу по алфавиту
    return ++$col;
}

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
$dgtId = $rowDGT['DgtID'];

// Запрос для получения данных ФИО и суммарного количества пропусков по данной дисциплине
$sql = "SELECT students.FIO, SUM(rating.`Value`) AS TotalValue, rating.Date, rating.TypeID FROM students 
        LEFT JOIN rating ON students.StudentID = rating.StudentID 
        WHERE students.GroupID = '$groupId' AND rating.DgtID = '$dgtId'
        AND rating.`Date` >= '$starttimeId' AND rating.`Date` <= '$endtimeId' AND students.Expelled = 0
        GROUP BY students.StudentID, rating.Date, rating.TypeID"; // Сортировка в порядке убывания, чтобы сначала были наибольшие значения

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

    // Создаем столбцы для каждой комбинации даты и типа пропуска
    $dateTypes = array();
    $dateTypeHeaders = array(); // Для хранения заголовков столбцов
    while ($row = $result->fetch_assoc()) {
        $date = date('d.m.y', strtotime($row['Date']));
        $typeId = $row['TypeID'];
        $type = ''; // Здесь будет название типа

        // Получаем название типа по TypeID из классификатора
        $sqlType = "SELECT `Type` FROM classtypes WHERE TypeID = '$typeId'";
        $resultType = $conn->query($sqlType);
        if ($resultType->num_rows > 0) {
            $rowType = $resultType->fetch_assoc();
            $type = $rowType['Type'];
        }

        $dateType = $date . ' ' . $type;

        // Если этот столбец еще не был добавлен, добавляем его
        if (!in_array($dateType, $dateTypeHeaders)) {
            $dateTypeHeaders[] = $dateType;
            $dateTypes[] = array('date' => $date, 'type' => $type);
        }
    }
    
    // Сортируем массив $dateTypes по дате
    usort($dateTypes, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });

    $columnIndex = 'B'; // Начинаем с столбца B

    foreach ($dateTypes as $dateType) {
        $sheet->setCellValue($columnIndex . '2', $dateType['date'] . ' ' . $dateType['type']);
        $columnIndex = nextColumn($columnIndex); // Переходим к следующему столбцу
    }

    $rowIndex = 3; // Индекс строки для данных

    // Создаем массив для хранения значений Pass для каждого студента по каждой комбинации даты и типа пропуска
    $studentAbsences = array();

    // Возвращаем указатель результата к началу
    $result->data_seek(0);

    while ($row = $result->fetch_assoc()) {
        $studentFIO = $row['FIO'];

        // Если студент еще не был добавлен в массив, добавляем его
        if (!isset($studentAbsences[$studentFIO])) {
            $studentAbsences[$studentFIO] = array_fill_keys(array_column($dateTypes, 'date'), array());
        }

        $date = date('d.m.y', strtotime($row['Date']));
        $typeId = $row['TypeID'];
        $type = ''; // Название типа

        // Получаем название типа по TypeID из классификатора
        $sqlType = "SELECT `Type` FROM classtypes WHERE TypeID = '$typeId'";
        $resultType = $conn->query($sqlType);
        if ($resultType->num_rows > 0) {
            $rowType = $resultType->fetch_assoc();
            $type = $rowType['Type'];
        }

        $dateType = $date . ' ' . $type;

        // Заполняем массив значениями Pass для каждого студента по каждой комбинации даты и типа пропуска
        $studentAbsences[$studentFIO][$date][$type] = $row['TotalValue'];
    }

    // Заполняем данные о пропусках студентов в таблицу
    foreach ($studentAbsences as $studentFIO => $absences) {
        $sheet->setCellValue('A' . $rowIndex, $studentFIO); // Записываем ФИО в столбец A
        $columnIndex = 'B'; // Начинаем со столбца B

        foreach ($dateTypes as $dateType) {
            // Заполняем данные о пропусках студента в соответствующие столбцы
            $pass = isset($absences[$dateType['date']][$dateType['type']]) ? $absences[$dateType['date']][$dateType['type']] : '';
            $sheet->setCellValue($columnIndex . $rowIndex, $pass);
            $columnIndex = nextColumn($columnIndex); // Переходим к следующему столбцу
        }
        
        $rowIndex++; // Переходим к следующей строке
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

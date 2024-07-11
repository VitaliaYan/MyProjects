<?php
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$groupId = $_POST['groupId'];
$disciplineId = $_POST['disciplineId'];
$starttimeId = date('Y-m-d', strtotime($_POST['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_POST['endtimeId']));

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

// Запрос для получения данных ФИО, даты и типа пропуска
$sql = "SELECT students.FIO, attandance.Date, attandance.TypeID, attandance.Pass 
        FROM students 
        LEFT JOIN attandance ON students.StudentID = attandance.StudentID 
        WHERE students.GroupID = '$groupId' 
        AND attandance.DisciplineID = '$disciplineId'
        AND attandance.Date >= '$starttimeId' 
        AND attandance.Date <= '$endtimeId'
        AND students.Expelled = 0;";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $starttimeId = date('d.m.y', strtotime($_POST['starttimeId']));
    $endtimeId = date('d.m.y', strtotime($_POST['endtimeId']));
    // Формируем HTML-код для таблицы
    $output = '<label>Группа ' . $groupName . '. Дисциплина: ' . $disciplineName . '. C: ' . $starttimeId . '. По: ' . $endtimeId . '</label>';
    $output .= '<table id="attandance-table2">';
    $output .= '<tr><th>ФИО</th>';

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

    foreach ($dateTypes as $dateType) {
        $output .= '<th>' . $dateType['date'] . ' ' . $dateType['type'] . '</th>';
    }
    $output .= '</tr>';

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
        $studentAbsences[$studentFIO][$date][$type] = $row['Pass'];
    }

    // Выводим данные о пропусках студентов в таблицу
    foreach ($studentAbsences as $studentFIO => $absences) {
        $output .= '<tr>';
        $output .= '<td>' . $studentFIO . '</td>';
        foreach ($dateTypes as $dateType) {
            $output .= '<td style="text-align: center;">';
            if (isset($absences[$dateType['date']][$dateType['type']])) {
                $output .= $absences[$dateType['date']][$dateType['type']];
            }
            $output .= '</td>';
        }
        $output .= '</tr>';
    }

    $output .= '</table>';
    
    echo json_encode(['success' => $output]);
} else {
    echo json_encode(['errors' => 'Нет данных.']);
}

$conn->close();
?>

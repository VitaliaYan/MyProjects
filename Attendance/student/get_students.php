<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$groupId = $_POST['groupId'];
$disciplineId = $_POST['disciplineId'];
$classId = $_POST['classId'];
$date = date('Y-m-d', strtotime($_POST['date']));
$typeId = $_POST['typeId'];

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

// Получаем название дисциплины
$sqlType = "SELECT classtypes.Type FROM classtypes WHERE classtypes.TypeID = '$typeId'";
$resultType = $conn->query($sqlType);
$rowType = $resultType->fetch_assoc();
$typeName = $rowType['Type'];

// Получаем название пары
$sqlClass = "SELECT * FROM attandance.classes WHERE classes.ClassID = '$classId'";
$resultClass = $conn->query($sqlClass);
$rowClass = $resultClass->fetch_assoc();

$sql = "SELECT FIO FROM attandance.students INNER JOIN attandance.groups ON `groups`.GroupID = students.GroupID WHERE `groups`.GroupId = '$groupId' AND students.Expelled = 0;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $date = date('d.m.y', strtotime($_POST['date']));
    // Формируем HTML-код для таблицы
    $output = '<label style="text-align: center;">Группа ' . $groupName . '. Дисциплина: ' . $disciplineName . '.<br> Дата: ' . $date . '. ' . $rowClass["ClassNumber"] . ' пара: ' . $rowClass["ClassTime"] . '. ' . $typeName . '</label>';
    $output .= '<table id="attandance-table">';
    $output .= '<tr><th>ФИО</th><th>Пропуски</th></tr>';

    while ($row = $result->fetch_assoc()) {
        $output .= '<tr>';
        $output .= '<td>' . $row["FIO"] . '</td>';
        $output .= '<td>';
        $output .= '<select name="pass">';
        $output .= '<option value="0">0</option>';
        $output .= '<option value="1">1</option>';
        $output .= '<option value="2">2</option>';
        $output .= '</select>';
        $output .= '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';
    
    echo $output;
} else {
    echo "Нет данных.";
}

$conn->close();
?>
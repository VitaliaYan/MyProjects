<?php
// Подключение к базе данных и выполнение запроса
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

// Запрос для получения данных ФИО и суммарного количества пропусков по данной дисциплине
$sql = "SELECT students.FIO, SUM(attandance.Pass) AS TotalPass FROM students 
        LEFT JOIN attandance ON students.StudentID = attandance.StudentID 
        WHERE students.GroupID = '$groupId' AND attandance.DisciplineID = '$disciplineId'
        AND attandance.Date >= '$starttimeId' AND attandance.Date <= '$endtimeId' AND students.Expelled = 0
        GROUP BY students.StudentID";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Формируем HTML-код для таблицы
    $starttimeId = date('d.m.y', strtotime($_POST['starttimeId']));
    $endtimeId = date('d.m.y', strtotime($_POST['endtimeId']));
    $output = '<label>Группа ' . $groupName . '. Дисциплина: ' . $disciplineName . '. C: ' . $starttimeId . '. По: ' . $endtimeId . '</label>';
    $output .= '<table id="attandance-table"';
    $output .= '<tr><th>ФИО</th><th>Пропуски</th></tr>';

    while ($row = $result->fetch_assoc()) {
        $output .= '<tr style="border: 2px solid #fff; border-radius: 4px;">';
        $output .= '<td>' . $row["FIO"] . '</td>';
        $output .= '<td id="totalpass">' . $row["TotalPass"] . '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';
    
    echo json_encode(['success' => $output]);
} else {
    echo json_encode(['errors' => 'Нет данных.']);
}

$conn->close();
?>

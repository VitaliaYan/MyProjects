<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$fio = isset($_POST['fio']) ? $_POST['fio'] : null;
$group = isset($_POST['group']) ? $_POST['group'] : null;

// Начало SQL-запроса
$sql = "SELECT students.FIO, `groups`.Groups, students.Head FROM attandance.students 
        LEFT JOIN attandance.`groups` ON `groups`.GroupID = students.GroupID WHERE students.Expelled = 0 ";

// Условия для поиска
$whereClauses = [];

if ($fio !== null && $fio !== '') {
    $whereClauses[] = "students.FIO LIKE '%" . $conn->real_escape_string($fio) . "%'";
}

if ($group !== null && $group !== '') {
    $whereClauses[] = "`groups`.Groups LIKE '%" . $conn->real_escape_string($group) . "%'";
}

// Добавление условий к запросу
if (!empty($whereClauses)) {
    $sql .= ' AND ' . implode(" AND ", $whereClauses);
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
$output = '<table id="attandance-table"';
$output .= '<tr><th>ФИО</th><th>Группа</th><th>Староста</th></tr>';
    while ($row = $result->fetch_assoc()) {
    $output .= '<tr style="border: 2px solid #fff; border-radius: 4px;">';
    $output .= '<td>' . $row["FIO"] . '</td>';
    $output .= '<td style="text-align: center;">' . $row["Groups"] . '</td>';
    // Проверяем значение столбца Head
    $headContent = $row["Head"] == 1 ? '&#10003;' : ''; // Используем символ Unicode для галочки
    $output .= '<td style="text-align: center;">' . $headContent . '</td>';
    $output .= '</tr>';
    }

    $output .= '</table>';

    echo $output;
}
$conn->close();
?>

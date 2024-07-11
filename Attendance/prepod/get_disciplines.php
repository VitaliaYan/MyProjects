<?php
// Подключение к базе данных
require_once('../config.php');
// Создание подключения
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных из AJAX-запроса
$groupId = $_POST['groupId'];
$termId = $_POST['termId'];

$sql = "SELECT disciplines.Discipline, disciplines.DisciplineID 
        FROM `groups` 
        INNER JOIN disciplinedirections ON `groups`.DirectionID = disciplinedirections.DirectionID
        INNER JOIN disciplines ON disciplines.DisciplineID = disciplinedirections.DisciplineID
        WHERE `groups`.GroupID = $groupId AND disciplinedirections.TermID = $termId";
$result = $conn->query($sql);

$disciplines = array();
while($row = $result->fetch_assoc()) {
    $disciplines[] = $row;
}

// Отправка дисциплин в формате JSON
header('Content-Type: application/json');
echo json_encode($disciplines);
?>
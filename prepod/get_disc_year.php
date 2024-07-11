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
$yearId = $_POST['yearId'];

switch ($yearId) {
    case 1:
        $termfrom = 1;
        $termto = 2;
        break;
    case 2:
        $termfrom = 3;
        $termto = 4;
        break;
    case 3:
        $termfrom = 5;
        $termto = 6;
        break;
    case 4:
        $termfrom = 7;
        $termto = 8;
        break;
    }

$sql = "SELECT disciplines.Discipline, disciplines.DisciplineID 
        FROM `groups` 
        INNER JOIN disciplinedirections ON `groups`.DirectionID = disciplinedirections.DirectionID
        INNER JOIN disciplines ON disciplines.DisciplineID = disciplinedirections.DisciplineID
        WHERE `groups`.`Year` = $yearId AND disciplinedirections.TermID >= $termfrom AND disciplinedirections.TermID <= $termto";
$result = $conn->query($sql);

$disciplines = array();
while($row = $result->fetch_assoc()) {
    $disciplines[] = $row;
}

// Отправка дисциплин в формате JSON
header('Content-Type: application/json');
echo json_encode($disciplines);
?>
<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

$groupId = $_POST['groupId'];
$disciplineId = $_POST['disciplineId'];
$starttimeId = date('Y-m-d', strtotime($_POST['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_POST['endtimeId']));
$termId = $_POST['termId'];

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

if ($rowDGT !== null) {
    $dgtId = $rowDGT['DgtID'];
} else {
    echo json_encode(['errors' => 'У вас нет прав доступа для просмотра рейтинга для данной группы и дисциплины.']);
    exit;
}

// Запрос для получения данных ФИО и суммарного количества пропусков по данной дисциплине
$sql = "SELECT students.FIO, SUM(rating.`Value`) AS TotalValue FROM students 
        LEFT JOIN rating ON students.StudentID = rating.StudentID 
        WHERE students.GroupID = '$groupId' AND rating.DgtID = '$dgtId'
        AND rating.`Date` >= '$starttimeId' AND rating.`Date` <= '$endtimeId' AND students.Expelled = 0
        GROUP BY students.StudentID
        ORDER BY TotalValue DESC"; // Сортировка в порядке убывания, чтобы сначала были наибольшие значения

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Формируем HTML-код для таблицы
    $starttimeId = date('d.m.y', strtotime($_POST['starttimeId']));
    $endtimeId = date('d.m.y', strtotime($_POST['endtimeId']));
    $output = '<label>Группа ' . $groupName . '. Дисциплина: ' . $disciplineName . '. C: ' . $starttimeId . '. По: ' . $endtimeId . '</label>';
    $output .= '<table id="attandance-table"';
    $output .= '<tr><th>ФИО</th><th>Рейтинг</th></tr>';

    while ($row = $result->fetch_assoc()) {
        $output .= '<tr style="border: 2px solid #fff; border-radius: 4px;">';
        $output .= '<td>' . $row["FIO"] . '</td>';
        $output .= '<td id="totalvalue" style="text-align: center;">' . $row["TotalValue"] . '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';
    
    echo json_encode(['success' => $output]);
} else {
    echo json_encode(['errors' => 'Нет данных за данный промежуток времени.']);
}

$conn->close();
?>

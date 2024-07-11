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
$classId = $_POST['classId'];
$date = $_POST['date'];
$termId = $_POST['termId'];
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
$sqlClass = "SELECT * FROM attandance.Classes WHERE classes.ClassID = '$classId'";
$resultClass = $conn->query($sqlClass);
$rowClass = $resultClass->fetch_assoc();

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
    echo json_encode(['errors' => 'У вас нет прав доступа для добавления рейтинга для данной группы и дисциплины.']);
    exit;
}

$sql = "SELECT FIO FROM attandance.students INNER JOIN attandance.groups ON `groups`.GroupID = students.GroupID WHERE `groups`.GroupId = '$groupId' AND students.Expelled = 0;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Формируем HTML-код для таблицы
    $output = '<label style="text-align: center;">Здравствуйте, '. $teacherFIO . '!</label>';
    $output .= '<label style="text-align: center;">Группа ' . $groupName . '. Дисциплина: ' . $disciplineName . '.<br> Дата: ' . $date . '. ' . $rowClass["ClassNumber"] . ' пара: ' . $rowClass["ClassTime"] . '. ' . $typeName . '</label>';
    $output .= '<table id="attandance-table">';
    $output .= '<tr><th>ФИО</th><th>Рейтинг</th></tr>';

    while ($row = $result->fetch_assoc()) {
        $output .= '<tr>';
        $output .= '<td>' . $row["FIO"] . '</td>';
        $output .= '<td>';
        $output .= '<select name="rat_value">';
        for ($i = -1; $i<=100; $i++)
        {
            $output .= '<option value="' . $i . '">' . $i . '</option>';
        }
        $output .= '</select>';
        $output .= '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';
    
    echo json_encode(['success' => $output]);
}

$conn->close();
?>
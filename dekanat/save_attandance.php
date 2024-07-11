<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$groupId = $_POST['groupId'];
$starttimeId = date('Y-m-d', strtotime($_POST['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_POST['endtimeId']));

// Получаем номер группы
$sqlGroup = "SELECT `groups`.Groups FROM `groups` WHERE `groups`.GroupID = '$groupId';";
$resultGroup = $conn->query($sqlGroup);
$rowGroup = $resultGroup->fetch_assoc();
$groupName = $rowGroup['Groups'];

// Получаем номер группы
$sqlStudID = "SELECT * FROM attandance.`students` WHERE `students`.GroupID = '$groupId' LIMIT 1;";
$resultStudID = $conn->query($sqlStudID);
$rowStud = $resultStudID->fetch_assoc();
$studentId = $rowStud['StudentID'];

// Получаем название дисциплины
$sqlDisciplines = "SELECT DISTINCT `attandance`.DisciplineID FROM attandance.`attandance` WHERE `attandance`.StudentID='$studentId' AND attandance.Date >= '$starttimeId' AND attandance.Date <= '$endtimeId';";
$resultDisciplines = $conn->query($sqlDisciplines);
$disciplines = array(); // Создаем пустой массив для хранения названий дисциплин
// Перебираем каждую строку результата запроса
while ($rowDisciplines = $resultDisciplines->fetch_assoc()) {
    // Добавляем название дисциплины из текущей строки в массив
    $disciplines[] = $rowDisciplines['DisciplineID'];
}

if (!empty($disciplines)){
    // Создаем массив для хранения данных о студентах и их баллах по дисциплинам
    $studentScores = array();

    // Получаем список студентов в группе
    $sqlStudents = "SELECT * FROM attandance.students WHERE students.GroupID = '$groupId' AND students.Expelled = 0;";
    $resultStudents = $conn->query($sqlStudents);

    // Перебираем каждого студента
    while ($rowStudent = $resultStudents->fetch_assoc()) {
        $studentID = $rowStudent['StudentID'];
        $FIO = $rowStudent['FIO'];

        // Создаем массив для хранения суммарных баллов по дисциплинам для текущего студента
        $studentScores[$FIO] = array();

        // Перебираем каждую дисциплину из списка
        foreach ($disciplines as $disciplineID) {
            // Получаем суммарное количество баллов для текущего студента и дисциплины за указанный период
            $sqlScore = "SELECT SUM(`Pass`) AS totalScore FROM `attandance` WHERE `StudentID`='$studentID' AND `DisciplineID`='$disciplineID' AND `Date` BETWEEN '$starttimeId' AND '$endtimeId'";
            $resultScore = $conn->query($sqlScore);
            $rowScore = $resultScore->fetch_assoc();

            // Сохраняем суммарное количество баллов для текущей дисциплины студента
            $totalScore = $rowScore['totalScore'];
            $studentScores[$FIO][$disciplineID] = $totalScore;
        }
    }
    if (!empty($studentScores)) {
        // Формируем HTML-код для таблицы
        $starttimeId = date('d.m.y', strtotime($_POST['starttimeId']));
        $endtimeId = date('d.m.y', strtotime($_POST['endtimeId']));
        $output = '<label>Группа ' . $groupName . '. С ' . $starttimeId . ' по ' . $endtimeId . '</label>';
        $output .= '<table id="attandance-table2">';
        $output .= '<thead><tr><th>ФИО</th>';

        // Добавляем столбцы для каждой дисциплины
        foreach ($disciplines as $disciplineID) {
            // Получаем название дисциплины по ID
            $sqlDisciplineName = "SELECT `Discipline` FROM `disciplines` WHERE `DisciplineID` = '$disciplineID'";
            $resultDisciplineName = $conn->query($sqlDisciplineName);
            $rowDisciplineName = $resultDisciplineName->fetch_assoc();
            $disciplineName = $rowDisciplineName['Discipline'];
            $output .= '<th style="text-align: center;" title="' . $disciplineName . '">' . $disciplineName . '</th>';
        }

        $output .= '</tr></thead><tbody>';
        // Для каждого студента добавляем строку в таблицу
        foreach ($studentScores as $FIO => $scores) {
            $output .= '<tr>';
            $output .= '<td>' . $FIO . '</td>';

            // Для каждой дисциплины добавляем ячейку с количеством баллов студента
            foreach ($disciplines as $disciplineID) {
                $score = isset($scores[$disciplineID]) ? $scores[$disciplineID] : 0;
                $output .= '<td style="text-align: center;">' . $score . '</td>';
            }

            $output .= '</tr>';
        }
        $output .= '</tbody></table>';

        echo json_encode(['success' => $output]);
    } else {
        echo json_encode(['errors' => 'Нет данных.']);
    }
} else{
    echo json_encode(['errors' => 'Нет данных.']);
}

$conn->close();
?>


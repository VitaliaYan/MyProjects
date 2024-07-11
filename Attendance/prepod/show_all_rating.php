<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

$groupId = $_POST['groupId'];
$starttimeId = date('Y-m-d', strtotime($_POST['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_POST['endtimeId']));

// Получаем номер группы
$sqlGroup = "SELECT `groups`.Groups FROM `groups` WHERE `groups`.GroupID = '$groupId';";
$resultGroup = $conn->query($sqlGroup);
$rowGroup = $resultGroup->fetch_assoc();
$groupName = $rowGroup['Groups'];

$userid=$_SESSION['userid'];
// Получаем ID преподавателя
$sqlFIO = "SELECT * FROM attandance.teachers WHERE teachers.UserID = '$userid'";
$resultFIO = $conn->query($sqlFIO);
$rowFIO = $resultFIO->fetch_assoc();
$teacherFIO = $rowFIO['FIO'];
$teacherId= $rowFIO['TeacherID'];

// Получаем DgtID
$sqlDGT = "SELECT discgroupteacher.DgtID FROM discgroupteacher
WHERE discgroupteacher.GroupID = '$groupId' AND discgroupteacher.TeacherID = '$teacherId'";
$resultDGT = $conn->query($sqlDGT);

// Создаем пустой массив для хранения уникальных DgtID
$dgtIds = array();

// Проверяем, есть ли результаты запроса
if ($resultDGT->num_rows > 0) {
    // Перебираем каждую строку результата запроса
    while ($rowDGT = $resultDGT->fetch_assoc()) {
        // Получаем DgtID из текущей строки
        $dgtId = $rowDGT['DgtID'];

        // Добавляем DgtID в массив, если его еще нет там
        if (!in_array($dgtId, $dgtIds)) {
            $dgtIds[] = $dgtId;
        }
    }
} else {
    // Если результатов нет, выводим сообщение об отсутствии дисциплин и завершаем выполнение скрипта
    echo json_encode(['errors' => 'У вас нет дисциплин с данной группой.']);
    exit;
}

// Создаем пустой массив для хранения пар DgtID и названий дисциплин
$dgtDisciplinePairs = array();

// Перебираем каждый уникальный DgtID
foreach ($dgtIds as $dgtId) {
    // Получаем названия дисциплин для текущего DgtID
    $sqlDisciplines = "SELECT DISTINCT disciplines.Discipline FROM rating 
        INNER JOIN discgroupteacher ON rating.DgtID = discgroupteacher.DgtID
        INNER JOIN disciplinedirections ON discgroupteacher.DiscDirectionID = disciplinedirections.DiscDirectionID
        INNER JOIN disciplines ON disciplinedirections.DisciplineID = disciplines.DisciplineID
        WHERE rating.DgtID = '$dgtId' 
        AND rating.`Date` >= '$starttimeId' 
        AND rating.`Date` <= '$endtimeId'";
    $resultDisciplines = $conn->query($sqlDisciplines);

    // Создаем массив для хранения названий дисциплин для текущего DgtID
    $disciplinesForDgt = array();

    // Перебираем результаты запроса и добавляем названия дисциплин в массив
    while ($rowDiscipline = $resultDisciplines->fetch_assoc()) {
        $disciplinesForDgt[] = $rowDiscipline['Discipline'];
    }

    // Добавляем пару DgtID и названий дисциплин в массив
    $dgtDisciplinePairs[$dgtId] = $disciplinesForDgt;
}

if (!empty($dgtDisciplinePairs)){
    // Создаем массив для хранения данных о студентах и их баллах по дисциплинам
    $studentScores = array();

    // Получаем список студентов в группе
    $sqlStudents = "SELECT * FROM attandance.students WHERE students.`GroupID` = '$groupId' AND students.Expelled = 0";
    $resultStudents = $conn->query($sqlStudents);

    // Перебираем каждого студента
    while ($rowStudent = $resultStudents->fetch_assoc()) {
        $studentId = $rowStudent['StudentID'];
        $FIO = $rowStudent['FIO'];

        // Создаем массив для хранения суммарных баллов по дисциплинам для текущего студента
        $studentScores[$FIO] = array();

        // Перебираем каждую пару DgtID и названий дисциплин
        foreach ($dgtDisciplinePairs as $dgtId => $disciplinesForDgt) {
            // Перебираем каждую дисциплину из списка для текущего DgtID
            foreach ($disciplinesForDgt as $discipline) {
                // Получаем суммарное количество баллов для текущего студента и дисциплины за указанный период
                $sqlScore = "SELECT students.FIO, SUM(rating.`Value`) AS TotalValue FROM students 
                    LEFT JOIN rating ON students.StudentID = rating.StudentID 
                    WHERE students.StudentID = '$studentId' AND rating.DgtID = '$dgtId'
                    AND rating.`Date` >= '$starttimeId' AND rating.`Date` <= '$endtimeId' AND students.Expelled = 0
                    GROUP BY students.StudentID";
                $resultScore = $conn->query($sqlScore);
                $rowScore = $resultScore->fetch_assoc();

                // Сохраняем суммарное количество баллов для текущей дисциплины студента
                $totalScore = $rowScore['TotalValue'];
                $studentScores[$FIO][$discipline] = $totalScore;
            }
        }
    }
    if (!empty($studentScores)) {
        // Создаем пустой массив для хранения данных о студентах и их суммарных рейтингах
        $studentTotalScores = array();

        // Перебираем каждого студента
        foreach ($studentScores as $FIO => $scores) {
            $totalScore = 0; // Инициализируем переменную для хранения суммарного рейтинга студента

            // Перебираем каждую дисциплину и вычисляем суммарный рейтинг студента по каждой дисциплине
            foreach ($scores as $disciplineScore) {
                $totalScore += $disciplineScore; // Добавляем рейтинг по текущей дисциплине к общему рейтингу студента
            }

            // Добавляем суммарный рейтинг студента в массив
            $studentTotalScores[$FIO] = $totalScore;
        }
        // Сортируем студентов по суммарному рейтингу в убывающем порядке
        arsort($studentTotalScores);
        
        // Создаем пустой массив для хранения всех дисциплин
        $allDisciplines = array();

        // Перебираем каждую пару DgtID и названий дисциплин
        foreach ($dgtDisciplinePairs as $dgtId => $disciplinesForDgt) {
            // Добавляем все дисциплины в общий массив
            $allDisciplines = array_merge($allDisciplines, $disciplinesForDgt);
        }

        // Убираем дубликаты дисциплин
        $allDisciplines = array_unique($allDisciplines);

        // Проверяем наличие дисциплин
        if (empty($allDisciplines)) {
            echo json_encode(['errors' => 'Нет данных за данный промежуток времени.']);
            exit;
        } else {
            // Формируем HTML-код для таблицы
            $starttimeId = date('d.m.y', strtotime($_POST['starttimeId']));
            $endtimeId = date('d.m.y', strtotime($_POST['endtimeId']));
            $output = '<label>Группа ' . $groupName . '. С ' . $starttimeId . ' по ' . $endtimeId . '</label>';
            $output .= '<table id="attandance-table2">';
            $output .= '<thead><tr><th>ФИО</th>';

            // Добавляем столбцы для каждой дисциплины
            foreach ($allDisciplines as $discipline) {
                $output .= '<th style="text-align: center;" title="' . $discipline . '">' . $discipline . '</th>';
            }

            // Добавляем столбец для суммарного рейтинга
            $output .= '<th style="text-align: center;">Суммарный рейтинг</th>';
            $output .= '</tr></thead><tbody>';

            // Для каждого студента добавляем строку в таблицу
            foreach ($studentTotalScores as $FIO => $totalScore) {
                $output .= '<tr>';
                $output .= '<td>' . $FIO . '</td>';

                // Получаем суммарный рейтинг по каждой дисциплине для текущего студента
                foreach ($allDisciplines as $discipline) {
                    // Получаем баллы студента по дисциплине из $studentScores
                    $score = isset($studentScores[$FIO][$discipline]) ? $studentScores[$FIO][$discipline] : 0;
                    $output .= '<td style="text-align: center;">' . $score . '</td>';
                }

                // Добавляем ячейку с суммарным рейтингом
                $output .= '<td style="text-align: center;">' . $totalScore . '</td>';
                $output .= '</tr>';
            }

            $output .= '</tbody></table>';

            echo json_encode(['success' => $output]);
        }
    } else {
        echo json_encode(['errors' => 'Нет данных.']);
    }
} else{
    echo json_encode(['errors' => 'Нет данных.']);
}

$conn->close();
?>


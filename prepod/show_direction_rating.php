<?php
// Подключение к базе данных и выполнение запроса
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

$disciplineId = $_POST['disciplineId'];
$starttimeId = date('Y-m-d', strtotime($_POST['starttimeId']));
$endtimeId = date('Y-m-d', strtotime($_POST['endtimeId']));
$yearId = $_POST['yearId'];

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

// Получаем номера групп, у которых есть записи в таблице rating за указанный период времени
$sqlDgt = "SELECT DISTINCT rating.DgtID FROM `groups`
              INNER JOIN discgroupteacher ON `groups`.GroupID = discgroupteacher.GroupID
              INNER JOIN disciplinedirections ON discgroupteacher.DiscDirectionID = disciplinedirections.DiscDirectionID 
              INNER JOIN disciplines ON disciplinedirections.DisciplineID = disciplines.DisciplineID
              INNER JOIN rating ON discgroupteacher.DgtID = rating.DgtID
              WHERE `groups`.`Year` = '$yearId'
              AND disciplinedirections.DisciplineID = '$disciplineId' 
              AND discgroupteacher.TeacherID = '$teacherId'";
$resultDgt = $conn->query($sqlDgt);

// Проверяем количество строк в результате запроса
if ($resultDgt->num_rows === 0) {
    echo json_encode(['errors' => 'У вас нет прав доступа для просмотра рейтинга по данной дисциплине.']);
    exit; // Прекращаем выполнение скрипта
} elseif ($resultDgt->num_rows > 0) {
    // Получаем номера групп, у которых есть записи в таблице rating за указанный период времени
    $sqlGroups = "SELECT DISTINCT `groups`.GroupID, `groups`.Groups, rating.DgtID FROM `groups`
    INNER JOIN discgroupteacher ON `groups`.GroupID = discgroupteacher.GroupID
    INNER JOIN disciplinedirections ON discgroupteacher.DiscDirectionID = disciplinedirections.DiscDirectionID 
    INNER JOIN disciplines ON disciplinedirections.DisciplineID = disciplines.DisciplineID
    INNER JOIN rating ON discgroupteacher.DgtID = rating.DgtID
    WHERE `groups`.`Year` = '$yearId'
    AND rating.`Date` >= '$starttimeId'
    AND rating.`Date` <= '$endtimeId'
    AND disciplinedirections.DisciplineID = '$disciplineId' 
    AND discgroupteacher.TeacherID = '$teacherId'";
    $resultGroups = $conn->query($sqlGroups);
    if ($resultGroups->num_rows > 0) {
        // Создаем пустой массив для хранения данных о студентах
        $studentsData = array();

        // Перебираем результаты запроса о группах
        while ($rowGroup = $resultGroups->fetch_assoc()) {
            $groupId = $rowGroup['GroupID'];
            $groupName = $rowGroup['Groups'];
            $dgtId = $rowGroup['DgtID'];

            // Запрос для получения данных ФИО и суммарного рейтинга по данной группе и дисциплине
            $sqlStudents = "SELECT students.FIO, SUM(rating.`Value`) AS TotalValue 
                            FROM students 
                            LEFT JOIN rating ON students.StudentID = rating.StudentID 
                            WHERE students.GroupID = '$groupId' 
                            AND rating.DgtID = '$dgtId'
                            AND rating.`Date` >= '$starttimeId' 
                            AND rating.`Date` <= '$endtimeId' 
                            AND students.Expelled = 0
                            GROUP BY students.StudentID";

            $resultStudents = $conn->query($sqlStudents);

            // Перебираем результаты запроса о студентах в группе
            while ($rowStudent = $resultStudents->fetch_assoc()) {
                $studentFIO = $rowStudent['FIO'];
                $totalValue = $rowStudent['TotalValue'];

                // Добавляем данные о студенте в массив данных о студентах
                $studentsData[] = array(
                    'FIO' => $studentFIO,
                    'GroupName' => $groupName,
                    'TotalValue' => $totalValue
                );
            }
        }

        // Сортируем массив данных о студентах по суммарному рейтингу в убывающем порядке
        usort($studentsData, function($a, $b) {
            return $b['TotalValue'] - $a['TotalValue'];
        });

        // Если есть данные о студентах, формируем таблицу
        if (!empty($studentsData)) {
            // Формируем HTML-код для таблицы с рейтингом
            $starttimeId = date('d.m.y', strtotime($_POST['starttimeId']));
            $endtimeId = date('d.m.y', strtotime($_POST['endtimeId']));
            $output = '<label>Дисциплина: ' . $disciplineName . '. C: ' . $starttimeId . '. По: ' . $endtimeId . '</label>';
            $output .= '<table id="attandance-table">';
            $output .= '<tr><th>ФИО</th><th>Группа</th><th>Рейтинг</th></tr>';

            // Перебираем данные о студентах и формируем строки таблицы
            foreach ($studentsData as $student) {
                $studentFIO = $student['FIO'];
                $groupName = $student['GroupName'];
                $totalValue = $student['TotalValue'];

                $output .= '<tr>';
                $output .= '<td>' . $studentFIO . '</td>';
                $output .= '<td style="text-align: center;">' . $groupName . '</td>';
                $output .= '<td style="text-align: center;">' . $totalValue . '</td>';
                $output .= '</tr>';
            }

            $output .= '</table>';

            echo json_encode(['success' => $output]);
        }
    } else {
        echo json_encode(['errors' => 'Нет данных за данный промежуток времени.']);
    }      
}

$conn->close();
?>

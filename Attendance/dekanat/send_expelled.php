<?php
require_once('../config.php'); // Подключение к базе данных
require '../vendor/autoload.php'; // Подключение автозагрузчика PHPSpreadsheet
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// Путь к файлу Excel, который отправляется на сервер
$filePath = $_FILES['file']['tmp_name'];
// Создаем объект для чтения Excel
$spreadsheet = IOFactory::load($filePath);
// Получаем список листов
$sheetNames = $spreadsheet->getSheetNames();

// Перебираем каждый лист
foreach ($sheetNames as $sheetName) {
    // Пропускаем листы
    if ($sheetName !== 'Список отчисленных') {
        continue;
    }

    // Получаем лист по его имени
    $sheet = $spreadsheet->getSheetByName($sheetName);

    // Получаем данные о студентах
    $studentsData = [];
    $startRow = 2; // Начало чтения данных с строки 2
    $endRow = $sheet->getHighestRow(); // Конец чтения данных (последняя заполненная строка)
    for ($row = $startRow; $row <= $endRow; $row++) {
        // Получаем ФИО студента из столбца A
        $fio = $sheet->getCell('A' . $row)->getValue();
        $groupName = $sheet->getCell('B' . $row)->getValue();
        // Добавляем ФИО студента в массив, исключая значения null
        if ($fio !== null && $groupName !== null) {
            $studentsData[$fio] = $groupName;
        }
    }

    $errors = [];
    if ($studentsData !== null) {
        foreach ($studentsData as $student => $group) {
            // Проверка формата ФИО
            if (!preg_match('/^[\p{L}\s\-]+$/u', $student)) {
                $errors[] = 'Неверный формат ФИО студента ' . $student;
            } else {
                // Разделяем ФИО на части
                $nameParts = explode(' ', $student);
                $nameCount = count($nameParts);
                
                // Проверка наличия хотя бы фамилии и имени
                if ($nameCount < 2) {
                    $errors[] = 'Неверный формат ФИО студента ' . $student;
                } elseif ($nameCount > 3) { // Более 3-х частей не ожидается
                    $errors[] = 'Неверный формат ФИО студента ' . $student;
                }
            }
            
            // Проверка формата номера группы
            if (substr($group, 0, 1) !== '4') {
                $errors[] = 'Неверный формат группы для студента ' . $student;
            }
        }

        // Если есть ошибки, возвращаем их в виде JSON
        if (!empty($errors)) {
            echo json_encode(['errors' => $errors]);
            exit;
        } else {
            foreach ($studentsData as $student => $group) {
                // Получаем ID группы
                $sqlGroup = "SELECT GroupID FROM attandance.`groups` WHERE `groups`.Groups='$group';";
                $resultGroup = $conn->query($sqlGroup);
                $rowGroup = $resultGroup->fetch_assoc();
                $groupId = $rowGroup['GroupID'];
                // Проверяем, существует ли студент в базе данных и в указанной группе
                $sqlStudent = "SELECT StudentID, GroupID FROM attandance.students WHERE FIO = '$student';";
                $resultStudent = $conn->query($sqlStudent);

                if ($resultStudent->num_rows > 0) {
                    // Студент существует в базе данных
                    $rowStudent = $resultStudent->fetch_assoc();
                    if ($rowStudent['GroupID'] == $groupId) {
                        // Студент уже в этой группе, обновляем expelled
                        $sqlUpdateStudent = "UPDATE attandance.students SET Expelled = 1 WHERE GroupID = ? AND StudentID = ?;";
                        $stmtUpdateStudent = $conn->prepare($sqlUpdateStudent);
                        $stmtUpdateStudent->bind_param("ii", $groupId, $rowStudent['StudentID']);
                        $stmtUpdateStudent->execute();
                        $stmtUpdateStudent->close();
                        continue;
                    } else {
                        // Студент существует, но не в этой группе, обновляем его группу
                        $sqlUpdateGroup = "UPDATE attandance.students SET GroupID = ?, Expelled = 1 WHERE StudentID = ?;";
                        $stmtUpdateGroup = $conn->prepare($sqlUpdateGroup);
                        $stmtUpdateGroup->bind_param("ii", $groupId, $rowStudent['StudentID']);
                        $stmtUpdateGroup->execute();
                        $stmtUpdateGroup->close();
                    }
                } else {
                    echo json_encode(['errors' => 'Студента с ФИО ('. $student.') нет в базе данных.']);
                }
            }
        }
    } else {
        echo json_encode(['errors' => 'Данные некорректны или пропущены. Проверьте правильность введенных данных.']);
        exit;
    }
}
echo json_encode(['message' => 'Данные успешно добавлены в базу данных.']);

?>

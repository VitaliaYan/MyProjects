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
foreach ($sheetNames as $groupName) {
    // Пропускаем листы, название которых не начинается с "4"
    if (substr($groupName, 0, 1) !== '4') {
        continue;
    }

    // Получаем лист по его имени
    $sheet = $spreadsheet->getSheetByName($groupName);

    // Получаем данные из нужных ячеек (D2, D3, D5, D6)
    $directionCode = $sheet->getCell('D2')->getValue();
    $year = $sheet->getCell('D3')->getValue();
    // Получаем ссылку на ячейку с ФИО старосты группы
    $headFullNameFormula = $sheet->getCell('D5')->getValue();
    // Разбиваем строку по символу "="
    $formulaParts = explode("=", $headFullNameFormula);
    // Получаем адрес ячейки из второй части строки
    $cellAddress = $formulaParts[1];
    // Получаем ФИО старосты группы из указанной ячейки
    $headFullName = $sheet->getCell($cellAddress)->getValue();
    $headEmail = $sheet->getCell('D6')->getValue();

    // Получаем данные о студентах
    $studentsData = [];
    $startRow = 2; // Начало чтения данных с строки 2
    $endRow = $sheet->getHighestRow(); // Конец чтения данных (последняя заполненная строка)
    for ($row = $startRow; $row <= $endRow; $row++) {
        // Получаем ФИО студента из столбца A
        $fio = $sheet->getCell('A' . $row)->getValue();
        // Добавляем ФИО студента в массив, исключая значения null
        if ($fio !== null) {
            $studentsData[] = $fio;
        }
    }

    $errors = [];
    if ($groupName !== null && $studentsData !== null && $directionCode !== null && $year !== null && $headFullName !== null && $headEmail !== null) {
        // Проверяем формат $studentsData
        foreach ($studentsData as $student) {
            if (!preg_match('/^\p{L}+ \p{L}+$/u', $student) && !preg_match('/^\p{L}+ \p{L}+ \p{L}+$/u', $student)) {
                $errors[] = 'Неверный формат ФИО студента ' . $student . ' на листе группы ' . $groupName;
            }
        }
        // Проверяем формат $directionCode
        if (!preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $directionCode)) {
            $errors[] = 'Неверный формат кода направления на листе группы ' . $groupName;
        }
        // Проверяем формат $year
        if (!is_numeric($year) || $year < 1 || $year > 4) {
            $errors[] = 'Неверный формат курса на листе группы ' . $groupName;
        }
        // Проверяем, что $headFullName есть в $studentsData
        if (!in_array($headFullName, $studentsData)) {
            $errors[] = 'ФИО старосты отсутствует в списке студентов на листе группы ' . $groupName;
        }
        // Проверяем формат $headEmail
        if (!filter_var($headEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Неверный формат электронной почты на листе группы ' . $groupName;
        }

        // Если есть ошибки, возвращаем их в виде JSON
        if (!empty($errors)) {
            echo json_encode(['errors' => $errors]);
            exit;
        } else {
            $sqlDirection = "SELECT DirectionID FROM attandance.directions WHERE Directions='$directionCode';";
            $resultDirection = $conn->query($sqlDirection);

            if ($resultDirection->num_rows > 0) {
                $rowDirection = $resultDirection->fetch_assoc();
                $directionId = $rowDirection['DirectionID'];
            } else {
                // Подготовка SQL-запроса для вставки данных о посещаемости
                $sql = "INSERT INTO attandance.directions (Directions) VALUES (?)";
                // Подготовка выражения SQL
                $stmt = $conn->prepare($sql);
                // Привязка параметров и выполнение SQL-запроса
                $stmt->bind_param("s", $directionCode);
                $stmt->execute();
                // Получаем последний вставленный ID
                $directionId = $stmt->insert_id;
                $stmt->close();
            }

            // Получаем номер направления
            $sqlGroup = "SELECT GroupID FROM attandance.`groups` WHERE `groups`.Groups='$groupName' AND `groups`.DirectionID='$directionId' AND `groups`.`Year`='$year';";
            $resultGroup = $conn->query($sqlGroup);

            if ($resultGroup->num_rows > 0) {
                $rowGroup = $resultGroup->fetch_assoc();
                $groupId = $rowGroup['GroupID'];
            } else {
                // Подготовка SQL-запроса для вставки данных о посещаемости
                $sql = "INSERT INTO attandance.`groups` (`Groups`, DirectionID, `Year`) VALUES (?, ?, ?)";
                // Подготовка выражения SQL
                $stmt = $conn->prepare($sql);
                // Привязка параметров и выполнение SQL-запроса
                $stmt->bind_param("sii", $groupName, $directionId, $year);
                $stmt->execute();
                // Получаем последний вставленный ID
                $groupId = $stmt->insert_id;
                $stmt->close();
            }

            foreach ($studentsData as $student) {
                // Проверяем, существует ли студент в базе данных и в указанной группе
                $sqlStudent = "SELECT StudentID, GroupID, Expelled FROM attandance.students WHERE FIO = '$student';";
                $resultStudent = $conn->query($sqlStudent);
            
                if ($resultStudent->num_rows > 0) {
                    // Студент существует в базе данных
                    $rowStudent = $resultStudent->fetch_assoc();
                    if ($rowStudent['GroupID'] == $groupId) {
                        // Студент уже в этой группе, обновляем expelled
                        $sqlUpdateStudent = "UPDATE attandance.students SET Expelled = 0, Head = 0 WHERE GroupID = ? AND StudentID = ?;";
                        $stmtUpdateStudent = $conn->prepare($sqlUpdateStudent);
                        $stmtUpdateStudent->bind_param("ii", $groupId, $rowStudent['StudentID']);
                        $stmtUpdateStudent->execute();
                        $stmtUpdateStudent->close();

                        continue;
                    } else {
                        // Студент существует, но не в этой группе, обновляем его группу
                        $sqlUpdateGroup = "UPDATE attandance.students SET GroupID = ?, Expelled = 0, Head = 0 WHERE StudentID = ?;";
                        $stmtUpdateGroup = $conn->prepare($sqlUpdateGroup);
                        $stmtUpdateGroup->bind_param("ii", $groupId, $rowStudent['StudentID']);
                        $stmtUpdateGroup->execute();
                        $stmtUpdateGroup->close();
                    }
                } else {
                    // Студент не существует в базе данных, добавляем его
                    $sqlInsertStudent = "INSERT INTO attandance.students (FIO, GroupID, Expelled) VALUES (?, ?, 0);";
                    $stmtInsertStudent = $conn->prepare($sqlInsertStudent);
                    $stmtInsertStudent->bind_param("si", $student, $groupId);
                    $stmtInsertStudent->execute();
                    $stmtInsertStudent->close();
                }
            }

            // Устанавливаем expelled = 1 для студентов, которые есть в базе данных, но не в текущем списке студентов
            $sqlAllStudents = "SELECT FIO FROM attandance.students WHERE GroupID = '$groupId';";
            $resultAllStudents = $conn->query($sqlAllStudents);

            while ($rowAllStudent = $resultAllStudents->fetch_assoc()) {
                // Проверяем, есть ли ID студента из базы данных в текущем списке
                if (!in_array($rowAllStudent['FIO'], $studentsData)) {
                    $sqlExpelStudent = "UPDATE attandance.students SET Expelled = 1, Head = 0 WHERE FIO = ?;";
                    $stmtExpelStudent = $conn->prepare($sqlExpelStudent);
                    $stmtExpelStudent->bind_param("s", $rowAllStudent['FIO']);
                    $stmtExpelStudent->execute();
                    $stmtExpelStudent->close();
                }
            }

            $sqlHeadStudent = "SELECT StudentID FROM attandance.students WHERE FIO ='$headFullName';";
            $resultHeadStudent = $conn->query($sqlHeadStudent);

            if ($resultHeadStudent->num_rows > 0) {
                $rowHeadStudent = $resultHeadStudent->fetch_assoc();
                $headStudentId = $rowHeadStudent['StudentID'];
                // Обновление столбцов Head и Email для найденного студента
                $sqlUpdateHead = "UPDATE attandance.students SET Head = 1, `E-mail` = ? WHERE StudentID = ?;";
                $stmtUpdateHead = $conn->prepare($sqlUpdateHead);
                $stmtUpdateHead->bind_param("si", $headEmail, $headStudentId);
                $stmtUpdateHead->execute();
                $stmtUpdateHead->close();

                // Проверяем, есть ли UserID
                $sqlCheckUserID = "SELECT UserID FROM attandance.users WHERE Username = '$headEmail';";
                $resultCheckUserID = $conn->query($sqlCheckUserID);
                if ($resultCheckUserID->num_rows === 0) {
                    // Пользователь не существует, генерируем пароль
                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
                    $password = "";
                    for ($i = 0; $i < 10; $i++) {
                        $password .= $chars[rand(0, strlen($chars) - 1)];
                    }

                    // Записываем пароль и другую информацию в CSV-файл
                    $csvData = array($headStudentId, $headEmail, $password, 'студент');
                    $file = fopen('users.csv', 'a'); // Открываем файл для записи, добавляя данные в конец файла
                    fputcsv($file, $csvData);
                    fclose($file);

                    // Хешируем пароль и добавляем пользователя в базу данных
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sqlInsertUser = "INSERT INTO attandance.users (Username, `Password`, `Key`) VALUES (?, ?, 'S');";
                    $stmtInsertUser = $conn->prepare($sqlInsertUser);
                    $stmtInsertUser->bind_param("ss", $headEmail, $hashed_password);
                    $stmtInsertUser->execute();
                    $stmtInsertUser->close();
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

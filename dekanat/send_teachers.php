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
foreach ($sheetNames as $teacherName) {
    // Проверка формата ФИО
    if (!preg_match('/^\p{L}+ \p{L}+$/u', $teacherName) && !preg_match('/^\p{L}+ \p{L}+ \p{L}+$/u', $teacherName)) {
        continue;
    }

    // Получаем лист по его имени
    $sheet = $spreadsheet->getSheetByName($teacherName);

    $teacherEmail = $sheet->getCell('F2')->getValue();

    // Получаем данные о дисцпилинах
    $disciplineData = [];
    $startRow = 2; // Начало чтения данных с строки 2
    $endRow = $sheet->getHighestRow(); // Конец чтения данных (последняя заполненная строка)

    for ($row = $startRow; $row <= $endRow; $row++) {
        // Получаем дисциплины из столбца A и группу из столбца B
        $discipline = $sheet->getCell('A' . $row)->getValue();
        $groupName = $sheet->getCell('B' . $row)->getValue();
        $termId = $sheet->getCell('C' . $row)->getValue();
        // Добавляем запись в массив, если все значения не равны null
        if ($discipline !== null && $groupName !== null && $termId !== null) {
            $disciplineData[] = [
                'discipline' => $discipline,
                'group' => $groupName,
                'term' => $termId
            ];
        }
    }

    $errors = [];
    // Проверяем, что массив данных не пуст
    if (empty($disciplineData)) {
        $errors[] = 'Лист преподавателя "' . $teacherName . '" не содержит данных.';
        echo json_encode(['errors' => $errors]);
        exit; // Пропускаем этот лист и переходим к следующему
    }

    if ($teacherName !== null && $disciplineData !== null && $teacherEmail !== null) {
        // Проверяем формат $disciplineData
        foreach ($disciplineData as $data) {
            
            // Проверка формата ФИО
            if (!preg_match('/^[\p{L}\s\-]+$/u', $teacherName)) {
                $errors[] = 'Неверный формат ФИО преподавателя ' . $teacherName;
            } else {
                // Разделяем ФИО на части
                $nameParts = explode(' ', $teacherName);
                $nameCount = count($nameParts);
                
                // Проверка наличия хотя бы фамилии и имени
                if ($nameCount < 2) {
                    $errors[] = 'Неверный формат ФИО преподавателя ' . $teacherName;
                } elseif ($nameCount > 3) { // Более 3-х частей не ожидается
                    $errors[] = 'Неверный формат ФИО преподавателя ' . $teacherName;
                }
            }
            // Проверка формата дисциплины
            if (!preg_match('/^[\p{L}\s]+$/u', $data['discipline'])) {
                $errors[] = 'Неверный формат названия дисциплины: ' . $data['discipline'] . ' на листе преподавателя: ' . $teacherName;
            }
            // Проверка формата номера группы
            if (substr($data['group'], 0, 1) !== '4') {
                $errors[] = 'Неверный формат группы ' . $data['group'];
            }
            // Проверяем формат $termId
            if (!is_numeric($data['term']) || $data['term'] < 1 || $data['term'] > 8) {
                $errors[] = 'Неверный формат семестра на листе преподавателя: ' . $teacherName.' для группы '.$data['group'];
            }
            // Проверяем формат $headEmail
            if (!filter_var($teacherEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Неверный формат электронной почты преподавателя: ' . $teacherName;
            }
        }

        // Если есть ошибки, возвращаем их в виде JSON
        if (!empty($errors)) {
            echo json_encode(['errors' => $errors]);
            exit;
        } else {
            // Проверяем, существует ли преподаватель в базе данных
            $sqlTeacher = "SELECT TeacherID FROM attandance.teachers WHERE FIO = '$teacherName';";
            $resultTeacher = $conn->query($sqlTeacher);
        
            if ($resultTeacher->num_rows > 0) {
                $rowTeacher = $resultTeacher->fetch_assoc();
                $teacherId = $rowTeacher['TeacherID'];
            } else {
                // Пользователь не существует, генерируем пароль
                $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
                $password = "";
                for ($i = 0; $i < 10; $i++) {
                    $password .= $chars[rand(0, strlen($chars) - 1)];
                }

                // Записываем пароль и другую информацию в CSV-файл
                $csvData = array($teacherName, $headEmail, $password, 'преподаватель');
                $file = fopen('users.csv', 'a'); // Открываем файл для записи, добавляя данные в конец файла
                fputcsv($file, $csvData);
                fclose($file);

                // Хеширование пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sqlInsertUser = "INSERT INTO attandance.`users` (Username, `Password`, `Key`) VALUES (?, ?, 'P');";
                $stmtInsertUser = $conn->prepare($sqlInsertUser);
                $stmtInsertUser->bind_param("ss", $teacherEmail, $hashed_password);
                $stmtInsertUser->execute();
                $userId = $stmtInsertUser->insert_id;
                $stmtInsertUser->close();

                $sqlInsertTeacher = "INSERT INTO attandance.teachers (FIO, UserID) VALUES (?, ?);";
                $stmtInsertTeacher = $conn->prepare($sqlInsertTeacher);
                $stmtInsertTeacher->bind_param("si", $teacherName, $userId);
                $stmtInsertTeacher->execute();
                $teacherId = $stmtInsertTeacher->insert_id;
                $stmtInsertTeacher->close();
            }

            foreach ($disciplineData as $data) {
                // Получаем ID группы
                $group = $data['group'];
                $sqlGroup = "SELECT GroupID, DirectionID FROM attandance.`groups` WHERE `groups`.Groups='$group';";
                $resultGroup = $conn->query($sqlGroup);
                $rowGroup = $resultGroup->fetch_assoc();
                $groupId = $rowGroup['GroupID'];
                $directionId = $rowGroup['DirectionID'];

                $discipline = $data['discipline'];
                // Получаем ID дисциплины
                $sqlDiscipline = "SELECT DisciplineID FROM attandance.disciplines WHERE disciplines.Discipline='$discipline';";
                $resultDiscipline = $conn->query($sqlDiscipline);
                if ($resultDiscipline->num_rows > 0) {
                    $rowDiscipline = $resultDiscipline->fetch_assoc();
                    $disciplineId = $rowDiscipline['DisciplineID'];
                } else {
                    $sqlInsertDiscipline = "INSERT INTO attandance.disciplines (Discipline) VALUES (?);";
                    $stmtInsertDiscipline = $conn->prepare($sqlInsertDiscipline);
                    $stmtInsertDiscipline->bind_param("s", $discipline);
                    $stmtInsertDiscipline->execute();
                    $disciplineId = $stmtInsertDiscipline->insert_id;
                    $stmtInsertDiscipline->close();
                }

                $termId = $data['term'];

                $sqlDiscDirection = "SELECT DiscDirectionID FROM attandance.disciplinedirections WHERE disciplinedirections.DisciplineID='$disciplineId' AND disciplinedirections.TermID = '$termId' AND disciplinedirections.DirectionID = '$directionId';";
                $resultDiscDirection= $conn->query($sqlDiscDirection);
                if ($resultDiscDirection->num_rows > 0) {
                    $rowDiscDirection = $resultDiscDirection->fetch_assoc();
                    $discdirectionId = $rowDiscDirection['DiscDirectionID'];
                } else {
                    echo json_encode(['errors' => 'Дисциплины "'.$discipline.'" нет в '.$termId.' сесместре для группы '.$group.', произошла оишбка.']);
                    exit;
                }

                $sqlDGT = "SELECT DgtID FROM attandance.discgroupteacher WHERE discgroupteacher.DiscDirectionID='$discdirectionId' AND discgroupteacher.GroupID = '$groupId' AND discgroupteacher.TeacherID = '$teacherId';";
                $resultDGT= $conn->query($sqlDGT);
                if ($resultDGT->num_rows > 0) {
                    $rowDGT = $resultDGT->fetch_assoc();
                    $gtId = $rowDGT['DgtID'];
                } else {
                    $sqlInsertDGT = "INSERT INTO attandance.discgroupteacher (DiscDirectionID, GroupID, TeacherID) VALUES (?, ?, ?);";
                    $stmtInsertDGT = $conn->prepare($sqlInsertDGT);
                    $stmtInsertDGT->bind_param("iii", $discdirectionId, $groupId, $teacherId);
                    $stmtInsertDGT->execute();
                    $dgtId = $stmtInsertDGT->insert_id;
                    $stmtInsertDGT->close();
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

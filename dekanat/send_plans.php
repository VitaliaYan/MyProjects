<?php
require_once('../config.php'); // Подключение к базе данных
require '../vendor/autoload.php'; // Подключение автозагрузчика PHPSpreadsheet
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Путь к файлу Excel, который отправляется на сервер
$filePath = $_FILES['file']['tmp_name'];
// Создаем объект для чтения Excel
$spreadsheet = IOFactory::load($filePath);
// Получаем список листов
$sheetNames = $spreadsheet->getSheetNames();

// Получаем лист по его имени
$sheet4 = $spreadsheet->getSheetByName('Курс 4');
$rows = [];
$currentSet = [];
$previousDisciplineNumber = null;
$highestRow = $sheet4->getHighestRow();
for ($row = 17; $row <= $highestRow; $row++) {
    $disciplineNumber = $sheet4->getCell('C' . $row)->getValue();

    if (is_numeric($disciplineNumber)) {
        if ($disciplineNumber == 1 && !empty($currentSet)) {
            $rows[] = $currentSet;
            $currentSet = [];
        }
        $currentSet[] = $row;
    }
}
if (!empty($currentSet)) {
    $rows[] = $currentSet;
}

// Получаем лист по его имени
$titlesheet = $spreadsheet->getSheetByName('Титул');
$directionName = $titlesheet->getCell('D27')->getValue();
// Получаем ID направления
$sqlDirection = "SELECT DirectionID FROM attandance.directions WHERE Directions='$directionName';";
$resultDirection = $conn->query($sqlDirection);
if ($resultDirection->num_rows > 0) {
    // Если дисциплина уже существует в базе данных, получаем ее DisciplineID
    $rowDirection = $resultDirection->fetch_assoc();
    $directionId = $rowDirection['DirectionID'];
} else {
    $sqlInsertDirection = "INSERT INTO attandance.directions (Directions) VALUES (?);";
    $stmtInsertDirection = $conn->prepare($sqlInsertDirection);
    $stmtInsertDirection->bind_param("s", $directionName);
    $stmtInsertDirection->execute();
    $directionId = $stmtInsertDirection->insert_id;
    $stmtInsertDirection->close();
}
$data = [];

foreach ($sheetNames as $sheetName) {
    $sheet = $spreadsheet->getSheetByName($sheetName);
    if (strpos($sheetName, 'Курс') === 0) {
        $term1 = $sheet->getCell('G3')->getValue();
        if ($term1 !== null && preg_match('/Семестр (\d+)/', $term1, $matches)) {
            $termId1 = $matches[1];
        }
        $term2 = $sheet->getCell('U3')->getValue();
        if ($term2 !== null && preg_match('/Семестр (\d+)/', $term2, $matches)) {
            $termId2 = $matches[1];
        }

        $courseIndex = intval(str_replace('Курс ', '', $sheetName)) - 1;
        if (isset($rows[$courseIndex])) {
            $term1Data = [];
            $term2Data = [];

            foreach ($rows[$courseIndex] as $row) {
                $disciplineName = $sheet->getCell('E' . $row)->getValue();
                $term1Value = $sheet->getCell('H' . $row)->getValue();
                if (!empty($term1Value)) {
                    $term1Data[] = $disciplineName;
                }
                $term2Value = $sheet->getCell('V' . $row)->getValue();
                if (!empty($term2Value)) {
                    $term2Data[] = $disciplineName;
                }
            }

            if (!isset($data[$termId1])) {
                $data[$termId1] = [];
            }
            if (!isset($data[$termId2])) {
                $data[$termId2] = [];
            }

            $data[$termId1] = array_merge($data[$termId1], $term1Data);
            $data[$termId2] = array_merge($data[$termId2], $term2Data);
        }
    }
}

// Новый ассоциативный массив для хранения ID дисциплин
$disciplineIds = [];

foreach ($data as $termId => $disciplines) {
    foreach ($disciplines as $disciplineName) {
        // Выполняем операцию SELECT, чтобы получить DisciplineID
        $sqlDiscipline = "SELECT DisciplineID FROM attandance.disciplines WHERE Discipline = '$disciplineName'";
        $resultDiscipline = $conn->query($sqlDiscipline);
        
        if ($resultDiscipline->num_rows > 0) {
            // Если дисциплина уже существует в базе данных, получаем ее DisciplineID
            $rowDiscipline = $resultDiscipline->fetch_assoc();
            $disciplineId = $rowDiscipline['DisciplineID'];
        } else {
            $sqlInsertDiscipline = "INSERT INTO attandance.disciplines (Discipline) VALUES (?);";
            $stmtInsertDiscipline = $conn->prepare($sqlInsertDiscipline);
            $stmtInsertDiscipline->bind_param("s", $disciplineName);
            $stmtInsertDiscipline->execute();
            $disciplineId = $stmtInsertDiscipline->insert_id;
            $stmtInsertDiscipline->close();
        }
        
        // Сохраняем DisciplineID в массиве для текущего termId
        $disciplineIds[$termId][] = $disciplineId;
    }
}

foreach ($disciplineIds as $termId => $termDisciplineIds) {
    foreach ($termDisciplineIds as $disciplineId) {
        // Проверяем наличие записи в таблице disciplinedirections для текущего termId и disciplineId
        $sqlDiscDirection = "SELECT DiscDirectionID FROM attandance.disciplinedirections WHERE TermID = '$termId' AND DisciplineID = '$disciplineId' AND DirectionID = '$directionId';";
        $resultDiscDirection = $conn->query($sqlDiscDirection);
        if ($resultDiscDirection->num_rows == 0) {
            // Если записи не существует, выполняем операцию INSERT
            $sqlInsertDirection = "INSERT INTO attandance.disciplinedirections (DirectionID, TermID, DisciplineID) VALUES (?, ?, ?);";
            $stmtInsertDirection = $conn->prepare($sqlInsertDirection);
            $stmtInsertDirection->bind_param("iii", $directionId, $termId, $disciplineId);
            $stmtInsertDirection->execute();
            $disciplineId = $stmtInsertDirection->insert_id;
            $stmtInsertDirection->close();
        }
    }
}

echo json_encode(['message' => 'Данные успешно добавлены в базу данных.']);
?>

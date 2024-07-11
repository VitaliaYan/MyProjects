<?php
session_start();

// Проверяем, аутентифицирован ли пользователь
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] >= 7200) {
    // Сессия не действительна, перенаправляем пользователя на страницу авторизации с параметром redirect_url
    $redirect_url = 'dekanat/sprav_students.php'; // URL текущей страницы, куда пользователь хотел перейти
    header("Location: ../authorize_form.php?redirect_url=" . urlencode($redirect_url));
    exit;
}

// Если сессия действительна, продолжаем выполнение страницы
if ($_SESSION['role'] === 'D') {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Справочник студентов</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/attand.css">
    <link rel="icon" href="../images/icon.png" type="image/png">
</head>

<script>
    $(document).ready(function() {

        $(document).ready(function() {
            // Обработчик изменений
            $("#fio, #group").on("input", function() {
                var group = $("#group").val();
                var fio = $("#fio").val();
                // Отправляем AJAX-запрос на сервер для получения дисциплин
                $.ajax({
                    url: "get_students_sprav.php", // Путь к PHP-скрипту на сервере
                    type: "POST",
                    data: { fio: fio, group: group },
                    dataType: "html",
                    success: function(response) {
                        $("#attandance-table-container").html(response).show();
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    });
</script>
<body>
    <div class="search">
        <input type="text" placeholder="Поиск по ФИО" id="fio" name="fio">
        <input type="text" placeholder="Поиск по группе" id="group" name="group">
    </div>
    <!-- Контейнер для таблицы с посещаемостью -->
    <div id="attandance-table-container" style="background-color: #3f5f78cf; background-size: cover;">
        <?php
            // Подключение к базе данных и выполнение запроса
            require_once('../config.php'); // Подключение к базе данных
            $conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Запрос для получения данных ФИО и суммарного количества пропусков по данной дисциплине
            $sql = "SELECT students.FIO, `groups`.Groups, students.Head FROM attandance.students LEFT JOIN attandance.`groups` ON `groups`.GroupID = students.GroupID WHERE students.Expelled = 0;";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
            $output = '<table id="attandance-table"';
            $output .= '<tr><th>ФИО</th><th>Группа</th><th>Староста</th></tr>';
                while ($row = $result->fetch_assoc()) {
                $output .= '<tr style="border: 2px solid #fff; border-radius: 4px;">';
                $output .= '<td>' . $row["FIO"] . '</td>';
                $output .= '<td style="text-align: center;">' . $row["Groups"] . '</td>';
                // Проверяем значение столбца Head
                $headContent = $row["Head"] == 1 ? '&#10003;' : ''; // Используем символ Unicode для галочки
                $output .= '<td style="text-align: center;">' . $headContent . '</td>';
                $output .= '</tr>';
                }

                $output .= '</table>';

                echo $output;
            }

            $conn->close();
        ?>
    </div>
</body>
</html>
<?php
} else {

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка доступа</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/attand.css">
    <style>
        /* Стили для центрирования контейнера и элемента внутри */
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #errmsg {
            text-align: center; /* Центрируем текст внутри блока */
        }
        #errmsg label {
            font-size: 30px; /* Размер текста */
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "../index.php";
        }, 2000); // Перенаправляем на index.php через 2 секунды
    </script>
</head>
<body>
    <div id="errmsg">
        <label>У вас нет прав доступа к этой странице. Пожалуйста, обратитесь к администратору.</label>
    </div>
</body>
</html>
<?php
}
?>
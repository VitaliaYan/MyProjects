<?php
session_start();

// Проверяем, аутентифицирован ли пользователь
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] >= 7200) {
    // Сессия не действительна, перенаправляем пользователя на страницу авторизации с параметром redirect_url
    $redirect_url = 'dekanat/send_authorize_students.php'; // URL текущей страницы, куда пользователь хотел перейти
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
    <title>Рассылка для авторизации студентов</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="../css/attand.css">
    <link rel="icon" href="../images/icon.png" type="image/png">
</head>
<script>
    $(document).ready(function() {
        // Обработчик клика для метки с классом password
        $('label.password').click(function() {
            // Получаем текущее значение фильтра размытия
            var currentBlur = $(this).css('filter');
            // Если фильтр размытия равен 'none', то применяем размытие, иначе убираем его
            if (currentBlur === 'none') {
                $(this).css('filter', 'blur(5px)');
            } else {
                $(this).css('filter', 'none');
            }
        });

        // Найти все кнопки с классом .sendBtn
        $('.sendBtn').click(function() {
            var fio = $(this).data('fio'); // Получить ID студента из атрибута data-id кнопки
            // Выполняем AJAX запрос для отправки данных на сервер
            $.ajax({
                url: 'register_user.php', // URL-адрес обработчика на сервере
                type: 'POST', // Метод запроса
                data: { fio: fio, key: 'студент'}, // Данные для отправки
                success: function(response) {
                    // Парсим JSON ответ от сервера
                    var datajson = JSON.parse(response);
                    if (datajson.message) {
                        // Выводим сообщение об успешной операции
                        Swal.fire({
                            icon: 'success',
                            text: datajson.message,
                            confirmButtonText: 'ОК',
                            customClass: {
                                popup: 'swal-custom',
                                confirmButton: 'swal-button'
                            },
                            buttonsStyling: false // Отключает стилизацию по умолчанию для использования кастомных классов
                        }).then(function() {
                            // Обновить страницу или выполнить другие действия после закрытия SweetAlert
                            location.reload(); // Обновить страницу
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Обработка ошибок при отправке запроса
                    console.error('Ошибка при отправке запроса:', error);
                    Swal.fire({
                        title: 'Ошибка',
                        text: 'Произошла ошибка при отправке запроса. Пожалуйста, попробуйте еще раз.',
                        confirmButtonText: 'ОК',
                        customClass: {
                            popup: 'swal-custom',
                            confirmButton: 'swal-button'
                        },
                        buttonsStyling: false // Отключает стилизацию по умолчанию для использования кастомных классов
                    });
                }
            });
        });
    });

</script>
<body>
    <!-- Контейнер для таблицы с посещаемостью -->
    <div id="attandance-table-container">
        <?php
            // Подключение к базе данных и выполнение запроса
            require_once('../config.php'); // Подключение к базе данных
            $conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT students.StudentID, students.FIO, students.`E-mail`, `groups`.Groups FROM attandance.students LEFT JOIN attandance.`groups` ON `groups`.GroupID = students.GroupID WHERE students.Head = 1;";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
            $output = '<table id="attandance-table" style="background-color: #3f5f78cf; background-size: cover;">';
            $output .= '<tr><th>ФИО</th><th>Группа</th><th>Логин</th><th>Пароль</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    $output .= '<tr style="border: 2px solid #fff; border-radius: 4px;">';
                    $output .= '<td>' . $row["FIO"] . '</td>';
                    $output .= '<td style="text-align: center;">' . $row["Groups"] . '</td>';
                    // Чтение CSV-файла
                    $csvFile = fopen('users.csv', 'r');
                    $lastMatchingRecord = null;

                    $csvFile = fopen('users.csv', 'r');
                    if ($csvFile !== false) {
                        // Чтение CSV-файла
                        while (($rowCSV = fgetcsv($csvFile)) !== FALSE) {
                            if(count($rowCSV) >= 4) { // Проверяем наличие достаточного количества значений в строке
                                $csvfio = $rowCSV[0]; // FIO из CSV-файла
                                $login = $rowCSV[1]; // Логин из CSV-файла
                                $password = $rowCSV[2]; // Пароль из CSV-файла
                                $key = $rowCSV[3]; // Ключ (например, "студент")

                                // Проверка, совпадает ли ID из CSV с ID студента
                                if ($csvfio == $row["FIO"] && $key == 'студент') {
                                    $lastMatchingRecord = $rowCSV; // Сохраняем последнюю подходящую запись
                                }
                            }
                        }
                        fclose($csvFile); // Закрытие файла
                    }
                    // Если найдена подходящая запись, выводим логин и пароль
                    if ($lastMatchingRecord) {
                        $login = $lastMatchingRecord[1]; // Логин из последней подходящей записи
                        $password = $lastMatchingRecord[2]; // Пароль из последней подходящей записи
                        $output .= '<td>' . $login . '</td>';
                        $output .= '<td><label class="password" style="filter: blur(5px);">'.$password.'</label></td>';
                    } else {
                        // Обработка случая, когда подходящая запись не найдена
                        $output .= '<td>'. $row["E-mail"] .'</td>';
                        $output .= '<td><label class="password" style="filter: blur(5px);">Не зарегистрирован</label></td>';
                        $output .= '<td><button style="margin:2px;" class="sendBtn" data-fio="' . $row["FIO"] . '">Зарегистрировать</button></td>';
                    }
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
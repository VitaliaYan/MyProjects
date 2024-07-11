<?php
session_start();

// Проверяем, аутентифицирован ли пользователь
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] >= 7200) {
    $redirect_url = 'dekanat/adduser.php'; // URL текущей страницы, куда пользователь хотел перейти
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
    <title>Добавление нового пользователя деканата</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="../css/authorize.css">
    <link rel="icon" href="../images/icon.png" type="image/png">
</head>
<body class="new-body">
    <form id="login-form" class="new-login-form">
        <label>Добавление нового пользователя деканата</label>
        <input type="text" placeholder="Имя пользователя" id="username">
        <input type="password" placeholder="Пароль" id="password">
        <input type="submit" value="Добавить пользователя">
    </form>
    <script>
        document.getElementById("login-form").addEventListener("submit", function(event) {
            event.preventDefault(); // Предотвращаем стандартное поведение формы
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            // Выполняем AJAX запрос для отправки данных на сервер
            $.ajax({
                url: 'send_user.php', // URL-адрес обработчика на сервере
                type: 'POST', // Метод запроса
                data: {username: username, password: password}, // Данные для отправки
                success: function(response) {
                    // Парсим JSON ответ от сервера
                    var datajson = JSON.parse(response);
                    // Проверяем, содержит ли ответ сообщение об ошибке
                    if (datajson.error) {
                        // Выводим сообщения об ошибках
                        Swal.fire({
                            title: 'Ошибка',
                            text: datajson.error,
                            confirmButtonText: 'ОК',
                            customClass: {
                                popup: 'swal-custom',
                                confirmButton: 'swal-button'
                            },
                            buttonsStyling: false // Отключает стилизацию по умолчанию для использования кастомных классов
                        });
                    } else if (datajson.message) {
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
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
} else {
    // Роль пользователя не 'D', выводим сообщение и перенаправляем на index.php с использованием JavaScript
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

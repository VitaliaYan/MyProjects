<?php
session_start();

// Проверяем, аутентифицирован ли пользователь
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] >= 7200) {
    $redirect_url = 'dekanat/add_lists.php'; // URL текущей страницы, куда пользователь хотел перейти
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
    <title>Добавление данных о студентах, преподавателях и их изменение</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="../css/lists.css">
    <link rel="icon" href="../images/icon.png" type="image/png">
    <script>
        function downloadFile(filePath){
            var link = document.createElement('a');
            link.href = filePath;
            // Устанавливаем атрибут загрузки
            link.setAttribute('download', '');
            // Добавляем ссылку на страницу и эмулируем клик по ней
            document.body.appendChild(link);
            link.click();
            // Удаляем ссылку из DOM
            document.body.removeChild(link);
        }

        function sendFile(id) {
            // Получаем доступ к элементу input file
            var fileInput = document.getElementById(id);
            // Получаем первый выбранный файл (если есть)
            var file = fileInput.files[0];
            // Проверяем, был ли выбран файл
            if (file) {
                // Создаем новый объект FormData
                var formData = new FormData();
                // Добавляем выбранный файл в объект FormData
                formData.append('file', file);
                // Определяем, куда отправлять файл в зависимости от значения id
                switch (id) {
                    case 'file1':
                        // Отправляем файл на обработчик send_group.php
                        sendFormData(formData, 'send_group.php');
                        break;
                    case 'file2':
                        // Отправляем файл на обработчик send_plans.php
                        sendFormData(formData, 'send_plans.php');
                        break;
                    case 'file3':
                        // Отправляем файл на обработчик send_teachers.php
                        sendFormData(formData, 'send_teachers.php');
                        break;
                    case 'file4':
                        // Отправляем файл на обработчик send_expelled.php
                        sendFormData(formData, 'send_expelled.php');
                        break;
                }
            } else {
                // Не все данные заполнены, показываем сообщение
                Swal.fire({
                    title: 'Ошибка',
                    text: 'Пожалуйста, выберите файл.',
                    confirmButtonText: 'ОК',
                    customClass: {
                        popup: 'swal-custom',
                        confirmButton: 'swal-button'
                    },
                    buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                });
            }
        }

        // Функция для отправки данных формы на сервер с помощью AJAX
        function sendFormData(formData, url) {
            // Выполняем AJAX запрос для отправки данных на сервер
            $.ajax({
                url: url, // URL-адрес обработчика на сервере
                type: 'POST', // Метод запроса
                data: formData, // Данные для отправки
                processData: false, // Не обрабатывать данные перед отправкой
                contentType: false, // Не устанавливать тип содержимого
                success: function(response) {
                    // Парсим JSON ответ от сервера
                    var datajson = JSON.parse(response);
                    // Проверяем, содержит ли ответ сообщение об ошибке
                    if (datajson.errors) {
                        if (Array.isArray(datajson.errors)) {
                            // Преобразуем каждый элемент массива в строку с использованием <br> для переноса строки
                            var errorMessages = datajson.errors.map(function(error) {
                                return error + '<br>';
                            }).join('');
                            // Выводим сообщения об ошибках
                            Swal.fire({
                                title: 'Ошибка',
                                html: errorMessages,
                                confirmButtonText: 'ОК',
                                customClass: {
                                    popup: 'swal-custom',
                                    confirmButton: 'swal-button'
                                },
                                buttonsStyling: false // Отключает стилизацию по умолчанию для использования кастомных классов
                            });
                        } else {
                            // Выводим сообщения об ошибках
                            Swal.fire({
                                title: 'Ошибка',
                                text: datajson.errors,
                                confirmButtonText: 'ОК',
                                customClass: {
                                    popup: 'swal-custom',
                                    confirmButton: 'swal-button'
                                },
                                buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                            });
                        }
                        
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
                            buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Обработка ошибок при отправке файла
                    console.error('Ошибка при отправке файла:', error);
                    Swal.fire({
                        title: 'Ошибка',
                        text: 'Произошла ошибка при отправке файла. Пожалуйста, попробуйте еще раз.',
                        confirmButtonText: 'ОК',
                        customClass: {
                            popup: 'swal-custom',
                            confirmButton: 'swal-button'
                        },
                        buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                    });
                }
            });
        }

    </script>
</head>
<body>
<div class="container">
    <div>
        <label>Списки групп</label>
        <div class="form-group">
            <input type="button" value="Скачать шаблон" onclick="downloadFile('../excel/Шаблон_Группы.xlsx')">
            <input type="file" id="file1" multiple>
            <input type="button" id="send1" value="Отправить" onclick="sendFile('file1')">
        </div>
    </div>
    <div>
        <label>Учебные планы</label>
        <div class="form-group">
            <input type="button" value="Скачать шаблон" onclick="downloadFile('../excel/09.03.02.xlsx')">
            <input type="file" id="file2" multiple>
            <input type="button" id="send2" value="Отправить" onclick="sendFile('file2')">
        </div>
    </div>
    <div>
        <label>Списки преподавателей и дисциплин</label>
        <div class="form-group">
            <input type="button" value="Скачать шаблон" onclick="downloadFile('../excel/Шаблон_Преподаватели.xlsx')">
            <input type="file" id="file3" multiple>
            <input type="button" id="send3" value="Отправить" onclick="sendFile('file3')">
        </div>
    </div>
    <div>
        <label>Отчисленные, академический отпуск</label>
        <div class="form-group">
            <input type="button" value="Скачать шаблон" onclick="downloadFile('../excel/Шаблон_Отчисления.xlsx')">
            <input type="file" id="file4" multiple>
            <input type="button" id="send4" value="Отправить" onclick="sendFile('file4')">
        </div>
    </div>
</div>
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
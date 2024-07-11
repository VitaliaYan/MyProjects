<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Балльно-рейтинговая система</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/itiymain.css">
<link rel="icon" href="images/icon.png" type="image/png">
</head>
<body>
<div id="container" class="container">
    <div class ="header">
        <img src="images/ti.svg" alt="Логотип" width="400"/>
        <h1>Автоматизированный учет посещаемости и балльно-рейтинговая система</h1>
        <h3>Факультет информационных технологий и управления СПбГТИ(ТУ)</h3>
    </div>
</div>
<div class="supermenu">
	<div class="menu">
		<a class="menu1">Студенту</a>
		<div class="menu-content">
			<a href="student/attand.php">Учет посещаемости</a>
		</div>
	</div>

	<div class="menu">
		<a class="menu2">Преподавателю</a>
		<div class="menu-content">
			<a href="prepod/rating.php">Добавить рейтинг</a>
			<a href="prepod/show_rating.php">Просмотр рейтинга группы</a>
			<a href="prepod/show_direct_rating.php">Рейтинг по потоку</a>
			<a href="prepod/show_attand.php">Просмотр посещаемости</a>
		</div>
	</div>

	<div class="menu">
		<a class="menu3">Деканату</a>
		<div class="menu-content">
			<a href="dekanat/save_rating.php">Выгрузка рейтинга</a>
			<a href="dekanat/save_attand.php">Выгрузка посещаемости</a>
			<a href="dekanat/add_lists.php">Добавление данных</a>
			<a href="dekanat/sprav_students.php">Справочник студентов</a>
			<div class="sub-menu">
				<a href="#">Рассылка &#9205;</a>
				<div class="sub-menu-content">
					<a href="dekanat/send_authorize_students.php">Студентам</a>
					<a href="dekanat/send_authorize_prepod.php">Преподавателям</a>
				</div>
			</div>
			<a href="dekanat/add_user.php">Добавление пользователя</a>
		</div>
	</div>
</div>
<div id="footer"> 
  &copy; 2024, Факультет информационных технологий и управления,<br />
    Санкт-Петербургский государственный технологический институт<br /> 
    (Технический университет)
</div>
</body>
</html>


<?php
$connect = mysqli_connect('localhost', 'root', '', 'doingsdone');

if ($connect === false) {
  $error = "Ошибка подключения: " . mysqli_connect_error();
  $error_content = renderTemplate('templates/error.php', ["error" => $error]);
	print($error_content);
	exit();
}
?>

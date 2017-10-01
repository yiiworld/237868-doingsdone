<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

session_start();
require_once('functions.php');
require_once('db_functions.php');
require_once('mysql_helper.php');
require_once('init.php');
require_once('vendor/autoload.php');

// устанавливаем часовой пояс в Московское время
date_default_timezone_set('Europe/Moscow');

// параметры запроса
$projects_list = [];
$tasks_list = [];
$page_content = null;
$current_user = null;
$register = isset($_GET['register']) || isset($_POST['register']);
$project_id = isset($_GET['project']) ? intval($_GET['project']) : null;
$show_modal = false; // показывать ли модальное окно

$errors = []; // ошибки заполнения форм

if (isset($_GET['show_completed'])) {
  setcookie('showCompleteTasks', $_GET['show_completed']);
  $header_line = "Location: /index.php" . (isset($_GET['project']) ? '?project=' . $_GET['project'] : '');
  header($header_line);
  exit;
}

// показывать или нет выполненные задачи
$show_complete_tasks = isset($_COOKIE['showCompleteTasks']) ? (int) $_COOKIE['showCompleteTasks'] === 1 : false;

if (isset($_SESSION["user"])) {
  require_once('authorized.php');
} else {
  require_once('non-authorized.php');
}

$layout_content = renderTemplate('./templates/layout.php', [
  'page_main_content' => $page_content,
  'page_title' => 'Дела в порядке!',
  'projects_list' => $projects_list,
  'tasks_list' => $tasks_list,
  'show_complete_tasks' => $show_complete_tasks,
  'project_id' => $project_id,
  'overlay' => $show_modal,
  'user' => $current_user,
  'connection' => $connection,
  'register' => $register
]);
print($layout_content);

?>

<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once('functions.php');

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// устанавливаем часовой пояс в Московское время
date_default_timezone_set('Europe/Moscow');

$days = rand(-3, 3);
$task_deadline_ts = strtotime("+" . $days . " day midnight"); // метка времени даты выполнения задачи
$current_ts = strtotime('now midnight'); // текущая метка времени

$date_deadline = date("d.m.Y", $task_deadline_ts);

$days_until_deadline = floor(($task_deadline_ts -$current_ts ) / 86400);

$projects_list = ["Все", "Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];
$tasks_list = [
  ["name" => "Собеседование в IT компании", "date" => "01.06.2018", "project" => "Работа", "completed" => false ],
  ["name" => "Выполнить тестовое задание", "date" => "25.05.2018", "project" => "Работа", "completed" => false ],
  ["name" => "Сделать задание первого раздела", "date" => "21.04.2018", "project" => "Учеба", "completed" => true ],
  ["name" => "Встреча с другом", "date" => "22.04.2018", "project" => "Входящие", "completed" => false ],
  ["name" => "Купить корм для кота", "date" => null, "project" => "Домашние дела", "completed" => false ],
  ["name" => "Заказать пиццу", "date" => null, "project" => "Домашние дела", "completed" => false ]
];

$project_id = isset($_GET['project']) ? $_GET['project'] : 0;
$add = isset($_GET['add']);
$errors = [];

if (!array_key_exists($project_id, $projects_list)) {
  http_response_code(404);
} else {
  $new_task_data = [
    "name" => "",
    "project" => $projects_list[0],
    "date" => "",
    "preview" => ""
  ];
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST)){
    $required = ["name", "project", "date"];
    $rules = ["date" => "validateDate"];
    $new_task_data  = [
      "name" => (isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""),
      "project" => (isset($_POST["project"]) ? htmlspecialchars($_POST["project"]) : ""),
      "date" => (isset($_POST["date"]) ? $_POST["date"] : ""),
      "preview" => (isset($_POST["preview"]) ? $_POST["preview"] : ""),
      "completed" => false
    ];

    $errors = validateForm($required, $rules, $new_task_data);
    if (!count($errors)) {
      if (isset($_FILES["preview"])) {
         move_uploaded_file($_FILES["preview"]["tmp_name"],  __DIR__ . '/' . $_FILES["preview"]["name"]);
      }
      array_unshift($tasks_list, $new_task_data);
    }
  }

  $filtered_tasks = find_project_tasks($tasks_list, $projects_list[$project_id]);
  $page_content = renderTemplate('./templates/index.php', [
    'tasks_list' => $filtered_tasks,
    'show_complete_tasks' => $show_complete_tasks
  ]);

  $layout_content = renderTemplate('./templates/layout.php', [
    'page_main_content' => $page_content,
    'page_title' => 'Дела в порядке!',
    'projects_list' => $projects_list,
    'tasks_list' => $tasks_list,
    'project_id' => $project_id,
    'overlay' => $add || count($errors)
  ]);
  print($layout_content);

  if ($add || count($errors)) {
    $modal_content = renderTemplate('./templates/modal.php', [
      'data' => $new_task_data,
      'projects_list' => $projects_list,
      'errors' => $errors
    ]);
    print($modal_content);
  }
}
?>

<?php
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
  ["name" => "Собеседование в IT компании", "date" => "01.06.2018", "category" => "Работа", "completed" => false ],
  ["name" => "Выполнить тестовое задание", "date" => "25.05.2018", "category" => "Работа", "completed" => false ],
  ["name" => "Сделать задание первого раздела", "date" => "21.04.2018", "category" => "Учеба", "completed" => true ],
  ["name" => "Встреча с другом", "date" => "22.04.2018", "category" => "Входящие", "completed" => false ],
  ["name" => "Купить корм для кота", "date" => null, "category" => "Домашние дела", "completed" => false ],
  ["name" => "Заказать пиццу", "date" => null, "category" => "Домашние дела", "completed" => false ]
];

function calc_number_of_tasks($tasks, $category) {
  if ($category === "Все") {
    $result = count($tasks);
  } else {
    $filtered_array = array_filter($tasks, function ($var) use($category) {
      return $var["category"] === $category;
    });
    $result = count($filtered_array);
  }
  return $result;
}

$page_content = renderTemplate('./templates/index.php', [
  'tasks_list' => $tasks_list,
  'show_complete_tasks' => $show_complete_tasks
]);
$layout_content = renderTemplate('./templates/layout.php', [
  'page_main_content' => $page_content,
  'page_title' => 'Дела в порядке!',
  'projects_list' => $projects_list,
  'tasks_list' => $tasks_list
]);
print($layout_content);
?>

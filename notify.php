<?php
require_once('vendor/autoload.php');
require_once('functions.php');
require_once('init.php');


$sql = "SELECT u.id, u.name AS username, u.email, t.name, DATE_FORMAT(t.complete_until, '%d-%m-%Y %h:%i') AS deadline FROM tasks AS t " .
      "JOIN users AS u ON t.user_id = u.id " .
      "WHERE t.complete_until BETWEEN CURRENT_TIMESTAMP() AND DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 1 HOUR) " .
      "AND completed_at IS NULL";
$all_users_tasks = selectData($connection, $sql, []);
$users = array_group_by($all_users_tasks, 'email');

foreach ($users as $email => $user_tasks) {
  $tasks_string = '';
  foreach ($user_tasks as $ind => $task) {
    if ($ind !== 0) {
      $tasks_string = $tasks_string . ', ';
    }
    $tasks_string = $tasks_string . $task["name"] . " на " . $task["deadline"];
  }

  // Конфигурация траспорта
  $transport = new Swift_SmtpTransport('smtp.mail.ru', 465, 'ssl');
  $transport->setUsername('doingsdone@mail.ru');
  $transport->setPassword('rds7BgcL');
  // Формирование сообщения
  $message = new Swift_Message();
  $message->setContentType("text/plain");
  $message->setTo([$email => $user_tasks[0]["username"]]);
  $message->setSubject("Уведомление от сервиса «Дела в порядке»");
  $message->setBody("Уважаемый " . $user_tasks[0]["username"] . ". У вас" .
                    (count($user_tasks) === 1 ?
                    " запланирована задача " :
                    " запланированы задачи ") . $tasks_string );
  $message->setFrom("doingsdone@mail.ru", "DoingsDone");
  // Отправка сообщения
  $mailer = new Swift_Mailer($transport);
  $mailer->send($message);
}
?>

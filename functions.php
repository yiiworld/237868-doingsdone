<?php
require_once('mysql_helper.php');

function renderTemplate ($template_path, $template_data) {
  if (file_exists($template_path)) {
    extract($template_data);
    ob_start();
    require_once($template_path);
    $result = ob_get_clean();
  } else {
    $result = '';
  }
  return $result;
}

function find_project_tasks($connection, $project_id, $user, $tasks) {
  $result = [];
  if ($project_id) {
    $sql = "SELECT name, complete_until, completed_at FROM tasks WHERE project_id = ?";
    $result = selectData($connection, $sql, [$project_id]);
  } else {
    $result = $tasks;
  }
  return $result;
}

function calc_number_of_tasks($connection, $project_id, $user, $tasks) {
  return count(find_project_tasks($connection, $project_id, $user, $tasks));
}

function validateDate($value) {
  if ($value)  {
    $tmp = explode(".", $value);
    if (!checkdate($tmp[1], $tmp[0], $tmp[2])) {
      return "Введите дату в формате ДД.ММ.ГГГГ";
    }
  }
}

function validateEmail($value) {
  if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
    return "E-mail введён некорректно";
  }
}

function validateForm($required, $rules, $data) {
  $errors = [];
  foreach ($data as $key => $value) {
    if (in_array($key, $required) && $value == "") {
      $errors[$key] = "Заполните это поле";
    }
    if (key_exists($key, $rules)) {
      $error_text = call_user_func($rules[$key], $value);
      if ($error_text) {
        $errors[$key] = $error_text;
      }
    }
  }
  return $errors;
}

function searchUserByEmail($email, $users) {
  $result = null;
  foreach ($users as $user) {
    if ($user["email"] == $email) {
      $result = $user;
      break;
    }
  }
  return $result;
}

/**
 * Получение данных
 *
 * @param resource $connection Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
 * @param array $data Массив со всеми значениями для запроса
 *
 * @return array $result Результат запроса данных
 */
 function selectData ($connection, $sql, $data = []) {
   $result = [];
   $stmt = db_get_prepare_stmt($connection, $sql, $data);
   if ($stmt) {
     $query_result = mysqli_stmt_execute($stmt);
     if ($query_result) {
       $result = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
     }
   }
   return $result;
 }

 /**
  * Вставка данных
  *
  * @param resource $connection Ресурс соединения
  * @param string $table Имя таблицы для вставки
  * @param array $data Ассоциативный массив пар "поле - значение"
  *
  * @return integer||bool $result Id добавленной записи или false
  */
  function insertData ($connection, $table, $data = []) {
    $result = false;
    $keys = implode(', ', array_keys($data));
    $values = array_values($data);
    if ($keys) {
      $sql = 'INSERT INTO ' . $table . ' (' . $keys . ') VALUES (' . str_repeat('?, ', count($values)-1) . '?' . ')';
      $stmt = db_get_prepare_stmt($connection, $sql, $values);
      if ($stmt) {
        $query_result = mysqli_stmt_execute($stmt);
        if ($query_result) {
          $result = mysqli_insert_id($connection);
        }
      }
    }
    return $result;
 }

 /**
  * Произвольный запрос
  *
  * @param resource $connection Ресурс соединения
  * @param string $sql SQL запрос с плейсхолдерами вместо значений
  * @param array $data Массив со всеми значениями для запроса
  *
  * @return bool $result Признак успешности выполнения запроса
  */
function execQuery ($connection, $sql, $data = []) {
  $result = false;
  $stmt = db_get_prepare_stmt($connection, $sql, $data);
  if ($stmt) {
    $result = mysqli_stmt_execute($stmt);
  }
  return $result;
}
?>

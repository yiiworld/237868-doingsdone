<?php
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

function find_project_tasks($tasks, $category) {
  if ($category === "Все") {
    $result = $tasks;
  } else {
    $filtered_array = array_filter($tasks, function ($var) use($category) {
      return $var["project"] === $category;
    });
    $result = $filtered_array;
  }
  return $result;
}

function calc_number_of_tasks($tasks, $category) {
  return count(find_project_tasks($tasks, $category));
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
?>

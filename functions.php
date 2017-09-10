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
      return $var["category"] === $category;
    });
    $result = $filtered_array;
  }
  return $result;
}

function calc_number_of_tasks($tasks, $category) {
  return count(find_project_tasks($tasks, $category));
}

?>

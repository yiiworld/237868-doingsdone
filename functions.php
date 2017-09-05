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
?>

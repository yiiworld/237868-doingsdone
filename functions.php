<?php
function renderTemplate ($template_path, $template_data) {
  if (file_exists($template_path)) {
    ob_start();
    print(require($template_path));
    $result = ob_get_clean();
  } else {
    $result = '';
  }
  return $result;
}
 ?>

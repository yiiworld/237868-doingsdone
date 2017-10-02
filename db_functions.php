<?php
require_once('mysql_helper.php');

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

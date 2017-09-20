INSERT INTO users SET
  email = 'ignat.v@gmail.com',
  password = '$2y$10$OqvsKHQwr0Wk6FMZDoHo1uHoXd4UdxJG/5UDtUiie00XaxMHrW8ka',
  name = 'Игнат';
INSERT INTO users SET
  email = 'kitty_93@li.ru',
  password = '$2y$10$bWtSjUhwgggtxrnJ7rxmIe63ABubHQs0AS0hgnOo41IEdMHkYoSVa',
  name = 'Леночка';
INSERT INTO users SET
  email = 'warrior07@mail.ru',
  password = '$2y$10$2OxpEH7narYpkOT1H5cApezuzh10tZEEQ2axgFOaKW.55LxIJBgWW',
  name = 'Руслан';

INSERT INTO projects SET name = "Все", user_id = 1;
INSERT INTO projects SET name = "Все", user_id = 2;
INSERT INTO projects SET name = "Все", user_id = 3;
INSERT INTO projects SET name = "Входящие", user_id = 1;
INSERT INTO projects SET name = "Входящие", user_id = 2;
INSERT INTO projects SET name = "Входящие", user_id = 3;
INSERT INTO projects SET name = "Учеба", user_id = 1;
INSERT INTO projects SET name = "Учеба", user_id = 2;
INSERT INTO projects SET name = "Учеба", user_id = 3;
INSERT INTO projects SET name = "Работа", user_id = 1;
INSERT INTO projects SET name = "Работа", user_id = 2;
INSERT INTO projects SET name = "Работа", user_id = 3;
INSERT INTO projects SET name = "Домашние дела", user_id = 1;
INSERT INTO projects SET name = "Домашние дела", user_id = 2;
INSERT INTO projects SET name = "Домашние дела", user_id = 3;
INSERT INTO projects SET name = "Авто", user_id = 1;
INSERT INTO projects SET name = "Авто", user_id = 2;
INSERT INTO projects SET name = "Авто", user_id = 3;

INSERT INTO tasks SET
  name = "Собеседование в IT компании",
  complete_until = "01.06.2018",
  project_id = 10,
  user_id = 1;
INSERT INTO tasks SET
  name = "Собеседование в IT компании",
  complete_until = "01.06.2018",
  project_id = 11,
  user_id = 2;
INSERT INTO tasks SET
  name = "Собеседование в IT компании",
  complete_until = "01.06.2018",
  project_id = 12,
  user_id = 3;

INSERT INTO tasks SET
  name = "Выполнить тестовое задание",
  complete_until = "25.05.2018",
  project_id = 10,
  user_id = 1;
INSERT INTO tasks SET
  name = "Выполнить тестовое задание",
  complete_until = "25.05.2018",
  project_id = 11,
  user_id = 2;
INSERT INTO tasks SET
  name = "Выполнить тестовое задание",
  complete_until = "25.05.2018",
  project_id = 12,
  user_id = 3;

INSERT INTO tasks SET
  name = "Сделать задание первого раздела",
  complete_until = "21.04.2018",
  completed_at = "21.04.2018",
  project_id = 7,
  user_id = 1;
INSERT INTO tasks SET
  name = "Сделать задание первого раздела",
  complete_until = "21.04.2018",
  completed_at = "21.04.2018",
  project_id = 8,
  user_id = 2;
INSERT INTO tasks SET
  name = "Сделать задание первого раздела",
  complete_until = "21.04.2018",
  completed_at = "21.04.2018",
  project_id = 9,
  user_id = 3;

INSERT INTO tasks SET
  name = "Встреча с другом",
  complete_until = "22.04.2018",
  project_id = 4,
  user_id = 1;
INSERT INTO tasks SET
  name = "Встреча с другом",
  complete_until = "22.04.2018",
  project_id = 5,
  user_id = 2;
INSERT INTO tasks SET
  name = "Встреча с другом",
  complete_until = "22.04.2018",
  project_id = 6,
  user_id = 3;

INSERT INTO tasks SET
  name = "Купить корм для кота",
  project_id = 13,
  user_id = 1;
INSERT INTO tasks SET
  name = "Купить корм для кота",
  project_id = 14,
  user_id = 2;
INSERT INTO tasks SET
  name = "Купить корм для кота",
  project_id = 15,
  user_id = 3;

INSERT INTO tasks SET
  name = "Заказать пиццу",
  project_id = 13,
  user_id = 1;
INSERT INTO tasks SET
  name = "Заказать пиццу",
  project_id = 14,
  user_id = 2;
INSERT INTO tasks SET
  name = "Заказать пиццу",
  project_id = 15,
  user_id = 2;

-- получить список из всех проектов для одного пользователя;
SELECT * FROM projects WHERE user_id = 1;

-- получить список из всех задач для одного проекта;
SELECT * FROM tasks WHERE project_id = 13 AND user_id = 1;

-- пометить задачу как выполненную;
UPDATE tasks SET completed_at = '24.05.2018' WHERE id = 4;

-- получить все задачи для завтрашнего дня;
SELECT * FROM tasks WHERE complete_until = CURDATE() + INTERVAL 1 DAY;

-- обновить название задачи по её идентификатору.
UPDATE tasks SET name = "Новое название" WHERE id = 1;

CREATE DATABASE doingsdone;
USE doingsdone;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(255) NOT NULL,
  name CHAR(128),
  password VARCHAR(255) NOT NULL,
  contacts TEXT
);

CREATE UNIQUE INDEX email_users ON users(email);

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name CHAR(128) NOT NULL,
  user_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE UNIQUE INDEX project_projects ON projects(name, user_id);
CREATE INDEX user_projects ON projects(user_id);

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME,
  name CHAR(128) NOT NULL,
  file TEXT,
  complete_until DATETIME,
  user_id INT NOT NULL,
  project_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id)
);

CREATE INDEX user_tasks ON tasks(user_id);
CREATE INDEX name_tasks ON tasks(name, user_id);
CREATE INDEX project_tasks ON tasks(project_id, user_id);
CREATE INDEX name_by_project_tasks ON tasks(name, project_id, user_id);

/*
  # Initial database schema for task management system

  1. Tables
    - clients
      - id (int, primary key)
      - name (varchar)
      - address (text)
      - phone (varchar)
      - maps_url (text)
      - created_at (timestamp)
      - updated_at (timestamp)
    
    - tasks
      - id (int, primary key)
      - client_id (int, foreign key)
      - description (text)
      - schedule_time (time)
      - schedule_date (date)
      - value (decimal)
      - expenses (decimal)
      - status (enum)
      - created_at (timestamp)
      - updated_at (timestamp)
      - archived_at (timestamp)
    
    - subtasks
      - id (int, primary key)
      - task_id (int, foreign key)
      - description (text)
      - completed (boolean)
      - created_at (timestamp)
      - updated_at (timestamp)
*/

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(50) NOT NULL,
    maps_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    description TEXT NOT NULL,
    schedule_time TIME NOT NULL,
    schedule_date DATE NOT NULL,
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expenses DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'problems', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    archived_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE subtasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    description TEXT NOT NULL,
    completed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Indexes for better performance
CREATE INDEX idx_client_name ON clients(name);
CREATE INDEX idx_task_date ON tasks(schedule_date);
CREATE INDEX idx_task_status ON tasks(status);
CREATE INDEX idx_task_archived ON tasks(archived_at);
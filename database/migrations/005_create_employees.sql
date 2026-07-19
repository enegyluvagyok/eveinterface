CREATE TABLE IF NOT EXISTS employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(191) NOT NULL,
    contractor_id INT UNSIGNED NOT NULL,
    subcontractor_id INT UNSIGNED NOT NULL,
    fullname VARCHAR(191) NOT NULL,
    idcard VARCHAR(191) NOT NULL,
    photo VARCHAR(191) NULL,
    avatar VARCHAR(191) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    imported_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_employees_imported (imported_at),
    CONSTRAINT fk_employees_contractor FOREIGN KEY (contractor_id) REFERENCES contractors (id),
    CONSTRAINT fk_employees_subcontractor FOREIGN KEY (subcontractor_id) REFERENCES subcontractors (id),
    CONSTRAINT fk_employees_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

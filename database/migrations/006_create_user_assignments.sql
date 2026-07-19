CREATE TABLE IF NOT EXISTS user_contractors (
    user_id BIGINT UNSIGNED NOT NULL,
    contractor_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, contractor_id),
    CONSTRAINT fk_user_contractors_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_user_contractors_contractor FOREIGN KEY (contractor_id) REFERENCES contractors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_subcontractors (
    user_id BIGINT UNSIGNED NOT NULL,
    subcontractor_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, subcontractor_id),
    CONSTRAINT fk_user_subcontractors_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_user_subcontractors_subcontractor FOREIGN KEY (subcontractor_id) REFERENCES subcontractors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

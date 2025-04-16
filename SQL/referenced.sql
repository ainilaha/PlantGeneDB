-- 参考文献表
CREATE TABLE referenced (
    reference_id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    authors VARCHAR(255) NOT NULL,
    publication_year YEAR,
    journal_name VARCHAR(255),
    volume_issue_pages VARCHAR(100),
    doi VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(upload_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
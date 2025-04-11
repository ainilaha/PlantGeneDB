-- 物种信息表
CREATE TABLE species (
    species_id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    scientific_name VARCHAR(255) NOT NULL,
    common_name VARCHAR(255),
    genus VARCHAR(100),
    genome_type VARCHAR(50),
    genome_size VARCHAR(50),
    chromosome_number INT,
    gene_number INT,
    cds_number INT,
    species_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(upload_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

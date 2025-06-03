CREATE TABLE Phenotype (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           Species VARCHAR(100),
                           Class VARCHAR(50),
                           Trait_name VARCHAR(100),
                           Record_num INT,
                           Planting_location VARCHAR(255),
                           Planting_date VARCHAR(100),
                           Treatment VARCHAR(200),
                           Source VARCHAR(200),
                           Link VARCHAR(255),
                           submitted_by INT,
                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                           updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                           FOREIGN KEY (submitted_by) REFERENCES users(id)
);
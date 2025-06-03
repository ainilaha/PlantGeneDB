CREATE TABLE Microbiomics (
         id INT AUTO_INCREMENT PRIMARY KEY,
         Species VARCHAR(255) NOT NULL,
         Article_Overview TEXT,
         Family_of_endophyte_fungi VARCHAR(255),
         Genus_of_endophyte_fungi VARCHAR(255),
         Species_of_endophyte_fungi VARCHAR(255),
         Genome_of_endophyte_fungi VARCHAR(255),
         Microbiome_of_endophyte_fungi VARCHAR(255),
         Tissue VARCHAR(255),
         Biotic_stress VARCHAR(255),
         Abiotic_stress VARCHAR(255),
         Source VARCHAR(255),
         Link VARCHAR(255),
         submitted_by INT,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
     );
ALTER TABLE Microbiomics 
ADD COLUMN Transcriptome_of_endophyte_fungi VARCHAR(255) 
AFTER Genome_of_endophyte_fungi;
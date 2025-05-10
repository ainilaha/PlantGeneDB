DROP TABLE IF EXISTS genomics_species;  

CREATE TABLE genomics_species (  
    id INT AUTO_INCREMENT PRIMARY KEY,
    species_name VARCHAR(255) NOT NULL,  -- 物种名称
    scientific_name VARCHAR(255) NOT NULL,  -- 包含品系信息的完整学名
    common_name VARCHAR(255),
    genus VARCHAR(255) NOT NULL,
    genome_type VARCHAR(255),
    genome_size VARCHAR(50),
    chromosome_number VARCHAR(50),
    gene_number VARCHAR(50),
    cds_number VARCHAR(50),
    description TEXT,
    image_url VARCHAR(255),
    genomic_sequence VARCHAR(255),
    cds_sequence VARCHAR(255),
    gff3_annotation VARCHAR(255),
    peptide_sequence VARCHAR(255),
    reference_link TEXT,  -- 包含完整参考文献信息和链接
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    submitted_by INT NULL,
    reviewed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (species_name)
);  

-- 插入Elymus nutans的第一个品系
INSERT INTO genomics_species (
    species_name,
    scientific_name,
    genus,
    genome_type,
    genome_size,
    chromosome_number,
    description,
    image_url,
    genomic_sequence,
    cds_sequence,
    gff3_annotation,
    peptide_sequence,
    reference_link,
    status
) VALUES (
    'Elymus nutans',  
    'Elymus nutans Griseb. cv. Aba',  
    'Elymus',
    'StStHHYY',
    '9.46Gb',  
    '21',
    'Elymus nutans Griseb. (E. nutans) is a perennial herbaceous plant of the Gramineae family. As an excellent pasture grass in the Qinghai-Tibetan Plateau region in China, it has strong resistance and tolerance to cold, rich nutrients, good palatability, high yield, and tolerance to desert areas among others. Its deep root system aids in soil stabilization, while its moderate salinity tolerance allows growth in marginal lands. Elymus nutans is also noted for its genetic diversity, enabling resilience to climate variability. Elymus nutans was cytogenetically identified as an allohexaploid (2n = 6x = 42) with a genomic constitution of StStHHYY.',
    'assets/img/Elymus_nutans.png',
    'Elymus_nutans_genome.fasta',
    'Elymus_nutans_cds.fasta',
    'Elymus_nutans_annotation.gff3',
    'Elymus_nutans_peptide.fasta',
    'our data, unpublished',
    'approved'
);

-- 插入Elymus nutans的第二个品系
INSERT INTO genomics_species (
    species_name,
    scientific_name,
    genus,
    genome_type,
    genome_size,
    description,
    image_url,
    genomic_sequence,
    cds_sequence,
    gff3_annotation,
    peptide_sequence,
    reference_link,
    status
) VALUES (
    'Elymus nutans',  
    'Elymus nutans wild accession FS20130',  
    'Elymus',
    'StStHHYY',
    '10.47Gb',
    'Elymus nutans Griseb. (E. nutans) is a perennial herbaceous plant of the Gramineae family. As an excellent pasture grass in the Qinghai-Tibetan Plateau region in China, it has strong resistance and tolerance to cold, rich nutrients, good palatability, high yield, and tolerance to desert areas among others. Its deep root system aids in soil stabilization, while its moderate salinity tolerance allows growth in marginal lands. Elymus nutans is also noted for its genetic diversity, enabling resilience to climate variability. Elymus nutans was cytogenetically identified as an allohexaploid (2n = 6x = 42) with a genomic constitution of StStHHYY.',
    'assets/img/Elymus_nutans.png',
    'Elymus_nutans_FS20130_genome.fasta',
    'Elymus_nutans_FS20130_cds.fasta',
    'Elymus_nutans_FS20130_annotation.gff3',
    'Elymus_nutans_FS20130_peptide.fasta',
    'Xiong, Y., Yuan, S., Xiong, Y. et al. Analysis of allohexaploid wheatgrass genome reveals its Y haplome origin in Triticeae and high-altitude adaptation. Nat Commun 16, 3104 (2025). https://www.nature.com/articles/s41467-025-58341-0',
    'approved'
);

-- 添加其他物种
INSERT INTO genomics_species (
    species_name,
    scientific_name,
    genus,
    status
) VALUES 
('Aegilops comosa', 'Aegilops comosa', 'Aegilops', 'approved'),
('Elymus sibiricus', 'Elymus sibiricus', 'Elymus', 'approved'),
('Pseudoroegneria libanotica', 'Pseudoroegneria libanotica', 'Pseudoroegneria', 'approved'),
('Leymus chinensis', 'Leymus chinensis', 'Leymus', 'approved'),
('Aegilops tauschii', 'Aegilops tauschii', 'Aegilops', 'approved'),
('Hordeum vulgare', 'Hordeum vulgare', 'Hordeum', 'approved'),
('Hordeum vulgare var. nudum', 'Hordeum vulgare var. nudum', 'Hordeum', 'approved'),
('Avena sativa', 'Avena sativa', 'Avena', 'approved'),
('Thinopyrum elongatum', 'Thinopyrum elongatum', 'Thinopyrum', 'approved'),
('Thinopyrum intermedium', 'Thinopyrum intermedium', 'Thinopyrum', 'approved'),
('Aegilops ventricosa', 'Aegilops ventricosa', 'Aegilops', 'approved'),
('Agropyron cristatum', 'Agropyron cristatum', 'Agropyron', 'approved'),
('Aegilops triuncialis', 'Aegilops triuncialis', 'Aegilops', 'approved'),
('Leymus secalinus', 'Leymus secalinus', 'Leymus', 'approved'),
('Triticale', 'Triticale', 'Triticale', 'approved'),
('Phalaris arundinacea', 'Phalaris arundinacea', 'Phalaris', 'approved');

CREATE TABLE genomics_species (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scientific_name VARCHAR(255) NOT NULL,
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
    reference_link VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_by INT,
    reviewed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
-- 1. Elymus nutans (Accession 1)
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Elymus nutans Griseb. cv. Aba', 
    '', 
    'Elymus', 
    'StStHHYY', 
    '9.46Gb', 
    '21', 
    '', 
    '', 
    'Elymus nutans Griseb. (E. nutans) is a perennial herbaceous plant of the Gramineae family. As an excellent pasture grass in the Qinghai-Tibetan Plateau region in China, it has strong resistance and tolerance to cold, rich nutrients, good palatability, high yield, and tolerance to desert areas among others. Its deep root system aids in soil stabilization, while its moderate salinity tolerance allows growth in marginal lands. Elymus nutans is also noted for its genetic diversity, enabling resilience to climate variability.',
    'assets/img/Elymus_nutans.png',
    'our data, unpublished'
);

-- 2. Elymus sibiricus
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Elymus sibiricus L. cv. Chuancao No.2', 
    'Siberian wild rye', 
    'Elymus', 
    'StStHH', 
    '6.74Gb', 
    '14', 
    '', 
    '', 
    'Elymus sibiricus L. (Gramineae) is an allotetraploid with a genome constitution of StStHH(2n = 4X = 28), as a model species of the Elymus genus, indigenous to the Qinghai Tibet Plateau (QTP) and widely distributed in Eurasia. As a highly adaptable perennial grass species, it plays a pivotal ecological and agronomic role in diverse habitats.',
    'assets/img/Elymus_sibiricus.png',
    'our data, unpublished'
);

-- 3. Pseudoroegneria libanotica
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Pseudoroegneria libanotica PI 228,392', 
    '', 
    'Pseudoroegneria', 
    'StSt', 
    '6.74Gb', 
    '7', 
    '46369', 
    '', 
    'Pseudoroegneria libanotica (Hack.) Á. Löve is a perennial grass species primarily distributed in arid mountainous regions and plateaus of West Asia, including Lebanon, Syria, and Turkey. It typically thrives in limestone-rich soils and exhibits strong drought resistance, often found in grasslands or shrubland margins at elevations of 1,000-2,500 m.',
    'assets/img/Pseudoroegneria_libanotica.png',
    'https://doi.org/10.1186/s12864-024-10140-5'
);

-- 4. Leymus chinensis
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Leymus chinensis Lc6-5', 
    'sheepgrass, Chinese wildrye', 
    'Leymus', 
    'NsNsXmXm', 
    '8Gb', 
    '14', 
    '84641', 
    '', 
    'Leymus chinensis (Trin.) Tzvel, commonly known as sheepgrass or Chinese wildrye, is a perennial rhizomatous grass (2n = 4× = 28, NsNsXmXm) belonging to the Triticeae tribe. As a dominant species in the Eurasian Steppe, it spans approximately 420,000 km² across northern and northeastern China, Mongolia, Siberia, and North Korea.',
    'assets/img/Leymus_chinensis.png',
    'https://doi.org/10.1073/pnas.2308984120'
);

-- 5. Aegilops tauschii
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Aegilops tauschii T093', 
    '', 
    'Aegilops', 
    'DD', 
    '4.12Gb', 
    '7', 
    '62389', 
    '693594', 
    'Aegilops tauschii Coss (2n = 2x = 14, DD), a diploid wild goatgrass naturally distributed from East Turkey to China and West Pakistan, is the progenitor of the D subgenome in hexaploid bread wheat (Triticum aestivum, AABBDD) and serves as a vital genetic reservoir for enhancing wheat performance and stress resilience.',
    'assets/img/Aegilops_tauschii.png',
    'https://doi.org/10.1038/s41477-017-0067-8'
);

-- 6. Hordeum vulgare
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Hordeum vulgare cv. Morex', 
    'Barley', 
    'Hordeum', 
    'HH', 
    '5.1Gb', 
    '7', 
    '', 
    '', 
    'Hordeum vulgare (commonly known as barley) is an annual grass species belonging to the Poaceae (Gramineae) family. As one of the world''s oldest cultivated cereal crops, Hordeum vulgare has played a crucial role in agriculture, serving as a staple food, animal feed, and a key ingredient in brewing and distilling industries.',
    'assets/img/Hordeum_vulgare.png',
    'https://doi.org/10.1038/nature11543'
);

-- 7. Hordeum vulgare var. nudum
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Hordeum vulgare L. var. nudum Lasa Goumang', 
    'qingke', 
    'Hordeum', 
    'HH', 
    '4.5Gb', 
    '7', 
    '46787', 
    '', 
    'Hordeum vulgare var. nudum, commonly known as qingke, is an annual grass species belonging to the Gramineae family. Its hull-free grains reduce processing costs, making it a preferred choice in traditional diets. This variety is highly valued for its nutritional quality, ease of processing, and adaptability to harsh environments.',
    'assets/img/Hordeum_vulgare_var_nudum.png',
    'https://doi.org/10.1038/s41597-020-0480-0'
);

-- 8. Avena sativa
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Avena sativa cv. Marvellous', 
    'Oat', 
    'Avena', 
    'AACCDD', 
    '11Gb', 
    '21', 
    '', 
    '', 
    'Avena sativa, commonly known as oat, is an annual herbaceous plant belonging to the Poaceae (Gramineae) family. Avena sativa, with global production ranking seventh among cereals, is an economically important worldwide food and livestock feed with strong adaptability to various harsh marginal environments.',
    'assets/img/Avena_sativa.png',
    'https://www.cell.com/molecular-plant/fulltext/S1674-2052(25)00027-9'
);

-- 9. Thinopyrum elongatum
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Thinopyrum elongatum, accession 401007', 
    '', 
    'Thinopyrum', 
    'EEEEEEE^St^E^St^E^St^E^St^', 
    '', 
    '35+2', 
    '', 
    '', 
    'Thinopyrum elongatum is a perennial herbaceous plant belonging to the Poaceae (Gramineae) family. As a highly adaptable and resilient grass species, it is widely distributed in temperate regions across Eurasia and North America. Thinopyrum elongatum is particularly valued for its exceptional abiotic stress tolerance.',
    'assets/img/Thinopyrum_elongatum.png',
    'https://doi.org/10.1007/s00122-020-03591-3'
);

-- 10. Thinopyrum intermedium
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Thinopyrum intermedium', 
    '', 
    'Thinopyrum', 
    'StStJJJsJs', 
    '12~14Gb', 
    '21', 
    '', 
    '', 
    'Thinopyrum intermedium is a perennial herbaceous plant of the Poaceae (Gramineae) family. It is particularly valued for its exceptional drought tolerance, winter hardiness, and soil conservation properties, making it an important forage crop and a promising candidate for sustainable agriculture in marginal environments.',
    'assets/img/Thinopyrum_intermedium.png',
    'https://doi.org/10.1007/s00122-016-2799-7'
);

-- 11. Aegilops ventricosa
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Aegilops ventricosa', 
    'barbed goatgrass', 
    'Aegilops', 
    'D^v^D^v^N^v^N^v^', 
    '8.67Gb', 
    '14', 
    '69000', 
    '', 
    'Aegilops ventricosa Tausch (syn. Aegilops ventricosa), commonly known as barbed goatgrass, is an allotetraploid wild grass species (2n = 4x = 28, genome D^v^D^v^N^v^N^v^) within the Poaceae family. Native to Mediterranean regions and neighboring arid areas, it thrives in disturbed habitats, rocky slopes, and nutrient-poor soils.',
    'assets/img/Aegilops_ventricosa.png',
    'https://doi.org/10.1016/j.xplc.2024.101131'
);

-- 12. Aegilops comosa
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Aegilops comosa', 
    'barbed goatgrass', 
    'Aegilops', 
    'MM', 
    '4.47Gb', 
    '7', 
    '39057', 
    '', 
    'Aegilops comosa L., commonly known as barbed goatgrass, is a diploid wild grass species (2n = 2x = 14, genome MM) within the Poaceae family, native to Mediterranean and Southwest Asian regions. It thrives in arid and semi-arid habitats, including rocky slopes and nutrient-poor soils.',
    'assets/img/Aegilops_comosa.png',
    'https://doi.org/10.1038/s41597-024-04346-1'
);

-- 13. Agropyron cristatum
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Agropyron cristatum', 
    'crested wheatgrass', 
    'Agropyron', 
    '', 
    '', 
    '7,14,24', 
    '', 
    '', 
    'Agropyron cristatum (L.) Gaertn. (Poaceae family), commonly known as crested wheatgrass, is a perennial grass native to arid and semi-arid regions of northern China, Mongolia, and Siberia. It thrives in dry grasslands, sandy soils, and slopes, forming dense tufts with rough, rolled leaves and robust spike-like inflorescences.',
    'assets/img/Agropyron_cristatum.png',
    ''
);

-- 14. Aegilops triuncialis
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Aegilops triuncialis', 
    'barbed goatgrass, triuncialis wheat', 
    'Aegilops', 
    'UUCC', 
    '', 
    '14', 
    '', 
    '', 
    'Aegilops triuncialis L., commonly known as barbed goatgrass or simply triuncialis wheat, is an allopolyploid grass species within the Poaceae family. Native to arid and semi-arid regions of Southwest Asia and the Mediterranean Basin, it thrives in disturbed habitats, rocky slopes, and grasslands with poor soil fertility.',
    'assets/img/Aegilops_triuncialis.png',
    ''
);

-- 15. Leymus secalinus
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Leymus secalinus', 
    'Lao Pi Jian, Bin Cao', 
    'Leymus', 
    'NsXm', 
    '', 
    '14,24', 
    '', 
    '', 
    'Leymus secalinus (Georgi) Tzvel., commonly known as "Lao Pi Jian" or "Bin Cao" in Chinese, is a perennial rhizomatous grass species within the Poaceae family. Native to arid and semi-arid regions of northern China, including Sichuan, Gansu, Qinghai, and Inner Mongolia, it thrives in diverse habitats.',
    'assets/img/Leymus_secalinus.png',
    ''
);

-- 16. Triticale
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Triticale', 
    'Triticale', 
    'Triticum×Secale', 
    '', 
    '', 
    '', 
    '', 
    '', 
    'Triticale (×Triticosecale Wittmack) is a human-made hybrid species derived from interspecific crosses between wheat (Triticum spp.) and rye (Secale cereale L.), followed by chromosome doubling to stabilize its genome. This allopolyploid combines the high yield and grain quality of wheat with the environmental resilience of rye.',
    'assets/img/Triticale.png',
    ''
);

-- 17. Phalaris arundinacea
INSERT INTO genomics_species (
    scientific_name, common_name, genus, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, description, 
    image_url, reference_link
) VALUES (
    'Phalaris arundinacea', 
    'reed canary grass, gardener''s-garters', 
    'Phalaris', 
    '', 
    '', 
    '7,14', 
    '', 
    '', 
    'Phalaris arundinacea L., commonly known as reed canary grass or gardener''s-garters, is a perennial rhizomatous grass species within the Poaceae family. Native to temperate regions of Europe, Asia, and North America, it has naturalized globally in diverse habitats, including wetlands, riverbanks, moist meadows, and disturbed soils.',
    'assets/img/Phalaris_arundinacea.png',
    ''
);

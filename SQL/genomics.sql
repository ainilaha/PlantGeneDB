DROP TABLE IF EXISTS genomics_species;
DROP TABLE IF EXISTS species_accessions;
DROP TABLE IF EXISTS species_references;

-- 创建主要的species表
CREATE TABLE genomics_species (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scientific_name VARCHAR(255) NOT NULL,
    common_name VARCHAR(255),
    genus VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 创建物种的不同资源库(accessions)表
CREATE TABLE species_accessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    species_id INT NOT NULL,
    accession_name VARCHAR(255) NOT NULL,
    genome_type VARCHAR(255),
    genome_size VARCHAR(50),
    chromosome_number VARCHAR(50),
    gene_number VARCHAR(50),
    cds_number VARCHAR(50),
    image_url VARCHAR(255),
    genomic_sequence VARCHAR(255),
    cds_sequence VARCHAR(255),
    gff3_annotation VARCHAR(255),
    peptide_sequence VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    submitted_by INT NULL,
    reviewed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (species_id) REFERENCES genomics_species(id) ON DELETE CASCADE
);

-- 创建参考文献表
CREATE TABLE species_references (
    id INT AUTO_INCREMENT PRIMARY KEY,
    species_id INT NOT NULL,
    accession_id INT NULL, -- 可以关联到特定的accession或整个物种
    authors TEXT,
    title TEXT,
    journal VARCHAR(255),
    year VARCHAR(10),
    doi VARCHAR(255),
    link VARCHAR(255),
    citation_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (species_id) REFERENCES genomics_species(id) ON DELETE CASCADE,
    FOREIGN KEY (accession_id) REFERENCES species_accessions(id) ON DELETE SET NULL
);

-- 插入物种数据 - 使用星号标记需要斜体的部分
-- 1. Elymus nutans
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Elymus nutans',
    '',
    'Elymus',
    '*Elymus nutans* Griseb. (*E. nutans*) is a perennial herbaceous plant of the Gramineae family. As an excellent pasture grass in the Qinghai-Tibetan Plateau region in China, it has strong resistance and tolerance to cold, rich nutrients, good palatability, high yield, and tolerance to desert areas among others. Its deep root system aids in soil stabilization, while its moderate salinity tolerance allows growth in marginal lands. *Elymus nutans* is also noted for its genetic diversity, enabling resilience to climate variability. *Elymus nutans* was cytogenetically identified as an allohexaploid (2n = 6x = 42) with a genomic constitution of StStHHYY.'
);

-- 2. Elymus sibiricus
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Elymus sibiricus',
    'Siberian wild rye',
    'Elymus',
    '*Elymus sibiricus* L. (Gramineae) is an allotetraploid with a genome constitution of StStHH(2n = 4X = 28), as a model species of the *Elymus* genus, indigenous to the Qinghai Tibet Plateau (QTP) and widely distributed in Eurasia. As a highly adaptable perennial grass species, it plays a pivotal ecological and agronomic role in diverse habitats, including meadows, valleys, bushwood, and open forests, at elevations ranging from 1,000 to 4,000 meters. Recognized as a dominant or constructive species in grassland communities, it exhibits exceptional stress tolerance, thriving under cold and drought conditions while demonstrating potential for saline-alkali soil adaptation due to its extensive root system. *E. sibiricus* is valued for its high yield, superior crude protein content, palatability, and vigorous tillering capacity, making it an ideal forage crop.'
);

-- 3. Pseudoroegneria libanotica
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Pseudoroegneria libanotica',
    '',
    'Pseudoroegneria',
    '*Pseudoroegneria libanotica* (Hack.) Á. Löve is a perennial grass species primarily distributed in arid mountainous regions and plateaus of West Asia, including Lebanon, Syria, and Turkey. It typically thrives in limestone-rich soils and exhibits strong drought resistance, often found in grasslands or shrubland margins at elevations of 1,000-2,500 m. Ecologically, this species serves as a wild forage resource for local livestock, while its robust root system contributes to soil stabilization and erosion control. Due to its remarkable adaptation to water-limited environments, *P. libanotica* is considered a potential genetic resource for drought-resistant traits, offering valuable insights for the improvement of cultivated cereals in Poaceae breeding programs.'
);

-- 4. Leymus chinensis
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Leymus chinensis',
    'sheepgrass, Chinese wildrye',
    'Leymus',
    '*Leymus chinensis* (Trin.) Tzvel, commonly known as sheepgrass or Chinese wildrye, is a perennial rhizomatous grass (2n = 4× = 28, NsNsXmXm) belonging to the Triticeae tribe. As a dominant species in the Eurasian Steppe, it spans approximately 420,000 km² across northern and northeastern China, Mongolia, Siberia, and North Korea. This ecologically and economically valuable grass exhibits exceptional adaptability to harsh environmental conditions, including drought, saline-alkali soils, low fertility, and freezing temperatures, owing to its extensive rhizome network. Additionally, *L. chinensis* is highly regarded in grassland restoration and livestock husbandry due to its high biomass yield, nutritional quality, and palatability, making it a key species for artificial grassland development and sustainable pasture management.'
);

-- 5. Aegilops tauschii
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Aegilops tauschii',
    '',
    'Aegilops',
    '*Aegilops tauschii* Coss (2n = 2x = 14, DD), a diploid wild goatgrass naturally distributed from East Turkey to China and West Pakistan, is the progenitor of the D subgenome in hexaploid bread wheat (*Triticum aestivum*, AABBDD) and serves as a vital genetic reservoir for enhancing wheat performance and stress resilience. Initially introduced as fodder, its drought tolerance and adaptability allowed it to proliferate as a common weed in arid farmlands and wheat fields. As the donor of wheat''s D subgenome, *Ae. tauschii* remains a key species for crop improvement efforts.'
);

-- 6. Hordeum vulgare
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Hordeum vulgare',
    'Barley',
    'Hordeum',
    '*Hordeum vulgare* (commonly known as barley) is an annual grass species belonging to the Poaceae (Gramineae) family. As one of the world''s oldest cultivated cereal crops, *Hordeum vulgare* has played a crucial role in agriculture, serving as a staple food, animal feed, and a key ingredient in brewing and distilling industries. It exhibits strong adaptability to diverse climates, including temperate, subarctic, and semi-arid regions, making it a globally important crop. *Hordeum vulgare* is a diploid (2n = 2x = 14, HH) species with a large genome (~5.1 Gb).'
);

-- 7. Hordeum vulgare var. nudum
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Hordeum vulgare var. nudum',
    'qingke',
    'Hordeum',
    '*Hordeum vulgare* var. *nudum*, commonly known as qingke, is an annual grass species belonging to the Gramineae family. Its hull-free grains reduce processing costs, making it a preferred choice in traditional diets. This variety is highly valued for its nutritional quality, ease of processing, and adaptability to harsh environments, making it a staple food in regions such as the Tibetan Plateau, the Himalayas, and parts of East Asia. *Hordeum vulgare* var. *nudum* is a diploid (2n = 2x = 14, HH) species with a genome size of ~4.5 Gb.'
);

-- 8. Avena sativa
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Avena sativa',
    'Oat',
    'Avena',
    '*Avena sativa*, commonly known as oat, is an annual herbaceous plant belonging to the Poaceae (Gramineae) family. *Avena sativa*, with global production ranking seventh among cereals, is an economically important worldwide food and livestock feed with strong adaptability to various harsh marginal environments. As one of the most important cereal crops globally, oats are widely cultivated for their nutritional grains, forage value, and soil-improving properties. *Avena sativa* is a hexaploid species (2n = 6x = 42, AACCDD), with three distinct subgenomes derived from ancestral wild oats (*A. sterilis*, *A. fatua*).'
);

-- 9. Thinopyrum elongatum
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Thinopyrum elongatum',
    '',
    'Thinopyrum',
    '*Thinopyrum elongatum* is a perennial herbaceous plant belonging to the Poaceae (Gramineae) family. As a highly adaptable and resilient grass species, it is widely distributed in temperate regions across Eurasia and North America. *Thinopyrum elongatum* is particularly valued for its exceptional abiotic stress tolerance, including drought, salinity, and cold resistance, making it an important genetic resource for cereal crop improvement and a promising forage grass for marginal lands. *Thinopyrum elongatum* is an decaploid species(2n = 10x = 70 + 4) with a genomic constitution of EEEEEEE^St^E^St^E^St^E^St^.'
);

-- 10. Thinopyrum intermedium
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Thinopyrum intermedium',
    '',
    'Thinopyrum',
    '*Thinopyrum intermedium* is a perennial herbaceous plant of the Poaceae (Gramineae) family. It is particularly valued for its exceptional drought tolerance, winter hardiness, and soil conservation properties, making it an important forage crop and a promising candidate for sustainable agriculture in marginal environments. *Thinopyrum intermedium* is an allohexaploid nature (2n = 6x = 42) with a genomic constitution of StStJJJsJs. Its hexaploid genome (StStJJJsJs) combines stress tolerance traits from multiple ancestors, bridging the gap between wild and cultivated Triticeae species.'
);

-- 11. Aegilops ventricosa
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Aegilops ventricosa',
    'barbed goatgrass',
    'Aegilops',
    '*Aegilops ventricosa* Tausch (syn. *Aegilops ventricosa*), commonly known as barbed goatgrass, is an allotetraploid wild grass species (2n = 4x = 28, genome D^v^D^v^N^v^N^v^) within the Poaceae family. Native to Mediterranean regions and neighboring arid areas, it thrives in disturbed habitats, rocky slopes, and nutrient-poor soils, showcasing exceptional adaptability to drought and low-fertility conditions. Its high-quality reference genome (8.67 Gb, contig N50 = 54.71 Mb), revealing evolutionary divergence in its D^v^ subgenome compared to the D subgenome of common wheat. Phylogenetic analyses indicate that *Ae. ventricosa* diverged approximately 0.7 million years ago, predating the hybridization event that formed hexaploid wheat.'
);

-- 12. Aegilops comosa
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Aegilops comosa',
    'barbed goatgrass',
    'Aegilops',
    '*Aegilops comosa* L., commonly known as barbed goatgrass, is a diploid wild grass species (2n = 2x = 14, genome MM) within the Poaceae family, native to Mediterranean and Southwest Asian regions. It thrives in arid and semi-arid habitats, including rocky slopes and nutrient-poor soils, demonstrating exceptional adaptability to drought and low-fertility conditions. Recent advancements in genomic research have unveiled its high-quality reference genome (4.47 Gb, contig N50 = 23.59 Mb, scaffold N50 = 619.05 Mb), assembled using PacBio HiFi and Hi-C sequencing technologies. The genome, annotated with 39,057 genes, reveals a high repeat content (86.30%) and structural variations, including an intra-chromosomal translocation on the 2M chromosome, which may underpin its unique stress-responsive traits.'
);

-- 13. Agropyron cristatum
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Agropyron cristatum',
    'crested wheatgrass',
    'Agropyron',
    '*Agropyron cristatum* (L.) Gaertn. (Poaceae family), commonly known as crested wheatgrass, is a perennial grass native to arid and semi-arid regions of northern China, Mongolia, and Siberia. It thrives in dry grasslands, sandy soils, and slopes, forming dense tufts with rough, rolled leaves and robust spike-like inflorescences. Valued primarily as a drought-tolerant forage crop, it provides early spring grazing for livestock due to its high nutritional content and adaptability to harsh environments. Additionally, its extensive root system aids in soil stabilization and desertification control. Traditional Chinese medicine utilizes its roots and aerial parts for treating respiratory ailments, hemorrhages, and urinary disorders, attributed to its rich phytochemical profile, including amino acids, flavonoids, and plant-derived salts.'
);

-- 14. Aegilops triuncialis
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Aegilops triuncialis',
    'barbed goatgrass, triuncialis wheat',
    'Aegilops',
    '*Aegilops triuncialis* L., commonly known as barbed goatgrass or simply triuncialis wheat, is an allopolyploid grass species within the Poaceae family. Native to arid and semi-arid regions of Southwest Asia and the Mediterranean Basin, it thrives in disturbed habitats, rocky slopes, and grasslands with poor soil fertility, demonstrating remarkable adaptability to drought-prone and nutrient-deficient environments. As a tetraploid species (2n = 4x = 28), it originated through hybridization between diploid progenitors *Aegilops umbellulata* (UU genome) and *Aegilops caudata* (CC genome), forming a stable UUCC genome structure. Genomic studies reveal that *Ae. triuncialis* has undergone at least two independent polyploidization events, as evidenced by sequence variations in nuclear loci (e.g., G43) and chloroplast DNA hotspots, highlighting its evolutionary complexity and minimal post-polyploidization genomic modifications.'
);

-- 15. Leymus secalinus
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Leymus secalinus',
    'Lao Pi Jian, Bin Cao',
    'Leymus',
    '*Leymus secalinus* (Georgi) Tzvel., commonly known as "Lao Pi Jian" or "Bin Cao" in Chinese, is a perennial rhizomatous grass species within the Poaceae family. Native to arid and semi-arid regions of northern China, including Sichuan, Gansu, Qinghai, and Inner Mongolia, it thrives in diverse habitats such as sandy soils, plains, mountainous grasslands, and oasis margins, demonstrating broad ecological adaptability to nutrient-poor and drought-prone environments. Its foliage, though moderately nutritious, is grazed by livestock during early growth stages, while its roots and aerial parts are traditionally used in Chinese medicine to treat respiratory ailments, hemorrhages, and urinary disorders due to their phytochemical richness, including flavonoids and amino acids. However, its invasive tendencies in agricultural fields, particularly in regions like Qinghai.'
);

-- 16. Triticale
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Triticale',
    'Triticale',
    'Triticum×Secale',
    '*Triticale* (×*Triticosecale* Wittmack) is a human-made hybrid species derived from interspecific crosses between wheat (*Triticum* spp.) and rye (*Secale cereale* L.), followed by chromosome doubling to stabilize its genome. This allopolyploid combines the high yield and grain quality of wheat with the environmental resilience of rye, resulting in octoploid (8x) or hexaploid (6x) varieties widely cultivated for grain and forage production. Primarily distributed in high-altitude and temperate regions globally, including southwestern China (e.g., Sichuan, Yunnan), northern Europe (Germany, Poland), Australia, and North America, it thrives in marginal environments such as cold, arid, and nutrient-poor soils where conventional crops underperform. As a nutrient-rich feed for livestock (e.g., cattle, sheep) due to its high protein (15-20%), lysine, and digestible fiber content, and as a grain for human consumption in bread, cereals, and bioethanol production.'
);

-- 17. Phalaris arundinacea
INSERT INTO genomics_species (scientific_name, common_name, genus, description)
VALUES (
    'Phalaris arundinacea',
    'reed canary grass, gardener''s-garters',
    'Phalaris',
    '*Phalaris arundinacea* L., commonly known as reed canary grass or gardener''s-garters, is a perennial rhizomatous grass species within the Poaceae family. Native to temperate regions of Europe, Asia, and North America, it has naturalized globally in diverse habitats, including wetlands, riverbanks, moist meadows, and disturbed soils with low fertility or high salinity, demonstrating remarkable adaptability to both waterlogged and drought-prone environments. Its high biomass yield and nutritional value make it suitable for livestock feed, particularly in early growth stages. Additionally, its robust root system contributes to soil stabilization and erosion control, particularly in degraded or saline-affected ecosystems. Genomic research on *P. arundinacea* remains limited compared to major crops, but advancements include the assembly of a reference transcriptome to study salt stress responses.'
);

-- 插入accession数据 - 使用学名的正确格式
-- 1. Elymus nutans - Accession 1
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url,
    genomic_sequence, cds_sequence, gff3_annotation, peptide_sequence
) VALUES (
    1, 'Elymus nutans Griseb. cv. Aba', 
    'StStHHYY', 
    '9.46Gb', 
    '21', 
    '', 
    '', 
    'assets/img/Elymus_nutans.png',
    'Elymus_nutans_genome.fasta',
    'Elymus_nutans_cds.fasta',
    'Elymus_nutans_annotation.gff3',
    'Elymus_nutans_peptide.fasta'
);

-- 1. Elymus nutans - Accession 2
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    1, 'Elymus nutans wild accession FS20130', 
    'StStHHYY', 
    '10.47Gb', 
    '21', 
    '', 
    '', 
    'assets/img/Elymus_nutans_wild.png'
);

-- 2. Elymus sibiricus
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    2, 'Elymus sibiricus L. cv. Chuancao No.2', 
    'StStHH', 
    '6.74Gb', 
    '14', 
    '', 
    '', 
    'assets/img/Elymus_sibiricus.png'
);

-- 3. Pseudoroegneria libanotica
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    3, 'Pseudoroegneria libanotica PI 228,392', 
    'StSt', 
    '6.74Gb', 
    '7', 
    '46369', 
    '', 
    'assets/img/Pseudoroegneria_libanotica.png'
);

-- 4. Leymus chinensis
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    4, 'Leymus chinensis Lc6-5', 
    'NsNsXmXm', 
    '8Gb', 
    '14', 
    '84641', 
    '', 
    'assets/img/Leymus_chinensis.png'
);

-- 5. Aegilops tauschii
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    5, 'Aegilops tauschii T093', 
    'DD', 
    '4.12Gb', 
    '7', 
    '62389', 
    '693594', 
    'assets/img/Aegilops_tauschii.png'
);

-- 6. Hordeum vulgare
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    6, 'Hordeum vulgare cv. Morex', 
    'HH', 
    '5.1Gb', 
    '7', 
    '', 
    '', 
    'assets/img/Hordeum_vulgare.png'
);

-- 7. Hordeum vulgare var. nudum
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    7, 'Hordeum vulgare L. var. nudum Lasa Goumang', 
    'HH', 
    '4.5Gb', 
    '7', 
    '46787', 
    '', 
    'assets/img/Hordeum_vulgare_var_nudum.png'
);

-- 8. Avena sativa
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    8, 'Avena sativa cv. Marvellous', 
    'AACCDD', 
    '11Gb', 
    '21', 
    '', 
    '', 
    'assets/img/Avena_sativa.png'
);

-- 9. Thinopyrum elongatum
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    9, 'Thinopyrum elongatum, accession 401007', 
    'EEEEEEE^St^E^St^E^St^E^St^', 
    '', 
    '35+2', 
    '', 
    '', 
    'assets/img/Thinopyrum_elongatum.png'
);

-- 10. Thinopyrum intermedium
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    10, 'Thinopyrum intermedium', 
    'StStJJJsJs', 
    '12~14Gb', 
    '21', 
    '', 
    '', 
    'assets/img/Thinopyrum_intermedium.png'
);

-- 11. Aegilops ventricosa
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url,
    genomic_sequence, cds_sequence, gff3_annotation, peptide_sequence
) VALUES (
    11, 'Aegilops ventricosa', 
    'D^v^D^v^N^v^N^v^', 
    '8.67Gb', 
    '14', 
    '69000', 
    '', 
    'assets/img/Aegilops_ventricosa.png',
    '', '', '', ''
);

-- 12. Aegilops comosa
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url,
    genomic_sequence, cds_sequence, gff3_annotation, peptide_sequence
) VALUES (
    12, 'Aegilops comosa', 
    'MM', 
    '4.47Gb', 
    '7', 
    '39057', 
    '', 
    'assets/img/Aegilops_comosa.png',
    'Aegilops_comosa_genome.fasta',
    'Aegilops_comosa_cds.fasta',
    'Aegilops_comosa_annotation.gff3',
    'Aegilops_comosa_peptide.fasta'
);

-- 13. Agropyron cristatum
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    13, 'Agropyron cristatum', 
    '', 
    '', 
    '7,14,24', 
    '', 
    '', 
    'assets/img/Agropyron_cristatum.png'
);

-- 14. Aegilops triuncialis
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    14, 'Aegilops triuncialis', 
    'UUCC', 
    '', 
    '14', 
    '', 
    '', 
    'assets/img/Aegilops_triuncialis.png'
);

-- 15. Leymus secalinus
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    15, 'Leymus secalinus', 
    'NsXm', 
    '', 
    '14,24', 
    '', 
    '', 
    'assets/img/Leymus_secalinus.png'
);

-- 16. Triticale
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    16, 'Triticale', 
    '', 
    '', 
    '', 
    '', 
    '', 
    'assets/img/Triticale.png'
);

-- 17. Phalaris arundinacea
INSERT INTO species_accessions (
    species_id, accession_name, genome_type, genome_size, 
    chromosome_number, gene_number, cds_number, image_url
) VALUES (
    17, 'Phalaris arundinacea', 
    '', 
    '', 
    '7,14', 
    '', 
    '', 
    'assets/img/Phalaris_arundinacea.png'
);

-- 插入参考文献数据 - 使用星号标记学名
-- 1. Elymus nutans - Accession 1
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    1, 1, '', '', '', '', '', 'our data, unpublished', 'our data, unpublished'
);

-- 1. Elymus nutans - Accession 2
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    1, 2, 'Xiong, Y., Yuan, S., Xiong, Y. et al.', 
    'Analysis of allohexaploid wheatgrass genome reveals its Y haplome origin in Triticeae and high-altitude adaptation', 
    'Nature Communications', 
    '2025', 
    '10.1038/s41467-025-58341-0', 
    'https://www.nature.com/articles/s41467-025-58341-0',
    'Xiong, Y., Yuan, S., Xiong, Y. et al. Analysis of allohexaploid wheatgrass genome reveals its Y haplome origin in Triticeae and high-altitude adaptation. Nat Commun 16, 3104 (2025). https://doi.org/10.1038/s41467-025-58341-0'
);

-- 2. Elymus sibiricus
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    2, 3, '', '', '', '', '', 'our data, unpublished', 'our data, unpublished'
);

-- 3. Pseudoroegneria libanotica
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    3, 4, 'Zhai, X., Wu, D., Chen, C. et al.', 
    'A chromosome level genome assembly of *Pseudoroegneria Libanotica* reveals a key *Kcs* gene involves in the cuticular wax elongation for drought resistance', 
    'BMC Genomics', 
    '2024', 
    '10.1186/s12864-024-10140-5', 
    'https://doi.org/10.1186/s12864-024-10140-5',
    'Zhai, X., Wu, D., Chen, C. et al. A chromosome level genome assembly of *Pseudoroegneria Libanotica* reveals a key *Kcs* gene involves in the cuticular wax elongation for drought resistance. BMC Genomics 25, 253 (2024). https://doi.org/10.1186/s12864-024-10140-5'
);

-- 4. Leymus chinensis
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    4, 5, 'T. Li, S. Tang, W. Li, S. Zhang, J. Wang, D. Pan, Z. Lin, X. Ma, Y. Chang, B. Liu, J. Sun, X. Wang, M. Zhao, C. You, H. Luo, M. Wang, X. Ye, J. Zhai, Z. Shen, & X. Cao', 
    'Genome evolution and initial breeding of the Triticeae grass *Leymus chinensis* dominating the Eurasian Steppe', 
    'Proceedings of the National Academy of Sciences', 
    '2023', 
    '10.1073/pnas.2308984120', 
    'https://doi.org/10.1073/pnas.2308984120',
    'T. Li, S. Tang, W. Li, S. Zhang, J. Wang, D. Pan, Z. Lin, X. Ma, Y. Chang, B. Liu, J. Sun, X. Wang, M. Zhao, C. You, H. Luo, M. Wang, X. Ye, J. Zhai, Z. Shen, & X. Cao, Genome evolution and initial breeding of the Triticeae grass *Leymus chinensis* dominating the Eurasian Steppe, Proc. Natl. Acad. Sci. U.S.A. 120 (44) e2308984120. https://doi.org/10.1073/pnas.2308984120'
);

-- 5. Aegilops tauschii
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    5, 6, 'Zhao, G., Zou, C., Li, K. et al.', 
    'The *Aegilops tauschii* genome reveals multiple impacts of transposons', 
    'Nature Plants', 
    '2017', 
    '10.1038/s41477-017-0067-8', 
    'https://doi.org/10.1038/s41477-017-0067-8',
    'Zhao, G., Zou, C., Li, K. et al. The *Aegilops tauschii* genome reveals multiple impacts of transposons. Nature Plants 3, 946-955 (2017). https://doi.org/10.1038/s41477-017-0067-8'
);

-- 6. Hordeum vulgare
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    6, 7, 'The International Barley Genome Sequencing Consortium', 
    'A physical, genetic and functional sequence assembly of the barley genome', 
    'Nature', 
    '2012', 
    '10.1038/nature11543', 
    'https://doi.org/10.1038/nature11543',
    'The International Barley Genome Sequencing Consortium. A physical, genetic and functional sequence assembly of the barley genome. Nature 491, 711-716 (2012). https://doi.org/10.1038/nature11543'
);

-- 7. Hordeum vulgare var. nudum
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    7, 8, 'Zeng, X., Xu, T., Ling, Z. et al.', 
    'An improved high-quality genome assembly and annotation of Tibetan hulless barley', 
    'Scientific Data', 
    '2020', 
    '10.1038/s41597-020-0480-0', 
    'https://doi.org/10.1038/s41597-020-0480-0',
    'Zeng, X., Xu, T., Ling, Z. et al. An improved high-quality genome assembly and annotation of Tibetan hulless barley. Sci Data 7, 139 (2020). https://doi.org/10.1038/s41597-020-0480-0'
);

-- 8. Avena sativa
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    8, 9, 'Li, Wei et al.', 
    'A gap-free complete genome assembly of oat and OatOmics, a multi-omics database', 
    'Molecular Plant', 
    '2025', 
    '', 
    'https://www.cell.com/molecular-plant/fulltext/S1674-2052(25)00027-9',
    'Li, Wei et al. A gap-free complete genome assembly of oat and OatOmics, a multi-omics database. Molecular Plant, Volume 18, Issue 2, 179-182 (2025).'
);

-- 9. Thinopyrum elongatum
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    9, 10, 'Baker, L., Grewal, S., Yang, Cy. et al.', 
    'Exploiting the genome of *Thinopyrum elongatum* to expand the gene pool of hexaploid wheat', 
    'Theoretical and Applied Genetics', 
    '2020', 
    '10.1007/s00122-020-03591-3', 
    'https://doi.org/10.1007/s00122-020-03591-3',
    'Baker, L., Grewal, S., Yang, Cy. et al. Exploiting the genome of *Thinopyrum elongatum* to expand the gene pool of hexaploid wheat. Theor Appl Genet 133, 2213-2226 (2020). https://doi.org/10.1007/s00122-020-03591-3'
);

-- 10. Thinopyrum intermedium
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    10, 11, 'Kantarski, T., Larson, S., Zhang, X. et al.', 
    'Development of the first consensus genetic map of intermediate wheatgrass (*Thinopyrum intermedium*) using genotyping-by-sequencing', 
    'Theoretical and Applied Genetics', 
    '2017', 
    '10.1007/s00122-016-2799-7', 
    'https://doi.org/10.1007/s00122-016-2799-7',
    'Kantarski, T., Larson, S., Zhang, X. et al. Development of the first consensus genetic map of intermediate wheatgrass (*Thinopyrum intermedium*) using genotyping-by-sequencing. Theor Appl Genet 130, 137-150 (2017). https://doi.org/10.1007/s00122-016-2799-7'
);

-- 11. Aegilops ventricosa
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    11, 12, 'Liu Z, Yang F, Wan H, et al.', 
    'Genome architecture of the allotetraploid wild grass *Aegilops ventricosa* reveals its evolutionary history and contributions to wheat improvement', 
    'Plant Communications', 
    '2025', 
    '10.1016/j.xplc.2024.101131', 
    'https://doi.org/10.1016/j.xplc.2024.101131',
    'Liu Z, Yang F, Wan H, et al. Genome architecture of the allotetraploid wild grass *Aegilops ventricosa* reveals its evolutionary history and contributions to wheat improvement. Plant Commun. 2025;6(1):101131. https://doi.org/10.1016/j.xplc.2024.101131'
);

-- 12. Aegilops comosa
INSERT INTO species_references (
    species_id, accession_id, authors, title, journal, year, doi, link, citation_text
) VALUES (
    12, 13, 'Li, H., Rehman, S.u., Song, R. et al.', 
    'Chromosome-scale assembly and annotation of the wild wheat relative *Aegilops comosa*', 
    'Scientific Data', 
    '2024', 
    '10.1038/s41597-024-04346-1', 
    'https://doi.org/10.1038/s41597-024-04346-1',
    'Li, H., Rehman, S.u., Song, R. et al. Chromosome-scale assembly and annotation of the wild wheat relative *Aegilops comosa*. Sci Data 11, 1454 (2024). https://doi.org/10.1038/s41597-024-04346-1'
);

-- 添加其他物种的引用，但没有具体内容
INSERT INTO species_references (species_id, accession_id, citation_text) VALUES (13, 14, '');
INSERT INTO species_references (species_id, accession_id, citation_text) VALUES (14, 15, '');
INSERT INTO species_references (species_id, accession_id, citation_text) VALUES (15, 16, '');
INSERT INTO species_references (species_id, accession_id, citation_text) VALUES (16, 17, '');
INSERT INTO species_references (species_id, accession_id, citation_text) VALUES (17, 18, '');

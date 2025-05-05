<?php
// This assumes you have a config.php file in the Admin directory
// If not, you'll need to create one or modify this part accordingly
require 'Admin/config.php';

// 从数据库获取所有物种信息用于下拉菜单
$query = "SELECT id, scientific_name FROM genomics_species ORDER BY scientific_name";
$result = $conn->query($query);

$species = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $species[] = $row;
    }
}

// 获取当前选中物种的信息以及其所有资源库和参考文献
$selectedSpecies = null;
$speciesAccessions = [];
$speciesReferences = [];

if (isset($_GET['species_id'])) {
    $species_id = $conn->real_escape_string($_GET['species_id']);
    
    // 获取物种基本信息
    $query = "SELECT * FROM genomics_species WHERE id = '$species_id'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $selectedSpecies = $result->fetch_assoc();
        
        // 获取该物种的所有资源库(accessions)
        $query = "SELECT * FROM species_accessions WHERE species_id = '$species_id' AND status = 'approved'";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $speciesAccessions[] = $row;
            }
        }
        
        // 获取该物种的所有参考文献
        $query = "SELECT r.*, a.accession_name 
                 FROM species_references r 
                 LEFT JOIN species_accessions a ON r.accession_id = a.id 
                 WHERE r.species_id = '$species_id'";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $speciesReferences[] = $row;
            }
        }
    }
}

/**
 * 格式化描述文本，将Markdown风格的星号标记(*text*)转换为HTML的<em>标签
 */
function formatDescription($text) {
    // 匹配Markdown风格的星号标记，将其转换为HTML的<em>标签
    $pattern = '/\*(.*?)\*/';
    $replacement = '<em>$1</em>';
    
    // 处理描述文本中的物种名称和其他需要使用斜体的内容
    $text = preg_replace($pattern, $replacement, $text);
    
    return $text;
}

/**
 * 处理科学名称的显示，精确区分斜体和正体部分
 * 例如: "Elymus nutans Griseb." => "<em>Elymus nutans</em> Griseb."
 */
function formatScientificName($name) {
    // 分离种和其他部分（如作者、品种等）
    $formattedName = '';
    
    // 匹配模式: 属名和种名应该是斜体，后面的作者、变种等是正体
    // 例如: "Hordeum vulgare L. var. nudum Lasa Goumang"
    // 变成: "<em>Hordeum vulgare</em> L. var. <em>nudum</em> Lasa Goumang"
    
    // 基本模式: 属名和种名是斜体
    if (preg_match('/^(\S+)\s+(\S+)(.*)$/', $name, $matches)) {
        // 属名和种名
        $genus = $matches[1];
        $species = $matches[2];
        $rest = $matches[3] ?? '';
        
        // 检查是否包含变种信息
        if (stripos($rest, ' var. ') !== false) {
            $parts = explode(' var. ', $rest, 2);
            $authors = $parts[0] ?? '';
            
            if (isset($parts[1]) && !empty($parts[1])) {
                $varietyParts = preg_split('/\s+/', trim($parts[1]), 2);
                $variety = $varietyParts[0] ?? '';
                $varietyRest = $varietyParts[1] ?? '';
                
                $formattedName = "<em>{$genus} {$species}</em>{$authors} var. <em>{$variety}</em> {$varietyRest}";
            } else {
                $formattedName = "<em>{$genus} {$species}</em>{$authors} var.";
            }
        } else {
            // 常规处理：属名和种名用斜体，作者用正体
            $formattedName = "<em>{$genus} {$species}</em>{$rest}";
        }
    } else {
        // 只有一个词的情况（可能只有属名）
        $formattedName = "<em>{$name}</em>";
    }
    
    return $formattedName;
}

/**
 * 处理属名称（只有属名时用斜体）
 */
function formatGenus($genus) {
    return "<em>{$genus}</em>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Omics-Genomics - QTP-GMD</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Swiper -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

  <!-- AOS -->
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

  <!-- Glightbox -->
  <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
  
  <!-- bootstrap-icons-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .species-image-card {
      border: 1px solid #e9ecef;
      transition: transform 0.3s ease;
      margin-left: 20px;
    }

    .species-image-card:hover {
      transform: translateY(-3px);
    }
    
    .reference-section {
      padding: 2rem;
      background-color: #f8f9fa;
      border-radius: 0.5rem;
      margin-top: 3rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    
    /* 参考文献样式 */
    .reference-text {
      font-style: normal; /* 参考文献本身不使用斜体 */
      color: #495057;
      margin-bottom: 1rem;
      border-left: 4px solid #3fbbc0;
      padding: 0.5rem 1rem;
      background-color: white;
      border-radius: 0.25rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.05);
    }
    
    /* 参考文献中的学名使用斜体 */
    .reference-text em {
      font-style: italic;
      font-family: 'Times New Roman', serif;
    }
    
    /* 参考文献链接样式 */
    .reference-link {
      padding-top: 0.5rem;
      padding-bottom: 1.5rem;
      text-align: center;
    }
    
    .reference-link a {
      transition: all 0.2s ease;
      padding: 0.5rem 1rem;
      background-color: #f8f9fa;
      border-radius: 0.25rem;
      border: 1px solid #dee2e6;
      color: #0d6efd;
      text-decoration: none;
      display: inline-block;
    }
    
    .reference-link a:hover {
      background-color: #e9ecef;
      box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
    }
    
    .description-content {
      border-left: 4px solid #3fbbc0;
      transition: all 0.3s ease;
    }

    .description-content:hover {
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    .description-content h5 {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
    }

    .description-content p {
      font-size: 1.05rem;
      line-height: 1.8;
      color: #495057;
    }
    
    /* 描述中的学名使用斜体 */
    .description-content em {
      font-style: italic;
      font-family: 'Times New Roman', serif;
      color: #2c3e50;
    }
    
    /* 强制使用斜体样式 - 只用于select的科学名称 */
    #speciesSelector,
    #speciesSelector option {
      font-style: italic !important;
      font-family: 'Times New Roman', serif !important;
    }

    /* 覆盖Bootstrap默认样式 */
    .form-select {
      background-image: none; /* 移除默认箭头 */
      padding-right: 12px; /* 调整内边距 */
    }
    
    /* 不再全局设置斜体，只在需要时使用 */
    .scientific-name {
      font-size: 1.2rem;
    }
    
    /* 学名自动应用斜体 */
    .scientific-name em {
      font-style: italic;
      font-family: 'Times New Roman', serif;
    }
    
    /* 作者名和其他文本保持正体 */
    .scientific-name span {
      font-style: normal;
      font-family: sans-serif;
    }
    
    /* 添加自定义下拉箭头 */
    #speciesSelector {
      background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 0.75rem center/16px 12px;
    }

    /* Accession部分样式 */
    .accession-section {
      margin-top: 3rem;
      padding-top: 2rem;
      border-top: 1px solid #dee2e6;
    }
    
    .accession-header {
      background-color: #f8f9fa;
      border-left: 4px solid #3fbbc0;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }
    
    /* 增大Accession标题字体 */
    .accession-title {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0;
      font-size: 1.3rem; /* 增大字体大小 */
    }
    
    /* 修复下载链接样式 */
    .download-cell {
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
      padding: 10px;
    }

    .download-link {
      display: block;
      width: 100%;
      height: 100%;
      text-decoration: none !important;
      color: inherit !important;
      padding: 10px 30px 10px 10px;
      position: relative;
      z-index: 1;
    }
    
    .download-link::after {
      content: "⬇️";
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 2;
    }

    .download-cell:hover {
      background-color: #f8f9fa;
      box-shadow: 0 0 8px rgba(63, 187, 192, 0.2);
    }
    
    /* 处理小屏幕响应式布局 */
    @media (max-width: 768px) {
      .species-image-card {
        margin-top: 1.5rem;
        max-width: 80%;
        margin-left: auto;
        margin-right: auto;
      }
      #speciesSelector {
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body class="starter-page-page">

  <!-- HEADER START - Inline instead of including header.php -->
  <header id="header" class="header sticky-top">
    <div class="topbar d-flex align-items-center">
      <div class="container d-flex justify-content-center justify-content-md-between">
        
      </div>
    </div>

    <div class="branding d-flex align-items-center">
      <div class="container position-relative d-flex align-items-center justify-content-end">
        <a href="index.html" class="logo d-flex align-items-center me-auto">
        <img src="./assets/img/logo.png" alt="">
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php">Home</a></li>
            
            <!-- Omics dropdown menu -->
            <li class="dropdown"><a href="genomics.php" class="active"><span>Omics</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="genomics.php" class="active">Genomics</a></li>
                <li><a href="transcriptomics.html">Transcriptomics</a></li>
                <li><a href="metabolomics.html">Metabolomics</a></li>
                <li><a href="Proteomics.html">Proteomics</a></li>
                <li><a href="Microbiomics.html">Microbiomics</a></li>
                <li><a href="Epigenomic.html">Epigenomic</a></li>
              </ul>
            </li>

            <li><a href="Phenotype.html">Phenotype</a></li>
            <li><a href="Germplasm.html">Germplasm</a></li>
        
            <!-- Tools dropdown menu -->
            <li class="dropdown"><a href="tools.html"><span>Tools</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="tools.html#jbrowse">Jbrowse</a></li>
                <li><a href="tools.html#blast">BLAST</a></li>
                <li><a href="tools.html#primer3">Primer3 Design</a></li>
                <li><a href="tools.html#orthovenn">OrthoVenn</a></li>
                <li><a href="tools.html#enrichment">Enrichment</a></li>
                <li><a href="tools.html#crispr">CRISPR-Predictor</a></li>
                <li><a href="tools.html#gene-search">Gene Search</a></li>
              </ul>
            </li>
        
            <li><a href="contact.html">Contact</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        <a class="cta-btn" href="login.html">Sign in</a>
      </div>
    </div>
  </header>
  <!-- HEADER END -->

  <main class="main">
    <!-- Genomics Section -->
    <section id="genomics" class="genomics-section section">
      <div class="container" data-aos="fade-up">

        <div class="section-header mb-5">
          <h2>Omics</h2>
          <h3>1.1 Genomics</h3>
        </div>

        <!-- Species Selector -->
        <div class="row mb-4">
          <div class="col-lg-6">
            <select class="form-select" 
                    id="speciesSelector" 
                    onchange="window.location.href='genomics.php?species_id='+this.value"
                    style="font-style: italic; font-family: 'Times New Roman', serif;">
              <option value="">Select a species</option>
              <?php foreach ($species as $specie): ?>
                <option value="<?= $specie['id'] ?>" 
                  <?= (isset($selectedSpecies) && $selectedSpecies['id'] == $specie['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($specie['scientific_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (isset($_SESSION['user_id'])): ?>
          <div class="col-lg-6 text-end">
            <a href="submit_species.php" class="btn btn-primary">Submit New Species</a>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($selectedSpecies): ?>
        <!-- 动态内容区域 -->
        <div id="speciesInfo" class="species-info-container">
          
          <!-- 物种描述 - 使用函数处理学名斜体 -->
          <div class="species-description mb-4" id="speciesDescription">
            <div class="description-content bg-light p-4 rounded-3">
              <p class="mb-0">
                <?= formatDescription($selectedSpecies['description']) ?>
              </p>
            </div>
          </div>
          
          <!-- 循环显示每个资源库 -->
          <?php foreach ($speciesAccessions as $index => $accession): ?>
          <div class="accession-section" id="accession-<?= $accession['id'] ?>">
            <div class="accession-header mb-4">
              <h4 class="accession-title">
                Accession <?= $index + 1 ?>: <span class="scientific-name"><?= formatScientificName($accession['accession_name']) ?></span>
              </h4>
            </div>
            
            <!-- 该资源库的物种信息 -->
            <h4>Species Information</h4>
            <div class="row align-items-start">
              <!-- 左侧信息栏（8列宽度） -->
              <div class="col-lg-8 col-md-7">
                <dl class="row">
                  <dt class="col-sm-4">Scientific Name</dt>
                  <dd class="col-sm-8 scientific-name">
                    <?= formatScientificName($accession['accession_name']) ?>
                  </dd>
          
                  <dt class="col-sm-4">Common name</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedSpecies['common_name'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genus</dt>
                  <dd class="col-sm-8 scientific-name">
                    <?= formatGenus($selectedSpecies['genus'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genome Type</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($accession['genome_type'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genome Size</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($accession['genome_size'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Chromosome Number</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($accession['chromosome_number'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Gene Number</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($accession['gene_number'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">CDS Number</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($accession['cds_number'] ?? '') ?>
                  </dd>
                </dl>
              </div>
          
              <!-- 右侧图片栏（4列宽度） -->
              <div class="col-lg-4 col-md-5">
                <div class="species-image-card bg-white p-3 rounded-3 shadow-sm">
                  <img
                       src="<?= htmlspecialchars($accession['image_url'] ?: 'assets/img/placeholder.png') ?>" 
                       class="img-fluid rounded" 
                       alt="Species Image"
                       style="max-height: 300px; object-fit: cover;"
                       loading="lazy">
                  <div class="image-caption text-center text-muted mt-2 small scientific-name">
                    <?= formatScientificName($accession['accession_name']) ?>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- 该资源库的基因组序列 - 修复了下载链接 -->
            <?php if (!empty($accession['genomic_sequence']) || !empty($accession['cds_sequence']) || 
                    !empty($accession['gff3_annotation']) || !empty($accession['peptide_sequence'])): ?>
            <div class="genomic-data mb-5 mt-4">
              <h4>Genomic Sequence</h4>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Genomic Sequence</th>
                      <th>CDS Sequence</th>
                      <th>GFF3 Annotation</th>
                      <th>Peptide Sequence</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="download-cell">
                        <a href="files/genomic/<?= htmlspecialchars($accession['genomic_sequence'] ?? '') ?>" 
                           download
                           class="download-link">
                          <?= htmlspecialchars($accession['genomic_sequence'] ?? 'Download') ?>
                        </a>
                      </td>
                      <td class="download-cell">
                        <a href="files/cds/<?= htmlspecialchars($accession['cds_sequence'] ?? '') ?>" 
                           download
                           class="download-link">
                          <?= htmlspecialchars($accession['cds_sequence'] ?? 'Download') ?>
                        </a>
                      </td>
                      <td class="download-cell">
                        <a href="files/annotation/<?= htmlspecialchars($accession['gff3_annotation'] ?? '') ?>" 
                           download
                           class="download-link">
                          <?= htmlspecialchars($accession['gff3_annotation'] ?? 'Download') ?>
                        </a>
                      </td>
                      <td class="download-cell">
                        <a href="files/peptide/<?= htmlspecialchars($accession['peptide_sequence'] ?? '') ?>" 
                           download
                           class="download-link">
                          <?= htmlspecialchars($accession['peptide_sequence'] ?? 'Download') ?>
                        </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <?php endif; ?>
            
            <!-- 该资源库的参考文献和链接（分开） -->
            <div class="reference-section">
              <h4 class="mb-4">Reference</h4>
              <?php 
              $found = false;
              foreach ($speciesReferences as $reference): 
                if ($reference['accession_id'] == $accession['id']):
                  $found = true;
              ?>
                <!-- 参考文献文本 - 处理其中可能包含的学名 -->
                <?php if (!empty($reference['citation_text'])): ?>
                <div class="reference-text">
                  <?= formatDescription($reference['citation_text']) ?>
                </div>
                <?php else: ?>
                <div class="reference-text">
                  No reference information available.
                </div>
                <?php endif; ?>
                
                <!-- 参考文献链接 - 独立的蓝色链接按钮 -->
                <?php if (!empty($reference['link'])): ?>
                <div class="reference-link">
                  <a href="<?= htmlspecialchars($reference['link']) ?>" 
                     target="_blank">
                    <?= htmlspecialchars($reference['link']) ?>
                  </a>
                </div>
                <?php endif; ?>
              <?php 
                endif;
              endforeach; 
              
              if (!$found):
              ?>
                <div class="reference-text">
                  No reference information available.
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
          
        </div>
        <?php else: ?>
        <!-- 当没有选择物种时显示默认信息 -->
        <div class="alert alert-info">
          Please select a species from the dropdown menu to view its genomic information.
        </div>
        <?php endif; ?>

      </div>
    </section><!-- End Genomics Section -->
  </main>

  <!-- FOOTER START - Inline instead of including footer.php -->
  <footer id="footer" class="footer light-background">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">QTP-GMD</span>
          </a>
          <div class="footer-contact pt-3">
            <p>No. 59, Middle Section of Qinglong Avenue, Fucheng District, Mianyang City, Sichuan Province</p>
            <p class="mt-3"><strong>Tel:</strong> <span> 0816-6089528</span></p>
            <p><strong>Email:</strong> <span>wangting@swust.edu.cn</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-wechat"></i></a>
            <a href=""><i class="bi bi-envelope"></i></a>
            <a href=""><i class="bi bi-globe"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-md-3 footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About Project</a></li>
            <li><a href="#">Database Access</a></li>
            <li><a href="#">Research Papers</a></li>
            <li><a href="#">Contact Us</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-3 footer-links">
          <h4>Database Resources</h4>
          <ul>
            <li><a href="#">Wheat Species Data</a></li>
            <li><a href="#">Forage Grass Collection</a></li>
            <li><a href="#">Growth Parameters</a></li>
            <li><a href="#">Environmental Data</a></li>
            <li><a href="#">Research Tools</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Partners</h4>
          <ul>
            <li><a href="#">Research Institutes</a></li>
            <li><a href="#">Universities</a></li>
            <li><a href="#">Agricultural Stations</a></li>
            <li><a href="#">Funding Organizations</a></li>
            <li><a href="#">Collaborators</a></li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">the QTP Grasses Database</strong> <span>All Rights Reserved</span></p>
      <!-- 修改后的备案信息 -->
      <div class="beian mt-2">
        <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow" style="color: color-mix(in srgb, var(--default-color), transparent 30%); font-size: 13px;">
          蜀ICP备2025136730号-1
        </a>
      </div>
    </div>

  </footer>
  <!-- FOOTER END -->

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    // 初始化页面加载后的交互元素
    document.addEventListener('DOMContentLoaded', function() {
      // 初始化AOS动画
      AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
      
      // 初始化提示工具
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
    
    // 平滑滚动到accession部分
    function scrollToAccession(accessionId) {
      const element = document.getElementById('accession-' + accessionId);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  </script>
</body>

</html>

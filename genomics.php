<?php
// This assumes you have a config.php file in the Admin directory
// If not, you'll need to create one or modify this part accordingly
require 'Admin/config.php';

// 首先获取所有已批准的物种（不包含品系）
$query = "SELECT DISTINCT species_name FROM genomics_species WHERE status = 'approved' ORDER BY species_name";
$result = $conn->query($query);

$speciesList = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $speciesList[] = $row;
    }
}

// 获取当前选中的物种信息
$selectedSpeciesName = '';
$accessions = [];

if (isset($_GET['species'])) {
    $selectedSpeciesName = $conn->real_escape_string($_GET['species']);
    
    // 获取该物种的所有品系
    $query = "SELECT * FROM genomics_species WHERE species_name = '$selectedSpeciesName' AND status = 'approved' ORDER BY scientific_name";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $accessions[] = $row;
        }
    }
}

// 选中的品系
$selectedAccession = null;
if (!empty($accessions)) {
    // 默认显示第一个品系，或者按用户选择显示
    if (isset($_GET['accession_id']) && is_numeric($_GET['accession_id'])) {
        $accession_id = intval($_GET['accession_id']);
        foreach ($accessions as $acc) {
            if ($acc['id'] == $accession_id) {
                $selectedAccession = $acc;
                break;
            }
        }
    }
    
    // 如果没有找到指定的品系，使用第一个
    if ($selectedAccession === null) {
        $selectedAccession = $accessions[0];
    }
}

// 获取物种描述信息
$speciesDescription = '';
if ($selectedSpeciesName && !empty($accessions)) {
    $speciesDescription = $accessions[0]['description'];
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
      border-top: 1px solid #dee2e6;
      padding-top: 2rem;
      margin-top: 3rem;
    }
    
    .reference-section p {
      font-style: italic;
      color: #555;
      line-height: 1.6;
    }
    
    .description-content {
      border-left: 4px solid #3fbbc0;
      transition: all 0.3s ease;
    }

    .description-content:hover {
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

    /* 科学名斜体样式 */
    .scientific-name {
      font-style: italic !important;
      font-family: 'Times New Roman', serif !important;
    }

    /* 物种选择器样式 */
    #speciesSelector {
      background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 0.75rem center/16px 12px;
    }
    
    /* 品系切换标签 */
    .accession-tabs {
      margin-top: 2rem;
      margin-bottom: 2rem;
    }
    
    .accession-tabs .nav-link {
      color: #495057;
      font-family: 'Times New Roman', serif;
      font-style: italic;
      border: 1px solid transparent;
      border-radius: 0.25rem;
      padding: 0.5rem 1rem;
      margin-right: 0.5rem;
    }
    
    .accession-tabs .nav-link.active {
      color: #0d6efd;
      border-color: #dee2e6 #dee2e6 #fff;
      background-color: #fff;
      font-weight: bold;
    }
    
    /* 下载按钮样式 */
    .download-cell {
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
      padding: 10px;
    }

    .download-link {
      display: inline-block;
      width: 100%;
      height: 100%;
      text-decoration: none !important;
      color: inherit !important;
      padding: inherit;
      margin: -10px;
    }

    .download-cell:hover {
      background-color: #f8f9fa;
      box-shadow: 0 0 8px rgba(63, 187, 192, 0.2);
    }

    .download-cell::after {
      content: "⬇️";
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      opacity: 1;
      transition: opacity 0.2s ease;
    }
    
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
      .accession-tabs .nav-link {
        padding: 0.4rem 0.7rem;
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
                    onchange="window.location.href='genomics.php?species='+this.value"
                    style="font-style: italic; font-family: 'Times New Roman', serif;">
              <option value="">Select a species</option>
              <?php foreach ($speciesList as $species): ?>
                <option value="<?= htmlspecialchars($species['species_name']) ?>" 
                  <?= ($selectedSpeciesName == $species['species_name']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($species['species_name']) ?>
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

        <?php if ($selectedSpeciesName && !empty($accessions)): ?>
        <!-- 物种描述部分 -->
        <div class="species-description mb-5" id="speciesDescription">
          <div class="description-content bg-light p-4 rounded-3">
            <p class="mb-0">
              <?= nl2br(htmlspecialchars($speciesDescription)) ?>
            </p>
          </div>
        </div>

        <!-- 品系选择标签 -->
        <?php if (count($accessions) > 1): ?>
        <div class="accession-tabs">
          <ul class="nav nav-tabs">
            <?php foreach ($accessions as $index => $accession): ?>
              <li class="nav-item">
                <a class="nav-link <?= ($selectedAccession['id'] == $accession['id']) ? 'active' : '' ?>" 
                   href="genomics.php?species=<?= urlencode($selectedSpeciesName) ?>&accession_id=<?= $accession['id'] ?>">
                  <?= htmlspecialchars($accession['scientific_name']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- 品系详细信息 -->
        <?php if ($selectedAccession): ?>
        <div id="speciesInfo" class="species-info-container">
          
          <!-- Species Information -->
          <div class="species-info mb-5">
            <h4>Species Information</h4>
            <div class="row align-items-start">
              <!-- Left information column (8 columns width) -->
              <div class="col-lg-8 col-md-7">
                <dl class="row">
                  <dt class="col-sm-4">Scientific Name</dt>
                  <dd class="col-sm-8" class="scientific-name">
                    <em><?= htmlspecialchars($selectedAccession['scientific_name']) ?></em>
                  </dd>
          
                  <dt class="col-sm-4">Common name</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedAccession['common_name'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genus</dt>
                  <dd class="col-sm-8" class="scientific-name">
                    <em><?= htmlspecialchars($selectedAccession['genus'] ?? '') ?></em>
                  </dd>
          
                  <dt class="col-sm-4">Genome Type</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedAccession['genome_type'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genome Size</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedAccession['genome_size'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Chromosome Number</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedAccession['chromosome_number'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Gene Number</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedAccession['gene_number'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">CDS Number</dt>
                  <dd class="col-sm-8">
                    <?= htmlspecialchars($selectedAccession['cds_number'] ?? '') ?>
                  </dd>
                </dl>
              </div>
          
              <!-- Right image column (4 columns width) -->
              <div class="col-lg-4 col-md-5">
                <div class="species-image-card bg-white p-3 rounded-3 shadow-sm">
                  <img id="speciesImage" 
                       src="<?= htmlspecialchars($selectedAccession['image_url'] ?: 'assets/img/placeholder.png') ?>" 
                       class="img-fluid rounded" 
                       alt="Species Image"
                       style="max-height: 300px; object-fit: cover;"
                       loading="lazy">
                  <div class="image-caption text-center text-muted mt-2 small" class="scientific-name">
                    <em><?= htmlspecialchars($selectedAccession['scientific_name']) ?></em>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Genomic Sequence -->
          <div class="genomic-data mb-5">
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
                    <td onclick="downloadFile('genomic', '<?= htmlspecialchars($selectedAccession['id']) ?>')" 
                        id="genomicSequence" 
                        class="download-cell"
                        data-original="<?= htmlspecialchars($selectedAccession['genomic_sequence'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedAccession['genomic_sequence'] ?? 'Download') ?>
                    </td>
                    <td onclick="downloadFile('cds', '<?= htmlspecialchars($selectedAccession['id']) ?>')" 
                        id="cdsSequence" 
                        class="download-cell"
                        data-original="<?= htmlspecialchars($selectedAccession['cds_sequence'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedAccession['cds_sequence'] ?? 'Download') ?>
                    </td>
                    <td class="download-cell">
                      <a href="files/annotation/<?= htmlspecialchars($selectedAccession['gff3_annotation'] ?? '') ?>" 
                        download
                        class="download-link"
                        id="gff3Annotation"
                        data-original="<?= htmlspecialchars($selectedAccession['gff3_annotation'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedAccession['gff3_annotation'] ?? 'Download') ?>
                      </a>
                    </td>
                    <td onclick="downloadFile('peptide', '<?= htmlspecialchars($selectedAccession['id']) ?>')" 
                        id="peptideSequence" 
                        class="download-cell"
                        data-original="<?= htmlspecialchars($selectedAccession['peptide_sequence'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedAccession['peptide_sequence'] ?? 'Download') ?>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Reference Section -->
          <?php if (!empty($selectedAccession['reference_link'])): ?>
          <div class="reference-section">
            <h4 class="text-center mb-3">Reference</h4>
            <div class="text-center">
              <p>
                <?php 
                  $reference = htmlspecialchars($selectedAccession['reference_link']);
                  echo nl2br($reference);
                ?>
              </p>
            </div>
          </div>
          <?php endif; ?>
          
        </div>
        <?php endif; ?>
        <?php else: ?>
        <!-- Display default message when no species is selected -->
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
    // File download functionality
    function downloadFile(type, speciesId) {
        // Show loading indicator
        const cell = document.getElementById(type + 'Sequence');
        cell.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        
        // Fetch file info from the server
        fetch(`get_file_info.php?species_id=${speciesId}&file_type=${type}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Construct download URL
                const downloadUrl = `files/${data.path}/${data.filename}`;
                
                // Create temporary download link
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = data.filename;
                link.style.display = 'none';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Restore original cell content
                const originalContent = cell.getAttribute('data-original') || data.filename;
                cell.innerHTML = originalContent;
                cell.setAttribute('data-original', originalContent);
            })
            .catch(error => {
                console.error('Download error:', error);
                alert('Error downloading file: ' + error.message);
                
                // Restore original cell content
                const originalContent = cell.getAttribute('data-original') || 'Download';
                cell.innerHTML = originalContent;
                cell.setAttribute('data-original', originalContent);
            });
    }
  </script>
</body>

</html>

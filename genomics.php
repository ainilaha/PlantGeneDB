<?php
// This assumes you have a config.php file in the Admin directory
// If not, you'll need to create one or modify this part accordingly
require 'Admin/config.php';

// Get all approved species
$query = "SELECT * FROM genomics_species WHERE status = 'approved' ORDER BY scientific_name";
$result = $conn->query($query);

$species = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $species[] = $row;
    }
}

// Get the currently selected species information
$selectedSpecies = null;
if (isset($_GET['species_id'])) {
    $species_id = $conn->real_escape_string($_GET['species_id']);
    $query = "SELECT * FROM genomics_species WHERE id = '$species_id' AND status = 'approved'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $selectedSpecies = $result->fetch_assoc();
    }
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

  <!-- Fonts -->
 <!-- <link href="https://fonts.googleapis.com" rel="preconnect">-->
<!--  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>-->
 <!-- <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
-->
  <!-- Vendor CSS Files -->
<!--  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">-->
<!--  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">-->
<!--  <link href="assets/vendor/aos/aos.css" rel="stylesheet">-->
<!--  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">-->
<!--  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">-->
<!--  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">-->

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
    .reference-link {
      border-top: 1px solid #dee2e6;
      padding-top: 2rem;
      margin-top: 3rem;
    }
    
    #referenceLink a {
      transition: color 0.3s ease;
      padding: 0.5rem 1rem;
      background-color: #f8f9fa;
      border-radius: 0.25rem;
    }
    
    #referenceLink a:hover {
      color: #0d6efd;
      text-decoration: none;
      background-color: #e9ecef;
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
    /* Force italic style */
    #speciesSelector,
    #speciesSelector option {
      font-style: italic !important;
      font-family: 'Times New Roman', serif !important;
    }

    /* Override Bootstrap default styles */
    .form-select {
      background-image: none; /* Remove default arrow */
      padding-right: 12px; /* Adjust padding */
    }
    #italic,#scientificName,#imageCaption,#genus{
      font-style: italic !important;
      font-family: 'Times New Roman', serif !important;
      font-size: large;
    }
    .species-description em {
      font-style: italic;
      font-family: 'Times New Roman', serif;
      color: #2c3e50;
    }
    /* Add custom dropdown arrow */
    #speciesSelector {
      background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 0.75rem center/16px 12px;
    }

    /* Add click feedback styles */
    /* 保持现有的 .download-cell 样式不变 */
    .download-cell {
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
      padding: 10px;
    }

    /* 专门针对下载链接的样式调整 */
    .download-link {
      display: inline-block; /* 改为 inline-block 而不是 block */
      width: 100%;
      height: 100%;
      text-decoration: none !important;
      color: inherit !important;
      padding: inherit; /* 继承父元素的 padding */
      margin: -10px; /* 抵消父元素的 padding */
    }

    /* 确保伪元素仍然显示 */
    .download-cell:hover::after {
      opacity: 1;
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

    .download-cell:hover::after {
      opacity: 1;
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
          <h1 class="sitename">QTP-GMD</h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php">Home</a></li>
            
            <!-- Omics dropdown menu -->
            <li class="dropdown"><a href="Genomics.html" class="active"><span>Omics</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="Genomics.html" class="active">Genomics</a></li>
                <li><a href="transcriptomics.html">Transcriptomics</a></li>
                <li><a href="metabolomics.html">Metabolomics</a></li>
              </ul>
            </li>
        
            <li><a href="Phenotype.html">Phenotype</a></li>
        
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
        <!-- Dynamic Content Area -->
        <div id="speciesInfo" class="species-info-container">
          
          <!-- Species Information -->
          <div class="species-info mb-5">
            <div class="species-description mb-4" id="speciesDescription">
              <div class="description-content bg-light p-4 rounded-3">
                <p class="mb-0">
                  <span id="scientificName" style="display: inline; font-style: italic; font-family: 'Times New Roman', serif;">
                    <?= htmlspecialchars($selectedSpecies['scientific_name']) ?>
                  </span> 
                  <?= htmlspecialchars($selectedSpecies['description']) ?>
                </p>
              </div>
            </div>
            <h4>Species Information</h4>
            <div class="row align-items-start">
              <!-- Left information column (8 columns width) -->
              <div class="col-lg-8 col-md-7">
                <dl class="row">
                  <dt class="col-sm-4">Scientific Name</dt>
                  <dd class="col-sm-8" id="scientificName">
                    <em><?= htmlspecialchars($selectedSpecies['scientific_name']) ?></em>
                  </dd>
          
                  <dt class="col-sm-4">Common name</dt>
                  <dd class="col-sm-8" id="commonName">
                    <?= htmlspecialchars($selectedSpecies['common_name'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genus</dt>
                  <dd class="col-sm-8" id="genus">
                    <em><?= htmlspecialchars($selectedSpecies['genus'] ?? '') ?></em>
                  </dd>
          
                  <dt class="col-sm-4">Genome Type</dt>
                  <dd class="col-sm-8" id="genomeType">
                    <?= htmlspecialchars($selectedSpecies['genome_type'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Genome Size</dt>
                  <dd class="col-sm-8" id="genomeSize">
                    <?= htmlspecialchars($selectedSpecies['genome_size'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Chromosome Number</dt>
                  <dd class="col-sm-8" id="chromosomeNumber">
                    <?= htmlspecialchars($selectedSpecies['chromosome_number'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">Gene Number</dt>
                  <dd class="col-sm-8" id="geneNumber">
                    <?= htmlspecialchars($selectedSpecies['gene_number'] ?? '') ?>
                  </dd>
          
                  <dt class="col-sm-4">CDS Number</dt>
                  <dd class="col-sm-8" id="cdsNumber">
                    <?= htmlspecialchars($selectedSpecies['cds_number'] ?? '') ?>
                  </dd>
                </dl>
              </div>
          
              <!-- Right image column (4 columns width) -->
              <div class="col-lg-4 col-md-5">
                <div class="species-image-card bg-white p-3 rounded-3 shadow-sm">
                  <img id="speciesImage" 
                       src="<?= htmlspecialchars($selectedSpecies['image_url'] ?: 'assets/img/placeholder.png') ?>" 
                       class="img-fluid rounded" 
                       alt="Species Image"
                       style="max-height: 300px; object-fit: cover;"
                       loading="lazy">
                  <div class="image-caption text-center text-muted mt-2 small" id="imageCaption">
                    <?= htmlspecialchars($selectedSpecies['scientific_name']) ?>
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
                    <td onclick="downloadFile('genomic', '<?= htmlspecialchars($selectedSpecies['id']) ?>')" 
                        id="genomicSequence" 
                        class="download-cell"
                        data-original="<?= htmlspecialchars($selectedSpecies['genomic_sequence'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedSpecies['genomic_sequence'] ?? 'Download') ?>
                    </td>
                    <td onclick="downloadFile('cds', '<?= htmlspecialchars($selectedSpecies['id']) ?>')" 
                        id="cdsSequence" 
                        class="download-cell"
                        data-original="<?= htmlspecialchars($selectedSpecies['cds_sequence'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedSpecies['cds_sequence'] ?? 'Download') ?>
                    </td>
                    <td class="download-cell">
                      <a href="files/annotation/<?= htmlspecialchars($selectedSpecies['gff3_annotation'] ?? '') ?>" 
                        download
                        class="download-link"
                        id="gff3Annotation"
                        data-original="<?= htmlspecialchars($selectedSpecies['gff3_annotation'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedSpecies['gff3_annotation'] ?? 'Download') ?>
                      </a>
                    </td>
                    <td onclick="downloadFile('peptide', '<?= htmlspecialchars($selectedSpecies['id']) ?>')" 
                        id="peptideSequence" 
                        class="download-cell"
                        data-original="<?= htmlspecialchars($selectedSpecies['peptide_sequence'] ?? 'Download') ?>">
                        <?= htmlspecialchars($selectedSpecies['peptide_sequence'] ?? 'Download') ?>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Reference Link -->
          <div class="reference-link mt-5">
            <h4 class="text-center mb-3">Reference Link</h4>
            <p id="referenceLink" class="text-center">
              <a href="<?= htmlspecialchars($selectedSpecies['reference_link'] ?? '#') ?>" 
                 class="d-inline-block" 
                 target="_blank">
                <?= htmlspecialchars($selectedSpecies['reference_link'] ?? '') ?>
              </a>
            </p>
          </div>
        </div>
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
            <p>Research Institute of Highland Agriculture</p>
            <p>University Campus, Building A</p>
            <p class="mt-3"><strong>Tel:</strong> <span>+86 XXX XXXX XXXX</span></p>
            <p><strong>Email:</strong> <span>contact@highlandgrass.edu.cn</span></p>
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

<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Qinghai-Tibetan Plateau Grass Multi-omics Database</title>
  <meta name="description" content="">
  <meta name="keywords" content="">
<!-- Vendor CSS Files 
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet"> 
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  -->


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

</head>

<body class="index-page">

  <header id="header" class="header sticky-top">

    <div class="topbar d-flex align-items-center">
      <div class="container d-flex justify-content-center justify-content-md-between">

      </div>
    </div><!-- End Top Bar -->

    <div class="branding d-flex align-items-center">

      <div class="container position-relative d-flex align-items-center justify-content-end">
        <a href="index.php" class="logo d-flex align-items-center me-auto">
          <img src="./assets/img/logo.png" alt="">
          <!-- Uncomment the line below if you also wish to use a text logo -->
          <!-- <h1 class="sitename">Medicio</h1>  -->
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php" class="active">Home</a></li>

            <!-- Omics 下拉菜单 -->
            <li class="dropdown"><a href="genomics.php"><span>Omics</span> <i
                  class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="genomics.php">Genomics</a></li>
                <li><a href="transcriptomics.html">Transcriptomics</a></li>
                <li><a href="metabolomics.html">Metabolomics</a></li>
                <li><a href="Proteomics.html">Proteomics</a></li>
                <li><a href="microbiomics.php">Microbiomics</a></li>
                <li><a href="Epigenomic.html">Epigenomic</a></li>
              </ul>
            </li>

            <li><a href="phenotype.php">Phenotype</a></li>
            <li><a href="Germplasm.html">Germplasm</a></li>

            <!-- Tools 下拉菜单 -->
            <li class="dropdown"><a href="tools.html"><span>Tools</span> <i
                  class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="tools.html#jbrowse">Jbrowse</a></li>
                <li><a href="tools.html#blast">BLAST</a></li>
                <li><a href="tools.html#primer3">Primer3 Design</a></li>
                <li><a href="tools.html#orthovenn">OrthoVenn</a></li>
                <li><a href="tools.html#enrichment">Enrichment</a></li>
                <li><a href="tools.html#gwas">GWAS Analysis</a></li>
                <li><a href="tools.html#crispr">CRISPR-Predictor</a></li>
                <li><a href="tools.html#gene-search">Gene Search</a></li>

            </li>
          </ul>
          <li><a href="contact.html">Contact</a></li>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        <a class="cta-btn" href="login.html">Sign in</a>

      </div>

    </div>

  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">
      <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">

        <div class="carousel-item active">
          <img src="assets/img/hero-carousel/1.jpg" alt="Highland wheat field" loading="lazy">
          <div class="container">
            <h2>Qinghai-Tibetan Plateau Grass Multi-omics Database</h2>
            <p>The QTP-GMD is a curated and integrated multi-omics resource for the wheat tribe grasses in Qinghai-Tibet Plateau.
                This Database provides multi-omics data containing genome, transcriptome, and metabolome, which provides multi-omics information for geneticists and breeders.
                Several useful bioinformatics tools have also been embedded in the database for for users to make it easy to utilize datasets to facilitate the breeding.</p>
            <a href="genomics.php" class="btn-get-started">Explore Database</a>
          </div>
        </div><!-- End Carousel Item -->

        <div class="carousel-item">
          <img src="assets/img/hero-carousel/dna.jpg" alt="Research laboratory" loading="lazy">
          <div class="container">
              <h2>Qinghai-Tibetan Plateau Grass Multi-omics Database</h2>
              <p>The QTP-GMD is a curated and integrated multi-omics resource for the wheat tribe grasser in Qinghai-Tibet Plateau.
                  This Database provides multi-omics data containing genome, transcriptome, and metabolome, which provides multi-omics information for geneticists and breeders.
                  Several useful bioinformatics tools have also been embedded in the database for for users to make it easy to utilize datasets to facilitate the breeding.</p>
            <a href="tools.html" class="btn-get-started">Explore Database</a>
          </div>
        </div><!-- End Carousel Item -->

        <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
          <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
        </a>

        <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
          <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
        </a>

        <ol class="carousel-indicators"></ol>

      </div>
    </section><!-- End Hero Section -->

    <!-- Featured Services Section -->
    <section id="featured-services" class="featured-services section">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Database</h2>
      </div><!-- End Section Title -->
      <!-- 1.Omics -->
      <div class="container">
        <div class="row gy-4">
          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <h4><a href="Gemonic.html" class="stretched-link">Genomics</a></h4>
              <p>Access comprehensive genomic data of highland grasses with sequence downloads, annotations, and species
                information.</p>
            </div>
          </div><!-- End Service Item -->

          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <h4><a href="transcriptomics.html" class="stretched-link">Transcriptomics</a></h4>
              <p>Access gene expression data with query tools, heatmaps, and GO enrichment analysis for highland grasses
                under various environmental conditions.</p>
            </div>
          </div><!-- End Service Item -->

          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item position-relative">
              <h4><a href="metabolite.html" class="stretched-link">Metabolomics</a></h4>
              <p>Explore metabolite information with detailed chemical properties, sample content queries, and
                interactive heatmaps for highland grass metabolomes.</p>
            </div>
          </div><!-- End Service Item -->
          <!-- 2.Phenotype -->
          <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="400">
            <div class="service-item position-relative">
              <h4><a href="Phenotype.html" class="stretched-link">Phenotype</a></h4>
              <p>Browse comprehensive phenotypic trait data including stress resistance, seed quality, and yield
                measurements from highland grass research across China.</p>
            </div>
          </div><!-- End Service Item -->

        </div>

      </div>

    </section><!-- End Featured Services Section -->


    <!-- Featured Tools Section -->
    <section id="tools" class="featured-services section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Analysis Tools</h2>
        <p>Comprehensive suite of tools for genetic and genomic analysis</p>
      </div>

      <div class="container">
        <div class="row gy-4">
          <!-- JBrowse -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#jbrowse" class="stretched-link">JBrowse</a></h4>
              <p>Interactive genome browser for visualizing and exploring genomic data</p>
            </div>
          </div>

          <!-- BLAST -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#blast" class="stretched-link">BLAST</a></h4>
              <p>Compare nucleotide or protein sequences against databases to find sequence similarities</p>
            </div>
          </div>

          <!-- Primer3 Design -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#primer3" class="stretched-link">Primer3 Design</a></h4>
              <p>Design optimal PCR primers for your DNA targets with customizable parameters</p>
            </div>
          </div>

          <!-- OrthoVenn -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#orthovenn" class="stretched-link">OrthoVenn</a></h4>
              <p>Compare and visualize orthologous gene clusters across multiple species</p>
            </div>
          </div>

          <!-- Enrichment -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#enrichment" class="stretched-link">Enrichment</a></h4>
              <p>Perform GO and KEGG pathway enrichment analysis on gene sets</p>
            </div>
          </div>

          <!-- GWAS Analysis -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#gwas" class="stretched-link">GWAS Analysis</a></h4>
              <p>Identify genetic variants associated with phenotypic traits in highland grasses</p>
            </div>
          </div>

          <!-- CRISPR-Predictor -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#crispr" class="stretched-link">CRISPR-Predictor</a></h4>
              <p>Design and evaluate CRISPR guide RNAs for genome editing applications</p>
            </div>
          </div>

          <!-- Gene Search -->
          <div class="col-xl-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative feature-card">
              <h4><a href="tools.html#gene-search" class="stretched-link">Gene Search</a></h4>
              <p>Quickly locate and retrieve comprehensive information about specific genes</p>
            </div>
          </div>

          
        </div>
      </div>
    </section>

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section accent-background">

      <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
          <div class="col-xl-10">
            <div class="text-center">
              <h3>Join Our Research Network</h3>
              <p>We would love to hear from you for any questions or comments to improve our work.</p>
              <a class="cta-btn" href="contact.html">Contact Us</a>
            </div>
          </div>
        </div>
      </div>

    </section><!-- End Call To Action Section -->

    <!-- Gallery Section -->
    <section id="gallery" class="gallery section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Gallery</h2>
        <p>Here we show some images of the QTP wheat tribe grasses.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "centeredSlides": true,
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 1,
                  "spaceBetween": 0
                },
                "768": {
                  "slidesPerView": 3,
                  "spaceBetween": 20
                },
                "1200": {
                  "slidesPerView": 5,
                  "spaceBetween": 20
                }
              }
            }
          </script>
          <div class="swiper-wrapper align-items-center">
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-1冰草.webp"><img src="assets/img/gallery/gallery-1冰草.webp"
                  class="img-fluid" alt="" loading="lazy"></a></div>
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-2赖草.jpg"><img src="assets/img/gallery/gallery-2赖草.jpg"
                  class="img-fluid" alt="" loading="lazy"></a></div>
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-3山羊草.jpg"><img src="assets/img/gallery/gallery-3山羊草.jpg"
                  class="img-fluid" alt="" loading="lazy"></a></div>
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-4小黑麦.jpg"><img src="assets/img/gallery/gallery-4小黑麦.jpg"
                  class="img-fluid" alt="" loading="lazy"></a></div>
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-5燕麦.jpg"><img src="assets/img/gallery/gallery-5燕麦.jpg"
                  class="img-fluid" alt="" loading="lazy"></a></div>
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-6虉草.jpg"><img src="assets/img/gallery/gallery-6虉草.jpg"
                  class="img-fluid" alt="" loading="lazy"></a></div>
            <div class="swiper-slide"><a class="glightbox" data-gallery="images-gallery"
                href="assets/img/gallery/gallery-7中间偃麦草.jpg"><img src="assets/img/gallery/gallery-7中间偃麦草.jpg"
                  class="img-fluid" alt="" loading="lazy"></a></div>
          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Gallery Section -->


  </main>

  <footer id="footer" class="footer light-background">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">QTP-GMD</span>
          </a>
          <div class="footer-contact pt-3">
            <p>No. 59, Middle Section of Qinglong Avenue, Fucheng District, Mianyang City, Sichuan Province, China</p>
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

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

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

</body>

</html>

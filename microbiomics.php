<?php
// 包含admin目录下的数据库连接文件
require_once 'Admin/config.php';

// 处理筛选条件
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 构建SQL查询
$sql = "SELECT * FROM Microbiomics WHERE 1=1";

if (!empty($category)) {
    $sql .= " AND Species = '" . $conn->real_escape_string($category) . "'";
}

if (!empty($search)) {
    $sql .= " AND (
        Species LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Article_Overview LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Family_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Genus_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Species_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Genome_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Transcriptome_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Microbiome_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Tissue LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Biotic_stress LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Abiotic_stress LIKE '%" . $conn->real_escape_string($search) . "%'
    )";
}

// 执行查询获取总记录数
$count_result = $conn->query($sql);
$total_records = $count_result ? $count_result->num_rows : 0;

// 设置分页参数
$records_per_page = 8;
$total_pages = ceil($total_records / $records_per_page);

// 获取当前页码
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// 计算SQL的LIMIT偏移量
$offset = ($current_page - 1) * $records_per_page;

// 修改SQL查询以包含分页
$page_sql = $sql . " LIMIT $offset, $records_per_page";
$result = $conn->query($page_sql);

// 获取物种列表，用于下拉筛选
$categoryQuery = "SELECT DISTINCT Species FROM Microbiomics WHERE Species IS NOT NULL AND Species != '' ORDER BY Species";
$categoryResult = $conn->query($categoryQuery);
$categories = [];

if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['Species'];
    }
}

// 辅助函数：清理HTML标签但保留文本内容
function strip_tags_preserve_content($html) {
    return strip_tags($html);
}
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title>Microbiomics</title>
        <meta name="description" content="">
        <meta name="keywords" content="">

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


        <!-- Main CSS File -->
        <link href="assets/css/main.css" rel="stylesheet">

        <style>
            .fixed-menu {
                right: 20px;
                z-index: 1000;
            }
            .phenotype-categories .badge {
                font-size: 0.9rem;
                padding: 0.6em 1em;
            }
            .metadata dt {
                font-weight: 500;
                color: #3fbbc0;
            }
            .table th {
                background-color: #3fbbc0;
                color: white;
            }
            .filter-section {
                background: rgba(44,73,100,0.03);
                padding: 1.5rem 0;
                border-bottom: 1px solid #dee2e6;
            }

            .form-select:focus {
                border-color: #3fbbc0;
                box-shadow: 0 0 0 0.25rem rgba(44,73,100,0.25);
            }
            .source-link {
                color: #3fbbc0;
                text-decoration: none;
                transition: all 0.3s;
            }

            .source-link:hover {
                color: #3fbbc0;
                text-decoration: underline;
            }

            .pagination .page-link {
                color: #3fbbc0;
            }

            .pagination .page-item.active .page-link {
                background-color: #fff;
                border-color: #3fbbc0;
            }
            /* Search box style */
            .input-group-text {
                background-color: #fff;
                border-right: none;
            }

            #searchInput {
                border-left: none;
            }

            #searchInput:focus {
                box-shadow: none;
                border-color: #ced4da;
            }
            /* Pagination style enhancement */
            .pagination .page-item.active .page-link {
                background-color: #fff;
                border-color: #3fbbc0;
            }

            .pagination .page-link {
                color: #3fbbc0;
                min-width: 40px;
                text-align: center;
            }

            .pagination .page-item:not(.disabled) .page-link:hover {
                background-color: #f8f9fa;
            }

            #exportBtn {
                background-color: #3fbbc0;
                border-color: #3fbbc0;
                padding: 0.5rem 1.2rem;
            }

            #exportBtn:hover {
                background-color: #3fbbc0;
                border-color: #3fbbc0;
            }

            /* 科学名称斜体样式 */
            .species-italic {
                font-style: italic;
                font-weight: normal;
            }

            /* 确保富文本内容正确显示 */
            .rich-text-content {
                line-height: 1.4;
            }

            .rich-text-content p {
                margin: 0;
                padding: 0;
            }

            .rich-text-content em {
                font-style: italic;
            }

            .rich-text-content strong {
                font-weight: bold;
            }
            .filter-section {
                background: rgba(44,73,100,0.03);
                padding: 2rem 0;
                border-bottom: 1px solid #dee2e6;
            }

            .form-select, .input-group {
                max-width: 100%;
            }

            .form-select-lg, .input-group-lg > .form-control,
            .input-group-lg > .btn {
                font-size: 1.1rem;
                padding: 0.75rem 1rem;
            }

            .form-select option {
                font-style: italic; /* 确保所有下拉框选项都为斜体 */
                font-family: 'Times New Roman', serif;
            }

            .input-group-text {
                background-color: #fff;
                border-right: none;
                padding: 0.75rem 1rem;
            }

            #searchInput {
                border-left: none;
                padding: 0.75rem 1rem;
            }

            #searchInput:focus {
                box-shadow: none;
                border-color: #ced4da;
            }

            #exportBtn {
                background-color: #3fbbc0;
                border-color: #3fbbc0;
                padding: 0.75rem 1.5rem;
            }

            #exportBtn:hover {
                background-color: #2f9a9e;
                border-color: #2f9a9e;
            }

            .col-md-5, .col-md-2 {
                padding-left: 15px;
                padding-right: 15px;
            }

            @media (min-width: 1200px) {
                .container {
                    max-width: 1400px;
                }
            }
        </style>
    </head>

    <body class="starter-page-page">

    <header id="header" class="header sticky-top">
        <div class="topbar d-flex align-items-center">
            <div class="container d-flex justify-content-center justify-content-md-between">
            </div>
        </div>

        <div class="branding d-flex align-items-center">
            <div class="container position-relative d-flex align-items-center justify-content-end">
                <a href="index.php" class="logo d-flex align-items-center me-auto">
                    <img src="./assets/img/logo.png" alt="">
                </a>

                <nav id="navmenu" class="navmenu">
                    <ul>
                        <li><a href="index.php">Home</a></li>

                        <!-- Omics dropdown menu -->
                        <li class="dropdown"><a href="genomics.php" class="active"><span>Omics</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                            <ul>
                                <li><a href="genomics.php">Genomics</a></li>
                                <li><a href="transcriptomics.html">Transcriptomics</a></li>
                                <li><a href="metabolomics.html">Metabolomics</a></li>
                                <li><a href="Proteomics.html">Proteomics</a></li>
                                <li><a href="microbiomics.php" class="active">Microbiomics</a></li>
                                <li><a href="Epigenomic.html">Epigenomic</a></li>
                            </ul>
                        </li>

                        <li><a href="phenotype.php">Phenotype</a></li>
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

    <main class="main">
        <section class="section">
            <div class="container" data-aos="fade-up">
                <div class="section-header mb-5">
                    <h2>Omics</h2>
                    <h3>Microbiomics</h3>
                </div>

                <!-- Filter and search form -->
                <form method="GET" action="microbiomics.php" id="filterForm">
                    <section class="filter-section mb-5">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <select class="form-select form-select-lg" id="categoryFilter" name="category" onchange="this.form.submit()" style="font-style: italic; font-family: 'Times New Roman', serif;">
                                        <option value="">All Species</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category == $cat) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(($cat)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <div class="input-group input-group-lg">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                                        <input type="text" class="form-control" id="searchInput" name="search"
                                               placeholder="Search traits or species..."
                                               value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-outline-secondary">Search</button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-primary btn-lg" id="exportBtn">
                                        <i class="bi bi-download me-2"></i>Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </form>

                <!-- Data table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="dataTable">
                        <thead class="table">
                        <tr>
                            <th>Article Overview</th>
                            <th>Family of Endophyte Fungi</th>
                            <th>Genus of Endophyte Fungi</th>
                            <th>Species of Endophyte Fungi</th>
                            <th>Genome of Endophyte Fungi</th>
                            <th>Transcriptome of Endophyte Fungi</th>
                            <th>Microbiome of Endophyte Fungi</th>
                            <th>Tissue</th>
                            <th>Biotic Stress</th>
                            <th>Abiotic Stress</th>
                            <th>Source</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Check if there is data
                        if ($result && $result->num_rows > 0) {
                            // Output data
                            while($row = $result->fetch_assoc()) {
                                echo "<tr data-category='" . htmlspecialchars(strip_tags_preserve_content($row["Species"])) . "'>";

                                // Article Overview - 保留HTML格式
                                echo "<td><div class='rich-text-content'>" . $row["Article_Overview"] . "</div></td>";

                                // Family of endophyte fungi - 默认斜体
                                echo "<td><span class='species-italic'>" . htmlspecialchars_decode($row["Family_of_endophyte_fungi"],ENT_QUOTES) . "</span></td>";

                                // Genus of endophyte fungi - 默认斜体
                                echo "<td><span class='species-italic'>" . htmlspecialchars_decode($row["Genus_of_endophyte_fungi"], ENT_QUOTES) . "</span></td>";

                                // Species of endophyte fungi - 默认斜体
                                echo "<td><span class='species-italic'>" . htmlspecialchars_decode($row["Species_of_endophyte_fungi"], ENT_QUOTES) . "</span></td>";

                                // Genome of endophyte fungi - 正常显示
                                echo "<td>" . htmlspecialchars($row["Genome_of_endophyte_fungi"]) . "</td>";

                                // Transcriptome of endophyte fungi - 正常显示
                                echo "<td>" . htmlspecialchars($row["Transcriptome_of_endophyte_fungi"] ?? '') . "</td>";

                                // 其他字段正常显示
                                echo "<td>" . htmlspecialchars_decode($row["Microbiome_of_endophyte_fungi"], ENT_QUOTES) . "</td>";
                                echo "<td>" . htmlspecialchars($row["Tissue"]) . "</td>";

                                // Biotic stress - 保留HTML格式
                                echo "<td><div class='rich-text-content'>" . $row["Biotic_stress"] . "</div></td>";

                                echo "<td>" . htmlspecialchars($row["Abiotic_stress"]) . "</td>";

                                // 修改Source列，使其可点击跳转到Link
                                if (!empty($row["Link"])) {
                                    echo "<td><a href='" . htmlspecialchars($row["Link"]) . "' class='source-link' target='_blank'>" . htmlspecialchars($row["Source"]) . "</a></td>";
                                } else {
                                    echo "<td>" . htmlspecialchars($row["Source"]) . "</td>";
                                }

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='11' class='text-center'>No data found</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <?php
                // Pagination (PHP implementation)
                if ($result) {
                    $total_records = $result->num_rows;
                    $records_per_page = 8;
                    $total_pages = ceil($total_records / $records_per_page);

                    // Get current page
                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    if ($current_page < 1) $current_page = 1;
                    if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

                    if ($total_pages > 1) {
                        echo '<nav aria-label="Data pagination" class="mt-4">';
                        echo '<ul class="pagination justify-content-center" id="paginationContainer">';

                        // Previous button
                        $prev_disabled = ($current_page == 1) ? 'disabled' : '';
                        echo '<li class="page-item ' . $prev_disabled . '">';
                        echo '<a class="page-link" href="?page=' . ($current_page - 1) . '&category=' . urlencode($category) . '&search=' . urlencode($search) . '">Previous</a>';
                        echo '</li>';

                        // Page buttons
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active = ($i == $current_page) ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?page=' . $i . '&category=' . urlencode($category) . '&search=' . urlencode($search) . '">' . $i . '</a>';
                            echo '</li>';
                        }

                        // Next button
                        $next_disabled = ($current_page == $total_pages) ? 'disabled' : '';
                        echo '<li class="page-item ' . $next_disabled . '">';
                        echo '<a class="page-link" href="?page=' . ($current_page + 1) . '&category=' . urlencode($category) . '&search=' . urlencode($search) . '">Next</a>';
                        echo '</li>';

                        echo '</ul>';
                        echo '</nav>';
                    }
                }
                ?>
            </div>
        </section>
    </main>

    <footer id="footer" class="footer light-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.php" class="logo d-flex align-items-center">
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
            <div class="beian mt-2">
                <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow" style="color: color-mix(in srgb, var(--default-color), transparent 30%); font-size: 13px;">
                    蜀ICP备2025136730号-1
                </a>
            </div>
        </div>
    </footer>

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
        document.addEventListener('DOMContentLoaded', function() {
            // Export functionality
            document.getElementById('exportBtn').addEventListener('click', exportToCSV);

            function exportToCSV() {
                // Get current filter conditions
                const category = document.getElementById('categoryFilter').value;
                const searchTerm = document.getElementById('searchInput').value;

                // Redirect to export script, passing filter parameters
                window.location.href = 'export_microbiomics.php?category=' + encodeURIComponent(category) + '&search=' + encodeURIComponent(searchTerm);
            }
        });
    </script>
    </body>
    </html>
<?php
// Close database connection
$conn->close();
?>
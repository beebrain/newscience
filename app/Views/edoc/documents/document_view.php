<!DOCTYPE html>
<html
    lang="th"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="<?= base_url(); ?>assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title><?php echo isset($document['title']) ? $document['title'] : 'เอกสาร'; ?> - ระบบ Edocument</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url(); ?>assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= base_url(); ?>assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="<?= base_url(); ?>assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="<?= base_url(); ?>assets/js/config.js"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        .file-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .pdf-container {
            width: 100%;
            height: 500px;
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .image-preview {
            max-width: 100%;
            max-height: 500px;
            border-radius: 0.375rem;
        }

        /* Mobile Optimizations */
        @media (max-width: 767.98px) {
            .authentication-inner {
                max-width: 100% !important;
                padding: 0 10px;
            }

            .card-body {
                padding: 1.25rem;
            }

            .pdf-container {
                height: 400px;
            }

            dt.col-sm-4,
            dd.col-sm-8 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            h4 {
                font-size: 1.25rem;
            }

            .nav-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .nav-tabs .nav-link i {
                margin-right: 4px;
            }

            .badge {
                white-space: normal;
                text-align: left;
            }
        }

        /* For very small screens */
        @media (max-width: 575.98px) {
            .authentication-wrapper {
                padding: 1rem 0;
            }

            .card-body {
                padding: 1rem;
            }

            .pdf-container {
                height: 300px;
            }

            .nav-tabs .nav-link {
                padding: 0.5rem;
            }

            .nav-tabs .nav-link span {
                display: none;
            }

            .nav-tabs .nav-link i {
                margin-right: 0;
            }

            dl.row {
                margin-bottom: 0;
            }

            dt.col-sm-4,
            dd.col-sm-8 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Content -->

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y" style="min-height: 50vh;">
            <div class="authentication-inner" style="max-width: 800px;">
                <?php if (!empty($document)): ?>
                    <!-- Document Info Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-2"><?php echo $document['title']; ?></h4>
                            <p class="mb-4">เอกสารประเภท: <?php echo $document['doctype']; ?></p>

                            <div class="col-xl-12">
                                <h6 class="text-muted">รายละเอียดเอกสาร</h6>
                                <div class="nav-align-top mb-4">
                                    <ul class="nav nav-tabs nav-fill" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-details" aria-controls="navs-justified-details" aria-selected="true">
                                                <i class="tf-icons bx bx-file"></i> <span>รายละเอียด</span>
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-preview" aria-controls="navs-justified-preview" aria-selected="false">
                                                <i class="tf-icons bx bx-show"></i> <span>ดูตัวอย่าง</span>
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-justified-participants" aria-controls="navs-justified-participants" aria-selected="false">
                                                <i class="tf-icons bx bx-user"></i> <span>ผู้เกี่ยวข้อง</span>
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <!-- Details Tab -->
                                        <div class="tab-pane fade active show" id="navs-justified-details" role="tabpanel">
                                            <div class="card-body">
                                                <dl class="row mt-2">
                                                    <?php if (!empty($document['officeiddoc'])): ?>
                                                        <dt class="col-sm-4">เลขที่เอกสาร</dt>
                                                        <dd class="col-sm-8"><?php echo $document['officeiddoc']; ?></dd>
                                                    <?php endif; ?>

                                                    <dt class="col-sm-4">วันที่ลงทะเบียน</dt>
                                                    <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($document['regisdate'])); ?></dd>

                                                    <dt class="col-sm-4">ประเภทเอกสาร</dt>
                                                    <dd class="col-sm-8"><?php echo $document['doctype']; ?></dd>

                                                    <dt class="col-sm-4">เจ้าของเอกสาร</dt>
                                                    <dd class="col-sm-8"><?php echo $document['owner']; ?></dd>

                                                    <?php if (!empty($document['pages'])): ?>
                                                        <dt class="col-sm-4">จำนวนหน้า</dt>
                                                        <dd class="col-sm-8"><?php echo $document['pages']; ?></dd>
                                                    <?php endif; ?>
                                                </dl>
                                            </div>
                                        </div>

                                        <!-- Preview Tab -->
                                        <div class="tab-pane fade" id="navs-justified-preview" role="tabpanel">
                                            <div class="card-body text-center">
                                                <?php
                                                // รองรับทั้ง fileaddress เดี่ยว และ JSON/หลายไฟล์ (ใช้ fileaddress_first จาก Controller)
                                                $file_for_preview = isset($document['fileaddress_first']) && $document['fileaddress_first'] !== '' ? $document['fileaddress_first'] : ($document['fileaddress'] ?? '');
                                                $file_extension = !empty($file_for_preview) ? strtolower(pathinfo($file_for_preview, PATHINFO_EXTENSION)) : '';
                                                $file_extension = preg_replace('/["\'\[\]\s]+$/', '', $file_extension);
                                                ?>

                                                <?php if (!empty($file_for_preview)): ?>
                                                    <?php if ($file_extension === 'pdf'): ?>
                                                        <div class="pdf-container mb-3">
                                                            <iframe src="<?php echo str_replace('public/', '', base_url('EdocDocument/' . $file_for_preview)); ?>" width="100%" height="100%" style="border: none;"></iframe>
                                                        </div>
                                                    <?php elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                        <img src="<?php echo str_replace('public/', '', base_url('EdocDocument/' . $file_for_preview)); ?>" alt="<?php echo htmlspecialchars($document['title'] ?? ''); ?>" class="image-preview mb-3">
                                                    <?php else: ?>
                                                        <div class="file-icon text-primary">
                                                            <i class="bx bx-file"></i>
                                                        </div>
                                                        <h6>ไฟล์ประเภท <?php echo strtoupper($file_extension); ?></h6>
                                                        <p class="text-muted mb-3">ไม่สามารถแสดงตัวอย่างได้ กรุณาดาวน์โหลดเพื่อดูเอกสาร</p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($document['fileaddress_list']) && count($document['fileaddress_list']) > 1): ?>
                                                        <p class="text-muted small mt-2">เอกสารนี้มี <?php echo count($document['fileaddress_list']); ?> ไฟล์ — แสดงตัวอย่างไฟล์แรก</p>
                                                    <?php endif; ?>

                                                    <!-- <a href="<?php echo base_url(); ?>index.php/Documents/secureDownload/<?php //echo $this->uri->segment(3); 
                                                                                                                                ?>" class="btn btn-primary">
                                                        <i class="bx bx-download me-1"></i> ดาวน์โหลดเอกสาร
                                                    </a> -->
                                                <?php else: ?>
                                                    <div class="file-icon text-warning">
                                                        <i class="bx bx-error-circle"></i>
                                                    </div>
                                                    <h6>ไม่พบไฟล์เอกสาร</h6>
                                                    <p class="text-muted"><?php echo isset($error) ? $error : 'เอกสารนี้ไม่มีไฟล์แนบ'; ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Participants Tab -->
                                        <div class="tab-pane fade" id="navs-justified-participants" role="tabpanel">
                                            <div class="card-body">
                                                <?php if (!empty($document['participant'])): ?>
                                                    <h6 class="mb-3">รายชื่อผู้เกี่ยวข้อง</h6>
                                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                                        <?php
                                                        $participants = array_map('trim', explode(',', $document['participant']));
                                                        foreach ($participants as $participant):
                                                            if (trim($participant) === 'ทุกคน'):
                                                        ?>
                                                                <span class="badge bg-primary"><?php echo $participant; ?></span>
                                                            <?php else: ?>
                                                                <span class="badge bg-label-info"><?php echo $participant; ?></span>
                                                        <?php endif;
                                                        endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted mb-0">ไม่มีข้อมูลผู้เกี่ยวข้อง</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (isset($is_temporary_access) && $is_temporary_access): ?>
                                <div class="alert alert-primary d-flex" role="alert">
                                    <span class="badge badge-center rounded-pill bg-primary border-label-primary p-3 me-2">
                                        <i class="bx bx-lock-open fs-6"></i>
                                    </span>
                                    <div class="d-flex flex-column ps-1">
                                        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">การเข้าถึงชั่วคราว</h6>
                                        <span>คุณกำลังดูเอกสารนี้ผ่านลิงค์เข้าถึงโดยตรง หากต้องการเข้าถึงเอกสารอื่นๆ โปรด
                                            <a href="<?php echo base_url(); ?>index.php/" class="alert-link">เข้าสู่ระบบ</a></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Error Card -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="file-icon text-warning" style="font-size: 4rem; margin-bottom: 1.5rem;">
                                <i class="bx bx-error-circle"></i>
                            </div>
                            <h4>ไม่พบเอกสารที่ต้องการ</h4>
                            <p class="text-muted">เอกสารที่คุณกำลังพยายามเข้าถึงอาจถูกลบหรือลิงก์อาจหมดอายุแล้ว</p>
                            <a href="<?php echo base_url(); ?>index.php/" class="btn btn-primary mt-3">
                                <i class="bx bx-home me-1"></i> กลับสู่หน้าหลัก
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?= base_url(); ?>assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= base_url(); ?>assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= base_url(); ?>assets/vendor/js/bootstrap.js"></script>
    <script src="<?= base_url(); ?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Main JS -->
    <script src="<?= base_url(); ?>assets/js/main.js"></script>

    <!-- Mobile Optimizations -->
    <script>
        // Check if device is mobile and adjust UI accordingly
        document.addEventListener('DOMContentLoaded', function() {
            // For very small screens, focus on the details tab first
            if (window.innerWidth < 576) {
                document.querySelectorAll('.nav-tabs .nav-item').forEach(function(item) {
                    item.querySelector('.nav-link span').style.display = 'none';
                });

                // Adjust PDF container height based on screen height
                const pdfContainer = document.querySelector('.pdf-container');
                if (pdfContainer) {
                    pdfContainer.style.height = (window.innerHeight * 0.6) + 'px';
                }
            }
        });
    </script>
</body>

</html>
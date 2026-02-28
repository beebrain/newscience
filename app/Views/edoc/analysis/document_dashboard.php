<?php

/**
 * Document Analysis Dashboard
 * 
 * A comprehensive visualization interface for document repository analytics with
 * role-differentiated capabilities. This implementation provides analytical insights
 * into document distribution, usage patterns, and temporal trends with appropriate
 * access controls based on user authorization level.
 */
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?? 'Document Analytics Dashboard' ?></title>

    <!-- Favicon -->
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/backend-plugin.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/backend.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/remixicon/fonts/remixicon.css') ?>">
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/apexcharts/dist/apexcharts.css') ?>">
</head>

<body class="color-light">
    <!-- Loader Start -->
    <div id="loading">
        <div id="loading-center">
        </div>
    </div>
    <!-- Loader END -->

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="iq-sidebar sidebar-default" id="mainMenu">
        </div>
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Document Analytics Dashboard</h4>
                                <p class="mb-0">
                                    <?php if ($isAdmin): ?>
                                        Comprehensive document repository analytics with administrator privileges.
                                    <?php else: ?>
                                        Document analytics for materials you're tagged in as a participant.
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="form-group mr-3 mb-0">
                                    <label for="period-selector" class="mb-0 mr-2">Analysis Period:</label>
                                    <select class="form-control" id="period-selector">
                                        <option value="all">All Time</option>
                                        <option value="year" selected>Current Year</option>
                                        <option value="quarter">Last Quarter</option>
                                        <option value="month">Last Month</option>
                                        <option value="week">Last Week</option>
                                    </select>
                                </div>
                                <?php if ($isAdmin): ?>
                                    <div class="form-group mr-3 mb-0">
                                        <label for="user-filter" class="mb-0 mr-2">User Filter:</label>
                                        <select class="form-control" id="user-filter">
                                            <option value="all">All Users</option>
                                            <option value="<?= $userId ?>">Only My Documents</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <a href="<?= base_url('document/export-analysis') ?>" class="btn btn-primary">
                                    <i class="las la-file-export mr-2"></i>Export Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Overview Section -->
                    <div class="col-lg-12">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Repository Analytics Overview</h4>
                                </div>
                                <div class="card-header-toolbar">
                                    <span class="badge badge-primary">
                                        <?= $isAdmin ? 'Administrator View' : 'User View' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Total Documents Card -->
                                    <div class="col-md-3 col-sm-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon iq-icon-box bg-primary rounded mr-3">
                                                <i class="ri-file-text-line text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 mt-0">Total Documents</h6>
                                                <h3 class="mb-0" id="total-documents">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total Pages Card -->
                                    <div class="col-md-3 col-sm-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon iq-icon-box bg-success rounded mr-3">
                                                <i class="ri-file-paper-2-line text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 mt-0">Total Pages</h6>
                                                <h3 class="mb-0" id="total-pages">
                                                    <div class="spinner-border spinner-border-sm text-success" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Document Types Card -->
                                    <div class="col-md-3 col-sm-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon iq-icon-box bg-danger rounded mr-3">
                                                <i class="ri-file-list-3-line text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 mt-0">Document Types</h6>
                                                <h3 class="mb-0" id="document-types">
                                                    <div class="spinner-border spinner-border-sm text-danger" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Average Pages Card -->
                                    <div class="col-md-3 col-sm-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon iq-icon-box bg-warning rounded mr-3">
                                                <i class="ri-file-chart-line text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 mt-0">Avg. Pages</h6>
                                                <h3 class="mb-0" id="avg-pages">
                                                    <div class="spinner-border spinner-border-sm text-warning" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Distribution Analysis -->
                    <div class="col-lg-6">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Document Type Distribution</h4>
                                </div>
                                <div class="card-header-toolbar">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="docTypeChartOptions" data-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-4-line mr-1"></i>Visualization
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="docTypeChartOptions">
                                            <li><a class="dropdown-item chart-type" data-type="donut" data-chart="doctype" href="#">Donut Chart</a></li>
                                            <li><a class="dropdown-item chart-type" data-type="pie" data-chart="doctype" href="#">Pie Chart</a></li>
                                            <li><a class="dropdown-item chart-type" data-type="bar" data-chart="doctype" href="#">Bar Chart</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="doctype-chart" style="height: 350px;">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Document Trend -->
                    <div class="col-lg-6">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Monthly Document Creation Trend</h4>
                                </div>
                                <div class="card-header-toolbar">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="trendChartOptions" data-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-4-line mr-1"></i>Visualization
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="trendChartOptions">
                                            <li><a class="dropdown-item chart-type" data-type="line" data-chart="trend" href="#">Line Chart</a></li>
                                            <li><a class="dropdown-item chart-type" data-type="bar" data-chart="trend" href="#">Bar Chart</a></li>
                                            <li><a class="dropdown-item chart-type" data-type="area" data-chart="trend" href="#">Area Chart</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="trend-chart" style="height: 350px;">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Document Contributors -->
                    <div class="col-lg-6">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Top Document Contributors</h4>
                                </div>
                                <div class="card-header-toolbar">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="ownerLimitOptions" data-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-filter-2-line mr-1"></i>Top
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="ownerLimitOptions">
                                            <li><a class="dropdown-item owner-limit" data-limit="5" href="#">Top 5</a></li>
                                            <li><a class="dropdown-item owner-limit" data-limit="10" href="#">Top 10</a></li>
                                            <li><a class="dropdown-item owner-limit" data-limit="15" href="#">Top 15</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="owners-chart" style="height: 350px;">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Page Distribution -->
                    <div class="col-lg-6">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Document Page Distribution</h4>
                                </div>
                                <div class="card-header-toolbar">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="pageDistOptions" data-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-4-line mr-1"></i>Visualization
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="pageDistOptions">
                                            <li><a class="dropdown-item chart-type" data-type="bar" data-chart="pages" href="#">Bar Chart</a></li>
                                            <li><a class="dropdown-item chart-type" data-type="bar-horizontal" data-chart="pages" href="#">Horizontal Bar</a></li>
                                            <li><a class="dropdown-item chart-type" data-type="donut" data-chart="pages" href="#">Donut Chart</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="pages-chart" style="height: 350px;">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($isAdmin): ?>
                        <!-- Advanced Analytics Section (Admin Only) -->
                        <div class="col-lg-12">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="header-title">
                                        <h4 class="card-title">Advanced Document Analytics</h4>
                                    </div>
                                    <div class="card-header-toolbar">
                                        <button class="btn btn-sm btn-outline-primary" id="toggle-advanced">
                                            <i class="ri-equalizer-line mr-1"></i>Configure Analysis
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="advanced-options" class="mb-4" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="metric-selection">Primary Metric</label>
                                                    <select class="form-control" id="metric-selection">
                                                        <option value="doc_count">Document Count</option>
                                                        <option value="avg_pages">Average Pages</option>
                                                        <option value="total_paper">Total Paper Usage</option>
                                                        <option value="max_pages">Maximum Pages</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="dimension-selection">Analysis Dimension</label>
                                                    <select class="form-control" id="dimension-selection">
                                                        <option value="doctype">Document Type</option>
                                                        <option value="owner">Document Owner</option>
                                                        <option value="time">Time Period</option>
                                                        <option value="participant">Document Participants</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="visualization-type">Visualization</label>
                                                    <select class="form-control" id="visualization-type">
                                                        <option value="bar">Bar Chart</option>
                                                        <option value="line">Line Chart</option>
                                                        <option value="radar">Radar Chart</option>
                                                        <option value="heatmap">Heat Map</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button class="btn btn-primary btn-block" id="apply-advanced">
                                                        Apply Analysis
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="advanced-analytics-chart" style="height: 450px;">
                                        <div class="d-flex justify-content-center align-items-center h-100">
                                            <div class="text-center">
                                                <p class="mb-2">Configure and apply advanced analysis parameters</p>
                                                <button class="btn btn-primary" id="show-advanced">
                                                    <i class="ri-equalizer-line mr-1"></i>Configure Analysis
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Recent Activity Table -->
                    <div class="col-lg-12">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Recent Document Activity</h4>
                                </div>
                                <div class="card-header-toolbar">
                                    <button class="btn btn-sm btn-outline-primary" id="refresh-activity">
                                        <i class="ri-refresh-line mr-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table data-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Document Count</th>
                                                <th>Total Pages</th>
                                                <th>Trend</th>
                                            </tr>
                                        </thead>
                                        <tbody id="activity-table-body">
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <footer class="iq-footer">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><a href="<?= base_url('policies/privacy') ?>">Privacy Policy</a></li>
                                <li class="list-inline-item"><a href="<?= base_url('policies/terms') ?>">Terms of Use</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-6 text-right">
                            <span class="mr-1">
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>ยฉ
                            </span> <a href="<?= base_url() ?>" class="">EdocDocument</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/backend-bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/table-treeview.js') ?>"></script>
    <script src="<?= base_url('assets/js/customizer.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/apexcharts/dist/apexcharts.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

    <script>
        // Load main menu via AJAX
        $("#mainMenu").load("<?= base_url('index.php/utility/menuController/getMainMenu') ?>");

        // Document Analysis Dashboard JavaScript with AJAX implementation
        $(document).ready(function() {
            // Define color palette for charts
            const colors = ['#3a57e8', '#4bc7d2', '#c03221', '#876cfe', '#079aa2', '#feb019', '#ffbf43', '#35c2c2', '#39795c', '#845EC2'];

            // Chart instances for reference
            let doctypeChart, trendChart, ownersChart, pagesChart, advancedChart;

            // Chart configuration
            let chartConfig = {
                doctype: {
                    type: 'donut'
                },
                trend: {
                    type: 'line'
                },
                pages: {
                    type: 'bar-horizontal'
                }
            };

            // Analysis parameters
            let params = {
                period: 'year',
                userFilter: 'all',
                ownerLimit: 10
            };

            // Initialize dashboard
            initializeDashboard();

            // Period selector change event
            $('#period-selector').on('change', function() {
                params.period = $(this).val();
                refreshDashboard();
            });

            // User filter change event (admin only)
            $('#user-filter').on('change', function() {
                params.userFilter = $(this).val();
                refreshDashboard();
            });

            // Chart type selectors
            $('.chart-type').on('click', function(e) {
                e.preventDefault();
                const chartId = $(this).data('chart');
                const chartType = $(this).data('type');

                chartConfig[chartId].type = chartType;

                // Reload specific chart
                if (chartId === 'doctype') {
                    loadDocTypeDistribution();
                } else if (chartId === 'trend') {
                    loadMonthlyTrend();
                } else if (chartId === 'pages') {
                    loadPageDistribution();
                }
            });

            // Owner limit selector
            $('.owner-limit').on('click', function(e) {
                e.preventDefault();
                params.ownerLimit = $(this).data('limit');
                loadTopOwners();
            });

            // Refresh activity button
            $('#refresh-activity').on('click', function() {
                loadRecentActivity();
            });

            // Advanced analytics toggle
            $('#toggle-advanced, #show-advanced').on('click', function() {
                $('#advanced-options').slideToggle();
            });

            // Apply advanced analytics
            $('#apply-advanced').on('click', function() {
                loadAdvancedAnalytics();
            });

            /**
             * Initialize the dashboard by loading all components
             */
            function initializeDashboard() {
                loadSummaryMetrics();
                loadDocTypeDistribution();
                loadMonthlyTrend();
                loadTopOwners();
                loadPageDistribution();
                loadRecentActivity();
            }

            /**
             * Refresh all dashboard components
             */
            function refreshDashboard() {
                loadSummaryMetrics();
                loadDocTypeDistribution();
                loadMonthlyTrend();
                loadTopOwners();
                loadPageDistribution();
                loadRecentActivity();
            }

            /**
             * Load summary metrics via AJAX
             */
            function loadSummaryMetrics() {
                $.ajax({
                    url: '<?= base_url('index.php/document/api/summary-metrics') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        user_filter: params.userFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Update summary cards
                        $('#total-documents').html(numberWithCommas(response.metrics.total_documents));
                        $('#total-pages').html(numberWithCommas(response.metrics.total_pages));
                        $('#document-types').html(response.metrics.document_types);
                        $('#avg-pages').html(response.metrics.average_pages);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading summary metrics:', error);
                        showErrorMessage('Failed to load summary metrics');
                    }
                });
            }

            /**
             * Load document type distribution chart via AJAX
             */
            function loadDocTypeDistribution() {
                // Show loading spinner
                $('#doctype-chart').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: '<?= base_url('index.php/document/api/doc-type-distribution') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        user_filter: params.userFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear the loading spinner
                        $('#doctype-chart').html('');

                        // Prepare chart configuration based on selected type
                        let options = {};

                        if (chartConfig.doctype.type === 'donut' || chartConfig.doctype.type === 'pie') {
                            options = {
                                series: response.map(item => parseInt(item.count)),
                                labels: response.map(item => item.doctype || 'Undefined'),
                                chart: {
                                    type: chartConfig.doctype.type,
                                    height: 350,
                                    fontFamily: 'Nunito, sans-serif',
                                    toolbar: {
                                        show: true
                                    }
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '55%',
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    fontSize: '16px',
                                                    label: 'Total Documents',
                                                    formatter: function(w) {
                                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val, opts) {
                                        return opts.w.config.labels[opts.seriesIndex];
                                    }
                                },
                                legend: {
                                    show: true,
                                    position: 'bottom'
                                },
                                responsive: [{
                                    breakpoint: 480,
                                    options: {
                                        chart: {
                                            width: 200
                                        },
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }],
                                colors: colors
                            };
                        } else if (chartConfig.doctype.type === 'bar') {
                            options = {
                                series: [{
                                    name: 'Documents',
                                    data: response.map(item => parseInt(item.count))
                                }],
                                chart: {
                                    type: 'bar',
                                    height: 350,
                                    fontFamily: 'Nunito, sans-serif',
                                    toolbar: {
                                        show: true
                                    }
                                },
                                plotOptions: {
                                    bar: {
                                        distributed: true,
                                        borderRadius: 4,
                                        horizontal: false,
                                    }
                                },
                                dataLabels: {
                                    enabled: false
                                },
                                xaxis: {
                                    categories: response.map(item => item.doctype || 'Undefined'),
                                    labels: {
                                        style: {
                                            fontSize: '12px'
                                        }
                                    }
                                },
                                colors: colors,
                                legend: {
                                    show: false
                                }
                            };
                        }

                        // Destroy previous chart if exists
                        if (doctypeChart) {
                            doctypeChart.destroy();
                        }

                        // Create new chart
                        doctypeChart = new ApexCharts(document.querySelector("#doctype-chart"), options);
                        doctypeChart.render();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading document type distribution:', error);
                        showErrorMessage('Failed to load document type distribution');
                    }
                });
            }

            /**
             * Load monthly trend chart via AJAX
             */
            function loadMonthlyTrend() {
                // Show loading spinner
                $('#trend-chart').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: '<?= base_url('index.php/document/api/monthly-trend') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        user_filter: params.userFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear the loading spinner
                        $('#trend-chart').html('');

                        const options = {
                            series: [{
                                name: "Documents",
                                data: response.map(item => parseInt(item.count))
                            }],
                            chart: {
                                height: 350,
                                type: chartConfig.trend.type,
                                zoom: {
                                    enabled: true
                                },
                                toolbar: {
                                    show: true
                                },
                                fontFamily: 'Nunito, sans-serif'
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                curve: 'smooth',
                                width: 3
                            },
                            fill: {
                                type: chartConfig.trend.type === 'area' ? 'gradient' : 'solid',
                                gradient: {
                                    shade: 'dark',
                                    type: "vertical",
                                    shadeIntensity: 0.5,
                                    gradientToColors: undefined,
                                    inverseColors: true,
                                    opacityFrom: 0.7,
                                    opacityTo: 0.3
                                }
                            },
                            colors: ['#3a57e8'],
                            grid: {
                                row: {
                                    colors: ['#f3f3f3', 'transparent'],
                                    opacity: 0.5
                                }
                            },
                            markers: {
                                size: 4
                            },
                            xaxis: {
                                categories: response.map(item => item.period),
                                title: {
                                    text: 'Time Period'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Document Count'
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + " documents";
                                    }
                                }
                            }
                        };

                        // Destroy previous chart if exists
                        if (trendChart) {
                            trendChart.destroy();
                        }

                        // Create new chart
                        trendChart = new ApexCharts(document.querySelector("#trend-chart"), options);
                        trendChart.render();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading monthly trend:', error);
                        showErrorMessage('Failed to load monthly trend data');
                    }
                });
            }

            /**
             * Load top owners chart via AJAX
             */
            function loadTopOwners() {
                // Show loading spinner
                $('#owners-chart').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: '<?= base_url('index.php/document/api/top-owners') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        user_filter: params.userFilter,
                        limit: params.ownerLimit
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear the loading spinner
                        $('#owners-chart').html('');

                        const options = {
                            series: [{
                                name: "Documents",
                                data: response.map(item => parseInt(item.count))
                            }],
                            chart: {
                                height: 350,
                                type: 'bar',
                                toolbar: {
                                    show: false
                                },
                                fontFamily: 'Nunito, sans-serif'
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    horizontal: true,
                                    distributed: true
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val;
                                },
                                style: {
                                    fontSize: '12px',
                                    colors: ['#fff']
                                }
                            },
                            colors: colors,
                            xaxis: {
                                categories: response.map(item => {
                                    // Truncate long owner names for better display
                                    return item.owner.length > 20 ? item.owner.substring(0, 20) + '...' : item.owner;
                                }),
                                title: {
                                    text: 'Document Count'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Document Owner'
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + " documents";
                                    }
                                }
                            }
                        };

                        // Destroy previous chart if exists
                        if (ownersChart) {
                            ownersChart.destroy();
                        }

                        // Create new chart
                        ownersChart = new ApexCharts(document.querySelector("#owners-chart"), options);
                        ownersChart.render();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading top owners:', error);
                        showErrorMessage('Failed to load top owners data');
                    }
                });
            }

            /**
             * Load page distribution chart via AJAX
             */
            function loadPageDistribution() {
                // Show loading spinner
                $('#pages-chart').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: '<?= base_url('index.php/document/api/page-distribution') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        user_filter: params.userFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear the loading spinner
                        $('#pages-chart').html('');

                        let options = {};

                        if (chartConfig.pages.type === 'donut' || chartConfig.pages.type === 'pie') {
                            options = {
                                series: response.map(item => parseInt(item.count)),
                                labels: response.map(item => item.page_range),
                                chart: {
                                    type: chartConfig.pages.type,
                                    height: 350,
                                    fontFamily: 'Nunito, sans-serif'
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '55%',
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    fontSize: '16px',
                                                    label: 'Total Documents',
                                                    formatter: function(w) {
                                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                colors: colors,
                                legend: {
                                    position: 'bottom'
                                }
                            };
                        } else {
                            const isHorizontal = chartConfig.pages.type === 'bar-horizontal';
                            options = {
                                series: [{
                                    name: "Documents",
                                    data: response.map(item => parseInt(item.count))
                                }],
                                chart: {
                                    height: 350,
                                    type: 'bar',
                                    toolbar: {
                                        show: false
                                    },
                                    fontFamily: 'Nunito, sans-serif'
                                },
                                plotOptions: {
                                    bar: {
                                        borderRadius: 4,
                                        horizontal: isHorizontal,
                                        distributed: true
                                    }
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val) {
                                        return val;
                                    },
                                    style: {
                                        fontSize: '12px',
                                        colors: ['#fff']
                                    }
                                },
                                colors: colors,
                                xaxis: {
                                    categories: response.map(item => item.page_range),
                                    title: {
                                        text: isHorizontal ? 'Document Count' : 'Page Range'
                                    }
                                },
                                yaxis: {
                                    title: {
                                        text: isHorizontal ? 'Page Range' : 'Document Count'
                                    }
                                },
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return val + " documents";
                                        }
                                    }
                                }
                            };
                        }

                        // Destroy previous chart if exists
                        if (pagesChart) {
                            pagesChart.destroy();
                        }

                        // Create new chart
                        pagesChart = new ApexCharts(document.querySelector("#pages-chart"), options);
                        pagesChart.render();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading page distribution:', error);
                        showErrorMessage('Failed to load page distribution data');
                    }
                });
            }

            /**
             * Load recent activity table via AJAX
             */
            function loadRecentActivity() {
                // Show loading spinner
                $('#activity-table-body').html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');

                $.ajax({
                    url: '<?= base_url('index.php/document/api/summary-metrics') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        user_filter: params.userFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.recent_activity && response.recent_activity.length > 0) {
                            // Clear the table body
                            $('#activity-table-body').html('');

                            // Calculate percent changes
                            let prevCount = null;

                            // Add rows to the table
                            response.recent_activity.forEach(function(item, index) {
                                let trendIcon = '';
                                let trendClass = '';

                                if (index < response.recent_activity.length - 1) {
                                    const nextItem = response.recent_activity[index + 1];
                                    const countDiff = item.count - nextItem.count;
                                    const percentChange = nextItem.count > 0 ? (countDiff / nextItem.count * 100).toFixed(1) : 0;

                                    if (countDiff > 0) {
                                        trendIcon = '<i class="ri-arrow-up-line"></i>';
                                        trendClass = 'text-success';
                                    } else if (countDiff < 0) {
                                        trendIcon = '<i class="ri-arrow-down-line"></i>';
                                        trendClass = 'text-danger';
                                    } else {
                                        trendIcon = '<i class="ri-subtract-line"></i>';
                                        trendClass = 'text-secondary';
                                    }

                                    trendIcon += ' ' + Math.abs(percentChange) + '%';
                                } else {
                                    trendIcon = 'N/A';
                                    trendClass = 'text-muted';
                                }

                                $('#activity-table-body').append(`
                                    <tr>
                                        <td>${item.date}</td>
                                        <td>${item.count}</td>
                                        <td>${item.total_pages || 'N/A'}</td>
                                        <td class="${trendClass}">${trendIcon}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            $('#activity-table-body').html('<tr><td colspan="4" class="text-center">No recent activity found</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading recent activity:', error);
                        $('#activity-table-body').html('<tr><td colspan="4" class="text-center text-danger">Failed to load recent activity</td></tr>');
                    }
                });
            }

            /**
             * Load advanced analytics chart via AJAX
             * This function is typically accessible only to administrators
             */
            function loadAdvancedAnalytics() {
                const metric = $('#metric-selection').val();
                const dimension = $('#dimension-selection').val();
                const visualizationType = $('#visualization-type').val();

                // Show loading spinner
                $('#advanced-analytics-chart').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');

                $.ajax({
                    url: '<?= base_url('document/api/advanced-analytics') ?>',
                    type: 'GET',
                    data: {
                        period: params.period,
                        metric: metric,
                        dimension: dimension,
                        user_filter: params.userFilter
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear the loading spinner
                        $('#advanced-analytics-chart').html('');

                        // Handle access denied error
                        if (response.access_denied) {
                            $('#advanced-analytics-chart').html(`
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                        <div class="mb-3"><i class="ri-lock-line text-danger" style="font-size: 3rem;"></i></div>
                                        <h5 class="text-danger">Access Restricted</h5>
                                        <p>${response.error}</p>
                                    </div>
                                </div>
                            `);
                            return;
                        }

                        // Generate chart options based on visualization type
                        let options = generateChartOptions(response, metric, dimension, visualizationType);

                        // Destroy previous chart if exists
                        if (advancedChart) {
                            advancedChart.destroy();
                        }

                        // Create new chart
                        advancedChart = new ApexCharts(document.querySelector("#advanced-analytics-chart"), options);
                        advancedChart.render();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading advanced analytics:', error);
                        $('#advanced-analytics-chart').html(`
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="text-center">
                                    <div class="mb-3"><i class="ri-error-warning-line text-danger" style="font-size: 3rem;"></i></div>
                                    <h5>Error Loading Data</h5>
                                    <p>Failed to load advanced analytics data. Please try again.</p>
                                </div>
                            </div>
                        `);
                    }
                });
            }

            /**
             * Generate chart options based on visualization type
             * 
             * @param {Array} data Chart data
             * @param {string} metric Metric name
             * @param {string} dimension Dimension name
             * @param {string} visualizationType Chart type
             * @returns {Object} Chart options
             */
            function generateChartOptions(data, metric, dimension, visualizationType) {
                let options = {};

                // Format data for chart
                const formattedData = formatChartData(data, metric);

                if (visualizationType === 'bar') {
                    options = {
                        series: [{
                            name: getMetricLabel(metric),
                            data: formattedData
                        }],
                        chart: {
                            height: 450,
                            type: 'bar',
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: true,
                                    reset: true
                                }
                            },
                            fontFamily: 'Nunito, sans-serif'
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '50%',
                                distributed: true
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        xaxis: {
                            categories: data.map(item => getDimensionValue(item, dimension)),
                            title: {
                                text: getDimensionLabel(dimension)
                            }
                        },
                        yaxis: {
                            title: {
                                text: getMetricLabel(metric)
                            }
                        },
                        colors: colors,
                        legend: {
                            show: false
                        }
                    };
                } else if (visualizationType === 'line') {
                    options = {
                        series: [{
                            name: getMetricLabel(metric),
                            data: formattedData
                        }],
                        chart: {
                            height: 450,
                            type: 'line',
                            zoom: {
                                enabled: true
                            },
                            toolbar: {
                                show: true
                            },
                            fontFamily: 'Nunito, sans-serif'
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        colors: ['#3a57e8'],
                        markers: {
                            size: 5
                        },
                        xaxis: {
                            categories: data.map(item => getDimensionValue(item, dimension)),
                            title: {
                                text: getDimensionLabel(dimension)
                            }
                        },
                        yaxis: {
                            title: {
                                text: getMetricLabel(metric)
                            }
                        }
                    };
                } else if (visualizationType === 'radar') {
                    options = {
                        series: [{
                            name: getMetricLabel(metric),
                            data: formattedData
                        }],
                        chart: {
                            height: 450,
                            type: 'radar',
                            toolbar: {
                                show: true
                            },
                            fontFamily: 'Nunito, sans-serif'
                        },
                        dataLabels: {
                            enabled: true
                        },
                        plotOptions: {
                            radar: {
                                size: 140,
                                polygons: {
                                    strokeColors: '#e9e9e9',
                                    fill: {
                                        colors: ['#f8f8f8', '#fff']
                                    }
                                }
                            }
                        },
                        colors: ['#FF4560'],
                        markers: {
                            size: 5,
                            colors: ['#FF4560'],
                            strokeWidth: 2,
                        },
                        xaxis: {
                            categories: data.map(item => getDimensionValue(item, dimension))
                        }
                    };
                } else if (visualizationType === 'heatmap') {
                    // Create data series for heatmap
                    const seriesData = [];
                    data.forEach((item, index) => {
                        seriesData.push({
                            name: getDimensionValue(item, dimension),
                            data: [{
                                x: getMetricLabel(metric),
                                y: parseFloat(item[metric])
                            }]
                        });
                    });

                    options = {
                        series: seriesData,
                        chart: {
                            height: 450,
                            type: 'heatmap',
                            toolbar: {
                                show: true
                            },
                            fontFamily: 'Nunito, sans-serif'
                        },
                        dataLabels: {
                            enabled: true
                        },
                        colors: ["#008FFB"]
                    };
                }

                return options;
            }

            /**
             * Format chart data based on metric
             * 
             * @param {Array} data Raw data array
             * @param {string} metric Metric name
             * @returns {Array} Formatted data
             */
            function formatChartData(data, metric) {
                return data.map(item => {
                    if (metric === 'avg_pages' || metric === 'max_pages' || metric === 'min_pages') {
                        return parseFloat(parseFloat(item[metric]).toFixed(2));
                    } else {
                        return parseInt(item[metric]);
                    }
                });
            }

            /**
             * Get label for metric
             * 
             * @param {string} metric Metric code
             * @returns {string} Human-readable metric label
             */
            function getMetricLabel(metric) {
                const labels = {
                    'doc_count': 'Document Count',
                    'avg_pages': 'Average Pages',
                    'total_paper': 'Total Paper Usage',
                    'max_pages': 'Maximum Pages',
                    'min_pages': 'Minimum Pages'
                };

                return labels[metric] || metric;
            }

            /**
             * Get label for dimension
             * 
             * @param {string} dimension Dimension code
             * @returns {string} Human-readable dimension label
             */
            function getDimensionLabel(dimension) {
                const labels = {
                    'doctype': 'Document Type',
                    'owner': 'Document Owner',
                    'time': 'Time Period',
                    'participant': 'Document Participant'
                };

                return labels[dimension] || dimension;
            }

            /**
             * Get value for dimension from item
             * 
             * @param {Object} item Data item
             * @param {string} dimension Dimension name
             * @returns {string} Dimension value
             */
            function getDimensionValue(item, dimension) {
                if (dimension === 'time') {
                    // Parse date for time dimension
                    return item.period || item.month || item.dimension || 'Unknown';
                }

                return item.dimension || 'Unknown';
            }

            /**
             * Format number with commas
             * 
             * @param {number} x Number to format
             * @returns {string} Formatted number
             */
            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            /**
             * Show error message
             * 
             * @param {string} message Error message
             */
            function showErrorMessage(message) {
                // Create and show toast notification for error messages
                const toastId = 'error-toast-' + Math.floor(Math.random() * 1000);

                const toastHtml = `
                    <div id="${toastId}" class="toast fade hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                        <div class="toast-header bg-danger text-white">
                            <i class="ri-error-warning-line mr-2"></i>
                            <strong class="mr-auto">Error</strong>
                            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;

                // Append toast to body if toast container doesn't exist
                if ($('#toast-container').length === 0) {
                    $('body').append('<div id="toast-container" class="position-fixed bottom-0 right-0 p-3" style="z-index: 9999;"></div>');
                }

                // Add toast to container and show it
                $('#toast-container').append(toastHtml);
                $(`#${toastId}`).toast('show');

                // Remove toast when hidden
                $(`#${toastId}`).on('hidden.bs.toast', function() {
                    $(this).remove();
                });

                // Log error to console for debugging
                console.error(message);
            }
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Tracer | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/alumni_tracer_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation (Unchanged) -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="/assets/logos/LumiNUs_Logo_Landscape_Blue.png" alt="LumiNUs Logo" class="logo-luminus">
                </div>
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="/admin/dashboard" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/directory" class="nav-item">
                    <i class="fa-solid fa-users"></i>
                    <span>Alumni Directory</span>
                </a>
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i>
                    <span>Perks & Discounts</span>
                </a>
                <a href="/admin/alumni_tracer" class="nav-item active">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Alumni Tracer</span>
                </a>
                <a href="/admin/messages" class="nav-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-item">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="{{ route('admin.logout') }}" class="nav-item logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Replace the header-actions div in the page-header -->
            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-location-dot"></i>
                            Alumni Tracer
                        </h1>
                        <p class="page-subtitle">Track and manage alumni employment and career progress</p>
                    </div>
                    
                    <div class="header-actions">
                        <div class="quick-actions-header">
                            {{-- <button class="quick-action-btn-sm" onclick="document.querySelector('[data-tab=builder]').click()">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit Tracer</span>
                            </button>
                            <button class="quick-action-btn-sm" onclick="document.querySelector('[data-tab=responses]').click()">
                                <i class="fa-solid fa-inbox"></i>
                                <span>View Responses</span>
                            </button>
                            <button class="quick-action-btn-sm" onclick="document.querySelector('[data-tab=analytics]').click()">
                                <i class="fa-solid fa-chart-bar"></i>
                                <span>Analytics</span>
                            </button> --}}
                            <button class="quick-action-btn-sm">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Send Reminders</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="header-tabs-row">
                    <div class="tracer-tabs">
                        <button class="tracer-tab active" data-tab="dashboard">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </button>
                        <button class="tracer-tab" data-tab="builder">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Tracer Builder</span>
                        </button>
                        <button class="tracer-tab" data-tab="responses">
                            <i class="fa-solid fa-inbox"></i>
                            <span>Responses</span>
                        </button>
                        <button class="tracer-tab" data-tab="analytics">
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Analytics</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- ============================================ -->
            <!-- TAB: DASHBOARD -->
            <!-- ============================================ -->
            <div class="tracer-panel active" id="dashboard-panel">
                <!-- Stats Overview -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="statTotalAlumni">0</span>
                            <span class="stat-label">Total Alumni</span>
                            <span class="stat-sub">--</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="statCompleted">0</span>
                            <span class="stat-label">Completed</span>
                            <span class="stat-sub">--</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon amber">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="statInProgress">0</span>
                            <span class="stat-label">In Progress</span>
                            <span class="stat-sub">--</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="statTotalQuestions">0</span>
                            <span class="stat-label">Total Questions</span>
                            <span class="stat-sub">0 phases · 0 sections</span>
                        </div>
                    </div>
                </div>

                <!-- Replace the dashboard-grid-2col section -->
                <div class="dashboard-grid-2col">
                <div class="chart-card">
                    <h3><i class="fa-solid fa-clock-rotate-left" style="color:#3b82f6;"></i> Recent Activity</h3>
                    <div class="activity-list" id="activityList">
                        <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No recent activity yet.</p>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3><i class="fa-solid fa-bullseye" style="color:#ef4444;"></i> Survey Completion Funnel</h3>
                    <div class="funnel-chart" id="funnelChart">
                        <div class="funnel-item" style="width: 100%;">
                            <span class="funnel-label">Invited</span>
                            <span class="funnel-value">0</span>
                        </div>
                        <div class="funnel-item" style="width: 0%;">
                            <span class="funnel-label">Opened</span>
                            <span class="funnel-value">0</span>
                        </div>
                        <div class="funnel-item" style="width: 0%;">
                            <span class="funnel-label">Completed</span>
                            <span class="funnel-value">0</span>
                        </div>
                        <div class="funnel-item" style="width: 0%;">
                            <span class="funnel-label">Verified</span>
                            <span class="funnel-value">0</span>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Charts Row -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3>Phase Completion Rate</h3>
                        <div class="bar-chart" id="phaseCompletionChart">
                            <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No phases created yet.</p>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3>Employment Status</h3>
                        <div class="employment-list" id="employmentStatusList">
                            <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No employment data yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions Table -->
                <div class="recent-submissions">
                    <div class="recent-submissions-header">
                        <h3>Recent Submissions</h3>
                        <div class="header-actions-table">
                            <select class="filter-select-sm">
                                <option>All Programs</option>
                                <option>BS IT</option>
                                <option>BS ME</option>
                                <option>BS Accountancy</option>
                            </select>
                            <a href="#" class="view-all-link">View All →</a>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="tracer-table">
                            <thead>
                                <tr>
                                    <th>Alumni</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Progress</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentSubmissionsBody">
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--gray-400); padding: 2rem;">
                                        No submissions yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB: TRACER BUILDER (Unchanged) -->
            <!-- ============================================ -->
            <div class="tracer-panel" id="builder-panel">
                <div class="builder-layout">
                    <!-- Phases Sidebar -->
                    <div class="phases-sidebar">
                        <div class="phases-header">
                            <h3>Phases</h3>
                            <button class="btn-add-phase" onclick="openPhaseModal()">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="phases-list" id="phasesList">
                            <!-- Phase cards will be rendered by JS -->
                        </div>
                    </div>

                    <!-- Builder Content Area -->
                    <div class="builder-content" id="builderContent">
                        <div class="empty-builder-state" id="emptyBuilderState">
                            <i class="fa-solid fa-layer-group empty-builder-icon"></i>
                            <h3>Select a phase to manage its content</h3>
                            <p>Choose a phase from the left panel or create a new one to get started.</p>
                        </div>
                        <div id="phaseDetailContent" style="display: none;">
                            <!-- Phase detail content rendered by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB: RESPONSES (Unchanged) -->
            <!-- ============================================ -->
            <div class="tracer-panel" id="responses-panel">
                <div class="responses-toolbar">
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" class="search-input" placeholder="Search by name or program..." id="responsesSearch">
                    </div>
                    <select class="filter-select" id="responsesFilter">
                        <option value="all">All Status</option>
                        <option value="complete">Complete</option>
                        <option value="in-progress">In Progress</option>
                    </select>
                    <button class="btn-export">
                        <i class="fa-solid fa-download"></i> Export CSV
                    </button>
                </div>

                <div class="responses-table-container">
                    <table class="tracer-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Alumni</th>
                                <th>Program</th>
                                <th>Year</th>
                                <th>Completion</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="responsesTableBody">
                            <!-- Rendered by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span id="responsesCount">0 results</span>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB: ANALYTICS -->
            <!-- ============================================ -->
            <div class="tracer-panel" id="analytics-panel">
                <!-- Analytics Header -->
                <div class="analytics-header">
                    <div>
                        <h2 class="analytics-title">Tracer Analytics</h2>
                        <p class="analytics-subtitle">Visualize and analyze alumni responses</p>
                    </div>
                    <div class="analytics-header-actions">
                        <button class="btn-add-chart" onclick="openChartBuilderModal()">
                            <i class="fa-solid fa-plus"></i> Add Chart
                        </button>
                        <button class="btn-export" onclick="exportAnalyticsReport()">
                            <i class="fa-solid fa-file-export"></i> Export Report
                        </button>
                    </div>
                </div>

                <!-- KPI Summary Cards (auto-populated from data) -->
                <div class="analytics-kpi-grid" id="analyticsKpiGrid">
                    <div class="kpi-card">
                        <div class="kpi-icon green"><i class="fa-solid fa-percent"></i></div>
                        <div class="kpi-value" style="color:#10b981;" id="kpiResponseRate">--</div>
                        <div class="kpi-label">Response Rate</div>
                        <div class="kpi-trend" id="kpiResponseRateTrend">--</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon blue"><i class="fa-solid fa-chart-simple"></i></div>
                        <div class="kpi-value" style="color:#3b82f6;" id="kpiAvgCompletion">--</div>
                        <div class="kpi-label">Avg. Completion</div>
                        <div class="kpi-trend" id="kpiAvgCompletionTrend">--</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon amber"><i class="fa-solid fa-users"></i></div>
                        <div class="kpi-value" style="color:#f59e0b;" id="kpiTotalResponses">--</div>
                        <div class="kpi-label">Total Responses</div>
                        <div class="kpi-trend" id="kpiTotalResponsesTrend">--</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon purple"><i class="fa-solid fa-clock"></i></div>
                        <div class="kpi-value" style="color:#8b5cf6;" id="kpiAvgTime">--</div>
                        <div class="kpi-label">Avg. Time to Complete</div>
                        <div class="kpi-trend" id="kpiAvgTimeTrend">--</div>
                    </div>
                </div>

                <!-- Customizable Charts Grid -->
                <div class="analytics-grid" id="analyticsChartsGrid">
                    <!-- Empty state when no charts configured -->
                    <div class="analytics-empty-state" id="analyticsEmptyState">
                        <i class="fa-solid fa-chart-pie"></i>
                        <h3>No charts configured yet</h3>
                        <p>Click "Add Chart" to select questions and visualize response data.</p>
                        <button class="btn btn-primary" onclick="openChartBuilderModal()">
                            Add Your First Chart
                        </button>
                    </div>
                    <!-- Charts will be rendered here dynamically -->
                </div>
            </div>
        </main>
    </div>

    <!-- ============================================ -->
    <!-- UPDATED QUESTION MODAL -->
    <!-- ============================================ -->
    <div class="modal-overlay" id="questionModal">
        <div class="modal-dialog" style="max-width: 700px;">
            <div class="modal-header">
                <h3 id="questionModalTitle">Add New Question</h3>
                <button class="modal-close" onclick="closeQuestionModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <!-- Question Label -->
                <div class="form-group">
                    <label>Question Label <span class="required">*</span></label>
                    <textarea class="form-control" id="qLabel" rows="2" placeholder="Enter the question text..."></textarea>
                </div>

                <!-- Question Type -->
                <div class="form-group">
                    <label>Question Type</label>
                    <select class="form-control" id="qType" onchange="handleTypeChange()">
                        <option value="short_answer">Short Answer</option>
                        <option value="paragraph">Paragraph</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="checkboxes">Checkboxes (Multiple Selection)</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="file_upload">File Upload</option>
                        <option value="likert_scale">Likert Scale</option>
                        <option value="multiple_choice_grid">Multiple Choice Grid</option>
                    </select>
                    <p class="help-text" id="typeHelp">Short single-line text input.</p>
                </div>

                <!-- Options for Multiple Choice / Checkboxes / Dropdown -->
                <div class="form-group" id="optionsGroup" style="display:none;">
                    <label>Answer Options</label>
                    <div id="optionsList" class="options-list"></div>
                    <div class="options-input-row">
                        <input type="text" class="form-control" id="newOption" placeholder="Type an option and press Enter..." onkeydown="handleOptionKeydown(event)">
                        <button class="btn btn-primary" onclick="addOption()" style="white-space:nowrap;">Add</button>
                    </div>
                </div>

                <!-- Placeholder for Short Answer / Paragraph -->
                <div class="form-group" id="placeholderGroup">
                    <label>Placeholder Text</label>
                    <input type="text" class="form-control" id="qPlaceholder" placeholder="e.g. Enter your answer here...">
                </div>

                <!-- File Upload Settings -->
                <div class="form-group" id="fileUploadGroup" style="display:none;">
                    <label>Allowed File Types</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label"><input type="checkbox" value="pdf" class="file-type-check"> PDF</label>
                        <label class="checkbox-label"><input type="checkbox" value="doc" class="file-type-check"> Word (DOC/DOCX)</label>
                        <label class="checkbox-label"><input type="checkbox" value="jpg" class="file-type-check"> Image (JPG/PNG)</label>
                        <label class="checkbox-label"><input type="checkbox" value="xlsx" class="file-type-check"> Excel (XLS/XLSX)</label>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>Max File Size (MB)</label>
                        <input type="number" class="form-control" id="fileMaxSize" placeholder="10" min="1" max="50" value="10">
                    </div>
                </div>

                <!-- Likert Scale / Multiple Choice Grid Settings -->
                <div class="form-group" id="gridSettingsGroup" style="display:none;">
                    <!-- Rows (Statements) -->
                    <div class="form-group">
                        <label>Statements (Rows)</label>
                        <div id="gridRowsList" class="options-list"></div>
                        <div class="options-input-row">
                            <input type="text" class="form-control" id="newGridRow" placeholder="Type a statement and press Enter..." onkeydown="handleGridRowKeydown(event)">
                            <button class="btn btn-primary" onclick="addGridRow()" style="white-space:nowrap;">Add Row</button>
                        </div>
                    </div>

                    <!-- Columns (Options/Scale) -->
                    <div class="form-group">
                        <label id="gridColumnsLabel">Scale Options (Columns)</label>
                        <div id="gridColumnsList" class="options-list"></div>
                        <div class="options-input-row">
                            <input type="text" class="form-control" id="newGridColumn" placeholder="Type an option and press Enter..." onkeydown="handleGridColumnKeydown(event)">
                            <button class="btn btn-primary" onclick="addGridColumn()" style="white-space:nowrap;">Add Column</button>
                        </div>
                    </div>
                </div>

                <!-- Required Toggle -->
                <div class="toggle-row">
                    <div>
                        <p class="toggle-label">Required Question</p>
                        <p class="toggle-sub">Alumni must answer before submitting</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="qRequired" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeQuestionModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveQuestion()">Save Question</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="phaseModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="phaseModalTitle">Add New Phase</h3>
                <button class="modal-close" onclick="closePhaseModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Phase Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="phaseTitle" placeholder="e.g. Personal Profile">
                </div>
                <div class="form-group">
                    <label>Subtitle</label>
                    <input type="text" class="form-control" id="phaseSubtitle" placeholder="e.g. Basic & contact info">
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <div class="icon-grid" id="iconGrid"></div>
                </div>
                <div class="form-group">
                    <label>Accent Color</label>
                    <div class="color-grid" id="colorGrid"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closePhaseModal()">Cancel</button>
                <button class="btn btn-primary" onclick="savePhase()">Save Phase</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="sectionModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="sectionModalTitle">Add New Section</h3>
                <button class="modal-close" onclick="closeSectionModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Section Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="sectionTitle" placeholder="e.g. Basic Information">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" class="form-control" id="sectionDesc" placeholder="Brief description of this section">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveSection()">Save Section</button>
            </div>
        </div>
    </div>

        <!-- ============================================ -->
    <!-- CHART BUILDER MODAL -->
    <!-- ============================================ -->
    <div class="modal-overlay" id="chartBuilderModal">
        <div class="modal-dialog" style="max-width: 650px;">
            <div class="modal-header">
                <h3 id="chartBuilderModalTitle">Add Analytics Chart</h3>
                <button class="modal-close" onclick="closeChartBuilderModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Chart Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="chartTitle" placeholder="e.g. Employment Status Distribution">
                </div>

                <div class="form-group">
                    <label>Chart Type</label>
                    <select class="form-control" id="chartType" onchange="handleChartTypeChange()">
                        <option value="bar">Bar Chart</option>
                        <option value="pie">Pie Chart</option>
                        <option value="horizontal_bar">Horizontal Bar Chart</option>
                        <option value="doughnut">Doughnut Chart</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Question to Visualize <span class="required">*</span></label>
                    <select class="form-control" id="chartQuestion" onchange="handleChartQuestionChange()">
                        <option value="">-- Select a question --</option>
                        <!-- Populated dynamically from tracer questions -->
                    </select>
                    <p class="help-text" id="chartQuestionHelp">Choose a multiple choice, checkbox, dropdown, or grid question.</p>
                </div>

                <div class="form-group" id="chartPhaseFilterGroup">
                    <label>Filter by Phase (optional)</label>
                    <select class="form-control" id="chartPhaseFilter">
                        <option value="">All Phases</option>
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="form-group" id="chartColorGroup">
                    <label>Color Scheme</label>
                    <div class="color-scheme-grid" id="colorSchemeGrid">
                        <button class="color-scheme-option selected" data-scheme="default" onclick="selectColorScheme('default', this)">
                            <span style="background:#3b82f6;"></span>
                            <span style="background:#10b981;"></span>
                            <span style="background:#f59e0b;"></span>
                            <span style="background:#8b5cf6;"></span>
                            <span style="background:#ef4444;"></span>
                        </button>
                        <button class="color-scheme-option" data-scheme="ocean" onclick="selectColorScheme('ocean', this)">
                            <span style="background:#06b6d4;"></span>
                            <span style="background:#0ea5e9;"></span>
                            <span style="background:#3b82f6;"></span>
                            <span style="background:#6366f1;"></span>
                            <span style="background:#8b5cf6;"></span>
                        </button>
                        <button class="color-scheme-option" data-scheme="forest" onclick="selectColorScheme('forest', this)">
                            <span style="background:#22c55e;"></span>
                            <span style="background:#10b981;"></span>
                            <span style="background:#059669;"></span>
                            <span style="background:#047857;"></span>
                            <span style="background:#065f46;"></span>
                        </button>
                        <button class="color-scheme-option" data-scheme="sunset" onclick="selectColorScheme('sunset', this)">
                            <span style="background:#f97316;"></span>
                            <span style="background:#ef4444;"></span>
                            <span style="background:#ec4899;"></span>
                            <span style="background:#a855f7;"></span>
                            <span style="background:#6366f1;"></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeChartBuilderModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveChart()">Add Chart</button>
            </div>
        </div>
    </div>

    <script>
    // ═══════════════════════════════════════
    // GLOBAL STATE
    // ═══════════════════════════════════════
    
    const typeHelpText = {
        short_answer: 'Short single-line text input for brief answers.',
        paragraph: 'Multi-line text area for longer responses.',
        multiple_choice: 'Alumni pick exactly one answer from the options.',
        checkboxes: 'Alumni can select multiple answers.',
        dropdown: 'Alumni choose one option from a dropdown menu.',
        file_upload: 'Alumni can upload a file (PDF, image, document, etc.).',
        likert_scale: 'Matrix with statements (rows) and a fixed scale (columns). Default scale: 1-5.',
        multiple_choice_grid: 'Matrix with statements (rows) and customizable option columns. Like Likert but you define the column labels.'
    };

    const typeLabels = {
        short_answer: 'Short Answer',
        paragraph: 'Paragraph',
        multiple_choice: 'Multiple Choice',
        checkboxes: 'Checkboxes',
        dropdown: 'Dropdown',
        file_upload: 'File Upload',
        likert_scale: 'Likert Scale',
        multiple_choice_grid: 'Multiple Choice Grid'
    };

    const typeIcons = {
        short_answer: 'fa-i-cursor',
        paragraph: 'fa-align-left',
        multiple_choice: 'fa-circle-dot',
        checkboxes: 'fa-check-square',
        dropdown: 'fa-chevron-down',
        file_upload: 'fa-cloud-arrow-up',
        likert_scale: 'fa-table-list',
        multiple_choice_grid: 'fa-border-all'
    };

    const DEFAULT_LIKERT_COLUMNS = [
        '1 - Strongly Disagree',
        '2 - Disagree', 
        '3 - Neutral',
        '4 - Agree',
        '5 - Strongly Agree'
    ];

    const icons = [
        { key: 'fa-user', label: 'Person' },
        { key: 'fa-book', label: 'Book' },
        { key: 'fa-briefcase', label: 'Briefcase' },
        { key: 'fa-bolt', label: 'Lightning' },
        { key: 'fa-clipboard-check', label: 'Clipboard' },
        { key: 'fa-graduation-cap', label: 'Graduation' },
        { key: 'fa-chart-line', label: 'Growth' },
        { key: 'fa-star', label: 'Stars' }
    ];

    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#1f2b67', '#ec4899'];

    let phases = [];

    const MOCK_RESPONSES = [];

    let selectedPhaseId = null;
    let expandedSections = new Set();
    let currentEditQuestion = null;
    let currentEditPhase = null;
    let currentEditSection = null;
    let tempOptions = [];
    let tempGridRows = [];
    let tempGridColumns = [];
    let selectedIcon = 'fa-user';
    let selectedColor = '#3b82f6';

    let analyticsCharts = []; // Configured charts by admin

        function getDefaultCharts() {
        // Returns default charts based on questions found in phases
        const charts = [];
        
        for (const phase of phases) {
            for (const section of phase.sections) {
                for (const q of section.questions) {
                    if (q.type === 'multiple_choice' || q.type === 'dropdown') {
                        charts.push({
                            title: q.label,
                            type: 'pie',
                            questionId: q.id,
                            phaseFilter: null,
                            colorScheme: 'default',
                        });
                    } else if (q.type === 'likert_scale' || q.type === 'multiple_choice_grid') {
                        charts.push({
                            title: q.label,
                            type: 'horizontal_bar',
                            questionId: q.id,
                            phaseFilter: null,
                            colorScheme: 'default',
                        });
                    } else if (q.type === 'checkboxes') {
                        charts.push({
                            title: q.label,
                            type: 'bar',
                            questionId: q.id,
                            phaseFilter: null,
                            colorScheme: 'default',
                        });
                    } else if (q.type === 'short_answer' || q.type === 'paragraph') {
                        charts.push({
                            title: q.label,
                            type: 'bar',
                            questionId: q.id,
                            phaseFilter: null,
                            colorScheme: 'ocean',
                        });
                    }
                }
            }
        }
        
        // Limit to first 6 charts to avoid overwhelming the dashboard
        return charts.slice(0, 6);
    }

    let currentEditingChart = null;
    let selectedColorScheme = 'default';

    const colorSchemes = {
        default: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#f97316', '#84cc16', '#14b8a6'],
        ocean: ['#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#f97316'],
        forest: ['#22c55e', '#10b981', '#059669', '#047857', '#065f46', '#84cc16', '#a3e635', '#65a30d', '#4d7c0f', '#3f6212'],
        sunset: ['#f97316', '#ef4444', '#ec4899', '#a855f7', '#6366f1', '#3b82f6', '#0ea5e9', '#06b6d4', '#10b981', '#84cc16']
    };

        // ═══════════════════════════════════════
    // API HELPERS
    // ═══════════════════════════════════════

    const API_BASE = '/admin/alumni_tracer';

    async function apiFetch(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                ...options,
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Request failed' }));
                throw new Error(error.message || `HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async function loadForms() {
        const forms = await apiFetch(`${API_BASE}/list`);
        if (forms.length > 0) {
            const form = await apiFetch(`${API_BASE}/${forms[0].id}`);
            phases = mapFormToPhases(form);
            selectedPhaseId = phases.length > 0 ? phases[0].id : null;
            // Auto-generate default charts
            analyticsCharts = getDefaultCharts();
        } else {
            phases = [];
            selectedPhaseId = null;
            analyticsCharts = [];
        }
        renderBuilder();
    }

    async function saveFormToBackend() {
        const payload = mapPhasesToPayload();
        const existingForms = await apiFetch(`${API_BASE}/list`);
        
        if (existingForms.length > 0) {
            const formId = existingForms[0].id;
            await apiFetch(`${API_BASE}/${formId}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
        } else {
            await apiFetch(API_BASE, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
        }
    }

    function mapFormToPhases(form) {
        if (!form.phases) return [];
        return form.phases.map(phase => ({
            id: phase.id,
            title: phase.title,
            subtitle: phase.subtitle || '',
            icon: phase.icon || 'fa-user',
            color: phase.color || '#3b82f6',
            sections: (phase.sections || []).map(section => ({
                id: section.id,  // ✅ Use database ID, not generated
                title: section.title,
                description: section.description || '',
                questions: (section.questions || []).map(q => {
                    const question = {
                        id: q.id,  // ✅ Use database ID
                        label: q.question_text,
                        type: q.type,
                        placeholder: q.placeholder || '',
                        required: q.is_required,
                    };
                    
                    if (q.options && q.options.length > 0) {
                        question.options = q.options.map(o => o.option_label);
                    }
                    
                    if (q.file_types) {
                        question.fileTypes = q.file_types;
                    }
                    if (q.max_file_size) {
                        question.maxSize = q.max_file_size;
                    }
                    
                    if (q.grid_rows && q.grid_rows.length > 0) {
                        question.gridRows = q.grid_rows.map(r => r.row_label);
                    }
                    if (q.grid_columns && q.grid_columns.length > 0) {
                        question.gridColumns = q.grid_columns.map(c => c.column_label);
                    }
                    
                    return question;
                })
            }))
        }));
    }

    function mapPhasesToPayload() {
        return {
            form_title: 'Alumni Tracer',
            form_description: 'Alumni tracer survey form',
            status: 2, // draft
            phases: phases.map(phase => ({
                title: phase.title,
                subtitle: phase.subtitle || '',
                icon: phase.icon || 'fa-user',
                color: phase.color || '#3b82f6',
                sections: (phase.sections || []).map(section => ({
                    title: section.title,
                    description: section.description || '',
                    questions: (section.questions || []).map(q => {
                        const question = {
                            question_text: q.label,
                            type: q.type,
                            is_required: q.required,
                            placeholder: q.placeholder || null,
                        };
                        
                        if (q.options && q.options.length > 0) {
                            question.options = q.options.map(opt => ({ label: opt }));
                        }
                        
                        if (q.type === 'file_upload') {
                            question.file_types = q.fileTypes || [];
                            question.max_file_size = q.maxSize || 10;
                        }
                        
                        if (q.gridRows && q.gridRows.length > 0) {
                            question.grid_rows = q.gridRows.map(row => ({ label: row }));
                        }
                        if (q.gridColumns && q.gridColumns.length > 0) {
                            question.grid_columns = q.gridColumns.map(col => ({ label: col }));
                        }
                        
                        return question;
                    })
                }))
            }))
        };
    }

    // ═══════════════════════════════════════
    // MOBILE MENU
    // ═══════════════════════════════════════

    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) toggleMobileMenu();
        });
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            document.getElementById('adminSidebar').classList.remove('mobile-open');
            document.getElementById('mobileOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // ═══════════════════════════════════════
    // TAB SWITCHING
    // ═══════════════════════════════════════

    document.querySelectorAll('.tracer-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tracer-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tracer-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab + '-panel').classList.add('active');

            if (this.dataset.tab === 'builder') renderBuilder();
            if (this.dataset.tab === 'responses') renderResponsesTable();
            if (this.dataset.tab === 'analytics') renderAnalytics();
        });
    });

    // ═══════════════════════════════════════
    // BUILDER RENDERING
    // ═══════════════════════════════════════

    function renderPhasesList() {
        const container = document.getElementById('phasesList');
        
        if (!phases || phases.length === 0) {
            container.innerHTML = `
                <div style="padding: 1.5rem; text-align: center; color: var(--gray-400); font-size: 0.8125rem;">
                    <i class="fa-solid fa-folder-open" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;"></i>
                    No phases yet. Click "Add" to create one.
                </div>
            `;
            return;
        }
        
        container.innerHTML = phases.map(phase => {
            const isSel = selectedPhaseId === phase.id;
            const totalQ = phase.sections.reduce((s, sec) => s + sec.questions.length, 0);
            return `
                <div class="phase-card ${isSel ? 'active' : ''}" onclick="selectPhase(${phase.id})">
                    <div class="phase-card-header">
                        <div class="phase-card-icon" style="background:${isSel ? 'rgba(255,255,255,0.15)' : phase.color + '20'}; color:${isSel ? '#fff' : phase.color};">
                            <i class="fa-solid ${phase.icon}"></i>
                        </div>
                        <div class="phase-card-info">
                            <p class="phase-card-title">${phase.title}</p>
                            <p class="phase-card-meta">${phase.sections.length} sections · ${totalQ} questions</p>
                        </div>
                        <div class="phase-card-actions">
                            <button onclick="event.stopPropagation(); openPhaseModal(${phase.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                            <button onclick="event.stopPropagation(); deletePhase(${phase.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function selectPhase(id) {
        selectedPhaseId = id;
        renderBuilder();
    }

    function renderBuilder() {
        renderPhasesList();
        const phase = phases.find(p => p.id === selectedPhaseId);
        const emptyState = document.getElementById('emptyBuilderState');
        const detailContent = document.getElementById('phaseDetailContent');

        if (!phase) {
            emptyState.style.display = 'block';
            detailContent.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        detailContent.style.display = 'block';

        detailContent.innerHTML = `
            <div class="builder-content-header">
                <div class="builder-phase-info">
                    <div class="builder-phase-icon" style="background:${phase.color}20; color:${phase.color};">
                        <i class="fa-solid ${phase.icon}"></i>
                    </div>
                    <div class="builder-phase-details">
                        <h2>${phase.title}</h2>
                        <p>${phase.subtitle || 'No subtitle'}</p>
                    </div>
                </div>
                <button class="btn-add-section" onclick="openSectionModal(null, ${phase.id})">
                    <i class="fa-solid fa-plus"></i> Add Section
                </button>
            </div>
            ${phase.sections.length === 0 ? `
                <div class="empty-builder-state" style="border: 2px dashed var(--gray-200); border-radius: var(--radius-xl); padding: 3rem;">
                    <i class="fa-solid fa-folder-plus" style="font-size: 3rem; color: var(--gray-300);"></i>
                    <p style="color: var(--gray-500); font-weight: 500; margin-top: 0.75rem;">No sections yet</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; margin-top: 0.25rem;">Click "Add Section" to get started.</p>
                </div>
            ` : `
                <div class="sections-list">
                    ${phase.sections.map((section, secIdx) => {
                        const isOpen = expandedSections.has(section.id);
                        return `
                            <div class="section-card ${isOpen ? 'expanded' : ''}">
                                <div class="section-header" onclick="toggleSection('${section.id}')">
                                    <div class="section-number" style="background:${phase.color};">${secIdx + 1}</div>
                                    <div class="section-info">
                                        <h4>${section.title}</h4>
                                        <p>${section.description ? section.description + ' · ' : ''}${section.questions.length} question${section.questions.length !== 1 ? 's' : ''}</p>
                                    </div>
                                    <div class="section-actions" onclick="event.stopPropagation();">
                                        <button class="btn-icon" onclick="openSectionModal('${section.id}', ${phase.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-icon delete" onclick="deleteSection(${phase.id}, '${section.id}')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                    <i class="fa-solid fa-chevron-${isOpen ? 'up' : 'down'} section-toggle"></i>
                                </div>
                                <div class="section-body">
                                    ${section.questions.length === 0 ? '<p style="color: var(--gray-400); text-align: center; padding: 1.5rem;">No questions yet.</p>' : `
                                        <div class="questions-list">
                                            ${section.questions.map((q, qi) => `
                                                <div class="question-item">
                                                    <i class="fa-solid fa-grip-vertical question-drag"></i>
                                                    <div class="question-number" style="background:${phase.color};">${qi + 1}</div>
                                                    <div class="question-content">
                                                        <p class="question-label-text">${q.label}</p>
                                                        <div class="question-meta">
                                                            <span class="question-type-badge"><i class="fa-solid ${typeIcons[q.type] || 'fa-question'}"></i> ${typeLabels[q.type] || q.type}</span>
                                                            ${q.required ? '<span class="required-badge">Required</span>' : ''}
                                                            ${q.options ? `<span style="font-size:0.6875rem;color:var(--gray-400);">${q.options.length} options</span>` : ''}
                                                            ${q.gridRows ? `<span style="font-size:0.6875rem;color:var(--gray-400);">${q.gridRows.length} rows</span>` : ''}
                                                        </div>
                                                    </div>
                                                    <div class="question-actions">
                                                        <button onclick="openQuestionModal(${phase.id}, '${section.id}', ${JSON.stringify(q).replace(/"/g, '&quot;')})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                                        <button class="delete-question" onclick="deleteQuestion(${phase.id}, '${section.id}', '${q.id}')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    `}
                                    <button class="add-question-btn" onclick="openQuestionModal(${phase.id}, '${section.id}', null)">
                                        <i class="fa-solid fa-plus"></i> Add Question
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `}
        `;
    }

    function toggleSection(sectionId) {
        // Convert to number for consistent Set operations (database IDs are integers)
        const id = Number(sectionId);
        if (expandedSections.has(id)) {
            expandedSections.delete(id);
        } else {
            expandedSections.add(id);
        }
        renderBuilder();
    }

    function deletePhase(id) {
        if (!confirm('Delete this phase and all its content?')) return;
        phases = phases.filter(p => p.id !== id);
        if (selectedPhaseId === id) selectedPhaseId = phases.length > 0 ? phases[0].id : null;
        renderBuilder();
        saveFormToBackend(); // Auto-save
    }

    function deleteSection(phaseId, secId) {
        if (!confirm('Delete this section?')) return;
        phases = phases.map(p => {
            if (Number(p.id) !== Number(phaseId)) return p;
            return { ...p, sections: p.sections.filter(s => Number(s.id) !== Number(secId)) };
        });
        expandedSections.delete(Number(secId));
        renderBuilder();
        saveFormToBackend();
    }

    function deleteQuestion(phaseId, secId, qId) {
        if (!confirm('Delete this question?')) return;
        phases = phases.map(p => {
            if (Number(p.id) !== Number(phaseId)) return p;
            return {
                ...p,
                sections: p.sections.map(s => {
                    if (Number(s.id) !== Number(secId)) return s;
                    return { ...s, questions: s.questions.filter(q => Number(q.id) !== Number(qId)) };
                })
            };
        });
        renderBuilder();
        saveFormToBackend();
    }

    // ═══════════════════════════════════════
    // QUESTION MODAL (UPDATED)
    // ═══════════════════════════════════════

    function openQuestionModal(phaseId, secId, question) {
        currentEditQuestion = { phaseId, secId, question };
        
        // Reset temp arrays
        tempOptions = question?.options ? [...question.options] : [];
        tempGridRows = question?.gridRows ? [...question.gridRows] : [];
        tempGridColumns = question?.gridColumns ? [...question.gridColumns] : [];
        
        // For likert_scale, set default columns if empty
        if (question?.type === 'likert_scale' && tempGridColumns.length === 0) {
            tempGridColumns = [...DEFAULT_LIKERT_COLUMNS];
        }
        
        document.getElementById('questionModalTitle').textContent = question ? 'Edit Question' : 'Add New Question';
        document.getElementById('qLabel').value = question ? question.label : '';
        document.getElementById('qType').value = question ? question.type : 'short_answer';
        document.getElementById('qPlaceholder').value = question?.placeholder || '';
        document.getElementById('qRequired').checked = question ? !!question.required : true;
        
        // File upload settings
        if (question?.type === 'file_upload') {
            document.querySelectorAll('.file-type-check').forEach(cb => {
                cb.checked = question.fileTypes ? question.fileTypes.includes(cb.value) : false;
            });
            document.getElementById('fileMaxSize').value = question.maxSize || 10;
        } else {
            document.querySelectorAll('.file-type-check').forEach(cb => cb.checked = false);
            document.getElementById('fileMaxSize').value = 10;
        }
        
        handleTypeChange();
        renderOptionsList();
        renderGridRowsList();
        renderGridColumnsList();
        
        document.getElementById('questionModal').classList.add('active');
    }

    function closeQuestionModal() {
        document.getElementById('questionModal').classList.remove('active');
        currentEditQuestion = null;
        tempOptions = [];
        tempGridRows = [];
        tempGridColumns = [];
    }

    function handleTypeChange() {
        const type = document.getElementById('qType').value;
        const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(type);
        const isTextType = ['short_answer', 'paragraph'].includes(type);
        const isFileType = type === 'file_upload';
        const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(type);
        
        // Show/hide groups
        document.getElementById('optionsGroup').style.display = isChoiceType ? 'block' : 'none';
        document.getElementById('placeholderGroup').style.display = isTextType ? 'block' : 'none';
        document.getElementById('fileUploadGroup').style.display = isFileType ? 'block' : 'none';
        document.getElementById('gridSettingsGroup').style.display = isGridType ? 'block' : 'none';
        
        // Update help text
        document.getElementById('typeHelp').textContent = typeHelpText[type] || '';
        
        // Update grid columns label
        document.getElementById('gridColumnsLabel').textContent = 
            type === 'likert_scale' ? 'Scale Options (Columns)' : 'Choice Options (Columns)';
        
        // Set defaults for grid types
        if (isGridType) {
            if (tempGridColumns.length === 0) {
                tempGridColumns = type === 'likert_scale' ? [...DEFAULT_LIKERT_COLUMNS] : ['Option 1'];
            }
            renderGridColumnsList();
        }
        
        // Clear irrelevant temp data
        if (!isChoiceType && !isGridType) tempOptions = [];
        if (!isGridType) {
            tempGridRows = [];
            tempGridColumns = [];
        }
    }

    // ═══════════════════════════════════════
    // OPTIONS MANAGEMENT (Multiple Choice / Checkboxes / Dropdown)
    // ═══════════════════════════════════════

    function renderOptionsList() {
        const container = document.getElementById('optionsList');
        if (!container) return;
        container.innerHTML = tempOptions.map((opt, i) => `
            <div class="option-item">
                <i class="fa-solid fa-grip-vertical" style="color:var(--gray-300);"></i>
                <span>${opt}</span>
                <button onclick="removeOption(${i})" style="color:var(--danger); cursor:pointer; background:none; border:none;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');
    }

    function addOption() {
        const input = document.getElementById('newOption');
        const val = input.value.trim();
        if (val) {
            tempOptions.push(val);
            input.value = '';
            renderOptionsList();
        }
    }

    function handleOptionKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addOption();
        }
    }

    function removeOption(index) {
        tempOptions.splice(index, 1);
        renderOptionsList();
    }

    // ═══════════════════════════════════════
    // GRID ROWS MANAGEMENT (Likert / Multiple Choice Grid)
    // ═══════════════════════════════════════

    function renderGridRowsList() {
        const container = document.getElementById('gridRowsList');
        if (!container) return;
        container.innerHTML = tempGridRows.map((row, i) => `
            <div class="option-item">
                <i class="fa-solid fa-grip-vertical" style="color:var(--gray-300);"></i>
                <span>${row}</span>
                <button onclick="removeGridRow(${i})" style="color:var(--danger); cursor:pointer; background:none; border:none;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');
    }

    function addGridRow() {
        const input = document.getElementById('newGridRow');
        const val = input.value.trim();
        if (val) {
            tempGridRows.push(val);
            input.value = '';
            renderGridRowsList();
        }
    }

    function handleGridRowKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addGridRow();
        }
    }

    function removeGridRow(index) {
        tempGridRows.splice(index, 1);
        renderGridRowsList();
    }

    // ═══════════════════════════════════════
    // GRID COLUMNS MANAGEMENT (Likert / Multiple Choice Grid)
    // ═══════════════════════════════════════

    function renderGridColumnsList() {
        const container = document.getElementById('gridColumnsList');
        if (!container) return;
        container.innerHTML = tempGridColumns.map((col, i) => `
            <div class="option-item">
                <i class="fa-solid fa-grip-vertical" style="color:var(--gray-300);"></i>
                <span>${col}</span>
                <button onclick="removeGridColumn(${i})" style="color:var(--danger); cursor:pointer; background:none; border:none;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');
    }

    function addGridColumn() {
        const input = document.getElementById('newGridColumn');
        const val = input.value.trim();
        if (val) {
            tempGridColumns.push(val);
            input.value = '';
            renderGridColumnsList();
        }
    }

    function handleGridColumnKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addGridColumn();
        }
    }

    function removeGridColumn(index) {
        tempGridColumns.splice(index, 1);
        renderGridColumnsList();
    }

    // ═══════════════════════════════════════
    // SAVE QUESTION (UPDATED)
    // ═══════════════════════════════════════

    function saveQuestion() {
        const label = document.getElementById('qLabel').value.trim();
        if (!label) {
            alert('Please enter a question label.');
            return;
        }
        
        const type = document.getElementById('qType').value;
        const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(type);
        const isTextType = ['short_answer', 'paragraph'].includes(type);
        const isFileType = type === 'file_upload';
        const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(type);
        
        if (isChoiceType && tempOptions.length < 2) {
            alert('Please add at least 2 options for this question type.');
            return;
        }
        
        if (isGridType) {
            if (tempGridRows.length === 0) {
                alert('Please add at least 1 statement (row) for the grid.');
                return;
            }
            if (tempGridColumns.length === 0) {
                alert('Please add at least 1 option (column) for the grid.');
                return;
            }
        }
        
        let fileTypes = [];
        let maxSize = 10;
        if (isFileType) {
            document.querySelectorAll('.file-type-check:checked').forEach(cb => fileTypes.push(cb.value));
            maxSize = parseInt(document.getElementById('fileMaxSize').value) || 10;
        }
        
        const qData = {
            id: currentEditQuestion.question ? currentEditQuestion.question.id : 'q-' + Date.now(),
            label,
            type,
            required: document.getElementById('qRequired').checked
        };

        if (isChoiceType) qData.options = [...tempOptions];
        if (isTextType) {
            const placeholder = document.getElementById('qPlaceholder').value.trim();
            if (placeholder) qData.placeholder = placeholder;
        }
        if (isFileType) {
            qData.fileTypes = fileTypes;
            qData.maxSize = maxSize;
        }
        if (isGridType) {
            qData.gridRows = [...tempGridRows];
            qData.gridColumns = [...tempGridColumns];
        }

        const { phaseId, secId, question } = currentEditQuestion;
        
        phases = phases.map(p => {
            if (Number(p.id) !== Number(phaseId)) return p;
            return {
                ...p,
                sections: p.sections.map(s => {
                    if (Number(s.id) !== Number(secId)) return s;
                    return {
                        ...s,
                        questions: question
                            ? s.questions.map(q => Number(q.id) === Number(question.id) ? qData : q)
                            : [...s.questions, qData]
                    };
                })
            };
        });

        closeQuestionModal();
        renderBuilder();
        saveFormToBackend();
    }

    // ═══════════════════════════════════════
    // PHASE MODAL
    // ═══════════════════════════════════════

    function openPhaseModal(phaseId) {
        const phase = phaseId ? phases.find(p => p.id === phaseId) : null;
        currentEditPhase = phase;
        selectedIcon = phase ? phase.icon : 'fa-user';
        selectedColor = phase ? phase.color : '#3b82f6';
        
        document.getElementById('phaseModalTitle').textContent = phase ? 'Edit Phase' : 'Add New Phase';
        document.getElementById('phaseTitle').value = phase ? phase.title : '';
        document.getElementById('phaseSubtitle').value = phase ? phase.subtitle : '';
        
        renderIconGrid();
        renderColorGrid();
        
        document.getElementById('phaseModal').classList.add('active');
    }

    function closePhaseModal() {
        document.getElementById('phaseModal').classList.remove('active');
        currentEditPhase = null;
    }

    function renderIconGrid() {
        document.getElementById('iconGrid').innerHTML = icons.map(ic => `
            <button onclick="selectIcon('${ic.key}')" class="icon-option ${selectedIcon === ic.key ? 'selected' : ''}">
                <i class="fa-solid ${ic.key}" style="color:${selectedIcon === ic.key ? '#32418C' : '#6b7280'};"></i>
                <span>${ic.label}</span>
            </button>
        `).join('');
    }

    function selectIcon(key) {
        selectedIcon = key;
        renderIconGrid();
    }

    function renderColorGrid() {
        document.getElementById('colorGrid').innerHTML = colors.map(c => `
            <button onclick="selectedColor = '${c}'; renderColorGrid();" class="color-option ${selectedColor === c ? 'selected' : ''}" style="background:${c};"></button>
        `).join('');
    }

    function savePhase() {
        const title = document.getElementById('phaseTitle').value.trim();
        if (!title) return;
        
        const data = {
            title,
            subtitle: document.getElementById('phaseSubtitle').value.trim(),
            icon: selectedIcon,
            color: selectedColor
        };

        if (currentEditPhase) {
            phases = phases.map(p => p.id === currentEditPhase.id ? { ...p, ...data } : p);
        } else {
            const newId = phases.length > 0 ? Math.max(0, ...phases.map(p => p.id)) + 1 : 1;
            phases.push({ id: newId, ...data, sections: [] });
            selectedPhaseId = newId;
        }

        closePhaseModal();
        renderBuilder();
        saveFormToBackend(); // Auto-save
    }

    // ═══════════════════════════════════════
    // SECTION MODAL
    // ═══════════════════════════════════════

    function openSectionModal(sectionId, phaseId) {
        // Convert both to numbers for reliable comparison
        const secId = sectionId ? Number(sectionId) : null;
        const phId = Number(phaseId);
        
        const section = secId ? phases.find(p => p.id === phId)?.sections.find(s => Number(s.id) === secId) : null;
        currentEditSection = { section, phaseId: phId };
        
        document.getElementById('sectionModalTitle').textContent = section ? 'Edit Section' : 'Add New Section';
        document.getElementById('sectionTitle').value = section ? section.title : '';
        document.getElementById('sectionDesc').value = section ? (section.description || '') : '';
        
        document.getElementById('sectionModal').classList.add('active');
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.remove('active');
        currentEditSection = null;
    }

    function saveSection() {
        const title = document.getElementById('sectionTitle').value.trim();
        if (!title) return;
        
        const data = {
            title,
            description: document.getElementById('sectionDesc').value.trim()
        };

        const { section, phaseId } = currentEditSection;

        phases = phases.map(p => {
            if (Number(p.id) !== Number(phaseId)) return p;
            return {
                ...p,
                sections: section
                    ? p.sections.map(s => Number(s.id) === Number(section.id) ? { ...s, ...data } : s)
                    : [...p.sections, { id: phaseId + '-' + Date.now(), ...data, questions: [] }]
            };
        });

        closeSectionModal();
        renderBuilder();
        saveFormToBackend();
    }

    // ═══════════════════════════════════════
    // RESPONSES TABLE
    // ═══════════════════════════════════════

    function renderResponsesTable(filteredData) {
        const data = filteredData || MOCK_RESPONSES;
        const tbody = document.getElementById('responsesTableBody');
        
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--gray-400); padding: 3rem;">
                        <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.75rem;"></i>
                        No responses found.
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = data.map(r => `
                <tr>
                    <td style="color:var(--gray-400);font-size:0.8125rem;">${r.id}</td>
                    <td>
                        <div class="alumni-info">
                            <div class="alumni-avatar">${r.name.split(' ').map(n => n[0]).join('')}</div>
                            <span class="alumni-name">${r.name}</span>
                        </div>
                    </td>
                    <td>${r.program}</td>
                    <td>${r.year}</td>
                    <td>
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:${r.completion}%; background:${r.completion === 100 ? '#10b981' : '#f59e0b'};"></div></div>
                            <span class="progress-text" style="color:${r.completion === 100 ? '#10b981' : '#f59e0b'};">${r.completion}%</span>
                        </div>
                    </td>
                    <td>${r.date}</td>
                    <td><span class="status-badge ${r.status}">${r.status === 'complete' ? 'Complete' : 'In Progress'}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-icon delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        document.getElementById('responsesCount').textContent = `${data.length} result${data.length !== 1 ? 's' : ''}`;
    }

    // Responses search & filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('responsesSearch');
        const filterSelect = document.getElementById('responsesFilter');

        if (searchInput) {
            searchInput.addEventListener('input', filterResponses);
        }
        if (filterSelect) {
            filterSelect.addEventListener('change', filterResponses);
        }

        function filterResponses() {
            const query = (searchInput?.value || '').toLowerCase();
            const status = filterSelect?.value || 'all';
            
            const filtered = MOCK_RESPONSES.filter(r => {
                const matchesSearch = r.name.toLowerCase().includes(query) || r.program.toLowerCase().includes(query);
                const matchesStatus = status === 'all' || r.status === status;
                return matchesSearch && matchesStatus;
            });
            
            renderResponsesTable(filtered);
        }

        // Show loading state in phases sidebar
                // Show loading state in phases sidebar
        const phasesList = document.getElementById('phasesList');
        phasesList.innerHTML = `
            <div class="skeleton-phase">
                <div class="skeleton-phase-inner">
                    <div class="skeleton-icon"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            </div>
            <div class="skeleton-phase">
                <div class="skeleton-phase-inner">
                    <div class="skeleton-icon"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            </div>
            <div class="skeleton-phase">
                <div class="skeleton-phase-inner">
                    <div class="skeleton-icon"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            </div>
        `;

        // Show loading in the center of the builder content area
        const emptyState = document.getElementById('emptyBuilderState');
        const detailContent = document.getElementById('phaseDetailContent');
        const builderContent = document.getElementById('builderContent');
        
        // Make builder-content fill its parent
        builderContent.style.cssText = 'display: flex; flex-direction: column; flex: 1; height: 100%; position: relative;';
        
        emptyState.innerHTML = `
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                <div class="loading-spinner" style="margin: 0 auto 0.75rem auto;"></div>
                <p class="loading-text">Loading tracer form...</p>
            </div>
        `;
        emptyState.style.cssText = 'position: relative; flex: 1; width: 100%;';
        detailContent.style.display = 'none';

        // Load tracer form from backend
        loadForms().then(() => {
            renderResponsesTable();
        }).catch(err => {
            console.error('Failed to load tracer form:', err);
            renderBuilder();
            renderResponsesTable();
        });
    });

    // ═══════════════════════════════════════
    // MODAL CLOSE ON OVERLAY CLICK
    // ═══════════════════════════════════════

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    // ESC to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        }
    });

        // ═══════════════════════════════════════
    // ANALYTICS RENDERING
    // ═══════════════════════════════════════

    /**
     * Render the analytics tab - called when switching to analytics tab or when charts change.
     */
    function renderAnalytics() {
        renderAnalyticsKPIs();
        renderAnalyticsChartsGrid();
        updateChartQuestionDropdown();
    }

    /**
     * Populate KPI cards from actual data.
     */
    function renderAnalyticsKPIs() {
        const totalAlumni = 0; // TODO: Fetch from backend
        const totalResponses = MOCK_RESPONSES.length;
        const completedResponses = MOCK_RESPONSES.filter(r => r.status === 'complete').length;
        const responseRate = totalAlumni > 0 ? Math.round((totalResponses / totalAlumni) * 100) : 0;
        const avgCompletion = totalResponses > 0 
            ? Math.round(MOCK_RESPONSES.reduce((sum, r) => sum + r.completion, 0) / totalResponses) 
            : 0;

        document.getElementById('kpiResponseRate').textContent = totalAlumni > 0 ? responseRate + '%' : '--';
        document.getElementById('kpiAvgCompletion').textContent = totalResponses > 0 ? avgCompletion + '%' : '--';
        document.getElementById('kpiTotalResponses').textContent = totalResponses || '--';
        document.getElementById('kpiAvgTime').textContent = '--'; // TODO: calculate from submitted_at timestamps
        document.getElementById('kpiResponseRateTrend').textContent = '--';
        document.getElementById('kpiAvgCompletionTrend').textContent = '--';
        document.getElementById('kpiTotalResponsesTrend').textContent = '--';
        document.getElementById('kpiAvgTimeTrend').textContent = '--';
    }

    /**
     * Render the charts grid with all configured charts.
     */
    function renderAnalyticsChartsGrid() {
        const grid = document.getElementById('analyticsChartsGrid');
        const emptyState = document.getElementById('analyticsEmptyState');

        if (analyticsCharts.length === 0) {
            emptyState.style.display = 'flex';
            // Remove any existing chart cards except empty state
            grid.querySelectorAll('.analytics-card').forEach(c => c.remove());
            return;
        }

        emptyState.style.display = 'none';

        // Remove existing chart cards
        grid.querySelectorAll('.analytics-card').forEach(c => c.remove());

        // Render each configured chart
        analyticsCharts.forEach((chart, index) => {
            const card = document.createElement('div');
            card.className = 'analytics-card';
            card.innerHTML = `
                <div class="analytics-card-header">
                    <h3>${chart.title}</h3>
                    <div class="analytics-card-actions">
                        <button class="btn-icon" onclick="editChart(${index})" title="Edit Chart">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn-icon delete" onclick="deleteChart(${index})" title="Remove Chart">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-container" id="chartContainer${index}">
                    ${generateChartHTML(chart, index)}
                </div>
            `;
            grid.appendChild(card);
        });
    }

    /**
     * Generate chart HTML based on chart type and question data.
     */
    function generateChartHTML(chart, index) {
        const question = findQuestionById(chart.questionId);
        if (!question) {
            return `<p style="color: var(--gray-400); text-align: center; padding: 2rem;">Question not found. It may have been deleted.</p>`;
        }

        let labels = [];
        let values = [];

        if (question.options && question.options.length > 0) {
            // Multiple choice, checkboxes, dropdown
            labels = question.options;
            values = labels.map(() => Math.floor(Math.random() * 50) + 5);
        } else if (question.gridRows && question.gridColumns) {
            // Likert scale or multiple choice grid
            labels = question.gridRows;
            values = labels.map(() => Math.floor(Math.random() * 40) + 10);
        } else {
            // short_answer, paragraph — show placeholder data
            labels = ['Responses'];
            values = [Math.floor(Math.random() * 30) + 5];
        }

        const colors = colorSchemes[chart.colorScheme] || colorSchemes.default;
        const maxValue = Math.max(...values, 1);

        if (chart.type === 'bar' || chart.type === 'horizontal_bar') {
            const isHorizontal = chart.type === 'horizontal_bar';
            return `
                <div class="${isHorizontal ? 'h-bar-list' : 'bar-chart'}">
                    ${labels.map((label, i) => {
                        const pct = Math.round((values[i] / maxValue) * 100);
                        if (isHorizontal) {
                            return `
                                <div class="h-bar-item">
                                    <div class="h-bar-header">
                                        <span class="h-bar-label">
                                            <span class="h-bar-dot" style="background:${colors[i % colors.length]};"></span> ${label}
                                        </span>
                                        <span class="h-bar-percent">${values[i]}</span>
                                    </div>
                                    <div class="h-bar-track">
                                        <div class="h-bar-fill" style="width:${pct}%; background:${colors[i % colors.length]};"></div>
                                    </div>
                                </div>
                            `;
                        }
                        return `
                            <div class="bar-chart-item">
                                <span class="bar-value">${values[i]}</span>
                                <div class="bar-fill" style="height: ${Math.max(pct * 1.5, 10)}px; background: ${colors[i % colors.length]};"></div>
                                <span class="bar-label">${label.length > 10 ? label.substring(0, 10) + '...' : label}</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        if (chart.type === 'pie' || chart.type === 'doughnut') {
            const total = values.reduce((a, b) => a + b, 0);
            return `
                <div class="simple-pie-chart">
                    <div class="pie-legend">
                        ${labels.map((label, i) => {
                            const pct = total > 0 ? Math.round((values[i] / total) * 100) : 0;
                            return `
                                <div class="pie-legend-item">
                                    <span class="pie-legend-dot" style="background:${colors[i % colors.length]};"></span>
                                    <span class="pie-legend-label">${label}</span>
                                    <span class="pie-legend-value">${values[i]} (${pct}%)</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                    <div class="pie-visual" style="
                        background: conic-gradient(
                            ${labels.map((label, i) => {
                                const pct = total > 0 ? (values[i] / total) * 100 : 0;
                                const prevPct = values.slice(0, i).reduce((a, b) => a + b, 0);
                                const prevAngle = total > 0 ? (prevPct / total) * 100 : 0;
                                return `${colors[i % colors.length]} ${prevAngle}% ${prevAngle + pct}%`;
                            }).join(', ')}
                        );
                        ${chart.type === 'doughnut' ? 'mask: radial-gradient(circle, transparent 40%, black 41%);' : ''}
                        ${chart.type === 'doughnut' ? '-webkit-mask: radial-gradient(circle, transparent 40%, black 41%);' : ''}
                    "></div>
                </div>
            `;
        }

        return `<p style="color: var(--gray-400); text-align: center;">Unknown chart type.</p>`;
    }

    /**
     * Find a question by ID across all phases/sections.
     */
    function findQuestionById(questionId) {
        for (const phase of phases) {
            for (const section of phase.sections) {
                const q = section.questions.find(q => q.id === questionId);
                if (q) return q;
            }
        }
        return null;
    }

    /**
     * Update the question dropdown in the chart builder modal.
     */
    function updateChartQuestionDropdown() {
        const select = document.getElementById('chartQuestion');
        const currentValue = select.value;
        
        let options = '<option value="">-- Select a question --</option>';
        
        for (const phase of phases) {
            for (const section of phase.sections) {
                for (const q of section.questions) {
                    // Allow ALL question types except file_upload
                    if (q.type !== 'file_upload') {
                        const label = q.label.length > 60 ? q.label.substring(0, 57) + '...' : q.label;
                        options += `<option value="${q.id}">[${typeLabels[q.type]}] ${label}</option>`;
                    }
                }
            }
        }

        select.innerHTML = options;
        if (currentValue && [...select.options].some(o => o.value === currentValue)) {
            select.value = currentValue;
        }
    }

    // ═══════════════════════════════════════
    // CHART BUILDER MODAL
    // ═══════════════════════════════════════

    function openChartBuilderModal(chartIndex = null) {
        updateChartQuestionDropdown();
        updatePhaseFilterDropdown();
        
        if (chartIndex !== null && chartIndex !== undefined) {
            // Editing existing chart
            const chart = analyticsCharts[chartIndex];
            currentEditingChart = chartIndex;
            document.getElementById('chartBuilderModalTitle').textContent = 'Edit Chart';
            document.getElementById('chartTitle').value = chart.title;
            document.getElementById('chartType').value = chart.type;
            document.getElementById('chartQuestion').value = chart.questionId;
            document.getElementById('chartPhaseFilter').value = chart.phaseFilter || '';
            selectedColorScheme = chart.colorScheme || 'default';
        } else {
            // Adding new chart
            currentEditingChart = null;
            document.getElementById('chartBuilderModalTitle').textContent = 'Add Analytics Chart';
            document.getElementById('chartTitle').value = '';
            document.getElementById('chartType').value = 'bar';
            document.getElementById('chartQuestion').value = '';
            document.getElementById('chartPhaseFilter').value = '';
            selectedColorScheme = 'default';
        }

        handleChartTypeChange();
        handleChartQuestionChange();
        renderColorSchemeOptions();
        
        document.getElementById('chartBuilderModal').classList.add('active');
    }

    function closeChartBuilderModal() {
        document.getElementById('chartBuilderModal').classList.remove('active');
        currentEditingChart = null;
    }

    function handleChartTypeChange() {
        const type = document.getElementById('chartType').value;
        document.getElementById('chartColorGroup').style.display = 
            ['pie', 'doughnut', 'bar', 'horizontal_bar'].includes(type) ? 'block' : 'block';
    }

    function handleChartQuestionChange() {
        const questionId = document.getElementById('chartQuestion').value;
        const question = questionId ? findQuestionById(questionId) : null;
        const helpEl = document.getElementById('chartQuestionHelp');
        
        if (question) {
            if (question.type === 'multiple_choice' || question.type === 'dropdown') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · ${question.options?.length || 0} options · Best visualized as Pie or Bar chart.`;
            } else if (question.type === 'checkboxes') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · ${question.options?.length || 0} options · Each option counted independently.`;
            } else if (question.type === 'likert_scale' || question.type === 'multiple_choice_grid') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · ${question.gridRows?.length || 0} rows × ${question.gridColumns?.length || 0} columns · Best visualized as Horizontal Bar chart.`;
            } else if (question.type === 'short_answer' || question.type === 'paragraph') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · Shows response count · Best visualized as Bar chart.`;
            }
        } else {
            helpEl.textContent = 'Choose a question to visualize its response data.';
        }
    }

    function updatePhaseFilterDropdown() {
        const select = document.getElementById('chartPhaseFilter');
        select.innerHTML = '<option value="">All Phases</option>' +
            phases.map(p => `<option value="${p.id}">${p.title}</option>`).join('');
    }

    function selectColorScheme(scheme, button) {
        selectedColorScheme = scheme;
        document.querySelectorAll('.color-scheme-option').forEach(b => b.classList.remove('selected'));
        button.classList.add('selected');
    }

    function renderColorSchemeOptions() {
        document.querySelectorAll('.color-scheme-option').forEach(btn => {
            btn.classList.toggle('selected', btn.dataset.scheme === selectedColorScheme);
        });
    }

    function saveChart() {
        const title = document.getElementById('chartTitle').value.trim();
        const type = document.getElementById('chartType').value;
        const questionId = document.getElementById('chartQuestion').value;
        const phaseFilter = document.getElementById('chartPhaseFilter').value;

        if (!title) {
            alert('Please enter a chart title.');
            return;
        }
        if (!questionId) {
            alert('Please select a question to visualize.');
            return;
        }

        const chartData = {
            title,
            type,
            questionId,
            phaseFilter: phaseFilter || null,
            colorScheme: selectedColorScheme,
        };

        if (currentEditingChart !== null) {
            analyticsCharts[currentEditingChart] = chartData;
        } else {
            analyticsCharts.push(chartData);
        }

        closeChartBuilderModal();
        renderAnalyticsChartsGrid();
    }

    function editChart(index) {
        openChartBuilderModal(index);
    }

    function deleteChart(index) {
        if (!confirm('Remove this chart from the analytics dashboard?')) return;
        analyticsCharts.splice(index, 1);
        renderAnalyticsChartsGrid();
    }

    function exportAnalyticsReport() {
        // TODO: Implement CSV/PDF export
        alert('Analytics export will be available when response data is connected.');
    }


</script>

</body>
</html>
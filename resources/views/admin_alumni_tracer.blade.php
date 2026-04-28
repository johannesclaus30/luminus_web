<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Alumni Tracer | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/alumni_tracer.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-current">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>
            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="div-dashboard-container">
            <div class="tracer-layout">
        
                <!-- ========== MAIN PANEL ========== -->
                <div class="tracer-main-panel" id="mainPanel">
                    <div id="editorView">
                        <div class="panel-header">
                            <div class="header-logo-placeholder" id="surveyLogoContainer">
                                <span id="surveyLogoText">NU</span>
                                <button class="change-logo-btn" id="changeLogoBtn" title="Change icon">🖉</button>
                                <input type="file" id="logoFileInput" accept="image/*" style="display: none;">
                            </div>
                            <div class="header-titles">
                                <h2 id="surveyTitleDisplay">NU Lipa College Tracer Study</h2>
                                <span class="status-badge" id="surveyStatusDisplay">Accepting Responses</span>
                            </div>
                            <div class="panel-actions">
                                <button class="btn-gray" id="previewBtn">👁 Preview</button>
                                <button class="btn-primary" id="saveBtn">💾 Save</button>
                            </div>
                        </div>

                        <div class="survey-builder-container">
                            <div class="form-group">
                                <label>Header Photo:</label>
                                <div class="photo-upload-box" id="headerPhotoBox">
                                    <img id="headerPhotoImg" src="/assets/FINAL-NULIPA.jpg" alt="National University Banner" class="banner-img">
                                    <button class="change-photo-btn" id="changePhotoBtn">Change Photo</button>
                                    <input type="file" id="photoFileInput" accept="image/*" style="display: none;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Survey Title:</label>
                                <input type="text" class="form-control title-input" id="surveyTitleInput" value="NU Lipa College Tracer Study">
                            </div>

                            <div class="builder-container" id="formBuilder">
                                <!-- Dynamic question cards rendered here -->
                            </div>
                            <div class="add-question-footer">
                                <button id="addQuestionBtn">+ Add Question</button>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Surveys view (hidden) -->
                    <div id="manageView" style="display: none;">
                        <div class="panel-header">
                            <div class="header-titles">
                                <h2>Manage Surveys</h2>
                            </div>
                        </div>
                        <div style="overflow-x: auto; margin-bottom: 28px;">
                            <table style="width: 100%; border-collapse: collapse;" id="manageTable">
                                <thead>
                                    <tr style="background: #f3f4f6; text-align: left;">
                                        <th style="padding: 12px;">Title</th>
                                        <th style="padding: 12px;">Status</th>
                                        <th style="padding: 12px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="manageTableBody"></tbody>
                            </table>
                        </div>
                        <div class="panel-header" style="margin-top: 12px;">
                            <div class="header-titles">
                                <h2>Recently Deleted Surveys</h2>
                            </div>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;" id="deletedTable">
                                <thead>
                                    <tr style="background: #f3f4f6; text-align: left;">
                                        <th style="padding: 12px;">Title</th>
                                        <th style="padding: 12px;">Deleted At</th>
                                        <th style="padding: 12px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="deletedTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========== RIGHT SIDEBAR ========== -->
                <div class="tracer-sidebar">
                    <h2 class="sidebar-title">Alumni Tracer</h2>
                    <button id="addNewTracerBtn" class="add-tracer-btn">+ Add New Alumni Tracer</button>
                    <div class="sidebar-tabs">
                        <button class="tab-btn active" id="tabAddNew">All Surveys</button>
                        <button class="tab-btn" id="tabManage">Manage Surveys</button>
                    </div>
                    <div class="survey-list" id="surveyList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== PREVIEW MODAL ========== -->
    <div class="modal-overlay" id="previewModal">
        <div class="modal-content">
            <button class="modal-close" id="closePreview">&times;</button>
            <div id="previewContent"></div>
        </div>
    </div>

    <script>
        (function() {
            // ===================== MOCK DATA =====================
            const surveys = [
                {
                    id: 1,
                    title: 'NU Lipa College Tracer Study',
                    status: 'Active',
                    logo: 'NU',
                    headerPhoto: '/assets/logos/nu_banner.png',
                    persisted: false,
                    questions: [
                        { id: 101, type: 'text', question_text: 'Full Name', subtitle: '', required: true },
                        { id: 102, type: 'choice', question_text: 'Employment Status', subtitle: 'Select your current status', required: true, display_type: 'list', options: [ { label: 'Employed', go_to: null }, { label: 'Unemployed', go_to: null }, { label: 'Self-employed', go_to: null } ], other_enabled: false },
                        { id: 103, type: 'likert', question_text: 'Rate the following aspects', subtitle: '1 = Very Dissatisfied, 5 = Very Satisfied', required: false, scale_points: 5, scale_labels: ['Very Dissatisfied', '', '', '', 'Very Satisfied'], statements: [ { id: 1001, text: 'Quality of Education' }, { id: 1002, text: 'Facilities' }, { id: 1003, text: 'Faculty Support' } ] },
                        { id: 104, type: 'section_header', question_text: 'Educational Background', subtitle: '', required: false }
                    ]
                },
                {
                    id: 2,
                    title: 'NU Lipa SHS Tracer Study',
                    status: 'Closed',
                    logo: 'NU',
                    headerPhoto: '/assets/logos/nu_banner.png',
                    persisted: false,
                    questions: [
                        { id: 201, type: 'checkbox', question_text: 'Which skills have you acquired?', subtitle: 'Select all that apply', required: true, display_type: 'list', options: [ { label: 'Communication', go_to: null }, { label: 'Leadership', go_to: null } ], other_enabled: true }
                    ]
                }
            ];

            let currentSurveyId = surveys[0].id;
            let questionCounter = 0;
            let deletedSurveys = [];

            // DOM references
            const formBuilder = document.getElementById('formBuilder');
            const surveyTitleInput = document.getElementById('surveyTitleInput');
            const surveyTitleDisplay = document.getElementById('surveyTitleDisplay');
            const surveyStatusDisplay = document.getElementById('surveyStatusDisplay');
            const surveyLogoText = document.getElementById('surveyLogoText');
            const headerPhotoImg = document.getElementById('headerPhotoImg');
            const surveyListContainer = document.getElementById('surveyList');
            const manageTableBody = document.getElementById('manageTableBody');
            const deletedTableBody = document.getElementById('deletedTableBody');
            const tabAddNew = document.getElementById('tabAddNew');
            const tabManage = document.getElementById('tabManage');
            const editorView = document.getElementById('editorView');
            const manageView = document.getElementById('manageView');
            const previewModal = document.getElementById('previewModal');
            const previewContent = document.getElementById('previewContent');
            const closePreview = document.getElementById('closePreview');
            const previewBtn = document.getElementById('previewBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const tracerListUrl = "{{ route('admin.alumni_tracer.list') }}";
            const tracerDeletedUrl = "{{ route('admin.alumni_tracer.deleted') }}";
            const tracerStoreUrl = "{{ route('admin.alumni_tracer.store') }}";
            const tracerUpdateBaseUrl = "{{ url('/admin/alumni_tracer') }}";

            // Branding upload elements
            const changeLogoBtn = document.getElementById('changeLogoBtn');
            const logoFileInput = document.getElementById('logoFileInput');
            const changePhotoBtn = document.getElementById('changePhotoBtn');
            const photoFileInput = document.getElementById('photoFileInput');

            // ---------- Utility ----------
            function generateQuestionId() {
                return Date.now() + Math.floor(Math.random()*1000) + questionCounter++;
            }

            function generateStatementId() {
                return 'stmt_' + Date.now() + Math.floor(Math.random()*1000) + questionCounter++;
            }

            function createEmptyQuestion(type = 'text') {
                const base = { id: generateQuestionId(), type, question_text: '', subtitle: '', required: false };
                if (type === 'choice' || type === 'checkbox') {
                    base.options = [{ label: '', go_to: null }];
                    base.other_enabled = false;
                    base.display_type = 'list';
                } else if (type === 'likert') {
                    base.scale_points = 5;
                    base.scale_labels = Array(5).fill('');
                    base.statements = [{ id: generateStatementId(), text: '' }];
                }
                return base;
            }

            function getCurrentSurvey() {
                return surveys.find(s => s.id === currentSurveyId);
            }

            function normalizeServerSurvey(form) {
                return {
                    id: form.id,
                    title: form.form_title || '',
                    status: form.is_active ? 'Active' : 'Closed',
                    logo: 'NU',
                    headerPhoto: form.form_header || '/assets/logos/nu_banner.png',
                    persisted: true,
                    deletedAt: form.deleted_at || null,
                    questions: (form.questions || []).map(question => {
                        const settings = question.settings || {};
                        const normalized = {
                            id: question.id,
                            type: question.type || 'text',
                            question_text: question.question_text || '',
                            subtitle: question.description || '',
                            required: !!question.is_required,
                        };

                        if (normalized.type === 'choice' || normalized.type === 'checkbox') {
                            normalized.options = (question.options || []).map(option => ({
                                label: option.option_label || option.option_value || '',
                                go_to: null,
                            }));
                            normalized.other_enabled = !!settings.other_enabled;
                            normalized.display_type = settings.display_type || 'list';
                        } else if (normalized.type === 'likert') {
                            const points = settings.scale_points || 5;
                            normalized.scale_points = points;
                            normalized.scale_labels = settings.scale_labels || Array(points).fill('');
                            normalized.statements = (settings.statements || []).map(statement => ({
                                id: statement.id || generateStatementId(),
                                text: statement.text || '',
                            }));
                        }

                        return normalized;
                    }),
                };
            }

            async function loadTracerData() {
                try {
                    const [activeResponse, deletedResponse] = await Promise.all([
                        fetch(tracerListUrl, { headers: { Accept: 'application/json' } }),
                        fetch(tracerDeletedUrl, { headers: { Accept: 'application/json' } }),
                    ]);

                    if (!activeResponse.ok || !deletedResponse.ok) {
                        throw new Error('Failed to load tracer forms.');
                    }

                    const activeForms = await activeResponse.json();
                    const deletedForms = await deletedResponse.json();

                    surveys.splice(0, surveys.length, ...activeForms.map(normalizeServerSurvey));
                    deletedSurveys = deletedForms.map(normalizeServerSurvey);
                    currentSurveyId = surveys.length ? surveys[0].id : null;
                } catch (error) {
                    deletedSurveys = [];
                }
            }

            function collectSurveyFromBuilder() {
                const currentSurvey = getCurrentSurvey();
                const questions = [];

                document.querySelectorAll('.question-card-builder').forEach(card => {
                    const question = {
                        id: parseInt(card.dataset.questionId),
                        type: card.querySelector('.type-select').value,
                        question_text: card.querySelector('.question-text-input').value,
                        subtitle: card.querySelector('.subtitle-input').value,
                        required: card.querySelector('.required-toggle input').checked,
                    };

                    if (question.type === 'choice' || question.type === 'checkbox') {
                        question.options = [];
                        card.querySelectorAll('.option-row').forEach(row => {
                            question.options.push({
                                label: row.querySelector('.option-label').value,
                                go_to: row.querySelector('.goto-select')?.value || null,
                            });
                        });
                        question.other_enabled = card.querySelector('.other-toggle input')?.checked || false;
                        question.display_type = card.querySelector('.display-type-toggle input')?.checked ? 'dropdown' : 'list';
                    } else if (question.type === 'likert') {
                        question.scale_points = parseInt(card.querySelector('.scale-points-input').value) || 5;
                        question.scale_labels = Array.from(card.querySelectorAll('.scale-label-item input')).map(inp => inp.value);
                        question.statements = [];
                        card.querySelectorAll('.likert-statement-row').forEach(row => {
                            question.statements.push({
                                id: row.dataset.statementId,
                                text: row.querySelector('.statement-text-input').value,
                            });
                        });
                    }

                    questions.push(question);
                });

                return {
                    id: currentSurvey ? currentSurvey.id : null,
                    title: surveyTitleInput.value,
                    status: surveyStatusDisplay.textContent === 'Accepting Responses' ? 'Active' : (surveyStatusDisplay.textContent === 'Closed' ? 'Closed' : 'Draft'),
                    logo: surveyLogoText.innerText === '' ? 'NU' : surveyLogoText.innerText,
                    headerPhoto: headerPhotoImg.src,
                    persisted: currentSurvey ? !!currentSurvey.persisted : false,
                    questions,
                };
            }

            // Sync DOM back to survey object
            function syncBuilderToSurvey() {
                const survey = getCurrentSurvey();
                if (!survey) return;
                Object.assign(survey, collectSurveyFromBuilder());
                refreshAllGoToDropdowns();
            }

            function refreshAllGoToDropdowns() {
                const survey = getCurrentSurvey();
                if (!survey || !survey.questions) return;
                const questionList = survey.questions;
                document.querySelectorAll('.option-row .goto-select').forEach(select => {
                    const currentValue = select.dataset.currentValue || select.value;
                    select.innerHTML = '<option value="">Continue to next</option>';
                    questionList.forEach((q, idx) => {
                        const displayText = q.question_text || 'Question #' + (idx + 1);
                        select.innerHTML += `<option value="${q.id}">Go to: ${displayText}</option>`;
                    });
                    select.innerHTML += '<option value="end">End of Form</option>';
                    const options = Array.from(select.options).map(o => o.value);
                    select.value = options.includes(currentValue) ? currentValue : '';
                    select.dataset.currentValue = select.value;
                });
            }

            function buildFormFromSurvey() {
                const survey = getCurrentSurvey();
                formBuilder.innerHTML = '';
                if (!survey || !survey.questions) return;
                survey.questions.forEach((q, index) => appendQuestionCard(q, index));
                addBetweenButtons();
                enableDragDrop();
                refreshAllGoToDropdowns();
            }

            function appendQuestionCard(questionData, index = null) {
                const q = questionData;
                const card = document.createElement('div');
                card.className = `question-card-builder ${q.type === 'section_header' ? 'section-header' : ''}`;
                card.dataset.questionId = q.id;
                card.draggable = true;
                card.innerHTML = `
                    <div class="card-drag-handle" title="Drag to reorder">&#x2630;</div>
                    <div class="card-question-row">
                        <input type="text" class="form-control question-text-input" value="${q.question_text || ''}" placeholder="Question text">
                    </div>
                    <div class="card-subtitle-row">
                        <input type="text" class="form-control subtitle-input" value="${q.subtitle || ''}" placeholder="Subtitle (optional)">
                    </div>
                    <div class="card-toolbar">
                        <span class="question-number">#${index !== null ? index + 1 : ''}</span>
                        <select class="form-control type-select">
                            <option value="text" ${q.type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="choice" ${q.type === 'choice' ? 'selected' : ''}>Choice</option>
                            <option value="checkbox" ${q.type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                            <option value="likert" ${q.type === 'likert' ? 'selected' : ''}>Likert Scale</option>
                            <option value="section_header" ${q.type === 'section_header' ? 'selected' : ''}>Section Header</option>
                        </select>
                        <label class="required-toggle"><input type="checkbox" ${q.required ? 'checked' : ''}> Required</label>
                        <div class="card-actions">
                            <button class="btn-icon duplicate" title="Duplicate question">&#x2398;</button>
                            <button class="btn-icon delete" title="Delete question">&#x2715;</button>
                        </div>
                    </div>
                    <div class="card-body"></div>
                `;
                const body = card.querySelector('.card-body');
                loadTypeBody(body, q);
                formBuilder.appendChild(card);
                attachCardEvents(card);
            }

            function loadTypeBody(bodyElement, question) {
                bodyElement.innerHTML = '';
                const type = question.type;
                if (type === 'choice' || type === 'checkbox') {
                    const isDropdown = question.display_type === 'dropdown';
                    bodyElement.innerHTML = `
                        <div class="options-list">
                            ${(question.options || []).map(opt => `
                                <div class="option-row">
                                    <input type="text" class="form-control option-label" placeholder="Option label" value="${opt.label || ''}">
                                    <select class="goto-select" data-current-value="${opt.go_to || ''}">
                                        <option value="">Continue to next</option>
                                    </select>
                                    <button class="remove-option" title="Remove option">&times;</button>
                                </div>
                            `).join('')}
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <button class="add-option-btn">+ Add Option</button>
                            <label class="other-toggle"><input type="checkbox" ${question.other_enabled ? 'checked' : ''}> Add "Other" option</label>
                            <label class="display-type-toggle"><input type="checkbox" ${isDropdown ? 'checked' : ''}> Display as Dropdown</label>
                        </div>
                        ${question.other_enabled ? '<div class="other-placeholder">Other: [manual text entry]</div>' : ''}
                    `;
                } else if (type === 'likert') {
                    const points = question.scale_points || 5;
                    const labels = question.scale_labels || Array(points).fill('');
                    const statements = question.statements || [{ id: generateStatementId(), text: '' }];
                    bodyElement.innerHTML = `
                        <div class="likert-setup">
                            <div>
                                <label>Scale Points</label>
                                <input type="number" class="form-control scale-points-input" min="2" max="10" value="${points}">
                            </div>
                        </div>
                        <div class="scale-labels-editor">
                            ${labels.map((label, i) => `
                                <div class="scale-label-item">
                                    <div class="point-number">${i+1}</div>
                                    <input type="text" class="form-control" value="${label || ''}" placeholder="Point ${i+1} label">
                                </div>
                            `).join('')}
                        </div>

                        <div class="likert-statements-section">
                            <label style="margin-top: 15px;">Statements (rows)</label>
                            <div class="likert-statements-list">
                                ${statements.map(stmt => `
                                    <div class="likert-statement-row" data-statement-id="${stmt.id}">
                                        <input type="text" class="form-control statement-text-input" value="${stmt.text || ''}" placeholder="Enter statement">
                                        <button class="remove-statement-btn" title="Remove statement">&times;</button>
                                    </div>
                                `).join('')}
                            </div>
                            <button class="add-statement-btn">+ Add Statement</button>
                        </div>

                        <div class="scale-preview" id="scalePreview">${generateScalePoints(points)}</div>
                    `;
                } else if (type === 'text') {
                    bodyElement.innerHTML = '<div class="text-preview">Long answer text...</div>';
                }
            }

            function generateScalePoints(count) {
                let html = '';
                for (let i = 1; i <= count; i++) html += `<div class="scale-point">${i}</div>`;
                return html;
            }

            function attachCardEvents(card) {
                const typeSelect = card.querySelector('.type-select');
                typeSelect.addEventListener('change', function(e) {
                    const newType = e.target.value;
                    card.classList.toggle('section-header', newType === 'section_header');
                    const body = card.querySelector('.card-body');
                    const qText = card.querySelector('.question-text-input').value;
                    const subtitle = card.querySelector('.subtitle-input').value;
                    const required = card.querySelector('.required-toggle input').checked;
                    loadTypeBody(body, { type: newType, question_text: qText, subtitle, required, options: [], scale_points: 5, scale_labels: [], statements: [], other_enabled: false, display_type: 'list' });
                    attachBodyEvents(card);
                    syncBuilderToSurvey();
                    refreshAllGoToDropdowns();
                });

                card.querySelector('.delete').addEventListener('click', () => {
                    card.remove();
                    updateQuestionNumbers();
                    addBetweenButtons();
                    syncBuilderToSurvey();
                    refreshAllGoToDropdowns();
                });

                card.querySelector('.duplicate').addEventListener('click', () => {
                    syncBuilderToSurvey();
                    const survey = getCurrentSurvey();
                    const origId = parseInt(card.dataset.questionId);
                    const origQ = survey.questions.find(q => q.id === origId);
                    if (!origQ) return;
                    const clone = JSON.parse(JSON.stringify(origQ));
                    clone.id = generateQuestionId();
                    // regen statement IDs for duplicate
                    if (clone.statements) clone.statements = clone.statements.map(s => ({ ...s, id: generateStatementId() }));
                    const newCard = buildCardElement(clone);
                    card.after(newCard);
                    updateQuestionNumbers();
                    addBetweenButtons();
                    syncBuilderToSurvey();
                    enableDragDrop();
                    refreshAllGoToDropdowns();
                });

                card.querySelector('.required-toggle input').addEventListener('change', syncBuilderToSurvey);
                card.querySelector('.question-text-input').addEventListener('input', () => { syncBuilderToSurvey(); refreshAllGoToDropdowns(); });
                card.querySelector('.subtitle-input').addEventListener('input', syncBuilderToSurvey);
                attachBodyEvents(card);
            }

            function attachBodyEvents(card) {
                const addBtn = card.querySelector('.add-option-btn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => {
                        const list = card.querySelector('.options-list');
                        const newRow = document.createElement('div');
                        newRow.className = 'option-row';
                        newRow.innerHTML = `
                            <input type="text" class="form-control option-label" placeholder="Option label">
                            <select class="goto-select"><option value="">Continue to next</option></select>
                            <button class="remove-option" title="Remove option">&times;</button>
                        `;
                        list.appendChild(newRow);
                        newRow.querySelector('.remove-option').addEventListener('click', () => { newRow.remove(); syncBuilderToSurvey(); });
                        newRow.querySelector('.option-label').addEventListener('input', syncBuilderToSurvey);
                        newRow.querySelector('.goto-select').addEventListener('change', function() { this.dataset.currentValue = this.value; syncBuilderToSurvey(); });
                        syncBuilderToSurvey();
                        refreshAllGoToDropdowns();
                    });
                }

                card.querySelectorAll('.remove-option').forEach(btn => btn.addEventListener('click', (e) => { e.target.closest('.option-row').remove(); syncBuilderToSurvey(); }));
                card.querySelectorAll('.option-label').forEach(inp => inp.addEventListener('input', syncBuilderToSurvey));
                card.querySelectorAll('.goto-select').forEach(sel => sel.addEventListener('change', function() { this.dataset.currentValue = this.value; syncBuilderToSurvey(); }));

                const otherToggle = card.querySelector('.other-toggle input');
                if (otherToggle) {
                    otherToggle.addEventListener('change', function() {
                        const placeholder = card.querySelector('.other-placeholder');
                        if (this.checked) {
                            if (!placeholder) {
                                const div = document.createElement('div');
                                div.className = 'other-placeholder';
                                div.textContent = 'Other: [manual text entry]';
                                card.querySelector('.options-list').after(div);
                            }
                        } else { const el = card.querySelector('.other-placeholder'); if (el) el.remove(); }
                        syncBuilderToSurvey();
                    });
                }

                const displayToggle = card.querySelector('.display-type-toggle input');
                if (displayToggle) displayToggle.addEventListener('change', syncBuilderToSurvey);

                const pointsInput = card.querySelector('.scale-points-input');
                if (pointsInput) {
                    pointsInput.addEventListener('input', () => {
                        const val = parseInt(pointsInput.value) || 5;
                        const preview = card.querySelector('#scalePreview');
                        if (preview) preview.innerHTML = generateScalePoints(val);
                        const editor = card.querySelector('.scale-labels-editor');
                        const currentLabels = Array.from(editor.querySelectorAll('input')).map(inp => inp.value);
                        editor.innerHTML = '';
                        for (let i = 0; i < val; i++) {
                            editor.innerHTML += `<div class="scale-label-item"><div class="point-number">${i+1}</div><input type="text" class="form-control" value="${currentLabels[i] || ''}" placeholder="Point ${i+1} label"></div>`;
                        }
                        editor.querySelectorAll('input').forEach(inp => inp.addEventListener('input', syncBuilderToSurvey));
                        syncBuilderToSurvey();
                    });
                    card.querySelectorAll('.scale-labels-editor input').forEach(inp => inp.addEventListener('input', syncBuilderToSurvey));
                }

                // Likert statements
                const addStatementBtn = card.querySelector('.add-statement-btn');
                if (addStatementBtn) {
                    addStatementBtn.addEventListener('click', () => {
                        const list = card.querySelector('.likert-statements-list');
                        const newRow = document.createElement('div');
                        newRow.className = 'likert-statement-row';
                        newRow.dataset.statementId = generateStatementId();
                        newRow.innerHTML = `
                            <input type="text" class="form-control statement-text-input" placeholder="Enter statement">
                            <button class="remove-statement-btn" title="Remove statement">&times;</button>
                        `;
                        list.appendChild(newRow);
                        newRow.querySelector('.remove-statement-btn').addEventListener('click', () => {
                            newRow.remove();
                            syncBuilderToSurvey();
                        });
                        newRow.querySelector('.statement-text-input').addEventListener('input', syncBuilderToSurvey);
                        syncBuilderToSurvey();
                    });
                }

                card.querySelectorAll('.remove-statement-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        btn.closest('.likert-statement-row').remove();
                        syncBuilderToSurvey();
                    });
                });

                card.querySelectorAll('.statement-text-input').forEach(inp => {
                    inp.addEventListener('input', syncBuilderToSurvey);
                });
            }

            function buildCardElement(questionData) {
                const temp = document.createElement('div');
                temp.innerHTML = '<div class="question-card-builder"></div>';
                const card = temp.firstChild;
                const q = questionData;
                card.className = `question-card-builder ${q.type === 'section_header' ? 'section-header' : ''}`;
                card.dataset.questionId = q.id;
                card.draggable = true;
                card.innerHTML = `
                    <div class="card-drag-handle" title="Drag to reorder">&#x2630;</div>
                    <div class="card-question-row">
                        <input type="text" class="form-control question-text-input" value="${q.question_text || ''}" placeholder="Question text">
                    </div>
                    <div class="card-subtitle-row">
                        <input type="text" class="form-control subtitle-input" value="${q.subtitle || ''}" placeholder="Subtitle (optional)">
                    </div>
                    <div class="card-toolbar">
                        <span class="question-number">?</span>
                        <select class="form-control type-select">
                            <option value="text" ${q.type==='text'?'selected':''}>Text</option>
                            <option value="choice" ${q.type==='choice'?'selected':''}>Choice</option>
                            <option value="checkbox" ${q.type==='checkbox'?'selected':''}>Checkbox</option>
                            <option value="likert" ${q.type==='likert'?'selected':''}>Likert Scale</option>
                            <option value="section_header" ${q.type==='section_header'?'selected':''}>Section Header</option>
                        </select>
                        <label class="required-toggle"><input type="checkbox" ${q.required?'checked':''}> Required</label>
                        <div class="card-actions">
                            <button class="btn-icon duplicate" title="Duplicate question">&#x2398;</button>
                            <button class="btn-icon delete" title="Delete question">&#x2715;</button>
                        </div>
                    </div>
                    <div class="card-body"></div>
                `;
                const body = card.querySelector('.card-body');
                loadTypeBody(body, q);
                attachCardEvents(card);
                return card;
            }

            // ... (rest of the functions: updateQuestionNumbers, addBetweenButtons, enableDragDrop, handleDrag events, renderSidebar, selectSurvey, renderManageTable, switchToEditor, switchToManage, resetForm, deleteSurvey, openPreview, branding uploads, button listeners) ...
            // They remain exactly as provided, but ensure the openPreview() function is updated as below.

            function updateQuestionNumbers() {
                document.querySelectorAll('.question-card-builder').forEach((card, idx) => {
                    const numSpan = card.querySelector('.question-number');
                    if (numSpan) numSpan.textContent = '#' + (idx + 1);
                });
            }

            function addBetweenButtons() {
                document.querySelectorAll('.add-between').forEach(el => el.remove());
                const cards = [...document.querySelectorAll('.question-card-builder')];
                cards.forEach((card, index) => {
                    if (index < cards.length - 1) {
                        const betweenDiv = document.createElement('div');
                        betweenDiv.className = 'add-between';
                        betweenDiv.innerHTML = '<button>+ Insert Between</button>';
                        betweenDiv.querySelector('button').addEventListener('click', () => {
                            const newQuestion = createEmptyQuestion('text');
                            const newCard = buildCardElement(newQuestion);
                            card.after(newCard);
                            updateQuestionNumbers();
                            addBetweenButtons();
                            syncBuilderToSurvey();
                            enableDragDrop();
                            refreshAllGoToDropdowns();
                        });
                        card.after(betweenDiv);
                    }
                });
            }

            function enableDragDrop() {
                const cards = document.querySelectorAll('.question-card-builder');
                cards.forEach(card => {
                    card.removeEventListener('dragstart', handleDragStart);
                    card.removeEventListener('dragover', handleDragOver);
                    card.removeEventListener('dragleave', handleDragLeave);
                    card.removeEventListener('drop', handleDrop);
                    card.removeEventListener('dragend', handleDragEnd);
                    card.addEventListener('dragstart', handleDragStart);
                    card.addEventListener('dragover', handleDragOver);
                    card.addEventListener('dragleave', handleDragLeave);
                    card.addEventListener('drop', handleDrop);
                    card.addEventListener('dragend', handleDragEnd);
                });
            }

            let draggedItem = null;
            function handleDragStart(e) { draggedItem = this; this.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; }
            function handleDragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; this.classList.add('drag-over'); }
            function handleDragLeave() { this.classList.remove('drag-over'); }
            function handleDrop(e) {
                e.stopPropagation();
                this.classList.remove('drag-over');
                if (draggedItem !== this && draggedItem) {
                    const parent = this.parentNode;
                    const ref = this.nextSibling === draggedItem ? this : this;
                    parent.insertBefore(draggedItem, ref);
                    addBetweenButtons();
                    updateQuestionNumbers();
                    syncBuilderToSurvey();
                    enableDragDrop();
                    refreshAllGoToDropdowns();
                }
            }
            function handleDragEnd() { this.classList.remove('dragging'); document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over')); draggedItem = null; }

            function renderSidebar() {
                surveyListContainer.innerHTML = '';
                surveys.forEach(survey => {
                    const item = document.createElement('div');
                    item.className = `survey-item${currentSurveyId === survey.id ? ' active' : ''}`;
                    item.dataset.surveyId = survey.id;
                    item.innerHTML = `
                        <div class="survey-item-icon ${survey.title.includes('SHS') ? 'warning' : ''}">NU</div>
                        <div class="survey-item-details">
                            <h4>${survey.title}</h4>
                            <span>${survey.status}</span>
                        </div>
                    `;
                    item.addEventListener('click', () => selectSurvey(survey.id));
                    surveyListContainer.appendChild(item);
                });
            }

            function selectSurvey(id) {
                if (currentSurveyId === id) return;
                syncBuilderToSurvey();
                currentSurveyId = id;
                document.querySelectorAll('.survey-item').forEach(item => item.classList.toggle('active', parseInt(item.dataset.surveyId) === id));
                const survey = getCurrentSurvey();
                if (survey) {
                    surveyTitleInput.value = survey.title;
                    surveyTitleDisplay.textContent = survey.title;
                    surveyStatusDisplay.textContent = survey.status === 'Active' ? 'Accepting Responses' : 'Closed';
                    surveyLogoText.innerText = survey.logo || 'NU';
                    headerPhotoImg.src = survey.headerPhoto || '/assets/logos/nu_banner.png';
                }
                buildFormFromSurvey();
                addBetweenButtons();
                enableDragDrop();
                refreshAllGoToDropdowns();
            }

            function renderManageTable() {
                manageTableBody.innerHTML = '';
                if (!surveys.length) {
                    manageTableBody.innerHTML = '<tr><td colspan="3" style="padding: 14px; color: #6b7280;">No active surveys yet.</td></tr>';
                    return;
                }

                surveys.forEach(survey => {
                    const row = document.createElement('tr');
                    row.style.borderBottom = '1px solid #e5e7eb';
                    const isActive = survey.status === 'Active';
                    row.innerHTML = `
                        <td style="padding: 12px; font-weight: 500;">${survey.title}</td>
                        <td style="padding: 12px;">
                            <label class="toggle-switch">
                                <input type="checkbox" class="status-toggle" data-survey-id="${survey.id}" ${isActive ? 'checked' : ''}>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 8px; font-size:13px;">${survey.status}</span>
                        </td>
                        <td style="padding: 12px;">
                            <button class="edit-btn" data-survey-id="${survey.id}" style="background: #32418c; color: white; border: none; padding: 6px 12px; border-radius: 6px; margin-right: 5px; cursor: pointer;">Edit</button>
                            <button class="delete-btn" data-survey-id="${survey.id}" style="background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;">Delete</button>
                        </td>
                    `;
                    manageTableBody.appendChild(row);
                });

                document.querySelectorAll('.status-toggle').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const id = parseInt(this.dataset.surveyId);
                        const survey = surveys.find(s => s.id === id);
                        if (survey) {
                            survey.status = this.checked ? 'Active' : 'Closed';
                            renderManageTable();
                            renderSidebar();
                            if (id === currentSurveyId) {
                                surveyStatusDisplay.textContent = survey.status === 'Active' ? 'Accepting Responses' : 'Closed';
                            }
                        }
                    });
                });

                document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', (e) => {
                    const id = parseInt(e.target.getAttribute('data-survey-id'));
                    selectSurvey(id);
                    switchToEditor();
                }));

                document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', (e) => {
                    const id = parseInt(e.target.getAttribute('data-survey-id'));
                    deleteSurvey(id);
                }));
            }

            function renderDeletedTable() {
                deletedTableBody.innerHTML = '';
                if (!deletedSurveys.length) {
                    deletedTableBody.innerHTML = '<tr><td colspan="3" style="padding: 14px; color: #6b7280;">No recently deleted surveys.</td></tr>';
                    return;
                }

                deletedSurveys.forEach(survey => {
                    const row = document.createElement('tr');
                    row.style.borderBottom = '1px solid #e5e7eb';
                    const deletedAt = survey.deletedAt ? new Date(survey.deletedAt).toLocaleString() : 'Recently deleted';
                    row.innerHTML = `
                        <td style="padding: 12px; font-weight: 500;">${survey.title}</td>
                        <td style="padding: 12px; color: #6b7280; font-size: 13px;">${deletedAt}</td>
                        <td style="padding: 12px;">
                            <button class="restore-btn" data-survey-id="${survey.id}" style="background: #047857; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;">Restore</button>
                        </td>
                    `;
                    deletedTableBody.appendChild(row);
                });

                document.querySelectorAll('.restore-btn').forEach(btn => btn.addEventListener('click', (e) => {
                    const id = parseInt(e.target.getAttribute('data-survey-id'));
                    restoreSurvey(id);
                }));
            }

            function switchToEditor() {
                editorView.style.display = 'block';
                manageView.style.display = 'none';
                tabAddNew.classList.add('active');
                tabManage.classList.remove('active');
            }

            function switchToManage() {
                syncBuilderToSurvey();
                editorView.style.display = 'none';
                manageView.style.display = 'block';
                tabAddNew.classList.remove('active');
                tabManage.classList.add('active');
                renderManageTable();
                renderDeletedTable();
            }

            function resetForm() {
                surveyTitleInput.value = '';
                surveyTitleDisplay.textContent = 'New Survey';
                surveyStatusDisplay.textContent = 'Draft';
                formBuilder.innerHTML = '';
                currentSurveyId = null;
                document.querySelectorAll('.survey-item').forEach(item => item.classList.remove('active'));
                addBetweenButtons();
            }

            async function deleteSurvey(id) {
                if (!confirm('Are you sure?')) return;
                const survey = surveys.find(s => s.id === id);

                try {
                    if (survey && survey.persisted) {
                        const response = await fetch(`${tracerUpdateBaseUrl}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.error || result.message || 'Failed to delete tracer form.');
                        }

                        const deletedForm = result.form ? normalizeServerSurvey(result.form) : { ...survey, deletedAt: new Date().toISOString() };
                        deletedSurveys.unshift(deletedForm);
                    } else if (survey) {
                        deletedSurveys.unshift({ ...survey, deletedAt: new Date().toISOString() });
                    }

                    const idx = surveys.findIndex(s => s.id === id);
                    if (idx > -1) surveys.splice(idx, 1);

                    if (currentSurveyId === id) {
                        currentSurveyId = surveys.length ? surveys[0].id : null;
                        if (currentSurveyId) selectSurvey(currentSurveyId);
                        else resetForm();
                    }

                    renderSidebar();
                    renderManageTable();
                    renderDeletedTable();
                } catch (error) {
                    alert(error.message || 'Failed to delete tracer form.');
                }
            }

            async function restoreSurvey(id) {
                try {
                    const response = await fetch(`${tracerUpdateBaseUrl}/${id}/restore`, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Failed to restore tracer form.');
                    }

                    const restoredSurvey = normalizeServerSurvey(result.form);
                    const deletedIndex = deletedSurveys.findIndex(s => s.id === id);
                    if (deletedIndex > -1) deletedSurveys.splice(deletedIndex, 1);

                    const activeIndex = surveys.findIndex(s => s.id === restoredSurvey.id);
                    if (activeIndex > -1) {
                        surveys.splice(activeIndex, 1, restoredSurvey);
                    } else {
                        surveys.unshift(restoredSurvey);
                    }

                    renderSidebar();
                    renderManageTable();
                    renderDeletedTable();
                    selectSurvey(restoredSurvey.id);
                } catch (error) {
                    alert(error.message || 'Failed to restore tracer form.');
                }
            }

            // Preview modal (updated for Likert matrix)
            function openPreview() {
                syncBuilderToSurvey();
                const survey = getCurrentSurvey();
                if (!survey) return;
                let html = '';
                if (survey.headerPhoto) {
                    html += `<img src="${survey.headerPhoto}" class="preview-header-img" alt="Header">`;
                }
                html += `<h2>${survey.title}</h2>`;
                survey.questions.forEach(q => {
                    html += '<div class="preview-question">';
                    html += `<h3>${q.question_text || 'Untitled'}</h3>`;
                    if (q.subtitle) html += `<div class="preview-subtitle">${q.subtitle}</div>`;

                    if (q.type === 'text') {
                        html += '<textarea class="form-control" placeholder="Your answer" disabled style="resize: vertical;"></textarea>';
                    } else if (q.type === 'choice' || q.type === 'checkbox') {
                        const isDropdown = q.display_type === 'dropdown';
                        if (isDropdown) {
                            html += '<select class="form-control" disabled>';
                            html += '<option value="">Select an option</option>';
                            q.options.forEach(opt => {
                                html += `<option value="${opt.label}">${opt.label}</option>`;
                            });
                            if (q.other_enabled) html += '<option value="other">Other</option>';
                            html += '</select>';
                        } else {
                            html += '<div class="preview-options">';
                            q.options.forEach(opt => {
                                html += `<label><input type="${q.type === 'choice' ? 'radio' : 'checkbox'}" disabled> ${opt.label}</label>`;
                            });
                            if (q.other_enabled) html += '<label><input type="checkbox" disabled> Other</label>';
                            html += '</div>';
                        }
                    } else if (q.type === 'likert') {
                        const points = q.scale_points || 5;
                        const labels = q.scale_labels || [];
                        const statements = q.statements || [];

                        html += '<div class="preview-likert-table">';
                        html += '<table style="width:100%; border-collapse: collapse;">';
                        html += '<thead><tr><th style="text-align:left; padding: 6px;"></th>';
                        for (let i = 0; i < points; i++) {
                            const label = labels[i] || (i+1).toString();
                            html += `<th style="text-align:center; padding: 6px; font-size:12px;">${label}</th>`;
                        }
                        html += '</tr></thead><tbody>';
                        statements.forEach(stmt => {
                            html += '<tr>';
                            html += `<td style="padding: 8px 6px; font-size:14px;">${stmt.text}</td>`;
                            for (let i = 0; i < points; i++) {
                                html += `<td style="text-align:center; padding: 6px;"><input type="radio" name="likert_${q.id}_${stmt.id}" disabled></td>`;
                            }
                            html += '</tr>';
                        });
                        html += '</tbody></table>';
                        html += '</div>';
                    } else if (q.type === 'section_header') {
                        html += '<hr style="margin: 20px 0 10px;">';
                    }
                    html += '</div>';
                });
                previewContent.innerHTML = html;
                previewModal.style.display = 'flex';
            }

            previewBtn.addEventListener('click', openPreview);
            closePreview.addEventListener('click', () => { previewModal.style.display = 'none'; });
            window.addEventListener('click', (e) => { if (e.target === previewModal) previewModal.style.display = 'none'; });

            // Branding uploads
            changeLogoBtn.addEventListener('click', () => logoFileInput.click());
            logoFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        surveyLogoText.innerHTML = `<img src="${event.target.result}" alt="Logo" style="width:100%;height:100%;object-fit:cover;">`;
                    };
                    reader.readAsDataURL(file);
                }
            });

            changePhotoBtn.addEventListener('click', () => photoFileInput.click());
            photoFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        headerPhotoImg.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Footer add question
            document.getElementById('addQuestionBtn').addEventListener('click', () => {
                const newQ = createEmptyQuestion('text');
                const card = buildCardElement(newQ);
                formBuilder.appendChild(card);
                updateQuestionNumbers();
                addBetweenButtons();
                syncBuilderToSurvey();
                enableDragDrop();
                refreshAllGoToDropdowns();
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });

            document.getElementById('addNewTracerBtn').addEventListener('click', () => { switchToEditor(); resetForm(); });
            tabAddNew.addEventListener('click', () => { switchToEditor(); resetForm(); });
            tabManage.addEventListener('click', switchToManage);

            surveyTitleInput.addEventListener('input', () => {
                surveyTitleDisplay.textContent = surveyTitleInput.value;
                const survey = getCurrentSurvey();
                if (survey) survey.title = surveyTitleInput.value;
            });

            document.getElementById('saveBtn').addEventListener('click', async () => {
                syncBuilderToSurvey();
                const formData = collectSurveyFromBuilder();

                if (!formData.title.trim()) {
                    alert('Please enter a survey title before saving.');
                    return;
                }

                const payload = {
                    form_title: formData.title.trim(),
                    form_description: null,
                    form_header: formData.headerPhoto,
                    is_active: formData.status === 'Active',
                    questions: formData.questions,
                };

                const currentSurvey = getCurrentSurvey();
                const shouldUpdate = !!(currentSurvey && currentSurvey.persisted && currentSurvey.id);
                const endpoint = shouldUpdate ? `${tracerUpdateBaseUrl}/${currentSurvey.id}` : tracerStoreUrl;
                const method = shouldUpdate ? 'PUT' : 'POST';

                try {
                    const response = await fetch(endpoint, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || result.message || 'Failed to save tracer form.');
                    }

                    const savedSurvey = normalizeServerSurvey(result.form);
                    const currentIndex = currentSurvey ? surveys.findIndex(s => s.id === currentSurvey.id) : -1;

                    if (currentIndex >= 0) {
                        surveys.splice(currentIndex, 1, savedSurvey);
                    } else {
                        surveys.unshift(savedSurvey);
                    }

                    currentSurveyId = savedSurvey.id;
                    renderSidebar();
                    selectSurvey(savedSurvey.id);
                    alert(result.message || 'Survey saved successfully.');
                } catch (error) {
                    alert(error.message || 'Failed to save tracer form.');
                }
            });

            // Initialize
            loadTracerData().finally(() => {
                renderSidebar();
                if (surveys.length && currentSurveyId) selectSurvey(currentSurveyId);
                else if (surveys.length) selectSurvey(surveys[0].id);
                else resetForm();
                addBetweenButtons();
                enableDragDrop();
                refreshAllGoToDropdowns();
            });
        })();
    </script>
</body>
</html>
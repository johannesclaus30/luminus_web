<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                                <button class="btn-primary" id="previewBtn">👁 Preview</button>
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
                        <div style="overflow-x: auto;">
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
                    </div>
                </div>

                <!-- ========== RIGHT SIDEBAR ========== -->
                <div class="tracer-sidebar">
                    <h2 class="sidebar-title">Alumni Tracer</h2>
                    <button id="addNewTracerBtn" class="add-tracer-btn">+ Add New Alumni Tracer</button>
                    <div class="sidebar-tabs">
                        <button class="tab-btn active" id="tabAddNew">Add New Survey</button>
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
                    questions: [
                        { id: 101, type: 'text', question_text: 'Full Name', subtitle: '', required: true },
                        { id: 102, type: 'choice', question_text: 'Employment Status', subtitle: 'Select your current status', required: true, display_type: 'list', options: [ { label: 'Employed', go_to: null }, { label: 'Unemployed', go_to: null }, { label: 'Self-employed', go_to: null } ], other_enabled: false },
                        { id: 103, type: 'likert', question_text: 'Rate your overall satisfaction', subtitle: '', required: false, scale_points: 5, scale_labels: ['Very Dissatisfied', '', '', '', 'Very Satisfied'] },
                        { id: 104, type: 'section_header', question_text: 'Educational Background', subtitle: '', required: false }
                    ]
                },
                {
                    id: 2,
                    title: 'NU Lipa SHS Tracer Study',
                    status: 'Closed',
                    logo: 'NU',
                    headerPhoto: '/assets/logos/nu_banner.png',
                    questions: [
                        { id: 201, type: 'checkbox', question_text: 'Which skills have you acquired?', subtitle: 'Select all that apply', required: true, display_type: 'list', options: [ { label: 'Communication', go_to: null }, { label: 'Leadership', go_to: null } ], other_enabled: true }
                    ]
                }
            ];

            let currentSurveyId = surveys[0].id;
            let questionCounter = 0;

            // DOM references
            const formBuilder = document.getElementById('formBuilder');
            const surveyTitleInput = document.getElementById('surveyTitleInput');
            const surveyTitleDisplay = document.getElementById('surveyTitleDisplay');
            const surveyStatusDisplay = document.getElementById('surveyStatusDisplay');
            const surveyLogoText = document.getElementById('surveyLogoText');
            const headerPhotoImg = document.getElementById('headerPhotoImg');
            const surveyListContainer = document.getElementById('surveyList');
            const manageTableBody = document.getElementById('manageTableBody');
            const tabAddNew = document.getElementById('tabAddNew');
            const tabManage = document.getElementById('tabManage');
            const editorView = document.getElementById('editorView');
            const manageView = document.getElementById('manageView');
            const previewModal = document.getElementById('previewModal');
            const previewContent = document.getElementById('previewContent');
            const closePreview = document.getElementById('closePreview');
            const previewBtn = document.getElementById('previewBtn');

            // Branding upload elements
            const changeLogoBtn = document.getElementById('changeLogoBtn');
            const logoFileInput = document.getElementById('logoFileInput');
            const changePhotoBtn = document.getElementById('changePhotoBtn');
            const photoFileInput = document.getElementById('photoFileInput');

            // ---------- Utility ----------
            function generateQuestionId() {
                return Date.now() + Math.floor(Math.random()*1000) + questionCounter++;
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
                }
                return base;
            }

            function getCurrentSurvey() {
                return surveys.find(s => s.id === currentSurveyId);
            }

            // Sync DOM back to survey object
            function syncBuilderToSurvey() {
                const survey = getCurrentSurvey();
                if (!survey) return;
                survey.logo = surveyLogoText.innerText === '' ? 'NU' : surveyLogoText.innerText;
                survey.headerPhoto = headerPhotoImg.src;
                const cards = document.querySelectorAll('.question-card-builder');
                survey.questions = [];
                cards.forEach(card => {
                    const q = {
                        id: parseInt(card.dataset.questionId),
                        type: card.querySelector('.type-select').value,
                        question_text: card.querySelector('.question-text-input').value,
                        subtitle: card.querySelector('.subtitle-input').value,
                        required: card.querySelector('.required-toggle input').checked
                    };
                    if (q.type === 'choice' || q.type === 'checkbox') {
                        q.options = [];
                        card.querySelectorAll('.option-row').forEach(row => {
                            q.options.push({
                                label: row.querySelector('.option-label').value,
                                go_to: row.querySelector('.goto-select')?.value || null
                            });
                        });
                        q.other_enabled = card.querySelector('.other-toggle input')?.checked || false;
                        q.display_type = card.querySelector('.display-type-toggle input')?.checked ? 'dropdown' : 'list';
                    } else if (q.type === 'likert') {
                        q.scale_points = parseInt(card.querySelector('.scale-points-input').value) || 5;
                        q.scale_labels = Array.from(card.querySelectorAll('.scale-label-item input')).map(inp => inp.value);
                    }
                    survey.questions.push(q);
                });
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
                    bodyElement.innerHTML = `
                        <div class="likert-setup"><div><label>Scale Points</label><input type="number" class="form-control scale-points-input" min="2" max="10" value="${points}"></div></div>
                        <div class="scale-labels-editor">
                            ${labels.map((label, i) => `
                                <div class="scale-label-item">
                                    <div class="point-number">${i+1}</div>
                                    <input type="text" class="form-control" value="${label || ''}" placeholder="Point ${i+1} label">
                                </div>
                            `).join('')}
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
                    loadTypeBody(body, { type: newType, question_text: qText, subtitle, required, options: [], scale_points: 5, scale_labels: [], other_enabled: false, display_type: 'list' });
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

            // Drag & Drop (unchanged)
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

            // Sidebar & manage functions
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

            function deleteSurvey(id) {
                if (!confirm('Are you sure?')) return;
                const idx = surveys.findIndex(s => s.id === id);
                if (idx > -1) surveys.splice(idx, 1);
                if (currentSurveyId === id) {
                    currentSurveyId = surveys.length ? surveys[0].id : null;
                    if (currentSurveyId) selectSurvey(currentSurveyId);
                    else resetForm();
                }
                renderSidebar();
                if (manageView.style.display !== 'none') renderManageTable();
            }

            // Preview modal
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
                        html += '<input type="text" class="form-control" placeholder="Your answer" disabled>';
                    } else if (q.type === 'choice' || q.type === 'checkbox') {
                        html += '<div class="preview-options">';
                        q.options.forEach(opt => {
                            html += `<label><input type="${q.type === 'choice' ? 'radio' : 'checkbox'}" disabled> ${opt.label}</label>`;
                        });
                        if (q.other_enabled) html += '<label><input type="checkbox" disabled> Other</label>';
                        html += '</div>';
                    } else if (q.type === 'likert') {
                        html += '<div class="scale-preview" style="justify-content: flex-start; gap: 15px;">';
                        for (let i = 0; i < (q.scale_points || 5); i++) {
                            html += `<div class="scale-point">${i+1}</div>`;
                        }
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

            // Initialize
            renderSidebar();
            if (surveys.length) selectSurvey(currentSurveyId);
            addBetweenButtons();
            enableDragDrop();
            refreshAllGoToDropdowns();
        })();
    </script>
</body>
</html>
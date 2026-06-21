@extends('layouts.auth')

@section('title', isset($flow) ? 'Edit Flow - Flow Builder' : 'Create Flow - Flow Builder')

@section('styles')
<!-- Drawflow CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.css">
<style>
    #drawflow {
        position: relative;
        width: 100%;
        height: 600px;
        border: 1px solid var(--border-color);
        background: var(--card-background);
        border-radius: var(--border-radius-md);
        background-size: 25px 25px;
        background-image: radial-gradient(var(--border-color) 1px, transparent 1px);
        overflow: hidden;
    }
    
    /* Customize node box */
    .drawflow .drawflow-node {
        background: var(--card-background) !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        color: var(--text-primary) !important;
        width: 200px !important;
        min-height: 90px !important;
        padding: 0 !important;
        box-shadow: var(--shadow-sm) !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .drawflow .drawflow-node.selected {
        border-color: var(--primary-color) !important;
        box-shadow: var(--shadow-md) !important;
    }
    .drawflow .drawflow-node .drawflow_content_node {
        padding: 12px 15px !important;
    }
    .drawflow .drawflow-node .title-box {
        font-weight: 700;
        font-size: 0.88rem;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .drawflow .drawflow-node .content-box {
        font-size: 0.72rem;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    /* Node Specific Borders */
    .drawflow .drawflow-node.trigger {
        border-left: 5px solid #10b981 !important; /* Green */
    }
    .drawflow .drawflow-node.send_message {
        border-left: 5px solid #3b82f6 !important; /* Blue */
    }
    .drawflow .drawflow-node.menu {
        border-left: 5px solid #8b5cf6 !important; /* Purple */
    }
    
    /* Port styling */
    .drawflow .drawflow-node .input, .drawflow .drawflow-node .output {
        width: 12px !important;
        height: 12px !important;
        background: var(--card-background) !important;
        border: 2px solid var(--primary-color) !important;
        border-radius: 50% !important;
        transition: background 0.15s ease;
    }
    .drawflow .drawflow-node .input:hover, .drawflow .drawflow-node .output:hover {
        background: var(--primary-color) !important;
    }
    .drawflow .drawflow-node .input {
        left: -6px !important;
    }
    .drawflow .drawflow-node .output {
        right: -6px !important;
    }
    
    /* Connection lines */
    .drawflow .connection .main-path {
        stroke: var(--primary-color) !important;
        stroke-width: 3px !important;
        stroke-linecap: round;
        opacity: 0.85;
    }
    
    /* Drag template style */
    .drag-node {
        background: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 15px;
        cursor: grab;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-primary);
        user-select: none;
    }
    .drag-node:hover {
        border-color: var(--primary-color);
        background: var(--input-focus-shadow);
        transform: translateY(-2px);
    }
    .drag-node i {
        font-size: 1.1rem;
    }
    
    /* Utility canvas controls */
    .canvas-controls {
        position: absolute;
        bottom: 1.5rem;
        left: 1.5rem;
        display: flex;
        gap: 8px;
        z-index: 10;
    }
    .canvas-controls .btn-circle {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--card-background);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        cursor: pointer;
        color: var(--text-primary);
    }
    .canvas-controls .btn-circle:hover {
        background-color: var(--input-focus-shadow);
        border-color: var(--primary-color);
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">
    <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle Sidebar">
        <i class="bi bi-list" style="font-size: 1.4rem;"></i>
    </button>

    <!-- Sidebar Navigation -->
    <aside class="dashboard-sidebar" id="dashboard-sidebar">
        <a href="{{ route('user.dashboard') }}" class="sidebar-brand">
            <div style="background-color: var(--primary-color); border-radius: 8px; padding: 6px; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm);">
                <i class="bi bi-whatsapp text-white" style="font-size: 1.1rem; line-height: 1;"></i>
            </div>
            <span>WhatsApp<span style="color: var(--primary-color);">SaaS</span></span>
        </a>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('user.dashboard') }}" class="sidebar-menu-link">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('contacts.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-people"></i>
                    <span>Contacts</span>
                </a>
            </li>
            <li>
                <a href="{{ route('groups.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-folder"></i>
                    <span>Contact Groups</span>
                </a>
            </li>
            <li>
                <a href="{{ route('chat.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-chat-dots"></i>
                    <span>Live Chat</span>
                </a>
            </li>
            <li>
                <a href="{{ route('wabas.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-whatsapp"></i>
                    <span>WABAs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('templates.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Templates</span>
                </a>
            </li>
            <li>
                <a href="{{ route('campaigns.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-send"></i>
                    <span>Campaigns</span>
                </a>
            </li>
            <li>
                <a href="{{ route('media.index') }}" class="sidebar-menu-link">
                    <i class="bi bi-image"></i>
                    <span>Media Library</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div style="background-color: var(--input-focus-shadow); color: var(--primary-color); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <h6 class="mb-0" style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">{{ Auth::user()->name }}</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">Tenant Client</span>
                </div>
            </div>
            <button class="btn btn-outline-danger w-100 btn-sm py-2" id="logout-btn" style="border-radius: var(--border-radius-md); font-weight: 600;">
                Log Out
            </button>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="dashboard-main">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom fade-in-element">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.2rem;">
                    {{ isset($flow) ? 'Edit Automated Flow' : 'Create Automated Flow' }}
                </h1>
                <span class="text-muted" style="font-size: 0.85rem;">Drag nodes, connect paths, and design chatbot automated conversations</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('flows.index') }}" class="btn btn-outline-secondary" style="border-radius: var(--border-radius-md); font-weight: 600;">
                    Cancel
                </a>
                <button type="button" class="btn btn-primary" id="save-flow-btn" style="border-radius: var(--border-radius-md); font-weight: 700; background-color: var(--primary-color); border-color: var(--primary-color);">
                    <i class="bi bi-cloud-check-fill me-1"></i> Save Flow Plan
                </button>
            </div>
        </header>

        <!-- Builder Grid -->
        <div class="row g-4 fade-in-element" style="animation-delay: 0.05s;">
            <!-- Left Side: Nodes Sidebar & Canvas Workspace -->
            <div class="col-lg-9 col-md-12">
                <!-- Meta Config Details Card -->
                <div class="card border p-3 mb-3" style="border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="flow_name" class="form-label fw-bold small text-muted">Flow Campaign Name</label>
                            <input type="text" id="flow_name" class="form-control form-control-custom" placeholder="e.g. Greeting Auto-Response Flow" value="{{ $flow->name ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="trigger_keywords" class="form-label fw-bold small text-muted">Trigger Keywords (comma separated)</label>
                            <input type="text" id="trigger_keywords" class="form-control form-control-custom" placeholder="hi, hello, help, menu" value="{{ isset($flow) ? implode(', ', $flow->trigger_keywords) : '' }}" required>
                        </div>
                    </div>
                </div>

                <!-- Drawflow Editor Workspace -->
                <div style="position: relative;">
                    <!-- Toolbox Sidebar Overlay -->
                    <div style="position: absolute; top: 1.25rem; left: 1.25rem; z-index: 10; display: flex; flex-direction: column; gap: 8px; width: 180px;">
                        <div class="drag-node trigger" draggable="true" ondragstart="drag(event)" data-node="trigger">
                            <i class="bi bi-play-circle-fill text-success"></i>
                            <span>Start Trigger</span>
                        </div>
                        <div class="drag-node send_message" draggable="true" ondragstart="drag(event)" data-node="send_message">
                            <i class="bi bi-chat-text-fill text-primary"></i>
                            <span>Send Message</span>
                        </div>
                        <div class="drag-node menu" draggable="true" ondragstart="drag(event)" data-node="menu">
                            <i class="bi bi-list-stars text-purple" style="color: #8b5cf6;"></i>
                            <span>Options Menu</span>
                        </div>
                    </div>

                    <!-- Canvas -->
                    <div id="drawflow" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <!-- Circle Controls -->
                        <div class="canvas-controls">
                            <button type="button" class="btn-circle" onclick="editor.zoom_in()" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
                            <button type="button" class="btn-circle" onclick="editor.zoom_out()" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
                            <button type="button" class="btn-circle" onclick="editor.zoom_reset()" title="Reset Zoom"><i class="bi bi-aspect-ratio"></i></button>
                            <button type="button" class="btn-circle text-danger" onclick="clearCanvas()" title="Clear Canvas"><i class="bi bi-x-circle-fill"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Settings Settings Drawer -->
            <div class="col-lg-3 col-md-12">
                <div class="card border p-4" style="height: 673px; border-radius: var(--border-radius-md); background-color: var(--card-background); border-color: var(--border-color) !important; position: sticky; top: 2rem; overflow-y: auto;">
                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--text-primary); font-size: 1.05rem;">Node Settings</h5>
                    
                    <div id="node-settings-empty" class="text-center text-muted py-5 my-5">
                        <i class="bi bi-gear" style="font-size: 2.5rem;"></i>
                        <p class="small mt-2 px-3">Select or double-click a node on the canvas to configure its chatbot settings.</p>
                    </div>

                    <div id="node-settings-form" class="d-none">
                        <input type="hidden" id="selected-node-id">
                        
                        <!-- Settings Container populated dynamically by type -->
                        <div id="dynamic-node-settings"></div>
                        
                        <!-- Apply Button -->
                        <div class="mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-primary btn-sm w-100 py-2" id="apply-settings-btn" style="border-radius: var(--border-radius-md); font-weight: 600; background-color: var(--primary-color); border-color: var(--primary-color);">
                                Apply Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<!-- Drawflow JS -->
<script src="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.js"></script>
<script>
    let editor;

    // Drawflow initialization
    $(document).ready(function() {
        const id = document.getElementById("drawflow");
        editor = new Drawflow(id);
        editor.start();

        // Load existing canvas data if editing
        @if(isset($flow))
            const rawCanvas = `{!! addslashes($flow->canvas_data) !!}`;
            try {
                editor.import(JSON.parse(rawCanvas));
            } catch(e) {
                console.error("Failed to load visual canvas: ", e);
            }
        @else
            // Create default trigger starting node
            createDefaultTriggerNode();
        @endif

        // Sidebar toggle
        $('#sidebar-toggle').on('click', function() {
            $('#dashboard-sidebar').toggleClass('show');
        });

        // Event hooks
        editor.on('nodeSelected', function(id) {
            const nodeData = editor.getNodeFromId(id);
            showNodeSettings(id, nodeData);
        });

        editor.on('nodeUnselected', function() {
            hideNodeSettings();
        });

        // Handle settings save
        $('#apply-settings-btn').on('click', function() {
            applyNodeSettings();
        });

        // Add menu options dynamically in settings drawer
        $(document).on('click', '#add-menu-option-btn', function() {
            addMenuOptionRow();
        });

        // Delete menu option row
        $(document).on('click', '.delete-option-row-btn', function() {
            $(this).closest('.option-row').remove();
            renumberOptionLabels();
        });

        // Save flow
        $('#save-flow-btn').on('click', function() {
            saveFlowToServer();
        });
    });

    // Node templates
    function createDefaultTriggerNode() {
        editor.addNode(
            'trigger',
            0,
            1,
            150,
            200,
            'trigger',
            { keywords: '' },
            `
            <div>
                <div class="title-box"><i class="bi bi-play-circle-fill text-success"></i> Start Trigger</div>
                <div class="content-box">Matches defined keywords</div>
            </div>
            `
        );
    }

    // Drag and Drop canvas handlers
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("node", ev.target.getAttribute('data-node'));
    }

    function drop(ev) {
        ev.preventDefault();
        const data = ev.dataTransfer.getData("node");
        
        // Calculate canvas offset positioning
        const rect = ev.target.getBoundingClientRect();
        const x = ev.clientX - rect.left;
        const y = ev.clientY - rect.top;

        addNodeToCanvas(data, x, y);
    }

    function addNodeToCanvas(type, x, y) {
        let nodeName = '';
        let numInputs = 0;
        let numOutputs = 1;
        let nodeData = {};
        let nodeHtml = '';

        if (type === 'trigger') {
            nodeName = 'trigger';
            numInputs = 0;
            numOutputs = 1;
            nodeData = { keywords: '' };
            nodeHtml = `
                <div>
                    <div class="title-box"><i class="bi bi-play-circle-fill text-success"></i> Start Trigger</div>
                    <div class="content-box">Matches defined keywords</div>
                </div>
            `;
        } else if (type === 'send_message') {
            nodeName = 'send_message';
            numInputs = 1;
            numOutputs = 1;
            nodeData = { message: 'Type response message...' };
            nodeHtml = `
                <div>
                    <div class="title-box"><i class="bi bi-chat-text-fill text-primary"></i> Send Message</div>
                    <div class="content-box text-truncate">Type message text...</div>
                </div>
            `;
        } else if (type === 'menu') {
            nodeName = 'menu';
            numInputs = 1;
            numOutputs = 0; // Starts with 0 outputs. Outputs are added dynamically as options are configured
            nodeData = { message: 'Please choose an option:', options: [] };
            nodeHtml = `
                <div>
                    <div class="title-box"><i class="bi bi-list-stars text-purple" style="color: #8b5cf6;"></i> Options Menu</div>
                    <div class="content-box">Branching choice node</div>
                </div>
            `;
        }

        editor.addNode(nodeName, numInputs, numOutputs, x, y, nodeName, nodeData, nodeHtml);
    }

    function clearCanvas() {
        Notiflix.Confirm.show(
            'Clear Workspace',
            'Are you sure you want to clear the entire Flow Builder workspace?',
            'Yes, Clear',
            'Cancel',
            function() {
                editor.clear();
                createDefaultTriggerNode();
                hideNodeSettings();
            },
            null,
            {
                okButtonBackground: 'var(--danger-color)',
                titleColor: 'var(--text-primary)',
                messageColor: 'var(--text-secondary)',
                backgroundColor: 'var(--card-background)'
            }
        );
    }

    // Node configuration panel handlers
    function showNodeSettings(id, node) {
        $('#node-settings-empty').addClass('d-none');
        $('#node-settings-form').removeClass('d-none');
        $('#selected-node-id').val(id);

        const container = $('#dynamic-node-settings');
        container.empty();

        const type = node.name;
        const data = node.data;

        if (type === 'trigger') {
            const html = `
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Keywords for Trigger</label>
                    <input type="text" id="node-trigger-keywords" class="form-control form-control-sm form-control-custom" placeholder="e.g. hi, hello" value="${data.keywords || ''}">
                    <small class="text-muted small mt-1 d-block">These local triggers will override general trigger keywords for this specific flow.</small>
                </div>
            `;
            container.append(html);
        }
        else if (type === 'send_message') {
            const html = `
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Auto-Response Message Text</label>
                    <textarea id="node-message-text" rows="5" class="form-control form-control-sm form-control-custom" placeholder="Type outgoing text reply... Use @{{name}} or @{{mobile}} for variables." style="font-size: 0.82rem;">${data.message || ''}</textarea>
                </div>
            `;
            container.append(html);
        }
        else if (type === 'menu') {
            let optionsHtml = '';
            const options = data.options || [];
            options.forEach((opt, idx) => {
                optionsHtml += `
                    <div class="option-row mb-2 border p-2 rounded" style="background-color: var(--background-color); border-color: var(--border-color) !important;">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1.5">
                            <span class="fw-bold small text-muted" style="font-size: 0.72rem;">Option #${idx + 1}</span>
                            <button type="button" class="btn btn-xs btn-link p-0 text-danger text-decoration-none delete-option-row-btn" style="font-size: 0.7rem;">Delete</button>
                        </div>
                        <input type="text" class="form-control form-control-xs form-control-custom menu-option-text" placeholder="Option description" value="${opt.text || ''}">
                    </div>
                `;
            });

            const html = `
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Menu Message Body</label>
                    <textarea id="node-menu-message" rows="3" class="form-control form-control-sm form-control-custom mb-3" placeholder="Please choose one of the options:" style="font-size: 0.82rem;">${data.message || ''}</textarea>
                    
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-bold small text-muted mb-0">Menu Selection Options</label>
                        <button type="button" class="btn btn-xs btn-primary-custom" id="add-menu-option-btn" style="font-size: 0.7rem; padding: 2px 8px;">
                            <i class="bi bi-plus-lg"></i> Add Option
                        </button>
                    </div>
                    <div id="menu-options-list">
                        ${optionsHtml}
                    </div>
                </div>
            `;
            container.append(html);
        }
    }

    function addMenuOptionRow() {
        const list = $('#menu-options-list');
        const numRows = list.find('.option-row').length + 1;
        
        const row = `
            <div class="option-row mb-2 border p-2 rounded animate-fade-in" style="background-color: var(--background-color); border-color: var(--border-color) !important;">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-1.5">
                    <span class="fw-bold small text-muted" style="font-size: 0.72rem;">Option #${numRows}</span>
                    <button type="button" class="btn btn-xs btn-link p-0 text-danger text-decoration-none delete-option-row-btn" style="font-size: 0.7rem;">Delete</button>
                </div>
                <input type="text" class="form-control form-control-xs form-control-custom menu-option-text" placeholder="Option description" value="">
            </div>
        `;
        list.append(row);
    }

    function renumberOptionLabels() {
        $('#menu-options-list .option-row').each(function(idx) {
            $(this).find('.fw-bold').text(`Option #${idx + 1}`);
        });
    }

    function hideNodeSettings() {
        $('#node-settings-form').addClass('d-none');
        $('#node-settings-empty').removeClass('d-none');
        $('#selected-node-id').val('');
    }

    function applyNodeSettings() {
        const id = $('#selected-node-id').val();
        if (!id) return;

        const node = editor.getNodeFromId(id);
        const type = node.name;
        const data = {};

        if (type === 'trigger') {
            data.keywords = $('#node-trigger-keywords').val();
            
            // Update visual text
            const textPreview = data.keywords ? `Trigger: ${data.keywords}` : 'Matches defined keywords';
            editor.updateNodeDataFromId(id, data);
            
            $(`#node-${id} .content-box`).text(textPreview);
        }
        else if (type === 'send_message') {
            data.message = $('#node-message-text').val();
            editor.updateNodeDataFromId(id, data);
            
            const textPreview = data.message ? data.message : 'Type message text...';
            $(`#node-${id} .content-box`).text(textPreview);
        }
        else if (type === 'menu') {
            data.message = $('#node-menu-message').val();
            data.options = [];

            $('#menu-options-list .option-row').each(function() {
                const optText = $(this).find('.menu-option-text').val();
                data.options.push({ text: optText });
            });

            // Dynamically manage node output connections in Drawflow based on menu option count
            const numOptions = data.options.length;
            const currentOutputs = Object.keys(node.outputs).length;

            // Remove excessive outputs
            if (currentOutputs > numOptions) {
                for (let i = currentOutputs; i > numOptions; i--) {
                    editor.removeNodeOutput(id, `output_${i}`);
                }
            }
            // Add required outputs
            else if (currentOutputs < numOptions) {
                for (let i = currentOutputs + 1; i <= numOptions; i++) {
                    editor.addNodeOutput(id);
                }
            }

            editor.updateNodeDataFromId(id, data);
            $(`#node-${id} .content-box`).text(`Menu contains ${numOptions} branching option paths`);
        }

        Notiflix.Notify.success('Node settings applied to canvas.');
        hideNodeSettings();
    }

    // Save Flow JSON state
    function saveFlowToServer() {
        const flowName = $('#flow_name').val().trim();
        const triggerRaw = $('#trigger_keywords').val().trim();

        if (!flowName) {
            Notiflix.Notify.failure('Please enter a Flow Name.');
            return;
        }

        if (!triggerRaw) {
            Notiflix.Notify.failure('Please enter at least one general Trigger Keyword.');
            return;
        }

        // Map general triggers to array
        const keywords = triggerRaw.split(',').map(k => k.trim()).filter(k => k !== '');

        // Validate canvas compiles
        const compiled = compileFlow();
        if (!compiled) return; // compile failed (e.g. no Trigger node)

        const canvasJson = JSON.stringify(editor.export());

        Notiflix.Loading.circle('Saving Chatbot Flow...');

        // Determine method & route for Create vs Edit
        const isEdit = "{{ isset($flow) ? 'true' : 'false' }}" === 'true';
        const url = isEdit ? `/flows/{{ $flow->id ?? 0 }}` : '/flows';
        const method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: {
                _token: "{{ csrf_token() }}",
                name: flowName,
                trigger_keywords: keywords,
                canvas_data: canvasJson,
                compiled_data: compiled
            },
            success: function(response) {
                Notiflix.Loading.remove();
                if (response.status) {
                    Notiflix.Notify.success(response.message);
                    setTimeout(function() {
                        window.location.href = response.redirect_url;
                    }, 1200);
                } else {
                    Notiflix.Notify.failure(response.message || 'Saving failed.');
                }
            },
            error: function(xhr) {
                Notiflix.Loading.remove();
                let msg = 'Failed to save flow to server.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Notiflix.Notify.failure(msg);
            }
        });
    }

    // Compile Drawflow nodes to backend execution graph
    function compileFlow() {
        const exportData = editor.export();
        const drawflowData = exportData.drawflow.Home.data;

        const nodes = {};
        let startNodeId = null;

        // Find trigger node
        Object.keys(drawflowData).forEach(nodeId => {
            const rawNode = drawflowData[nodeId];
            if (rawNode.name === 'trigger') {
                startNodeId = nodeId;
            }
        });

        if (!startNodeId) {
            Notiflix.Notify.failure("Visual validation error: Please add a Start Trigger node to your canvas.");
            return null;
        }

        // Process all nodes
        Object.keys(drawflowData).forEach(nodeId => {
            const rawNode = drawflowData[nodeId];
            const nodeType = rawNode.name;
            const nodeData = rawNode.data;

            const compiledNode = {
                id: nodeId,
                type: nodeType
            };

            if (nodeType === 'trigger') {
                compiledNode.keywords = nodeData.keywords ? nodeData.keywords.split(',').map(k => k.trim()) : [];
                // Link trigger output 1 connection
                const conn = rawNode.outputs.output_1?.connections[0];
                compiledNode.next_node_id = conn ? conn.node : null;
            }
            else if (nodeType === 'send_message') {
                compiledNode.message = nodeData.message || '';
                const conn = rawNode.outputs.output_1?.connections[0];
                compiledNode.next_node_id = conn ? conn.node : null;
            }
            else if (nodeType === 'menu') {
                compiledNode.message = nodeData.message || '';
                compiledNode.options = {};
                
                const optionsList = nodeData.options || [];
                optionsList.forEach((opt, idx) => {
                    const portName = `output_${idx + 1}`;
                    const conn = rawNode.outputs[portName]?.connections[0];
                    
                    compiledNode.options[idx + 1] = {
                        text: opt.text,
                        next_node_id: conn ? conn.node : null
                    };
                });
            }

            nodes[nodeId] = compiledNode;
        });

        return {
            start_node_id: startNodeId,
            nodes: nodes
        };
    }
</script>
@endsection

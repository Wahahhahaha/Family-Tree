@extends('layouts.app')

@section('title', __('management_console.title'))
@section('body-class', 'page-family-tree')

@section('styles')
<style>
    .console-card {
        margin-top: 18px;
        width: 100%;
        max-width: none;
        box-sizing: border-box;
        background: #ffffff;
        border: 1px solid #eaf1f6;
        border-radius: 20px;
        box-shadow: var(--shadow);
        padding: 18px;
    }

    .console-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding: 4px 2px 0;
    }

    .console-title {
        margin: 0 0 6px;
        font-size: 24px;
        font-family: "Sora", sans-serif;
        color: #12344a;
    }

    .console-subtitle {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
        max-width: 860px;
    }

    .console-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(300px, 0.9fr);
        gap: 16px;
        align-items: start;
    }

    .console-panel,
    .console-side,
    .console-log-shell {
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        border: 1px solid #e9f0f6;
        border-radius: 18px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .console-panel,
    .console-side {
        padding: 18px;
    }

    .tool-switcher {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
    }

    .tool-pill {
        appearance: none;
        border: 1px solid #d8e5ef;
        background: #f7fbfe;
        color: #1b4a65;
        border-radius: 999px;
        padding: 10px 14px;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.18s ease;
    }

    .tool-pill:hover {
        background: #eef7fc;
        border-color: #c7dceb;
    }

    .tool-pill.is-active {
        color: #ffffff;
        background: linear-gradient(120deg, var(--primary), #35b0df);
        border-color: transparent;
        box-shadow: 0 10px 18px rgba(31, 154, 214, 0.16);
    }

    .console-field label,
    .console-side h3,
    .console-log-head h3 {
        display: block;
        margin: 0 0 10px;
        color: #12344a;
        font-size: 14px;
        font-weight: 800;
    }

    .console-input {
        width: 100%;
        min-height: 220px;
        resize: vertical;
        padding: 16px;
        border-radius: 14px;
        border: 1px solid #d7e3ec;
        background: #f8fbfd;
        color: #102133;
        font-family: Consolas, Monaco, "Courier New", monospace;
        font-size: 14px;
        line-height: 1.65;
        outline: none;
        transition: 0.2s ease;
    }

    .console-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(31, 154, 214, 0.12);
        background: #ffffff;
    }

    .console-hint {
        margin-top: 10px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .console-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .console-btn {
        height: 40px;
        border-radius: 12px;
        padding: 0 16px;
        border: 0;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.18s ease;
    }

    .console-btn:hover {
        transform: translateY(-1px);
    }

    .console-btn:disabled {
        cursor: not-allowed;
        opacity: 0.72;
        transform: none;
    }

    .console-btn--primary {
        color: #ffffff;
        background: linear-gradient(120deg, var(--primary), #35b0df);
    }

    .console-btn--ghost {
        color: #1b4a65;
        background: #f7fbfe;
        border: 1px solid #d8e5ef;
    }

    .console-side {
        display: grid;
        gap: 12px;
    }

    .console-preset-card {
        border-radius: 14px;
        padding: 14px;
        background: #f8fbfd;
        border: 1px solid #e2edf4;
    }

    .console-preset-card strong {
        display: block;
        margin-bottom: 6px;
        color: #12344a;
        font-size: 13px;
    }

    .console-preset-card code {
        display: block;
        white-space: pre-wrap;
        word-break: break-word;
        color: #3f5d71;
        font-family: Consolas, Monaco, "Courier New", monospace;
        font-size: 12.5px;
        line-height: 1.6;
    }

    .console-log-shell {
        margin-top: 16px;
        padding: 18px;
    }

    .console-log-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 12px;
    }

    .console-log-meta {
        color: var(--muted);
        font-size: 13px;
    }

    .console-log-stream {
        display: grid;
        gap: 12px;
        max-height: 520px;
        overflow: auto;
        padding-right: 4px;
    }

    .console-entry {
        border-radius: 14px;
        padding: 14px 14px 13px;
        background: #f8fbfd;
        border: 1px solid #e2edf4;
    }

    .console-entry--system {
        background: linear-gradient(135deg, #f4fbff, #f6fcf8);
    }

    .console-entry__head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 10px;
    }

    .console-entry__tool {
        color: #1b587d;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 11px;
        font-weight: 800;
    }

    .console-entry__time {
        color: var(--muted);
        font-size: 12px;
    }

    .console-entry__command,
    .console-entry__output {
        white-space: pre-wrap;
        word-break: break-word;
        font-family: Consolas, Monaco, "Courier New", monospace;
        font-size: 13px;
        line-height: 1.65;
    }

    .console-entry__command {
        margin-bottom: 8px;
        color: #12344a;
        font-weight: 700;
    }

    .console-entry__output {
        margin: 0;
        color: #334f63;
    }

    .console-entry--success {
        border-left: 4px solid var(--accent);
    }

    .console-entry--error {
        border-left: 4px solid #ef7d57;
    }

    .console-note {
        margin-top: 10px;
        color: var(--muted);
        font-size: 12px;
    }

    @media (max-width: 1024px) {
        .console-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .console-card {
            padding: 16px;
            border-radius: 18px;
        }

        .console-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .console-panel,
        .console-side,
        .console-log-shell {
            padding: 16px;
        }
    }
</style>
@endsection

@section('content')
<div class="wrapper">
    <section class="console-card">
        <div class="console-head">
            <div>
                <h2 class="console-title">{{ __('management_console.title') }}</h2>
                <p class="console-subtitle">{{ __('management_console.description') }}</p>
            </div>
        </div>

        <div class="console-grid">
            <div class="console-panel">
                <div class="tool-switcher" role="tablist" aria-label="Console tools">
                    <button type="button" class="tool-pill is-active" data-tool="artisan">{{ __('management_console.artisan') }}</button>
                    <button type="button" class="tool-pill" data-tool="tinker">{{ __('management_console.tinker') }}</button>
                    <button type="button" class="tool-pill" data-tool="sql">{{ __('management_console.sql') }}</button>
                </div>

                <div class="console-field">
                    <label for="consoleCommand" id="consoleLabel">{{ __('management_console.artisan_command') }}</label>
                    <textarea id="consoleCommand" class="console-input" spellcheck="false" placeholder="{{ __('management_console.artisan') }}">{{ __('management_console.artisan_sample') }}</textarea>
                    <div id="consoleHint" class="console-hint">{{ __('management_console.artisan_hint') }}</div>
                </div>

                <div class="console-actions">
                    <button type="button" id="runConsoleBtn" class="console-btn console-btn--primary">{{ __('management_console.run_command') }}</button>
                    <button type="button" id="clearConsoleBtn" class="console-btn console-btn--ghost">{{ __('management_console.clear_log') }}</button>
                </div>
            </div>

            <aside class="console-side">
                <h3>{{ __('management_console.quick_examples') }}</h3>
                <div class="console-preset-card">
                    <strong>{{ __('management_console.artisan') }}</strong>
                    <code>migrate --status</code>
                    <code>route:list</code>
                </div>
                <div class="console-preset-card">
                    <strong>{{ __('management_console.tinker') }}</strong>
                    <code>return DB::table('user')->count();</code>
                    <code>return App\Models\User::latest()->first();</code>
                </div>
                <div class="console-preset-card">
                    <strong>{{ __('management_console.raw_sql') }}</strong>
                    <code>SELECT * FROM user LIMIT 5;</code>
                    <code>UPDATE user SET deleted_at = NOW() WHERE userid = 1;</code>
                </div>
                <div class="console-note">
                    {{ __('management_console.quick_examples') }}.
                </div>
            </aside>
        </div>

        <div class="console-log-shell">
            <div class="console-log-head">
                <div>
                    <h3>{{ __('management_console.log') }}</h3>
                    <div class="console-log-meta">{{ __('management_console.log_description') }}</div>
                </div>
                <div class="console-log-meta" id="consoleLogCount">1 {{ __('management_console.entry') }}</div>
            </div>

            <div id="consoleLog" class="console-log-stream" aria-live="polite">
                <div class="console-entry console-entry--system">
                    <div class="console-entry__head">
                        <span class="console-entry__tool">{{ __('management_console.system') }}</span>
                        <span class="console-entry__time"><?php echo e(now()->toDateTimeString()); ?></span>
                    </div>
                    <pre class="console-entry__output">{{ __('management_console.ready') }}</pre>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var activeTool = 'artisan';
    var toolMap = {
        artisan: {
            label: @json(__('management_console.artisan_command')),
            hint: @json(__('management_console.artisan_hint')),
            sample: @json(__('management_console.artisan_sample'))
        },
        tinker: {
            label: @json(__('management_console.php_code')),
            hint: @json(__('management_console.tinker_hint')),
            sample: @json(__('management_console.tinker_sample'))
        },
        sql: {
            label: @json(__('management_console.raw_sql')),
            hint: @json(__('management_console.sql_hint')),
            sample: @json(__('management_console.sql_sample'))
        }
    };

    var commandInput = document.getElementById('consoleCommand');
    var runBtn = document.getElementById('runConsoleBtn');
    var clearBtn = document.getElementById('clearConsoleBtn');
    var consoleHint = document.getElementById('consoleHint');
    var consoleLabel = document.getElementById('consoleLabel');
    var consoleLog = document.getElementById('consoleLog');
    var consoleLogCount = document.getElementById('consoleLogCount');
    var toolButtons = Array.prototype.slice.call(document.querySelectorAll('.tool-pill'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var textRunning = @json(__('management_console.running'));
    var textCommandEmpty = @json(__('management_console.command_empty'));
    var textServerError = @json(__('management_console.server_error'));
    var textCleared = @json(__('management_console.cleared'));
    var textNoOutput = @json(__('management_console.no_output'));
    var textEntry = @json(__('management_console.entry'));
    var textEntries = @json(__('management_console.entries'));

    function normalizeValue(value) {
        if (value === null || value === undefined) {
            return textNoOutput;
        }

        if (typeof value === 'string') {
            return value.trim() === '' ? textNoOutput : value;
        }

        if (typeof value === 'number' || typeof value === 'boolean') {
            return String(value);
        }

        if (Array.isArray(value)) {
            if (value.length === 0) {
                return '[]';
            }

            return value.map(function (item, index) {
                return (index + 1) + '. ' + normalizeValue(item).replace(/\n/g, '\n   ');
            }).join('\n');
        }

        if (typeof value === 'object') {
            var keys = Object.keys(value);
            if (keys.length === 0) {
                return '{}';
            }

            return keys.map(function (key) {
                return key + ': ' + normalizeValue(value[key]).replace(/\n/g, '\n  ');
            }).join('\n');
        }

        return String(value);
    }

    function updateCount() {
        var count = consoleLog.querySelectorAll('.console-entry:not(.console-entry--system)').length + 1;
        consoleLogCount.textContent = count + ' ' + (count === 1 ? textEntry : textEntries);
    }

    function appendEntry(tool, command, output, success, durationMs, executedAt) {
        var entry = document.createElement('div');
        entry.className = 'console-entry ' + (success ? 'console-entry--success' : 'console-entry--error');

        var head = document.createElement('div');
        head.className = 'console-entry__head';

        var toolEl = document.createElement('span');
        toolEl.className = 'console-entry__tool';
        toolEl.textContent = tool;

        var timeEl = document.createElement('span');
        timeEl.className = 'console-entry__time';
        timeEl.textContent = executedAt + ' | ' + durationMs + ' ms';

        var commandEl = document.createElement('div');
        commandEl.className = 'console-entry__command';
        commandEl.textContent = '> ' + command;

        var outputEl = document.createElement('pre');
        outputEl.className = 'console-entry__output';
        outputEl.textContent = normalizeValue(output);

        head.appendChild(toolEl);
        head.appendChild(timeEl);
        entry.appendChild(head);
        entry.appendChild(commandEl);
        entry.appendChild(outputEl);
        consoleLog.appendChild(entry);
        consoleLog.scrollTop = consoleLog.scrollHeight;
        updateCount();
    }

    function setTool(tool) {
        activeTool = tool;
        toolButtons.forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-tool') === tool);
        });

        if (toolMap[tool]) {
            consoleLabel.textContent = toolMap[tool].label;
            consoleHint.textContent = toolMap[tool].hint;
            if (!commandInput.value.trim()) {
                commandInput.value = toolMap[tool].sample;
            }
        }
    }

    function runConsole() {
        var command = commandInput.value.trim();
        if (!command) {
            appendEntry(activeTool, '', textCommandEmpty, false, 0, new Date().toLocaleString());
            return;
        }

        runBtn.disabled = true;
        runBtn.textContent = textRunning;

        fetch('/management/console/run', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                tool: activeTool,
                command: command
            })
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            });
        })
        .then(function (result) {
            var data = result.data || {};
            if (!result.ok || data.success === false) {
                appendEntry(activeTool, command, data.message || data.error || textServerError, false, data.duration_ms || 0, data.executed_at || new Date().toLocaleString());
                return;
            }

            appendEntry(activeTool, command, data.output, true, data.duration_ms || 0, data.executed_at || new Date().toLocaleString());
        })
        .catch(function (error) {
            appendEntry(activeTool, command, 'Error: ' + error.message, false, 0, new Date().toLocaleString());
        })
        .finally(function () {
            runBtn.disabled = false;
            runBtn.textContent = @json(__('management_console.run_command'));
        });
    }

    toolButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setTool(btn.getAttribute('data-tool'));
        });
    });

    runBtn.addEventListener('click', runConsole);

    clearBtn.addEventListener('click', function () {
        consoleLog.innerHTML = '';
        var entry = document.createElement('div');
        entry.className = 'console-entry console-entry--system';
        entry.innerHTML = '<div class="console-entry__head"><span class="console-entry__tool">' + @json(__('management_console.system')) + '</span><span class="console-entry__time">' + new Date().toLocaleString() + '</span></div><pre class="console-entry__output">' + @json(__('management_console.cleared')) + '</pre>';
        consoleLog.appendChild(entry);
        updateCount();
    });

    commandInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
            event.preventDefault();
            runConsole();
        }
    });

    document.addEventListener('click', function (event) {
        var code = event.target.closest('.console-preset-card code');
        if (code) {
            commandInput.value = code.textContent.trim();
            commandInput.focus();
        }
    });

    setTool(activeTool);
    updateCount();
})();
</script>
@endsection

<?php
use App\Auth;
use App\Helper;

$pageTitle = 'Interactive Whiteboard — Infinity Learning';
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm" style="padding:1rem 0 2rem">
    <div class="container" style="max-width:1400px">
        <div class="flex items-center justify-between" style="flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
            <div>
                <a href="javascript:history.back()" class="back-link">← Back</a>
                <h1 style="font-size:1.6rem;margin:.2rem 0">🎨 Interactive Digital Whiteboard</h1>
                <p class="muted" style="font-size:.85rem;margin:0">Live teaching canvas for problem-solving, diagramming, annotations, and coaching.</p>
            </div>
            <div class="flex items-center" style="gap:.5rem;flex-wrap:wrap">
                <a href="/chess/live" class="btn btn-secondary btn-sm">♟️ Return to Live Chess</a>
                <button type="button" id="btn-save-wb" class="btn btn-primary btn-sm">💾 Download Snapshot (PNG)</button>
            </div>
        </div>

        <!-- Whiteboard Shell -->
        <div class="card" style="padding:0;overflow:hidden;border-radius:14px;box-shadow:0 12px 30px rgba(0,0,0,0.12)">
            <!-- Top Toolbar -->
            <div class="wb-toolbar" style="display:flex;align-items:center;justify-content:between;gap:.5rem;padding:.75rem 1rem;background:var(--surface-2);border-bottom:1px solid var(--border);flex-wrap:wrap">
                <!-- Drawing Tools -->
                <div class="flex items-center" style="gap:.35rem">
                    <button type="button" class="btn btn-ghost btn-sm tool-btn active" data-tool="pen" title="Pencil / Freehand">✏️ Pen</button>
                    <button type="button" class="btn btn-ghost btn-sm tool-btn" data-tool="highlighter" title="Highlighter">🖍️ Highlight</button>
                    <button type="button" class="btn btn-ghost btn-sm tool-btn" data-tool="line" title="Straight Line">📏 Line</button>
                    <button type="button" class="btn btn-ghost btn-sm tool-btn" data-tool="arrow" title="Pointer Arrow">➡️ Arrow</button>
                    <button type="button" class="btn btn-ghost btn-sm tool-btn" data-tool="rect" title="Rectangle">⬜ Box</button>
                    <button type="button" class="btn btn-ghost btn-sm tool-btn" data-tool="circle" title="Circle">⭕ Circle</button>
                    <button type="button" class="btn btn-ghost btn-sm tool-btn" data-tool="eraser" title="Eraser">🧹 Eraser</button>
                </div>

                <div style="height:24px;width:1px;background:var(--border);margin:0 .3rem"></div>

                <!-- Colors -->
                <div class="flex items-center" style="gap:.35rem">
                    <span class="muted" style="font-size:.78rem">Color:</span>
                    <?php 
                    $colors = ['#1e293b', '#ef4444', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#ffffff'];
                    foreach ($colors as $c): ?>
                        <button type="button" class="color-swatch <?= $c === '#1e293b' ? 'active' : '' ?>" data-color="<?= $c ?>" 
                                style="width:22px;height:22px;border-radius:50%;background:<?= $c ?>;border:2px solid <?= $c === '#ffffff' ? '#cbd5e1' : '#fff' ?>;box-shadow:0 1px 3px rgba(0,0,0,0.2);cursor:pointer;outline:none"></button>
                    <?php endforeach; ?>
                </div>

                <div style="height:24px;width:1px;background:var(--border);margin:0 .3rem"></div>

                <!-- Stroke Size -->
                <div class="flex items-center" style="gap:.4rem">
                    <span class="muted" style="font-size:.78rem">Size:</span>
                    <input type="range" id="wb-size" min="2" max="28" value="4" style="width:70px;cursor:pointer">
                    <span id="wb-size-val" style="font-size:.78rem;font-weight:600;min-width:20px">4px</span>
                </div>

                <div style="height:24px;width:1px;background:var(--border);margin:0 .3rem"></div>

                <!-- Background Grid Selector -->
                <div class="flex items-center" style="gap:.4rem">
                    <span class="muted" style="font-size:.78rem">Grid:</span>
                    <select id="wb-grid-select" class="input" style="padding:.2rem .4rem;font-size:.8rem;height:auto">
                        <option value="plain">Plain White</option>
                        <option value="math">Graph Grid</option>
                        <option value="ruled">Ruled Lines</option>
                        <option value="dark">Chalkboard Dark</option>
                    </select>
                </div>

                <div style="margin-left:auto;display:flex;align-items:center;gap:.4rem">
                    <button type="button" id="btn-undo" class="btn btn-ghost btn-sm" title="Undo">↩ Undo</button>
                    <button type="button" id="btn-clear" class="btn btn-ghost btn-sm" style="color:var(--danger)" title="Clear Entire Canvas">🗑️ Clear</button>
                </div>
            </div>

            <!-- Canvas Container -->
            <div id="wb-canvas-wrap" style="position:relative;width:100%;height:750px;background:#ffffff;overflow:hidden;cursor:crosshair">
                <canvas id="wb-canvas" style="position:absolute;top:0;left:0;width:100%;height:100%"></canvas>
            </div>
        </div>
    </div>
</section>

<style>
.tool-btn.active {
    background: var(--primary) !important;
    color: #fff !important;
}
.color-swatch.active {
    outline: 2px solid var(--primary) !important;
    transform: scale(1.15);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrap = document.getElementById('wb-canvas-wrap');
    const canvas = document.getElementById('wb-canvas');
    const ctx = canvas.getContext('2d');

    let currentTool = 'pen';
    let currentColor = '#1e293b';
    let currentSize = 4;
    let isDrawing = false;
    let startX = 0;
    let startY = 0;
    let snapshot = null;
    let history = [];
    const MAX_HISTORY = 20;

    // Canvas resize & resolution
    function resizeCanvas() {
        const rect = wrap.getBoundingClientRect();
        // Save current contents
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const tempCtx = tempCanvas.getContext('2d');
        tempCtx.drawImage(canvas, 0, 0);

        canvas.width = rect.width;
        canvas.height = rect.height;

        redrawBackground();
        ctx.drawImage(tempCanvas, 0, 0);
    }

    function redrawBackground() {
        const gridType = document.getElementById('wb-grid-select').value;
        const w = canvas.width;
        const h = canvas.height;

        if (gridType === 'dark') {
            wrap.style.background = '#0f172a';
        } else {
            wrap.style.background = '#ffffff';
        }

        // Draw background onto canvas underlying style
        if (gridType === 'math') {
            wrap.style.backgroundImage = 'radial-gradient(circle, #cbd5e1 1px, transparent 1px)';
            wrap.style.backgroundSize = '24px 24px';
        } else if (gridType === 'ruled') {
            wrap.style.backgroundImage = 'linear-gradient(#e2e8f0 1px, transparent 1px)';
            wrap.style.backgroundSize = '100% 32px';
        } else {
            wrap.style.backgroundImage = 'none';
        }
    }

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
    saveState();

    // Tool Buttons
    document.querySelectorAll('.tool-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTool = btn.dataset.tool;
        });
    });

    // Swatches
    document.querySelectorAll('.color-swatch').forEach(sw => {
        sw.addEventListener('click', () => {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
            sw.classList.add('active');
            currentColor = sw.dataset.color;
        });
    });

    // Stroke size
    const sizeSlider = document.getElementById('wb-size');
    const sizeVal = document.getElementById('wb-size-val');
    sizeSlider.addEventListener('input', (e) => {
        currentSize = parseInt(e.target.value);
        sizeVal.textContent = currentSize + 'px';
    });

    // Grid select
    document.getElementById('wb-grid-select').addEventListener('change', () => {
        redrawBackground();
    });

    function saveState() {
        if (history.length >= MAX_HISTORY) {
            history.shift();
        }
        history.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
    }

    // Undo
    document.getElementById('btn-undo').addEventListener('click', () => {
        if (history.length > 1) {
            history.pop();
            const prevState = history[history.length - 1];
            ctx.putImageData(prevState, 0, 0);
        }
    });

    // Clear
    document.getElementById('btn-clear').addEventListener('click', () => {
        if (confirm('Clear the entire whiteboard?')) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            saveState();
        }
    });

    // Download PNG
    document.getElementById('btn-save-wb').addEventListener('click', () => {
        // Create export canvas with baked background
        const expCanvas = document.createElement('canvas');
        expCanvas.width = canvas.width;
        expCanvas.height = canvas.height;
        const expCtx = expCanvas.getContext('2d');

        const gridType = document.getElementById('wb-grid-select').value;
        expCtx.fillStyle = gridType === 'dark' ? '#0f172a' : '#ffffff';
        expCtx.fillRect(0, 0, expCanvas.width, expCanvas.height);
        expCtx.drawImage(canvas, 0, 0);

        const a = document.createElement('a');
        a.download = 'whiteboard-' + new Date().toISOString().slice(0,10) + '.png';
        a.href = expCanvas.toDataURL('image/png');
        a.click();
    });

    // Drawing handlers
    function getCoords(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDraw(e) {
        isDrawing = true;
        const coords = getCoords(e);
        startX = coords.x;
        startY = coords.y;
        snapshot = ctx.getImageData(0, 0, canvas.width, canvas.height);

        ctx.beginPath();
        ctx.moveTo(startX, startY);

        if (currentTool === 'eraser') {
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = currentSize * 3;
            ctx.globalAlpha = 1.0;
        } else if (currentTool === 'highlighter') {
            ctx.strokeStyle = currentColor;
            ctx.lineWidth = currentSize * 4;
            ctx.globalAlpha = 0.35;
        } else {
            ctx.strokeStyle = currentColor;
            ctx.lineWidth = currentSize;
            ctx.globalAlpha = 1.0;
        }
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    function draw(e) {
        if (!isDrawing) return;
        const coords = getCoords(e);

        if (currentTool === 'pen' || currentTool === 'eraser' || currentTool === 'highlighter') {
            ctx.lineTo(coords.x, coords.y);
            ctx.stroke();
        } else {
            // Restore snapshot for shape preview
            ctx.putImageData(snapshot, 0, 0);
            ctx.beginPath();

            if (currentTool === 'line') {
                ctx.moveTo(startX, startY);
                ctx.lineTo(coords.x, coords.y);
                ctx.stroke();
            } else if (currentTool === 'rect') {
                ctx.strokeRect(startX, startY, coords.x - startX, coords.y - startY);
            } else if (currentTool === 'circle') {
                const radius = Math.sqrt(Math.pow(coords.x - startX, 2) + Math.pow(coords.y - startY, 2));
                ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
                ctx.stroke();
            } else if (currentTool === 'arrow') {
                drawArrow(startX, startY, coords.x, coords.y);
            }
        }
    }

    function drawArrow(fromx, fromy, tox, toy) {
        const headlen = 12 + currentSize;
        const angle = Math.atan2(toy - fromy, tox - fromx);
        ctx.moveTo(fromx, fromy);
        ctx.lineTo(tox, toy);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(tox, toy);
        ctx.lineTo(tox - headlen * Math.cos(angle - Math.PI / 6), toy - headlen * Math.sin(angle - Math.PI / 6));
        ctx.moveTo(tox, toy);
        ctx.lineTo(tox - headlen * Math.cos(angle + Math.PI / 6), toy - headlen * Math.sin(angle + Math.PI / 6));
        ctx.stroke();
    }

    function stopDraw() {
        if (isDrawing) {
            isDrawing = false;
            ctx.globalAlpha = 1.0;
            saveState();
        }
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDraw);

    // Touch support
    canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startDraw(e); }, { passive: false });
    canvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e); }, { passive: false });
    window.addEventListener('touchend', stopDraw);
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>

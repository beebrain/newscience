<?php
$id = (int) ($id ?? 0);
$checkUrl = base_url('p/' . $id . '/check');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กำลังตรวจสอบระบบ | Program SPA</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Noto+Sans+Thai:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        * { font-family: 'Inter', 'Noto Sans Thai', sans-serif; }
        body { background: #0a0f1e; }
        .ring-anim {
            width: 80px; height: 80px; border: 2px solid rgba(218,165,32,0.15); border-top-color: #daa520;
            border-radius: 50%; animation: spin 1s linear infinite;
        }
        .ring-outer {
            width: 100px; height: 100px; border: 1px solid rgba(218,165,32,0.08); border-bottom-color: rgba(218,165,32,0.3);
            border-radius: 50%; animation: spin 2.5s linear infinite reverse; position: absolute;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .check-icon { opacity: 0; transform: scale(0.5); transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        .check-icon.show { opacity: 1; transform: scale(1); }
        .status-text { transition: all 0.5s; }
        .gold-glow { text-shadow: 0 0 20px rgba(218,165,32,0.4); }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center text-white antialiased">
    <div class="text-center max-w-md mx-auto px-6">
        <div id="spinner-wrap" class="relative flex items-center justify-center mb-10 h-[100px]">
            <div class="ring-outer"></div>
            <div class="ring-anim"></div>
        </div>
        <div id="check-icon" class="check-icon mb-10 h-[100px] flex items-center justify-center hidden">
            <div class="w-20 h-20 rounded-full bg-emerald-500/20 flex items-center justify-center">
                <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </div>
        </div>

        <h1 id="status-title" class="text-xl font-semibold text-white mb-2 status-text">กำลังตรวจสอบระบบ</h1>
        <p id="status-sub" class="text-slate-400 text-sm mb-8 status-text">ตรวจสอบฐานข้อมูลและข้อมูลหลักสูตร...</p>

        <div id="steps" class="text-left space-y-3 mb-8">
            <div class="flex items-center gap-3 text-sm" id="step-tables">
                <div class="w-5 h-5 rounded-full border border-slate-600 flex items-center justify-center step-dot">
                    <div class="w-2 h-2 rounded-full bg-slate-600 animate-pulse"></div>
                </div>
                <span class="text-slate-400">ตรวจสอบตารางฐานข้อมูล</span>
            </div>
            <div class="flex items-center gap-3 text-sm" id="step-data">
                <div class="w-5 h-5 rounded-full border border-slate-600 flex items-center justify-center step-dot">
                    <div class="w-2 h-2 rounded-full bg-slate-600"></div>
                </div>
                <span class="text-slate-400">ตรวจสอบข้อมูลหลักสูตร</span>
            </div>
            <div class="flex items-center gap-3 text-sm" id="step-ready">
                <div class="w-5 h-5 rounded-full border border-slate-600 flex items-center justify-center step-dot">
                    <div class="w-2 h-2 rounded-full bg-slate-600"></div>
                </div>
                <span class="text-slate-400">เตรียมระบบ</span>
            </div>
        </div>

        <div id="error" class="hidden mt-4 p-4 rounded-xl bg-red-900/20 border border-red-800/50">
            <p id="error-message" class="text-red-300 text-sm"></p>
        </div>
    </div>

    <script>
(function() {
    var checkUrl = <?= json_encode($checkUrl) ?>;

    function markStep(id, ok) {
        var el = document.getElementById(id);
        var dot = el.querySelector('.step-dot');
        var text = el.querySelector('span');
        if (ok) {
            dot.innerHTML = '<svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>';
            dot.className = 'w-5 h-5 rounded-full border border-emerald-500/50 flex items-center justify-center';
            text.className = 'text-emerald-300';
        } else {
            dot.innerHTML = '<svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>';
            dot.className = 'w-5 h-5 rounded-full border border-red-500/50 flex items-center justify-center';
            text.className = 'text-red-300';
        }
    }

    setTimeout(function() { markStep('step-tables', true); }, 400);

    $.ajax({ url: checkUrl, method: 'GET', dataType: 'json' })
        .done(function(res) {
            setTimeout(function() { markStep('step-data', res.status === 'ok'); }, 800);
            setTimeout(function() {
                if (res.status === 'ok' && res.spa_url) {
                    markStep('step-ready', true);
                    document.getElementById('spinner-wrap').classList.add('hidden');
                    document.getElementById('check-icon').classList.remove('hidden');
                    setTimeout(function() { document.getElementById('check-icon').classList.add('show'); }, 50);
                    document.getElementById('status-title').textContent = 'System Ready';
                    document.getElementById('status-title').classList.add('gold-glow');
                    document.getElementById('status-title').style.color = '#daa520';
                    document.getElementById('status-sub').textContent = 'กำลังเปิดหน้าหลักสูตรในแท็บใหม่...';
                    setTimeout(function() { window.open(res.spa_url, '_blank'); }, 300);
                } else {
                    markStep('step-ready', false);
                    document.getElementById('error').classList.remove('hidden');
                    document.getElementById('error-message').textContent = res.message || 'เกิดข้อผิดพลาด';
                    document.getElementById('status-title').textContent = 'ตรวจสอบไม่สำเร็จ';
                    document.getElementById('status-sub').textContent = '';
                }
            }, 1200);
        })
        .fail(function(xhr) {
            markStep('step-tables', false);
            markStep('step-data', false);
            markStep('step-ready', false);
            var msg = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
            try { var j = JSON.parse(xhr.responseText || '{}'); if (j.message) msg = j.message; } catch(e) {}
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('error-message').textContent = msg;
            document.getElementById('status-title').textContent = 'เกิดข้อผิดพลาด';
            document.getElementById('status-sub').textContent = '';
        });
})();
    </script>
</body>
</html>

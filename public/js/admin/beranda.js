// Interaksi Beranda Admin: hitung naik, jam, maskot, checklist, konfeti
(function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 1. Angka kartu naik pelan-pelan (count-up)
    document.querySelectorAll('.card_value[data-target]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-target'), 10) || 0;
        if (reduce || target === 0) {
            el.textContent = target;
            return;
        }
        var start = null;
        var dur = 900;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(step);
        }
        el.textContent = 0;
        requestAnimationFrame(step);
    });

    // 2. Jam berjalan
    var clock = document.getElementById('live_clock');
    function pad(n) { return n < 10 ? '0' + n : n; }
    function tick() {
        var d = new Date();
        if (clock) clock.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }
    tick();
    setInterval(tick, 1000);

    // 3. Maskot: klik untuk pesan semangat
    var messages = [
        'Semangat, Admin! 💪',
        'Jangan lupa minum air ya 💧',
        'Kopi dulu, baru verifikasi ☕',
        'Kamu keren hari ini! ✨',
        'Satu akun, satu langkah 🐾',
        'Fasilitas kampus aman di tanganmu 🏫'
    ];
    var mascot = document.getElementById('mascot');
    var bubble = document.getElementById('mascot_bubble');
    var bubbleTimer;
    if (mascot && bubble) {
        mascot.addEventListener('click', function () {
            bubble.textContent = messages[Math.floor(Math.random() * messages.length)];
            bubble.classList.remove('bubble_hidden');
            clearTimeout(bubbleTimer);
            bubbleTimer = setTimeout(function () {
                bubble.classList.add('bubble_hidden');
            }, 2500);
        });
    }

    // 4. Checklist tugas hari ini (tersimpan per hari di browser)
    var panel = document.getElementById('todo_panel');
    if (!panel) return;
    var boxes = panel.querySelectorAll('input[type="checkbox"]');
    var fill = document.getElementById('todo_progress');
    var statusEl = document.getElementById('todo_status');
    var storeKey = 'admin_todo_' + panel.getAttribute('data-date');
    var saved = {};
    try { saved = JSON.parse(localStorage.getItem(storeKey)) || {}; } catch (e) { saved = {}; }

    function confetti() {
        if (reduce) return;
        var rect = panel.getBoundingClientRect();
        var items = ['🎉', '✨', '⭐', '🎊', '💛'];
        for (var i = 0; i < 24; i++) {
            var s = document.createElement('span');
            s.className = 'confetti';
            s.textContent = items[i % items.length];
            s.style.left = (rect.left + rect.width / 2) + 'px';
            s.style.top = (rect.top + 40) + 'px';
            s.style.setProperty('--dx', (Math.random() * 360 - 180) + 'px');
            s.style.setProperty('--dy', (Math.random() * 260 - 60) + 'px');
            s.style.setProperty('--rot', (Math.random() * 540 - 270) + 'deg');
            document.body.appendChild(s);
            (function (el) { setTimeout(function () { el.remove(); }, 1500); })(s);
        }
    }

    function update(celebrate) {
        var done = 0;
        boxes.forEach(function (b) { if (b.checked) done++; });
        var pct = boxes.length ? Math.round(done / boxes.length * 100) : 0;
        fill.style.width = pct + '%';
        if (done === boxes.length) {
            statusEl.textContent = 'Semua beres! Kamu hebat 🎉';
            if (celebrate) confetti();
        } else {
            statusEl.textContent = done + ' dari ' + boxes.length + ' tugas selesai';
        }
    }

    boxes.forEach(function (b) {
        b.checked = !!saved[b.getAttribute('data-key')];
        b.addEventListener('change', function () {
            saved[b.getAttribute('data-key')] = b.checked;
            try { localStorage.setItem(storeKey, JSON.stringify(saved)); } catch (e) {}
            update(true);
        });
    });
    update(false);
})();
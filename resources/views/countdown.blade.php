<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segera Hadir — PPDB SMPS2 Al-Muhajirin</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64x64.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cd-bg: #050a18;
            --cd-surface: rgba(255,255,255,0.04);
            --cd-border: rgba(255,255,255,0.08);
            --cd-text: #f1f5f9;
            --cd-text-dim: #64748b;
            --cd-accent: #38bdf8;
            --cd-accent-glow: rgba(56, 189, 248, 0.15);
            --cd-green: #34d399;
        }

        html { height: 100%; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--cd-bg);
            color: var(--cd-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient glow orbs */
        .cd-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }
        .cd-orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(56,189,248,0.12) 0%, transparent 70%);
            top: -10%; left: -5%;
            animation: cd-float 12s ease-in-out infinite;
        }
        .cd-orb-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(52,211,153,0.08) 0%, transparent 70%);
            bottom: -8%; right: -5%;
            animation: cd-float 15s ease-in-out infinite reverse;
        }
        .cd-orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(139,92,246,0.06) 0%, transparent 70%);
            top: 40%; right: 20%;
            animation: cd-float 18s ease-in-out infinite;
        }

        @keyframes cd-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, -30px) scale(1.05); }
            66% { transform: translate(-15px, 20px) scale(0.95); }
        }

        /* Grid pattern overlay */
        .cd-grid-bg {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
            pointer-events: none;
        }

        .cd-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 680px;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        /* Logo */
        .cd-logo-wrap {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
        }
        .cd-logo {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: var(--cd-surface);
            border: 1px solid var(--cd-border);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        .cd-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        /* Badge */
        .cd-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--cd-accent-glow);
            border: 1px solid rgba(56,189,248,0.2);
            color: var(--cd-accent);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 9999px;
            margin-bottom: 1.5rem;
        }
        .cd-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--cd-accent);
            animation: cd-pulse 2s ease-in-out infinite;
        }

        @keyframes cd-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        /* Title */
        .cd-title {
            font-size: clamp(28px, 5vw, 42px);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 30%, var(--cd-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cd-subtitle {
            font-size: 15px;
            color: var(--cd-text-dim);
            line-height: 1.7;
            margin-bottom: 2.5rem;
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Countdown timer */
        .cd-timer {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .cd-timer-block {
            background: var(--cd-surface);
            border: 1px solid var(--cd-border);
            border-radius: 16px;
            padding: 20px 16px 14px;
            min-width: 90px;
            backdrop-filter: blur(10px);
            transition: border-color 0.3s;
        }
        .cd-timer-block:hover {
            border-color: rgba(56,189,248,0.25);
        }

        .cd-timer-value {
            font-size: 40px;
            font-weight: 800;
            line-height: 1;
            color: #ffffff;
            font-variant-numeric: tabular-nums;
            transition: transform 0.15s;
        }

        .cd-timer-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--cd-text-dim);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 8px;
        }

        /* Open date info */
        .cd-open-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(52,211,153,0.08);
            border: 1px solid rgba(52,211,153,0.15);
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 2rem;
        }
        .cd-open-date-icon {
            font-size: 20px;
        }
        .cd-open-date-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--cd-green);
        }

        /* Info cards */
        .cd-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 2rem;
            text-align: left;
        }
        @media (min-width: 500px) {
            .cd-info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .cd-info-card {
            background: var(--cd-surface);
            border: 1px solid var(--cd-border);
            border-radius: 14px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .cd-info-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }
        .cd-info-card h4 {
            font-size: 13px;
            font-weight: 700;
            color: var(--cd-text);
            margin-bottom: 2px;
        }
        .cd-info-card p {
            font-size: 12px;
            color: var(--cd-text-dim);
            line-height: 1.5;
        }

        /* WhatsApp CTA */
        .cd-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #22c55e;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(34,197,94,0.3);
        }
        .cd-wa-btn:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34,197,94,0.4);
            color: #ffffff;
        }
        .cd-wa-btn svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        /* Footer */
        .cd-footer {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 1.5rem;
            font-size: 12px;
            color: var(--cd-text-dim);
        }

        /* Open state — shown when countdown reaches zero */
        .cd-open-msg {
            display: none;
            animation: cd-reveal 0.6s cubic-bezier(0.16,1,0.3,1) forwards;
        }
        .cd-open-msg.active {
            display: block;
        }

        @keyframes cd-reveal {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cd-open-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--cd-accent), #818cf8);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 14px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(56,189,248,0.3);
            margin-top: 1.5rem;
        }
        .cd-open-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(56,189,248,0.4);
            color: #ffffff;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .cd-timer { gap: 8px; }
            .cd-timer-block { min-width: 72px; padding: 14px 10px 10px; }
            .cd-timer-value { font-size: 30px; }
        }
    </style>
</head>
<body>

    <div class="cd-orb cd-orb-1"></div>
    <div class="cd-orb cd-orb-2"></div>
    <div class="cd-orb cd-orb-3"></div>
    <div class="cd-grid-bg"></div>

    <div class="cd-wrapper">
        {{-- Logo --}}
        <div class="cd-logo-wrap">
            <div class="cd-logo">
                <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin">
            </div>
        </div>

        {{-- Badge --}}
        <div class="cd-badge">
            <span class="cd-badge-dot"></span>
            Maintenance Terjadwal
        </div>

        {{-- Title --}}
        <h1 class="cd-title">Coming Soon</h1>
        <p class="cd-subtitle">
            Sesuatu yang baru sedang kami siapkan untuk calon siswa-siswi terbaik. Pendaftaran akan segera dibuka — pastikan kamu tidak ketinggalan!
        </p>

        {{-- Open Date --}}
        <div class="cd-open-date">
            <span class="cd-open-date-icon">📅</span>
            <span class="cd-open-date-text">{{ $openAtFormatted }}</span>
        </div>

        {{-- Countdown Timer --}}
        <div class="cd-timer" id="countdownTimer">
            <div class="cd-timer-block">
                <div class="cd-timer-value" id="cdDays">--</div>
                <div class="cd-timer-label">Hari</div>
            </div>
            <div class="cd-timer-block">
                <div class="cd-timer-value" id="cdHours">--</div>
                <div class="cd-timer-label">Jam</div>
            </div>
            <div class="cd-timer-block">
                <div class="cd-timer-value" id="cdMinutes">--</div>
                <div class="cd-timer-label">Menit</div>
            </div>
            <div class="cd-timer-block">
                <div class="cd-timer-value" id="cdSeconds">--</div>
                <div class="cd-timer-label">Detik</div>
            </div>
        </div>

        {{-- Info Cards --}}
        <div class="cd-info-grid">
            <div class="cd-info-card">
                <div class="cd-info-icon" style="background:rgba(56,189,248,0.1);color:#38bdf8;">📝</div>
                <div>
                    <h4>Pendaftaran Ditangguhkan</h4>
                    <p>Registrasi akun baru & login sementara tidak tersedia hingga jadwal pembukaan.</p>
                </div>
            </div>
            <div class="cd-info-card">
                <div class="cd-info-icon" style="background:rgba(251,191,36,0.1);color:#fbbf24;">🔒</div>
                <div>
                    <h4>Data Anda Aman</h4>
                    <p>Seluruh data pendaftaran yang sudah dikirim tetap tersimpan dengan aman.</p>
                </div>
            </div>
        </div>

        {{-- WhatsApp CTA --}}
        <a class="cd-wa-btn" href="https://wa.me/6287821055283" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Hubungi Panitia PPDB
        </a>

        {{-- Message shown when countdown reaches zero --}}
        <div class="cd-open-msg" id="openMessage">
            <h2 style="font-size:28px;font-weight:800;color:var(--cd-green);margin-bottom:0.5rem;">🎉 Portal Sudah Dibuka!</h2>
            <p style="font-size:14px;color:var(--cd-text-dim);margin-bottom:1rem;">Silakan klik tombol di bawah untuk mengakses portal PPDB.</p>
            <a class="cd-open-link" href="{{ url('/') }}">
                Masuk ke Portal PPDB →
            </a>
        </div>
    </div>

    <div class="cd-footer">
        &copy; {{ date('Y') }} SMPS2 Al-Muhajirin — Sistem Penerimaan Murid Baru
    </div>

    <script>
        (function() {
            const targetDate = new Date("{{ $openAt }}").getTime();
            const $days = document.getElementById('cdDays');
            const $hours = document.getElementById('cdHours');
            const $minutes = document.getElementById('cdMinutes');
            const $seconds = document.getElementById('cdSeconds');
            const $timer = document.getElementById('countdownTimer');
            const $openMsg = document.getElementById('openMessage');

            function pad(n) { return String(n).padStart(2, '0'); }

            function tick() {
                const now = Date.now();
                const diff = targetDate - now;

                if (diff <= 0) {
                    $timer.style.display = 'none';
                    $openMsg.classList.add('active');
                    // Auto-redirect after 3 seconds
                    setTimeout(function() { window.location.href = "{{ url('/') }}"; }, 3000);
                    return;
                }

                const days = Math.floor(diff / 86400000);
                const hours = Math.floor((diff % 86400000) / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);

                $days.textContent = pad(days);
                $hours.textContent = pad(hours);
                $minutes.textContent = pad(minutes);
                $seconds.textContent = pad(seconds);

                requestAnimationFrame(function() {
                    setTimeout(tick, 1000);
                });
            }

            tick();
        })();
    </script>
</body>
</html>


@push('styles')
<style>
    /* Welcome Hero */
    .welcome-hero {
        position: relative;
        padding: 3rem;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.8));
        border-radius: 30px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        backdrop-filter: blur(20px);
    }
    .hero-bg-accent {
        position: absolute;
        top: -100px;
        right: -100px;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
        z-index: 0;
        pointer-events: none;
    }

    .hero-welcome-text h1 { color: #fff; }
    .h-stat-label { font-size: 0.75rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .h-stat-val { font-size: 1.25rem; font-weight: 700; color: #fff; }

    /* Hero Wallet Card */
    .hero-wallet-card {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.8), rgba(30, 64, 175, 0.9));
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border-radius: 28px;
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transform: translateY(0);
        transition: all 0.4s ease;
    }
    .hero-wallet-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px -12px rgba(59, 130, 246, 0.6);
    }
    .hw-glow {
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        filter: blur(40px);
        border-radius: 50%;
    }
    .hw-amount { font-size: 2.25rem; font-weight: 800; color: #fff; letter-spacing: -1px; }
    .btn-glass { background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; }
    .btn-glass:hover { background: rgba(255, 255, 255, 0.2); color: #fff; }

    /* Section Icons */
    .section-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.2rem;
    }
    .section-icon.identity { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .section-icon.verification { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .section-icon.lifestyle { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    /* Service Cards Tweak */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 15px;
    }
    .qa-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 20px 15px;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .qa-card:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: var(--clr-primary);
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.3);
    }
    .qa-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 1.4rem;
    }
    .qa-label { font-size: 0.85rem; font-weight: 600; color: rgba(255,255,255,0.8); }

    /* Stats & Panels */
    .mini-stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .mini-stat-item:hover {
        background: rgba(255, 255, 255, 0.04);
        transform: translateX(4px);
        border-color: rgba(255,255,255,0.1);
    }
    .mini-stat-item i { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03); border-radius: 8px; }
    
    .x-small { font-size: 0.75rem; }
    .gap-4 { gap: 1.5rem; }
    .gap-3 { gap: 1rem; }
    .gap-2 { gap: 0.5rem; }

    /* Hub Icons */
    .hub-icon { background: rgba(255, 255, 255, 0.05); color: #fff; }
    .qa-badge { position: absolute; top: 8px; right: 8px; font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; color: #fff; font-weight: 800; }

    @media (max-width: 768px) {
        .welcome-hero { padding: 2rem; }
        .hero-stats-row { flex-wrap: wrap; }
    }
</style>
@endpush

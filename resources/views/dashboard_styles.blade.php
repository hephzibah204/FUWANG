@push('styles')
<style>
    /* Global Dashboard Tokens */
    :root {
        --dash-card-bg: rgba(15, 23, 42, 0.7);
        --dash-card-border: rgba(255, 255, 255, 0.08);
        --dash-glow-primary: rgba(59, 130, 246, 0.15);
        --dash-glow-purple: rgba(139, 92, 246, 0.15);
    }

    /* Welcome Hero Container */
    .welcome-hero {
        position: relative;
        padding: 2.25rem 2.5rem;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
        border-radius: 24px;
        border: 1px solid var(--dash-card-border);
        overflow: hidden;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
    }

    .hero-bg-accent {
        position: absolute;
        top: -120px;
        right: -80px;
        width: 380px;
        height: 380px;
        background: radial-gradient(circle, var(--dash-glow-primary) 0%, rgba(139, 92, 246, 0.08) 40%, transparent 70%);
        z-index: 0;
        pointer-events: none;
        filter: blur(20px);
    }

    .hero-welcome-text h1 {
        color: #f8fafc;
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 2.1rem;
    }

    .hero-stats-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .h-stat {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 14px;
        padding: 10px 16px;
        min-width: 130px;
    }

    .h-stat-label {
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 2px;
        font-weight: 600;
    }

    .h-stat-val {
        font-size: 1.15rem;
        font-weight: 700;
        color: #f8fafc;
    }

    /* Modern Glass Wallet Card */
    .hero-wallet-card {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #2563eb 100%);
        border-radius: 22px;
        padding: 1.75rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 35px -10px rgba(37, 99, 235, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }

    .hero-wallet-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 25px 45px -10px rgba(37, 99, 235, 0.55);
    }

    .hw-glow {
        position: absolute;
        bottom: -40px;
        right: -40px;
        width: 140px;
        height: 140px;
        background: rgba(255, 255, 255, 0.15);
        filter: blur(30px);
        border-radius: 50%;
        pointer-events: none;
    }

    .hw-content {
        position: relative;
        z-index: 1;
    }

    .hw-amount {
        font-size: 2.2rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.8px;
        line-height: 1.2;
    }

    .balance-toggle-btn {
        background: rgba(255, 255, 255, 0.18);
        border: none;
        color: #ffffff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-left: 8px;
        font-size: 0.85rem;
    }

    .balance-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Virtual Account Showcase Card */
    .va-highlight-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85));
        border: 1px solid var(--dash-card-border);
        border-radius: 20px;
        padding: 1.5rem;
        backdrop-filter: blur(16px);
        position: relative;
        overflow: hidden;
    }

    .va-bank-badge {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 8px;
        background: rgba(59, 130, 246, 0.15);
        color: #93c5fd;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .va-num-display {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        color: #f8fafc;
        font-family: 'Outfit', monospace;
    }

    .va-copy-btn {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.35);
        border-radius: 10px;
        padding: 6px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .va-copy-btn:hover {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
    }

    /* Search & Category Filter Hub */
    .service-filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .service-search-box {
        position: relative;
        min-width: 260px;
        max-width: 380px;
        flex: 1;
    }

    .service-search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .service-search-box input {
        width: 100%;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 10px 14px 10px 38px;
        color: #f8fafc;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .service-search-box input:focus {
        background: rgba(255, 255, 255, 0.07);
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        outline: none;
    }

    .category-pills {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .cat-pill {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        color: #94a3b8;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        user-select: none;
    }

    .cat-pill:hover {
        background: rgba(255, 255, 255, 0.07);
        color: #f8fafc;
    }

    .cat-pill.active {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Quick Grid & Service Card Upgrades */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 14px;
    }

    .qa-card {
        background: rgba(255, 255, 255, 0.025);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 18px;
        padding: 18px 12px;
        text-align: center;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .qa-card:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(59, 130, 246, 0.5);
        transform: translateY(-4px);
        box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.25);
    }

    .qa-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.3rem;
        background: rgba(255, 255, 255, 0.04);
        color: #60a5fa;
        transition: all 0.2s ease;
    }

    .qa-card:hover .qa-icon {
        background: rgba(59, 130, 246, 0.2);
        transform: scale(1.08);
    }

    .qa-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #e2e8f0;
        line-height: 1.25;
    }

    .qa-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 0.6rem;
        padding: 2px 6px;
        border-radius: 6px;
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    /* Panels & Cards */
    .panel-card {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid var(--dash-card-border);
        border-radius: 20px;
        padding: 1.5rem;
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .panel-hdr {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    /* Recent Transactions Styling */
    .txn-row-item {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.04);
        border-radius: 14px;
        padding: 12px 16px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .txn-row-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        transform: translateX(4px);
    }

    .txn-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .txn-icon.success {
        background: rgba(16, 185, 129, 0.12);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .txn-icon.failed {
        background: rgba(239, 68, 68, 0.12);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .txn-icon.pending {
        background: rgba(245, 158, 11, 0.12);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    /* Mini Stat Items */
    .mini-stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .mini-stat-item:hover {
        background: rgba(255, 255, 255, 0.04);
        transform: translateX(3px);
    }

    .mini-stat-item i {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 10px;
        font-size: 1rem;
    }

    /* KYC Badge Pill */
    .kyc-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .kyc-verified {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .kyc-unverified {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    /* Responsive Queries */
    @media (max-width: 991px) {
        .welcome-hero {
            padding: 1.75rem;
        }
        .hero-welcome-text h1 {
            font-size: 1.75rem;
        }
    }

    @media (max-width: 767px) {
        .service-filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .service-search-box {
            max-width: 100%;
        }
        .quick-grid {
            grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
            gap: 10px;
        }
        .qa-card {
            padding: 14px 8px;
        }
        .qa-icon {
            width: 40px;
            height: 40px;
            font-size: 1.15rem;
        }
        .qa-label {
            font-size: 0.75rem;
        }
    }
</style>
@endpush

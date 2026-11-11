@push('styles')
<style>
    /* Global Dashboard Tokens */
    :root {
        --dash-card-bg: #0f172a;
        --dash-card-border: rgba(255, 255, 255, 0.08);
    }

    [data-theme="light"],
    body.theme-light {
        --dash-card-bg: #ffffff;
        --dash-card-border: #e2e8f0;
    }

    /* Welcome Hero Container */
    .welcome-hero {
        position: relative;
        padding: 1.75rem 2rem;
        background: var(--dash-card-bg);
        border-radius: 16px;
        border: 1px solid var(--dash-card-border);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }


    .welcome-heading {
        color: var(--clr-text-main, #f8fafc);
        font-weight: 700;
        letter-spacing: -0.3px;
        font-size: 1.55rem;
        line-height: 1.25;
        margin-bottom: 0.25rem;
    }

    .welcome-subtext {
        color: var(--clr-text-muted, #94a3b8);
        font-size: 0.875rem;
        margin-bottom: 1.25rem;
        line-height: 1.4;
    }

    .hero-stats-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .h-stat {
        background: rgba(255, 255, 255, 0.025);
        border: 1px solid var(--dash-card-border);
        border-radius: 10px;
        padding: 8px 14px;
        min-width: 120px;
    }

    .h-stat-label {
        font-size: 0.7rem;
        color: var(--clr-text-muted, #94a3b8);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
        font-weight: 500;
    }

    .h-stat-val {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--clr-text-main, #f8fafc);
        font-variant-numeric: tabular-nums;
    }

    /* Professional Financial Wallet Card */
    .hero-wallet-card {
        background: linear-gradient(135deg, #0f274a 0%, #1e40af 100%);
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }


    .hw-content {
        position: relative;
        z-index: 1;
    }

    .hw-amount {
        font-size: 2rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.5px;
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
    }

    .balance-toggle-btn {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #ffffff;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s ease;
        margin-left: 8px;
        font-size: 0.8rem;
    }

    .balance-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        font-weight: 600;
        transition: background 0.15s ease;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    /* Virtual Account Showcase Card */
    .va-highlight-card {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-card-border);
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .va-bank-badge {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    .va-num-display {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        color: var(--clr-text-main, #f8fafc);
        font-variant-numeric: tabular-nums;
        font-family: inherit;
    }

    .va-copy-btn {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.25);
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        transition: all 0.15s ease;
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
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .service-search-box {
        position: relative;
        min-width: 240px;
        max-width: 360px;
        flex: 1;
    }

    .service-search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--clr-text-muted, #94a3b8);
        font-size: 0.85rem;
    }

    .service-search-box input {
        width: 100%;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--dash-card-border);
        border-radius: 10px;
        padding: 8px 12px 8px 34px;
        color: var(--clr-text-main, #f8fafc);
        font-size: 0.85rem;
        transition: border-color 0.15s ease;
    }

    .service-search-box input:focus {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--clr-primary, #3b82f6);
        outline: none;
    }

    .category-pills {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .cat-pill {
        background: transparent;
        border: 1px solid var(--dash-card-border);
        color: var(--clr-text-muted, #94a3b8);
        padding: 6px 13px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        user-select: none;
    }

    .cat-pill:hover {
        background: rgba(255, 255, 255, 0.04);
        color: var(--clr-text-main, #f8fafc);
    }

    .cat-pill.active {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
    }

    /* Quick Grid & Service Cards */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
    }

    .qa-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--dash-card-border);
        border-radius: 12px;
        padding: 14px 10px;
        text-align: center;
        transition: border-color 0.15s ease, background 0.15s ease;
        text-decoration: none !important;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .qa-card:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(59, 130, 246, 0.35);
        transform: none;
        box-shadow: none;
    }

    .qa-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-size: 1.15rem;
        background: rgba(59, 130, 246, 0.08);
        color: #3b82f6;
    }

    .qa-card:hover .qa-icon {
        background: rgba(59, 130, 246, 0.14);
        transform: none;
    }

    .qa-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--clr-text-main, #e2e8f0);
        line-height: 1.25;
    }

    .qa-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: 0.58rem;
        padding: 1px 5px;
        border-radius: 4px;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Panels & Cards */
    .panel-card {
        background: var(--dash-card-bg);
        border: 1px solid var(--dash-card-border);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .panel-hdr {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    /* Recent Transactions Styling */
    .txn-row-item {
        background: rgba(255, 255, 255, 0.015);
        border: 1px solid var(--dash-card-border);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: background 0.15s ease;
        text-decoration: none !important;
    }

    .txn-row-item:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.12);
        transform: none;
    }

    .txn-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .txn-icon.success {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .txn-icon.failed {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .txn-icon.pending {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    /* Mini Stat Items */
    .mini-stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.015);
        border: 1px solid var(--dash-card-border);
        border-radius: 10px;
        transition: background 0.15s ease;
    }

    .mini-stat-item:hover {
        background: rgba(255, 255, 255, 0.035);
        transform: none;
    }

    .mini-stat-item i {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 8px;
        font-size: 0.9rem;
    }

    /* KYC Badge Pill */
    .kyc-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .kyc-verified {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .kyc-unverified {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }

    /* Responsive Queries */
    @media (max-width: 991px) {
        .welcome-hero {
            padding: 1.5rem;
        }
        .welcome-heading {
            font-size: 1.35rem;
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
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
        }
        .qa-card {
            padding: 12px 6px;
        }
        .qa-icon {
            width: 34px;
            height: 34px;
            font-size: 1rem;
        }
        .qa-label {
            font-size: 0.72rem;
        }
    }
</style>
@endpush

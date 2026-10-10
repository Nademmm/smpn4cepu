<style>
    /* 1. Global Font & Panel Base */
    body.fi-body {
        background-color: #f8fafc !important;
        font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        -webkit-font-smoothing: antialiased !important;
    }

    /* 2. Login Page Container & Modern Card ala Skomda */
    .fi-simple-layout {
        min-height: 100vh !important;
        background: radial-gradient(circle at 50% 10%, #f1f5f9 0%, #f8fafc 100%) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem !important;
    }

    .fi-simple-main-ctn {
        width: 100% !important;
        max-width: 440px !important;
        margin: 0 auto !important;
    }

    .fi-simple-main {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1.5rem !important; /* 24px */
        padding: 2.25rem 2rem !important;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03) !important;
    }

    /* Sembunyikan default header sederhana Filament agar tidak dobel/tumpang-tindih */
    .fi-simple-header {
        display: none !important;
    }

    /* 3. Input & Form Fields */
    .fi-fo-field-wrp-label label {
        font-size: 0.72rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #475569 !important;
        margin-bottom: 0.25rem !important;
    }

    .fi-input-wrp {
        border-radius: 0.75rem !important; /* 12px */
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        transition: all 0.15s ease-in-out !important;
        box-shadow: none !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #d97706 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15) !important;
    }

    .fi-input-wrp input {
        font-size: 0.825rem !important;
        color: #0f172a !important;
    }

    /* 4. Action Buttons (Tombol Masuk) */
    .fi-btn {
        border-radius: 0.75rem !important;
        font-weight: 800 !important;
        transition: all 0.15s ease-in-out !important;
    }

    .fi-btn-primary {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.25), 0 2px 4px -2px rgba(217, 119, 6, 0.15) !important;
        min-height: 44px !important;
        font-size: 0.875rem !important;
    }

    .fi-btn-primary:hover {
        filter: brightness(1.05) !important;
        box-shadow: 0 6px 12px -2px rgba(217, 119, 6, 0.3) !important;
    }

    .fi-btn-primary:active {
        transform: scale(0.98) !important;
    }

    /* Checkbox & Remember Me */
    .fi-checkbox-input {
        border-radius: 0.35rem !important;
        border: 1px solid #cbd5e1 !important;
    }

    .fi-checkbox-input:checked {
        background-color: #d97706 !important;
        border-color: #d97706 !important;
    }

    /* 5. Topbar & Header Admin */
    .fi-topbar {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
    }

    /* 6. Sidebar Admin */
    aside.fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }

    .fi-sidebar-item-btn {
        border-radius: 0.75rem !important;
        font-weight: 700 !important;
        transition: all 0.15s ease-in-out !important;
    }

    .fi-sidebar-item-btn:hover {
        background-color: #f1f5f9 !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        font-weight: 800 !important;
        color: #94a3b8 !important;
    }

    /* 7. Kartu Dashboard & Widget */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        border-radius: 1.25rem !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02) !important;
    }
</style>


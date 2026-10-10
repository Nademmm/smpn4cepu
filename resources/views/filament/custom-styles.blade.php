<style>
    /* 1. Body & Background Global Panel */
    body.fi-body {
        background-color: #f8fafc !important;
        font-family: 'Nunito', system-ui, -apple-system, sans-serif !important;
    }

    /* 2. Login Page / Simple Page Container */
    .fi-simple-layout {
        min-height: 100vh !important;
        background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem !important;
    }

    .fi-simple-main-ctn {
        width: 100% !important;
        max-width: 27rem !important; /* ~430px */
        margin: 0 auto !important;
    }

    .fi-simple-main {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1.5rem !important; /* 24px */
        padding: 2.25rem 2rem !important;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04) !important;
    }

    /* Sembunyikan default header sederhana Filament agar tidak dobel/tumpang-tindih */
    .fi-simple-header {
        display: none !important;
    }

    /* 3. Input & Form Fields */
    .fi-input-wrp {
        border-radius: 0.75rem !important; /* 12px */
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
    }

    /* 4. Action Buttons */
    .fi-btn {
        border-radius: 0.75rem !important;
        font-weight: 800 !important;
        transition: all 0.15s ease-in-out !important;
    }

    .fi-btn-primary {
        box-shadow: 0 2px 4px 0 rgba(245, 158, 11, 0.2) !important;
    }

    .fi-btn-primary:active {
        transform: scale(0.98) !important;
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
        font-size: 0.7rem !important;
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

<?php
declare(strict_types=1);

if (!function_exists('marketingActive')) {
    function marketingActive(string $file): string
    {
        return basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === $file ? ' class="active"' : '';
    }
}

$marketingCurrentFile = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$marketingFiles = [
    'marketing_dashboard.php',
    'marketing_plans.php',
    'marketing_subscriptions.php',
    'marketing_campaigns.php',
    'marketing_imports.php',
    'marketing_reports.php',
];
$marketingOpen = in_array($marketingCurrentFile, $marketingFiles, true);
?>
<style>
    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu {
        display: block !important;
        width: auto !important;
        margin: 0.25rem 0.6rem !important;
        padding: 0 !important;
        border-radius: 14px !important;
        background: transparent !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu:hover {
        background: transparent !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-toggle {
        display: grid !important;
        grid-template-columns: 24px minmax(0, 1fr) 20px !important;
        align-items: center !important;
        column-gap: 14px !important;
        width: 100% !important;
        min-height: 48px !important;
        padding: 0.75rem 1rem !important;
        border-radius: 14px !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
        color: var(--text-secondary) !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu.active > .sidebar-submenu-toggle,
    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-toggle:hover {
        background: linear-gradient(135deg, var(--primary-soft), rgba(255, 255, 255, 0.04)) !important;
        color: var(--text-primary) !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-toggle .material-icons {
        width: 24px !important;
        min-width: 24px !important;
        text-align: center !important;
        display: inline-flex !important;
        justify-content: center !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-toggle .link-text {
        display: block !important;
        min-width: 0 !important;
        margin: 0 !important;
        white-space: normal !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu .submenu-chevron {
        margin: 0 !important;
        justify-self: end !important;
        transition: transform .2s ease !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu.is-open .submenu-chevron {
        transform: rotate(180deg) !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-list {
        display: none !important;
        width: 100% !important;
        margin: 8px 0 10px !important;
        padding: 0 0 0 38px !important;
        list-style: disc !important;
        list-style-position: outside !important;
        box-sizing: border-box !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu.is-open > .sidebar-submenu-list {
        display: block !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-list > li {
        display: list-item !important;
        width: auto !important;
        margin: 0 !important;
        padding: 8px 8px !important;
        border-radius: 10px !important;
        background: transparent !important;
        box-shadow: none !important;
        color: var(--text-secondary) !important;
        font-size: 14px !important;
        line-height: 1.25 !important;
        white-space: normal !important;
        cursor: pointer !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-list > li::marker {
        color: #a7adba !important;
        font-size: .8em !important;
    }

    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-list > li:hover,
    .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-list > li.active {
        background: var(--bg-muted) !important;
        color: var(--text-primary) !important;
    }

    .sidebar.collapsed .sidebar-menu > ul > li.sidebar-submenu.marketing-menu > .sidebar-submenu-list,
    .sidebar.collapsed .sidebar-menu > ul > li.sidebar-submenu.marketing-menu .submenu-chevron {
        display: none !important;
    }
</style>
<li class="sidebar-submenu marketing-menu <?= $marketingOpen ? 'is-open active' : '' ?>">
    <div class="sidebar-submenu-toggle" role="button" tabindex="0" aria-expanded="<?= $marketingOpen ? 'true' : 'false' ?>">
        <span class="material-icons" style="color:#0f766e">campaign</span>
        <span class="link-text">Marketing</span>
        <span class="material-icons submenu-chevron">expand_more</span>
    </div>
    <ul class="sidebar-submenu-list">
        <li<?= marketingActive('marketing_dashboard.php') ?> onclick="location.href='../marketing/marketing_dashboard.php'"><span class="link-text">Dashboard</span></li>
        <li<?= marketingActive('marketing_plans.php') ?> onclick="location.href='../marketing/marketing_plans.php'"><span class="link-text">Planes</span></li>
        <li<?= marketingActive('marketing_subscriptions.php') ?> onclick="location.href='../marketing/marketing_subscriptions.php'"><span class="link-text">Solicitudes</span></li>
        <li<?= marketingActive('marketing_campaigns.php') ?> onclick="location.href='../marketing/marketing_campaigns.php'"><span class="link-text">Campañas</span></li>
        <li<?= marketingActive('marketing_imports.php') ?> onclick="location.href='../marketing/marketing_imports.php'"><span class="link-text">Importar Meta Ads</span></li>
        <li<?= marketingActive('marketing_reports.php') ?> onclick="location.href='../marketing/marketing_reports.php'"><span class="link-text">Reportes</span></li>
    </ul>
</li>
<script>
    (() => {
        const initMarketingSubmenus = () => {
            document.querySelectorAll('.marketing-menu > .sidebar-submenu-toggle').forEach((toggle) => {
                if (toggle.dataset.marketingToggleReady === '1') {
                    return;
                }
                toggle.dataset.marketingToggleReady = '1';

                const runToggle = (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    const item = toggle.closest('.marketing-menu');
                    if (!item) {
                        return;
                    }

                    const isOpen = item.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                };

                toggle.addEventListener('click', runToggle);
                toggle.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        runToggle(event);
                    }
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMarketingSubmenus);
        } else {
            initMarketingSubmenus();
        }
    })();
</script>

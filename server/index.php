<?php
    $path = rtrim(str_replace("\\","/",dirname($_SERVER["PHP_SELF"])), "/");
    $isHttps = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https");
    $url = ($isHttps ? "https://" : "http://" ) . $_SERVER["HTTP_HOST"] . $path;
    header("X-Frame-Options: DENY");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    // header("Clear-Site-Data: \"cache\""); // disabled — breaks PWA standalone mode
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <title>IT Support (AZCO)</title>

        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#047AC3">
        <link rel="icon" type="image/png" href="<?=$url ?>/images/support-32.png">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/v4-shims.css">
        <style>
            .login-switch-btn {
                display: inline-block;
                margin-top: 12px;
                padding: 8px 16px;
                background: transparent;
                color: #555;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 13px;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
            }
            .login-switch-btn:hover {
                background: #047AC3;
                color: #fff;
                border-color: #047AC3;
            }
            .login-switch-btn i {
                margin-right: 6px;
            }
            /* Hide Documentation / Donate links in footer */
            .main-layout-footer__extra-links { display: none !important; }
            <?php if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0): ?>
            /* Admin: hide the Welcome bar + language picker entirely */
            .main-layout-header { display: none !important; }
            <?php else: ?>
            /* User: hide just the language picker */
            .main-layout-header__languages { display: none !important; }
            <?php endif; ?>



            /* Urgent ticket toggle */
            .urgent-row {
                display: flex;
                align-items: flex-start;
                gap: 15px;
                margin-bottom: 0;
            }
            .urgent-row > .form-field {
                flex: 1;
                min-width: 0;
            }
            .urgent-toggle-wrap {
                flex: 0 0 auto;
                padding-top: 24px;
            }
            .urgent-toggle-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 18px;
                border: 2px solid #ccc;
                border-radius: 5px;
                background: #fafafa;
                cursor: pointer;
                transition: all 0.2s;
                white-space: nowrap;
                font-size: 13px;
                font-weight: 600;
                color: #888;
                height: 42px;
                box-sizing: border-box;
                user-select: none;
            }
            .urgent-toggle-btn:hover {
                border-color: #e04;
                color: #c33;
                background: #fff5f5;
            }
            .urgent-toggle-btn.urgent-on {
                border-color: #dc3545;
                background: #dc3545;
                color: #fff;
            }
            .urgent-toggle-btn .urgent-icn { font-size: 15px; }
            .urgent-detail-row {
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                transition: max-height 0.35s ease, opacity 0.25s ease, margin 0.35s ease;
                margin: 0;
            }
            .urgent-detail-row.open {
                max-height: 120px;
                opacity: 1;
                margin: 8px 0 4px;
            }
            .urgent-detail-inner {
                display: flex;
                align-items: center;
                gap: 20px;
                background: #fff3cd;
                border: 1px solid #ffe082;
                border-radius: 5px;
                padding: 10px 16px;
                font-size: 13px;
                color: #7a5d00;
                line-height: 1.45;
            }
            .urgent-detail-inner .urgent-msg {
                flex: 1;
            }
            .urgent-detail-inner .urgent-phone-group {
                flex: 0 0 auto;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .urgent-detail-inner .urgent-phone-group label {
                font-weight: 600;
                white-space: nowrap;
                font-size: 13px;
                color: #7a5d00;
            }
            .urgent-detail-inner .urgent-phone-group input {
                padding: 6px 10px;
                border: 1px solid #d4b446;
                border-radius: 4px;
                font-size: 13px;
                width: 155px;
                background: #fff;
                color: #333;
            }
            .urgent-detail-inner .urgent-phone-group input:focus {
                outline: none;
                border-color: #dc3545;
                box-shadow: 0 0 0 2px rgba(220,53,69,0.15);
            }
            @media (max-width: 600px) {
                .urgent-row { flex-direction: column; gap: 0; }
                .urgent-toggle-wrap { padding-top: 0; margin-bottom: 10px; }
                .urgent-detail-inner { flex-direction: column; align-items: flex-start; gap: 10px; }
            }


            /* BCC notification email field on settings page */
            .bcc-email-setting {
                margin-top: 15px;
                padding: 15px 18px;
                background: #f8f9fa;
                border: 1px solid #e0e0e0;
                border-radius: 6px;
            }
            .bcc-email-setting label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #333;
                margin-bottom: 6px;
            }
            .bcc-email-setting .bcc-desc {
                font-size: 12px;
                color: #888;
                margin-bottom: 8px;
                line-height: 1.4;
            }
            .bcc-email-setting input {
                width: 100%;
                max-width: 340px;
                padding: 8px 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 13px;
                color: #333;
                box-sizing: border-box;
            }
            .bcc-email-setting input:focus {
                outline: none;
                border-color: #047AC3;
                box-shadow: 0 0 0 2px rgba(4,122,195,0.15);
            }
            .bcc-email-saved {
                display: inline-block;
                margin-left: 10px;
                font-size: 12px;
                color: #28a745;
                opacity: 0;
                transition: opacity 0.3s;
            }
            .bcc-email-saved.show { opacity: 1; }


            /* Maintenance mode BCC override */
            .maint-override-box {
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.3s ease, padding 0.3s ease;
                margin: 0;
                padding: 0 18px;
                background: #fff3cd;
                border: 1px solid transparent;
                border-radius: 6px;
            }
            .maint-override-box.open {
                max-height: 150px;
                opacity: 1;
                margin-top: 12px;
                padding: 12px 18px;
                border-color: #ffe082;
            }
            .maint-override-box label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #7a5d00;
                margin-bottom: 4px;
            }
            .maint-override-box .maint-desc {
                font-size: 12px;
                color: #9a7d20;
                margin-bottom: 8px;
                line-height: 1.4;
            }
            .maint-override-box input {
                padding: 7px 12px;
                border: 1px solid #d4b446;
                border-radius: 4px;
                font-size: 13px;
                width: 100%;
                max-width: 320px;
                background: #fff;
                color: #333;
                box-sizing: border-box;
            }
            .maint-override-box input:focus {
                outline: none;
                border-color: #dc3545;
                box-shadow: 0 0 0 2px rgba(220,53,69,0.15);
            }
            .maint-override-saved {
                display: inline-block;
                margin-left: 8px;
                font-size: 12px;
                color: #28a745;
                opacity: 0;
                transition: opacity 0.3s;
            }
            .maint-override-saved.show { opacity: 1; }

            /* Dashboard dual-column layout */
            .dashboard-dual-active {
                display: flex !important;
                flex-direction: row !important;
                gap: 20px;
            }
            .dashboard-dual-active > .dashboard-dual__tickets {
                flex: 1;
                min-width: 0;
                order: -1;
            }
            .dashboard-dual-active > .admin-panel-activity {
                flex: 1;
                min-width: 0;
            }
            .dashboard-dual__tickets h4 {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 12px;
                color: #333;
            }
            .dashboard-tickets-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
                table-layout: fixed;
            }
            .dashboard-tickets-table th {
                text-align: left;
                padding: 6px 6px;
                border-bottom: 2px solid #ddd;
                font-weight: 600;
                color: #555;
                white-space: nowrap;
                overflow: hidden;
            }
            .dashboard-tickets-table td {
                padding: 6px 6px;
                border-bottom: 1px solid #eee;
                vertical-align: middle;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .dashboard-tickets-table tr.dt-row {
                cursor: pointer;
                transition: background 0.15s;
            }
            .dashboard-tickets-table tr.dt-row:hover {
                background: #f0f7ff;
            }
            .dashboard-tickets-table tr.dt-row--closed td {
                opacity: 0.55;
            }
            .dashboard-tickets-table .dt-number {
                color: #047AC3;
                font-weight: 600;
            }
            .dashboard-tickets-table .dt-title {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .dashboard-tickets-table .dt-unassigned {
                color: #999;
                font-style: italic;
            }
            .dt-load-more {
                display: block;
                margin: 12px auto;
                padding: 6px 20px;
                background: #047AC3;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
            }
            .dt-load-more:hover { background: #035f96; }
            .dt-load-more:disabled { opacity: 0.5; cursor: default; }
            .dt-controls { margin-bottom: 10px; display: flex; align-items: center; gap: 12px; }
            .dt-controls label { font-size: 13px; color: #555; cursor: pointer; user-select: none; }
            .dt-controls input[type=checkbox] { margin-right: 4px; }
            .dt-empty { text-align: center; color: #999; padding: 20px; font-size: 14px; }
            .dt-loading { text-align: center; padding: 20px; color: #888; }
            @media (max-width: 900px) {
                .dashboard-dual-active {
                    flex-direction: column !important;
                }
            }
        </style>
    </head>
    <body>
        <div id="app"></div>

        <script>
            opensupports_version = '4.11.0';
            root = "<?=$url ?>";
            apiRoot = '<?=$url ?>/api';
            globalIndexPath = "<?=$path ?>";
            showLogs = false;
        </script>
        <?php if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false)): ?>
          <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=String.prototype.startsWith,Array.from,Array.prototype.fill,Array.prototype.keys,Array.prototype.find,Array.prototype.findIndex,Array.prototype.includes,String.prototype.repeat,Number.isInteger,Promise&flags=gated"></script>
        <?php endif; ?>
        <script src="<?=$url ?>/bundle.js?v=4"></script>
        <script>
            // App version - bump this when deploying changes
            var APP_VERSION = '7';

            // Force reload when PWA is restored from bfcache (app resume)
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    window.location.reload();
                }
            });
            // Also catch visibility change (Edge PWA sometimes uses this instead)
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible' && !sessionStorage.getItem('_alive')) {
                    sessionStorage.setItem('_alive', '1');
                    window.location.reload();
                }
            });
            // Mark page as alive on load, clear on hide
            sessionStorage.setItem('_alive', '1');
            window.addEventListener('pagehide', function() {
                sessionStorage.removeItem('_alive');
            });

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' });
            }

            // Version check: fetch /version.txt (no cache) and hard-reload if stale
            (function() {
                if (sessionStorage.getItem('_vcheck') === APP_VERSION) return;
                fetch('/version.txt', { cache: 'no-store' })
                    .then(function(r) { return r.text(); })
                    .then(function(v) {
                        if (v.trim() !== APP_VERSION) {
                            // Page is stale — nuke SW + caches and hard-reload
                            if ('caches' in window) {
                                caches.keys().then(function(names) {
                                    names.forEach(function(n) { caches.delete(n); });
                                }).then(function() {
                                    window.location.reload(true);
                                });
                            } else {
                                window.location.reload(true);
                            }
                        } else {
                            sessionStorage.setItem('_vcheck', APP_VERSION);
                        }
                    })
                    .catch(function() {});
            })();

            // Rewrite "Powered by OpenSupports" link to AZCO fork
            (function() {
                var obs = new MutationObserver(function() {
                    var link = document.querySelector('.main-layout-footer__os-link');
                    if (link && link.href !== 'https://github.com/AZCO-Corp/AZCO-IT-Support') {
                        link.href = 'https://github.com/AZCO-Corp/AZCO-IT-Support';
                    }
                });
                obs.observe(document.getElementById('app'), { childList: true, subtree: true });
            })();

            // Inject login switch buttons for PWA navigation
            (function() {
                var isAdmin = window.location.pathname.indexOf('/admin') === 0;

                var observer = new MutationObserver(function() {
                    if (!isAdmin) {
                        // User login page: add "Admin Login" button
                        var userContainer = document.querySelector('.main-home-page__link-buttons-container');
                        if (userContainer && !document.getElementById('login-switch-btn')) {
                            var btn = document.createElement('a');
                            btn.id = 'login-switch-btn';
                            btn.href = '/admin';
                            btn.className = 'login-switch-btn';
                            var icon = document.createElement('i');
                            icon.className = 'fas fa-shield-alt';
                            btn.appendChild(icon);
                            btn.appendChild(document.createTextNode(' Admin Login'));
                            userContainer.appendChild(btn);
                        }
                    } else {
                        // Admin login page: add "User Login" button
                        var forgotBtn = document.querySelector('.admin-login-page .login-widget__forgot-password');
                        if (forgotBtn && !document.getElementById('login-switch-btn')) {
                            var btn = document.createElement('a');
                            btn.id = 'login-switch-btn';
                            btn.href = '/';
                            btn.className = 'login-switch-btn';
                            var icon = document.createElement('i');
                            icon.className = 'fas fa-user';
                            btn.appendChild(icon);
                            btn.appendChild(document.createTextNode(' User Login'));
                            forgotBtn.parentNode.insertBefore(btn, forgotBtn.nextSibling);
                        }
                    }
                });

                observer.observe(document.getElementById('app'), {
                    childList: true,
                    subtree: true
                });
            })();

            // === "Assigned To" column injection (admin ticket lists) ===
            (function() {
                if (window.location.pathname.indexOf('/admin') !== 0) return;

                // Global map: ticketNumber → owner name
                window.__ticketOwnerMap = {};

                // 1) XHR interceptor: capture /staff/get-tickets and /staff/get-new-tickets responses
                var origOpen = XMLHttpRequest.prototype.open;
                var origSend = XMLHttpRequest.prototype.send;

                XMLHttpRequest.prototype.open = function(method, url) {
                    this._url = url;
                    return origOpen.apply(this, arguments);
                };

                XMLHttpRequest.prototype.send = function() {
                    var self = this;
                    if (self._url && (self._url.indexOf('/staff/get-tickets') !== -1 || self._url.indexOf('/staff/get-new-tickets') !== -1 || self._url.indexOf('/staff/get-all-tickets') !== -1)) {
                        self.addEventListener('load', function() {
                            try {
                                var resp = JSON.parse(self.responseText);
                                if (resp.status === 'success' && resp.data && resp.data.tickets) {
                                    resp.data.tickets.forEach(function(t) {
                                        var num = t.ticketNumber || t.ticket_number;
                                        if (num) {
                                            window.__ticketOwnerMap[String(num)] = (t.owner && t.owner.name) ? t.owner.name : null;
                                        }
                                    });
                                }
                            } catch(e) {}
                        });
                    }
                    return origSend.apply(this, arguments);
                };

                // 2) MutationObserver: inject "Assigned To" header + cells
                var tableObserver = new MutationObserver(function() {
                    // Find ticket list tables (they use .ticket-list__table or similar)
                    var headers = document.querySelectorAll('.ticket-list__header');
                    headers.forEach(function(header) {
                        if (header.getAttribute('data-assigned-injected')) return;

                        var cols = header.querySelectorAll('[class*="col-md-"]');
                        if (cols.length < 5) return; // Not the full table header

                        // Shrink Title column: col-md-4 → col-md-3
                        var titleCol = cols[1];
                        if (titleCol && titleCol.className.indexOf('col-md-4') !== -1) {
                            titleCol.className = titleCol.className.replace('col-md-4', 'col-md-3');
                        }

                        // Insert "Assigned To" header before the Date column (last col)
                        var datCol = cols[cols.length - 1];
                        var newHeader = document.createElement('div');
                        newHeader.className = 'ticket-list__header-column col-md-2';
                        newHeader.textContent = 'Assigned To';
                        datCol.parentNode.insertBefore(newHeader, datCol);

                        header.setAttribute('data-assigned-injected', '1');
                    });

                    // Process ticket rows
                    var rows = document.querySelectorAll('.ticket-list__row');
                    rows.forEach(function(row) {
                        if (row.getAttribute('data-assigned-injected')) return;

                        var cols = row.querySelectorAll('[class*="col-md-"]');
                        if (cols.length < 5) return;

                        // Get ticket number from first column
                        var numCol = cols[0];
                        var ticketNum = numCol ? numCol.textContent.replace(/[^0-9]/g, '') : '';

                        // Shrink Title column: col-md-4 → col-md-3
                        var titleCol = cols[1];
                        if (titleCol && titleCol.className.indexOf('col-md-4') !== -1) {
                            titleCol.className = titleCol.className.replace('col-md-4', 'col-md-3');
                        }

                        // Build "Assigned To" cell
                        var ownerName = window.__ticketOwnerMap[ticketNum];
                        var newCell = document.createElement('div');
                        newCell.className = 'ticket-list__header-column col-md-2';
                        if (ownerName) {
                            newCell.textContent = ownerName;
                        } else {
                            newCell.innerHTML = '<span style="color:#999;font-style:italic">Unassigned</span>';
                        }

                        // Insert before Date column (last col)
                        var datCol = cols[cols.length - 1];
                        datCol.parentNode.insertBefore(newCell, datCol);

                        row.setAttribute('data-assigned-injected', '1');
                    });
                });

                tableObserver.observe(document.getElementById('app'), {
                    childList: true,
                    subtree: true
                });
            })();
        
            // === Dashboard dual-column: All Tickets + Activity ===
            (function() {
                if (window.location.pathname.indexOf('/admin') !== 0) return;

                var allTickets = [];
                var currentPage = 1;
                var totalPages = 1;
                var showClosed = false;
                var isLoading = false;

                function isDashboardPath() {
                    var path = window.location.pathname.replace(/\/+$/, '');
                    return (path === '/admin/panel' || path === '/admin' || path === '/admin/panel/activity');
                }

                function getCSRFParams() {
                    // The app stores session data as: root + "_" + key
                    // e.g. "https://itsupport.securusconverting.com_token"
                    var token = localStorage.getItem(root + '_token') || '';
                    var userId = localStorage.getItem(root + '_userId') || '';
                    return 'csrf_token=' + encodeURIComponent(token) + '&csrf_userid=' + encodeURIComponent(userId);
                }

                function fetchTickets(page, closed, append) {
                    isLoading = true;
                    var tbody = document.getElementById('dt-tbody');
                    var btn = document.getElementById('dt-load-more-btn');
                    if (tbody && allTickets.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="dt-loading">Loading tickets...</td></tr>';
                    }
                    if (btn) { btn.disabled = true; btn.textContent = 'Loading...'; }

                    fetch(apiRoot + '/staff/get-all-tickets', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'page=' + page + '&closed=' + (closed ? '1' : '0') + '&query=&' + getCSRFParams()
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(resp) {
                        isLoading = false;
                        if (resp.status === 'success' && resp.data) {
                            totalPages = resp.data.pages || 1;
                            var tickets = resp.data.tickets || [];
                            tickets.forEach(function(t) {
                                var num = t.ticketNumber || t.ticket_number;
                                if (num) window.__ticketOwnerMap[String(num)] = (t.owner && t.owner.name) ? t.owner.name : null;
                            });
                            allTickets = append ? allTickets.concat(tickets) : tickets;
                        }
                        renderTickets();
                    })
                    .catch(function() { isLoading = false; renderTickets(); });
                }

                function renderTickets() {
                    var tbody = document.getElementById('dt-tbody');
                    if (!tbody) return;
                    var btn = document.getElementById('dt-load-more-btn');

                    if (allTickets.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="dt-empty">No tickets found</td></tr>';
                        if (btn) btn.style.display = 'none';
                        return;
                    }

                    var html = '';
                    allTickets.forEach(function(t) {
                        var num = t.ticketNumber || t.ticket_number || '';
                        var title = t.title || '';
                        var fullTitle = title;
                        if (title.length > 40) title = title.substring(0, 40) + '...';
                        var author = (t.author && t.author.name) ? t.author.name : (t.authorName || t.authorEmail || '');
                        var owner = (t.owner && t.owner.name) ? t.owner.name : '';
                        var date = t.date || '';
                        var isClosed = t.closed;

                        html += '<tr class="dt-row' + (isClosed ? ' dt-row--closed' : '') + '" data-ticket="' + num + '">';
                        html += '<td class="dt-number">#' + num + '</td>';
                        html += '<td class="dt-title" title="' + fullTitle.replace(/"/g, '&quot;') + '">' + title + '</td>';
                        html += '<td>' + author + '</td>';
                        html += '<td>' + (owner || '<span class="dt-unassigned">Unassigned</span>') + '</td>';
                        html += '<td>' + date + '</td>';
                        html += '</tr>';
                    });
                    tbody.innerHTML = html;

                    tbody.querySelectorAll('.dt-row').forEach(function(row) {
                        row.addEventListener('click', function() {
                            var tn = this.getAttribute('data-ticket');
                            if (tn) window.location.href = '/admin/panel/tickets/view-ticket/' + tn;
                        });
                    });

                    if (btn) {
                        btn.style.display = (currentPage >= totalPages) ? 'none' : 'block';
                        btn.disabled = false;
                        btn.textContent = 'Load More';
                    }
                }

                function injectDashboard() {
                    // Already have our panel? skip.
                    if (document.getElementById('dashboard-dual-tickets')) return;

                    var activityPanel = document.querySelector('.admin-panel-activity');
                    if (!activityPanel) return;
                    if (!isDashboardPath()) return;

                    // Add flex class to activity panel's parent (don't move any React nodes)
                    var parent = activityPanel.parentNode;
                    parent.classList.add('dashboard-dual-active');

                    // Create our tickets panel and insert it before the activity panel
                    // CSS order:-1 puts it on the left
                    var ticketsPanel = document.createElement('div');
                    ticketsPanel.className = 'dashboard-dual__tickets';
                    ticketsPanel.id = 'dashboard-dual-tickets';
                    ticketsPanel.innerHTML = '<h4>All Tickets</h4>' +
                        '<div class="dt-controls">' +
                        '  <label><input type="checkbox" id="dt-show-closed"> Show closed tickets</label>' +
                        '</div>' +
                        '<table class="dashboard-tickets-table">' +
                        '  <colgroup><col style="width:14%"><col style="width:36%"><col style="width:18%"><col style="width:16%"><col style="width:16%"></colgroup>' +
                        '  <thead><tr>' +
                        '    <th>#</th><th>Title</th><th>Author</th><th>Assigned</th><th>Date</th>' +
                        '  </tr></thead>' +
                        '  <tbody id="dt-tbody"><tr><td colspan="5" class="dt-loading">Loading tickets...</td></tr></tbody>' +
                        '</table>' +
                        '<button id="dt-load-more-btn" class="dt-load-more" style="display:none;">Load More</button>';

                    parent.insertBefore(ticketsPanel, activityPanel);

                    document.getElementById('dt-show-closed').addEventListener('change', function() {
                        showClosed = this.checked;
                        currentPage = 1;
                        allTickets = [];
                        fetchTickets(1, showClosed, false);
                    });

                    document.getElementById('dt-load-more-btn').addEventListener('click', function() {
                        if (currentPage < totalPages && !isLoading) {
                            currentPage++;
                            fetchTickets(currentPage, showClosed, true);
                        }
                    });

                    allTickets = [];
                    currentPage = 1;
                    totalPages = 1;
                    showClosed = false;
                    fetchTickets(1, false, false);
                }

                function cleanupDashboard() {
                    var ticketsPanel = document.getElementById('dashboard-dual-tickets');
                    if (ticketsPanel) {
                        var parent = ticketsPanel.parentNode;
                        if (parent) parent.classList.remove('dashboard-dual-active');
                        ticketsPanel.remove();
                    }
                    allTickets = [];
                    currentPage = 1;
                    totalPages = 1;
                    showClosed = false;
                }

                var obs = new MutationObserver(function() {
                    if (isDashboardPath()) {
                        injectDashboard();
                    } else {
                        cleanupDashboard();
                    }
                });
                obs.observe(document.getElementById('app'), { childList: true, subtree: true });
            })();


            // === Urgent ticket toggle ===
            (function() {
                window.__urgentTicket = false;
                window.__urgentPhone = '';

                // XHR intercept for ticket create
                var _xhrOpen = XMLHttpRequest.prototype.open;
                var _xhrSend = XMLHttpRequest.prototype.send;
                XMLHttpRequest.prototype.open = function(m, u) {
                    this.__u = u;
                    return _xhrOpen.apply(this, arguments);
                };
                XMLHttpRequest.prototype.send = function(body) {
                    if (this.__u && this.__u.indexOf('/ticket/create') !== -1 && window.__urgentTicket && body) {
                        var ph = (window.__urgentPhone || '').trim();
                        if (typeof body === 'string') {
                            body += '&urgent=1&urgentPhone=' + encodeURIComponent(ph);
                        } else if (body instanceof FormData) {
                            body.append('urgent', '1');
                            body.append('urgentPhone', ph);
                        }
                    }
                    return _xhrSend.call(this, body);
                };

                function inject() {
                    var form = document.querySelector('.create-ticket-form');
                    if (!form || form.dataset.urg) return;

                    // Find the Title form-field by scanning labels
                    var fields = form.querySelectorAll('.form-field');
                    var titleField = null;
                    for (var i = 0; i < fields.length; i++) {
                        var lbl = fields[i].querySelector('.form-field__label');
                        if (lbl && lbl.textContent.trim().toLowerCase() === 'title') {
                            titleField = fields[i];
                            break;
                        }
                    }
                    if (!titleField) return;
                    form.dataset.urg = '1';
                    window.__urgentTicket = false;
                    window.__urgentPhone = '';

                    // Build the row: [ Title field ] [ Urgent button ]
                    var row = document.createElement('div');
                    row.className = 'urgent-row';
                    titleField.parentNode.insertBefore(row, titleField);
                    row.appendChild(titleField);

                    var wrap = document.createElement('div');
                    wrap.className = 'urgent-toggle-wrap';
                    wrap.innerHTML = '<button type="button" class="urgent-toggle-btn" id="urg-btn">' +
                        '<span class="urgent-icn"><i class="fas fa-exclamation-triangle"></i></span> ' +
                        'Urgent</button>';
                    row.appendChild(wrap);

                    // Detail bar (slides open below)
                    var detail = document.createElement('div');
                    detail.className = 'urgent-detail-row';
                    detail.id = 'urg-detail';
                    detail.innerHTML = '<div class="urgent-detail-inner">' +
                        '<span class="urgent-msg"><strong>\u26A0</strong> Business function is down. ' +
                        'You must be reachable by phone for 15 min.</span>' +
                        '<span class="urgent-phone-group">' +
                        '<label>Phone:</label>' +
                        '<input type="tel" id="urg-phone" placeholder="555-123-4567" />' +
                        '</span></div>';
                    row.parentNode.insertBefore(detail, row.nextSibling);

                    var btn = document.getElementById('urg-btn');
                    var bar = document.getElementById('urg-detail');
                    var ph = document.getElementById('urg-phone');

                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.__urgentTicket = !window.__urgentTicket;
                        btn.classList.toggle('urgent-on', window.__urgentTicket);
                        bar.classList.toggle('open', window.__urgentTicket);
                        if (window.__urgentTicket) ph.focus();
                        if (!window.__urgentTicket) { window.__urgentPhone = ''; ph.value = ''; }
                    });
                    ph.addEventListener('input', function() { window.__urgentPhone = this.value; });
                }

                var obs = new MutationObserver(inject);
                obs.observe(document.getElementById('app'), { childList: true, subtree: true });
            })();


            // === BCC Email setting (admin system preferences) ===
            (function() {
                if (window.location.pathname.indexOf('/admin') !== 0) return;

                function getCSRF() {
                    var token = localStorage.getItem(root + '_token') || '';
                    var userId = localStorage.getItem(root + '_userId') || '';
                    return 'csrf_token=' + encodeURIComponent(token) + '&csrf_userid=' + encodeURIComponent(userId);
                }

                function tryInject() {
                    if (document.getElementById('bcc-email-setting')) return;
                    var anchor = document.querySelector('.admin-panel-email-settings__servers');
                    if (!anchor) return;

                    var box = document.createElement('div');
                    box.className = 'bcc-email-setting';
                    box.id = 'bcc-email-setting';
                    box.innerHTML =
                        '<label for="bcc-email-input">BCC Notification Email</label>' +
                        '<div class="bcc-desc">Ticket correspondence (staff alerts, replies) is BCC\'d here. Urgent tickets are sent directly to this address. Leave blank to disable.</div>' +
                        '<div style="display:flex;align-items:center">' +
                        '<input type="email" id="bcc-email-input" placeholder="e.g. it-notify@azcocorp.com" />' +
                        '<span class="bcc-email-saved" id="bcc-email-saved">Saved</span>' +
                        '</div>';

                    anchor.insertAdjacentElement('afterend', box);

                    var input = document.getElementById('bcc-email-input');
                    var saved = document.getElementById('bcc-email-saved');

                    // Load current value
                    fetch(apiRoot + '/system/get-settings', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'allSettings=1&' + getCSRF()
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(resp) {
                        if (resp.status === 'success' && resp.data && resp.data['bcc-email'] != null) {
                            input.value = resp.data['bcc-email'];
                        }
                    }).catch(function() {});

                    function save() {
                        fetch(apiRoot + '/system/edit-settings', {
                            method: 'POST',
                            credentials: 'include',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'bcc-email=' + encodeURIComponent(input.value) + '&' + getCSRF()
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(resp) {
                            if (resp.status === 'success') {
                                saved.classList.add('show');
                                setTimeout(function() { saved.classList.remove('show'); }, 2000);
                            }
                        }).catch(function() {});
                    }

                    input.addEventListener('blur', save);
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') { e.preventDefault(); save(); input.blur(); }
                    });
                }

                // Use both MutationObserver and interval for reliability
                new MutationObserver(tryInject).observe(document.getElementById('app'), { childList: true, subtree: true });
                setInterval(tryInject, 1000);
            })();


            // === Maintenance mode BCC override (system preferences) ===
            (function() {
                if (window.location.pathname.indexOf('/admin') !== 0) return;

                function mGetCSRF() {
                    var token = localStorage.getItem(root + '_token') || '';
                    var userId = localStorage.getItem(root + '_userId') || '';
                    return 'csrf_token=' + encodeURIComponent(token) + '&csrf_userid=' + encodeURIComponent(userId);
                }

                function tryInjectOverride() {
                    if (document.getElementById('maint-override-box')) return;
                    var anchor = document.querySelector('.admin-panel-system-preferences__maintenance');
                    if (!anchor) return;

                    var box = document.createElement('div');
                    box.className = 'maint-override-box';
                    box.id = 'maint-override-box';
                    box.innerHTML =
                        '<label>Notification Override</label>' +
                        '<div class="maint-desc">While maintenance mode is on, all ticket notifications will go to this email only instead of the normal BCC address.</div>' +
                        '<div style="display:flex;align-items:center">' +
                        '<input type="email" id="maint-override-input" placeholder="your-email@azcocorp.com" />' +
                        '<span class="maint-override-saved" id="maint-override-saved">Saved</span>' +
                        '</div>';

                    anchor.insertAdjacentElement('afterend', box);

                    var input = document.getElementById('maint-override-input');
                    var saved = document.getElementById('maint-override-saved');

                    // Load settings and sync visibility
                    fetch(apiRoot + '/system/get-settings', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'allSettings=1&' + mGetCSRF()
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(resp) {
                        if (resp.status === 'success' && resp.data) {
                            if (resp.data['maintenance-bcc-override'] != null) {
                                input.value = resp.data['maintenance-bcc-override'];
                            }
                            if (resp.data['maintenance-mode']) {
                                box.classList.add('open');
                            }
                        }
                    }).catch(function() {});

                    // Watch the maintenance toggle for changes
                    var maintToggle = anchor.querySelector('input[type="checkbox"], .toggle-button, [class*="toggle"]');
                    if (maintToggle) {
                        maintToggle.addEventListener('click', function() {
                            setTimeout(function() {
                                // Re-check: toggle may have changed
                                var isOn = maintToggle.className.indexOf('disabled') === -1;
                                box.classList.toggle('open', isOn);
                            }, 200);
                        });
                    }

                    // Poll the toggle state (React may re-render it)
                    setInterval(function() {
                        var tog = anchor.querySelector('.toggle-button');
                        if (!tog) return;
                        var isOn = tog.className.indexOf('disabled') === -1;
                        if (isOn && !box.classList.contains('open')) box.classList.add('open');
                        if (!isOn && box.classList.contains('open')) box.classList.remove('open');
                    }, 500);

                    function saveOverride() {
                        fetch(apiRoot + '/system/edit-settings', {
                            method: 'POST',
                            credentials: 'include',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'maintenance-bcc-override=' + encodeURIComponent(input.value) + '&' + mGetCSRF()
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(resp) {
                            if (resp.status === 'success') {
                                saved.classList.add('show');
                                setTimeout(function() { saved.classList.remove('show'); }, 2000);
                            }
                        }).catch(function() {});
                    }

                    input.addEventListener('blur', saveOverride);
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') { e.preventDefault(); saveOverride(); input.blur(); }
                    });
                }

                new MutationObserver(tryInjectOverride).observe(document.getElementById('app'), { childList: true, subtree: true });
                setInterval(tryInjectOverride, 1000);
            })();

        </script>
    </body>
</html>

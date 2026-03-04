<?php
    $path = rtrim(str_replace("\\","/",dirname($_SERVER["PHP_SELF"])), "/");
    $isHttps = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https");
    $url = ($isHttps ? "https://" : "http://" ) . $_SERVER["HTTP_HOST"] . $path;
    header("X-Frame-Options: DENY");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Clear-Site-Data: \"cache\"");
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <title>IT Support (AZCO)</title>

        <link rel="manifest" href="<?=strpos($_SERVER['REQUEST_URI'], '/admin') === 0 ? '/manifest-admin.json' : '/manifest.json'?>">
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
                // Unregister old SW and register fresh
                navigator.serviceWorker.getRegistrations().then(function(regs) {
                    regs.forEach(function(r) { r.unregister(); });
                }).then(function() {
                    navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' });
                });
            }

            // Version check: fetch /version.txt (no cache) and hard-reload if stale
            (function() {
                if (sessionStorage.getItem('_vcheck') === APP_VERSION) return;
                fetch('/version.txt', { cache: 'no-store' })
                    .then(function(r) { return r.text(); })
                    .then(function(v) {
                        if (v.trim() !== APP_VERSION) {
                            // Page is stale — nuke SW + caches and hard-reload
                            if ('serviceWorker' in navigator) {
                                navigator.serviceWorker.getRegistrations().then(function(regs) {
                                    return Promise.all(regs.map(function(r) { return r.unregister(); }));
                                }).then(function() {
                                    if ('caches' in window) {
                                        caches.keys().then(function(names) {
                                            names.forEach(function(n) { caches.delete(n); });
                                        });
                                    }
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

        </script>
    </body>
</html>

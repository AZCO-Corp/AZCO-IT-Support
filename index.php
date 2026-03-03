<?php
    $path = rtrim(str_replace("\\","/",dirname($_SERVER["PHP_SELF"])), "/");
    $isHttps = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https");
    $url = ($isHttps ? "https://" : "http://" ) . $_SERVER["HTTP_HOST"] . $path;
    header("X-Frame-Options: DENY");
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
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js');
            }

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
                    if (self._url && (self._url.indexOf('/staff/get-tickets') !== -1 || self._url.indexOf('/staff/get-new-tickets') !== -1)) {
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
        </script>
    </body>
</html>

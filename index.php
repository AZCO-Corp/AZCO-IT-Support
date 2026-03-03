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
        <meta name="theme-color" content="#414a52">
        <link rel="icon" type="image/png" href="<?=$url ?>/images/support-32.png">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/v4-shims.css">
        <style>
            .admin-login-btn {
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
            .admin-login-btn:hover {
                background: #414a52;
                color: #fff;
                border-color: #414a52;
            }
            .admin-login-btn i {
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
        <script src="<?=$url ?>/bundle.js"></script>
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js');
            }

            // Inject "Admin Login" button into the user login widget
            (function() {
                if (window.location.pathname.indexOf('/admin') === 0) return;

                var observer = new MutationObserver(function(mutations) {
                    var container = document.querySelector('.main-home-page__link-buttons-container');
                    if (container && !document.getElementById('admin-login-btn')) {
                        var btn = document.createElement('a');
                        btn.id = 'admin-login-btn';
                        btn.href = '/admin';
                        btn.className = 'admin-login-btn';
                        var icon = document.createElement('i');
                        icon.className = 'fas fa-shield-alt';
                        btn.appendChild(icon);
                        btn.appendChild(document.createTextNode(' Admin Login'));
                        container.appendChild(btn);
                    }
                });

                observer.observe(document.getElementById('app'), {
                    childList: true,
                    subtree: true
                });
            })();
        </script>
    </body>
</html>
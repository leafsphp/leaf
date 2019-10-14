<?php
    require_once __DIR__ . '/src/router/Request.php';
    require_once __DIR__ . '/src/router/Router.php';

    // core modules
    require __DIR__ . '/src/core/respond.php';
    require __DIR__ . '/src/core/request.php';
    require __DIR__ . '/src/core/fieldValidate.php';
    require __DIR__ . '/src/core/date.php';
    require __DIR__ . '/src/core/auth.php';

    // helpers    
    require __DIR__ . '/src/helpers/constants.php';
    require __DIR__ . '/src/helpers/jwt.php';

    // config files
    require __DIR__ . '/src/config/db.php';
    require __DIR__ . '/src/config/headers.php';
    require __DIR__ . '/src/config/init.php';

    // routes
    require __DIR__ . '/routes/index.php';

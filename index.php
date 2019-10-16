<?php
    // header config
    require __DIR__ . '/src/config/headers.php';

    // router
    require_once __DIR__ . '/src/router/Request.php';
    require_once __DIR__ . '/src/router/Router.php';

    // core modules
    require __DIR__ . '/src/core/respond.php';
    require __DIR__ . '/src/core/request.php';
    require __DIR__ . '/src/core/fieldValidate.php';
    require __DIR__ . '/src/core/date.php';

    // helpers    
    require __DIR__ . '/src/helpers/constants.php';
    require __DIR__ . '/src/helpers/jwt.php';

    // config files
    require __DIR__ . '/src/config/db.php';

    // dependent modules
    require __DIR__ . '/src/core/auth.php';

    // module init
    require __DIR__ . '/src/config/init.php';

    // routes
    require __DIR__ . '/routes/index.php';

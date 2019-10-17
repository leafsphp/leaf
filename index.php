<?php
    // header config
    require __DIR__ . '/src/Config/headers.php';
    // request/response
    require __DIR__ . '/src/Core/respond.php';
    require __DIR__ . '/src/Core/request.php';

    // router 
    require_once __DIR__ . '/src/Core/router.php';

    // core modules
    require __DIR__ . '/src/Core/fieldValidate.php';
    require __DIR__ . '/src/Core/date.php';

    // helpers    
    require __DIR__ . '/src/Helpers/constants.php';
    require __DIR__ . '/src/Helpers/jwt.php';

    // config files
    require __DIR__ . '/src/Config/db.php';

    // dependent modules
    require __DIR__ . '/src/Core/auth.php';

    // module init
    require __DIR__ . '/src/Config/init.php';

    // routes
    require __DIR__ . '/routes/index.php';

	$leaf->run();
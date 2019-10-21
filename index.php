<?php
    // header config
    require __DIR__ . '/src/Config/headers.php';
    // request/response
    require __DIR__ . '/src/Core/response.php';
    require __DIR__ . '/src/Core/request.php';

    // router 
    require_once __DIR__ . '/src/Core/leaf.php';

    // core modules
    require __DIR__ . '/src/Core/validation.php';
    require __DIR__ . '/src/Core/customdate.php';

    // helpers    
    require __DIR__ . '/src/Helpers/constants.php';
    require __DIR__ . '/src/Helpers/jwt.php';

    // config files
    require __DIR__ . '/src/Config/db.php';

    // dependent modules
    require __DIR__ . '/src/Core/authentication.php';

    // module init
    // require __DIR__ . '/src/Config/init.php';

    // routes
    $leaf = new Leaf\Core\Leaf;
    $response = new Leaf\Core\Response;
    $request = new Leaf\Core\Request;

    $leaf->get('/home', function() use($response, $request) {
        $id = $request->getParam('id');
        echo $response->respond(["message" => "Welcome to the Leaf Framework....your id is ".$id ]);
    });

	$leaf->run();
<?php
    // header config
    require __DIR__ . '/src/Config/headers.php';
    // request/response
    require __DIR__ . '/src/Core/Http/response.php';
    require __DIR__ . '/src/Core/Http/request.php';
    require __DIR__ . '/src/Core/Http/session.php';

    // router 
    require_once __DIR__ . '/src/Core/leaf.php';

    // core modules
    require __DIR__ . '/src/Core/validation.php';
    require __DIR__ . '/src/Core/leafdate.php';

    // helpers    
    require __DIR__ . '/src/Helpers/constants.php';
    require __DIR__ . '/src/Helpers/jwt.php';

    // config files
    require __DIR__ . '/src/Config/db.php';

    // dependent modules
    require __DIR__ . '/src/Core/authentication.php';
    // require __DIR__ . '/src/Core/csrf.php';

    // module init
    // require __DIR__ . '/src/Config/init.php';

    // routes
    $leaf = new Leaf\Core\Leaf;
    $response = new Leaf\Core\Http\Response;
    $request = new Leaf\Core\Http\Request;
    $date = new Leaf\Core\LeafDate;
    $session = new Leaf\Core\Http\Session;

    $leaf->get('/home', function() use($response, $request, $session) {
        $session->set('hello', 'User created');
        $response->respond(["message" => $session->getBody() ]);
        // echo json_encode($body);
    });

    $leaf->get('/logout', function() use($session) {
        $session->destroy();
        echo "User logged out";
    });

    $leaf->get('/date', function() use($date, $response) {
        $data = $date->GetDayFromNumber(2);
        $response->respond(["message" => $data]);
    });

	$leaf->run();
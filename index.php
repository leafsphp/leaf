<?php
    // header config
    // require __DIR__ . '/src/Config/headers.php';
    // header('content-type: text/html');
    // request/response
    require __DIR__ . '/src/Core/Http/response.php';
    require __DIR__ . '/src/Core/Http/request.php';
    require __DIR__ . '/src/Core/Http/session.php';
    require __DIR__ . '/src/Veins/Template.php';

    // router 
    require_once __DIR__ . '/src/Core/leaf.php';

    // core modules
    require __DIR__ . '/src/Core/validation.php';
    require __DIR__ . '/src/Core/date.php';

    // helpers    
    require __DIR__ . '/src/Helpers/constants.php';
    require __DIR__ . '/src/Helpers/jwt.php';

    // config files
    require __DIR__ . '/src/Config/db.php';

    // dependent modules
    require __DIR__ . '/src/Core/authentication.php';
    require __DIR__ . '/src/Core/Middleware/middlewareInterface.php';
    require __DIR__ . '/src/Core/csrf.php';


    // module init
    // require __DIR__ . '/src/Config/init.php';

    // routes
    $leaf = new Leaf\Core\Leaf;
    $response = new Leaf\Core\Http\Response;
    $request = new Leaf\Core\Http\Request;
    $date = new Leaf\Core\Date;
    $session = new Leaf\Core\Http\Session;
    $csrf = new Leaf\Core\CSRF;
    $vein = new Leaf\Veins\Template;

    $vein->registerPlugin(new Leaf\Veins\Template\Plugin\PathReplace);

    $leaf->set404(function() use($response) {
        $response->respond("Error 404");
    });

    $leaf->get('/', function() use($vein, $response) {
        $vein->assign([
            "title" => "Veins",
            "pageTitle" => "Leaf Veins",
            "headerLinks" => [
                "home" => "Home",
                "about" => "About",
                "contact" => "Contact"
            ],
            "articles" => [
                "one" => [
                    "id" => 1,
                    "title" => "One",
                    "body" => "This is article one...aka body 1"
                ],
                "two" => [
                    "id" => 2,
                    "title" => "Two",
                    "body" => "This is article two...aka body 2"
                ],
                "three" => [
                    "id" => 3,
                    "title" => "Three",
                    "body" => "This is article three...aka body 3"
                ],
            ]
        ]);
        $vein->renderTemplate("index");
    });

    $leaf->get('/article/{id}', function($id) use($vein, $response) {
        $vein->assign([
            "title" => "Veins",
            "pageTitle" => "Leaf Veins",
            "id" => $id,
            "headerLinks" => [
                "home" => "Home",
                "about" => "About",
                "contact" => "Contact"
            ]
        ]);
        echo $vein->renderTemplate("article");
    });

    $leaf->get('/home', function() use($response, $request, $session) {
        // $session->set('hello', 'User created');
        // $response->respond(["message" => $session->getBody() ]);
        $response->renderMarkup('
        <form method="post" action="/user/add">
        <input type="text" name="username" placeholder="username">
                <input type="text" name="password" placeholder="password">
                <button>Submit</button>
            </form>
        ');
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

    $leaf->post('/user/add', function() use($response, $request, $csrf, $session) {
        $username = $request->getParam('username');
        $session->set('Username', $username);
        $response->respond($request->getBody());
    });

	$leaf->run();
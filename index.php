<?php
/**
 * Step 1: Require the Leaf Framework
 *
 * If you are not using Composer, you need to require the
 * Leaf Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Leaf/Leaf.php';
/**
 * Composer autoloader for extra packages
 */
require 'vendor/autoload.php';

\Leaf\Leaf::registerAutoloader();
/**
 * Step 2: Instantiate a Leaf application
 *
 * This example instantiates a Leaf application using
 * its default settings. However, you will usually configure
 * your Leaf application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Leaf\Leaf();
/**
 * Initialise the Leaf Form package
 */
$form = new \Leaf\Form();
/**
 * Initialise the Leaf Auth package
 */
$auth = new \Leaf\Auth();

/**
 * Leaf's 404 Handler, you can pass in a custom HTML page or text as a function
 */
$app->set404();

/**
 * Bringing in a component("controller")
 */
require 'app/Component.php';
/**
 * Step 3: Define the Leaf application routes
 *
 * Here we define several Leaf application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Leaf::get`, `Leaf::post`, `Leaf::put`, `Leaf::patch`, and `Leaf::delete`
 * is an anonymous function.
 */

// Home Route with Leaf Vein Templating
$app->get('/', function () use($app) {
    $app->veins->configure([
        'veins_dir' => 'app/pages/',
        'cache_dir' => 'app/pages/cache/'
    ]);
    $app->veins->set([
        "title" => "Leaf PHP Framework",
        "welcome" => 'Congratulations, you\'re on <span class="green">Leaf</span>'
    ]);
    $app->veins->render("index");
});

// Blade Templating
$app->get("/blade/test", function() use($app) {
    $app->blade->configure("app/pages", "app/pages/cache");
    echo $app->blade->render('test', ['name' => 'Michael Darko']);
});

// Using a Component(controller)
$app->get("/component", "Component@trigger");

$app->get('/form/', function() use($app) {
    $app->response->renderMarkup("
        <form method='POST' action='/post'>
            <input name='username' placeholder='username'>
            <input name='password' placeholder='password'>
            <button>submit</button>
        </form>
    ");
});

$app->post("/login", function() use($app, $auth) {
    // connect to the database
    $auth->connect("localhost", "root", "", "test");
    // return json encoded data
    $app->response->respond(
        // sign a user in, in literally 1 line
        $auth->login("users", $app->request->getBody(), "md5")
    );
});

$app->get('/posts', function() use($app) {
    //  connect to database
    $app->db->connect("localhost", "root", "", "mvc");

    // sql select
    $posts = $app->db->select("posts")->fetchAll();
    $data = [];

    foreach ($posts as $post) {
        $post["created_at"] = $post["created_at"] == null ? null : $app->date->getEnglishTimestampFromTimestamp($post["created_at"]);
        $post["updated_at"] = $post["updated_at"] == null ? null : $app->date->getEnglishTimestampFromTimestamp($post["updated_at"]);
        array_push($data, $post);
    }
    $app->response->respond($data);
});

// POST route
$app->post( '/post', function() use($app, $form) {
    // form validate
    // $form->validate([
    //     "username" => ["ValidUsername", "TextOnly"],
    //     "password" => "required"
    // ]);

    // // set session variables
    // if (count($form->errors()) == 0) {
    //     $app->session->set("user", $app->request->getBody());
    //     $app->response->respond($app->session->getBody());
    // } else {
    //     $app->response->respondWithCode($form->errors());
    // }

    echo $form->get("username");

    // echo $_POST["username"];
});

// PUT route
$app->put( '/put', function () {
    echo 'This is a PUT route';
});

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete('/delete', function () {
    echo 'This is a DELETE route';
});

/**
 * Step 4: Run the Leaf application
 *
 * This method should be called last. This executes the Leaf application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
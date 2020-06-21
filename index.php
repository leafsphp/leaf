<?php
/**
 * Step 1: Require the Leaf Framework
 *
 * If you are not using Composer, you need to require the
 * Leaf Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Leaf/App.php';
/**
 * Composer autoloader for extra packages
 */
require 'vendor/autoload.php';

\Leaf\App::registerAutoloader();
/**
 * Step 2: Instantiate a Leaf application
 *
 * This example instantiates a Leaf application using
 * its default settings. However, you will usually configure
 * your Leaf application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Leaf\App();
/**
 * Initialise the Leaf Auth package
 */
$auth = new \Leaf\Auth();
$form = new \Leaf\Form;

/**
 * Leaf's 404 Handler, you can pass in a custom HTML page or text as a function
 */
$app->set404();

/**
 * Step 3: Define the Leaf application routes
 *
 * Here we define several Leaf application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Leaf::get`, `Leaf::post`, `Leaf::put`, `Leaf::patch`, and `Leaf::delete`
 * is an anonymous function.
 */

// Home Route with Blade Templating
$app->get('/', function () use($app) {
    $app->blade->configure("app/pages", "app/pages/cache");
    $page = $app->blade->render("index", [
        "title" => "Leaf PHP Framework",
        "welcome" => 'Congratulations, you\'re on <span class="green">Leaf</span>'
    ]);
    $app->response->renderMarkup($page);
});

// POST route
$app->post('/post', function () use ($app) {
    $app->response->respond("This is a POST route");
});

// Example User Login 
$app->post("/login", function() use($app, $auth) {
    // connect to the database
    $auth->connect("localhost", "root", "", "test");

    // sign a user in, in literally 1 line
    $user = $auth->login("users", $app->request->body(), "md5");

    // return json encoded data
    $app->response->respond(
        !$user ? $auth->errors() : $user
    );
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

$app->get("/pdo", function() use($app) {
    $data = [];
    $db = new \Leaf\Db("localhost", "root", "", "test");
    if (count($db->errors()) > 0) {
        $app->response->throwErr($db->errors());
    }
    // $data = $db->query("SELECT * FROM users")->limit(5)->fetchAll();
    // $data = $db->select("users WHERE id = ?")->bind("1")->fetchAll();
    $data = $db->insert("users")->params([
        "username" => "Sety",
        "email" => "sety@gmail.com",
        "password" => md5("test")
    ])->execute();
    // $data = $db->select("users WHERE id = ?")->bind("1")->fetchAll();
    // $data = $app->db->choose("users", "*", [
    //     "username" => "mychi",
    //     "password" => md5("test")
    // ])->fetchAssoc();
    $app->response->respond($data);
});

/**
 * Step 4: Run the Leaf application
 *
 * This method should be called last. This executes the Leaf application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
<?php

test('application accessors', function () {
    expect(app()->request())->toBeInstanceOf(\Leaf\Http\Request::class);
    expect(app()->response())->toBeInstanceOf(\Leaf\Http\Response::class);
    expect(app()->headers())->toBeInstanceOf(\Leaf\Http\Headers::class);
});

test('app mode', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->setBasePath('/');

    app()->config('test', false);
    app()->config('mode', 'TEST');

    app()->set404(function () {
    });

    app()->script('TEST', function () {
        app()->config('test', true);
    });

    app()->run();

    expect(app()->config('mode'))->toBe('TEST');
    expect(app()->config('test'))->toBe(true);
});

test('set 404', function () {
    app()->config('testKey.one', 'ooooo');

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/home';

    app()->set404(function () {
        app()->config('testKey.one', true);
    });

    app()->run();

    expect(app()->config('testKey.one'))->toBe(true);
});

test('leaf middleware', function () {
    app()->config('anotherKey', false);

    class AppMid extends \Leaf\Middleware
    {
        public function call()
        {
            app()->config('anotherKey', true);
            $this->next();
        }
    }

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->use(new AppMid());
    app()->get('/', function () {
    });
    app()->run();

    expect(app()->config('anotherKey'))->toBe(true);
});

test('in-route middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $app = new Leaf\App();
    $app->config('useMiddleware', false);

    $m = function () use ($app) {
        $app->config('useMiddleware', true);
    };

    $app->get('/', ['middleware' => $m, function () {
    }]);
    $app->run();

    expect($app->config('useMiddleware'))->toBe(true);
});

test('in-route middleware + group', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/group-test';

    $app = new Leaf\App();
    $app->config('useMiddlewares', false);

    $m = function () use ($app) {
        $app->config('useMiddlewares', true);
    };

    $app->group('/group-test', function () use ($app, $m) {
        $app->get('/', ['middleware' => $m, function () {
        }]);
    });

    $app->run();

    expect($app->config('useMiddlewares'))->toBe(true);
});

test('before route middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $app = new Leaf\App();

    $app->config('inTest', 'true');
    $app->before('GET', '/', function () use ($app) {
        $app->config('inTest', 'false');
    });
    $app->get('/', function () {
    });
    $app->run();

    expect($app->config('inTest'))->toBe('false');
});

test('before router middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/test';

    $app = new Leaf\App();

    $app->config('inTest2', 'true');

    $app->before('GET', '/.*', function () use ($app) {
        $app->config('inTest2', 'false');
    });
    $app->get('/test', function () {
    });
    $app->run();

    expect($app->config('inTest2'))->toBe('false');
});

test('swap out leaf response', function () {
    class TestResponse extends \Leaf\Http\Response
    {
        public function customMethod()
        {
            return 'This is some test response';
        }
    }

    $leafInstance1 = new \Leaf\App();
    $leafInstance1->setResponseClass(TestResponse::class);

    expect($leafInstance1->response->customMethod())->toBe('This is some test response');
});

test('get route info', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/homepage';

    $routePath = '/homepage';

    app()->get($routePath, function () use ($routePath) {
        $routeData = app()->getRoute();
        expect($routeData['path'])->toBe($routePath);
    });

    app()->run();
});

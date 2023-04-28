<?php

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

test('in-route named middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $app = new Leaf\App();
    $app->config('useNamedMiddleware', false);
    $app->registerMiddleware('mid1', function () use ($app) {
        $app->config('useNamedMiddleware', true);
    });

    $app->get('/', ['middleware' => 'mid1', function () {
    }]);
    $app->run();

    expect($app->config('useNamedMiddleware'))->toBe(true);
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

test('grouped in-route named middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/groups/test';

    $app = new Leaf\App();
    $app->config('useGroupNamedMiddleware', false);
    $app->registerMiddleware('mid2', function () use ($app) {
        $app->config('useGroupNamedMiddleware', true);
    });

    $app->group('/groups', function () use($app) {
        $app->get('/test', ['middleware' => 'mid2', function () {
        }]);
    });

    $app->run();

    expect($app->config('useGroupNamedMiddleware'))->toBe(true);
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

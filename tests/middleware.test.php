<?php

class StaticTestClassMid
{
    public static $called = false;
}

afterEach(function () {
    StaticTestClassMid::$called = false;
});

test('leaf middleware', function () {
    class AppMid extends \Leaf\Middleware
    {
        public function call()
        {
            StaticTestClassMid::$called = true;
            $this->next();
        }
    }

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->use(new AppMid());
    app()->get('/', function () {});
    app()->run();

    expect(StaticTestClassMid::$called)->toBe(true);
});

test('leaf middleware with next data', function () {

    class AppMid2 extends \Leaf\Middleware
    {
        public function call()
        {
            $this->next([
                'data' => 'Some data',
            ]);
        }
    }

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->use(new AppMid2());

    app()->get('/', function () {});

    app()->run();

    expect(request()->next('data'))->toBe('Some data');
});

test('in-route middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $app = new Leaf\App();

    $m = function () use ($app) {
        StaticTestClassMid::$called = true;
    };

    $app->get('/', ['middleware' => $m, function () {}]);
    $app->run();

    expect(StaticTestClassMid::$called)->toBe(true);
});

test('in-route named middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $app = new Leaf\App();

    $app->registerMiddleware('mid1', function () use ($app) {
        StaticTestClassMid::$called = true;
    });

    $app->get('/', ['middleware' => 'mid1', function () {}]);
    $app->run();

    expect(StaticTestClassMid::$called)->toBe(true);
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
        $app->get('/', ['middleware' => $m, function () {}]);
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

    $app->group('/groups', function () use ($app) {
        $app->get('/test', ['middleware' => 'mid2', function () {}]);
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
    $app->get('/', function () {});
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
    $app->get('/test', function () {});
    $app->run();

    expect($app->config('inTest2'))->toBe('false');
});

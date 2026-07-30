<?php

class StaticTestClassMid
{
    public static $called = false;
}

afterEach(function () {
    StaticTestClassMid::$called = false;
    response()->next([]);
});

test('leaf middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->use(function () {
        StaticTestClassMid::$called = true;
    });
    app()->get('/', function () {});
    app()->run();

    expect(StaticTestClassMid::$called)->toBe(true);
});

test('leaf middleware with next data', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->use(function () {
        response()->next([
            'data' => 'Some data',
        ]);
    });

    app()->get('/', function () {});

    app()->run();

    expect(request()->next('data'))->toBe('Some data');
});

test('in-route middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $app = new Leaf\App();

    $m = function () {
        response()->next([
            'data' => 'in-route middleware',
        ]);
    };

    $app->get('/', ['middleware' => $m, function () {}]);

    $app->run();

    expect(request()->next('data'))->toBe('in-route middleware');
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

test('grouped in-route named middleware', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/groups/test';

    app()->registerMiddleware('mid34', function () {
        app()->response()->next([
            'data' => 'grouped in-route named middleware',
        ]);
    });

    app()->group('/groups', function () {
        app()->get('/test', ['middleware' => 'mid34', function () {}]);
    });

    app()->run();

    expect(app()->request()->next('data'))->toBe('grouped in-route named middleware');
});

test('group middleware runs for dynamic routes inside the group', function () {
    // https://github.com/leafsphp/leaf/issues/316 + #327
    \Leaf\Router::reset();
    app()->setBasePath('/');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/practices/42/submit';

    app()->registerMiddleware('mid316', function () {
        app()->response()->next([
            'data' => 'group middleware on dynamic route',
        ]);
    });

    app()->group('/practices', ['middleware' => 'mid316', function () {
        app()->get('/{id}/submit', function () {});
    }]);

    app()->run();

    expect(app()->request()->next('data'))->toBe('group middleware on dynamic route');
});

test('in-route middleware runs for dynamic routes inside a group', function () {
    \Leaf\Router::reset();
    app()->setBasePath('/');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/teams/7';

    app()->registerMiddleware('mid327', function () {
        app()->response()->next([
            'data' => 'in-route middleware on dynamic route',
        ]);
    });

    app()->group('/teams', function () {
        app()->get('/{id}', ['middleware' => 'mid327', function () {}]);
    });

    app()->run();

    expect(app()->request()->next('data'))->toBe('in-route middleware on dynamic route');
});

test('dynamic group middleware still runs when a catch-all middleware exists', function () {
    // the reported scenario: auth.required on a group + any global middleware
    \Leaf\Router::reset();
    app()->setBasePath('/');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/practices/42/submit';

    $ran = ['global' => false, 'group' => false];

    app()->use(function () use (&$ran) {
        $ran['global'] = true;
    });

    app()->registerMiddleware('mid316b', function () use (&$ran) {
        $ran['group'] = true;
    });

    app()->group('/practices', ['middleware' => 'mid316b', function () {
        app()->get('/{id}/submit', function () {});
    }]);

    app()->run();

    expect($ran['global'])->toBeTrue()
        ->and($ran['group'])->toBeTrue();
});

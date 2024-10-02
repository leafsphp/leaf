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

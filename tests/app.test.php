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

    app()->get('/', function () {});

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

test('set app down', function () {
    app()->config('testKey.three', 1);

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/setAppDown';

    app()->config('app.down', true);

    app()->setDown(function () {
        app()->config('testKey.three', 2);
    });

    app()->post('/setAppDown', function () {
        app()->config('testKey.three', 3);
    });

    app()->run();

    expect(app()->config('testKey.three'))->toBe(2);
    app()->config('app.down', false);
});

test('get route info', function () {
    $routePath = '/getRouteInfo';

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $routePath;

    app()->get($routePath, function () use ($routePath) {
        $routeData = app()->getRoute();
        expect($routeData['path'])->toBe($routePath);
    });

    app()->run();
});

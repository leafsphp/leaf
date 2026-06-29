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

test('static route dispatch uses the matching route', function () {
    app()->config('testKey.route', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/static-route';

    app()->setBasePath('/');

    app()->get('/other-route', function () {
        app()->config('testKey.route', 'other');
    });

    app()->get('/static-route', function () {
        app()->config('testKey.route', 'static');
    });

    app()->run();

    expect(app()->config('testKey.route'))->toBe('static');
});

test('dynamic route dispatch uses the matching route', function () {
    app()->config('testKey.routeParam', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/users/42';

    app()->setBasePath('/');

    app()->get('/posts/{id}', function ($id) {
        app()->config('testKey.routeParam', "post-$id");
    });

    app()->get('/users/{id}', function ($id) {
        app()->config('testKey.routeParam', "user-$id");
    });

    app()->run();

    expect(app()->config('testKey.routeParam'))->toBe('user-42');
});

test('route registration order is preserved for overlapping static and dynamic routes', function () {
    app()->config('testKey.routePriority', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/ordered-users/new';

    app()->setBasePath('/');

    app()->get('/ordered-users/{id}', function ($id) {
        app()->config('testKey.routePriority', "dynamic-$id");
    });

    app()->get('/ordered-users/new', function () {
        app()->config('testKey.routePriority', 'static');
    });

    app()->run();

    expect(app()->config('testKey.routePriority'))->toBe('dynamic-new');
});

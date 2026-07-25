<?php

test('optional params match with and without a value', function () {
    app()->config('testKey.optional', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/opt-posts/5';

    app()->setBasePath('/');

    app()->get('/opt-posts/{id?}', function ($id = 'none') {
        app()->config('testKey.optional', "post-$id");
    });

    app()->run();

    expect(app()->config('testKey.optional'))->toBe('post-5');

    $_SERVER['REQUEST_URI'] = '/opt-posts';

    app()->run();

    expect(app()->config('testKey.optional'))->toBe('post-none');
});

test('root-level optional params match the root path', function () {
    \Leaf\Router::reset();
    app()->config('testKey.rootOptional', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    app()->setBasePath('/');

    app()->get('/{page?}', function ($page = 'home') {
        app()->config('testKey.rootOptional', $page);
    });

    app()->run();

    expect(app()->config('testKey.rootOptional'))->toBe('home');

    $_SERVER['REQUEST_URI'] = '/about';

    app()->run();

    expect(app()->config('testKey.rootOptional'))->toBe('about');
});

test('inline constraints only match the given pattern', function () {
    app()->config('testKey.constraint', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/c-users/42';

    app()->setBasePath('/');

    app()->get('/c-users/{id:[0-9]+}', function ($id) {
        app()->config('testKey.constraint', "user-$id");
    });

    app()->set404(function () {
        app()->config('testKey.constraint', '404');
    });

    app()->run();

    expect(app()->config('testKey.constraint'))->toBe('user-42');

    $_SERVER['REQUEST_URI'] = '/c-users/abc';

    app()->run();

    expect(app()->config('testKey.constraint'))->toBe('404');
});

test('constraints with quantifiers match exact formats', function () {
    app()->config('testKey.quantifier', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/q-blog/2026';

    app()->setBasePath('/');

    app()->get('/q-blog/{year:[0-9]{4}}', function ($year) {
        app()->config('testKey.quantifier', "year-$year");
    });

    app()->set404(function () {
        app()->config('testKey.quantifier', '404');
    });

    app()->run();

    expect(app()->config('testKey.quantifier'))->toBe('year-2026');

    $_SERVER['REQUEST_URI'] = '/q-blog/20261';

    app()->run();

    expect(app()->config('testKey.quantifier'))->toBe('404');
});

test('registration order breaks ties between overlapping dynamic routes', function () {
    app()->config('testKey.dynamicOrder', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/d-items/thing';

    app()->setBasePath('/');

    app()->get('/d-items/{id}', function ($id) {
        app()->config('testKey.dynamicOrder', "first-$id");
    });

    app()->get('/d-items/{slug}', function ($slug) {
        app()->config('testKey.dynamicOrder', "second-$slug");
    });

    app()->run();

    expect(app()->config('testKey.dynamicOrder'))->toBe('first-thing');
});

test('dynamic routes win over catch-all fallbacks', function () {
    \Leaf\Router::reset();
    app()->config('testKey.fallback', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/f-users/7';

    app()->setBasePath('/');

    app()->get('/{catchall}', function ($catchall) {
        app()->config('testKey.fallback', "fallback-$catchall");
    });

    app()->get('/f-users/{id}', function ($id) {
        app()->config('testKey.fallback', "user-$id");
    });

    app()->run();

    expect(app()->config('testKey.fallback'))->toBe('user-7');

    $_SERVER['REQUEST_URI'] = '/anything';

    app()->run();

    expect(app()->config('testKey.fallback'))->toBe('fallback-anything');
});

test('router reset clears all registered routes and handlers', function () {
    app()->config('testKey.reset', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/reset-route';

    app()->setBasePath('/');

    app()->get('/reset-route', function () {
        app()->config('testKey.reset', 'handled');
    });

    app()->run();

    expect(app()->config('testKey.reset'))->toBe('handled');

    \Leaf\Router::reset();
    app()->config('testKey.reset', null);

    app()->setBasePath('/');

    app()->set404(function () {
        app()->config('testKey.reset', '404');
    });

    app()->run();

    expect(app()->config('testKey.reset'))->toBe('404');
});

test('router hooks can replace routes at runtime', function () {
    \Leaf\Router::reset();
    app()->config('testKey.hooked', null);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/hooked-route';

    app()->setBasePath('/');

    app()->get('/original-route', function () {
        app()->config('testKey.hooked', 'original');
    });

    app()->hook('router.before', function ($context) {
        foreach ($context['routes'] as $method => $routes) {
            foreach ($routes as $index => $route) {
                if ($route['pattern'] === '/original-route') {
                    $context['routes'][$method][$index]['pattern'] = '/hooked-route';
                }
            }
        }

        return $context;
    });

    app()->run();

    expect(app()->config('testKey.hooked'))->toBe('original');
});

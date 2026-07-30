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

test('base path is not auto-stripped when the request does not live under the script dir', function () {
    // https://github.com/leafsphp/leaf/issues/323 — php -S serving from a subdir
    \Leaf\Router::reset();
    $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/practices/42';

    $matched = null;
    app()->get('/practices/{id}', function ($id) use (&$matched) {
        $matched = $id;
    });

    app()->run();

    expect(\Leaf\Router::getBasePath())->toBe('/')
        ->and($matched)->toBe('42');
});

test('base path is stripped for real subfolder deployments', function () {
    \Leaf\Router::reset();
    $_SERVER['SCRIPT_NAME'] = '/subdir/index.php';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/subdir/users/5';

    $matched = null;
    app()->get('/users/{id}', function ($id) use (&$matched) {
        $matched = $id;
    });

    app()->run();

    expect(\Leaf\Router::getBasePath())->toBe('/subdir/')
        ->and($matched)->toBe('5');
});

test('setBasePath with an empty string means no base path', function () {
    \Leaf\Router::reset();
    app()->setBasePath('');

    expect(\Leaf\Router::getBasePath())->toBe('/');
});

test('getCurrentUri never fires the 404 handler as a side effect', function () {
    \Leaf\Router::reset();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/outside';
    app()->setBasePath('/api/');

    $fired = false;
    app()->set404(function () use (&$fired) {
        $fired = true;
    });

    $uri = \Leaf\Router::getCurrentUri();

    expect($fired)->toBeFalse()
        ->and($uri)->toBe('/outside'); // path untouched when base doesn't prefix it
});

test('group names cascade into route names', function () {
    // https://github.com/leafsphp/leaf/issues/279
    \Leaf\Router::reset();

    app()->group('/admin', ['name' => 'admin', function () {
        app()->get('/dashboard', ['name' => 'dashboard', function () {}]);

        app()->group('/reports', ['name' => 'reports', function () {
            app()->get('/{id}', ['name' => 'show', function () {}]);
        }]);
    }]);

    expect(\Leaf\Router::route('admin.dashboard'))->toBe('/admin/dashboard')
        ->and(\Leaf\Router::route('admin.reports.show', ['id' => 9]))->toBe('/admin/reports/9');
});

test('resource routes name themselves', function () {
    \Leaf\Router::reset();

    app()->resource('/users', 'UsersController');

    expect(\Leaf\Router::route('users.index'))->toBe('/users')
        ->and(\Leaf\Router::route('users.show', ['id' => 3]))->toBe('/users/3')
        ->and(\Leaf\Router::route('users.edit', ['id' => 3]))->toBe('/users/3/edit');
});

test('resource names compose with group names', function () {
    \Leaf\Router::reset();

    app()->group('/admin', ['name' => 'admin', function () {
        app()->apiResource('/users', 'UsersController');
    }]);

    expect(\Leaf\Router::route('admin.users.index'))->toBe('/admin/users')
        ->and(\Leaf\Router::route('admin.users.update', ['id' => 5]))->toBe('/admin/users/5');
});

test('unnamed routes in named groups stay unnamed', function () {
    \Leaf\Router::reset();

    app()->group('/named', ['name' => 'named', function () {
        app()->get('/plain', function () {});
    }]);

    $ref = new ReflectionClass(\Leaf\Router::class);
    $prop = $ref->getProperty('namedRoutes');
    $prop->setAccessible(true);

    expect($prop->getValue())->toBe([]);
});

<?php

test('application accessors', function () {
	expect(app()->request())->toBeInstanceOf(\Leaf\Http\Request::class);
	expect(app()->response())->toBeInstanceOf(\Leaf\Http\Response::class);
	expect(app()->headers())->toBeInstanceOf(\Leaf\Http\Headers::class);
});

test('app mode', function () {
	app()->config('app.down', false);

	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/';

	app()->config('mode', 'TEST');
	app()->get('/', function () {});
	app()->script('TEST', function () {
		app()->config('app.down', true);
	});

	app()->run();

	expect(app()->config('mode'))->toBe('TEST');
	expect(app()->config('app.down'))->toBe(true);
});

test('set error handler', function () {
	app()->config('app.down', false);

	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/';

	// create an error to trigger error handler
	app()->get('/', function () {$app;});

	app()->setErrorHandler(function () {
		app()->config('app.down', true);
	});

	app()->run();

	expect(app()->config('app.down'))->toBe(true);
});

test('set 404', function () {
	app()->config('app.down', false);

	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/';

	app()->set404(function () {
		app()->config('app.down', true);
	});

	app()->run();

	expect(app()->config('app.down'))->toBe(true);
});

test('leaf middleware', function () {
	$app = new Leaf\App;
	app()->config('app.down', false);

	class AppMid extends \Leaf\Middleware {
		public function call()
		{
			app()->config('app.down', true);
			$this->next();
		}
	}

	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/';

	app()->use(new AppMid);
	app()->get('/', function () {});
	app()->run();

	expect(app()->config('app.down'))->toBe(true);
});

test('in-route middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/';

	$app = new Leaf\App;
	$app->config('app.down', false);

	$m = function () use($app) {
		$app->config('app.down', true);
	};

	$app->get('/', ['middleware' => $m, function () {}]);
	$app->run();

	expect($app->config('app.down'))->toBe(true);
});

test('before route middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/';

	$app = new Leaf\App;

	$app->config('inTest', 'true');
	$app->before('GET', '/', function () use($app) {
		$app->config('inTest', 'false');
	});
	$app->get('/', function () {});
	$app->run();

	expect($app->config('inTest'))->toBe('false');
});

test('before router middleware', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI'] = '/test';

	$app = new Leaf\App;
	
	$app->config('inTest2', 'true');

	$app->before('GET', '/.*', function () use($app) {
		$app->config('inTest2', 'false');
	});
	$app->get('/test', function () {});
	$app->run();

	expect($app->config('inTest2'))->toBe('false');
});

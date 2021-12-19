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

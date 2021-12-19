<?php

test('leaf container defaults', function () {
	$app = new \Leaf\App;

	expect($app->request)->toBeInstanceOf(\Leaf\Http\Request::class);
	expect($app->response)->toBeInstanceOf(\Leaf\Http\Response::class);
	expect($app->headers)->toBeInstanceOf(\Leaf\Http\Headers::class);
});

test('set container item', function () {
	$app = new \Leaf\App;

	$app->register('req', function () {
		return new \Leaf\Http\Request();
	});

	expect($app->req)->toBeInstanceOf(\Leaf\Http\Request::class);
});

test('directly set container item', function () {
	$app = new \Leaf\App;

	$app->item = 1;

	expect($app->item)->toBe(1);
});

test('remove container item', function () {
	unset(app()->request);

	expect(app()->request)->toBeFalsy();
});

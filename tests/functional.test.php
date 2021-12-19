<?php

test('functional mode root', function () {
	expect(app())->toBeInstanceOf(\Leaf\App::class);
});

test('shared app instance', function () {
	$app = new \Leaf\App();
	$app->item = 1;

	expect(app()->item)->toBe(1);
});

test('functional mode response', function () {
	expect(app()->response())->toBeInstanceOf(\Leaf\Http\Response::class);
});

test('functional mode request', function () {
	expect(app()->request())->toBeInstanceOf(\Leaf\Http\Request::class);
});

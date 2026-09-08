<?php

declare(strict_types=1);

use Onelegstudios\Index\Index;

it('resolves the singleton', function () {
    expect(app(Index::class))->toBeInstanceOf(Index::class);
});

it('returns the same instance from the container', function () {
    expect(app(Index::class))->toBe(app(Index::class));
});

it('merges the package config', function () {
    expect(config('index.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('index::messages.placeholder'))->toBe('Index placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('index::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('index:placeholder')
        ->expectsOutputToContain('Index placeholder command executed.')
        ->assertSuccessful();
});

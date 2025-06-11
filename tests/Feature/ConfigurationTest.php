<?php

describe('Application Configuration', function () {
    test('app configuration is loaded correctly', function () {
        expect(config('app.name'))->toBeString();
        expect(config('app.env'))->toBeString();
        expect(config('app.debug'))->toBeIn([true, false]);
        expect(config('app.url'))->toBeString();
    });

    test('database configuration is valid', function () {
        expect(config('database.default'))->toBeString();
        expect(config('database.connections'))->toBeArray();
        expect(config('database.connections.sqlite'))->toBeArray();
    });

    test('filament configuration is loaded', function () {
        expect(config('filament'))->toBeArray();
    });

    test('logging configuration is valid', function () {
        expect(config('logging.default'))->toBeString();
        expect(config('logging.channels'))->toBeArray();
    });

    test('cache configuration is valid', function () {
        expect(config('cache.default'))->toBeString();
        expect(config('cache.stores'))->toBeArray();
    });

    test('mail configuration is valid', function () {
        expect(config('mail.default'))->toBeString();
        expect(config('mail.mailers'))->toBeArray();
    });

    test('session configuration is valid', function () {
        expect(config('session.driver'))->toBeString();
        expect(config('session.lifetime'))->toBeInt();
    });

    test('queue configuration is valid', function () {
        expect(config('queue.default'))->toBeString();
        expect(config('queue.connections'))->toBeArray();
    });
});

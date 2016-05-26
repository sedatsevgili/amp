#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Amp\Coroutine;
use Amp\Observable;
use Amp\Pause;
use Amp\Postponed;
use Amp\Loop\NativeLoop;
use Interop\Async\Loop;

Loop::execute(Amp\coroutine(function () {
    try {
        $postponed = new Postponed;

        $postponed->emit(new Pause(500, 1));
        $postponed->emit(new Pause(1500, 2));
        $postponed->emit(new Pause(1000, 3));
        $postponed->emit(new Pause(2000, 4));
        $postponed->emit(5);
        $postponed->emit(6);
        $postponed->emit(7);
        $postponed->emit(new Pause(2000, 8));
        $postponed->emit(9);
        $postponed->emit(10);
        $postponed->complete(11);

        $generator = function (Observable $observable) {
            $observer = $observable->getObserver();

            while (yield $observer->isValid()) {
                printf("Observable emitted %d\n", $observer->getCurrent());
                yield new Pause(500); // Artificial back-pressure on observer.
            }

            printf("Observable result %d\n", $observer->getReturn());
        };

        yield new Coroutine($generator($postponed->getObservable()));

    } catch (\Exception $exception) {
        printf("Exception: %s\n", $exception);
    }
}), $loop = new NativeLoop());

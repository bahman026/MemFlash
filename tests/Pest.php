<?php

declare(strict_types=1);

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests get the full Laravel application, so facades, the container and
| the database are available.
|
| Unit tests are deliberately left unbound: the FSRS module is pure, with no
| framework, database or clock dependency, and binding it to a booted
| application would only make the suite slower and hide accidental coupling.
|
*/

uses(TestCase::class)->in('Feature');

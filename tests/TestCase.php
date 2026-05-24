<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledViewPath = storage_path('framework/testing/views/'.str_replace('.', '', uniqid('blade_', true)));

        File::ensureDirectoryExists($compiledViewPath);

        config()->set('view.compiled', $compiledViewPath);
    }
}

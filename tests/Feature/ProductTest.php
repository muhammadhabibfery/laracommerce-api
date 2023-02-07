<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Validations\ProductValidation;

class ProductTest extends TestCase
{
    use ProductValidation;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }
}

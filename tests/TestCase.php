<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\Attributes\Seed;
use Illuminate\Foundation\Testing\Attributes\Seeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Os papéis e permissões são dados de referência: o cadastro e os convites
 * dependem deles, então todo teste com banco já começa com eles.
 */
#[Seed]
#[Seeder(RolesAndPermissionsSeeder::class)]
abstract class TestCase extends BaseTestCase
{
    /**
     * As páginas são renderizadas sem depender do build do front (public/build).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}

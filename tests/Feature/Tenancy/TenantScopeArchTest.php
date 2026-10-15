<?php

use App\Models\PersonalAccessToken;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Models com `tenant_id` que não podem ter o escopo, com o motivo.
 */
const MODELS_WITHOUT_TENANT_SCOPE = [
    // O Sanctum busca o token antes de existir empresa no contexto; o
    // middleware `tenant.token` é quem confere a empresa dele.
    PersonalAccessToken::class,
];

it('applies the tenant scope to every model whose table has a tenant_id column', function () {
    $unscopedModels = collect(File::allFiles(app_path('Models')))
        ->map(fn (SplFileInfo $file): string => 'App\\Models\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
        ->filter(fn (string $class): bool => is_subclass_of($class, Model::class) && ! (new ReflectionClass($class))->isAbstract())
        ->filter(fn (string $class): bool => Schema::hasColumn((new $class)->getTable(), 'tenant_id'))
        ->reject(fn (string $class): bool => $class::hasGlobalScope(TenantScope::class))
        ->reject(fn (string $class): bool => in_array($class, MODELS_WITHOUT_TENANT_SCOPE, true));

    expect($unscopedModels->all())->toBeEmpty();
});

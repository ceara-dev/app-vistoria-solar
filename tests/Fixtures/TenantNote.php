<?php

namespace Tests\Fixtures;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Model de empresa mínimo, para testar a tenancy antes de existirem módulos de domínio.
 */
#[Fillable(['title'])]
class TenantNote extends Model
{
    use BelongsToTenant;

    public static function createTable(): void
    {
        Schema::create('tenant_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->timestamps();
        });
    }
}

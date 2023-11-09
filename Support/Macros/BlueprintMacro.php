<?php

declare(strict_types=1);

namespace Modules\Common\Support\Macros;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

/**
 * @mixin \Illuminate\Database\Schema\Blueprint
 */
class BlueprintMacro
{
    public function hasIndex(): callable
    {
        return function (string $index): bool {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();

            return $schemaManager->listTableDetails($this->getTable())->hasIndex($index);
        };
    }

    public function dropIndexIfExists(): callable
    {
        return function (string $index): Fluent {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            if ($this->hasIndex($index)) {
                return $this->dropIndex($index);
            }

            return new Fluent();
        };
    }

    public function extJson($column = 'ext')
    {
        return $this->json($column)->nullable();
    }
}

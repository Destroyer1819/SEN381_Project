<?php

namespace App\Domain\Categories;

use App\Models\Category;

// D-010: maps a category code to its handler. Bound in AppServiceProvider.
// An unknown code falls back to the Other handler instead of failing (R-007).
class CategoryRegistry
{
    /**
     * @param  array<int, class-string<CategoryHandler>>  $handlers  category code => handler class
     * @param  class-string<CategoryHandler>  $fallback
     */
    public function __construct(
        private array $handlers,
        private string $fallback,
    ) {}

    public function forCode(?int $code): CategoryHandler
    {
        return app($this->handlers[$code] ?? $this->fallback);
    }

    public function forCategoryId(mixed $categoryId): CategoryHandler
    {
        $code = is_numeric($categoryId) ? Category::whereKey((int) $categoryId)->value('code') : null;

        return $this->forCode($code);
    }

    // Used by the test that checks every seeded category has its own handler
    public function codes(): array
    {
        return array_keys($this->handlers);
    }
}

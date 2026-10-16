<?php

namespace App\Domain\Categories\Handlers;

use App\Domain\Categories\CategoryHandler;

// Defaults: no extra rules and no routing. Handlers override only what differs for their category.
abstract class BaseCategoryHandler implements CategoryHandler
{
    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    public function defaultAssignee(): ?int
    {
        return null;
    }
}

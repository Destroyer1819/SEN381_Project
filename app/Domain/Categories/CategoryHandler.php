<?php

namespace App\Domain\Categories;

// D-010 Strategy: each category's own validation and routing behaviour lives behind this interface,
// so adding a category means one new handler and one registry line, without editing tested code.
interface CategoryHandler
{
    // Extra validation rules for this category, merged into StoreServiceRequest's base rules
    public function rules(): array;

    // Messages for those extra rules (NFR-008: name the field and say how to fix it)
    public function messages(): array;

    // Staff member new requests in this category should go to, or null to leave them in the open pool.
    // No category routes yet; the hook exists so routing can be added per category later (D-010).
    public function defaultAssignee(): ?int;
}

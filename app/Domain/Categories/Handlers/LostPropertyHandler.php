<?php

namespace App\Domain\Categories\Handlers;

// Lost property (104): the item has to be identifiable to be matched with found items.
class LostPropertyHandler extends BaseCategoryHandler
{
    public const MIN_DESCRIPTION = 20;

    public function rules(): array
    {
        return ['description' => ['min:'.self::MIN_DESCRIPTION]];
    }

    public function messages(): array
    {
        return ['description.min' => 'For lost property, describe the item (what it is, colour, any markings) in at least '.self::MIN_DESCRIPTION.' characters.'];
    }
}

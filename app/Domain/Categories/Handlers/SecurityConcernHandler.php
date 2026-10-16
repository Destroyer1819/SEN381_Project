<?php

namespace App\Domain\Categories\Handlers;

// Security concerns (105): security staff act on the report alone, so it needs more detail
// (what was seen, when, who or what was involved) than a general fault report.
class SecurityConcernHandler extends BaseCategoryHandler
{
    public const MIN_DESCRIPTION = 30;

    public function rules(): array
    {
        return ['description' => ['min:'.self::MIN_DESCRIPTION]];
    }

    public function messages(): array
    {
        return ['description.min' => 'For a security concern, describe what happened, when and who was involved in at least '.self::MIN_DESCRIPTION.' characters.'];
    }
}

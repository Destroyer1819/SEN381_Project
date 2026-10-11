// Client-side mirror of App\Http\Requests\StoreServiceRequest (FR-001).
// The server is always the authority; this only gives instant feedback.
export const LIMITS = { descriptionMin: 10, descriptionMax: 2000, textMax: 255 } as const;

export interface RequestFormInput {
    category_id?: string | number;
    location?: string;
    area?: string;
    description?: string;
}

export type RequestFormErrors = Partial<Record<'category_id' | 'location' | 'area' | 'description', string>>;

export function validateRequestForm(values: RequestFormInput): RequestFormErrors {
    const errors: RequestFormErrors = {};
    const location = (values.location ?? '').trim();
    const area = (values.area ?? '').trim();
    const description = (values.description ?? '').trim();

    if (!values.category_id) errors.category_id = 'Choose a category.';

    if (!location) errors.location = 'Enter the location.';
    else if (location.length > LIMITS.textMax) errors.location = `Location must be at most ${LIMITS.textMax} characters.`;

    if (!area) errors.area = 'Enter the area.';
    else if (area.length > LIMITS.textMax) errors.area = `Area must be at most ${LIMITS.textMax} characters.`;

    if (!description) errors.description = 'Describe the problem.';
    else if (description.length < LIMITS.descriptionMin) errors.description = `Description must be at least ${LIMITS.descriptionMin} characters.`;
    else if (description.length > LIMITS.descriptionMax) errors.description = `Description must be at most ${LIMITS.descriptionMax} characters.`;

    return errors;
}

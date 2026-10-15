export interface ErrorContent {
    title: string;
    message: string;
}

/** Status codes that have their own screen. Anything else gets the generic wording. */
export const HANDLED_ERROR_STATUSES = [403, 404] as const;

const CONTENT: Record<number, ErrorContent> = {
    403: {
        title: 'You do not have access to this page',
        message: 'Your account role does not allow this. If you think this is a mistake, contact a CivicConnect administrator.',
    },
    404: {
        title: 'Page not found',
        message: 'The page you asked for does not exist or may have been moved. Check the link, or go back to your requests.',
    },
};

export const errorContent = (status: number): ErrorContent =>
    CONTENT[status] ?? { title: 'Something went wrong', message: 'An unexpected error occurred. Please try again.' };

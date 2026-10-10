import * as React from 'react';

import { cn } from '@/lib/utils';

/** A plain <select> styled like the kit's Input. Native on purpose: it is accessible, works on phones and is easy to test. */
const NativeSelect = React.forwardRef<HTMLSelectElement, React.ComponentProps<'select'>>(({ className, ...props }, ref) => (
    <select
        ref={ref}
        className={cn(
            'border-input bg-background ring-offset-background focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-base focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
            className,
        )}
        {...props}
    />
));
NativeSelect.displayName = 'NativeSelect';

export { NativeSelect };

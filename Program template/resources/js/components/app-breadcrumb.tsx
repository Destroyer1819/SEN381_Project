import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { usePage } from '@inertiajs/react';
import { Fragment } from 'react/jsx-runtime';

interface BreadcrumbSegment {
    title: string;
    url?: string;
}

export function AppBreadcrumb() {
    const { url } = usePage();

    // Remove query parameters and trailing slash
    const pathWithoutQuery = url.split('?')[0];
    const currentPath = pathWithoutQuery.endsWith('/')
        ? pathWithoutQuery.slice(0, -1)
        : pathWithoutQuery;
    const segments = currentPath.split('/').filter(Boolean);

    // Generate breadcrumb segments
    const breadcrumbSegments: BreadcrumbSegment[] = segments.map(
        (segment, index) => {
            const path = `/${segments.slice(0, index + 1).join('/')}`;
            return {
                title:
                    segment.charAt(0).toUpperCase() +
                    segment.slice(1).replace(/-/g, ' '),
                url: path,
            };
        },
    );

    if (breadcrumbSegments.length === 0) {
        breadcrumbSegments.push({ title: 'Dashboard' });
    }

    return (
        <Breadcrumb>
            <BreadcrumbList>
                {breadcrumbSegments.map((segment, index) => (
                    <Fragment key={index}>
                        {index < breadcrumbSegments.length - 1 ? (
                            <>
                                <BreadcrumbItem className="hidden md:block">
                                    <BreadcrumbLink>
                                        {segment.title}
                                    </BreadcrumbLink>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator className="hidden md:block" />
                            </>
                        ) : (
                            <BreadcrumbItem className="hidden md:block">
                                <BreadcrumbPage>{segment.title}</BreadcrumbPage>
                            </BreadcrumbItem>
                        )}
                    </Fragment>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    );
}

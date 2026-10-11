import { type PageProps } from '@/types';
import { createInertiaApp, type ResolvedComponent } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';
import { RouteName } from 'ziggy-js';
import { route } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.tsx`,
                import.meta.glob<{ default: ResolvedComponent }>(
                    './Pages/**/*.tsx',
                ),
            ).then((module) => module.default),
        setup: ({ App, props }) => {
            const ziggy = page.props.ziggy as PageProps['ziggy'];

            /* eslint-disable */
            // @ts-expect-error
            global.route<RouteName> = (name, params, absolute) =>
                route(name, params as any, absolute, {
                    ...ziggy,
                    location: new URL(ziggy.location),
                });
            /* eslint-enable */

            return <App {...props} />;
        },
    }),
);

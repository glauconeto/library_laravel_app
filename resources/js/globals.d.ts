/**
 * Globals injected at runtime by the `@routes` Blade directive
 * (see resources/views/app.blade.php).
 *
 * The Ziggy configuration object is serialized into the page as `const Ziggy = ...`
 * before the scripts load, and `window.route` is bound to it in resources/js/app.tsx.
 */
declare global {
    interface ZiggyConfig {
        url: string;
        port: number | null;
        defaults: Record<string, string | number>;
        routes: Record<string, {
            uri: string;
            methods: ('GET' | 'HEAD' | 'POST' | 'PATCH' | 'PUT' | 'OPTIONS' | 'DELETE')[];
            domain?: string;
            parameters?: string[];
            bindings?: Record<string, string>;
            wheres?: Record<string, unknown>;
            middleware?: string[];
        }>;
        location?: {
            host?: string;
            pathname?: string;
            search?: string;
        };
    }

    const Ziggy: ZiggyConfig;

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    function route(name: string, params?: any, absolute?: boolean): string;

    interface Window {
        route: typeof route;
    }
}

export {};

import type { route as ziggyRoute } from 'ziggy-js';
import type { SharedPageProps, Toast } from './shared';

declare global {
    // eslint-disable-next-line no-var
    var route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedPageProps;
        flashDataType: {
            toast?: Toast;
        };
    }
}

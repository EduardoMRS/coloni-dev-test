import mitt from 'mitt';
import type { App } from 'vue';

export type Events = {
  'open-task-form': { id: number; title: string };
};

export const bus = mitt<Events>();

export default {
  install(app: App) {
    app.config.globalProperties.$bus = bus;
    app.provide('$bus', bus);

    if (typeof window !== 'undefined') {
      window.$bus = bus;
    }
  }
};

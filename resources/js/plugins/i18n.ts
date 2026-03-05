import { createI18n } from 'vue-i18n';
import ptBR from '@/locales/pt-BR.json';
import en from '@/locales/en.json';

export type MessageSchema = typeof ptBR;

const i18n = createI18n<[MessageSchema], 'pt-BR' | 'en'>({
    legacy: false,
    locale: 'pt-BR',
    fallbackLocale: 'en',
    messages: {
        'pt-BR': ptBR,
        en: en,
    },
});

export default {
  install(app: any) {
    app.config.globalProperties.$t = i18n.global.t;
    app.provide('$t', i18n.global.t);

    if (typeof window !== 'undefined') {
      window.$t = i18n.global.t;
    }
  }
};

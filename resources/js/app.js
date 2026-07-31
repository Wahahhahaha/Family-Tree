import './bootstrap';
import '../css/app.css';
import Layout from '@/Layouts/Layout.vue';
import { ZiggyVue } from 'ziggy-js';

import {createApp, h} from 'vue';
import { createInertiaApp } from '@inertiajs/vue3'


createInertiaApp({
    title: title => title ? `${title} - Family Trees` : 'Family Trees',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        let page = pages[`./Pages/${name}.vue`];
        
        if (page.default.layout === undefined) {
            page.default.layout = Layout;
        }
        
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, window.Ziggy);

        // Global translation helper (Safe version)
        app.config.globalProperties.__ = (key) => {
            const pageProps = props.initialPage.props;
            const translations = pageProps.translations || {};
            const menuTranslations = pageProps.menu_translations || {};
            const chatbotTranslations = pageProps.chatbot_translations || {};
            
            return translations[key] || menuTranslations[key] || chatbotTranslations[key] || key;
        };

        app.mount(el);
    },
})
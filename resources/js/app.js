import '../css/app.css'
import 'aos/dist/aos.css'

import { createApp, h, nextTick } from 'vue'
import { createInertiaApp, router } from '@inertiajs/vue3'
import AOS from 'aos'

createInertiaApp({
  resolve: async (name) => {
    const pages = import.meta.glob('./**/pages/**/*.vue')
    const page = pages[`./${name}.vue`]

    if (!page) {
      throw new Error(`Page not found: ${name}`)
    }

    return await page()
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    app.use(plugin)
    app.mount(el)

    AOS.init({
      duration: 700,
      easing: 'ease-out-cubic',
      once: true,
      offset: 40,
    })

    router.on('success', async () => {
      await nextTick()
      AOS.refreshHard()
    })
  },
  progress: {
    color: '#dc2626',
    showSpinner: false,
  },
})
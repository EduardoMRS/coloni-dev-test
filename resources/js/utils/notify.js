import { toast } from 'vue-sonner'

export default {
  install(app) {
    const notify = {
      success(msg, options = {}) {
        toast.success(msg, options)
      },
      error(msg, options = {}) {
        toast.error(msg, options)
      },
      warning(msg, options = {}) {
        toast.warning(msg, options)
      },
      info(msg, options = {}) {
        toast(msg, options)
      },
      custom(renderer) {
        toast.custom(renderer)
      }
    }

    app.config.globalProperties.$notify = notify
    app.provide('notify', notify)
    window.notify = notify
  }
}

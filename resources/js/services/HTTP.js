import axios from 'axios'
import router from '@/router'
// import i18n from '@/i18n'

const instance = axios.create()

instance.defaults.withCredentials = true
instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
instance.defaults.headers.common['Content-Type'] = 'application/json'

instance.interceptors.response.use(
  response => {
    return response
  },
  error => {
    if (axios.isCancel(error)) {
      return error
    }

    const status = error.response.status

    if (status === 404) {
      // 404 not found
      router.push({ name: '404', })
    } else if (status === 403) {
      // Forbidden
      router.push({
        name: '403', params: { errorMessage: error.response.data.message },
      })
    } else if (status === 401) {
      // Session timeout / Logged out
      window.location.href = Application.config.url + '/login'
    } else if (status === 409) {
      // Conflicts
      Application.$emit('conflict', error.response.data.message)
    } else if (status === 419) {
      // Handle expired CSRF token
      Application.$emit('token-expired', error)

      router.push({
        name: 'login', params: { errorMessage: error.response.data.message },
      })
    } else if (status === 422) {
      // Emit form validation errors event
      Application.$emit('form-validation-errors', error.response.data.message)

    } else if (status === 429) {
      // Handle throttle errors
      Application.$emit('too-many-requests', error)
    } else if (status === 503) {
      Application.$emit('maintenance-mode', error.response.data.message)
    } else if (status >= 500) {
      // 500 errors
      Application.$emit('error', error.response.data.message)
    }

    // Do something with response error
    return Promise.reject(error)
  }
)

export default instance
export const CancelToken = axios.CancelToken

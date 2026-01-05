import axios from 'axios';
window.axios = axios;

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Handle 419 CSRF Token Mismatch
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 419) {
      window.location.reload();
    }
    return Promise.reject(error);
  },
);


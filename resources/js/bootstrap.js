import axios from 'axios';
import { setupErrorInterceptor } from './errorHandler';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize the global error interceptor
setupErrorInterceptor(window.axios);

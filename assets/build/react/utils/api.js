import axios from 'axios';
const CSRF_NAME = 'csrf-token'; // = framework.csrf_protection.cookie_name
const SAFE_METHODS = ['get', 'head', 'options'];
function generateToken() {
  const bytes = crypto.getRandomValues(new Uint8Array(18));
  return btoa(String.fromCharCode(...bytes));
}
const api = axios.create({
  baseURL: '/api',
  withCredentials: true
});
api.interceptors.request.use(config => {
  const method = (config.method ?? 'get').toLowerCase();
  if (SAFE_METHODS.includes(method)) return config;
  const token = generateToken();
  const secure = location.protocol === 'https:';
  document.cookie = `${secure ? '__Host-' : ''}${CSRF_NAME}_${token}=${CSRF_NAME}; path=/; samesite=strict` + (secure ? '; secure' : '');
  config.headers.set(CSRF_NAME, token);
  return config;
});
export default api;
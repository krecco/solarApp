// axios
import axios from 'axios'
import { $apiUrl } from '@serverConfig'

const axiosNoJWT = axios.create({
  // You can add your headers here
  // ================================
  baseURL: $apiUrl,
  // timeout: 1000,
  // headers: {'X-Custom-Header': 'foobar'}
})

//  Vue.prototype.$http = axiosIns

export default axiosNoJWT

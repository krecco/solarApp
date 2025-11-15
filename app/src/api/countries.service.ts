import api from './index'

export interface Country {
  code: string
  name: string
  native: string
}

export const countriesService = {
  /**
   * Get list of all countries
   */
  async getCountries(): Promise<Country[]> {
    const response = await api.get('/api/v1/countries')
    return response.data
  },
}

export default countriesService

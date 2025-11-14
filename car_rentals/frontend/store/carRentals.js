import { defineStore } from 'pinia';
import api from '@/services/api';

export const useCarRentalsStore = defineStore('carRentals', {
  state: () => ({
    // Vehicles
    vehicles: [],
    currentVehicle: null,
    vehiclesLoading: false,
    vehiclesError: null,

    // Rentals
    rentals: [],
    currentRental: null,
    rentalsLoading: false,
    rentalsError: null,

    // Filters
    filters: {
      search: '',
      category: null,
      status: null,
      location: null,
      available_from: null,
      available_to: null,
    },

    // Pagination
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    },
  }),

  getters: {
    availableVehicles: (state) => {
      return state.vehicles.filter(v => v.status?.value === 'available');
    },

    activeRentals: (state) => {
      return state.rentals.filter(r => r.status?.value === 'active');
    },

    pendingRentals: (state) => {
      return state.rentals.filter(r => r.status?.value === 'pending');
    },

    completedRentals: (state) => {
      return state.rentals.filter(r => r.status?.value === 'completed');
    },
  },

  actions: {
    // Vehicles Actions
    async fetchVehicles(params = {}) {
      this.vehiclesLoading = true;
      this.vehiclesError = null;

      try {
        const queryParams = {
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage,
          ...this.filters,
          ...params,
        };

        const response = await api.get('/v1/car-rentals/vehicles', { params: queryParams });

        this.vehicles = response.data.data;
        this.pagination = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        };

        return response.data;
      } catch (error) {
        this.vehiclesError = error.response?.data?.message || 'Failed to fetch vehicles';
        throw error;
      } finally {
        this.vehiclesLoading = false;
      }
    },

    async fetchVehicle(id) {
      this.vehiclesLoading = true;
      this.vehiclesError = null;

      try {
        const response = await api.get(`/v1/car-rentals/vehicles/${id}`);
        this.currentVehicle = response.data.data;
        return response.data.data;
      } catch (error) {
        this.vehiclesError = error.response?.data?.message || 'Failed to fetch vehicle';
        throw error;
      } finally {
        this.vehiclesLoading = false;
      }
    },

    async createVehicle(vehicleData) {
      this.vehiclesLoading = true;
      this.vehiclesError = null;

      try {
        const response = await api.post('/v1/car-rentals/vehicles', vehicleData);
        this.vehicles.unshift(response.data.data);
        return response.data.data;
      } catch (error) {
        this.vehiclesError = error.response?.data?.message || 'Failed to create vehicle';
        throw error;
      } finally {
        this.vehiclesLoading = false;
      }
    },

    async updateVehicle(id, vehicleData) {
      this.vehiclesLoading = true;
      this.vehiclesError = null;

      try {
        const response = await api.put(`/v1/car-rentals/vehicles/${id}`, vehicleData);
        const index = this.vehicles.findIndex(v => v.id === id);
        if (index !== -1) {
          this.vehicles[index] = response.data.data;
        }
        if (this.currentVehicle?.id === id) {
          this.currentVehicle = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.vehiclesError = error.response?.data?.message || 'Failed to update vehicle';
        throw error;
      } finally {
        this.vehiclesLoading = false;
      }
    },

    async deleteVehicle(id) {
      this.vehiclesLoading = true;
      this.vehiclesError = null;

      try {
        await api.delete(`/v1/car-rentals/vehicles/${id}`);
        this.vehicles = this.vehicles.filter(v => v.id !== id);
        if (this.currentVehicle?.id === id) {
          this.currentVehicle = null;
        }
      } catch (error) {
        this.vehiclesError = error.response?.data?.message || 'Failed to delete vehicle';
        throw error;
      } finally {
        this.vehiclesLoading = false;
      }
    },

    async checkVehicleAvailability(id, params) {
      try {
        const response = await api.get(`/v1/car-rentals/vehicles/${id}/availability`, { params });
        return response.data.data;
      } catch (error) {
        throw error;
      }
    },

    // Rentals Actions
    async fetchRentals(params = {}) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const queryParams = {
          page: this.pagination.currentPage,
          per_page: this.pagination.perPage,
          ...params,
        };

        const response = await api.get('/v1/car-rentals/rentals', { params: queryParams });

        this.rentals = response.data.data;
        this.pagination = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        };

        return response.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to fetch rentals';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async fetchRental(id) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.get(`/v1/car-rentals/rentals/${id}`);
        this.currentRental = response.data.data;
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to fetch rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async createRental(rentalData) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.post('/v1/car-rentals/rentals', rentalData);
        this.rentals.unshift(response.data.data);
        this.currentRental = response.data.data;
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to create rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async updateRental(id, rentalData) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.put(`/v1/car-rentals/rentals/${id}`, rentalData);
        const index = this.rentals.findIndex(r => r.id === id);
        if (index !== -1) {
          this.rentals[index] = response.data.data;
        }
        if (this.currentRental?.id === id) {
          this.currentRental = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to update rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async verifyRental(id, action, notes = null) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.post(`/v1/car-rentals/rentals/${id}/verify`, {
          action,
          notes,
        });
        const index = this.rentals.findIndex(r => r.id === id);
        if (index !== -1) {
          this.rentals[index] = response.data.data;
        }
        if (this.currentRental?.id === id) {
          this.currentRental = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to verify rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async checkinRental(id, data) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.post(`/v1/car-rentals/rentals/${id}/checkin`, data);
        const index = this.rentals.findIndex(r => r.id === id);
        if (index !== -1) {
          this.rentals[index] = response.data.data;
        }
        if (this.currentRental?.id === id) {
          this.currentRental = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to check in rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async checkoutRental(id, data) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.post(`/v1/car-rentals/rentals/${id}/checkout`, data);
        const index = this.rentals.findIndex(r => r.id === id);
        if (index !== -1) {
          this.rentals[index] = response.data.data;
        }
        if (this.currentRental?.id === id) {
          this.currentRental = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to check out rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    async cancelRental(id, reason = null) {
      this.rentalsLoading = true;
      this.rentalsError = null;

      try {
        const response = await api.post(`/v1/car-rentals/rentals/${id}/cancel`, { reason });
        const index = this.rentals.findIndex(r => r.id === id);
        if (index !== -1) {
          this.rentals[index] = response.data.data;
        }
        if (this.currentRental?.id === id) {
          this.currentRental = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        this.rentalsError = error.response?.data?.message || 'Failed to cancel rental';
        throw error;
      } finally {
        this.rentalsLoading = false;
      }
    },

    // Filter Actions
    updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters };
    },

    resetFilters() {
      this.filters = {
        search: '',
        category: null,
        status: null,
        location: null,
        available_from: null,
        available_to: null,
      };
    },

    // Pagination Actions
    setPage(page) {
      this.pagination.currentPage = page;
    },

    // Clear Actions
    clearCurrentVehicle() {
      this.currentVehicle = null;
    },

    clearCurrentRental() {
      this.currentRental = null;
    },

    clearErrors() {
      this.vehiclesError = null;
      this.rentalsError = null;
    },
  },
});

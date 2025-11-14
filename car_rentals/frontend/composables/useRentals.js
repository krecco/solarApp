import { ref, computed } from 'vue';
import { useCarRentalsStore } from '../store/carRentals';

export function useRentals() {
  const store = useCarRentalsStore();

  const rentals = computed(() => store.rentals);
  const currentRental = computed(() => store.currentRental);
  const loading = computed(() => store.rentalsLoading);
  const error = computed(() => store.rentalsError);
  const activeRentals = computed(() => store.activeRentals);
  const pendingRentals = computed(() => store.pendingRentals);
  const completedRentals = computed(() => store.completedRentals);

  const fetchRentals = async (params = {}) => {
    return await store.fetchRentals(params);
  };

  const fetchRental = async (id) => {
    return await store.fetchRental(id);
  };

  const createRental = async (rentalData) => {
    return await store.createRental(rentalData);
  };

  const updateRental = async (id, rentalData) => {
    return await store.updateRental(id, rentalData);
  };

  const verifyRental = async (id, action, notes = null) => {
    return await store.verifyRental(id, action, notes);
  };

  const checkinRental = async (id, data) => {
    return await store.checkinRental(id, data);
  };

  const checkoutRental = async (id, data) => {
    return await store.checkoutRental(id, data);
  };

  const cancelRental = async (id, reason = null) => {
    return await store.cancelRental(id, reason);
  };

  const clearCurrentRental = () => {
    store.clearCurrentRental();
  };

  return {
    rentals,
    currentRental,
    loading,
    error,
    activeRentals,
    pendingRentals,
    completedRentals,
    fetchRentals,
    fetchRental,
    createRental,
    updateRental,
    verifyRental,
    checkinRental,
    checkoutRental,
    cancelRental,
    clearCurrentRental,
  };
}

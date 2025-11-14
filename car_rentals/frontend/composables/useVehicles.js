import { ref, computed } from 'vue';
import { useCarRentalsStore } from '../store/carRentals';

export function useVehicles() {
  const store = useCarRentalsStore();

  const vehicles = computed(() => store.vehicles);
  const currentVehicle = computed(() => store.currentVehicle);
  const loading = computed(() => store.vehiclesLoading);
  const error = computed(() => store.vehiclesError);
  const availableVehicles = computed(() => store.availableVehicles);

  const fetchVehicles = async (params = {}) => {
    return await store.fetchVehicles(params);
  };

  const fetchVehicle = async (id) => {
    return await store.fetchVehicle(id);
  };

  const createVehicle = async (vehicleData) => {
    return await store.createVehicle(vehicleData);
  };

  const updateVehicle = async (id, vehicleData) => {
    return await store.updateVehicle(id, vehicleData);
  };

  const deleteVehicle = async (id) => {
    return await store.deleteVehicle(id);
  };

  const checkAvailability = async (id, params) => {
    return await store.checkVehicleAvailability(id, params);
  };

  const clearCurrentVehicle = () => {
    store.clearCurrentVehicle();
  };

  return {
    vehicles,
    currentVehicle,
    loading,
    error,
    availableVehicles,
    fetchVehicles,
    fetchVehicle,
    createVehicle,
    updateVehicle,
    deleteVehicle,
    checkAvailability,
    clearCurrentVehicle,
  };
}

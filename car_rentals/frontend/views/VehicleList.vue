<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-4">
          <h1 class="text-h4">{{ $t('car_rentals.vehicles') }}</h1>
          <v-btn
            v-if="canAddVehicle"
            color="primary"
            @click="showAddDialog = true"
          >
            <v-icon left>mdi-plus</v-icon>
            {{ $t('car_rentals.vehicle.add_new') }}
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-row>
      <v-col cols="12" md="3">
        <v-text-field
          v-model="filters.search"
          :label="$t('common.search')"
          prepend-inner-icon="mdi-magnify"
          clearable
          density="compact"
          @update:model-value="fetchVehicles"
        ></v-text-field>
      </v-col>

      <v-col cols="12" md="2">
        <v-select
          v-model="filters.category"
          :label="$t('car_rentals.category.title')"
          :items="categories"
          clearable
          density="compact"
          @update:model-value="fetchVehicles"
        ></v-select>
      </v-col>

      <v-col cols="12" md="2">
        <v-select
          v-model="filters.status"
          :label="$t('common.status')"
          :items="statuses"
          clearable
          density="compact"
          @update:model-value="fetchVehicles"
        ></v-select>
      </v-col>

      <v-col cols="12" md="2">
        <v-select
          v-model="filters.location"
          :label="$t('common.location')"
          :items="locations"
          clearable
          density="compact"
          @update:model-value="fetchVehicles"
        ></v-select>
      </v-col>

      <v-col cols="12" md="3">
        <div class="d-flex gap-2">
          <v-text-field
            v-model="filters.available_from"
            type="date"
            :label="$t('car_rentals.rental.available_from')"
            density="compact"
          ></v-text-field>
          <v-text-field
            v-model="filters.available_to"
            type="date"
            :label="$t('car_rentals.rental.available_to')"
            density="compact"
          ></v-text-field>
        </div>
      </v-col>
    </v-row>

    <!-- Vehicle Grid -->
    <v-row v-if="!loading">
      <v-col
        v-for="vehicle in vehicles"
        :key="vehicle.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <VehicleCard
          :vehicle="vehicle"
          :show-book-button="isCustomer"
          :show-edit-button="canEdit"
          :show-delete-button="canDelete"
          @book="openBookingDialog"
          @view-details="viewVehicleDetails"
          @edit="editVehicle"
          @delete="deleteVehicle"
        />
      </v-col>
    </v-row>

    <!-- Loading State -->
    <v-row v-else>
      <v-col
        v-for="n in 8"
        :key="n"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <v-skeleton-loader type="card"></v-skeleton-loader>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-row v-if="!loading && vehicles.length === 0">
      <v-col cols="12" class="text-center py-12">
        <v-icon size="64" color="grey-lighten-1">mdi-car-off</v-icon>
        <p class="text-h6 mt-4">{{ $t('car_rentals.no_vehicles_found') }}</p>
      </v-col>
    </v-row>

    <!-- Pagination -->
    <v-row v-if="totalPages > 1">
      <v-col cols="12" class="d-flex justify-center">
        <v-pagination
          v-model="currentPage"
          :length="totalPages"
          @update:model-value="fetchVehicles"
        ></v-pagination>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/store/auth';
import VehicleCard from '../components/VehicleCard.vue';
import api from '@/services/api';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();

const loading = ref(false);
const vehicles = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const showAddDialog = ref(false);

const filters = ref({
  search: '',
  category: null,
  status: null,
  location: null,
  available_from: null,
  available_to: null
});

const categories = computed(() => [
  { value: 'economy', title: t('car_rentals.category.economy') },
  { value: 'compact', title: t('car_rentals.category.compact') },
  { value: 'midsize', title: t('car_rentals.category.midsize') },
  { value: 'luxury', title: t('car_rentals.category.luxury') },
  { value: 'suv', title: t('car_rentals.category.suv') },
  { value: 'van', title: t('car_rentals.category.van') },
]);

const statuses = computed(() => [
  { value: 'available', title: t('car_rentals.status.available') },
  { value: 'rented', title: t('car_rentals.status.rented') },
  { value: 'maintenance', title: t('car_rentals.status.maintenance') },
]);

const locations = ref([
  { value: 'Berlin', title: 'Berlin' },
  { value: 'Munich', title: 'Munich' },
  { value: 'Hamburg', title: 'Hamburg' },
  { value: 'Frankfurt', title: 'Frankfurt' },
]);

const isCustomer = computed(() => authStore.user?.role === 'customer');
const canEdit = computed(() => ['manager', 'admin'].includes(authStore.user?.role));
const canDelete = computed(() => authStore.user?.role === 'admin');
const canAddVehicle = computed(() => ['manager', 'admin'].includes(authStore.user?.role));

const fetchVehicles = async () => {
  loading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: 12,
      ...filters.value
    };

    const response = await api.get('/v1/car-rentals/vehicles', { params });
    vehicles.value = response.data.data;
    totalPages.value = response.data.meta.last_page;
  } catch (error) {
    console.error('Failed to fetch vehicles:', error);
  } finally {
    loading.value = false;
  }
};

const openBookingDialog = (vehicle) => {
  router.push({
    name: 'BookVehicle',
    params: { vehicleId: vehicle.id }
  });
};

const viewVehicleDetails = (vehicle) => {
  router.push({
    name: 'VehicleDetails',
    params: { id: vehicle.id }
  });
};

const editVehicle = (vehicle) => {
  router.push({
    name: 'EditVehicle',
    params: { id: vehicle.id }
  });
};

const deleteVehicle = async (vehicle) => {
  if (confirm(t('car_rentals.vehicle.confirm_delete'))) {
    try {
      await api.delete(`/v1/car-rentals/vehicles/${vehicle.id}`);
      fetchVehicles();
    } catch (error) {
      console.error('Failed to delete vehicle:', error);
    }
  }
};

onMounted(() => {
  fetchVehicles();
});
</script>

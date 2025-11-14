<template>
  <v-card class="vehicle-card" elevation="2">
    <v-img
      :src="vehicleImage"
      height="200"
      cover
      class="align-end"
    >
      <v-chip
        :color="statusColor"
        size="small"
        class="ma-2"
      >
        {{ vehicle.status.label }}
      </v-chip>
    </v-img>

    <v-card-title class="text-h6">
      {{ vehicle.full_name }}
    </v-card-title>

    <v-card-subtitle>
      {{ vehicle.category.label }} • {{ vehicle.transmission.label }}
    </v-card-subtitle>

    <v-card-text>
      <div class="d-flex justify-space-between align-center mb-2">
        <div class="text-subtitle-2">
          <v-icon size="small">mdi-seat-passenger</v-icon>
          {{ vehicle.seats }} {{ $t('car_rentals.seats') }}
        </div>
        <div class="text-subtitle-2">
          <v-icon size="small">mdi-fuel</v-icon>
          {{ vehicle.fuel_type.label }}
        </div>
        <div class="text-subtitle-2">
          <v-icon size="small">mdi-map-marker</v-icon>
          {{ vehicle.location }}
        </div>
      </div>

      <v-divider class="my-2"></v-divider>

      <div class="d-flex justify-space-between align-center">
        <div>
          <div class="text-h5 text-primary">
            {{ formatCurrency(vehicle.daily_rate) }}
          </div>
          <div class="text-caption">{{ $t('car_rentals.rental.per_day') }}</div>
        </div>

        <div v-if="vehicle.average_rating" class="d-flex align-center">
          <v-icon color="amber">mdi-star</v-icon>
          <span class="ml-1">{{ vehicle.average_rating }}</span>
          <span class="text-caption ml-1">({{ vehicle.total_reviews }})</span>
        </div>
      </div>

      <div v-if="vehicle.features && vehicle.features.length" class="mt-3">
        <v-chip
          v-for="feature in vehicle.features.slice(0, 3)"
          :key="feature"
          size="x-small"
          class="mr-1 mb-1"
        >
          {{ feature }}
        </v-chip>
      </div>
    </v-card-text>

    <v-card-actions>
      <v-btn
        v-if="showBookButton"
        color="primary"
        variant="elevated"
        block
        @click="$emit('book', vehicle)"
        :disabled="!vehicle.status || vehicle.status.value !== 'available'"
      >
        <v-icon left>mdi-calendar-check</v-icon>
        {{ $t('car_rentals.rental.book_now') }}
      </v-btn>

      <v-btn
        v-if="showDetailsButton"
        color="primary"
        variant="text"
        block
        @click="$emit('view-details', vehicle)"
      >
        {{ $t('car_rentals.vehicle.details') }}
      </v-btn>

      <v-btn
        v-if="showEditButton"
        color="primary"
        variant="text"
        @click="$emit('edit', vehicle)"
      >
        <v-icon>mdi-pencil</v-icon>
      </v-btn>

      <v-btn
        v-if="showDeleteButton"
        color="error"
        variant="text"
        @click="$emit('delete', vehicle)"
      >
        <v-icon>mdi-delete</v-icon>
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  vehicle: {
    type: Object,
    required: true
  },
  showBookButton: {
    type: Boolean,
    default: false
  },
  showDetailsButton: {
    type: Boolean,
    default: true
  },
  showEditButton: {
    type: Boolean,
    default: false
  },
  showDeleteButton: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['book', 'view-details', 'edit', 'delete']);

const { t } = useI18n();

const vehicleImage = computed(() => {
  // Placeholder image - in real app, get from file_container
  return `https://via.placeholder.com/400x200?text=${props.vehicle.make}+${props.vehicle.model}`;
});

const statusColor = computed(() => {
  return props.vehicle.status?.color || 'grey';
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount);
};
</script>

<style scoped>
.vehicle-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.vehicle-card .v-card-text {
  flex-grow: 1;
}
</style>

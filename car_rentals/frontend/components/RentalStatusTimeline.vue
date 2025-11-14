<template>
  <v-timeline side="end" density="compact" class="rental-timeline">
    <v-timeline-item
      v-for="(status, index) in statuses"
      :key="status.value"
      :dot-color="getStatusColor(status, index)"
      :icon="getStatusIcon(status)"
      size="small"
    >
      <template v-slot:opposite>
        <div class="text-caption">
          {{ status.date ? formatDate(status.date) : '' }}
        </div>
      </template>

      <div>
        <div class="font-weight-bold">{{ status.label }}</div>
        <div class="text-caption text-grey">
          {{ status.description }}
        </div>
        <div v-if="status.actor" class="text-caption mt-1">
          <v-icon size="x-small">mdi-account</v-icon>
          {{ status.actor }}
        </div>
      </div>
    </v-timeline-item>
  </v-timeline>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  rental: {
    type: Object,
    required: true
  }
});

const { t } = useI18n();

const statuses = computed(() => {
  const timeline = [
    {
      value: 'draft',
      label: t('car_rentals.status.draft'),
      description: 'Booking created',
      date: props.rental.created_at,
      completed: true
    },
    {
      value: 'pending',
      label: t('car_rentals.status.pending'),
      description: 'Waiting for verification',
      date: props.rental.created_at,
      completed: ['pending', 'verified', 'confirmed', 'active', 'completed'].includes(props.rental.status.value)
    },
    {
      value: 'verified',
      label: t('car_rentals.status.verified'),
      description: 'Documents verified',
      date: props.rental.verified_at,
      actor: props.rental.verified_by?.name,
      completed: ['verified', 'confirmed', 'active', 'completed'].includes(props.rental.status.value)
    },
    {
      value: 'confirmed',
      label: t('car_rentals.status.confirmed'),
      description: 'Payment received',
      date: props.rental.payment_date,
      completed: ['confirmed', 'active', 'completed'].includes(props.rental.status.value)
    },
    {
      value: 'active',
      label: t('car_rentals.status.active'),
      description: 'Vehicle picked up',
      date: props.rental.actual_pickup_date,
      completed: ['active', 'completed'].includes(props.rental.status.value)
    },
    {
      value: 'completed',
      label: t('car_rentals.status.completed'),
      description: 'Vehicle returned',
      date: props.rental.actual_return_date,
      completed: props.rental.status.value === 'completed'
    }
  ];

  return timeline;
});

const getStatusColor = (status, index) => {
  if (props.rental.status.value === 'cancelled' || props.rental.status.value === 'rejected') {
    return index === 0 ? 'grey' : 'grey-lighten-2';
  }

  if (status.completed) {
    return 'success';
  }

  if (status.value === props.rental.status.value) {
    return 'primary';
  }

  return 'grey-lighten-2';
};

const getStatusIcon = (status) => {
  const icons = {
    draft: 'mdi-file-document-outline',
    pending: 'mdi-clock-outline',
    verified: 'mdi-check-circle',
    confirmed: 'mdi-credit-card-check',
    active: 'mdi-car',
    completed: 'mdi-check-all'
  };

  return icons[status.value] || 'mdi-circle-small';
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.rental-timeline {
  max-width: 600px;
}
</style>

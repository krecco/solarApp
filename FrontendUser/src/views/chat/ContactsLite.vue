<template>
  <component
    :is="tag"
    class="d-flex align-items-center mb-1"
    v-on="$listeners"
  >
    <b-avatar
      size="42"
      :text="user.avatar"
      :badge="isChatContact"
      class="badge-minimal"
      :badge-variant="resolveAvatarBadgeVariant(user.status)"
      :variant="user.avatarBg"
    />
    <div class="chat-info flex-grow-1">
      <h5 class="mb-0 ml-1">
        {{ user.fullName }}
      </h5>
    </div>
  </component>
</template>

<script>
import { BAvatar, BBadge } from 'bootstrap-vue'
import { formatDateToMonthShort } from '@core/utils/filter'
import useChat from './useChat'

export default {
  components: {
    BAvatar,
    BBadge,
  },
  props: {
    tag: {
      type: String,
      default: 'div',
    },
    user: {
      type: Object,
      required: true,
    },
    isChatContact: {
      type: Boolean,
      dedfault: false,
    },
  },
  setup() {
    const { resolveAvatarBadgeVariant } = useChat()
    return { formatDateToMonthShort, resolveAvatarBadgeVariant }
  },
}
</script>

<style>

</style>

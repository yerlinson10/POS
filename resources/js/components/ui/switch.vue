<template>
  <button
    type="button"
    :class="cn(
      'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50',
      checked
        ? 'bg-primary'
        : 'bg-input',
      props.class
    )"
    role="switch"
    :aria-checked="checked"
    :disabled="disabled"
    @click="toggle"
  >
    <span
      :class="cn(
        'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
        checked ? 'translate-x-5' : 'translate-x-0'
      )"
    />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  checked?: boolean
  disabled?: boolean
  class?: string
}

interface Emits {
  (e: 'update:checked', value: boolean): void
}

const props = withDefaults(defineProps<Props>(), {
  checked: false,
  disabled: false
})

const emit = defineEmits<Emits>()

const checked = computed(() => props.checked)

const toggle = () => {
  if (!props.disabled) {
    emit('update:checked', !checked.value)
  }
}
</script>

<template>
  <div :class="cn(badgeVariants({ variant }), ($attrs.class as string) || '')">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { badgeVariants, type BadgeVariants } from './index'
import { cn } from '../../../lib/utils'

interface BadgeProps {
  variant?: BadgeVariants['variant']
  class?: string
}

const props = withDefaults(defineProps<BadgeProps>(), {
  variant: 'default',
})
</script>

<script setup lang="ts">
import { computed } from 'vue'
import { TabsTrigger as TabsTriggerPrimitive, type TabsTriggerProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsTriggerProps {
  class?: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<Props>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})
</script>

<template>
  <TabsTriggerPrimitive
    v-bind="{ ...delegatedProps, ...$attrs }"
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm',
      props.class,
    )"
  >
    <slot />
  </TabsTriggerPrimitive>
</template>

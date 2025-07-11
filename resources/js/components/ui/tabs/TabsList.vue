<script setup lang="ts">
import { computed } from 'vue'
import { TabsList as TabsListPrimitive, type TabsListProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsListProps {
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
  <TabsListPrimitive
    v-bind="{ ...delegatedProps, ...$attrs }"
    :class="cn(
      'inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground',
      props.class,
    )"
  >
    <slot />
  </TabsListPrimitive>
</template>

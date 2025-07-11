<script setup lang="ts">
import { computed } from 'vue'
import { TabsContent as TabsContentPrimitive, type TabsContentProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsContentProps {
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
  <TabsContentPrimitive
    v-bind="{ ...delegatedProps, ...$attrs }"
    :class="cn(
      'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      props.class,
    )"
  >
    <slot />
  </TabsContentPrimitive>
</template>

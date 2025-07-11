<script setup lang="ts">
import { computed } from 'vue'
import { TabsRoot, type TabsRootEmits, type TabsRootProps, useForwardPropsEmits } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsRootProps {
  class?: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<Props>()
const emits = defineEmits<TabsRootEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <TabsRoot
    v-bind="{ ...forwarded, ...$attrs }"
    :class="cn('', props.class)"
  >
    <slot />
  </TabsRoot>
</template>

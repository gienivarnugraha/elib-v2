<template>
  <div>
    <Teleport :to="teleportTarget">
      <Transition name="fade">
        <div v-show="modelValue" ref="refMask"
          class="a-dialog-wrapper grid place-items-end fixed inset-0  bg-[hsla(var(--a-backdrop-c),var(--a-backdrop-opacity))]">
          <Transition name="slide-over">
            <ACard v-show="modelValue" :title="title" :subtitle="subtitle"
              class="a-dialog backface-hidden h-screen transform translate-z-0 max-w-[calc(100vw-2rem)]"
              @keydown.esc="close">
              <!-- ℹ️ Recursively pass down slots to child -->
              <template #header-right>
                <ABtn icon="i-bx-x" variant="light" icon-only color="primary" v-tooltip="'Close'"
                  class="rounded-full text-xs h-8 w-8" @click="close" />
              </template>

              <slot></slot>

            </ACard>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { useTeleport, useDOMScrollLock } from 'anu-vue';
import { onClickSameTarget } from '@/composables/onClickSameTarget'
import { onUnmounted, watch } from 'vue';

const props = defineProps({
  title: String,
  subtitle: String,
  modelValue: Boolean,
  persistent: Boolean,
})

const emits = defineEmits(['update:modelValue', 'close'])

const { teleportTarget } = useTeleport()

const refMask = ref()

const router = useRouter()

onClickSameTarget(refMask, () => {
  // If dialog is open & persistent prop is false => Close dialog
  if (props.modelValue && !props.persistent) {
    close()
  }
})

// Lock DOM scroll when modelValue is `true`
// ℹ️ We need to use type assertion here because of this issue: https://github.com/johnsoncodehk/volar/issues/2219
useDOMScrollLock(toRef(props, 'modelValue'))

const close = () => {
  emits('update:modelValue', false)
  emits('close', true)
  router.back()
}

// const watchShow = watch(() => props.modelValue, (val) => !val && close())

onUnmounted(() => {
  // watchShow()
})

</script>
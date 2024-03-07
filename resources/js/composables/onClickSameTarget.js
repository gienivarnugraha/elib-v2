import { useEventListener } from '@vueuse/core'
export function onClickSameTarget(elRef, handler) {
  let isMouseDownOnTarget = false
  let isMouseUpOnTarget = false

  // refer to this https://javascript.info/mouse-events-basics
  // events fired in the order: mousedown -> mouseup -> click
  const onClick = (e) => {
    if (isMouseDownOnTarget && isMouseUpOnTarget)
      handler(e)

    isMouseDownOnTarget = isMouseUpOnTarget = false
  }

  const onMousedown = (e) => {
    isMouseDownOnTarget = e.target === e.currentTarget
  }
  const onMouseup = (e) => {
    isMouseUpOnTarget = e.target === e.currentTarget
  }

  const cleanup = [
    useEventListener(elRef, 'click', onClick),
    useEventListener(elRef, 'mousedown', onMousedown),
    useEventListener(elRef, 'mouseup', onMouseup),
  ]

  const stop = () => cleanup.forEach(fn => fn())

  return stop
}
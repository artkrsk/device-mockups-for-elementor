import { DATA_ATTRS } from './constants/DATA_ATTRS'
import { SELECTORS } from './constants/SELECTORS'

class GalleryRotator {
  private readonly timers = new Map<Element, number>()

  start(screen: Element, intervalMs: number, loop: boolean): void {
    this.stop(screen)

    const items = screen.querySelectorAll(SELECTORS.GALLERY_ITEM)
    const count = items.length
    if (count === 0) {
      return
    }

    // First frame shows IMMEDIATELY on start (no initial delay); only the SUBSEQUENT
    // frames honour the interval. Index advances 0 → 1 → … (item 0 is never skipped).
    screen.setAttribute(DATA_ATTRS.GALLERY_ACTIVE, '0')

    if (count === 1) {
      return
    }

    let index = 0

    const tick = () => {
      index += 1

      if (index >= count) {
        if (loop) {
          index = 0
        } else {
          // Stay on the last frame: stop ticking but keep the attribute. Clear the timer
          // directly — stop() would remove the attribute and flash the last frame away.
          const timer = this.timers.get(screen)
          if (timer !== undefined && timer > 0) {
            window.clearInterval(timer)
          }
          this.timers.delete(screen)
          return
        }
      }

      screen.setAttribute(DATA_ATTRS.GALLERY_ACTIVE, String(index))
    }

    const id = window.setInterval(tick, intervalMs)
    this.timers.set(screen, id)
  }

  stop(screen: Element): void {
    const timer = this.timers.get(screen)
    if (timer !== undefined) {
      window.clearInterval(timer)
      this.timers.delete(screen)
    }
    // Reset to the static main image/video once rotation stops (hover-out, or out of view).
    screen.removeAttribute(DATA_ATTRS.GALLERY_ACTIVE)
  }

  stopAll(): void {
    for (const screen of this.timers.keys()) {
      this.stop(screen)
    }
  }
}

export const galleryRotator = new GalleryRotator()

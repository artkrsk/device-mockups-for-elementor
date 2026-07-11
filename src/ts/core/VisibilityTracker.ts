import { DEFAULTS } from './constants/DEFAULTS'
import type { IVisibilityCallbacks } from './interfaces/IVisibilityCallbacks'

class VisibilityTracker {
  private readonly observer: IntersectionObserver
  private readonly callbacks = new Map<Element, IVisibilityCallbacks>()

  constructor() {
    this.observer = new IntersectionObserver(this.handleIntersect.bind(this), {
      threshold: DEFAULTS.VISIBILITY_THRESHOLD
    })
  }

  observe(el: Element, onEnter: () => void, onLeave: () => void): void {
    this.callbacks.set(el, { onEnter, onLeave })
    this.observer.observe(el)
  }

  unobserve(el: Element): void {
    this.callbacks.delete(el)
    this.observer.unobserve(el)
  }

  private handleIntersect(entries: IntersectionObserverEntry[]): void {
    entries.forEach((entry) => {
      const cbs = this.callbacks.get(entry.target)
      if (!cbs) {
        return
      }
      if (entry.isIntersecting) {
        cbs.onEnter()
      } else {
        cbs.onLeave()
      }
    })
  }
}

export const visibilityTracker = new VisibilityTracker()

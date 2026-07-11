import { SELECTORS } from './constants/SELECTORS'
import type { IMockupConfig } from './interfaces/IMockupConfig'
import { MockupInstance } from './MockupInstance'

export class MockupManager {
  private readonly instances = new Map<Element, MockupInstance>()

  init(root: ParentNode = document, opts?: IMockupConfig): MockupInstance[] {
    const roots = Array.from(root.querySelectorAll<Element>(SELECTORS.ROOT))
    const created: MockupInstance[] = []
    for (const el of roots) {
      if (this.instances.has(el)) {
        continue
      }
      const instance = new MockupInstance(el, opts)
      this.instances.set(el, instance)
      created.push(instance)
    }
    return created
  }

  refresh(root: ParentNode = document, opts?: IMockupConfig): MockupInstance[] {
    return this.init(root, opts)
  }

  destroy(root: ParentNode): void {
    this.instances.forEach((instance, el) => {
      if ((root as Element).contains(el)) {
        instance.destroy()
        this.instances.delete(el)
      }
    })
  }
}

export const mockupManager = new MockupManager()

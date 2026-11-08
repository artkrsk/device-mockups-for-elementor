import type { IMockupConfig } from './interfaces/IMockupConfig'
import type { MockupInstance } from './MockupInstance'
import { mockupManager } from './MockupManager'
import type { TElementorBaseConstructor } from './types/TElementorBaseConstructor'

/**
 * Builds the widget handler on top of Elementor's handler base. Called from the
 * 'elementor/frontend/init' listener, where the base is guaranteed to exist — resolving it at
 * module evaluation would depend on our script running after Elementor's.
 */
export function createMockupHandler(Base: TElementorBaseConstructor) {
  return class MockupHandler extends Base {
    private instances: MockupInstance[] = []

    onInit(...args: unknown[]): void {
      super.onInit(...args)
      const opts: IMockupConfig = {
        editMode: window.elementorFrontend?.isEditMode?.() ?? false
      }
      const el = this.$element?.[0]
      if (!el) {
        return
      }
      this.instances = mockupManager.init(el, opts)
    }

    onDestroy(): void {
      super.onDestroy()
      for (const i of this.instances) {
        i.destroy()
      }
      this.instances = []
    }
  }
}

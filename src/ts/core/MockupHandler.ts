import type { IMockupConfig } from './interfaces/IMockupConfig'
import type { MockupInstance } from './MockupInstance'
import { mockupManager } from './MockupManager'
import type { TElementorBaseConstructor } from './types/TElementorBaseConstructor'

// window.elementorModules is set by Elementor before 'elementor/frontend/init' fires.
// The `?? Object` fallback lets the class define cleanly when the module is evaluated
// on non-Elementor pages — MockupHandler is never instantiated in that case.
const ElementorBase = (window.elementorModules?.frontend?.handlers?.Base ??
  Object) as unknown as TElementorBaseConstructor

export class MockupHandler extends ElementorBase {
  private instances: MockupInstance[] = []

  onInit(...args: unknown[]): void {
    super.onInit(...args)
    const opts: IMockupConfig = {
      editMode: window.elementorFrontend?.isEditMode?.() ?? false
    }
    this.instances = mockupManager.init(this.$element[0] as Element, opts)
  }

  onDestroy(): void {
    super.onDestroy()
    for (const i of this.instances) {
      i.destroy()
    }
    this.instances = []
  }
}

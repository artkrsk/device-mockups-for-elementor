import { MOCKUP_SKINS } from './core/constants/MOCKUP_SKINS'
import { MockupHandler } from './core/MockupHandler'
import { mockupManager } from './core/MockupManager'

// Init the whole document on load — REGARDLESS of Elementor. Mockups can arrive outside the
// per-widget Elementor handler below (theme template overrides, AJAX-injected markup), so gating
// this on "Elementor absent" would leave them dead on any Elementor page. init() is idempotent
// (already-tracked roots are skipped), so the handler re-attaching to an Elementor-widget
// mockup is a harmless no-op.
function initDocument(): void {
  mockupManager.init(document)
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDocument)
} else {
  initDocument()
}

// Elementor frontend + editor preview: attach per skin (editMode opts + lifecycle for widget mockups)
window.addEventListener('elementor/frontend/init', () => {
  MOCKUP_SKINS.forEach((skin) => {
    window.elementorFrontend?.elementsHandler.attachHandler(
      'arts-device-mockup',
      MockupHandler,
      skin
    )
  })
})

// Expose globally for AJAX consumers (infinite scroll, page transitions)
window.artsDeviceMockups = mockupManager

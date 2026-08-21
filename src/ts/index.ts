import { MOCKUP_SKINS } from './core/constants/MOCKUP_SKINS'
import { MockupHandler } from './core/MockupHandler'
import { mockupManager } from './core/MockupManager'

// Init the whole document on load — REGARDLESS of Elementor. Mockups can arrive outside the
// per-widget Elementor handler below (theme template overrides, AJAX-injected markup), so gating
// this on "Elementor absent" would leave them dead on any Elementor page. init() is idempotent
// (already-tracked roots are skipped), so the handler re-attaching creates nothing — and this
// pass always wins that race, because elementorFrontend.init() itself runs after DOMContentLoaded.
// A widget in the initial markup is therefore owned by THIS pass, and the handler's editMode opt
// only reaches widgets Elementor re-renders afterwards.
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

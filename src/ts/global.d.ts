import type {
  ElementorEditor,
  ElementorFrontend,
  ElementorModules
} from '@artemsemkin/elementor-types'
import type { IVimeoNamespace } from './core/interfaces/IVimeoNamespace'
import type { IYTNamespace } from './core/interfaces/IYTNamespace'
import type { MockupManager } from './core/MockupManager'

declare global {
  interface Window {
    elementorFrontend?: ElementorFrontend
    elementorModules?: ElementorModules
    elementor?: ElementorEditor
    artsDeviceMockups?: MockupManager
    // External video SDKs, loaded on demand by the YouTube / Vimeo players.
    YT?: IYTNamespace
    onYouTubeIframeAPIReady?: () => void
    Vimeo?: IVimeoNamespace
  }
}

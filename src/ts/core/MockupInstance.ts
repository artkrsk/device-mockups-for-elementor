import { DATA_ATTRS } from './constants/DATA_ATTRS'
import { DEFAULTS } from './constants/DEFAULTS'
import { SELECTORS } from './constants/SELECTORS'
import { galleryRotator } from './GalleryRotator'
import type { IMockupConfig } from './interfaces/IMockupConfig'
import type { IVideoPlayer } from './interfaces/IVideoPlayer'
import type { TGalleryTrigger } from './types/TGalleryTrigger'
import type { TVideoType } from './types/TVideoType'
import { visibilityTracker } from './VisibilityTracker'
import { HostedPlayer } from './video/HostedPlayer'
import { VimeoPlayer } from './video/VimeoPlayer'
import { YoutubePlayer } from './video/YoutubePlayer'

export class MockupInstance {
  private readonly root: Element
  private readonly screen: Element | null
  private player: IVideoPlayer | null = null
  private readonly editMode: boolean
  private readonly playOnHover: boolean
  private readonly galleryTrigger: TGalleryTrigger
  private readonly galleryIntervalMs: number
  private readonly galleryLoop: boolean
  private readonly hasGallery: boolean

  // Live state — the video plays only when it is the visible frame (no gallery item covering it)
  // AND its trigger condition is met (in viewport, or hovering when play-on-hover).
  private inView = false
  private hovering = false
  private galleryActive = false

  constructor(root: Element, opts?: IMockupConfig) {
    this.root = root
    this.screen = root.querySelector(SELECTORS.SCREEN)
    this.editMode = opts?.editMode ?? false
    this.playOnHover = root.hasAttribute(DATA_ATTRS.PLAY_ON_HOVER)
    this.galleryTrigger =
      (root.getAttribute(DATA_ATTRS.GALLERY_TRIGGER) as TGalleryTrigger | null) ??
      DEFAULTS.GALLERY_TRIGGER
    this.galleryIntervalMs =
      parseInt(root.getAttribute(DATA_ATTRS.GALLERY_INTERVAL) ?? '', 10) ||
      DEFAULTS.GALLERY_INTERVAL
    this.galleryLoop = root.hasAttribute(DATA_ATTRS.GALLERY_LOOP)
    this.hasGallery = !!this.screen?.querySelector(SELECTORS.GALLERY_ITEM)

    this.initVideo()
    this.initVisibility()
    this.initHover()
  }

  destroy(): void {
    visibilityTracker.unobserve(this.root)
    if (this.screen) {
      galleryRotator.stop(this.screen)
    }
    this.player?.destroy()
    this.player = null
    this.root.removeEventListener('mouseenter', this.onMouseEnter)
    this.root.removeEventListener('mouseleave', this.onMouseLeave)
  }

  private initVideo(): void {
    const videoType = this.root.getAttribute(DATA_ATTRS.VIDEO) as TVideoType | null
    if (!videoType || videoType === 'none') {
      return
    }

    const videoUrl = this.root.getAttribute(DATA_ATTRS.VIDEO_URL) ?? ''

    if (videoType === 'hosted') {
      const videoEl = this.root.querySelector<HTMLVideoElement>(SELECTORS.VIDEO)
      if (videoEl) {
        this.player = new HostedPlayer(videoEl)
      }
    } else if (videoType === 'youtube') {
      if (this.screen instanceof HTMLElement) {
        this.player = new YoutubePlayer(this.screen, videoUrl)
      }
    } else if (videoType === 'vimeo') {
      if (this.screen instanceof HTMLElement) {
        this.player = new VimeoPlayer(this.screen, videoUrl)
      }
    }
  }

  /** The video plays only when it is the visible media (no gallery covering it) and its trigger fires. */
  private syncVideo(): void {
    if (!this.player || this.editMode) {
      return
    }

    const wantsToPlay = this.playOnHover ? this.hovering : this.inView

    if (wantsToPlay && !this.galleryActive) {
      this.player.play()
    } else {
      this.player.pause()
    }
  }

  /** Start the rotating gallery and pause the now-covered video. */
  private startGallery(): void {
    if (!this.hasGallery || !this.screen) {
      return
    }
    this.galleryActive = true
    galleryRotator.start(this.screen, this.galleryIntervalMs, this.galleryLoop)
    this.syncVideo()
  }

  /** Stop the gallery (uncovering the video) and resume the video where appropriate. */
  private stopGallery(): void {
    if (!this.screen) {
      return
    }
    this.galleryActive = false
    galleryRotator.stop(this.screen)
    this.syncVideo()
  }

  /** Observe the viewport for in-view video autoplay and auto-triggered gallery rotation. */
  private initVisibility(): void {
    const trackGallery = this.hasGallery && this.galleryTrigger === 'auto'
    const needsObserver = (this.player !== null || trackGallery) && !this.editMode

    if (!needsObserver) {
      return
    }

    visibilityTracker.observe(
      this.root,
      () => {
        this.inView = true
        if (trackGallery) {
          this.startGallery()
        }
        this.syncVideo()
      },
      () => {
        this.inView = false
        if (trackGallery) {
          this.stopGallery()
        }
        this.syncVideo()
      }
    )
  }

  private initHover(): void {
    const needsHover = this.playOnHover || (this.hasGallery && this.galleryTrigger === 'hover')
    if (!needsHover) {
      return
    }

    this.root.addEventListener('mouseenter', this.onMouseEnter)
    this.root.addEventListener('mouseleave', this.onMouseLeave)
  }

  private readonly onMouseEnter = (): void => {
    this.hovering = true
    if (this.hasGallery && this.galleryTrigger === 'hover') {
      this.startGallery()
    }
    this.syncVideo()
  }

  // Typed as EventListener (accepts Event) so it can be passed to removeEventListener on Element.
  // relatedTarget is reached by asserting the Event to MouseEvent (no narrowing possible).
  private readonly onMouseLeave = (e: Event): void => {
    const mouse = e as MouseEvent
    if (this.root.contains(mouse.relatedTarget as Node | null)) {
      return
    }
    this.hovering = false
    if (this.hasGallery && this.galleryTrigger === 'hover') {
      this.stopGallery()
    }
    this.syncVideo()
  }
}

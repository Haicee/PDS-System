let faceApiLoader = null

import FaceService from './FaceServices'

// Expose globally so Alpine x-data can find it without timing issues
window.formCache = function () {
    return {
        preview: null,
        stream: null,
        streaming: false,
        submitting: false,
        face: null,
        detectionState: 'idle', // idle | searching | no_face | dark | ready | captured
        detectionMessage: null,
        detectTimer: null,
        autoCaptureScheduled: false,
        brightnessThreshold: 120,
        detectionProgress: 0,
        readyTicks: 0,
        requiredReadyTicks: 7,
        minDetectionScore: 0.90,

        async init() {
            const form = document.querySelector('form')
            const saved = JSON.parse(localStorage.getItem('register_cache') || '{}')

            // Wait for face-api global to exist (loaded via CDN in Blade)
            await this.waitForFaceApi()

            // Restore cached text/select values
            Object.entries(saved).forEach(([name, value]) => {
                if (name === 'profile_photo_base64') return
                const input = form.querySelector(`[name="${name}"]`)
                const isSelect = input?.tagName === 'SELECT'
                if (input && input.type !== 'file' && input.type !== 'password' && (!input.value || isSelect)) {
                    input.value = value
                    input.dispatchEvent(new Event('input'))
                }
            })

            // Restore cached photo
            this.restorePhoto(saved.profile_photo_base64, form)

            // Cache normal inputs on change
            form.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.type === 'file' || input.type === 'password') return
                input.addEventListener('input', () => {
                    const cache = JSON.parse(localStorage.getItem('register_cache') || '{}')
                    cache[input.name] = input.value
                    localStorage.setItem('register_cache', JSON.stringify(cache))
                })
            })

            // Init face service
            const video = this.$refs.video
            if (!video) return
            this.face = new FaceService(video, { modelPath: '/models', brightnessThreshold: 80 })
            await this.face.loadModels()
        },

        async waitForFaceApi(retries = 50) {
            if (window.faceapi) return
            if (!faceApiLoader) {
                faceApiLoader = new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[data-faceapi-cdn]')
                    if (existing) {
                        existing.addEventListener('load', () => resolve(window.faceapi))
                        existing.addEventListener('error', () => reject(new Error('faceapi cdn failed')))
                        return
                    }
                    const s = document.createElement('script')
                    s.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js'
                    s.async = true
                    s.dataset.faceapiCdn = 'true'
                    s.onload = () => resolve(window.faceapi)
                    s.onerror = () => reject(new Error('faceapi cdn failed'))
                    document.head.appendChild(s)
                })
            }
            await faceApiLoader
            if (!window.faceapi) {
                throw new Error('faceapi not loaded')
            }
        },

        restorePhoto(base64, form) {
            if (!base64) return
            this.preview = base64
            const dt = new DataTransfer()
            const byteString = atob(base64.split(',')[1])
            const ab = new ArrayBuffer(byteString.length)
            const ia = new Uint8Array(ab)
            for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i)
            const blob = new Blob([ab], { type: 'image/jpeg' })
            const file = new File([blob], 'profile_photo.jpg', { type: 'image/jpeg' })
            dt.items.add(file)
            const uploadInput = form.querySelector("[name='profile_photo']")
            if (uploadInput) uploadInput.files = dt.files
        },

        handlePhoto(event) {
            const file = event.target.files[0]
            if (!file) return
            this.cachePhoto(file)
        },

        cachePhoto(file) {
            const reader = new FileReader()
            reader.onload = (e) => {
                this.preview = e.target.result
                const cache = JSON.parse(localStorage.getItem('register_cache') || '{}')
                cache.profile_photo_base64 = e.target.result
                localStorage.setItem('register_cache', JSON.stringify(cache))
            }
            reader.readAsDataURL(file)
        },

        async startCamera() {
            if (!this.face) return
            try {
                await this.face.startCamera()
                this.stream = this.face.stream
                this.streaming = this.face.streaming
                this.detectionState = 'searching'
                this.detectionMessage = 'Center your face in the circle.'
                this.detectionProgress = 0
                this.startDetectionLoop()
            } catch (e) {
                console.error(e)
                this.streaming = false
            }
        },

        chooseUpload() {
            this.stopCamera()
            this.$refs.uploadInput?.click()
        },

        async captureFrame() {
            const eligibility = await this.checkEligibility()
            if (!eligibility) return
            const blob = await this.face.captureIfValid()
            if (!blob) return
            const file = new File([blob], 'profile_photo.jpg', { type: 'image/jpeg' })
            const dt = new DataTransfer()
            dt.items.add(file)
            if (this.$refs.uploadInput) this.$refs.uploadInput.files = dt.files
            this.cachePhoto(file)
            this.stopCamera()
            this.detectionState = 'captured'
            this.detectionMessage = null
            this.detectionProgress = 0
            this.autoCaptureScheduled = false
        },

        startDetectionLoop() {
            this.stopDetectionLoop()
            this.detectTimer = setInterval(() => this.runDetectionTick(), 600)
        },

        stopDetectionLoop() {
            if (this.detectTimer) {
                clearInterval(this.detectTimer)
                this.detectTimer = null
            }
            this.autoCaptureScheduled = false
            this.detectionProgress = 0
            this.readyTicks = 0
        },

        async runDetectionTick() {
            if (!this.streaming || !this.face) return
            const eligible = await this.checkEligibility(true)
            if (!eligible) return

            this.detectionState = 'ready'
            this.detectionMessage = 'Hold still… capturing in a moment.'
            // incorporate brightness headroom into progress
            const brightnessOk = this.brightnessOk === true
            const lightFactor = brightnessOk ? 1 : 0
            const step = brightnessOk ? 20 : 10
            this.detectionProgress = Math.min(100, this.detectionProgress + step)
            this.readyTicks += 1

            if (this.detectionProgress >= 100 && this.readyTicks >= this.requiredReadyTicks && brightnessOk && !this.autoCaptureScheduled) {
                this.autoCaptureScheduled = true
                this.captureFrame()
            }
        },

        async checkEligibility(fromTick = false) {
            const detection = await this.face.detectFace()
            if (!detection) {
                this.detectionState = 'no_face'
                this.detectionMessage = 'No face detected. Please center your face.'
                this.autoCaptureScheduled = false
                this.detectionProgress = Math.max(0, this.detectionProgress - 8)
                this.readyTicks = 0
                return false
            }

            if (detection.score && detection.score < this.minDetectionScore) {
                this.detectionState = 'no_face'
                this.detectionMessage = 'Face not clear. Hold steady.'
                this.autoCaptureScheduled = false
                this.detectionProgress = Math.max(0, this.detectionProgress - 2)
                this.readyTicks = Math.max(0, this.readyTicks - 1)
                return false
            }

            const brightness = this.face.checkLighting()
            if (brightness < this.brightnessThreshold) {
                this.detectionState = 'dark'
                this.detectionMessage = 'Lighting too low. Move to a brighter spot.'
                this.autoCaptureScheduled = false
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                this.readyTicks = 0
                return false
            }

            const brightnessOk = brightness >= this.brightnessThreshold + 3

            const { box } = detection
            const video = this.face.video
            const ratioW = box.width / (video?.videoWidth || 1)
            const ratioH = box.height / (video?.videoHeight || 1)
            const faceRatio = Math.max(ratioW, ratioH)
            const minRatio = 0.40
            const maxRatio = 0.60

            if (faceRatio < minRatio) {
                this.detectionState = 'too_far'
                this.detectionMessage = 'Move closer so your face fills the circle.'
                this.autoCaptureScheduled = false
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                this.readyTicks = 0
                return false
            }

            if (faceRatio > maxRatio) {
                this.detectionState = 'too_close'
                this.detectionMessage = 'Move back slightly so your face fits the circle.'
                this.autoCaptureScheduled = false
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                this.readyTicks = 0
                return false
            }

            const faceCenterX = (box.x + box.width / 2) / (video?.videoWidth || 1)
            const faceCenterY = (box.y + box.height / 2) / (video?.videoHeight || 1)
            const dx = Math.abs(faceCenterX - 0.5)
            const dy = Math.abs(faceCenterY - 0.5)
            const dyWeight = 1.15 // slightly tighter vertical tolerance without biasing upward
            // Tie centering to the visible circle: face center plus its radius must remain within the circle (with a tighter margin)
            const faceRadius = faceRatio / 2
            const circleRadius = 0.5
            const margin = 0.04
            const maxCenterDistance = Math.max(0.005, circleRadius - margin - faceRadius)
            const centerDistance = Math.hypot(dx, dy * dyWeight)
            if (centerDistance > maxCenterDistance) {
                this.detectionState = 'off_center'
                this.detectionMessage = 'Center your face in the guide.'
                this.autoCaptureScheduled = false
                this.detectionProgress = 0
                this.readyTicks = 0
                return false
            }

            // If near the edge, slow progress accumulation
            if (centerDistance > maxCenterDistance * 0.6) {
                this.detectionProgress = Math.max(0, this.detectionProgress - 5)
                this.readyTicks = Math.max(0, this.readyTicks - 1)
                return false
            }

            // update shared brightnessOk if called from tick
            if (fromTick) {
                this.brightnessOk = brightnessOk
            }
            return brightnessOk
        },

        stopCamera() {
            this.face?.stopCamera()
            this.stream = null
            this.streaming = false
            this.stopDetectionLoop()
            this.detectionState = 'idle'
            this.detectionMessage = null
            this.detectionProgress = 0
        },

        clear() {
            this.preview = null
            const uploadInput = this.$refs.uploadInput
            if (uploadInput) uploadInput.value = ''
            this.stopCamera()
            const cache = JSON.parse(localStorage.getItem('register_cache') || '{}')
            delete cache.profile_photo_base64
            localStorage.setItem('register_cache', JSON.stringify(cache))
        }
    }
}

// Ensure Alpine picks it up whether it starts before or after this script
if (window.Alpine) {
    window.Alpine.data('formCache', window.formCache)
}
document.addEventListener('alpine:init', () => {
    window.Alpine?.data('formCache', window.formCache)
})

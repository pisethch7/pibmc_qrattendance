<template>
  <div class="max-w-xl mx-auto px-4 sm:px-6 py-6 space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 shadow-lg shadow-emerald-500/25">
        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
        </svg>
      </div>
      <h1 class="text-2xl font-extrabold text-white font-['Outfit']">
        Classroom Check-In
      </h1>
      <p class="text-xs text-slate-400">
        Scan the classroom screen QR code to verify your attendance
      </p>
    </div>

    <!-- GPS Status & Geolocation Badge -->
    <div class="glass-panel p-4 rounded-2xl border border-slate-800 space-y-3">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider flex items-center space-x-2">
          <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Device Geolocation</span>
        </span>

        <span
          class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
          :class="gpsLocked ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30'"
        >
          {{ gpsLocked ? 'GPS Ready' : 'Acquiring GPS...' }}
        </span>
      </div>

      <div class="flex items-center justify-between text-xs text-slate-400">
        <div v-if="latitude && longitude" class="font-mono">
          {{ latitude.toFixed(5) }}°, {{ longitude.toFixed(5) }}°
          <span v-if="accuracy" class="text-[10px] text-slate-500">(±{{ Math.round(accuracy) }}m)</span>
        </div>
        <div v-else class="text-slate-500 italic">
          Waiting for location permission...
        </div>

        <button
          type="button"
          @click="requestLocation"
          class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold"
        >
          Refresh GPS
        </button>
      </div>

      <!-- Dev Coordinates Simulator Toggle -->
      <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
        <span class="text-slate-500">Testing on Desktop or Remote?</span>
        <button
          type="button"
          @click="simulateCampusCoordinates"
          class="px-2 py-1 rounded bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/20 font-medium transition-colors"
        >
          Set Classroom Coordinates (Phnom Penh)
        </button>
      </div>
    </div>

    <!-- Success Result Card -->
    <div
      v-if="checkInResult && checkInResult.success"
      class="p-6 rounded-2xl bg-emerald-950/40 border border-emerald-500/50 glow-emerald text-center space-y-4 animate-in fade-in zoom-in duration-300"
    >
      <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <div>
        <span
          class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-block mb-2"
          :class="checkInResult.record.status === 'present' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'"
        >
          Marked {{ checkInResult.record.status }}
        </span>
        <h3 class="text-xl font-bold text-white font-['Outfit']">
          {{ checkInResult.record.session?.course?.name || 'Class Attendance Confirmed' }}
        </h3>
        <p class="text-xs text-slate-300 mt-1">
          Checked in at {{ formatTime(checkInResult.record.checked_in_at) }}
        </p>
      </div>

      <div class="pt-2">
        <router-link
          to="/student"
          class="inline-block px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-600/30"
        >
          Back to My Dashboard
        </router-link>
      </div>
    </div>

    <!-- Error Result Card -->
    <div
      v-if="errorMessage"
      class="p-5 rounded-2xl bg-rose-950/40 border border-rose-500/40 glow-rose space-y-3 animate-in fade-in duration-300"
    >
      <div class="flex items-start space-x-3">
        <div class="p-2 rounded-xl bg-rose-500/20 text-rose-400 flex-shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="text-sm font-bold text-rose-200">Check-In Failed</h4>
          <p class="text-xs text-rose-300 mt-1">{{ errorMessage }}</p>
        </div>
      </div>
      <div class="pt-2 text-right">
        <button
          @click="resetScanner"
          class="px-3.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold"
        >
          Try Again
        </button>
      </div>
    </div>

    <!-- Scanner & Token Input Area -->
    <div v-if="!checkInResult" class="glass-panel p-6 rounded-3xl border border-slate-800 space-y-6">
      <!-- Hidden file decoder container for Html5Qrcode -->
      <div id="qr-file-decoder" style="display: none;"></div>

      <!-- Scan Mode Selector Tabs (Camera vs Import QR Image) -->
      <div class="grid grid-cols-2 gap-2 p-1 rounded-2xl bg-slate-900 border border-slate-800">
        <button
          type="button"
          @click="switchScanTab('camera')"
          class="flex items-center justify-center space-x-2 py-2.5 px-3 rounded-xl text-xs font-semibold transition-all"
          :class="activeScanTab === 'camera'
            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30'
            : 'text-slate-400 hover:text-white'"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          </svg>
          <span>Live Camera</span>
        </button>

        <button
          type="button"
          @click="switchScanTab('import')"
          class="flex items-center justify-center space-x-2 py-2.5 px-3 rounded-xl text-xs font-semibold transition-all"
          :class="activeScanTab === 'import'
            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30'
            : 'text-slate-400 hover:text-white'"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span>Import QR Image</span>
        </button>
      </div>

      <!-- TAB 1: Camera Scanner Container -->
      <div v-show="activeScanTab === 'camera'" class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">
            Live Camera Scanner
          </span>
          <button
            v-if="cameraRunning"
            @click="stopCamera"
            class="text-xs text-rose-400 hover:text-rose-300 font-semibold"
          >
            Stop Camera
          </button>
          <button
            v-else
            @click="startCamera"
            class="text-xs text-emerald-400 hover:text-emerald-300 flex items-center space-x-1 font-semibold"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
            <span>Start Camera</span>
          </button>
        </div>

        <div class="relative w-full rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 min-h-[260px] flex items-center justify-center">
          <div id="qr-reader" class="w-full"></div>

          <!-- Camera Placeholder when stopped -->
          <div v-if="!cameraRunning" class="text-center p-6 space-y-3">
            <div class="w-12 h-12 mx-auto rounded-xl bg-slate-800 flex items-center justify-center text-slate-400">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
            </div>
            <p class="text-xs text-slate-400 max-w-xs">
              Point your camera at the screen's rotating QR code
            </p>
            <button
              type="button"
              @click="startCamera"
              class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-md shadow-emerald-600/25"
            >
              Enable Camera Scanner
            </button>
          </div>
        </div>
      </div>

      <!-- TAB 2: Import QR Image Container -->
      <div v-show="activeScanTab === 'import'" class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">
            Import QR Code Image
          </span>
          <span class="text-[11px] text-slate-400">JPG, PNG, WEBP</span>
        </div>

        <!-- Hidden File Input -->
        <input
          type="file"
          ref="qrFileInput"
          accept="image/*"
          @change="handleQrImageUpload"
          class="hidden"
        />

        <!-- Dropzone / Click to select image -->
        <div
          @click="triggerFileInput"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleFileDrop"
          class="relative p-8 rounded-2xl border-2 border-dashed cursor-pointer text-center transition-all flex flex-col items-center justify-center space-y-3 min-h-[220px]"
          :class="isDragging
            ? 'border-emerald-500 bg-emerald-500/10'
            : 'border-slate-800 hover:border-slate-700 bg-slate-900/60 hover:bg-slate-900/90'"
        >
          <!-- Upload Status Loading -->
          <div v-if="importingImage" class="space-y-3">
            <svg class="animate-spin w-8 h-8 text-emerald-400 mx-auto" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-xs font-semibold text-emerald-300">Decoding QR Code from image...</p>
          </div>

          <!-- Preview & Result if loaded -->
          <div v-else-if="importedImagePreview" class="space-y-2">
            <img :src="importedImagePreview" alt="Imported QR" class="w-28 h-28 object-contain rounded-xl mx-auto border border-slate-700" />
            <p class="text-xs font-semibold text-emerald-400">QR Code Image Loaded</p>
            <button
              type="button"
              @click.stop="triggerFileInput"
              class="text-[11px] text-slate-400 hover:text-white underline"
            >
              Choose different image
            </button>
          </div>

          <!-- Default Empty State -->
          <div v-else class="space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-bold text-white">Click or drag & drop QR image</p>
              <p class="text-xs text-slate-400 mt-1">Upload teacher exported JPG or saved screenshot</p>
            </div>
            <button
              type="button"
              class="mt-2 px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold shadow"
            >
              Browse Image
            </button>
          </div>
        </div>

        <div v-if="importSuccessMessage" class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center space-x-2">
          <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ importSuccessMessage }}</span>
        </div>
      </div>

      <!-- Divider -->
      <div class="relative flex items-center justify-center">
        <div class="border-t border-slate-800 w-full"></div>
        <span class="bg-slate-950 px-3 text-[11px] uppercase tracking-wider font-semibold text-slate-500 absolute">
          or submit token manually
        </span>
      </div>

      <!-- Manual Token Form -->
      <form @submit.prevent="submitCheckIn" class="space-y-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
            Attendance Token
          </label>
          <input
            v-model="token"
            type="text"
            required
            placeholder="Scanned token will appear here"
            class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
          />
        </div>

        <button
          type="submit"
          :disabled="submitting || !token"
          class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-sm shadow-lg shadow-emerald-600/25 disabled:opacity-50 transition-all flex items-center justify-center space-x-2"
        >
          <svg v-if="submitting" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ submitting ? 'Verifying Coordinates & Token...' : 'Confirm Attendance' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { Html5Qrcode } from 'html5-qrcode';
import jsQR from 'jsqr';
import api from '../../services/api';

const route = useRoute();

const token = ref('');
const latitude = ref(null);
const longitude = ref(null);
const accuracy = ref(null);
const gpsLocked = ref(false);

const activeScanTab = ref('camera'); // 'camera' or 'import'
const qrFileInput = ref(null);
const isDragging = ref(false);
const importingImage = ref(false);
const importedImagePreview = ref('');
const importSuccessMessage = ref('');

const cameraRunning = ref(false);
let html5QrCode = null;

const submitting = ref(false);
const checkInResult = ref(null);
const errorMessage = ref('');

const switchScanTab = (tab) => {
  activeScanTab.value = tab;
  errorMessage.value = '';
  if (tab === 'import' && cameraRunning.value) {
    stopCamera();
  }
};

const triggerFileInput = () => {
  qrFileInput.value?.click();
};

const handleFileDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (file) {
    processQrImageFile(file);
  }
};

const handleQrImageUpload = (e) => {
  const file = e.target?.files?.[0];
  if (file) {
    processQrImageFile(file);
  }
};

// Robust multi-pass QR decoder using BarcodeDetector, jsQR, multi-scale & crop passes
const decodeQrFromImageFile = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = async () => {
        try {
          // Pass 1: Browser Native BarcodeDetector if available (instant & hardware-accelerated)
          if ('BarcodeDetector' in window) {
            try {
              const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
              const barcodes = await detector.detect(img);
              if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                return resolve(barcodes[0].rawValue);
              }
            } catch (detectorErr) {
              console.warn('BarcodeDetector pass failed, falling back to jsQR:', detectorErr);
            }
          }

          const originalWidth = img.naturalWidth || img.width;
          const originalHeight = img.naturalHeight || img.height;

          // Helper to test jsQR on canvas
          const tryJsQR = (testCanvas) => {
            const ctx = testCanvas.getContext('2d', { willReadFrequently: true });
            const imgData = ctx.getImageData(0, 0, testCanvas.width, testCanvas.height);
            const res = jsQR(imgData.data, imgData.width, imgData.height, {
              inversionAttempts: 'attemptBoth',
            });
            return res ? res.data : null;
          };

          // Pass 2: jsQR at natural resolution
          const baseCanvas = document.createElement('canvas');
          baseCanvas.width = originalWidth;
          baseCanvas.height = originalHeight;
          const baseCtx = baseCanvas.getContext('2d', { willReadFrequently: true });
          baseCtx.drawImage(img, 0, 0);

          let decoded = tryJsQR(baseCanvas);
          if (decoded) return resolve(decoded);

          // Pass 3: Multi-scale downscaling (handles high-resolution 12MP/4K camera photos)
          const targetDimensions = [1600, 1200, 800, 500];
          for (const maxDim of targetDimensions) {
            if (originalWidth > maxDim || originalHeight > maxDim) {
              const scale = Math.min(maxDim / originalWidth, maxDim / originalHeight);
              const scaledW = Math.round(originalWidth * scale);
              const scaledH = Math.round(originalHeight * scale);

              const scaledCanvas = document.createElement('canvas');
              scaledCanvas.width = scaledW;
              scaledCanvas.height = scaledH;
              const sCtx = scaledCanvas.getContext('2d', { willReadFrequently: true });
              sCtx.drawImage(img, 0, 0, scaledW, scaledH);

              decoded = tryJsQR(scaledCanvas);
              if (decoded) return resolve(decoded);
            }
          }

          // Pass 4: Crop central regions (handles sheets/cards where QR is centered with borders/headers)
          const cropRatios = [0.85, 0.7, 0.55];
          for (const ratio of cropRatios) {
            const cropW = Math.round(originalWidth * ratio);
            const cropH = Math.round(originalHeight * ratio);
            const cropX = Math.round((originalWidth - cropW) / 2);
            const cropY = Math.round((originalHeight - cropH) / 2);

            const cropCanvas = document.createElement('canvas');
            cropCanvas.width = cropW;
            cropCanvas.height = cropH;
            const cCtx = cropCanvas.getContext('2d', { willReadFrequently: true });
            cCtx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

            decoded = tryJsQR(cropCanvas);
            if (decoded) return resolve(decoded);
          }

          // Pass 5: Fallback to Html5Qrcode.scanFile if DOM element exists
          try {
            const elem = document.getElementById('qr-file-decoder');
            if (elem) {
              const html5Scanner = new Html5Qrcode('qr-file-decoder');
              const html5Result = await html5Scanner.scanFile(file, false);
              html5Scanner.clear();
              if (html5Result) return resolve(html5Result);
            }
          } catch (html5Err) {
            console.warn('Html5Qrcode scanFile fallback failed:', html5Err);
          }

          reject(new Error('No QR code could be found in this image.'));
        } catch (err) {
          reject(err);
        }
      };
      img.onerror = () => reject(new Error('Failed to load image file.'));
      img.src = e.target.result;
    };
    reader.onerror = () => reject(new Error('Failed to read image file.'));
    reader.readAsDataURL(file);
  });
};

const processQrImageFile = async (file) => {
  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'Please select a valid image file (JPG, PNG, WEBP).';
    return;
  }

  importingImage.value = true;
  errorMessage.value = '';
  importSuccessMessage.value = '';

  if (importedImagePreview.value) {
    URL.revokeObjectURL(importedImagePreview.value);
  }
  importedImagePreview.value = URL.createObjectURL(file);

  try {
    if (cameraRunning.value) {
      await stopCamera();
    }

    const decodedText = await decodeQrFromImageFile(file);
    const foundToken = extractToken(decodedText);

    if (foundToken) {
      token.value = foundToken;
      importSuccessMessage.value = 'QR code detected successfully! Submitting check-in...';
      await submitCheckIn();
    } else {
      errorMessage.value = 'Could not read attendance token from this QR code.';
    }
  } catch (err) {
    console.error('File scan error:', err);
    errorMessage.value = 'No QR code could be detected in this image. Please ensure the QR code is clear and well-lit.';
  } finally {
    importingImage.value = false;
    if (qrFileInput.value) {
      qrFileInput.value.value = '';
    }
  }
};

const requestLocation = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        latitude.value = parseFloat(pos.coords.latitude.toFixed(7));
        longitude.value = parseFloat(pos.coords.longitude.toFixed(7));
        accuracy.value = pos.coords.accuracy;
        gpsLocked.value = true;
      },
      (err) => {
        console.warn('Geolocation warning:', err.message);
        // Fallback default if blocked
        simulateCampusCoordinates();
      },
      { enableHighAccuracy: true, timeout: 8000 }
    );
  } else {
    simulateCampusCoordinates();
  }
};

const simulateCampusCoordinates = () => {
  latitude.value = 11.5564000;
  longitude.value = 104.9282000;
  accuracy.value = 5;
  gpsLocked.value = true;
};

const extractToken = (rawText) => {
  try {
    const url = new URL(rawText);
    const t = url.searchParams.get('token');
    if (t) return t;
  } catch {
    // Not a URL, treat rawText as token
  }
  return rawText.trim();
};

const startCamera = async () => {
  try {
    html5QrCode = new Html5Qrcode('qr-reader');
    cameraRunning.value = true;
    await html5QrCode.start(
      { facingMode: 'environment' },
      {
        fps: 10,
        qrbox: { width: 220, height: 220 },
      },
      (decodedText) => {
        const foundToken = extractToken(decodedText);
        token.value = foundToken;
        stopCamera();
        submitCheckIn();
      },
      () => {}
    );
  } catch (err) {
    cameraRunning.value = false;
    console.error('Camera startup error:', err);
  }
};

const stopCamera = async () => {
  if (html5QrCode && cameraRunning.value) {
    try {
      await html5QrCode.stop();
      html5QrCode.clear();
    } catch (e) {
      console.error(e);
    } finally {
      cameraRunning.value = false;
    }
  }
};

const submitCheckIn = async () => {
  if (!token.value) return;

  submitting.value = true;
  errorMessage.value = '';

  try {
    const payload = {
      token: token.value,
    };
    if (latitude.value !== null && longitude.value !== null) {
      payload.latitude = latitude.value;
      payload.longitude = longitude.value;
    }

    const { data } = await api.post('/attendance/check-in', payload);

    checkInResult.value = data;
  } catch (err) {
    const res = err.response?.data;
    if (res?.errors) {
      // Collect Laravel validation errors
      const firstErrorKey = Object.keys(res.errors)[0];
      errorMessage.value = res.errors[firstErrorKey][0];
    } else {
      errorMessage.value = res?.message || 'Check-in failed. Please try again.';
    }
  } finally {
    submitting.value = false;
  }
};

const resetScanner = () => {
  token.value = '';
  checkInResult.value = null;
  errorMessage.value = '';
};

const formatTime = (isoString) => {
  if (!isoString) return '';
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

onMounted(() => {
  requestLocation();

  // If token is in URL query (?token=XYZ), populate and auto-submit
  if (route.query.token) {
    token.value = route.query.token.toString();
    setTimeout(() => {
      if (latitude.value && longitude.value) {
        submitCheckIn();
      }
    }, 500);
  }
});

onUnmounted(() => {
  stopCamera();
});
</script>

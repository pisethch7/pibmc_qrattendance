/**
 * Persistent Device Identification Utility for PIBMC Attendance.
 * Provides unique client device ID and friendly device name.
 */

const STORAGE_KEY_DEVICE_ID = 'pibmc_device_id';

/**
 * Get or generate persistent UUID for this device / browser.
 */
export function getDeviceId() {
  let deviceId = localStorage.getItem(STORAGE_KEY_DEVICE_ID);
  if (!deviceId) {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
      deviceId = crypto.randomUUID();
    } else {
      deviceId = 'dev_' + 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
      });
    }
    localStorage.setItem(STORAGE_KEY_DEVICE_ID, deviceId);
  }
  return deviceId;
}

/**
 * Detect a friendly device and browser label from user-agent.
 */
export function getDeviceName() {
  const ua = navigator.userAgent || '';

  // Operating System / Platform
  let os = 'Unknown Device';
  if (/iPhone/i.test(ua)) os = 'iPhone';
  else if (/iPad/i.test(ua)) os = 'iPad';
  else if (/Android/i.test(ua)) os = 'Android Phone';
  else if (/Windows NT 10.0/i.test(ua)) os = 'Windows 10/11 PC';
  else if (/Windows/i.test(ua)) os = 'Windows PC';
  else if (/Macintosh|Mac OS X/i.test(ua)) os = 'MacBook / Mac';
  else if (/Linux/i.test(ua)) os = 'Linux Device';

  // Browser
  let browser = 'Browser';
  if (/Edg\//i.test(ua)) browser = 'Edge';
  else if (/Chrome\//i.test(ua) && !/Edg\//i.test(ua)) browser = 'Chrome';
  else if (/Safari\//i.test(ua) && !/Chrome\//i.test(ua)) browser = 'Safari';
  else if (/Firefox\//i.test(ua)) browser = 'Firefox';

  return `${os} (${browser})`;
}

/**
 * Clear stored device ID (primarily for testing/manual reset).
 */
export function resetLocalDeviceId() {
  localStorage.removeItem(STORAGE_KEY_DEVICE_ID);
}

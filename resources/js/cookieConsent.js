import { ref } from 'vue';

const STORAGE_KEY = 'africs_cookie_consent';

export const cookieBannerVisible = ref(false);

export function initCookieConsent() {
    if (typeof window === 'undefined') {
        return;
    }

    if (!window.localStorage.getItem(STORAGE_KEY)) {
        cookieBannerVisible.value = true;
    }
}

export function setCookieConsent(value) {
    window.localStorage.setItem(STORAGE_KEY, value);
    cookieBannerVisible.value = false;
}

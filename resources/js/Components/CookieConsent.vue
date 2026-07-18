<script setup>
import { onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cookieBannerVisible, initCookieConsent, setCookieConsent } from '@/cookieConsent';

onMounted(initCookieConsent);
</script>

<template>
    <Transition name="fade">
        <div v-if="cookieBannerVisible" class="cookie-banner" role="dialog" aria-label="Cookie preferences">
            <p>
                We use strictly necessary cookies to keep you signed in and
                keep the site secure. We don't use any tracking or marketing
                cookies today.
                <Link :href="route('cookie-policy')">Read our Cookie Policy</Link>.
            </p>
            <div class="cookie-actions">
                <button type="button" class="btn btn-on-dark" @click="setCookieConsent('declined')">
                    Decline
                </button>
                <button type="button" class="btn btn-primary" @click="setCookieConsent('accepted')">
                    Accept
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <ApplicationLogo style="height: 4.5rem" alt="Africs" />
                <p class="footer-tagline">
                    Business Advisory &amp; Technology Solutions. Adding
                    value in business, technology, and design — based in
                    Banjul, The Gambia.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="Africs on Facebook">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13.5 21v-7.2h2.4l.4-3H13.5V8.9c0-.9.2-1.5 1.5-1.5h1.4V4.9c-.3 0-1.1-.1-2.1-.1-2.1 0-3.5 1.3-3.5 3.6v2.4H8.5v3H10.8V21h2.7Z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Africs on X">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </a>
                    <a href="#" aria-label="Africs on Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <rect x="4" y="4" width="16" height="16" rx="4.5" />
                            <circle cx="12" cy="12" r="3.3" />
                            <circle cx="16.3" cy="7.7" r="0.6" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <a href="#" aria-label="Africs on LinkedIn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="4" width="16" height="16" rx="3.5" />
                            <line x1="8" y1="10.5" x2="8" y2="16" />
                            <circle cx="8" cy="7.3" r="0.9" fill="currentColor" stroke="none" />
                            <path d="M12 16v-3.6c0-1.4.9-2.4 2.1-2.4s1.9.9 1.9 2.4V16" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <p class="footer-col-title">Quick Links</p>
                <div class="footer-col-links">
                    <Link href="/">Home</Link>
                    <a href="/#capabilities">Capabilities</a>
                    <a href="/#process">How we work</a>
                    <a href="/#academy">Academy</a>
                    <Link :href="route('contact')">Contact</Link>
                </div>
            </div>

            <div class="footer-col">
                <p class="footer-col-title">Our Divisions</p>
                <div class="footer-col-links">
                    <a href="/#capabilities">Digital Transformation</a>
                    <a href="/#capabilities">Business Strategy</a>
                    <a href="/#capabilities">Research &amp; Development</a>
                    <a href="/#capabilities">Operational Excellence</a>
                </div>
            </div>

            <div class="footer-col">
                <p class="footer-col-title">Contact Info</p>
                <div class="footer-col-links">
                    <span>Banjul, The Gambia</span>
                    <a href="mailto:info@africsinc.com">info@africsinc.com</a>
                    <a href="/">africsinc.com</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ year }} Africs. All rights reserved.</p>
            <Link :href="route('cookie-policy')">Cookie Policy</Link>
        </div>
    </footer>
</template>

<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const capabilities = [
    {
        code: 'PRODUCT DESIGN',
        name: 'User experience & interface design',
        desc: 'Mapping how people actually move through a product, then designing the interface around that reality — not the org chart.',
    },
    {
        code: 'VALIDATION',
        name: 'Prototyping & user testing',
        desc: 'We test direction with real users before committing engineering time to it, so the expensive mistakes get caught on paper.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Design Services — Africs"
        meta-description="Product and service design that earns trust before a single line of code ships."
        canonical="https://africsinc.com/services/design"
        eyebrow="Services"
        title="Design."
        lede="Product and service design that earns trust before a single line of code ships."
        primary-label="Talk to us"
        :primary-href="route('contact')"
        section-eyebrow="Capabilities"
        section-title="What Design covers."
        :cards="capabilities"
        cta-title="Ready to design something people trust?"
        cta-text="Tell us about the challenge you're facing — we'll tell you honestly how we can help."
        cta-label="Get in touch"
        :cta-href="route('contact')"
        cta-email="info@africsinc.com"
    />
</template>

<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const capabilities = [
    {
        code: 'CONSULTING',
        name: 'Business strategy',
        desc: 'Strategic advisory, growth planning, and market strategy for organizations navigating complex change.',
    },
    {
        code: 'OPERATIONS',
        name: 'Operational excellence',
        desc: 'Process optimisation and delivery discipline that keeps the value we add compounding well beyond the engagement.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Business Services — Africs"
        meta-description="Strategic advisory and operational discipline that turns ambition into an executable plan."
        canonical="https://africsinc.com/services/business"
        eyebrow="Services"
        title="Business."
        lede="Strategic advisory and operational discipline that turns ambition into an executable plan."
        primary-label="Talk to us"
        :primary-href="route('contact')"
        section-eyebrow="Capabilities"
        section-title="What Business covers."
        :cards="capabilities"
        cta-title="Ready to talk business strategy?"
        cta-text="Tell us about the challenge you're facing — we'll tell you honestly how we can help."
        cta-label="Get in touch"
        :cta-href="route('contact')"
        cta-email="info@africsinc.com"
    />
</template>

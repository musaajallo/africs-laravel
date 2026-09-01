<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const capabilities = [
    {
        code: 'TECHNOLOGY',
        name: 'Digital transformation',
        desc: 'Software, cloud infrastructure, and IT strategy that turn technology into a genuine business advantage.',
    },
    {
        code: 'INNOVATION',
        name: 'Research & development',
        desc: "Early-stage research that adds value where an off-the-shelf answer doesn't exist yet.",
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Technology Services — Africs"
        meta-description="Software, infrastructure, and applied research that turn technology into a genuine advantage."
        canonical="https://africsinc.com/services/technology"
        eyebrow="Services"
        title="Technology."
        lede="Software, infrastructure, and applied research that turn technology into a genuine advantage."
        primary-label="Talk to us"
        :primary-href="route('contact')"
        section-eyebrow="Capabilities"
        section-title="What Technology covers."
        :cards="capabilities"
        cta-title="Ready to build the right technology?"
        cta-text="Tell us about the challenge you're facing — we'll tell you honestly how we can help."
        cta-label="Get in touch"
        :cta-href="route('contact')"
        cta-email="info@africsinc.com"
    />
</template>

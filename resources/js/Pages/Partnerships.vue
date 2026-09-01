<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const partners = [
    {
        code: 'PUBLIC SECTOR',
        name: 'Governments & agencies',
        desc: 'Advisory and delivery partnerships with ministries, regulators, and public bodies modernising how they serve citizens.',
    },
    {
        code: 'DEVELOPMENT',
        name: 'Funders & development partners',
        desc: 'Working alongside donors, DFIs, and foundations to design, run, and measure programmes that hold up to scrutiny.',
    },
    {
        code: 'INDUSTRY',
        name: 'Companies & delivery partners',
        desc: 'Joint pursuits and subcontracting with firms who need a local partner with real depth across business, technology, and design.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Partnerships — Africs"
        meta-description="Partner with Africs — advisory and delivery partnerships across the public sector, development partners, and industry."
        canonical="https://africsinc.com/partnerships"
        eyebrow="Work with us"
        title="Partnerships."
        lede="We do our best work with partners who bring their own context, mandate, and reach — and let us bring the delivery."
        hero-image="/images/partnerships/hero.jpg"
        hero-image-alt="Africs team meeting with a partner organisation"
        primary-label="Explore a partnership"
        :primary-href="route('contact')"
        section-eyebrow="Who we partner with"
        section-title="Three kinds of partnership."
        section-lede="Whatever the structure, the principle is the same — a clear division of responsibility and a shared standard for what good looks like."
        :cards="partners"
        cta-title="Have a partnership in mind?"
        cta-text="Tell us about the mandate, the timeline, and where you need support — we'll come back with an honest view."
        cta-label="Start the conversation"
        :cta-href="route('contact')"
        cta-email="partnerships@africsinc.com"
    />
</template>

<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const tracks = [
    {
        code: 'BUSINESS',
        name: 'Business fundamentals',
        desc: 'Strategy, operations, and financial literacy for founders and teams who need to turn ideas into a working plan.',
    },
    {
        code: 'TECHNOLOGY',
        name: 'Technology fundamentals',
        desc: 'Software, data, and cloud basics — enough to build, ship, and make sound technology decisions with confidence.',
    },
    {
        code: 'DESIGN',
        name: 'Design fundamentals',
        desc: 'Research, product thinking, and interface craft for people who want their work to be used, not just built.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Africs Academy — Training the next generation of builders"
        meta-description="Africs Academy runs courses in business, technology, and design fundamentals for people who want to add value the way we do."
        canonical="https://africsinc.com/academy"
        eyebrow="Initiative"
        title="Africs Academy."
        lede="Training the next generation of builders — courses in business, technology, and design fundamentals for people who want to add value the way we do."
        hero-image="/images/academy/hero.webp"
        hero-image-alt="Trainees at an Africs Academy computer-lab session"
        primary-label="Register your interest"
        :primary-href="route('contact')"
        section-eyebrow="Tracks"
        section-title="Three foundations, taught by practitioners."
        section-lede="Every track is built and delivered by the same people who do the work on client engagements — so what you learn is what actually ships."
        :cards="tracks"
        cta-title="Want to hear when enrolment opens?"
        cta-text="Tell us which track interests you and we'll let you know the moment the first cohort is announced."
        cta-label="Register your interest"
        :cta-href="route('contact')"
        cta-email="academy@africsinc.com"
    >
        <template #hero-extra>
            <span class="badge">Coming soon</span>
        </template>
    </InitiativePageLayout>
</template>

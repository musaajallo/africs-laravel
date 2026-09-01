<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const paths = [
    {
        code: 'CONSULTING',
        name: 'Business & strategy',
        desc: 'Analysts and consultants who frame the problem, run the analysis, and stay close to clients through delivery.',
    },
    {
        code: 'ENGINEERING',
        name: 'Technology & data',
        desc: 'Engineers, data specialists, and cloud practitioners who build the systems our recommendations depend on.',
    },
    {
        code: 'DESIGN',
        name: 'Design & research',
        desc: 'Researchers and designers who make sure what we ship is understood, adopted, and actually used.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Careers — Africs"
        meta-description="Build your career at Africs — consulting, engineering and data, and design and research roles across business, technology, and design."
        canonical="https://africsinc.com/careers"
        eyebrow="Careers"
        title="Careers."
        lede="Work on problems that matter, with people who care how the work is done — across business, technology, and design."
        hero-image="/images/careers/hero.jpg"
        hero-image-alt="The Africs team at work"
        primary-label="See open roles"
        :primary-href="route('contact')"
        section-eyebrow="Where you'd fit"
        section-title="Three paths, one standard."
        section-lede="Whichever path you join on, you'll work across disciplines — nobody here is boxed into a single lane for long."
        :cards="paths"
        cta-title="Don't see your role?"
        cta-text="We're always glad to hear from strong people early. Send your CV and a note on the work you want to do."
        cta-label="Introduce yourself"
        :cta-href="route('contact')"
        cta-email="careers@africsinc.com"
    />
</template>

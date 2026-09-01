<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const programs = [
    {
        code: 'EDUCATION',
        name: 'Education & literacy',
        desc: 'Quality education and digital-skills training in underserved communities, so more people can take part in the modern economy.',
    },
    {
        code: 'TECHNOLOGY',
        name: 'Technology & innovation',
        desc: 'Closing the digital divide through access, tools, and entrepreneurship programmes for young people across the region.',
    },
    {
        code: 'COMMUNITY',
        name: 'Community building',
        desc: 'Mentorship, networks, and collaborative development projects that leave communities stronger than we found them.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Limitless Africs — Empowering communities across Africa"
        meta-description="Limitless Africs is Africs' social initiative — empowering communities through education, technology, and sustainable development."
        canonical="https://africsinc.com/limitless-africs"
        eyebrow="Initiative"
        title="Limitless Africs."
        lede="Our social initiative — empowering communities across Africa through education, technology, and sustainable development."
        hero-image="/images/limitless/hero.webp"
        hero-image-alt="Group photo of a Limitless Africs community programme cohort"
        primary-label="Get involved"
        :primary-href="route('contact')"
        section-eyebrow="What we do"
        section-title="Three programmes, one goal."
        section-lede="Each programme is designed to compound — skills lead to opportunity, opportunity strengthens communities, and stronger communities create more builders."
        :cards="programs"
        cta-title="Want to get involved?"
        cta-text="Whether you want to volunteer, partner, or support the work — tell us how you'd like to help."
        cta-label="Get involved"
        :cta-href="route('contact')"
        cta-email="limitless@africsinc.com"
    />
</template>

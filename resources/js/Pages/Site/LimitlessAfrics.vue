<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

// Areas of interest — not fixed causes we build around. These are the places
// we already have the expertise to do good work, and where we expand from as
// the opportunity arises.
const areas = [
    {
        code: 'EDUCATION',
        name: 'Education & literacy',
        desc: 'Access to quality education and digital skills in underserved communities, so more people can take part in the modern economy.',
    },
    {
        code: 'TECHNOLOGY',
        name: 'Technology & innovation',
        desc: 'Closing the digital divide through access, tools, and entrepreneurship — building on the work we already do every day.',
    },
    {
        code: 'COMMUNITY',
        name: 'Community building',
        desc: 'Mentorship, networks, and collaborative projects that leave communities stronger than we found them.',
    },
];

// Initiatives — distinct efforts run under Limitless Africs, each with its own
// name, focus, and people.
const initiatives = [
    {
        code: 'LANGTURE',
        name: 'Langture',
        desc: 'Investing in language and culture — first in our own communities, then further afield. Spoken and written languages, and sign language.',
    },
    {
        code: 'HACKATHORN',
        name: 'HackAThorn',
        desc: 'Hacking as problem-solving, across every discipline — not only technology. Bringing people together to work a hard problem and build something that answers it.',
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
        section-eyebrow="Areas of interest"
        section-title="Where we start, and where we grow."
        section-lede="We don't pick causes and build around them. We start with what we already have the expertise to do well, and expand as the opportunity arises."
        :cards="areas"
        cta-title="Want to get involved?"
        cta-text="Whether you want to volunteer, partner, or support the work — tell us how you'd like to help."
        cta-label="Get involved"
        :cta-href="route('contact')"
        cta-email="limitless@africsinc.com"
    >
        <template #after-cards>
            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">Initiatives</p>
                <h2 class="section-title">What we've started.</h2>
                <p class="section-lede">
                    Distinct efforts run under Limitless Africs — each with its
                    own name, focus, and people.
                </p>
            </div>

            <div class="programs-grid">
                <article
                    v-for="initiative in initiatives"
                    :key="initiative.code"
                    class="program-card"
                >
                    <p class="division-code">{{ initiative.code }}</p>
                    <h3 class="division-name">{{ initiative.name }}</h3>
                    <p class="division-desc">{{ initiative.desc }}</p>
                </article>
            </div>
        </template>
    </InitiativePageLayout>
</template>

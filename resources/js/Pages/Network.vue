<script setup>
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const categories = [
    {
        code: 'BUSINESS & FINANCE',
        name: 'Business & finance',
        desc: 'Accountants, auditors, tax advisers, and management consultants we trust with clients’ books, filings, and planning.',
    },
    {
        code: 'TECHNOLOGY',
        name: 'Technology & delivery',
        desc: 'Engineering firms, agencies, and independent developers we sub-contract to or recommend for hands-on build work.',
    },
    {
        code: 'LEGAL & ADVISORY',
        name: 'Legal & advisory',
        desc: 'Lawyers, regulatory advisers, and sector specialists for the parts of a project that need formal, licensed expertise.',
    },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="The Africs Network — Businesses & professionals we recommend"
        meta-description="A curated network of businesses and independent professionals Africs knows, has worked with, and is happy to recommend to clients."
        canonical="https://africsinc.com/network"
        eyebrow="Who we recommend"
        title="The Africs Network."
        lede="A curated network of businesses and independent professionals we know, have worked with, and are glad to point clients toward."
        hero-image="/images/network/hero.jpg"
        hero-image-alt="Africs working alongside partner businesses"
        primary-label="Request an introduction"
        :primary-href="route('contact')"
        section-eyebrow="What's in the network"
        section-title="People and firms we vouch for."
        section-lede="Organised by the kind of work clients most often ask us for someone to handle."
        :cards="categories"
        cta-title="Looking for a recommendation?"
        cta-text="Tell us the kind of business or professional you need. If someone in the network fits, we'll make a warm introduction and step back — no finder's fees."
        cta-label="Request an introduction"
        :cta-href="route('contact')"
        cta-email="network@africsinc.com"
    >
        <template #after-cards>
            <p class="section-lede" style="margin-top: 2rem">
                Everyone here has either done real work with us or come
                strongly referred by someone who has. The full directory is
                being compiled — until then, ask us directly.
            </p>
        </template>
    </InitiativePageLayout>
</template>

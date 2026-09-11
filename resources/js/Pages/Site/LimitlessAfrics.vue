<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import InitiativePageLayout from '@/Components/InitiativePageLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

// How we work — national priorities we map, initiatives we build around them,
// and the projects we actually implement under each initiative.
const model = [
    {
        code: '01',
        name: 'National priorities',
        desc: 'We map the priorities that matter most for the country, so effort is aimed where it counts.',
    },
    {
        code: '02',
        name: 'Initiatives',
        desc: 'We build focused, long-running initiatives around those priorities — Langture and HackAThorn are two of them.',
    },
    {
        code: '03',
        name: 'Projects',
        desc: 'Each initiative carries many projects — but a project doesn’t have to sit under one. Projects are what we implement — ours, a partner’s, or yours.',
    },
];

// Mainstreaming — cross-cutting groups we check every project against, rather
// than running as separate side programmes.
const mainstreaming = [
    {
        code: 'GENDER',
        name: 'Gender',
        desc: 'Making sure a project’s design, delivery, and benefits don’t default to whoever is easiest to reach — women and men are represented and considered at every stage.',
    },
    {
        code: 'DISABILITY',
        name: 'Disability',
        desc: 'Removing the physical, digital, and communication barriers that keep people with disabilities out of a project meant for everyone — sign language, under Langture, is one part of this.',
    },
    {
        code: 'ENVIRONMENT',
        name: 'Environment & climate',
        desc: 'Checking every project for its environmental footprint and its resilience to a changing climate, even when neither is the project’s main purpose.',
    },
    {
        code: 'AGE',
        name: 'Age',
        desc: 'Making sure older people aren’t designed out of a project built around younger or working-age assumptions.',
    },
    {
        code: 'YOUTH',
        name: 'Youth',
        desc: 'With most of the region’s population young, making sure young people are represented as participants and decision-makers, not just beneficiaries.',
    },
];

const activeLens = ref(0);

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
// name, focus, people, and many projects.
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
    {
        code: 'IDEABANK',
        name: 'IdeaBank',
        desc: 'A collaboratively built, online catalogue of identified problems and possible solutions — a shared resource projects and initiatives can draw on instead of starting from scratch.',
    },
    {
        code: 'COLLECTSUN',
        name: 'CollectSun',
        desc: 'Conferences, unconferences, meetups, and other networking events, built to create the space for genuine discussion — and to feed what comes out of it into IdeaBank.',
    },
    {
        code: 'TECHTREK',
        name: 'TechTrek',
        desc: 'Digital literacy and hands-on technology training — drones, robotics, VR/AR, and open-source computing — taken to where people are, including rural Gambia.',
    },
    {
        code: 'BOOKSHARE',
        name: 'BookShare',
        desc: 'A catalogue of books owners are willing to lend, connected through a network for borrowing and returning them — from which book clubs, reviews, and a platform for young Gambian writers can develop.',
    },
];

// TechTrek's second project — refurbishing old school computers rather than
// waiting on new hardware.
const refurbSteps = [
    {
        name: 'Find & collect',
        desc: 'Schools with computers set aside for dust, a dead battery, or neglect — not failure.',
    },
    {
        name: 'Refurbish',
        desc: 'A clean, fresh thermal paste, and any small repairs needed to make each machine reliable again.',
    },
    {
        name: 'Install Edubuntu',
        desc: 'A Linux distribution built for classrooms — free, open-source, and light enough for older hardware.',
    },
    {
        name: 'Train',
        desc: 'Students and teachers learn to use it, so the machines get used — not just switched on once.',
    },
];

// National work mapping — representative only (see docs/national-work-mapping.md
// for the full brief). Sample projects below are placeholders for layout and
// interaction purposes, not real project records — replace before this ships.
const clusters = [
    'Governance',
    'Economy & Livelihoods',
    'Foreign & Security',
    'Human Capital',
    'Production & Natural Resources',
    'Infrastructure & Territory',
    'Social & Culture',
];

const partnerTypes = ['Government', 'Community', 'Private', 'NGO'];

const regions = ['National', 'Banjul', 'KMC', 'WCR', 'NBR', 'LRR', 'CRR', 'URR'];

const sampleProjects = [
    {
        title: 'Regional youth council digital platform',
        summary: 'An online platform regional youth councils use to coordinate, report, and reach the national body.',
        region: 'National',
        partnerTypes: ['Government', 'Community'],
        clusters: ['Governance', 'Infrastructure & Territory'],
        status: 'active',
    },
    {
        title: 'Rural borehole & water access programme',
        summary: 'Boreholes and maintenance training so villages don’t lose access the moment a pump breaks.',
        region: 'URR',
        partnerTypes: ['Community', 'NGO'],
        clusters: ['Production & Natural Resources'],
        status: 'completed',
    },
    {
        title: 'Roadside traffic safety signage',
        summary: 'Signage and markings on high-incident stretches of road, done with the local council.',
        region: 'KMC',
        partnerTypes: ['Government'],
        clusters: ['Infrastructure & Territory', 'Foreign & Security'],
        status: 'completed',
    },
    {
        title: 'Market vendor safety & livelihoods training',
        summary: 'Practical safety and business training for market vendors, run with a private-sector partner.',
        region: 'National',
        partnerTypes: ['Private', 'Community'],
        clusters: ['Economy & Livelihoods', 'Human Capital'],
        status: 'ongoing',
    },
    {
        title: 'School digital literacy pilot',
        summary: 'A digital-skills pilot in a handful of schools, ahead of a wider rollout.',
        region: 'WCR',
        partnerTypes: ['Government', 'NGO'],
        clusters: ['Human Capital', 'Governance'],
        status: 'active',
    },
    {
        title: 'Coastal tourism & culture mapping',
        summary: 'Mapping coastal cultural sites so they can be preserved and, where appropriate, opened to tourism.',
        region: 'National',
        partnerTypes: ['Government', 'Private'],
        clusters: ['Social & Culture', 'Production & Natural Resources'],
        status: 'completed',
    },
    {
        title: 'Banjul markets drainage & flood mitigation',
        summary: 'Improved drainage around Banjul’s central markets, to cut the seasonal flooding that shuts vendors down every rainy season.',
        region: 'Banjul',
        partnerTypes: ['Government'],
        clusters: ['Infrastructure & Territory', 'Production & Natural Resources'],
        status: 'ongoing',
    },
    {
        title: 'North Bank community radio for civic reporting',
        summary: 'A community radio partnership that lets North Bank residents report local issues straight to regional administration.',
        region: 'NBR',
        partnerTypes: ['Community', 'NGO'],
        clusters: ['Governance', 'Social & Culture'],
        status: 'active',
    },
    {
        title: 'Lower River smallholder irrigation cooperative',
        summary: 'A farmer-run irrigation cooperative supported with equipment and cooperative-governance training.',
        region: 'LRR',
        partnerTypes: ['Community', 'Private'],
        clusters: ['Production & Natural Resources', 'Economy & Livelihoods'],
        status: 'completed',
    },
    {
        title: 'Central River ferry crossing safety upgrade',
        summary: 'Safety upgrades at a key river crossing used daily by commuters and traders between the two banks.',
        region: 'CRR',
        partnerTypes: ['Government'],
        clusters: ['Infrastructure & Territory', 'Foreign & Security'],
        status: 'ongoing',
    },
];

const activeClusters = ref([]);
const activePartnerTypes = ref([]);
const activeRegions = ref([]);

function toggle(list, value) {
    const index = list.value.indexOf(value);

    if (index === -1) {
        list.value.push(value);
    } else {
        list.value.splice(index, 1);
    }
}

const filteredProjects = computed(() => sampleProjects.filter((project) => (
    (activeClusters.value.length === 0 || project.clusters.some((c) => activeClusters.value.includes(c)))
    && (activePartnerTypes.value.length === 0 || project.partnerTypes.some((t) => activePartnerTypes.value.includes(t)))
    && (activeRegions.value.length === 0 || activeRegions.value.includes(project.region))
)));

const statusLabels = { active: 'Active', ongoing: 'Ongoing', completed: 'Completed' };

// Plain-language cluster → COFOG crosswalk, shown collapsed as a legend —
// never the primary label. See docs/national-work-mapping.md.
const crosswalk = [
    { cluster: 'Governance', codes: '01.1 (executive organs, local administration)' },
    { cluster: 'Economy & Livelihoods', codes: '01.1 (fiscal affairs), 04.1, 04.2, 04.6' },
    { cluster: 'Foreign & Security', codes: '01.1 (external affairs), 02, 03.1, 03.3' },
    { cluster: 'Human Capital', codes: '07, 09.1–09.4, 09.8, 10' },
    { cluster: 'Production & Natural Resources', codes: '04.2, 04.3, 05, 06.3' },
    { cluster: 'Infrastructure & Territory', codes: '04.4, 04.5, 06.2' },
    { cluster: 'Social & Culture', codes: '04.7, 08.1, 08.2' },
];
</script>

<template>
    <InitiativePageLayout
        :can-login="canLogin"
        meta-title="Limitless Africs — Improving quality of life across Africa"
        meta-description="Limitless Africs is Africs' social initiative — improving people's quality of life directly, and by creating the circumstances that make a good life possible."
        canonical="https://africsinc.com/limitless-africs"
        eyebrow="Initiative"
        title="Limitless Africs."
        lede="Limitless Africs is a for-impact organization that exists to improve people's quality of life — by adding value any way we can. Sometimes that's direct help; more often it's creating the circumstances in which a good life becomes possible, using technology, research, and whatever else the work calls for."
        hero-image="/images/limitless/hero.webp"
        hero-image-alt="Group photo of a Limitless Africs project team in the field"
        primary-label="Get involved"
        :primary-href="route('contact')"
        section-eyebrow="Areas of interest"
        section-title="Where we start, and where we grow."
        section-lede="We don't pick causes and build around them. We start with what we already have the expertise to do well, and expand as the opportunity arises."
        :cards="areas"
        cta-title="Want to get involved?"
        cta-text="Whether you want to volunteer, partner, or bring us a project to implement — tell us how you'd like to help."
        cta-label="Get involved"
        :cta-href="route('contact')"
        cta-email="limitless@africsinc.com"
    >
        <template #before-cards>
            <div class="section-head">
                <p class="section-eyebrow">About the name</p>
                <h2 class="section-title">Africans are limitless. Resources are the constraint.</h2>
                <p class="section-lede">
                    The name is a claim, not a slogan. African capacity for
                    development, innovation, and change is not the limit —
                    resources usually are. Limitless Africs exists to
                    unlock that potential by working with partners,
                    individuals, communities, and initiatives to provide
                    them.
                </p>
            </div>

            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">How we work</p>
                <h2 class="section-title">Priorities, initiatives, projects.</h2>
                <p class="section-lede">
                    We run our own projects, partner with international
                    organisations, and deliver projects for people who have one
                    in mind but don't want to start an organisation just to make
                    it happen. Nobody should have to found an institution to get
                    a project done — that's what we're here for. It just has to
                    be organised properly.
                </p>
            </div>

            <div class="programs-grid">
                <article
                    v-for="step in model"
                    :key="step.code"
                    class="program-card"
                >
                    <p class="division-code">{{ step.code }}</p>
                    <h3 class="division-name">{{ step.name }}</h3>
                    <p class="division-desc">{{ step.desc }}</p>
                </article>
            </div>

            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">Mainstreaming</p>
                <h2 class="section-title">Making sure the right people are represented.</h2>
                <p class="section-lede">
                    Mainstreaming isn't a side programme — it's a check we run
                    against everything we do. Left alone, a project tends to
                    reach whoever is easiest to reach. Mainstreaming is how we
                    make sure the groups most likely to be left out are
                    actually accounted for in the main work, not bolted on
                    afterward.
                </p>
            </div>

            <div class="mainstreaming-tabs">
                <div class="mainstreaming-tablist" role="tablist">
                    <button
                        v-for="(lens, index) in mainstreaming"
                        :key="lens.code"
                        type="button"
                        role="tab"
                        :aria-selected="activeLens === index"
                        class="mainstreaming-tab"
                        :class="{ 'is-active': activeLens === index }"
                        @click="activeLens = index"
                    >
                        {{ lens.name }}
                    </button>
                </div>

                <div class="mainstreaming-panel" role="tabpanel">
                    <p class="division-code">{{ mainstreaming[activeLens].code }}</p>
                    <p class="mainstreaming-desc">{{ mainstreaming[activeLens].desc }}</p>
                </div>
            </div>
        </template>

        <template #after-cards>
            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">Initiatives</p>
                <h2 class="section-title">The initiatives so far.</h2>
                <p class="section-lede">
                    Each one is a focused, long-running effort with many
                    projects under it.
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

            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">TechTrek · Kids in Technology</p>
                <h2 class="section-title">A laptop, a scholarship, and an Arduino kit — for 10 students.</h2>
                <p class="section-lede">
                    Our first TechTrek project is a partnership with
                    <a
                        class="inline-link"
                        href="https://kidsintechnology.org"
                        target="_blank"
                        rel="noopener"
                    >Kids in Technology</a>, a Gambia-focused STEM
                    programme for children and teenagers. We're raising what
                    it takes to give 10 students a laptop, a scholarship,
                    and an Arduino starter kit each — the hardware and
                    support to take them from using technology to building
                    with it.
                </p>
            </div>

            <div class="spotlight">
                <div class="spotlight-stats">
                    <div class="spotlight-stat">
                        <p class="spotlight-stat-value">10</p>
                        <p class="spotlight-stat-label">Laptops</p>
                    </div>
                    <div class="spotlight-stat">
                        <p class="spotlight-stat-value">10</p>
                        <p class="spotlight-stat-label">Scholarships</p>
                    </div>
                    <div class="spotlight-stat">
                        <p class="spotlight-stat-value">10</p>
                        <p class="spotlight-stat-label">Arduino kits</p>
                    </div>
                </div>

                <div class="spotlight-cta">
                    <Link :href="route('contact')" class="btn btn-primary btn-lg">
                        Support this project
                    </Link>
                    <a href="mailto:limitless@africsinc.com" class="btn btn-secondary btn-lg">
                        limitless@africsinc.com
                    </a>
                </div>
            </div>

            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">TechTrek · School refurbishment</p>
                <h2 class="section-title">Old computers, a new life, in a classroom.</h2>
                <p class="section-lede">
                    Rather than wait on new hardware, we find schools with
                    computers that still work but have been written off,
                    bring them back to reliable condition, and put a
                    classroom-ready Linux distribution on them. Then we do
                    it again at the next school — one region at a time.
                </p>
            </div>

            <ol class="step-flow">
                <li
                    v-for="(step, index) in refurbSteps"
                    :key="step.name"
                    class="step-flow-item"
                >
                    <span class="step-flow-index">{{ String(index + 1).padStart(2, '0') }}</span>
                    <span class="step-flow-name">{{ step.name }}</span>
                    <span class="step-flow-desc">{{ step.desc }}</span>
                </li>
            </ol>

            <div class="section-head" style="margin-top: 4rem">
                <p class="section-eyebrow">Where we've worked</p>
                <h2 class="section-title">We don't confine our work to a fixed set of sectors.</h2>
                <p class="section-lede">
                    We go where the gap is, and where we can find the
                    people, resources, or solution to close it. Below is a
                    sample of that work, tagged by the parts of national
                    life it touches — a project can carry more than one
                    tag, because the real work usually does.
                </p>
                <p class="work-map-sample-note">
                    Representative sample for now — illustrative projects,
                    not a verified record of every engagement.
                </p>
            </div>

            <div class="work-map">
                <div class="work-map-filters">
                    <div class="work-map-filter-group">
                        <p class="work-map-filter-label">Cluster</p>
                        <div class="work-map-chip-row">
                            <button
                                v-for="cluster in clusters"
                                :key="cluster"
                                type="button"
                                class="work-map-chip"
                                :class="{ 'is-active': activeClusters.includes(cluster) }"
                                @click="toggle(activeClusters, cluster)"
                            >
                                {{ cluster }}
                            </button>
                        </div>
                    </div>

                    <div class="work-map-filter-group">
                        <p class="work-map-filter-label">Partner type</p>
                        <div class="work-map-chip-row">
                            <button
                                v-for="type in partnerTypes"
                                :key="type"
                                type="button"
                                class="work-map-chip"
                                :class="{ 'is-active': activePartnerTypes.includes(type) }"
                                @click="toggle(activePartnerTypes, type)"
                            >
                                {{ type }}
                            </button>
                        </div>
                    </div>

                    <div class="work-map-filter-group">
                        <p class="work-map-filter-label">Region</p>
                        <div class="work-map-chip-row">
                            <button
                                v-for="region in regions"
                                :key="region"
                                type="button"
                                class="work-map-chip"
                                :class="{ 'is-active': activeRegions.includes(region) }"
                                @click="toggle(activeRegions, region)"
                            >
                                {{ region }}
                            </button>
                        </div>
                    </div>
                </div>

                <p v-if="!filteredProjects.length" class="work-map-empty">
                    No sample project matches every filter — clear one to
                    see more.
                </p>

                <div v-else class="work-map-grid">
                    <article
                        v-for="project in filteredProjects"
                        :key="project.title"
                        class="work-map-card"
                    >
                        <div class="work-map-card-head">
                            <span
                                class="work-map-status"
                                :class="`is-${project.status}`"
                            >
                                {{ statusLabels[project.status] }}
                            </span>
                            <span class="work-map-region">{{ project.region }}</span>
                        </div>
                        <h3 class="work-map-card-title">{{ project.title }}</h3>
                        <p class="work-map-card-summary">{{ project.summary }}</p>
                        <div class="work-map-tags">
                            <span
                                v-for="tag in project.clusters"
                                :key="tag"
                                class="work-map-tag"
                            >
                                {{ tag }}
                            </span>
                        </div>
                    </article>
                </div>

                <details class="work-map-legend">
                    <summary>How we classify this work</summary>
                    <p class="work-map-legend-note">
                        Each plain-language cluster above maps to one or
                        more government-function codes (COFOG). Several
                        codes appear under more than one cluster —
                        government functions genuinely overlap, so we
                        don't force a project into a single box.
                    </p>
                    <div class="work-map-legend-table-wrap">
                        <table class="work-map-legend-table">
                            <thead>
                                <tr>
                                    <th>Cluster</th>
                                    <th>COFOG codes covered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in crosswalk" :key="row.cluster">
                                    <td>{{ row.cluster }}</td>
                                    <td>{{ row.codes }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="work-map-legend-download">
                        <a :href="route('limitless-africs.classification-pdf', { download: 1 })">
                            Download the full ministry crosswalk (PDF)
                        </a>
                    </p>
                </details>
            </div>
        </template>
    </InitiativePageLayout>
</template>

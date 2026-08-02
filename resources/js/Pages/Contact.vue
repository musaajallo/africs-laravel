<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const page = usePage();
const pageRoot = ref(null);

useScrollReveal(pageRoot);

const form = useForm({
    name: '',
    email: '',
    company: '',
    phone: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Contact — Africs">
        <meta
            name="description"
            content="Get in touch with Africs — tell us about the challenge you're facing and we'll tell you honestly how we can add value."
        />
        <link rel="canonical" href="https://africsinc.com/contact" />
        <meta property="og:title" content="Contact — Africs" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://africsinc.com/contact" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div ref="pageRoot">
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="page-hero">
                <div class="container">
                    <p class="section-eyebrow">Contact</p>
                    <h1 class="section-title" style="font-size: clamp(2rem, 4vw, 2.75rem)">
                        Ready to add value to your next project?
                    </h1>
                    <p class="section-lede">
                        Tell us about the challenge you're facing — we'll
                        tell you honestly how we can help.
                    </p>
                </div>
            </section>

            <section class="section" style="padding-top: 0">
                <div class="container">
                    <div class="contact-grid">
                        <form class="contact-form" @submit.prevent="submit">
                            <div v-if="page.props.flash?.success" class="form-success">
                                {{ page.props.flash.success }}
                            </div>

                            <div class="field-row">
                                <div class="field">
                                    <InputLabel for="name" value="Name" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="field">
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        autocomplete="email"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>

                            <div class="field-row">
                                <div class="field">
                                    <InputLabel for="company" value="Company (optional)" />
                                    <TextInput
                                        id="company"
                                        type="text"
                                        v-model="form.company"
                                        autocomplete="organization"
                                    />
                                    <InputError :message="form.errors.company" />
                                </div>

                                <div class="field">
                                    <InputLabel for="phone" value="Phone (optional)" />
                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        v-model="form.phone"
                                        autocomplete="tel"
                                    />
                                    <InputError :message="form.errors.phone" />
                                </div>
                            </div>

                            <div class="field">
                                <InputLabel for="message" value="Message" />
                                <textarea
                                    id="message"
                                    class="field-input"
                                    v-model="form.message"
                                    required
                                ></textarea>
                                <InputError :message="form.errors.message" />
                            </div>

                            <div class="form-actions" style="justify-content: flex-start">
                                <PrimaryButton :disabled="form.processing">
                                    Send message
                                    <span aria-hidden="true">&rarr;</span>
                                </PrimaryButton>
                            </div>
                        </form>

                        <aside class="contact-aside">
                            <div class="contact-aside-item">
                                <p class="contact-aside-label">Email</p>
                                <p class="contact-aside-value">
                                    <a href="mailto:info@africsinc.com">info@africsinc.com</a>
                                </p>
                            </div>
                            <div class="contact-aside-item">
                                <p class="contact-aside-label">Based in</p>
                                <p class="contact-aside-value">Banjul, The Gambia</p>
                                <p class="contact-aside-coords">13.45&deg; N, 16.58&deg; W</p>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            <!-- What happens after you submit -->
            <section class="section section-dark contact-process reveal">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">After you hit send</p>
                        <h2 class="section-title" style="color: var(--color-cream-text)">
                            What happens next
                        </h2>
                        <p class="section-lede">
                            No inbox black hole. Here's exactly what to
                            expect once you send that message.
                        </p>
                    </div>

                    <div class="process-list">
                        <div class="process-step">
                            <p class="process-number">01</p>
                            <h3 class="process-title">A person reads it</h3>
                            <p class="process-desc">
                                Every message reaches a member of the team
                                directly — not a shared inbox on autopilot.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">02</p>
                            <h3 class="process-title">We ask before we assume</h3>
                            <p class="process-desc">
                                If anything's unclear, we'll follow up with
                                questions before we guess at a solution.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">03</p>
                            <h3 class="process-title">We talk it through</h3>
                            <p class="process-desc">
                                Once we understand the challenge, we set up a
                                short call to scope it properly.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>

import { onMounted, onUnmounted } from 'vue';

// Fades in every `.reveal` element inside `rootRef` as it scrolls into view.
// Respects prefers-reduced-motion by revealing everything immediately.
export function useScrollReveal(rootRef) {
    let observer;

    onMounted(() => {
        const targets = rootRef.value?.querySelectorAll('.reveal') ?? [];
        const prefersReducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (prefersReducedMotion) {
            targets.forEach((el) => el.classList.add('is-visible'));
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -80px 0px' },
        );

        targets.forEach((el) => observer.observe(el));
    });

    onUnmounted(() => observer?.disconnect());
}

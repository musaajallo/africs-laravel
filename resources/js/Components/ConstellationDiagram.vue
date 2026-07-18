<script setup>
const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
</script>

<template>
    <div class="constellation" aria-hidden="true">
        <svg viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="edgeBT" gradientUnits="userSpaceOnUse" x1="200" y1="46" x2="64" y2="272">
                    <stop offset="0" stop-color="#d4a24c" stop-opacity="0.9" />
                    <stop offset="1" stop-color="#d4a24c" stop-opacity="0.15" />
                </linearGradient>
                <linearGradient id="edgeBD" gradientUnits="userSpaceOnUse" x1="200" y1="46" x2="336" y2="272">
                    <stop offset="0" stop-color="#d4a24c" stop-opacity="0.9" />
                    <stop offset="1" stop-color="#d4a24c" stop-opacity="0.15" />
                </linearGradient>
                <linearGradient id="edgeTD" gradientUnits="userSpaceOnUse" x1="64" y1="272" x2="336" y2="272">
                    <stop offset="0" stop-color="#d4a24c" stop-opacity="0.6" />
                    <stop offset="0.5" stop-color="#d4a24c" stop-opacity="0.35" />
                    <stop offset="1" stop-color="#d4a24c" stop-opacity="0.6" />
                </linearGradient>
                <radialGradient id="nodeGlow" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#d4a24c" stop-opacity="0.55" />
                    <stop offset="100%" stop-color="#d4a24c" stop-opacity="0" />
                </radialGradient>
            </defs>

            <path
                id="edge-bt"
                class="constellation-line"
                style="animation-delay: 0.1s"
                d="M200 46 L64 272"
                pathLength="1"
                stroke="url(#edgeBT)"
                stroke-width="1.75"
            />
            <path
                id="edge-bd"
                class="constellation-line"
                style="animation-delay: 0.3s"
                d="M200 46 L336 272"
                pathLength="1"
                stroke="url(#edgeBD)"
                stroke-width="1.75"
            />
            <path
                id="edge-td"
                class="constellation-line"
                style="animation-delay: 0.5s"
                d="M64 272 L336 272"
                pathLength="1"
                stroke="url(#edgeTD)"
                stroke-width="1.75"
            />

            <template v-if="!prefersReducedMotion">
                <circle r="3.5" fill="#f3efe3" class="pulse-dot">
                    <animateMotion dur="3.2s" begin="1.2s" repeatCount="indefinite">
                        <mpath href="#edge-bt" />
                    </animateMotion>
                </circle>
                <circle r="3.5" fill="#f3efe3" class="pulse-dot">
                    <animateMotion dur="3.6s" begin="2s" repeatCount="indefinite">
                        <mpath href="#edge-bd" />
                    </animateMotion>
                </circle>
                <circle r="3" fill="#f3efe3" class="pulse-dot">
                    <animateMotion dur="4.2s" begin="2.6s" repeatCount="indefinite">
                        <mpath href="#edge-td" />
                    </animateMotion>
                </circle>
            </template>

            <g class="constellation-node" style="animation-delay: 0.6s">
                <circle cx="200" cy="46" r="18" fill="url(#nodeGlow)" class="node-glow" style="animation-delay: 0s" />
                <circle cx="200" cy="46" r="7" fill="#d4a24c" stroke="#f3efe3" stroke-width="1.5" />
                <text x="200" y="22" text-anchor="middle">BUSINESS</text>
            </g>

            <g class="constellation-node" style="animation-delay: 0.75s">
                <circle cx="64" cy="272" r="18" fill="url(#nodeGlow)" class="node-glow" style="animation-delay: 0.7s" />
                <circle cx="64" cy="272" r="7" fill="#d4a24c" stroke="#f3efe3" stroke-width="1.5" />
                <text x="64" y="300" text-anchor="middle">TECHNOLOGY</text>
            </g>

            <g class="constellation-node" style="animation-delay: 0.9s">
                <circle cx="336" cy="272" r="18" fill="url(#nodeGlow)" class="node-glow" style="animation-delay: 1.4s" />
                <circle cx="336" cy="272" r="7" fill="#d4a24c" stroke="#f3efe3" stroke-width="1.5" />
                <text x="336" y="300" text-anchor="middle">DESIGN</text>
            </g>
        </svg>
    </div>
</template>

<style scoped>
.constellation {
    color: rgba(243, 239, 227, 0.35);
    animation: float 7s ease-in-out infinite;
}

.constellation svg {
    width: 100%;
    height: auto;
    overflow: visible;
}

.constellation text {
    font-family: var(--font-mono);
    font-size: 11px;
    letter-spacing: 0.08em;
    fill: var(--color-cream-text);
}

.constellation-line {
    stroke-dasharray: 1;
    stroke-dashoffset: 1;
    animation: draw 1s ease forwards;
}

.constellation-node {
    opacity: 0;
    transform-origin: center;
    animation: pop-in 0.5s ease forwards;
}

.node-glow {
    transform-box: fill-box;
    transform-origin: center;
    animation: pulse 2.4s ease-in-out infinite;
}

.pulse-dot {
    filter: drop-shadow(0 0 3px rgba(243, 239, 227, 0.8));
}

@keyframes draw {
    to {
        stroke-dashoffset: 0;
    }
}

@keyframes pop-in {
    from {
        opacity: 0;
        transform: scale(0.4);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.5;
        transform: scale(0.85);
    }
    50% {
        opacity: 1;
        transform: scale(1.25);
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .constellation {
        animation: none;
    }

    .constellation-line,
    .constellation-node {
        animation: none;
        stroke-dashoffset: 0;
        opacity: 1;
    }

    .node-glow {
        animation: none;
        opacity: 0.6;
    }
}
</style>

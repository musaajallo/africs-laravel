<template>
    <div class="constellation" aria-hidden="true">
        <svg viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                class="constellation-line"
                style="animation-delay: 0.1s"
                d="M200 46 L64 272"
                pathLength="1"
                stroke="currentColor"
                stroke-width="1.5"
            />
            <path
                class="constellation-line"
                style="animation-delay: 0.3s"
                d="M200 46 L336 272"
                pathLength="1"
                stroke="currentColor"
                stroke-width="1.5"
            />
            <path
                class="constellation-line"
                style="animation-delay: 0.5s"
                d="M64 272 L336 272"
                pathLength="1"
                stroke="currentColor"
                stroke-width="1.5"
            />

            <g class="constellation-node" style="animation-delay: 0.6s">
                <circle cx="200" cy="46" r="7" fill="#d4a24c" />
                <text x="200" y="24" text-anchor="middle">BUSINESS</text>
            </g>

            <g class="constellation-node" style="animation-delay: 0.75s">
                <circle cx="64" cy="272" r="7" fill="#d4a24c" />
                <text x="64" y="300" text-anchor="middle">TECHNOLOGY</text>
            </g>

            <g class="constellation-node" style="animation-delay: 0.9s">
                <circle cx="336" cy="272" r="7" fill="#d4a24c" />
                <text x="336" y="300" text-anchor="middle">DESIGN</text>
            </g>
        </svg>
    </div>
</template>

<style scoped>
.constellation {
    color: rgba(243, 239, 227, 0.35);
}

.constellation svg {
    width: 100%;
    height: auto;
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

@media (prefers-reduced-motion: reduce) {
    .constellation-line,
    .constellation-node {
        animation: none;
        stroke-dashoffset: 0;
        opacity: 1;
    }
}
</style>

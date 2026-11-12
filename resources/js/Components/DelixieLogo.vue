<script setup>
import { useId } from 'vue';

defineProps({
    sizeClass: {
        type: String,
        default: 'w-7 h-7',
    },
    showWordmark: {
        type: Boolean,
        default: true,
    },
    wordmarkClass: {
        type: String,
        default: 'text-lg font-extrabold tracking-tight text-[#13293D]',
    },
    wordmarkText: {
        type: String,
        default: 'Delixie',
    },
});

const uid = useId();
const gradId = `delixie-wing-${uid}`;
</script>

<template>
    <span class="inline-flex items-center gap-2.5 select-none">
        <!-- Konsep 4: The Aero-Maritime Wing Monogram (D + Air Wing + Sea Hull) -->
        <svg
            :class="[sizeClass, 'shrink-0 drop-shadow-[0_2px_6px_rgba(19,41,61,0.16)] transition-transform duration-200 group-hover:scale-105']"
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <defs>
                <linearGradient :id="gradId" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#265D7A" />
                    <stop offset="100%" stop-color="#67C7C6" />
                </linearGradient>
            </defs>
            <!-- Upper Aero D Arc (Cargo Wing Slice) -->
            <path
                d="M 18 16 H 48 C 70 16 83 28 84 44 L 58 44 L 34 32 V 56 L 18 62 V 16 Z"
                :fill="`url(#${gradId})`"
            />
            <!-- Airplane Silhouette Cutting Horizon -->
            <path
                d="M 48 44 L 88 37 L 78 46 L 56 49 Z"
                fill="#13293D"
            />
            <!-- Lower Maritime Hull forming base of D -->
            <path
                d="M 18 68 L 34 62 V 68 H 52 C 66 68 74 62 79 54 L 84 54 C 81 74 66 84 46 84 H 18 V 68 Z"
                fill="#13293D"
            />
            <!-- Sea Wake Accent Line -->
            <path
                d="M 24 76 H 52 C 62 76 69 72 73 66"
                stroke="#67C7C6"
                stroke-width="3"
                stroke-linecap="round"
            />
        </svg>

        <span v-if="showWordmark" :class="wordmarkClass">
            {{ wordmarkText }}
        </span>
    </span>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import NavbarPill from '@/Components/Landing/NavbarPill.vue';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import FloatingUtilityWidget from '@/Components/Landing/FloatingUtilityWidget.vue';
import AsymmetricBentoGrid from '@/Components/Landing/AsymmetricBentoGrid.vue';
import FooterSection from '@/Components/Landing/FooterSection.vue';
import FixieAssistantFab from '@/Components/Landing/FixieAssistantFab.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    carriers: {
        type: Array,
        default: () => [],
    },
    locations: {
        type: Array,
        default: () => [],
    },
    tariffs: {
        type: Array,
        default: () => [],
    },
    sampleShipments: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);
const utilityRef = ref(null);

const scrollToUtility = (tab = 'track') => {
    if (utilityRef.value) {
        utilityRef.value.setActiveTab(tab);
    }
    const el = document.getElementById('utility-hub');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const handleFixieTrack = (code) => {
    scrollToUtility('track');
    setTimeout(() => {
        if (utilityRef.value) {
            utilityRef.value.triggerTrack(code);
        }
    }, 350);
};
</script>

<template>
    <Head title="Delixie - Global Shipment Visibility & Real-Time Logistics Platform" />

    <div class="min-h-screen font-sans bg-transparent text-[#FAEFDB] selection:bg-[#BE2388] selection:text-white overflow-x-hidden relative">
        <!-- 1. Floating Pill Navbar -->
        <NavbarPill :auth-user="authUser" @scroll-to="scrollToUtility" />

        <!-- 2. Section 1: Hero Section + Subtle Cargo Silhouettes -->
        <HeroSection :locations="locations" @scroll-to-track="scrollToUtility('track')" />

        <!-- 3. Section 2: Centered Tracking Command Bar & Key Stats Strip -->
        <FloatingUtilityWidget
            ref="utilityRef"
            :locations="locations"
            :sample-shipments="sampleShipments"
            :stats="stats"
        />

        <!-- 4. Section 3: Global Route Spotlight & Section 4: 3-Column Equal Capability Strip -->
        <AsymmetricBentoGrid :carriers="carriers" :locations="locations" :stats="stats" />

        <!-- 5. Section 5: Minimalist Single-Row Footer -->
        <FooterSection :auth-user="authUser" />

        <!-- 6. AI Copilot Fixie Floating Action Button & Mascot (Auto-Collapses on Scroll) -->
        <FixieAssistantFab
            :sample-shipments="sampleShipments"
            @trigger-track="handleFixieTrack"
            @scroll-to-tab="scrollToUtility"
        />
    </div>
</template>

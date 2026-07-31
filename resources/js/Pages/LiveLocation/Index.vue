<script setup>
import { Head, usePage, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import {
    MapPin, Navigation, Map as MapIcon,
    Users, Info, RefreshCw, ShieldCheck,
    LocateFixed, Loader2
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useAlert } from '@/Composables/useAlert';
import { usePoll } from '@inertiajs/vue3';

const props = defineProps({
    locations: Array,
    translations: Object,
});

// Poll for location updates every 5 seconds
usePoll(5000, {
    only: ['locations'],
    onSuccess: () => {
        updateMarkers();
    }
});

const mapContainer = ref(null);
const map = ref(null);
const markers = ref({});
const isSharing = ref(false);
const watchId = ref(null);
const { auth } = usePage().props;
const { showAlert } = useAlert();

const loadLeaflet = () => {
    return new Promise((resolve) => {
        if (window.L) return resolve();

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = resolve;
        document.head.appendChild(script);
    });
};

const initMap = () => {
    if (!mapContainer.value || !window.L) return;

    // Default to Indonesia center
    map.value = window.L.map(mapContainer.value).setView([-2.5489, 118.0149], 5);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map.value);

    updateMarkers();
};

const updateMarkers = () => {
    if (!map.value || !window.L) return;

    props.locations.forEach(loc => {
        const markerKey = loc.memberid;

        if (markers.value[markerKey]) {
            markers.value[markerKey].setLatLng([loc.latitude, loc.longitude]);
        } else {
            const icon = window.L.divIcon({
                className: 'custom-div-icon',
                html: `
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full border-4 border-white shadow-lg overflow-hidden bg-sky-100">
                            ${loc.picture ? `<img src="${loc.picture}" class="w-full h-full object-cover">` : `<div class="w-full h-full flex items-center justify-center text-sky-500 font-bold">${loc.name.charAt(0)}</div>`} 
                        </div>
                        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-3 h-3 bg-white border-2 border-sky-500 rotate-45"></div>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });

            const seenTime = new Date(loc.updated_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const seenLabel = props.translations.seen_at.replace(':time', seenTime);

            const marker = window.L.marker([loc.latitude, loc.longitude], { icon })
                .bindTooltip(`
                    <div class="text-center px-2 py-1">
                        <p class="font-black uppercase text-xs text-slate-900 mb-0.5">${loc.name}</p>
                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">${seenLabel}</p>
                    </div>
                `, {
                    direction: 'top',
                    offset: [0, -40],
                    className: 'luxury-map-tooltip',
                    opacity: 1
                })
                .addTo(map.value);

            markers.value[markerKey] = marker;
        }
    });
};

const startSharing = () => {
    if (!navigator.geolocation) {
        console.warn('Geolocation is not supported by your browser');
        return;
    }

    isSharing.value = true;
    let firstUpdate = true;

    watchId.value = navigator.geolocation.watchPosition(
        (position) => {
            const { latitude, longitude, accuracy } = position.coords;

            // Send to server
            window.axios.post(route('live-location.update'), {
                latitude, longitude, accuracy
            }).then(() => {
                if (firstUpdate) {
                    router.reload({ only: ['locations'], onSuccess: () => updateMarkers() });
                    firstUpdate = false;
                }
            }).catch(error => {
                console.error('Failed to update location:', error);
            });

            // Center map on first position
            if (isSharing.value && firstUpdate) {
                map.value.setView([latitude, longitude], 13);
            }
        },
        (error) => {
            console.error('Location error:', error);
            stopSharing();
        },
        { enableHighAccuracy: true }
    );
};

const stopSharing = () => {
    if (watchId.value !== null) {
        navigator.geolocation.clearWatch(watchId.value);
        watchId.value = null;
    }
    isSharing.value = false;
};

onMounted(async () => {
    await loadLeaflet();
    initMap();
    if (auth.is_family_member) {
        startSharing();
    }
});

onUnmounted(() => {
    stopSharing();
});
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-rose-100 text-rose-600 rounded-2xl">
                        <MapPin :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <div v-if="auth.is_family_member && isSharing" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 shadow-sm shrink-0 animate-in fade-in zoom-in">       
                <div class="relative flex items-center justify-center">
                    <div class="absolute w-3 h-3 bg-emerald-400 rounded-full animate-ping"></div>
                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full relative z-10"></div>
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest">{{ translations.live_sharing_active }}</span>        
            </div>
        </div>

        <!-- Map Container -->
        <div class="relative bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden h-[70vh]">
            <div ref="mapContainer" class="w-full h-full z-0"></div>

            <!-- Overlay Info -->
            <div class="absolute bottom-8 left-8 z-[1000] flex flex-col gap-4">
                <div class="bg-white/80 backdrop-blur-md p-4 rounded-3xl border border-slate-100 shadow-xl max-w-xs animate-in fade-in slide-in-from-left-4 duration-700">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-xl">
                            <Users :size="18" />
                        </div>
                        <div>
                            <p class="font-black text-slate-900 text-[10px] uppercase tracking-widest mb-1">{{ translations.in_the_area }}</p>
                            <p class="text-sm font-bold text-slate-600">{{ translations.active_members.replace(':count', locations.length) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend Overlay -->
            <div class="absolute top-8 left-8 z-[1000] flex flex-col gap-2">
                <div class="bg-white/80 backdrop-blur-md px-4 py-2 rounded-full border border-slate-100 shadow-lg flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">{{ translations.real_time_sync }}</span>
                </div>
            </div>
        </div>

        <!-- Safety Notice -->
        <div class="mt-8 flex items-center justify-center gap-3 text-slate-400">
            <ShieldCheck :size="14" />
            <p class="text-[10px] font-bold uppercase tracking-widest">{{ translations.safety_notice }}</p>
        </div>
    </div>
</template>

<style>
/* Leaflet custom styles must be global or non-scoped for markers */
.custom-div-icon {
    background: transparent !important;
    border: none !important;
}

.leaflet-popup-content-wrapper {
    border-radius: 20px !important;
    padding: 0 !important;
    border: 2px solid #f1f5f9 !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
}

.leaflet-popup-tip-container {
    display: none !important;
}

.luxury-map-tooltip {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(8px) !important;
    border: 1px solid #f1f5f9 !important;
    border-radius: 16px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    padding: 4px !important;
}

.luxury-map-tooltip::before {
    display: none !important;
}

.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
</style>

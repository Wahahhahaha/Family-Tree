<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { User, Heart, ArrowDownCircle } from 'lucide-vue-next';

const props = defineProps({
    node: Object,
    isRoot: {
        type: Boolean,
        default: false
    },
    visibleMax: {
        type: Number,
        default: 999
    },
    translations: Object
});

const emit = defineEmits(['select-member', 'expand-down']);

const selectMember = (member) => {
    emit('select-member', member);
};

const calculateAge = (member) => {
    if (!member.birthdate) return null;

    const birth = new Date(member.birthdate);
    const end = member.life_status === 'deceased' && member.deaddate
        ? new Date(member.deaddate)
        : new Date();

    let age = end.getFullYear() - birth.getFullYear();
    const monthDiff = end.getMonth() - birth.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && end.getDate() < birth.getDate())) {
        age--;
    }

    return Math.max(0, age);
};

const calculateTimeSinceDeath = (deaddate) => {
    if (!deaddate) return null;

    const start = new Date(deaddate);
    const now = new Date();

    if (start > now) return null;

    let years = now.getFullYear() - start.getFullYear();
    let months = now.getMonth() - start.getMonth();
    let days = now.getDate() - start.getDate();

    if (days < 0) {
        months--;
        const prevMonth = new Date(now.getFullYear(), now.getMonth(), 0);
        days += prevMonth.getDate();
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    let result = [];
    if (years > 0) result.push(`${years}Y`);
    if (months > 0) result.push(`${months}M`);
    if (years === 0 && months === 0 && days >= 0) result.push(`${days}D`);

    return result.length > 0 ? result.join(' ') : (props.translations?.recently || 'Recently');
};

// Check if a child is a single parent child of the main member
const isSingleParentChildOfMain = (child) => {
    const mainId = Number(props.node.member.memberid);
    const pIds = (child.parent_ids || []).map(Number);
    const partnerIds = (props.node.partners || []).map(p => Number(p.memberid));
    return pIds.includes(mainId) && !pIds.some(pid => partnerIds.includes(pid));
};

// Check if a child belongs to a specific marriage
const isMarriageChildOfPartner = (child, partnerId) => {
    const mainId = Number(props.node.member.memberid);
    const pIds = (child.parent_ids || []).map(Number);
    return pIds.includes(mainId) && pIds.includes(Number(partnerId));
};

// Check if a child is a single parent child of a specific partner
const isSingleParentChildOfPartner = (child, partnerId) => {
    const mainId = Number(props.node.member.memberid);
    const targetPartnerId = Number(partnerId);
    const pIds = (child.parent_ids || []).map(Number);
    const otherPartnerIds = (props.node.partners || []).map(p => Number(p.memberid)).filter(id => id !== targetPartnerId);

    return pIds.includes(targetPartnerId) && !pIds.includes(mainId) && !pIds.some(pid => otherPartnerIds.includes(pid));
};

// Summary checks for stems
const hasSingleParentStemsMain = computed(() => (props.node.children || []).some(isSingleParentChildOfMain));   
const hasMarriageStems = (pId) => (props.node.children || []).some(c => isMarriageChildOfPartner(c, pId));      
const hasSingleParentStemsPartner = (pId) => (props.node.children || []).some(c => isSingleParentChildOfPartner(c, pId));

// Check if THIS node (the child) is a single parent child for coloring its own lines
const isSingleParentChildNode = computed(() => {
    return !props.isRoot && props.node.parent_ids?.length === 1;
});
</script>

<template>
    <li :class="{
        'root-node': isRoot,
        'is-single-parent-path': isSingleParentChildNode
    }">

        <div class="node-unit-wrapper flex flex-col items-center">
            <div class="family-unit flex items-center justify-center relative">

                <!-- Left Partner (if multiple partners) -->
                <template v-if="node.partners && node.partners.length > 1">
                    <div class="flex items-center">
                        <div class="card-column relative">
                            <div class="member-card member-card-box partner shadow-sm hover:shadow-xl transition-all duration-300 relative z-20"
                                :data-member-id="node.partners[0].memberid"
                                :class="{ 'grayscale bg-slate-50 border-slate-200': node.partners[0].life_status === 'deceased' }"
                                @click.stop="selectMember(node.partners[0])">

                                <div class="absolute -top-3 -left-3 px-3 h-6 bg-sky-400 text-white rounded-full flex items-center justify-center font-black text-[8px] uppercase tracking-widest shadow-lg border-2 border-white z-30 grayscale-0">
                                    {{ translations?.gen || 'Gen' }} {{ node.generation }}
                                </div>

                                <div class="img-frame" :class="{ 'grayscale border-slate-100': node.partners[0].life_status === 'deceased' }">
                                    <img v-if="node.partners[0].picture" :src="node.partners[0].picture" class="actual-img" @load="$emit('image-loaded')">      
                                    <div v-else class="img-placeholder"><User :size="24" /></div>
                                </div>
                                <div class="info-box">
                                    <h4 class="name-text">{{ node.partners[0].name }}</h4>
                                    <div class="flex flex-col items-center mt-0.5">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <p class="status-text">{{ translations?.[node.partners[0].life_status] || node.partners[0].life_status }}</p>
                                            <template v-if="calculateAge(node.partners[0]) !== null">
                                                <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                                <p class="status-text text-sky-600">{{ calculateAge(node.partners[0]) }} {{ translations?.yrs || 'YRS' }}</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bridge-column flex flex-col items-center mx-2">
                            <div class="marriage-bridge-container relative" :data-marriage-id="node.member.memberid + '-' + node.partners[0].memberid">
                                <div class="horizontal-bridge-line opacity-0"></div>
                                <div class="heart-badge"><Heart :size="10" fill="currentColor" /></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Main Member -->
                <div class="card-column relative">
                    <div class="member-card member-card-box main shadow-sm hover:shadow-xl transition-all duration-300 relative z-20"
                        :data-member-id="node.member.memberid"
                        :class="{ 'grayscale bg-slate-50 border-slate-200': node.member.life_status === 'deceased' }"
                        @click.stop="selectMember(node.member)">

                        <div class="absolute -top-3 -left-3 px-3 h-6 bg-sky-600 text-white rounded-full flex items-center justify-center font-black text-[8px] uppercase tracking-widest shadow-lg border-2 border-white z-30 grayscale-0">
                            {{ translations?.gen || 'Gen' }} {{ node.generation }}
                        </div>

                        <div class="img-frame" :class="{ 'grayscale border-slate-100': node.member.life_status === 'deceased' }">
                            <img v-if="node.member.picture" :src="node.member.picture" class="actual-img" @load="$emit('image-loaded')">      
                            <div v-else class="img-placeholder"><User :size="24" /></div>
                        </div>
                        <div class="info-box">
                            <h4 class="name-text">{{ node.member.name }}</h4>
                            <div class="flex flex-col items-center mt-0.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <p class="status-text">{{ translations?.[node.member.life_status] || node.member.life_status }}</p>
                                    <template v-if="calculateAge(node.member) !== null">
                                        <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                        <p class="status-text text-sky-600">{{ calculateAge(node.member) }} {{ translations?.yrs || 'YRS' }}</p>
                                    </template>
                                </div>
                                <p v-if="node.member.life_status === 'deceased' && calculateTimeSinceDeath(node.member.deaddate)" class="text-[6px] font-black text-slate-400 uppercase tracking-tighter mt-0.5">
                                    {{ translations?.gone_for?.replace(':time', calculateTimeSinceDeath(node.member.deaddate)) || `Gone for ${calculateTimeSinceDeath(node.member.deaddate)}` }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Partner (or only partner) -->
                <template v-if="node.partners && node.partners.length > 0">
                    <div class="flex items-center">
                        <div class="bridge-column flex flex-col items-center mx-2">
                            <div class="marriage-bridge-container relative" :data-marriage-id="node.member.memberid + '-' + (node.partners.length > 1 ? node.partners[1].memberid : node.partners[0].memberid)">
                                <div class="horizontal-bridge-line opacity-0"></div>
                                <div class="heart-badge"><Heart :size="10" fill="currentColor" /></div>
                            </div>
                        </div>

                        <div class="card-column relative">
                            <div class="member-card member-card-box partner shadow-sm hover:shadow-xl transition-all duration-300 relative z-20"
                                :data-member-id="node.partners.length > 1 ? node.partners[1].memberid : node.partners[0].memberid"
                                :class="{ 'grayscale bg-slate-50 border-slate-200': (node.partners.length > 1 ? node.partners[1] : node.partners[0]).life_status === 'deceased' }"
                                @click.stop="selectMember(node.partners.length > 1 ? node.partners[1] : node.partners[0])">

                                <div class="absolute -top-3 -left-3 px-3 h-6 bg-sky-400 text-white rounded-full flex items-center justify-center font-black text-[8px] uppercase tracking-widest shadow-lg border-2 border-white z-30 grayscale-0">
                                    {{ translations?.gen || 'Gen' }} {{ node.generation }}
                                </div>

                                <div class="img-frame" :class="{ 'grayscale border-slate-100': (node.partners.length > 1 ? node.partners[1] : node.partners[0]).life_status === 'deceased' }">
                                    <img v-if="(node.partners.length > 1 ? node.partners[1] : node.partners[0]).picture" :src="(node.partners.length > 1 ? node.partners[1] : node.partners[0]).picture" class="actual-img" @load="$emit('image-loaded')">      
                                    <div v-else class="img-placeholder"><User :size="24" /></div>
                                </div>
                                <div class="info-box">
                                    <h4 class="name-text">{{ (node.partners.length > 1 ? node.partners[1] : node.partners[0]).name }}</h4>
                                    <div class="flex flex-col items-center mt-0.5">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <p class="status-text">{{ translations?.[(node.partners.length > 1 ? node.partners[1] : node.partners[0]).life_status] || (node.partners.length > 1 ? node.partners[1] : node.partners[0]).life_status }}</p>
                                            <template v-if="calculateAge(node.partners.length > 1 ? node.partners[1] : node.partners[0]) !== null">
                                                <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                                <p class="status-text text-sky-600">{{ calculateAge(node.partners.length > 1 ? node.partners[1] : node.partners[0]) }} {{ translations?.yrs || 'YRS' }}</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Children expansion / list -->
            <template v-if="node.children && node.children.length">
                <div v-if="node.generation >= visibleMax" class="pt-[60px] relative z-30 flex justify-center w-full">
                    <button @click.stop="emit('expand-down')" class="px-6 py-3 bg-sky-50 text-sky-600 border border-sky-100 rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-sm hover:bg-sky-100 transition-all flex items-center gap-2 group">
                        <ArrowDownCircle :size="14" class="group-hover:translate-y-0.5 transition-transform" /> 
                        {{ translations?.view_descendants?.replace(':count', node.children.length) || `View ${node.children.length} Descendants` }}
                    </button>
                </div>
                <ul v-else class="children-list-wrapper">
                    <TreeNode v-for="child in node.children"
                            :key="child.member.memberid"
                            :node="child"
                            :visible-max="visibleMax"
                            :translations="translations"
                            @select-member="(m) => emit('select-member', m)"
                            @expand-down="emit('expand-down')"
                            @image-loaded="$emit('image-loaded')" />
                </ul>
            </template>
        </div>
    </li>
</template>

<style scoped>
li {
    text-align: center;
    list-style-type: none;
    position: relative;
    padding: 60px 10px 0 10px;
    display: table-cell;
    vertical-align: top;
}

li:only-child { padding-top: 0; }

ul.children-list-wrapper {
    padding-top: 60px;
    position: relative;
    display: table;
    margin: 0 auto;
    width: 100%;
}

.marriage-bridge-container {
    width: 40px;
    height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.horizontal-bridge-line {
    width: 100%;
    height: 2px;
    background: #cbd5e1;
}
.heart-badge {
    position: absolute;
    background: white;
    padding: 3px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    color: #f43f5e;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    z-index: 30;
}

.member-card-box {
    width: 160px;
    height: 120px;
    background: white;
    border-radius: 28px;
    border: 2px solid #f1f5f9;
    padding: 14px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.member-card-box.main { border-color: #e2e8f0; }
.member-card-box.partner { border-color: #f8fafc; }

.img-frame {
    width: 50px;
    height: 50px;
    margin-bottom: 8px;
    border-radius: 16px;
    overflow: hidden;
    background: #f8fafc;
    border: 2px solid white;
}
.actual-img { width: 100%; height: 100%; object-fit: cover; }
.img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; }

.name-text {
    font-size: 10px;
    font-weight: 900;
    color: #0f172a;
    text-transform: uppercase;
    text-align: center;
    line-height: 1.2;
}
.status-text {
    font-size: 8px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
}
</style>

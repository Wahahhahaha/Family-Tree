<script setup>
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import { 
    ShieldCheck, Key, UserCheck, 
    Users, ShieldAlert, CheckCircle2,
    XCircle, Info, Lock, Save, Loader2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    permissions: Array,
    roles: Array,
    levels: Array,
    rolePermissions: Array,
    levelPermissions: Array,
    translations: Object,
});

const isUpdating = ref(false);

const checkPermission = (type, id, permId) => {
    if (type === 'role') {
        return props.rolePermissions.some(rp => rp.role_id === id && rp.permission_id === permId);
    }
    return props.levelPermissions.some(lp => lp.level_id === id && lp.permission_id === permId);
};

const togglePermission = (type, id, permId) => {
    const currentValue = checkPermission(type, id, permId);

    router.post(route('permissions.update'), {
        type: type,
        id: id,
        permission_id: permId,
        value: !currentValue
    }, {
        preserveScroll: true,
        onStart: () => isUpdating.value = true,
        onFinish: () => isUpdating.value = false,
    });
};

const permissionGroups = computed(() => {
    const groups = {};
    props.permissions.forEach(p => {
        if (!groups[p.group]) groups[p.group] = [];
        groups[p.group].push(p);
    });
    return groups;
});
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-black text-slate-900 flex items-center gap-3 mb-4">
                    <div class="p-2.5 bg-indigo-100 text-indigo-600 rounded-2xl">
                        <ShieldCheck :size="32" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 font-medium leading-relaxed">
                    {{ translations.desc }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div v-if="isUpdating" class="flex items-center gap-2 px-6 py-4 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100 shadow-sm animate-pulse">
                    <Loader2 :size="16" class="animate-spin" />
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ translations.syncing }}</span>
                </div>
                <div v-else class="px-6 py-4 bg-white border border-slate-100 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations.secure }}</span>
                </div>
            </div>
        </div>

        <!-- Permission Table -->
        <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="p-10 text-left w-1/3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">{{ translations.capabilities }}</span>
                            </th>

                            <!-- Roles (Superadmin, Admin) -->
                            <th v-for="role in roles" :key="'role-'+role.roleid" class="p-10 text-center border-l border-slate-50">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-sm">
                                        <Lock v-if="role.rolename === 'Superadmin'" :size="18" />
                                        <Key v-else :size="18" />
                                    </div>
                                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">{{ role.rolename }}</span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ translations.system_role }}</span>
                                </div>
                            </th>

                            <!-- Levels (Family Member) -->
                            <th v-for="level in levels.filter(l => l.levelname === 'Family Member')" :key="'level-'+level.levelid" class="p-10 text-center border-l border-slate-50">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm">
                                        <Users :size="18" />
                                    </div>
                                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">{{ level.levelname }}</span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ translations.dynasty_level }}</span>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-for="(perms, group) in permissionGroups" :key="group">
                            <!-- Group Row -->
                            <tr class="bg-slate-50/30">
                                <td :colspan="roles.length + 2" class="px-10 py-4 border-y border-slate-50">    
                                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full"></div>
                                        {{ group }} {{ translations.management_suffix }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Permission Rows -->
                            <tr v-for="perm in perms" :key="perm.id" class="border-b border-slate-50 hover:bg-slate-50/20 transition-colors group">
                                <td class="px-10 py-8">
                                    <h4 class="text-sm font-black text-slate-700 uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ perm.name }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">slug: {{ perm.slug }}</p>
                                </td>

                                <!-- Role Checkboxes -->
                                <td v-for="role in roles" :key="'role-cell-'+role.roleid" class="px-10 py-8 text-center border-l border-slate-50">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="checkPermission('role', role.roleid, perm.id)"
                                            @change="togglePermission('role', role.roleid, perm.id)"
                                            class="sr-only peer"
                                            :disabled="isUpdating || (role.rolename === 'Superadmin')"
                                        >
                                        <div class="w-12 h-6 bg-slate-100 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 shadow-inner"></div>

                                        <!-- Lock icon for Superadmin -->
                                        <div v-if="role.rolename === 'Superadmin'" class="absolute -top-1 -right-1 text-amber-500">
                                            <ShieldAlert :size="10" />
                                        </div>
                                    </label>
                                </td>

                                <!-- Level Checkboxes -->
                                <td v-for="level in levels.filter(l => l.levelname === 'Family Member')" :key="'level-cell-'+level.levelid" class="px-10 py-8 text-center border-l border-slate-50">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="checkPermission('level', level.levelid, perm.id)"
                                            @change="togglePermission('level', level.levelid, perm.id)"
                                            class="sr-only peer"
                                            :disabled="isUpdating"
                                        >
                                        <div class="w-12 h-6 bg-slate-100 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                                    </label>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer Legend -->
            <div class="px-10 py-8 bg-slate-900 text-white flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ translations.role_authorized }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-emerald-600 rounded-full"></div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ translations.level_authorized }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <ShieldAlert :size="14" class="text-amber-500" />
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ translations.immutable_access }}</span>
                    </div>
                </div>

                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 italic">
                    {{ translations.audit_note }}
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}

/* Custom switch animations */
.peer:checked ~ div::after {
    background-color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>

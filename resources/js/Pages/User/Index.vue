<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserCog, Plus, Search, Shield, Trash2, Edit, RotateCcw, X, Key, User, Info } from 'lucide-vue-next';
import { useAlert } from '@/Composables/useAlert';

const props = defineProps({
    users: Array,
    levels: Array,
    roles: Array,
    hasSuperadmin: Boolean,
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedUser = ref(null);
const { showConfirm } = useAlert();

const addForm = useForm({
    username: '',
    name: '',
    email: '',
    phonenumber: '',
    roleid: '',
});

const editForm = useForm({
    username: '',
    name: '',
    email: '',
    phonenumber: '',
    roleid: '',
});

const openAddModal = () => {
    addForm.reset();
    addForm.clearErrors();
    showAddModal.value = true;
};

const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.username = user.username;
    editForm.name = user.name || '';
    editForm.email = user.email || '';
    editForm.phonenumber = user.phonenumber || '';
    // Ensure roleid is treated as a number for the select value binding
    editForm.roleid = user.roleid ? Number(user.roleid) : '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitAddUser = () => {
    addForm.post(route('users.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
        preserveScroll: true,
    });
};

const submitEditUser = () => {
    editForm.put(route('users.update', selectedUser.value.userid), {
        onSuccess: () => {
            showEditModal.value = false;
        },
        preserveScroll: true,
    });
};

const handleResetPassword = async (user) => {
    if (await showConfirm(`Are you sure you want to reset password for "${user.username}" to default (password)?`)) {
        router.post(route('users.reset-password', user.userid), {}, {
            preserveScroll: true,
        });
    }
};

const handleDeleteUser = async (user) => {
    if (await showConfirm(`Are you sure you want to delete user "${user.username}"?`)) {
        router.delete(route('users.destroy', user.userid), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head title="User Data Management" />

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 text-indigo-600 rounded-xl">
                        <UserCog :size="24" />
                    </div>
                    User Data
                </h1>
                <p class="text-slate-500 mt-2 font-medium">Manage system access and member accounts.</p>
            </div>
            
            <button @click="openAddModal" class="flex items-center gap-2 px-6 py-3 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all">
                <Plus :size="18" /> Add New User
            </button>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Table Toolbar -->
            <div class="p-6 border-b border-slate-50 flex flex-col sm:flex-row gap-4 justify-between items-center">
                <div class="relative w-full sm:w-96">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input type="text" placeholder="Search by username or level..." class="w-full pl-12 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-sky-50 focus:border-sky-500 outline-none transition-all text-sm font-medium">
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50">Username</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50">Access Level</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="user in users" :key="user.userid" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                                        <Shield :size="20" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 leading-tight">{{ user.username }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ user.name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    {{ user.rolename || user.levelname || 'Regular User' }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <button @click="handleResetPassword(user)" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Reset Password to Default">
                                        <RotateCcw :size="18" />
                                    </button>
                                    <button @click="openEditModal(user)" class="p-2 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-all" title="Edit User">
                                        <Edit :size="18" />
                                    </button>
                                    <button @click="handleDeleteUser(user)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete User">
                                        <Trash2 :size="18" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="3" class="px-6 py-20 text-center text-slate-400 font-medium">
                                No users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Table Footer (Pagination Placeholder) -->
            <div class="p-6 bg-slate-50/30 border-t border-slate-50 flex justify-between items-center">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Total {{ users.length }} Users</p>
                <div class="flex gap-2">
                    <!-- Pagination buttons could go here -->
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>
            
            <div class="relative bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-3">
                        <div class="p-2 bg-sky-100 text-sky-600 rounded-xl">
                            <Plus :size="20" />
                        </div>
                        Add New User
                    </h3>
                    <button @click="showAddModal = false" class="p-2 hover:bg-slate-200 rounded-full transition-colors">
                        <X :size="20" class="text-slate-400" />
                    </button>
                </div>

                <form @submit.prevent="submitAddUser" class="p-8 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                            <div class="relative">
                                <User class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                <input v-model="addForm.username" type="text" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" placeholder="username123" required>
                            </div>
                            <p v-if="addForm.errors.username" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ addForm.errors.username }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Full Name</label>
                            <div class="relative">
                                <Shield class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                <input v-model="addForm.name" type="text" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" placeholder="John Doe" required>
                            </div>
                            <p v-if="addForm.errors.name" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ addForm.errors.name }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                            <div class="relative">
                                <RotateCcw class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 rotate-90" :size="16" />
                                <input v-model="addForm.email" type="email" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" placeholder="john@example.com" required>
                            </div>
                            <p v-if="addForm.errors.email" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ addForm.errors.email }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Phone Number</label>
                            <div class="relative">
                                <Plus class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                <input v-model="addForm.phonenumber" type="text" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" placeholder="0812xxxx" required>
                            </div>
                            <p v-if="addForm.errors.phonenumber" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ addForm.errors.phonenumber }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Assigned Role</label>
                        <div class="relative">
                            <UserCog class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                            <select v-model="addForm.roleid" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold appearance-none bg-white" required>
                                <option value="" disabled>Select a role</option>
                                <template v-for="role in roles" :key="role.roleid">
                                    <option v-if="!(role.roleid === 1 && hasSuperadmin)" :value="role.roleid">
                                        {{ role.rolename }}
                                    </option>
                                </template>
                            </select>
                        </div>
                        <p v-if="addForm.errors.roleid" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ addForm.errors.roleid }}</p>
                    </div>

                    <div class="p-4 bg-sky-50 rounded-2xl border border-sky-100 mt-4">
                        <p class="text-[10px] text-sky-700 font-medium leading-relaxed">
                            <strong class="text-sky-800 uppercase tracking-tighter">Note:</strong> 
                            User will be created with default password <code class="bg-white px-1 rounded font-bold">password</code>.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showAddModal = false" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="addForm.processing" class="flex items-center gap-2 px-8 py-2.5 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all disabled:opacity-50">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>
            
            <div class="relative bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-3">
                        <div class="p-2 bg-sky-100 text-sky-600 rounded-xl">
                            <Edit :size="20" />
                        </div>
                        Edit User
                    </h3>
                    <button @click="showEditModal = false" class="p-2 hover:bg-slate-200 rounded-full transition-colors">
                        <X :size="20" class="text-slate-400" />
                    </button>
                </div>

                <form @submit.prevent="submitEditUser" class="p-8 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Username</label>
                            <div class="relative">
                                <User class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                <input v-model="editForm.username" type="text" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" required>
                            </div>
                            <p v-if="editForm.errors.username" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ editForm.errors.username }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Full Name</label>
                            <div class="relative">
                                <Shield class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                <input v-model="editForm.name" type="text" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" required>
                            </div>
                            <p v-if="editForm.errors.name" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ editForm.errors.name }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                            <div class="relative">
                                <RotateCcw class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 rotate-90" :size="16" />
                                <input v-model="editForm.email" type="email" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" required>
                            </div>
                            <p v-if="editForm.errors.email" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ editForm.errors.email }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Phone Number</label>
                            <div class="relative">
                                <Plus class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                                <input v-model="editForm.phonenumber" type="text" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold" required>
                            </div>
                            <p v-if="editForm.errors.phonenumber" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ editForm.errors.phonenumber }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Assigned Role</label>
                        <div class="relative">
                            <UserCog class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
                            <select v-model="editForm.roleid" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-50 outline-none transition-all text-sm font-bold appearance-none bg-white" required>
                                <option value="" disabled>Select a role</option>
                                <template v-for="role in roles" :key="role.roleid">
                                    <option v-if="!(role.roleid === 1 && hasSuperadmin && selectedUser?.roleid !== 1)" :value="role.roleid">
                                        {{ role.rolename }}
                                    </option>
                                </template>
                            </select>
                        </div>
                        <p v-if="editForm.errors.roleid" class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ editForm.errors.roleid }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-6">
                        <button type="button" @click="showEditModal = false" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="flex items-center gap-2 px-8 py-2.5 bg-sky-600 text-white rounded-xl font-bold hover:bg-sky-700 shadow-lg shadow-sky-100 transition-all disabled:opacity-50">
                            Update Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

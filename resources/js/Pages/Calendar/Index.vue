<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Calendar as CalendarIcon, 
    ChevronLeft, 
    ChevronRight, 
    MapPin, 
    Clock, 
    Info,
    CalendarDays,
    Cake,
    PartyPopper,
    User
} from 'lucide-vue-next';

const props = defineProps({
    events: Array,
    birthdays: Array,
    translations: Object
});

const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

const englishMonthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

const translatedMonthNames = computed(() => {
    return englishMonthNames.map(name => props.translations.months[name] || name);
});

const daysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();
const firstDayOfMonth = (month, year) => new Date(year, month, 1).getDay();

const calendarDays = computed(() => {
    const totalDays = daysInMonth(currentMonth.value, currentYear.value);
    const startDay = firstDayOfMonth(currentMonth.value, currentYear.value);
    const days = [];

    // Padding for empty days at start
    for (let i = 0; i < startDay; i++) {
        days.push({ day: null, fullDate: null });
    }

    // Actual days
    for (let i = 1; i <= totalDays; i++) {
        const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        const monthDay = `${String(currentMonth.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;    

        const dayEvents = props.events.filter(e => e.date === dateStr);
        const dayBirthdays = props.birthdays.filter(b => b.month_day === monthDay);

        days.push({
            day: i,
            fullDate: dateStr,
            isToday: dateStr === today.toISOString().split('T')[0],
            events: dayEvents,
            birthdays: dayBirthdays,
            totalItems: dayEvents.length + dayBirthdays.length
        });
    }

    return days;
});

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const selectedDateItems = ref(null);
const showItemsForDate = (day) => {
    if (day.totalItems > 0) {
        selectedDateItems.value = day;
    } else {
        selectedDateItems.value = null;
    }
};

const birthdaysThisMonth = computed(() => {
    return props.birthdays
        .filter(b => {
            const bMonth = parseInt(b.month_day.split('-')[0]) - 1;
            return bMonth === currentMonth.value;
        })
        .sort((a, b) => parseInt(a.month_day.split('-')[1]) - parseInt(b.month_day.split('-')[1]));
});

const eventsThisMonth = computed(() => {
    return props.events
        .filter(e => {
            const eDate = new Date(e.date);
            return eDate.getMonth() === currentMonth.value && eDate.getFullYear() === currentYear.value;        
        })
        .sort((a, b) => new Date(a.date) - new Date(b.date));
});
</script>

<template>
    <div class="page-wrapper py-10 px-4 sm:px-6 lg:px-8">
        <Head :title="translations.title" />

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                    <div class="p-2 bg-sky-100 text-sky-600 rounded-xl">
                        <CalendarDays :size="24" />
                    </div>
                    {{ translations.title }}
                </h1>
                <p class="text-slate-500 mt-2 font-medium">{{ translations.desc }}</p>
            </div>

            <div class="flex items-center bg-white border border-slate-100 p-1.5 rounded-2xl shadow-sm">        
                <button @click="prevMonth" class="p-2 hover:bg-slate-50 rounded-xl text-slate-400 hover:text-sky-600 transition-all">
                    <ChevronLeft :size="20" />
                </button>
                <div class="px-6 text-sm font-black text-slate-700 uppercase tracking-widest min-w-[160px] text-center">
                    {{ translatedMonthNames[currentMonth] }} {{ currentYear }}
                </div>
                <button @click="nextMonth" class="p-2 hover:bg-slate-50 rounded-xl text-slate-400 hover:text-sky-600 transition-all">
                    <ChevronRight :size="20" />
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Calendar Grid -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden overflow-x-auto">
                    <div class="min-w-[700px]">
                        <div class="grid grid-cols-7 border-b border-slate-50">
                            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day"    
                                class="py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                {{ translations.days[day] || day }}
                            </div>
                        </div>
                        <div class="grid grid-cols-7">
                            <div v-for="(item, index) in calendarDays" :key="index"
                                @click="showItemsForDate(item)"
                                class="min-h-[140px] p-4 border-r border-b border-slate-50 relative cursor-pointer transition-all hover:bg-slate-50/50"
                                :class="{ 'bg-slate-50/30': !item.day }">

                                <span v-if="item.day"
                                    class="text-sm font-bold"
                                    :class="item.isToday ? 'bg-sky-600 text-white w-7 h-7 flex items-center justify-center rounded-lg shadow-lg shadow-sky-100' : 'text-slate-400'">
                                    {{ item.day }}
                                </span>

                                <div v-if="item.day && item.totalItems > 0" class="mt-2 space-y-1">
                                    <!-- Birthdays first -->
                                    <div v-for="birthday in item.birthdays" :key="birthday.id"
                                        class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-tighter bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-1">
                                        <Cake :size="10" /> {{ birthday.name.split(' ')[0] }}
                                    </div>

                                    <!-- Events -->
                                    <div v-for="event in item.events" :key="event.id"
                                        class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-tighter shadow-sm"
                                        :class="event.status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-sky-50 text-sky-700 border border-sky-100'">
                                        {{ event.title }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Panel -->
            <div class="space-y-6">
                <!-- Selected Date Items -->
                <div v-if="selectedDateItems" class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 animate-in slide-in-from-right-4 duration-300">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">
                        {{ translations.on_date.replace(':day', selectedDateItems.day).replace(':month', translatedMonthNames[currentMonth]) }}
                    </h3>
                    <div class="space-y-4">
                        <!-- Birthdays in list -->
                        <div v-for="birthday in selectedDateItems.birthdays" :key="birthday.id" class="p-4 rounded-2xl bg-rose-50/50 border-2 border-rose-100 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-white shadow-sm border-2 border-white">
                                <img v-if="birthday.picture" :src="birthday.picture" class="w-full h-full object-cover">
                                <User v-else :size="20" class="m-auto mt-2 text-rose-200" />
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-rose-900 uppercase tracking-tight">{{ birthday.name }}</h4>
                                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest flex items-center gap-1 mt-0.5">
                                    <Cake :size="10" /> {{ translations.birthday }}
                                </p>
                            </div>
                        </div>

                        <!-- Events in list -->
                        <div v-for="event in selectedDateItems.events" :key="event.id" class="p-4 rounded-2xl border-2 border-slate-50 hover:border-sky-100 transition-all group">
                            <h4 class="font-bold text-slate-900 group-hover:text-sky-600 transition-colors uppercase text-sm tracking-tight">{{ event.title }}</h4>
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center gap-2 text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                                    <Clock :size="12" class="text-sky-400" /> {{ event.time }}
                                </div>
                                <div v-if="event.location" class="flex items-center gap-2 text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                                    <MapPin :size="12" class="text-rose-400" /> {{ event.location }}
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link :href="route('events.show', event.id)" class="text-[9px] font-black text-sky-600 uppercase tracking-[0.2em] hover:underline flex items-center gap-1">
                                    {{ translations.details }} <ChevronRight :size="10" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200 p-8 text-center">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 shadow-sm">
                        <Info :size="24" />
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-relaxed">{{ translations.select_date }}</p>
                </div>

                <!-- Birthdays Summary -->
                <div class="bg-gradient-to-br from-rose-500 to-rose-700 rounded-[2rem] p-6 text-white shadow-xl shadow-rose-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <PartyPopper :size="20" class="opacity-80" />
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-80">{{ translations.month_birthdays.replace(':month', translatedMonthNames[currentMonth]) }}</span>
                        </div>
                        <span class="text-[10px] font-black bg-white/20 px-2 py-0.5 rounded-lg">{{ birthdaysThisMonth.length }}</span>
                    </div>
                    <div v-if="birthdaysThisMonth.length" class="space-y-3">
                        <div v-for="b in birthdaysThisMonth.slice(0, 5)" :key="b.id" class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center text-[10px] font-black">
                                {{ b.month_day.split('-')[1] }}
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest truncate">{{ b.name }}</span>
                        </div>
                        <p v-if="birthdaysThisMonth.length > 5" class="text-[8px] font-black uppercase opacity-60 text-center pt-2">{{ translations.more_members.replace(':count', birthdaysThisMonth.length - 5) }}</p>
                    </div>
                    <p v-else class="text-[10px] font-bold uppercase opacity-80">{{ translations.no_birthdays }}</p>   
                </div>

                <!-- Events Summary -->
                <div class="bg-gradient-to-br from-sky-600 to-sky-800 rounded-[2rem] p-6 text-white shadow-xl shadow-sky-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <CalendarIcon :size="20" class="opacity-80" />
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-80">{{ translations.month_events.replace(':month', translatedMonthNames[currentMonth]) }}</span>
                        </div>
                        <span class="text-[10px] font-black bg-white/20 px-2 py-0.5 rounded-lg">{{ eventsThisMonth.length }}</span>
                    </div>
                    <div v-if="eventsThisMonth.length" class="space-y-3">
                        <div v-for="e in eventsThisMonth.slice(0, 5)" :key="e.id" class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center text-[10px] font-black text-center leading-tight">
                                {{ e.date.split('-')[2] }}
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest truncate">{{ e.title }}</span>
                        </div>
                        <p v-if="eventsThisMonth.length > 5" class="text-[8px] font-black uppercase opacity-60 text-center pt-2">{{ translations.more_events.replace(':count', eventsThisMonth.length - 5) }}</p>
                    </div>
                    <p v-else class="text-[10px] font-bold uppercase opacity-80">{{ translations.no_events }}</p>       
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
</style>

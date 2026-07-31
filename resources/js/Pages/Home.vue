<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import TreeNode from '@/Components/FamilyTree/TreeNode.vue';
import Modal from '@/Components/ui/Modal.vue';
import { 
    Network, Search, ZoomIn, ZoomOut, RotateCcw, 
    X, User, Mail, Phone, Calendar, Heart, 
    MapPin, Info, Briefcase, GraduationCap,
    Download, Loader2, ShieldCheck, Droplets,
    Globe, ExternalLink, Pencil, Save, RotateCw,
    Camera, Baby, UserPlus, ArrowUpCircle, HeartHandshake,
    GitCommit, Plus, Trash2, Ghost, Upload, FileText,
    ArrowDownCircle
} from 'lucide-vue-next';
import { toPng } from 'html-to-image';
import { useAlert } from '@/Composables/useAlert';

// Face Detection State
const isFaceApiLoaded = ref(false);
const isDetectingFace = ref(false);

const loadFaceApi = async () => {
    if (window.faceapi) return;

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
        script.onload = async () => {
            try {
                const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                isFaceApiLoaded.value = true;
                resolve();
            } catch (e) {
                console.error('Face API model load error:', e);
                reject(e);
            }
        };
        script.onerror = reject;
        document.head.appendChild(script);
    });
};

const props = defineProps({
    treeData: Array,
    socialPlatforms: Array,
    translations: Object,
});

const scale = ref(1);
const selectedMember = ref(null);
const isEditing = ref(false);
const showDeceasedModal = ref(false);
const showDeleteValidationModal = ref(false);
const addingRelativeType = ref(null);
const treeContainer = ref(null);
const svgCanvas = ref(null);
const isExporting = ref(false);
const photoInput = ref(null);
const photoPreview = ref(null);
const { showConfirm, showAlert } = useAlert();

const treeKey = ref(0);
const visibleMin = ref(1);
const visibleMax = ref(999);
const pathToMe = ref([]);
const auth = usePage().props.auth;
const currentUserId = auth.user?.userid;

const canDeleteMember = (member) => {
    if (!member || !auth.user) return false;
    const roleId = usePage().props.auth.roleid ? Number(usePage().props.auth.roleid) : null;
    const isFamilyMember = usePage().props.auth.is_family_member;
    const userMember = pathToMe.value?.[pathToMe.value.length - 1]?.member;
    const isSelf = (userMember && Number(member.memberid) === Number(userMember.memberid)) ||
                   (member.userid && currentUserId && Number(member.userid) === Number(currentUserId));

    if (isSelf) return false;
    if (roleId === 1 || roleId === 2) return true;
    if (isFamilyMember) {
        if (!userMember) return false;
        const isPartner = member.partners_list?.some(p => Number(p.memberid) === Number(userMember.memberid)) ||
                          userMember.partners_list?.some(p => Number(p.memberid) === Number(member.memberid));  
        if (isPartner) return true;
        const isChild = member.parent_relationships?.some(p => Number(p.parent_id) === Number(userMember.memberid));
        if (isChild) return true;
    }
    return false;
};

const findPathToUser = (nodes, userId, currentPath = []) => {
    if (!nodes) return null;
    const items = Array.isArray(nodes) ? nodes : [nodes];
    for (const node of items) {
        const newPath = [...currentPath, node];
        if (Number(node.member.userid) === Number(userId)) return newPath;
        if (node.partners && node.partners.some(p => Number(p.userid) === Number(userId))) return newPath;
        if (node.children) {
            const path = findPathToUser(node.children, userId, newPath);
            if (path) return path;
        }
    }
    return null;
};

const drawLines = () => {
    if (!svgCanvas.value || !treeContainer.value) return;

    const svg = svgCanvas.value;
    const container = treeContainer.value;
    
    while (svg.firstChild) {
        svg.removeChild(svg.firstChild);
    }

    const scrollWidth = container.scrollWidth;
    const scrollHeight = container.scrollHeight;
    
    svg.setAttribute('viewBox', `0 0 ${scrollWidth} ${scrollHeight}`);
    svg.style.width = `${scrollWidth}px`;
    svg.style.height = `${scrollHeight}px`;

    const createLine = (x1, y1, x2, y2, color = '#CBD5E1') => {
        const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', color);
        line.setAttribute('stroke-width', '2');
        line.setAttribute('stroke-linecap', 'square'); // Use square caps to avoid gaps
        line.classList.add('tree-connector-line');
        svg.appendChild(line);
    };

    const getCenterTop = (el) => {
        const r = el.getBoundingClientRect();
        const cr = container.getBoundingClientRect();
        return {
            x: (r.left + r.width / 2 - cr.left + container.scrollLeft) / scale.value,
            y: (r.top - cr.top + container.scrollTop) / scale.value
        };
    };

    const getCenterBottom = (el) => {
        const r = el.getBoundingClientRect();
        const cr = container.getBoundingClientRect();
        return {
            x: (r.left + r.width / 2 - cr.left + container.scrollLeft) / scale.value,
            y: (r.bottom - cr.top + container.scrollTop) / scale.value
        };
    };

    const traverse = (node) => {
        if (!node) return;

        const container = treeContainer.value;
        const memberEl = container.querySelector(`[data-member-id="${node.member.memberid}"]`);
        if (!memberEl) return;

        const mainPos = getCenterBottom(memberEl);
        const mainMidY = mainPos.y - 60; // Center height of the card (120px / 2)

        const marriages = [];
        if (node.partners && node.partners.length > 0) {
            node.partners.forEach(partner => {
                const partnerEl = container.querySelector(`[data-member-id="${partner.memberid}"]`);
                if (partnerEl) {
                    const pPos = getCenterBottom(partnerEl);
                    const bridgeX = (mainPos.x + pPos.x) / 2;
                    const bridgeY = mainMidY;
                    
                    // Marriage bridge in SVG
                    createLine(mainPos.x, bridgeY, pPos.x, bridgeY, '#0ea5e9');
                    
                    marriages.push({
                        ids: [Number(node.member.memberid), Number(partner.memberid)].sort((a,b) => a-b),
                        anchor: { x: bridgeX, y: bridgeY }
                    });
                }
            });
        }

        if (node.children && node.children.length > 0 && node.generation < visibleMax.value) {
            const groupedChildren = {
                single: [],
                marriages: {}
            };

            node.children.forEach(child => {
                const pIds = (child.parent_ids || []).map(Number);
                const mainId = Number(node.member.memberid);
                
                let foundMarriage = false;
                marriages.forEach(m => {
                    if (pIds.includes(m.ids[0]) && pIds.includes(m.ids[1])) {
                        const key = m.ids.join('-');
                        if (!groupedChildren.marriages[key]) groupedChildren.marriages[key] = [];
                        groupedChildren.marriages[key].push(child);
                        foundMarriage = true;
                    }
                });

                if (!foundMarriage && pIds.includes(mainId)) {
                    groupedChildren.single.push(child);
                }
            });

            // Draw for each marriage group
            Object.keys(groupedChildren.marriages).forEach(key => {
                const children = groupedChildren.marriages[key];
                const marriage = marriages.find(m => m.ids.join('-') === key);
                drawConnectorGroup(marriage.anchor, children, '#0ea5e9', 75); // 75px down from mid-card (mainPos.y + 15)
            });

            // Draw for single parent group
            if (groupedChildren.single.length > 0) {
                drawConnectorGroup(mainPos, groupedChildren.single, '#f43f5e', 40); // 40px down from bottom-card (mainPos.y + 40)
            }

            node.children.forEach(traverse);
        }
    };

    const drawConnectorGroup = (parentAnchor, children, color, offsetDown) => {
        const container = treeContainer.value;
        const childAnchors = [];
        children.forEach(child => {
            const childEl = container.querySelector(`[data-member-id="${child.member.memberid}"]`);
            if (childEl) {
                childAnchors.push(getCenterTop(childEl));
            }
        });

        if (childAnchors.length > 0) {
            const junctionY = Math.round(parentAnchor.y + offsetDown);
            const parentX = Math.round(parentAnchor.x);
            
            // Vertical down from parent anchor to junction height
            createLine(parentX, Math.round(parentAnchor.y), parentX, junctionY, color);

            const minX = Math.round(Math.min(...childAnchors.map(a => a.x)));
            const maxX = Math.max(...childAnchors.map(a => a.x));
            const centerX = Math.round((minX + maxX) / 2);

            // Horizontal connection from parent vertical line to the children's horizontal bar center (if needed)
            // Or just ensure the horizontal bar covers the parentX
            const barMinX = Math.min(minX, parentX);
            const barMaxX = Math.max(maxX, parentX);

            // Horizontal junction bar spanning all children AND the parent connection point
            createLine(barMinX, junctionY, barMaxX, junctionY, color);

            // Vertical down to each child from the bar
            childAnchors.forEach(anchor => {
                const childX = Math.round(anchor.x);
                createLine(childX, junctionY, childX, Math.round(anchor.y), color);
            });
        }
    };

    const traverseRoot = (nodes) => {
        if (!nodes) return;
        const items = Array.isArray(nodes) ? nodes : [nodes];
        items.forEach(traverse);
    };

    traverseRoot(props.treeData);
};

onMounted(() => {
    const roleId = auth.user?.roleid;
    const isHighAuthority = roleId === 1 || roleId === 2;
    if (props.treeData && props.treeData.length > 0 && currentUserId) {
        const path = findPathToUser(props.treeData, currentUserId);
        if (path && path.length > 0 && !isHighAuthority) {
            pathToMe.value = path;
            const meNode = path[path.length - 1];
            const centerGen = meNode.generation;
            visibleMin.value = Math.max(1, centerGen - 2);
            visibleMax.value = centerGen + 2;
        } else {
            visibleMin.value = 1;
            visibleMax.value = 999;
            pathToMe.value = [];
        }
    } else {
        visibleMin.value = 1;
        visibleMax.value = 999;
    }

    window.addEventListener('resize', drawLines);
    setTimeout(() => {
        nextTick(() => {
            drawLines();
        });
    }, 1000);
});

onUnmounted(() => {
    window.removeEventListener('resize', drawLines);
});

watch([scale, treeKey, visibleMin, visibleMax], () => {
    nextTick(() => {
        setTimeout(drawLines, 100);
    });
});

const visibleRoots = computed(() => {
    if (!props.treeData || props.treeData.length === 0) return [];
    if (pathToMe.value.length > 0 && visibleMax.value < 900) {
        const rootInPath = pathToMe.value.find(n => n.generation === visibleMin.value);
        return rootInPath ? [rootInPath] : [props.treeData[0]];
    }
    return props.treeData;
});

const expandUp = () => { visibleMin.value = Math.max(1, visibleMin.value - 3); };
const expandDown = () => { visibleMax.value += 3; };

const form = useForm({
    name: '', birthdate: '', birthplace: '', bloodtype: '', job: '', education_status: '',
    email: '', phonenumber: '', address: '', address_detail: '', country: 'Indonesia',
    province: '', city: '', life_status: 'alive', marital_status: 'single', gender: 'male',
    deaddate: '', grave_location_url: '', picture: null, _method: 'PUT',
    related_to: null, second_parent_id: '', relation_type: null, primary_parent_id: '',
    secondary_parent_id: '', social_media: [],
});

const allCountries = [
    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan",
    "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bh Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi",
    "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic",
    "Denmark", "Djibouti", "Dominica", "Dominican Republic",
    "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia",
    "Fiji", "Finland", "France",
    "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana",
    "Haiti", "Honduras", "Hungary",
    "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Ivory Coast",
    "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan",
    "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
    "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar",
    "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway",
    "Oman", "Pakistan", "Palau", "Palestine State", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
    "Qatar", "Romania", "Russia", "Rwanda",
    "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria",
    "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu",
    "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan",
    "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
];

const hasDetailedProvinces = computed(() => form.country === 'Indonesia');
const provinces = ["Aceh", "Bali", "Banten", "Bengkulu", "DI Yogyakarta", "DKI Jakarta", "Gorontalo", "Jambi", "Jawa Barat", "Jawa Tengah", "Jawa Timur", "Kalimantan Barat", "Kalimantan Selatan", "Kalimantan Tengah", "Kalimantan Timur", "Kalimantan Utara", "Kepulauan Bangka Belitung", "Kepulauan Riau", "Lampung", "Maluku", "Maluku Utara", "Nusa Tenggara Barat", "Nusa Tenggara Timur", "Papua", "Papua Barat", "Papua Barat Daya", "Papua Pegunungan", "Papua Selatan", "Papua Tengah", "Riau", "Sulawesi Barat", "Sulawesi Selatan", "Sulawesi Tengah", "Sulawesi Tenggara", "Sulawesi Utara", "Sumatera Barat", "Sumatera Selatan", "Sumatera Utara"];

const cityData = {
    "Aceh": ["Banda Aceh", "Langsa", "Lhokseumawe", "Meulaboh", "Sabang", "Subulussalam"],
    "Bali": ["Denpasar", "Badung", "Bangli", "Buleleng", "Gianyar", "Jembrana", "Karangasem", "Klungkung", "Tabanan"],
    "Banten": ["Tangerang", "Serang", "Cilegon", "Tangerang Selatan", "Lebak", "Pandeglang"],
    "Bengkulu": ["Bengkulu City", "Bengkulu Selatan", "Bengkulu Tengah", "Bengkulu Utara", "Kaur", "Kepahiang", "Lebong", "Mukomuko", "Rejang Lebong", "Seluma"],
    "DI Yogyakarta": ["Yogyakarta", "Bantul", "Gunungkidul", "Kulon Progo", "Sleman"],
    "DKI Jakarta": ["Jakarta Pusat", "Jakarta Utara", "Jakarta Barat", "Jakarta Selatan", "Jakarta Timur", "Kepulauan Seribu"],
    "Gorontalo": ["Gorontalo City", "Boalemo", "Bone Bolango", "Gorontalo Utara", "Pohuwato"],
    "Jambi": ["Jambi City", "Sungaipenuh", "Batanghari", "Bungo", "Kerinci", "Merangin", "Muaro Jambi", "Sarolangun", "Tanjung Jabung Barat", "Tanjung Jabung Timur", "Tebo"],
    "Jawa Barat": ["Bandung", "Bekasi", "Bogor", "Cimahi", "Cirebon", "Depok", "Sukabumi", "Tasikmalaya", "Banjar", "Subang", "Sumedang", "Garut", "Indramayu", "Karawang", "Kuningan", "Majalengka", "Purwakarta"],
    "Jawa Tengah": ["Semarang", "Magelang", "Pekalongan", "Salatiga", "Surakarta", "Tegal", "Banyumas", "Batang", "Blora", "Boyolali", "Brebes", "Cilacap", "Demak", "Grobogan", "Jepara", "Karanganyar", "Kebumen", "Kendal", "Klaten", "Kudus", "Pati", "Pemalang", "Purbalingga", "Purworejo", "Rembang", "Sragen", "Sukoharjo", "Temanggung", "Wonogiri", "Wonosobo"],
    "Jawa Timur": ["Surabaya", "Batu", "Blitar", "Kediri", "Madiun", "Malang", "Mojokerto", "Pasuruan", "Probolinggo", "Banyuwangi", "Bojonegoro", "Bondowoso", "Gresik", "Jember", "Jombang", "Lamongan", "Lumajang", "Magetan", "Nganjuk", "Ngawi", "Pacitan", "Pamekasan", "Ponorogo", "Sampang", "Sidoarjo", "Situbondo", "Sumenep", "Trenggalek", "Tuban", "Tulungagung"],
    "Kalimantan Barat": ["Pontianak", "Singkawang", "Bengkayang", "Kapuas Hulu", "Kayong Utara", "Ketapang", "Kubu Raya", "Landak", "Melawi", "Mempawah", "Sambas", "Sanggau", "Sekadau", "Sintang"],
    "Kalimantan Selatan": ["Banjarmasin", "Banjarbaru", "Balangan", "Banjar", "Barito Kuala", "Hulu Sungai Selatan", "Hulu Sungai Tengah", "Hulu Sungai Utara", "Kotabaru", "Tabalong", "Tanah Bumbu", "Tanah Laut", "Tapin"],
    "Kalimantan Tengah": ["Palangka Raya", "Barito Selatan", "Barito Timur", "Barito Utara", "Gunung Mas", "Kapuas", "Katingan", "Kotawaringin Barat", "Kotawaringin Timur", "Lamandau", "Murung Raya", "Pulang Pisau", "Sukamara", "Seruyan"],
    "Kalimantan Timur": ["Samarinda", "Balikpapan", "Bontang", "Berau", "Kutai Barat", "Kutai Kartanegara", "Kutai Timur", "Mahakam Ulu", "Paser", "Penajam Paser Utara"],
    "Kalimantan Utara": ["Tarakan", "Bulungan", "Malinau", "Nunukan", "Tana Tidung"],
    "Kepulauan Bangka Belitung": ["Pangkalpinang", "Bangka", "Bangka Barat", "Bangka Selatan", "Bangka Tengah", "Belitung", "Belitung Timur"],
    "Kepulauan Riau": ["Batam", "Tanjungpinang", "Bintan", "Karimun", "Kepulauan Anambas", "Lingga", "Natuna"],
    "Lampung": ["Bandar Lampung", "Metro", "Lampung Barat", "Lampung Selatan", "Lampung Tengah", "Lampung Timur", "Lampung Utara", "Mesuji", "Pesawaran", "Pesisir Barat", "Pringsewu", "Tanggamus", "Tulang Bawang", "Tulang Bawang Barat", "Way Kanan"],
    "Maluku": ["Ambon", "Tual", "Buru", "Buru Selatan", "Kepulauan Aru", "Kepulauan Tanimbar", "Maluku Barat Daya", "Maluku Tengah", "Maluku Tenggara", "Seram Bagian Barat", "Seram Bagian Timur"],
    "Maluku Utara": ["Ternate", "Tidore Kepulauan", "Halmahera Barat", "Halmahera Tengah", "Halmahera Utara", "Halmahera Selatan", "Kepulauan Sula", "Halmahera Timur", "Pulau Morotai", "Pulau Taliabu"],
    "Nusa Tenggara Barat": ["Mataram", "Bima", "Dompu", "Lombok Barat", "Lombok Tengah", "Lombok Timur", "Lombok Utara", "Sumbawa", "Sumbawa Barat"],
    "Nusa Tenggara Timur": ["Kupang", "Alor", "Belu", "Ende", "Flores Timur", "Kupang Regency", "Lembata", "Malaka", "Manggarai", "Manggarai Barat", "Manggarai Timur", "Nagekeo", "Ngada", "Rote Ndao", "Sabu Raijua", "Sikka", "Sumba Barat", "Sumbawa Barat Daya", "Sumba Tengah", "Sumba Timur", "Timor Tengah Selatan", "Timor Tengah Utara"],
    "Papua": ["Jayapura", "Biak Numfor", "Keerom", "Kepulauan Yapen", "Mamberamo Raya", "Sarmi", "Supiori", "Waropen"],
    "Papua Barat": ["Manokwari", "Fakfak", "Kaimana", "Manokwari Selatan", "Pegunungan Arfak", "Teluk Bintuni", "Teluk Wondama"],
    "Papua Barat Daya": ["Sorong", "Maybrat", "Raja Ampat", "Sorong Regency", "Sorong Selatan", "Tambrauw"],
    "Papua Pegunungan": ["Wamena", "Jayawijaya", "Lanny Jaya", "Mamberamo Tengah", "Nduga", "Pegunungan Bintang", "Tolikara", "Yahukimo", "Yalimo"],
    "Papua Selatan": ["Merauke", "Asmat", "Boven Digoel", "Mappi"],
    "Papua Tengah": ["Nabire", "Deiyai", "Dogiyai", "Intan Jaya", "Mimika", "Paniai", "Puncak", "Puncak Jaya"],
    "Riau": ["Pekanbaru", "Dumai", "Bengkalis", "Indragiri Hilir", "Indragiri Hulu", "Kampar", "Kepulauan Meranti", "Kuantan Singingi", "Pelalawan", "Rokan Hilir", "Rokan Hulu", "Siak"],
    "Sulawesi Barat": ["Mamuju", "Majene", "Mamasa", "Mamuju Tengah", "Pasangkayu", "Polewali Mandar"],
    "Sulawesi Selatan": ["Makassar", "Palopo", "Parepare", "Bantaeng", "Barru", "Bone", "Bulukumba", "Enrekang", "Gowa", "Jeneponto", "Kepulauan Selayar", "Luwu", "Luwu Timur", "Luwu Utara", "Maros", "Pangkajene dan Kepulauan", "Pinrang", "Sidenreng Rappang", "Sinjai", "Soppeng", "Takalar", "Tana Toraja", "Toraja Utara", "Wajo"],
    "Sulawesi Tengah": ["Palu", "Banggai", "Banggai Kepulauan", "Banggai Laut", "Buol", "Donggala", "Morowali", "Morowali Utara", "Parigi Moutong", "Poso", "Sigi", "Tojo Una-Una", "Toli-Toli"],
    "Sulawesi Tenggara": ["Kendari", "Baubau", "Bombana", "Buton", "Buton Selatan", "Buton Tengah", "Buton Utara", "Kolaka", "Kolaka Timur", "Kolaka Utara", "Konawe", "Konawe Kepulauan", "Konawe Selatan", "Konawe Utara", "Muna", "Muna Barat", "Wakatobi"],
    "Sulawesi Utara": ["Manado", "Bitung", "Kotamobagu", "Tomohon", "Bolaang Mongondow", "Bolaang Mongondow Selatan", "Bolaang Mongondow Timur", "Bolaang Mongondow Utara", "Kepulauan Sangihe", "Kepulauan Siau Tagulandang Biaro", "Kepulauan Talaud", "Minahasa", "Minahasa Selatan", "Minahasa Tenggara", "Minahasa Utara"],
    "Sumatera Barat": ["Padang", "Bukittinggi", "Padang Panjang", "Pariaman", "Payakumbuh", "Sawahlunto", "Solok", "Agam", "Dharmasraya", "Kepulauan Mentawai", "Lima Puluh Kota", "Padang Pariaman", "Pasaman", "Pasaman Barat", "Pesisir Selatan", "Sijunjung", "Solok Regency", "Solok Selatan", "Tanah Datar"],
    "Sumatera Selatan": ["Palembang", "Lubuklinggau", "Pagar Alam", "Prabumulih", "Banyuasin", "Empat Lawang", "Lahat", "Muara Enim", "Musi Banyuasin", "Musi Rawas", "Musi Rawas Utara", "Ogan Ilir", "Ogan Komering Ilir", "Ogan Komering Ulu", "Ogan Komering Ulu Selatan", "Ogan Komering Ulu Timur", "Penukal Abab Lematang Ilir"],
    "Sumatera Utara": ["Medan", "Binjai", "Gunungsitoli", "Padangsidimpuan", "Pematangsiantar", "Sibolga", "Tanjungbalai", "Tebing Tinggi", "Asahan", "Batubara", "Dairi", "Deli Serdang", "Humbang Hasundutan", "Karo", "Labuhanbatu", "Labuhanbatu Selatan", "Labuhanbatu Utara", "Langkat", "Mandailing Natal", "Nias", "Nias Barat", "Nias Selatan", "Nias Utara", "Padang Lawas", "Padang Lawas Utara", "Pakpak Bharat", "Samosir", "Serdang Bedagai", "Simalungun", "Tapanuli Selatan", "Tapanuli Tengah", "Tapanuli Utara", "Toba Samosir"]
};

const cities = computed(() => {
    if (!form.province) return [];
    return cityData[form.province] || [];
});

const handleSelectMember = (member) => {
    selectedMember.value = member;
    isEditing.value = false;
    addingRelativeType.value = null;
    photoPreview.value = member.picture;
};

const closePanel = () => {
    selectedMember.value = null;
    isEditing.value = false;
    addingRelativeType.value = null;
};

const startEditing = () => {
    isEditing.value = true;
    addingRelativeType.value = null;
    
    const primaryRel = selectedMember.value.parent_relationships?.[0];
    const secondaryRel = selectedMember.value.parent_relationships?.[1];

    form.name = selectedMember.value.name;
    form.gender = selectedMember.value.gender;
    form.life_status = selectedMember.value.life_status;
    form.marital_status = selectedMember.value.marital_status;
    form.birthdate = selectedMember.value.birthdate ? selectedMember.value.birthdate.split(/[T ]/)[0] : '';
    form.bloodtype = selectedMember.value.bloodtype;
    form.birthplace = selectedMember.value.birthplace;
    form.job = selectedMember.value.job;
    form.education_status = selectedMember.value.education_status;
    form.email = selectedMember.value.email;
    form.phonenumber = selectedMember.value.phonenumber;
    form.address = selectedMember.value.address;
    form.address_detail = selectedMember.value.address_detail;
    form.country = selectedMember.value.country || 'Indonesia';
    form.province = selectedMember.value.province;
    form.city = selectedMember.value.city;
    form.deaddate = selectedMember.value.deaddate ? selectedMember.value.deaddate.split(/[T ]/)[0] : '';
    form.grave_location_url = selectedMember.value.grave_location_url;
    form.picture = null;
    form._method = 'PUT';
    form.related_to = null;
    form.relation_type = null;
    form.primary_parent_id = primaryRel ? primaryRel.parent_id : '';
    form.secondary_parent_id = secondaryRel ? secondaryRel.parent_id : '';
    form.social_media = selectedMember.value.social_media ? selectedMember.value.social_media.map(s => ({
        ownid: s.ownid, socialid: s.socialid, link: s.link
    })) : [];
    photoPreview.value = selectedMember.value.picture;
};

const startAddingRelative = (type) => {
    addingRelativeType.value = type;
    isEditing.value = true;
    form.name = '';
    form.gender = type === 'spouse' ? (selectedMember.value.gender === 'male' ? 'female' : 'male') : 'male';
    form.life_status = 'alive';
    form.marital_status = type === 'spouse' ? 'married' : 'single';
    form.birthdate = '';
    form.bloodtype = '';
    form.birthplace = '';
    form.job = '';
    form.education_status = '';
    form.email = '';
    form.phonenumber = '';
    form.address = '';
    form.address_detail = '';
    form.country = 'Indonesia';
    form.province = '';
    form.city = '';
    form.deaddate = '';
    form.grave_location_url = '';
    form.picture = null;
    form._method = 'POST';
    form.related_to = selectedMember.value.memberid;
    form.relation_type = type;
    if (type === 'child') {
        form.primary_parent_id = selectedMember.value.memberid;
        form.secondary_parent_id = selectedMember.value.partners_list?.[0]?.memberid || '';
    } else if (type === 'parent') {
        form.related_to = selectedMember.value.memberid;
    }
    form.social_media = [];
    photoPreview.value = null;
};

const cancelEditing = () => {
    addingRelativeType.value = null;
    isEditing.value = false;
    photoPreview.value = selectedMember.value?.picture || null;
};

const addSocialLink = () => {
    if (form.social_media.length < 3) {
        form.social_media.push({ socialid: props.socialPlatforms[0]?.socialid, link: '' });
    }
};

const removeSocialLink = (index) => { form.social_media.splice(index, 1); };

const triggerPhotoUpload = () => { if (isEditing.value) photoInput.value.click(); };

const handlePhotoChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => photoPreview.value = e.target.result;
    reader.readAsDataURL(file);
    isDetectingFace.value = true;
    try {
        await loadFaceApi();
        const img = new Image();
        img.src = URL.createObjectURL(file);
        await new Promise(r => img.onload = r);
        const detections = await faceapi.detectAllFaces(img, new faceapi.TinyFaceDetectorOptions());
        if (detections.length === 0) {
            showAlert(props.translations.face_not_detected, 'error');
            photoPreview.value = selectedMember.value?.picture || null;
            form.picture = null;
        } else if (detections.length > 1) {
            showAlert(props.translations.face_multiple_detected, 'error');
            photoPreview.value = selectedMember.value?.picture || null;
            form.picture = null;
        } else {
            form.picture = file;
            showAlert(props.translations.face_verified, 'success');
        }
    } catch (err) {
        console.error('Face detection failed:', err);
        form.picture = file;
    } finally {
        isDetectingFace.value = false;
    }
};

const handleSubmit = () => {
    const url = addingRelativeType.value 
        ? route('family-members.store')
        : route('family-members.update', selectedMember.value.memberid);
    form.post(url, {
        forceFormData: true,
        onSuccess: () => {
            closePanel();
            treeKey.value++;
            router.reload({ only: ['treeData'] });
            showAlert(props.translations?.success_save || 'Data saved successfully!', 'success');
        },
        onError: (errors) => {
            console.error('Submission Errors:', errors);
            const firstError = Object.values(errors)[0];
            showAlert(firstError || 'Failed to save data. Please check the form.', 'error');
        }
    });
};

const deceasedForm = useForm({ deaddate: '', grave_location_url: '', });
const markDeceased = () => { showDeceasedModal.value = true; };

const submitDeceased = () => {
    deceasedForm.post(route('family-members.mark-deceased', selectedMember.value.memberid), {
        onSuccess: () => {
            showDeceasedModal.value = false;
            closePanel();
            treeKey.value++;
            router.reload({ only: ['treeData'] });
        }
    });
};

const deleteMember = async () => {
    const confirmed = await showConfirm(props.translations.delete_confirm_title, props.translations.delete_confirm_desc.replace(':name', selectedMember.value.name));
    if (confirmed) {
        router.delete(route('family-members.destroy', selectedMember.value.memberid), {
            onSuccess: () => {
                closePanel();
                treeKey.value++;
                router.reload({ only: ['treeData'] });
            }
        });
    }
};

const zoomIn = () => { if (scale.value < 2) scale.value += 0.1; };
const zoomOut = () => { if (scale.value > 0.5) scale.value -= 0.1; };
const resetZoom = () => { scale.value = 1; };

const saveTree = async () => {
    if (!treeContainer.value) return;
    isExporting.value = true;
    try {
        const dataUrl = await toPng(treeContainer.value, {
            backgroundColor: '#F8FAFC',
            pixelRatio: 2,
            cacheBust: true,
            filter: (node) => {
                // Ignore buttons or interactive elements if needed, for now include all
                return true;
            }
        });
        const link = document.createElement('a');
        link.download = `family-tree-${new Date().toISOString().split('T')[0]}.png`;
        link.href = dataUrl;
        link.click();
    } catch (err) {
        console.error('Export failed:', err);
    } finally {
        isExporting.value = false;
    }
};

watch(() => props.treeData, () => { treeKey.value++; }, { deep: true });
</script>

<template>
    <div class="h-screen flex flex-col overflow-hidden relative">
        <Head :title="translations?.tree_view || 'Family Tree'" />

        <!-- Mark Deceased Modal -->
        <Modal :show="showDeceasedModal" @close="showDeceasedModal = false" max-width="lg">
            <form @submit.prevent="submitDeceased" class="p-8 space-y-6">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl"><Ghost :size="24" /></div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ translations?.mark_deceased || 'Mark Deceased' }}</h2>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ selectedMember?.name }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations?.passed_date || 'Passed Date' }}</label>
                        <input v-model="deceasedForm.deaddate" type="date" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none transition-all" required>
                        <div v-if="deceasedForm.errors.deaddate" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ deceasedForm.errors.deaddate }}</div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ translations?.grave_location || 'Grave Location URL' }}</label>
                        <input v-model="deceasedForm.grave_location_url" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-4 focus:ring-amber-500 outline-none transition-all" :placeholder="translations?.placeholder_grave || 'Grave Location'">
                        <div v-if="deceasedForm.errors.grave_location_url" class="text-rose-500 text-[10px] mt-1.5 font-bold uppercase tracking-wider">{{ deceasedForm.errors.grave_location_url }}</div>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" @click="showDeceasedModal = false" class="flex-1 px-8 py-5 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all flex items-center justify-center gap-2">
                        {{ translations?.cancel || 'Cancel' }}
                    </button>
                    <button type="submit" :disabled="deceasedForm.processing" class="flex-1 px-8 py-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-black transition-all flex items-center justify-center gap-2">
                        <Loader2 v-if="deceasedForm.processing" :size="16" class="animate-spin" />
                        <Save v-else :size="16" /> {{ deceasedForm.processing ? (translations?.processing || 'Processing...') : (translations?.save_data || 'Save Data') }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Floating Controls -->
        <div class="absolute top-6 right-6 z-30 flex items-center gap-3" @click.stop>
            <div class="bg-white/80 backdrop-blur-md p-1.5 rounded-2xl border border-slate-100 shadow-xl shadow-sky-100/50 flex items-center gap-1">
                <button @click="zoomIn" class="p-3 hover:bg-sky-50 rounded-xl text-slate-400 hover:text-sky-600 transition-all" :title="translations?.zoom_in"><ZoomIn :size="20" /></button>
                <button @click="zoomOut" class="p-3 hover:bg-sky-50 rounded-xl text-slate-400 hover:text-sky-600 transition-all" :title="translations?.zoom_out"><ZoomOut :size="20" /></button>
                <div class="w-px h-6 bg-slate-100 mx-1"></div>
                <button @click="resetZoom" class="p-3 hover:bg-sky-50 rounded-xl text-slate-400 hover:text-sky-600 transition-all" :title="translations?.reset_zoom"><RotateCcw :size="20" /></button>
                <div class="w-px h-6 bg-slate-100 mx-1"></div>
                <button @click="saveTree" :disabled="isExporting" class="p-3 hover:bg-emerald-50 rounded-xl text-slate-400 hover:text-emerald-600 transition-all disabled:opacity-50" :title="translations?.export_png">
                    <Loader2 v-if="isExporting" :size="20" class="animate-spin" /><Download v-else :size="20" />
                </button>
            </div>
        </div>

        <!-- Canvas -->
        <div class="flex-1 overflow-auto relative p-20 scrollbar-hide">
            <div ref="treeContainer" class="min-w-max min-h-max flex flex-row items-start justify-center gap-24 transition-transform duration-300 ease-out origin-top" :style="{ transform: `scale(${scale})` }">
                <svg ref="svgCanvas" class="absolute inset-0 pointer-events-none z-10 overflow-visible"></svg>

                <div v-if="visibleMin > 1" class="absolute top-10 left-1/2 -translate-x-1/2 z-30">
                    <button @click="expandUp" class="px-8 py-4 bg-sky-50 text-sky-600 border border-sky-100 rounded-full text-xs font-black uppercase tracking-[0.2em] shadow-sm hover:bg-sky-100 transition-all flex items-center gap-3 active:scale-95 group">
                        <ArrowUpCircle :size="16" class="group-hover:-translate-y-0.5 transition-transform" />  
                        {{ translations?.view_ancestors || 'View Ancestors' }}
                    </button>
                    <div class="absolute top-full left-1/2 -translate-x-1/2 w-0.5 h-12 bg-sky-100 mt-0"></div>  
                </div>

                <div v-for="(root, rootIdx) in visibleRoots" :key="root.member.memberid + treeKey" class="flex flex-col items-center">
                    <ul class="tree">
                        <TreeNode
                            :node="root"
                            :is-root="true"
                            :visible-max="visibleMax"
                            :key="treeKey"
                            :translations="translations"
                            @select-member="handleSelectMember"
                            @expand-down="expandDown"
                            @image-loaded="drawLines"
                        />
                    </ul>
                </div>
            </div>
        </div>

        <!-- Side Panel -->
        <transition name="slide-panel">
            <div v-if="selectedMember" class="absolute top-0 right-0 h-full w-[420px] bg-white border-l border-slate-100 shadow-2xl z-40 overflow-y-auto scrollbar-hide" @click.stop>
                <div class="sticky top-0 bg-white/80 backdrop-blur-md p-6 border-b border-slate-50 flex items-center justify-between z-10">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-sky-100 text-sky-600 rounded-xl"><Info :size="20" /></div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-tighter">{{ addingRelativeType ? (translations?.creating.replace(':type', addingRelativeType) || `Add ${addingRelativeType}`) : (isEditing ? (translations?.edit || 'Edit') + ' ' + selectedMember.name : selectedMember.name) }}</h2>
                    </div>
                    <button @click="closePanel" class="p-2 hover:bg-slate-100 rounded-xl text-slate-400 transition-all"><X :size="20" /></button>
                </div>

                <div class="p-8">
                    <div class="text-center mb-10">
                        <input type="file" ref="photoInput" @change="handlePhotoChange" class="hidden" accept="image/*">
                        <div class="group relative w-32 h-32 rounded-[2.5rem] overflow-hidden bg-slate-50 border-4 border-white shadow-xl mx-auto mb-6 transition-all duration-300" :class="{ 'cursor-pointer hover:ring-4 hover:ring-sky-500/20': isEditing }" @click="triggerPhotoUpload">
                            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" :class="{ 'opacity-50': isDetectingFace }">
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-300"><User :size="48" /></div>
                            <div v-if="isDetectingFace" class="absolute inset-0 bg-sky-600/20 flex flex-col items-center justify-center text-sky-700 gap-2">
                                <Loader2 :size="24" class="animate-spin" />
                                <span class="text-[8px] font-black uppercase tracking-widest">{{ translations?.scanning_face || 'Scanning Face...' }}</span>
                            </div>
                            <div v-if="isEditing && !isDetectingFace" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center text-white gap-1">
                                <Camera :size="24" /><span class="text-[8px] font-black uppercase tracking-widest">{{ translations?.upload || 'Upload' }}</span>
                            </div>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 leading-tight uppercase tracking-tight px-4">{{ addingRelativeType ? (form.name || (translations?.new_relative.replace(':type', addingRelativeType) || `New ${addingRelativeType}`)) : selectedMember.name }}</h2>
                        <p v-if="!addingRelativeType && selectedMember.username" class="text-[10px] font-black text-sky-600 uppercase tracking-widest mt-1">@{{ selectedMember.username }}</p>
                        <div class="mt-3 flex items-center justify-center flex-wrap gap-2">
                            <template v-if="!isEditing">
                                <span class="px-3 py-1 bg-sky-50 text-sky-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-100">{{ translations?.[selectedMember.life_status] || selectedMember.life_status }}</span>
                                <span v-if="selectedMember.gender" class="px-3 py-1 bg-slate-50 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-100">{{ translations?.[selectedMember.gender] || selectedMember.gender }}</span>
                                <Link v-if="selectedMember.memberid" :href="`/wiki/member/${selectedMember.memberid}`" class="px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-100 flex items-center gap-1 transition-all"><ShieldCheck :size="12" /> {{ translations?.wiki || 'Wiki' }}</Link>
                                <button @click="startEditing" class="px-3 py-1 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100 flex items-center gap-1 transition-all"><Pencil :size="12" /> {{ translations?.edit || 'Edit' }}</button>
                            </template>
                            <template v-else>
                                <span class="px-4 py-1 bg-sky-600 text-white rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-700 shadow-lg shadow-sky-100">{{ addingRelativeType ? (translations?.creating.replace(':type', addingRelativeType) || `Creating ${addingRelativeType}`) : (translations?.editing_member || 'Editing Member') }}</span>
                            </template>
                        </div>
                    </div>

                    <div v-if="!isEditing" class="space-y-8">
                        <div class="bg-slate-50 p-4 rounded-[2rem] border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-center mb-4">{{ translations?.quick_grow_tree || 'Quick Grow Tree' }}</p>
                            <div class="grid gap-2" :class="selectedMember.is_partner ? 'grid-cols-1' : 'grid-cols-3'">
                                <button v-if="!selectedMember.is_partner" @click="startAddingRelative('spouse')" class="flex flex-col items-center gap-2 p-3 bg-white hover:bg-rose-50 border border-slate-100 rounded-2xl transition-all group">
                                    <div class="p-2 bg-rose-50 text-rose-500 rounded-xl group-hover:scale-110 transition-transform"><HeartHandshake :size="18" /></div>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">{{ translations?.add_spouse || 'Add Spouse' }}</span>
                                </button>
                                <button @click="startAddingRelative('child')" class="flex flex-col items-center gap-2 p-3 bg-white hover:bg-sky-50 border border-slate-100 rounded-2xl transition-all group" :class="{ 'px-6': selectedMember.is_partner }">
                                    <div class="p-2 bg-sky-50 text-sky-500 rounded-xl group-hover:scale-110 transition-transform"><Baby :size="18" /></div>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">{{ translations?.add_child || 'Add Child' }}</span>
                                </button>
                                <button v-if="!selectedMember.is_partner" @click="startAddingRelative('parent')" class="flex flex-col items-center gap-2 p-3 bg-white hover:bg-amber-50 border border-slate-100 rounded-2xl transition-all group">
                                    <div class="p-2 bg-amber-100 text-amber-500 rounded-xl group-hover:scale-110 transition-transform"><ArrowUpCircle :size="18" /></div>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">{{ translations?.add_parent || 'Add Parent' }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div v-if="selectedMember.parent_relationships?.length" class="space-y-3">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.lineage_parents || 'Lineage & Parents' }}</p>
                                <div v-for="rel in selectedMember.parent_relationships" :key="rel.relationid" class="flex items-center gap-3 p-3 bg-amber-50/50 rounded-2xl border border-amber-100">
                                    <div class="p-2 bg-amber-100 text-amber-600 rounded-lg"><GitCommit :size="14" /></div>
                                    <div class="flex-1"><p class="text-[10px] font-bold text-amber-700 uppercase tracking-tight">{{ translations?.child_of.replace(':name', rel.parent_name) || `Child of ${rel.parent_name}` }}</p></div>
                                </div>
                            </div>
                            <div v-else-if="!addingRelativeType && !isEditing" class="p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center italic">{{ translations?.root_of_lineage || 'Root of Lineage' }}</p>
                            </div>
                            <div v-if="selectedMember.birthdate || selectedMember.birthplace" class="flex gap-4 items-start">
                                <div class="p-2 bg-sky-50 text-sky-500 rounded-xl shrink-0"><Calendar :size="18" /></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.born || 'Born' }}</p>
                                    <p class="text-sm font-bold text-slate-700 mt-0.5">
                                        {{ selectedMember.birthdate ? selectedMember.birthdate.split(/[T ]/)[0] : (translations?.hidden || 'HIDDEN') }}
                                        <span v-if="selectedMember.birthplace" class="block text-xs text-slate-400 font-medium">{{ selectedMember.birthplace }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-if="selectedMember.bloodtype" class="flex gap-4 items-start">
                                    <div class="p-2 bg-rose-50 text-rose-500 rounded-xl shrink-0"><Droplets :size="18" /></div>
                                    <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.blood || 'Blood' }}</p><p class="text-sm font-bold text-slate-700 mt-0.5">{{ selectedMember.bloodtype }}</p></div>        
                                </div>
                                <div v-if="selectedMember.education_status" class="flex gap-4 items-start">     
                                    <div class="p-2 bg-amber-50 text-amber-500 rounded-xl shrink-0"><GraduationCap :size="18" /></div>
                                    <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.education || 'Education' }}</p><p class="text-sm font-bold text-slate-700 mt-0.5">{{ selectedMember.education_status }}</p></div>
                                </div>
                            </div>
                            <div v-if="selectedMember.job" class="flex gap-4 items-start"><div class="p-2 bg-emerald-50 text-emerald-500 rounded-xl shrink-0"><Briefcase :size="18" /></div><div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.occupation || 'Occupation' }}</p><p class="text-sm font-bold text-slate-700 mt-0.5">{{ selectedMember.job }}</p></div></div>
                            <div v-if="selectedMember.social_media?.length" class="flex gap-4 items-start border-t border-slate-50 pt-6"><div class="p-2 bg-indigo-50 text-indigo-500 rounded-xl shrink-0"><Globe :size="18" /></div><div class="flex-1"><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">{{ translations?.social_media || 'Social Media' }}</p><div class="flex flex-wrap gap-2"><a v-for="social in selectedMember.social_media" :key="social.ownid" :href="social.link" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-white border border-slate-100 hover:border-indigo-200 rounded-xl text-xs font-bold text-slate-600 hover:text-indigo-600 transition-all group">{{ social.socialname }}<ExternalLink :size="10" class="opacity-0 group-hover:opacity-100 transition-opacity" /></a></div></div></div>
                            <div v-if="selectedMember.email" class="flex gap-4 items-start border-t border-slate-50 pt-6"><div class="p-2 bg-blue-50 text-blue-500 rounded-xl shrink-0"><Mail :size="18" /></div><div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.email || 'Email' }}</p><p class="text-sm font-bold text-slate-700 mt-0.5 truncate max-w-[250px]">{{ selectedMember.email }}</p></div></div>
                            <div v-if="selectedMember.phonenumber" class="flex gap-4 items-start"><div class="p-2 bg-indigo-50 text-indigo-500 rounded-xl shrink-0"><Phone :size="18" /></div><div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.phone || 'Phone' }}</p><p class="text-sm font-bold text-slate-700 mt-0.5">{{ selectedMember.phonenumber }}</p></div></div>
                            <div v-if="selectedMember.address" class="flex gap-4 items-start"><div class="p-2 bg-rose-50 text-rose-500 rounded-xl shrink-0"><MapPin :size="18" /></div><div><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ translations?.current_residence || 'Current Residence' }}</p><p class="text-sm font-bold text-slate-700 mt-0.5">{{ selectedMember.address }}</p></div></div>
                        </div>

                        <div class="pt-8 border-t border-slate-50 space-y-3">
                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] text-center">{{ translations?.administrative_vault || 'Administrative Vault' }}</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button v-if="selectedMember.life_status === 'alive'" @click="markDeceased" class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 hover:bg-amber-50 text-slate-400 hover:text-amber-600 rounded-2xl border border-slate-100 transition-all text-[10px] font-black uppercase tracking-widest group">
                                    <Ghost :size="14" class="group-hover:scale-110 transition-transform" /> {{ translations?.mark_deceased || 'Mark Deceased' }}
                                </button>
                                <button v-if="canDeleteMember(selectedMember)" @click="deleteMember" class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-2xl border border-slate-100 transition-all text-[10px] font-black uppercase tracking-widest group" :class="{ 'col-span-2': selectedMember.life_status !== 'alive' }">
                                    <Trash2 :size="14" class="group-hover:scale-110 transition-transform" /> {{ translations?.delete_member || 'Delete Member' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <form v-else @submit.prevent="handleSubmit" class="space-y-6 animate-in fade-in duration-300">
                        <div class="space-y-4">
                            <div v-if="addingRelativeType === 'child' || (!addingRelativeType && form.primary_parent_id)" class="p-4 bg-sky-50 rounded-3xl border-2 border-sky-100">
                                <label class="text-[10px] font-black text-sky-600 uppercase tracking-widest block mb-2">{{ translations?.parenting_mode || 'Parenting Mode' }}</label>
                                <select v-model="form.secondary_parent_id" class="w-full bg-white border-slate-200 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500">
                                    <option value="">{{ translations?.single_parent || 'Single Parent' }} ({{ addingRelativeType === 'child' ? selectedMember.name : (selectedMember.parent_relationships?.[0]?.parent_name || 'Parent') }})</option>
                                    <template v-if="addingRelativeType === 'child'"><option v-for="partner in selectedMember.partners_list" :key="partner.memberid" :value="partner.memberid">{{ translations?.with_partner?.replace(':name', partner.name) || `With ${partner.name}` }}</option></template>
                                    <template v-else-if="selectedMember.parent_relationships?.[0]?.parent_partners"><option v-for="partner in selectedMember.parent_relationships[0].parent_partners" :key="partner.memberid" :value="partner.memberid">{{ translations?.with_partner?.replace(':name', partner.name) || `With ${partner.name}` }}</option></template>      
                                </select>
                            </div>

                            <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.full_name || 'Full Name' }}</label><input v-model="form.name" type="text" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold p-3 focus:ring-sky-500 outline-none border-2"><div v-if="form.errors.name" class="text-rose-500 text-[10px] mt-1 font-bold">{{ form.errors.name }}</div></div>

                            <div v-if="!addingRelativeType" class="grid grid-cols-2 gap-4">
                                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.gender || 'Gender' }}</label><select v-model="form.gender" :disabled="isEditing && !addingRelativeType" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 focus:border-sky-500 outline-none disabled:opacity-60"><option value="male">{{ translations?.male || 'Male' }}</option><option value="female">{{ translations?.female || 'Female' }}</option></select></div>
                                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.life_status || 'Life Status' }}</label><select v-model="form.life_status" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 focus:border-sky-500 outline-none"><option value="alive">{{ translations?.alive || 'Alive' }}</option><option value="deceased">{{ translations?.deceased || 'Deceased' }}</option></select></div>
                            </div>
                            <div v-else><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.gender || 'Gender' }}</label><select v-model="form.gender" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 focus:border-sky-500 outline-none"><option value="male">{{ translations?.male || 'Male' }}</option><option value="female">{{ translations?.female || 'Female' }}</option></select></div>

                            <div v-if="form.life_status === 'deceased'" class="space-y-4 p-4 bg-slate-50 rounded-3xl border-2 border-slate-100 animate-in zoom-in-95 duration-300">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.passed_date || 'Passed Date' }}</label><input v-model="form.deaddate" type="date" class="w-full bg-white border-slate-200 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-rose-400"></div>
                                        <div class="flex items-end pb-1"><p class="text-[9px] text-slate-400 italic font-medium leading-tight">{{ translations?.resting_peace || 'Resting in peace forever.' }}</p></div>       
                                    </div>
                                    <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.grave_location || 'Grave Location URL' }}</label><input v-model="form.grave_location_url" type="text" class="w-full bg-white border-slate-200 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-rose-400" :placeholder="translations?.placeholder_grave"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.birthdate || 'Birthdate' }}</label><input v-model="form.birthdate" type="date" :disabled="isEditing && !addingRelativeType" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500 disabled:opacity-60"></div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.blood_type || 'Blood Type' }}</label>
                                    <select v-model="form.bloodtype" :disabled="isEditing && !addingRelativeType && selectedMember?.bloodtype" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500 disabled:opacity-60">
                                        <option value="">{{ translations?.unknown || 'Unknown' }}</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div> 
                            </div>

                            <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.birthplace || 'Birthplace' }}</label><input v-model="form.birthplace" type="text" :disabled="isEditing && !addingRelativeType" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500 disabled:opacity-60"></div>

                            <div class="space-y-4 p-4 bg-sky-50/50 rounded-3xl border-2 border-sky-100/50">     
                                <p class="text-[10px] font-black text-sky-600 uppercase tracking-widest block mb-2">{{ translations?.location_details || 'Location Details' }}</p>
                                <div class="space-y-4">
                                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ translations?.country || 'Country' }}</label><select v-model="form.country" class="w-full bg-white border-slate-100 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500"><option v-for="country in allCountries" :key="country" :value="country">{{ country }}</option></select></div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div v-if="hasDetailedProvinces"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ translations?.province_state || 'Province' }}</label><select v-model="form.province" class="w-full bg-white border-slate-100 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500"><option value="">{{ translations?.select_province || 'Select Province' }}</option><option v-for="province in provinces" :key="province" :value="province">{{ province }}</option></select></div>
                                        <div v-else><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ translations?.province_state || 'Province / State' }}</label><input v-model="form.province" type="text" class="w-full bg-white border-slate-100 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500" :placeholder="translations?.province_state"></div>
                                        <div v-if="hasDetailedProvinces"><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ translations?.city_district || 'City' }}</label><select v-model="form.city" :disabled="!form.province" class="w-full bg-white border-slate-100 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500 disabled:opacity-50"><option value="">{{ translations?.select_city || 'Select City' }}</option><option v-for="city in cities" :key="city" :value="city">{{ city }}</option></select></div>
                                        <div v-else><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ translations?.city_district || 'City / District' }}</label><input v-model="form.city" type="text" class="w-full bg-white border-slate-100 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500" :placeholder="translations?.city_district"></div>
                                    </div>
                                    <div><label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">{{ translations?.address_detail || 'Address Detail' }}</label><textarea v-model="form.address_detail" rows="2" class="w-full bg-white border-slate-100 border-2 rounded-xl text-sm font-bold p-2.5 outline-none focus:border-sky-500" :placeholder="translations?.placeholder_address"></textarea></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.education || 'Education' }}</label><input v-model="form.education_status" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500" :placeholder="translations?.placeholder_edu"></div>
                                <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.occupation || 'Occupation' }}</label><input v-model="form.job" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500"></div>
                            </div>

                            <div class="space-y-4 p-4 bg-indigo-50/50 rounded-3xl border-2 border-indigo-100/50">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ translations?.social_accounts || 'Social Media' }}</p>
                                    <button v-if="form.social_media.length < 3" type="button" @click="addSocialLink" class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all"><Plus :size="12" /></button>
                                </div>
                                <div v-for="(social, index) in form.social_media" :key="index" class="flex gap-2 items-center bg-white p-2 rounded-2xl border border-indigo-50">
                                    <select v-model="social.socialid" class="bg-slate-50 border-none text-[10px] font-bold rounded-xl p-2 outline-none w-24">
                                        <option v-for="platform in socialPlatforms" :key="platform.socialid" :value="platform.socialid">{{ platform.socialname }}</option>
                                    </select>
                                    <input v-model="social.link" type="text" class="flex-1 bg-slate-50 border-none text-[10px] font-bold rounded-xl p-2 outline-none" :placeholder="translations?.placeholder_social">
                                    <button type="button" @click="removeSocialLink(index)" class="p-2 text-rose-400 hover:text-rose-600 transition-all"><Trash2 :size="14" /></button>
                                </div>
                                <p v-if="!form.social_media.length" class="text-[9px] text-slate-400 text-center py-2 italic">{{ translations?.no_social_links || 'No links' }}</p>
                            </div>

                            <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.email || 'Email' }}</label><input v-model="form.email" type="email" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500"></div>
                            <div><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">{{ translations?.phone || 'Phone' }}</label><input v-model="form.phonenumber" type="text" class="w-full bg-slate-50 border-slate-100 border-2 rounded-2xl text-sm font-bold p-3 outline-none focus:border-sky-500"></div>
                        </div>

                        <div class="flex gap-3 pt-6 border-t border-slate-50">
                            <button type="button" @click="cancelEditing" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-slate-200 flex items-center justify-center gap-2"><RotateCw :size="14" /> {{ translations?.cancel || 'Cancel' }}</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-4 bg-sky-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-sky-100 hover:bg-sky-700 flex items-center justify-center gap-2"><Loader2 v-if="form.processing" :size="14" class="animate-spin" /><Save v-else :size="14" /> {{ form.processing ? (translations?.processing || 'Processing...') : (translations?.save_data || 'Save Data') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.tree { margin: 0 auto; padding: 0; list-style: none; display: table; position: relative; }
.slide-panel-enter-active, .slide-panel-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }     
.slide-panel-enter-from, .slide-panel-leave-to { transform: translateX(120%); opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

:deep(.tree-connector-line) {
    transition: all 0.3s ease;
    fill: none;
}
</style>

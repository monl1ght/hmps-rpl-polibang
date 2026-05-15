<script setup>
import { computed, onUnmounted, ref, watch } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";
import MemberEditForm from "@/admin/components/MemberEditForm.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  hero: {
    type: Object,
    default: () => ({}),
  },
  divisions: {
    type: Array,
    default: () => [],
  },
  members: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const editingDivision = ref(null);
const editingMember = ref(null);
const previewPhoto = ref(null);
const editPreviewPhoto = ref(null);
const heroImagePreviews = ref({
  image_one_file: null,
  image_two_file: null,
  image_three_file: null,
  image_four_file: null,
});

const fallbackPhoto = "https://i.pravatar.cc/400?img=32";

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const divisions = computed(() => (Array.isArray(props.divisions) ? props.divisions : []));
const members = computed(() => (Array.isArray(props.members) ? props.members : []));
const categories = computed(() =>
  Array.isArray(props.categories) ? props.categories : []
);

const heroSetting = computed(() => props.hero || {});

const stats = computed(() => {
  const core = members.value.filter((item) => item.category === "core").length;
  const coordinators = members.value.filter((item) => item.category === "coordinator")
    .length;
  const membersOnly = members.value.filter((item) => item.category === "member").length;

  return [
    {
      label: "Pengurus Inti",
      value: core,
      helper: "Ketua, sekretaris, bendahara, dan inti",
      icon: "crown",
    },
    {
      label: "Jumlah Divisi",
      value: divisions.value.length,
      helper: "Total divisi aktif di struktur",
      icon: "grid",
    },
    {
      label: "Koordinator",
      value: coordinators,
      helper: "Koordinator setiap divisi",
      icon: "star",
    },
    {
      label: "Total Pengurus",
      value: core + coordinators + membersOnly,
      helper: "Seluruh pengurus HMPS RPL",
      icon: "users",
    },
  ];
});

const heroForm = useForm({
  badge: "",
  title: "",
  title_highlight: "",
  description: "",
  primary_button_label: "",
  primary_button_url: "",
  secondary_button_label: "",
  secondary_button_url: "",
  tertiary_button_label: "",
  tertiary_button_url: "",
  image_one_alt: "",
  image_two_alt: "",
  image_three_alt: "",
  image_four_alt: "",
  image_one_file: null,
  image_two_file: null,
  image_three_file: null,
  image_four_file: null,
  floating_badge_top_icon: "",
  floating_badge_top_title: "",
  floating_badge_top_subtitle: "",
  floating_badge_bottom_icon: "",
  floating_badge_bottom_title: "",
  floating_badge_bottom_subtitle: "",
  is_active: true,
});

const fillHeroForm = (hero = {}) => {
  heroForm.badge = hero.badge || "";
  heroForm.title = hero.title || "";
  heroForm.title_highlight = hero.title_highlight || "";
  heroForm.description = hero.description || "";
  heroForm.primary_button_label = hero.primary_button_label || "";
  heroForm.primary_button_url = hero.primary_button_url || "";
  heroForm.secondary_button_label = hero.secondary_button_label || "";
  heroForm.secondary_button_url = hero.secondary_button_url || "";
  heroForm.tertiary_button_label = hero.tertiary_button_label || "";
  heroForm.tertiary_button_url = hero.tertiary_button_url || "";
  heroForm.image_one_alt = hero.image_one_alt || "";
  heroForm.image_two_alt = hero.image_two_alt || "";
  heroForm.image_three_alt = hero.image_three_alt || "";
  heroForm.image_four_alt = hero.image_four_alt || "";
  heroForm.floating_badge_top_icon = hero.floating_badge_top_icon || "";
  heroForm.floating_badge_top_title = hero.floating_badge_top_title || "";
  heroForm.floating_badge_top_subtitle = hero.floating_badge_top_subtitle || "";
  heroForm.floating_badge_bottom_icon = hero.floating_badge_bottom_icon || "";
  heroForm.floating_badge_bottom_title = hero.floating_badge_bottom_title || "";
  heroForm.floating_badge_bottom_subtitle = hero.floating_badge_bottom_subtitle || "";
  heroForm.is_active = Boolean(hero.is_active ?? true);
};

watch(
  () => props.hero,
  (hero) => fillHeroForm(hero || {}),
  { immediate: true, deep: true }
);

const divisionForm = useForm({
  name: "",
  short_name: "",
  description: "",
  sort_order: 0,
});

const divisionEditForm = useForm({
  name: "",
  short_name: "",
  description: "",
  sort_order: 0,
});

const memberForm = useForm({
  management_division_id: "",
  name: "",
  position: "",
  category: "member",
  photo_file: null,
  sort_order: 0,
});

const memberEditForm = useForm({
  management_division_id: "",
  name: "",
  position: "",
  category: "member",
  photo_file: null,
  sort_order: 0,
});

const coreMembers = computed(() =>
  members.value.filter((item) => item.category === "core")
);

const divisionMembers = computed(() =>
  divisions.value.map((division) => ({
    ...division,
    coordinators: members.value.filter(
      (member) =>
        member.category === "coordinator" &&
        Number(member.management_division_id) === Number(division.id)
    ),
    members: members.value.filter(
      (member) =>
        member.category === "member" &&
        Number(member.management_division_id) === Number(division.id)
    ),
  }))
);

const memberTotalByDivision = (division) => {
  return (division.coordinators?.length || 0) + (division.members?.length || 0);
};

const categoryLabel = (category) => {
  if (category === "core") return "Pengurus Inti";
  if (category === "coordinator") return "Koordinator";
  return "Anggota";
};

const categoryClass = (category) => {
  if (category === "core") return "border-red-200 bg-red-50 text-red-700";
  if (category === "coordinator") return "border-blue-200 bg-blue-50 text-blue-700";
  return "border-slate-200 bg-slate-100 text-slate-700";
};

const quickNavItems = [
  {
    href: "#hero-kepengurusan",
    title: "Hero",
    description: "Konten utama halaman",
    icon: "hero",
  },
  {
    href: "#divisi-kepengurusan",
    title: "Divisi",
    description: "Struktur organisasi",
    icon: "division",
  },
  {
    href: "#form-pengurus",
    title: "Tambah Pengurus",
    description: "Input data anggota",
    icon: "member",
  },
  {
    href: "#daftar-pengurus",
    title: "Daftar Pengurus",
    description: "Kelola semua data",
    icon: "list",
  },
];

const memberSearch = ref("");
const selectedMemberCategory = ref("all");

const normalizeText = (value) =>
  String(value || "")
    .toLowerCase()
    .trim();

const memberMatchesFilter = (member, division = {}) => {
  const keyword = normalizeText(memberSearch.value);
  const category = selectedMemberCategory.value;
  const keywordMatch =
    !keyword ||
    [
      member.name,
      member.position,
      member.category,
      division.name,
      division.short_name,
    ].some((item) => normalizeText(item).includes(keyword));
  const categoryMatch = category === "all" || member.category === category;

  return keywordMatch && categoryMatch;
};

const filteredCoreMembers = computed(() => coreMembers.value.filter(memberMatchesFilter));

const hasActiveMemberFilter = computed(
  () => Boolean(memberSearch.value.trim()) || selectedMemberCategory.value !== "all"
);

const filteredDivisionMembers = computed(() =>
  divisionMembers.value.map((division) => ({
    ...division,
    coordinators: division.coordinators.filter((member) =>
      memberMatchesFilter(member, division)
    ),
    members: division.members.filter((member) => memberMatchesFilter(member, division)),
  }))
);

const visibleDivisionMembers = computed(() =>
  filteredDivisionMembers.value.filter(
    (division) => !hasActiveMemberFilter.value || memberTotalByDivision(division) > 0
  )
);

const filteredMemberCount = computed(
  () =>
    filteredCoreMembers.value.length +
    filteredDivisionMembers.value.reduce(
      (total, division) => total + memberTotalByDivision(division),
      0
    )
);

const resetMemberFilter = () => {
  memberSearch.value = "";
  selectedMemberCategory.value = "all";
};

const getPhoto = (photo) => photo || fallbackPhoto;

const revokePreview = (preview) => {
  if (preview && String(preview).startsWith("blob:")) {
    URL.revokeObjectURL(preview);
  }
};

const heroImageFields = [
  {
    input: "image_one_file",
    url: "image_one_url",
    alt: "image_one_alt",
    label: "Foto Hero 1",
  },
  {
    input: "image_two_file",
    url: "image_two_url",
    alt: "image_two_alt",
    label: "Foto Hero 2",
  },
  {
    input: "image_three_file",
    url: "image_three_url",
    alt: "image_three_alt",
    label: "Foto Hero 3",
  },
  {
    input: "image_four_file",
    url: "image_four_url",
    alt: "image_four_alt",
    label: "Foto Hero 4",
  },
];

const handleHeroImageFile = (event, field) => {
  const file = event.target.files[0] || null;
  revokePreview(heroImagePreviews.value[field]);
  heroForm[field] = file;
  heroImagePreviews.value[field] = file ? URL.createObjectURL(file) : null;
};

const getHeroImagePreview = (field) =>
  heroImagePreviews.value[field.input] || heroSetting.value[field.url] || null;

const updateHeroSetting = () => {
  const heroId = heroSetting.value?.id;
  if (!heroId) return;

  heroForm.post(`/admin/kepengurusan/hero/${heroId}`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      heroImageFields.forEach((field) => {
        revokePreview(heroImagePreviews.value[field.input]);
        heroImagePreviews.value[field.input] = null;
        heroForm[field.input] = null;
      });
    },
  });
};

const handlePhotoFile = (event) => {
  const file = event.target.files[0] || null;

  revokePreview(previewPhoto.value);

  memberForm.photo_file = file;
  previewPhoto.value = file ? URL.createObjectURL(file) : null;
};

const handleEditPhotoFile = (event) => {
  const file = event.target.files[0] || null;

  revokePreview(editPreviewPhoto.value);

  memberEditForm.photo_file = file;
  editPreviewPhoto.value = file
    ? URL.createObjectURL(file)
    : editingMember.value?.photo_url || null;
};

const resetDivisionForm = () => {
  divisionForm.reset();
  divisionForm.sort_order = 0;
};

const resetMemberForm = () => {
  revokePreview(previewPhoto.value);

  memberForm.reset();
  memberForm.category = "member";
  memberForm.management_division_id = "";
  memberForm.photo_file = null;
  memberForm.sort_order = 0;
  previewPhoto.value = null;
};

const storeDivision = () => {
  divisionForm.post("/admin/kepengurusan/divisions", {
    preserveScroll: true,
    onSuccess: () => resetDivisionForm(),
  });
};

const editDivision = (division) => {
  editingDivision.value = division;

  divisionEditForm.name = division.name || "";
  divisionEditForm.short_name = division.short_name || "";
  divisionEditForm.description = division.description || "";
  divisionEditForm.sort_order = Number(division.sort_order || 0);
};

const cancelEditDivision = () => {
  editingDivision.value = null;
  divisionEditForm.reset();
};

const updateDivision = () => {
  if (!editingDivision.value) return;

  divisionEditForm.put(`/admin/kepengurusan/divisions/${editingDivision.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditDivision(),
  });
};

const deleteDivision = (division) => {
  if (!confirm(`Hapus divisi "${division.name}"?`)) return;

  router.delete(`/admin/kepengurusan/divisions/${division.id}`, {
    preserveScroll: true,
  });
};

const storeMember = () => {
  memberForm.post("/admin/kepengurusan/members", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => resetMemberForm(),
  });
};

const editMember = (member) => {
  revokePreview(editPreviewPhoto.value);

  editingMember.value = member;

  memberEditForm.management_division_id = member.management_division_id || "";
  memberEditForm.name = member.name || "";
  memberEditForm.position = member.position || "";
  memberEditForm.category = member.category || "member";
  memberEditForm.photo_file = null;
  memberEditForm.sort_order = Number(member.sort_order || 0);

  editPreviewPhoto.value = member.photo_url || null;
};

const cancelEditMember = () => {
  revokePreview(editPreviewPhoto.value);

  editingMember.value = null;
  memberEditForm.reset();
  editPreviewPhoto.value = null;
};

const updateMember = () => {
  if (!editingMember.value) return;

  memberEditForm
    .transform((data) => ({
      ...data,
      _method: "PUT",
    }))
    .post(`/admin/kepengurusan/members/${editingMember.value.id}`, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => cancelEditMember(),
    });
};

const deleteMember = (member) => {
  if (!confirm(`Hapus pengurus "${member.name}"?`)) return;

  router.delete(`/admin/kepengurusan/members/${member.id}`, {
    preserveScroll: true,
  });
};

onUnmounted(() => {
  revokePreview(previewPhoto.value);
  revokePreview(editPreviewPhoto.value);
  heroImageFields.forEach((field) => revokePreview(heroImagePreviews.value[field.input]));
});
</script>

<template>
  <Head title="Custom Kepengurusan" />

  <div
    class="custom-kepengurusan-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8 sm:pb-10"
  >
    <!-- Header -->
    <section
      id="top-kepengurusan"
      data-aos="fade-up"
      class="relative overflow-hidden rounded-[2.25rem] border border-slate-800 bg-[radial-gradient(circle_at_top_right,rgba(239,68,68,0.32),transparent_34%),linear-gradient(135deg,#020617,#0f172a_46%,#7f1d1d)] p-6 text-white shadow-[0_28px_80px_rgba(2,6,23,0.26)] sm:p-8 lg:p-10"
    >
      <div
        class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-red-500/20 blur-[80px]"
      />
      <div
        class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-white/10 blur-[80px]"
      />

      <div
        class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between"
      >
        <div>
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.12em] text-red-100 ring-1 ring-white/10"
          >
            <span
              class="h-2 w-2 rounded-full bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.14)]"
            />
            Admin / Custom Kepengurusan
          </div>

          <h1
            class="max-w-3xl text-[2rem] font-black leading-[1.08] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.15rem]"
          >
            Kelola Struktur
            <span class="block text-red-300">Kepengurusan HMPS RPL</span>
          </h1>

          <p class="mt-5 max-w-2xl text-sm leading-8 text-slate-300 sm:text-base">
            Tambah, edit, dan atur pengurus inti, koordinator divisi, anggota divisi, foto
            pengurus, serta urutan tampil pada halaman Kepengurusan.
          </p>

          <div class="mt-7 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-4">
            <div
              v-for="item in stats"
              :key="`hero-${item.label}`"
              class="rounded-2xl border border-white/10 bg-white/10 p-3 backdrop-blur"
            >
              <p
                class="text-[0.65rem] font-black uppercase tracking-[0.12em] text-red-100"
              >
                {{ item.label }}
              </p>
              <p class="mt-2 text-2xl font-black tracking-[-0.04em] text-white">
                {{ item.value }}
              </p>
            </div>
          </div>
        </div>

        <a
          href="/kepengurusan"
          target="_blank"
          class="group relative inline-flex min-h-[46px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-sm font-black text-slate-950 shadow-[0_14px_34px_rgba(255,255,255,0.12)] transition hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700 sm:w-auto sm:min-h-[52px]"
        >
          <span
            class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(239,68,68,0.14),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
          />
          <svg
            class="relative z-10 h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.4"
              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.4"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
            />
          </svg>
          <span class="relative z-10">Preview Kepengurusan</span>
        </a>
      </div>
    </section>

    <!-- Flash -->
    <div
      v-if="flashSuccess"
      class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_10px_28px_rgba(16,185,129,0.08)]"
    >
      {{ flashSuccess }}
    </div>

    <div
      v-if="flashError"
      class="rounded-[1.35rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_10px_28px_rgba(239,68,68,0.08)]"
    >
      {{ flashError }}
    </div>

    <!-- Quick Navigation -->
    <section
      data-aos="fade-up"
      class="grid grid-cols-2 gap-3 sm:grid-cols-2 xl:grid-cols-4"
    >
      <a
        v-for="item in quickNavItems"
        :key="item.href"
        :href="item.href"
        class="group flex items-center gap-4 rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-[0_14px_34px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_22px_48px_rgba(15,23,42,0.09)]"
      >
        <span
          class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-white transition duration-300 group-hover:bg-red-600 group-hover:scale-105"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="item.icon === 'hero'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 5h16v14H4V5Zm4 10 3-3 2 2 3-4 4 5"
            />
            <path
              v-else-if="item.icon === 'division'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 6h16M7 6v12m10-12v12M4 18h16M12 6v12"
            />
            <path
              v-else-if="item.icon === 'member'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4.42 0-8 2.24-8 5v2h16v-2c0-2.76-3.58-5-8-5Z"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M5 7h14M5 12h14M5 17h9"
            />
          </svg>
        </span>

        <span class="min-w-0">
          <span class="block text-sm font-black tracking-[-0.02em] text-slate-950">
            {{ item.title }}
          </span>
          <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
            {{ item.description }}
          </span>
        </span>
      </a>
    </section>

    <!-- Hero Section Customization -->
    <section
      id="hero-kepengurusan"
      data-aos="fade-up"
      class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_54px_rgba(2,6,23,0.06)] ring-1 ring-slate-100"
    >
      <div
        class="border-b border-slate-200 bg-[linear-gradient(135deg,#fff7f7,#ffffff)] px-5 py-5 sm:px-6 lg:px-8"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Hero Section
            </div>
            <h2 class="text-2xl font-black tracking-[-0.035em] text-slate-950">
              Custom Tampilan Hero Kepengurusan
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">
              Atur badge, judul, deskripsi, tombol aksi, foto kolase hero, dan floating
              badge yang tampil di halaman user Kepengurusan.
            </p>
          </div>

          <div
            class="rounded-2xl border border-red-100 bg-white px-4 py-3 text-xs font-bold leading-6 text-slate-500 shadow-sm"
          >
            JPG/PNG/WebP • Maksimal 4 MB per foto
          </div>
        </div>
      </div>

      <form class="space-y-8 p-5 sm:p-6 lg:p-8" @submit.prevent="updateHeroSetting">
        <div class="grid gap-5 lg:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800">Badge kecil</label>
            <input
              v-model="heroForm.badge"
              type="text"
              class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              placeholder="Struktur Kepengurusan HMPS RPL"
            />
            <p v-if="heroForm.errors.badge" class="mt-2 text-sm text-red-600">
              {{ heroForm.errors.badge }}
            </p>
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800"
                >Judul utama</label
              >
              <input
                v-model="heroForm.title"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="Kepengurusan"
              />
              <p v-if="heroForm.errors.title" class="mt-2 text-sm text-red-600">
                {{ heroForm.errors.title }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800"
                >Highlight judul</label
              >
              <input
                v-model="heroForm.title_highlight"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="HMPS RPL"
              />
            </div>
          </div>
        </div>

        <div>
          <label class="mb-2 block text-sm font-bold text-slate-800"
            >Deskripsi hero</label
          >
          <textarea
            v-model="heroForm.description"
            rows="4"
            class="w-full rounded-[1.35rem] border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            placeholder="Tuliskan deskripsi singkat hero section..."
          />
          <p v-if="heroForm.errors.description" class="mt-2 text-sm text-red-600">
            {{ heroForm.errors.description }}
          </p>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800"
              >Tombol utama</label
            >
            <div class="grid gap-3">
              <input
                v-model="heroForm.primary_button_label"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="Inti"
              />
              <input
                v-model="heroForm.primary_button_url"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="#pengurus-inti"
              />
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800"
              >Tombol kedua</label
            >
            <div class="grid gap-3">
              <input
                v-model="heroForm.secondary_button_label"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="Divisi"
              />
              <input
                v-model="heroForm.secondary_button_url"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="#divisi"
              />
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800"
              >Tombol ketiga</label
            >
            <div class="grid gap-3">
              <input
                v-model="heroForm.tertiary_button_label"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="Semangat"
              />
              <input
                v-model="heroForm.tertiary_button_url"
                type="text"
                class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="#closing"
              />
            </div>
          </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
          <div
            v-for="field in heroImageFields"
            :key="field.input"
            class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4"
          >
            <div class="flex items-start gap-4">
              <div
                class="h-28 w-28 shrink-0 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"
              >
                <img
                  v-if="getHeroImagePreview(field)"
                  :src="getHeroImagePreview(field)"
                  :alt="heroForm[field.alt] || field.label"
                  class="h-full w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center text-center text-xs font-bold text-slate-400"
                >
                  Belum ada foto
                </div>
              </div>

              <div class="min-w-0 flex-1">
                <label class="block text-sm font-black text-slate-900">{{
                  field.label
                }}</label>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                  Upload foto baru jika ingin mengganti gambar.
                </p>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="mt-3 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-red-600 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-red-700"
                  @change="handleHeroImageFile($event, field.input)"
                />
                <input
                  v-model="heroForm[field.alt]"
                  type="text"
                  class="mt-3 h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  placeholder="Alt text gambar"
                />
                <p v-if="heroForm.errors[field.input]" class="mt-2 text-sm text-red-600">
                  {{ heroForm.errors[field.input] }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
          <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-black text-slate-950">Floating Badge Atas</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-[0.28fr_1fr]">
              <input
                v-model="heroForm.floating_badge_top_icon"
                type="text"
                class="h-[52px] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                placeholder="👥"
              />
              <input
                v-model="heroForm.floating_badge_top_title"
                type="text"
                class="h-[52px] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                placeholder="Struktur Tersusun"
              />
            </div>
            <input
              v-model="heroForm.floating_badge_top_subtitle"
              type="text"
              class="mt-3 h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              placeholder="Pengurus dan divisi"
            />
          </div>

          <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-black text-slate-950">Floating Badge Bawah</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-[0.28fr_1fr]">
              <input
                v-model="heroForm.floating_badge_bottom_icon"
                type="text"
                class="h-[52px] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                placeholder="🚀"
              />
              <input
                v-model="heroForm.floating_badge_bottom_title"
                type="text"
                class="h-[52px] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                placeholder="Aktif & Profesional"
              />
            </div>
            <input
              v-model="heroForm.floating_badge_bottom_subtitle"
              type="text"
              class="mt-3 h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              placeholder="HMPS Rekayasa Perangkat Lunak"
            />
          </div>
        </div>

        <div
          class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between"
        >
          <label class="inline-flex items-center gap-3 text-sm font-bold text-slate-700">
            <input
              v-model="heroForm.is_active"
              type="checkbox"
              class="h-5 w-5 rounded border-slate-300 text-red-600 focus:ring-red-500"
            />
            Aktifkan hero section custom
          </label>

          <button
            type="submit"
            :disabled="heroForm.processing"
            class="inline-flex min-h-[52px] items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-6 text-sm font-black text-white shadow-[0_14px_32px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_44px_rgba(220,38,38,0.30)] disabled:cursor-not-allowed disabled:opacity-60"
          >
            {{ heroForm.processing ? "Menyimpan Hero..." : "Simpan Hero Section" }}
          </button>
        </div>
      </form>
    </section>

    <!-- Stats -->
    <section
      data-aos="fade-up"
      class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4"
    >
      <div
        v-for="item in stats"
        :key="item.label"
        class="group rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-[0_14px_36px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_22px_52px_rgba(15,23,42,0.10)]"
      >
        <div
          class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100 transition group-hover:scale-105"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="item.icon === 'crown'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M5 16 3 7l5 4 4-7 4 7 5-4-2 9H5Zm0 0v3h14v-3"
            />
            <path
              v-else-if="item.icon === 'grid'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 5a1 1 0 0 1 1-1h5v6H4V5Zm10-1h5a1 1 0 0 1 1 1v5h-6V4ZM4 14h6v6H5a1 1 0 0 1-1-1v-5Zm10 0h6v5a1 1 0 0 1-1 1h-5v-6Z"
            />
            <path
              v-else-if="item.icon === 'star'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 1-8 0m8 0v1a4 4 0 0 0 4 4m-8-5v1a4 4 0 0 1-4 4m8 0H8m0 0a5 5 0 0 0-5 5h18a5 5 0 0 0-5-5"
            />
          </svg>
        </div>

        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
          {{ item.label }}
        </p>

        <h3 class="mt-3 text-3xl font-black tracking-[-0.04em] text-slate-950">
          {{ item.value }}
        </h3>

        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
          {{ item.helper }}
        </p>
      </div>
    </section>

    <!-- Form Divisi -->
    <section
      id="divisi-kepengurusan"
      data-aos="fade-up"
      class="grid gap-6 xl:grid-cols-[0.45fr_0.55fr]"
    >
      <!-- Add Division -->
      <div
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Tambah Divisi
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Data Divisi Baru
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Buat divisi kepengurusan seperti PSDM, Humas, Kominfo, Akademik, atau divisi
              lainnya.
            </p>
          </div>

          <form class="grid gap-5" @submit.prevent="storeDivision">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Nama Divisi
                <span class="text-red-600">*</span>
              </label>
              <input
                v-model="divisionForm.name"
                type="text"
                placeholder="Contoh: Divisi Kominfo"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="divisionForm.errors.name"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ divisionForm.errors.name }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Nama Singkat</label
              >
              <input
                v-model="divisionForm.short_name"
                type="text"
                placeholder="Contoh: KOMINFO"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="divisionForm.errors.short_name"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ divisionForm.errors.short_name }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Deskripsi</label
              >
              <textarea
                v-model="divisionForm.description"
                rows="4"
                placeholder="Tuliskan deskripsi singkat divisi..."
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="divisionForm.errors.description"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ divisionForm.errors.description }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Urutan Tampil</label
              >
              <input
                v-model="divisionForm.sort_order"
                type="number"
                min="0"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                Angka kecil akan tampil lebih awal pada halaman Kepengurusan.
              </p>
            </div>

            <button
              type="submit"
              :disabled="divisionForm.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                divisionForm.processing ? "Menyimpan..." : "Tambah Divisi"
              }}</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Division List -->
      <div
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
          >
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Daftar Divisi
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Kelola Divisi
              </h2>

              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Kelola nama, singkatan, deskripsi, dan urutan tampil divisi.
              </p>
            </div>

            <span
              class="w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
            >
              {{ divisions.length }} Divisi
            </span>
          </div>

          <div class="space-y-3">
            <div
              v-for="division in divisions"
              :key="division.id"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
            >
              <div v-if="editingDivision?.id !== division.id" class="p-4">
                <div
                  class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
                >
                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <p class="text-base font-black text-slate-950">
                        {{ division.name }}
                      </p>

                      <span
                        class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                      >
                        {{ division.short_name || "Tanpa Singkatan" }}
                      </span>
                    </div>

                    <p class="mt-2 text-sm font-semibold text-slate-500">
                      {{ division.members_count }} anggota • Urutan
                      {{ division.sort_order }}
                    </p>

                    <p class="mt-3 text-justify text-sm leading-7 text-slate-500">
                      {{ division.description || "Belum ada deskripsi divisi." }}
                    </p>
                  </div>

                  <div class="flex shrink-0 gap-2">
                    <button
                      type="button"
                      class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                      @click="editDivision(division)"
                    >
                      Edit
                    </button>

                    <button
                      type="button"
                      class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                      @click="deleteDivision(division)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>
              </div>

              <form
                v-else
                class="grid gap-4 bg-white p-4"
                @submit.prevent="updateDivision"
              >
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Divisi
                    </span>
                    <h3 class="mt-2 text-lg font-black text-slate-950">
                      Edit {{ division.name }}
                    </h3>
                  </div>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditDivision"
                  >
                    Batal Edit
                  </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-2 block text-sm font-extrabold text-slate-800"
                      >Nama Divisi</label
                    >
                    <input
                      v-model="divisionEditForm.name"
                      type="text"
                      class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />
                  </div>

                  <div>
                    <label class="mb-2 block text-sm font-extrabold text-slate-800"
                      >Nama Singkat</label
                    >
                    <input
                      v-model="divisionEditForm.short_name"
                      type="text"
                      class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />
                  </div>
                </div>

                <div>
                  <label class="mb-2 block text-sm font-extrabold text-slate-800"
                    >Deskripsi</label
                  >
                  <textarea
                    v-model="divisionEditForm.description"
                    rows="3"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <div
                  class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                  <div class="sm:w-40">
                    <label class="mb-2 block text-sm font-extrabold text-slate-800"
                      >Urutan</label
                    >
                    <input
                      v-model="divisionEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />
                  </div>

                  <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditDivision"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="divisionEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {{ divisionEditForm.processing ? "Menyimpan..." : "Simpan Divisi" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!divisions.length"
              class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
            >
              <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M4 5a1 1 0 0 1 1-1h5v6H4V5Zm10-1h5a1 1 0 0 1 1 1v5h-6V4ZM4 14h6v6H5a1 1 0 0 1-1-1v-5Zm10 0h6v5a1 1 0 0 1-1 1h-5v-6Z"
                  />
                </svg>
              </div>

              <h3 class="mt-4 text-lg font-black text-slate-950">Belum ada divisi</h3>
              <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                Tambahkan divisi terlebih dahulu agar koordinator dan anggota dapat
                dikelompokkan.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Form Pengurus -->
    <section
      id="form-pengurus"
      data-aos="fade-up"
      class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div
          class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Tambah Pengurus
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Data Pengurus Baru
            </h2>

            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Untuk pengurus inti, pilih kategori Pengurus Inti dan divisi boleh
              dikosongkan. Untuk koordinator atau anggota, pilih divisi yang sesuai.
            </p>
          </div>

          <span
            class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
          >
            JPG / PNG / WEBP
          </span>
        </div>

        <form class="grid gap-5 lg:grid-cols-2" @submit.prevent="storeMember">
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Nama Pengurus
              <span class="text-red-600">*</span>
            </label>
            <input
              v-model="memberForm.name"
              type="text"
              placeholder="Nama lengkap"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p v-if="memberForm.errors.name" class="mt-2 text-xs font-bold text-red-600">
              {{ memberForm.errors.name }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Jabatan
              <span class="text-red-600">*</span>
            </label>
            <input
              v-model="memberForm.position"
              type="text"
              placeholder="Contoh: Ketua HMPS RPL"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p
              v-if="memberForm.errors.position"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ memberForm.errors.position }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Kategori</label
            >
            <select
              v-model="memberForm.category"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option
                v-for="category in categories"
                :key="category.value"
                :value="category.value"
              >
                {{ category.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">Divisi</label>
            <select
              v-model="memberForm.management_division_id"
              :disabled="memberForm.category === 'core'"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 disabled:ring-0"
            >
              <option value="">Pilih Divisi</option>
              <option
                v-for="division in divisions"
                :key="division.id"
                :value="division.id"
              >
                {{ division.name }}
              </option>
            </select>
            <p
              v-if="memberForm.category === 'core'"
              class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold leading-5 text-amber-700"
            >
              Divisi otomatis dikosongkan jika kategori Pengurus Inti.
            </p>
            <p v-else class="mt-2 text-xs font-semibold leading-5 text-slate-500">
              Pilih divisi untuk koordinator atau anggota divisi.
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Foto Pengurus</label
            >
            <label
              class="group flex min-h-[12rem] cursor-pointer flex-col items-center justify-center rounded-[1.35rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/40"
            >
              <div
                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm transition duration-300 group-hover:scale-105"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M12 16V4m0 0-4 4m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                  />
                </svg>
              </div>
              <p class="mt-4 text-sm font-black text-slate-950">
                Klik untuk memilih foto
              </p>
              <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
                Format JPG, JPEG, PNG, atau WEBP. Gunakan foto yang jelas dan
                proporsional.
              </p>
              <input
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="sr-only"
                @change="handlePhotoFile"
              />
            </label>
            <p
              v-if="memberForm.errors.photo_file"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ memberForm.errors.photo_file }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Urutan Tampil</label
            >
            <input
              v-model="memberForm.sort_order"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
              Angka kecil akan tampil lebih awal pada halaman user.
            </p>

            <div
              v-if="previewPhoto"
              class="mt-4 w-fit rounded-[1.3rem] border border-slate-200 bg-white p-2 shadow-[0_12px_30px_rgba(2,6,23,0.08)]"
            >
              <img
                :src="previewPhoto"
                alt="Preview foto"
                class="h-36 w-36 rounded-[1.05rem] object-cover"
              />
              <p class="px-2 pt-2 text-xs font-bold text-slate-500">Preview foto baru</p>
            </div>
          </div>

          <div class="flex justify-end lg:col-span-2">
            <button
              type="submit"
              :disabled="memberForm.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                memberForm.processing ? "Menyimpan..." : "Tambah Pengurus"
              }}</span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Daftar Pengurus -->
    <section
      id="daftar-pengurus"
      data-aos="fade-up"
      class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Data Pengurus
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Daftar Pengurus
            </h2>

            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Pengurus inti tampil di bagian atas halaman Kepengurusan. Koordinator dan
              anggota tampil berdasarkan divisi masing-masing.
            </p>
          </div>

          <span
            class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
          >
            {{ members.length }} Pengurus
          </span>
        </div>

        <div
          class="mb-7 grid gap-3 rounded-[1.45rem] border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_220px_auto] md:items-center"
        >
          <div class="relative">
            <svg
              class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.3"
                d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
              />
            </svg>
            <input
              v-model="memberSearch"
              type="text"
              placeholder="Cari nama, jabatan, atau divisi..."
              class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
            />
          </div>

          <select
            v-model="selectedMemberCategory"
            class="h-[3.15rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
          >
            <option value="all">Semua Kategori</option>
            <option
              v-for="category in categories"
              :key="`filter-${category.value}`"
              :value="category.value"
            >
              {{ category.label }}
            </option>
          </select>

          <button
            type="button"
            class="inline-flex min-h-[3.15rem] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black uppercase tracking-[0.08em] text-slate-600 transition hover:bg-red-50 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!hasActiveMemberFilter"
            @click="resetMemberFilter"
          >
            Reset Filter
          </button>

          <p class="text-xs font-bold leading-5 text-slate-500 md:col-span-3">
            Menampilkan {{ filteredMemberCount }} dari {{ members.length }} pengurus.
          </p>
        </div>

        <!-- Pengurus Inti -->
        <div>
          <div class="mb-4 flex items-center justify-between gap-4">
            <h3 class="text-sm font-black uppercase tracking-[0.12em] text-slate-400">
              Pengurus Inti
            </h3>
            <span
              class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700 ring-1 ring-red-100"
            >
              {{ filteredCoreMembers.length }} Data
            </span>
          </div>

          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="member in filteredCoreMembers"
              :key="member.id"
              class="group overflow-hidden rounded-[1.45rem] border border-slate-200 bg-slate-50 transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
            >
              <div v-if="editingMember?.id !== member.id" class="p-4">
                <div class="flex gap-4">
                  <img
                    :src="getPhoto(member.photo_url)"
                    :alt="member.name"
                    class="h-24 w-24 shrink-0 rounded-[1.15rem] object-cover shadow-sm ring-1 ring-white transition duration-300 group-hover:scale-[1.03]"
                  />

                  <div class="min-w-0 flex-1">
                    <span
                      class="inline-flex rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="categoryClass(member.category)"
                    >
                      {{ categoryLabel(member.category) }}
                    </span>

                    <h4 class="mt-3 line-clamp-2 font-black leading-snug text-slate-950">
                      {{ member.name }}
                    </h4>

                    <p class="mt-1 text-sm font-semibold text-slate-500">
                      {{ member.position }}
                    </p>

                    <p class="mt-1 text-xs font-semibold text-slate-400">
                      Urutan {{ member.sort_order }}
                    </p>
                  </div>
                </div>

                <div class="mt-4 flex gap-2">
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                    @click="editMember(member)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteMember(member)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <div v-else class="bg-white p-4">
                <MemberEditForm
                  :member-edit-form="memberEditForm"
                  :divisions="divisions"
                  :categories="categories"
                  :edit-preview-photo="editPreviewPhoto"
                  @submit="updateMember"
                  @cancel="cancelEditMember"
                  @photo-change="handleEditPhotoFile"
                />
              </div>
            </div>

            <div
              v-if="!filteredCoreMembers.length"
              class="rounded-[1.45rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center md:col-span-2 xl:col-span-3"
            >
              <p class="text-sm font-bold text-slate-500">Belum ada pengurus inti.</p>
            </div>
          </div>
        </div>

        <div
          v-if="hasActiveMemberFilter && !filteredMemberCount"
          class="mt-8 rounded-[1.45rem] border border-dashed border-red-200 bg-red-50/50 p-8 text-center"
        >
          <div
            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.25"
                d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
              />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-black text-slate-950">Data tidak ditemukan</h3>
          <p class="mx-auto mt-2 max-w-lg text-sm font-semibold leading-7 text-slate-500">
            Tidak ada pengurus yang sesuai dengan kata kunci atau kategori yang dipilih.
          </p>
        </div>

        <!-- Divisi -->
        <div class="mt-10 space-y-6">
          <div
            v-for="division in visibleDivisionMembers"
            :key="division.id"
            class="overflow-hidden rounded-[1.6rem] border border-slate-200 bg-slate-50"
          >
            <div class="border-b border-slate-200 bg-white p-5">
              <div
                class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
              >
                <div>
                  <p class="text-xs font-black uppercase tracking-[0.12em] text-red-600">
                    {{ division.short_name || division.name }}
                  </p>

                  <h3 class="mt-1 text-xl font-black tracking-[-0.03em] text-slate-950">
                    {{ division.name }}
                  </h3>

                  <p class="mt-2 max-w-2xl text-justify text-sm leading-7 text-slate-500">
                    {{ division.description || "Belum ada deskripsi divisi." }}
                  </p>
                </div>

                <span
                  class="w-fit rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
                >
                  {{ memberTotalByDivision(division) }} Pengurus
                </span>
              </div>
            </div>

            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
              <div
                v-for="member in [...division.coordinators, ...division.members]"
                :key="member.id"
                class="group overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
              >
                <div v-if="editingMember?.id !== member.id" class="p-4">
                  <div class="flex gap-4">
                    <img
                      :src="getPhoto(member.photo_url)"
                      :alt="member.name"
                      class="h-24 w-24 shrink-0 rounded-[1.15rem] object-cover shadow-sm ring-1 ring-slate-100 transition duration-300 group-hover:scale-[1.03]"
                    />

                    <div class="min-w-0 flex-1">
                      <span
                        class="inline-flex rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="categoryClass(member.category)"
                      >
                        {{ categoryLabel(member.category) }}
                      </span>

                      <h4
                        class="mt-3 line-clamp-2 font-black leading-snug text-slate-950"
                      >
                        {{ member.name }}
                      </h4>

                      <p class="mt-1 text-sm font-semibold text-slate-500">
                        {{ member.position }}
                      </p>

                      <p class="mt-1 text-xs font-semibold text-slate-400">
                        Urutan {{ member.sort_order }}
                      </p>
                    </div>
                  </div>

                  <div class="mt-4 flex gap-2">
                    <button
                      type="button"
                      class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-slate-50 px-4 text-xs font-black text-slate-700 ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                      @click="editMember(member)"
                    >
                      Edit
                    </button>

                    <button
                      type="button"
                      class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                      @click="deleteMember(member)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>

                <div v-else class="p-4">
                  <MemberEditForm
                    :member-edit-form="memberEditForm"
                    :divisions="divisions"
                    :categories="categories"
                    :edit-preview-photo="editPreviewPhoto"
                    @submit="updateMember"
                    @cancel="cancelEditMember"
                    @photo-change="handleEditPhotoFile"
                  />
                </div>
              </div>

              <div
                v-if="!division.coordinators.length && !division.members.length"
                class="rounded-[1.35rem] border border-dashed border-slate-300 bg-white p-8 text-center md:col-span-2 xl:col-span-3"
              >
                <p class="text-sm font-bold text-slate-500">
                  Belum ada pengurus di divisi ini atau tidak sesuai filter aktif.
                </p>
              </div>
            </div>
          </div>

          <div
            v-if="!divisions.length"
            class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
          >
            <h3 class="text-lg font-black text-slate-950">Belum ada data divisi</h3>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-7 text-slate-500">
              Tambahkan divisi terlebih dahulu sebelum mengelompokkan koordinator dan
              anggota.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
:global(html) {
  scroll-behavior: smooth;
}
</style>

<style scoped>
@media (max-width: 639px) {
  .custom-kepengurusan-mobile-page,
  .custom-kepengurusan-mobile-page * {
    box-sizing: border-box;
  }

  .custom-kepengurusan-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-kepengurusan-mobile-page :is(section, article, form, div) {
    min-width: 0;
  }

  .custom-kepengurusan-mobile-page :is(input, select, textarea, button, a, img) {
    max-width: 100%;
  }

  .custom-kepengurusan-mobile-page p {
    text-align: left !important;
  }

  .custom-kepengurusan-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-kepengurusan-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-kepengurusan-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type .relative.z-10.flex {
    gap: 1rem !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type .mt-7.grid {
    margin-top: 1.15rem !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 0.65rem !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type .mt-7.grid > div {
    padding: 0.7rem !important;
    border-radius: 1rem !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type .mt-7.grid p:first-child {
    font-size: 0.58rem !important;
    line-height: 1.25 !important;
  }

  .custom-kepengurusan-mobile-page > section:first-of-type .mt-7.grid p:last-child {
    font-size: 1.28rem !important;
    line-height: 1.05 !important;
    margin-top: 0.35rem !important;
  }

  .custom-kepengurusan-mobile-page > section:nth-of-type(2) {
    display: flex !important;
    gap: 0.65rem !important;
    overflow-x: auto !important;
    padding-bottom: 0.35rem !important;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
  }

  .custom-kepengurusan-mobile-page > section:nth-of-type(2) > a {
    min-width: 12rem !important;
    width: 12rem !important;
    scroll-snap-align: start;
    padding: 0.85rem !important;
    border-radius: 1.05rem !important;
    gap: 0.75rem !important;
  }

  .custom-kepengurusan-mobile-page > section:nth-of-type(2) > a > span:first-child {
    width: 2.4rem !important;
    height: 2.4rem !important;
    border-radius: 0.9rem !important;
  }

  .custom-kepengurusan-mobile-page > section:nth-of-type(2) > a .block.text-sm {
    font-size: 0.82rem !important;
  }

  .custom-kepengurusan-mobile-page > section:nth-of-type(2) > a .mt-1.block {
    font-size: 0.7rem !important;
    line-height: 1.35 !important;
  }

  .custom-kepengurusan-mobile-page > section:nth-of-type(3),
  .custom-kepengurusan-mobile-page > section:nth-of-type(4),
  .custom-kepengurusan-mobile-page > section:nth-of-type(5),
  .custom-kepengurusan-mobile-page > section:nth-of-type(6),
  .custom-kepengurusan-mobile-page > section:nth-of-type(7) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-kepengurusan-mobile-page > section > div:not(.h-1\.5),
  .custom-kepengurusan-mobile-page > section > form {
    padding: 1rem !important;
  }

  .custom-kepengurusan-mobile-page .rounded-\[1\.35rem\],
  .custom-kepengurusan-mobile-page .rounded-\[1\.45rem\],
  .custom-kepengurusan-mobile-page .rounded-\[1\.5rem\],
  .custom-kepengurusan-mobile-page .rounded-\[1\.8rem\],
  .custom-kepengurusan-mobile-page .rounded-\[2rem\],
  .custom-kepengurusan-mobile-page .rounded-\[2\.25rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-kepengurusan-mobile-page label,
  .custom-kepengurusan-mobile-page .text-sm.font-bold,
  .custom-kepengurusan-mobile-page .text-sm.font-black,
  .custom-kepengurusan-mobile-page .text-sm.font-extrabold {
    font-size: 0.82rem !important;
  }

  .custom-kepengurusan-mobile-page input:not([type="checkbox"]):not([type="file"]),
  .custom-kepengurusan-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-kepengurusan-mobile-page input[type="file"] {
    width: 100% !important;
    font-size: 0.78rem !important;
  }

  .custom-kepengurusan-mobile-page textarea {
    min-height: 112px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-kepengurusan-mobile-page button,
  .custom-kepengurusan-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-kepengurusan-mobile-page button[type="submit"],
  .custom-kepengurusan-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-kepengurusan-mobile-page .grid.lg\:grid-cols-2,
  .custom-kepengurusan-mobile-page .grid.lg\:grid-cols-3,
  .custom-kepengurusan-mobile-page .grid.sm\:grid-cols-2,
  .custom-kepengurusan-mobile-page .grid.md\:grid-cols-2,
  .custom-kepengurusan-mobile-page .grid.xl\:grid-cols-3,
  .custom-kepengurusan-mobile-page .grid.xl\:grid-cols-4,
  .custom-kepengurusan-mobile-page .grid.md\:grid-cols-\[1fr_220px_auto\],
  .custom-kepengurusan-mobile-page .grid.xl\:grid-cols-\[0\.45fr_0\.55fr\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-kepengurusan-mobile-page .lg\:col-span-2,
  .custom-kepengurusan-mobile-page .md\:col-span-3 {
    grid-column: auto !important;
  }

  .custom-kepengurusan-mobile-page .grid.grid-cols-2.gap-3,
  .custom-kepengurusan-mobile-page .grid.grid-cols-2.gap-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-kepengurusan-mobile-page
    .grid.gap-5.lg\:grid-cols-2
    > .rounded-\[1\.35rem\]
    .flex.items-start.gap-4 {
    flex-direction: column !important;
    gap: 0.85rem !important;
  }

  .custom-kepengurusan-mobile-page .h-28.w-28 {
    width: 100% !important;
    height: 10rem !important;
  }

  .custom-kepengurusan-mobile-page .group.flex.min-h-\[12rem\] {
    min-height: 10.25rem !important;
    padding: 1.25rem !important;
    border-radius: 1.05rem !important;
  }

  .custom-kepengurusan-mobile-page .h-14.w-14,
  .custom-kepengurusan-mobile-page .h-12.w-12,
  .custom-kepengurusan-mobile-page .h-11.w-11 {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-kepengurusan-mobile-page .flex.justify-end,
  .custom-kepengurusan-mobile-page .flex.flex-col-reverse,
  .custom-kepengurusan-mobile-page .flex.shrink-0.gap-2,
  .custom-kepengurusan-mobile-page .flex.gap-2.lg\:flex-col {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
  }

  .custom-kepengurusan-mobile-page .flex.shrink-0.gap-2,
  .custom-kepengurusan-mobile-page .flex.gap-2.lg\:flex-col {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    width: 100% !important;
  }

  .custom-kepengurusan-mobile-page .flex.flex-col.justify-center.gap-3,
  .custom-kepengurusan-mobile-page .flex.flex-col.gap-4.border-t {
    align-items: stretch !important;
  }

  .custom-kepengurusan-mobile-page .inline-flex.w-fit,
  .custom-kepengurusan-mobile-page .w-fit {
    max-width: 100%;
  }

  .custom-kepengurusan-mobile-page .h-36.w-36 {
    width: 100% !important;
    height: 12rem !important;
  }

  .custom-kepengurusan-mobile-page .group.overflow-hidden img,
  .custom-kepengurusan-mobile-page img.object-cover {
    max-width: 100%;
  }

  .custom-kepengurusan-mobile-page .truncate {
    min-width: 0;
  }

  .custom-kepengurusan-mobile-page .break-all,
  .custom-kepengurusan-mobile-page .break-words,
  .custom-kepengurusan-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-kepengurusan-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-kepengurusan-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-kepengurusan-mobile-page .flex.flex-wrap.items-center.gap-2 > span,
  .custom-kepengurusan-mobile-page .mt-3.flex.flex-wrap.gap-2 > span {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-kepengurusan-mobile-page .p-5,
  .custom-kepengurusan-mobile-page .sm\:p-6,
  .custom-kepengurusan-mobile-page .lg\:p-7,
  .custom-kepengurusan-mobile-page .lg\:p-8,
  .custom-kepengurusan-mobile-page .p-4 {
    padding: 1rem !important;
  }

  .custom-kepengurusan-mobile-page .mt-7,
  .custom-kepengurusan-mobile-page .mt-6 {
    margin-top: 1.25rem !important;
  }

  .custom-kepengurusan-mobile-page .mb-7,
  .custom-kepengurusan-mobile-page .mb-6 {
    margin-bottom: 1.25rem !important;
  }

  .custom-kepengurusan-mobile-page .gap-5,
  .custom-kepengurusan-mobile-page .gap-6 {
    gap: 1rem !important;
  }

  .custom-kepengurusan-mobile-page .gap-8 {
    gap: 1.25rem !important;
  }

  .custom-kepengurusan-mobile-page .space-y-8 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 1.5rem !important;
  }

  .custom-kepengurusan-mobile-page .shadow-\[0_16px_44px_rgba\(15\,23\,42\,0\.06\)\],
  .custom-kepengurusan-mobile-page .shadow-\[0_14px_36px_rgba\(15\,23\,42\,0\.05\)\] {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055) !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-kepengurusan-mobile-page *,
  .custom-kepengurusan-mobile-page *::before,
  .custom-kepengurusan-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

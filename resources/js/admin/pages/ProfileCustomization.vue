<script setup>
import { computed, onUnmounted, reactive, ref, watch } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  sections: {
    type: Array,
    default: () => [],
  },
  items: {
    type: Array,
    default: () => [],
  },
  heroImages: {
    type: Array,
    default: () => [],
  },
  sectionOptions: {
    type: Array,
    default: () => [],
  },
  groupOptions: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const selectedSectionKey = ref("hero");
const editingItem = ref(null);
const editingHeroImage = ref(null);

const heroImagePreview = ref(null);
const heroImageEditPreview = ref(null);

const itemSearch = ref("");
const selectedItemGroup = ref("all");
const selectedItemStatus = ref("all");

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const sections = computed(() => (Array.isArray(props.sections) ? props.sections : []));
const items = computed(() => (Array.isArray(props.items) ? props.items : []));
const profileHeroImages = computed(() =>
  Array.isArray(props.heroImages) ? props.heroImages : []
);

const sectionOptions = computed(() =>
  Array.isArray(props.sectionOptions) ? props.sectionOptions : []
);

const groupOptions = computed(() =>
  Array.isArray(props.groupOptions) ? props.groupOptions : []
);

const activeHeroImageCount = computed(() => {
  return profileHeroImages.value.filter((item) => Boolean(item.is_active)).length;
});

const sectionLabels = computed(() =>
  sectionOptions.value.reduce((result, item) => {
    result[item.key] = item.label;
    return result;
  }, {})
);

const groupLabels = computed(() =>
  groupOptions.value.reduce((result, item) => {
    result[item.value] = item.label;
    return result;
  }, {})
);

const activeSection = computed(() => {
  return sections.value.find((item) => item.key === selectedSectionKey.value) || null;
});

const stats = computed(() => [
  {
    label: "Section Profil",
    value: sections.value.length,
    helper: "Hero, Tentang, Sejarah, Visi Misi",
    icon: "layout",
  },
  {
    label: "Foto Hero",
    value: `${activeHeroImageCount.value}/4`,
    helper: "Foto aktif pada kolase hero",
    icon: "image",
  },
  {
    label: "Sejarah",
    value: items.value.filter((item) => item.group === "history").length,
    helper: "Periode kepengurusan",
    icon: "history",
  },
  {
    label: "Total Item",
    value: items.value.length,
    helper: "Seluruh konten dinamis",
    icon: "database",
  },
]);

const heroMeta = reactive({
  floating_top_title: "",
  floating_top_subtitle: "",
  floating_bottom_title: "",
  floating_bottom_subtitle: "",
});

const aboutMeta = reactive({
  paragraph_2: "",
  paragraph_3: "",
  function_title: "",
});

const visionMeta = reactive({
  vision: "",
});

const sectionForm = useForm({
  badge: "",
  title: "",
  title_highlight: "",
  description: "",
  primary_button_label: "",
  primary_button_url: "",
  meta: {},
  is_active: true,
  sort_order: 0,
});

const heroImageForm = useForm({
  title: "",
  alt_text: "",
  image_file: null,
  is_active: true,
  sort_order: 0,
});

const heroImageEditForm = useForm({
  title: "",
  alt_text: "",
  image_file: null,
  is_active: true,
  sort_order: 0,
});

const itemForm = useForm({
  group: "ticker",
  label: "",
  title: "",
  subtitle: "",
  name: "",
  position: "",
  description: "",
  meta: {
    ketua: "",
    wakil: "",
  },
  is_active: true,
  sort_order: 0,
});

const itemEditForm = useForm({
  group: "ticker",
  label: "",
  title: "",
  subtitle: "",
  name: "",
  position: "",
  description: "",
  meta: {
    ketua: "",
    wakil: "",
  },
  is_active: true,
  sort_order: 0,
});

const groupedItems = computed(() => {
  return groupOptions.value.map((group) => ({
    ...group,
    items: items.value.filter((item) => item.group === group.value),
  }));
});

const activeItemCount = computed(
  () => items.value.filter((item) => item.is_active).length
);

const filteredGroupedItems = computed(() => {
  const keyword = itemSearch.value.trim().toLowerCase();

  return groupedItems.value
    .filter(
      (group) =>
        selectedItemGroup.value === "all" || group.value === selectedItemGroup.value
    )
    .map((group) => ({
      ...group,
      items: group.items.filter((item) => {
        const searchableText = [
          item.group,
          item.label,
          item.title,
          item.subtitle,
          item.name,
          item.position,
          item.description,
          item.meta?.ketua,
          item.meta?.wakil,
        ]
          .filter(Boolean)
          .join(" ")
          .toLowerCase();

        const matchesKeyword = !keyword || searchableText.includes(keyword);
        const matchesStatus =
          selectedItemStatus.value === "all" ||
          (selectedItemStatus.value === "active" && item.is_active) ||
          (selectedItemStatus.value === "inactive" && !item.is_active);

        return matchesKeyword && matchesStatus;
      }),
    }));
});

const hasItemFilters = computed(() =>
  Boolean(
    itemSearch.value ||
      selectedItemGroup.value !== "all" ||
      selectedItemStatus.value !== "all"
  )
);

const clearItemFilters = () => {
  itemSearch.value = "";
  selectedItemGroup.value = "all";
  selectedItemStatus.value = "all";
};

const isSelectedSection = (key) => selectedSectionKey.value === key;

const sectionButtonClass = (section) => {
  if (isSelectedSection(section.key)) {
    return "border border-slate-800 bg-[linear-gradient(135deg,#0f172a,#1e293b_65%,#7f1d1d)] text-white shadow-[0_16px_36px_rgba(15,23,42,0.22)]";
  }

  return "border border-slate-200 bg-slate-50/90 text-slate-800 hover:border-red-200 hover:bg-white hover:shadow-[0_12px_30px_rgba(15,23,42,0.06)]";
};

const sectionBadgeClass = (section) => {
  if (isSelectedSection(section.key)) {
    return section.is_active
      ? "bg-white/10 text-white ring-1 ring-white/20"
      : "bg-white/10 text-white/70 ring-1 ring-white/20";
  }

  return section.is_active
    ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
    : "bg-slate-100 text-slate-500 ring-1 ring-slate-200";
};

const sectionDotClass = (section) => {
  if (isSelectedSection(section.key)) {
    return section.is_active ? "bg-red-300" : "bg-white/40";
  }

  return section.is_active ? "bg-emerald-500" : "bg-slate-400";
};

const itemStatusClass = (isActive) => {
  return isActive
    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
    : "border-slate-200 bg-slate-100 text-slate-600";
};

const heroImageStatusClass = (isActive) => {
  return isActive
    ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
    : "bg-slate-100 text-slate-600 ring-1 ring-slate-200";
};

const groupBadgeClass = (group) => {
  if (group === "ticker") return "border-red-200 bg-red-50 text-red-700";
  if (group === "history") return "border-blue-200 bg-blue-50 text-blue-700";
  if (group === "mission") return "border-emerald-200 bg-emerald-50 text-emerald-700";
  if (group === "identity") return "border-violet-200 bg-violet-50 text-violet-700";
  if (group === "function") return "border-amber-200 bg-amber-50 text-amber-700";

  return "border-slate-200 bg-slate-100 text-slate-700";
};

const resetSectionMeta = () => {
  heroMeta.floating_top_title = "";
  heroMeta.floating_top_subtitle = "";
  heroMeta.floating_bottom_title = "";
  heroMeta.floating_bottom_subtitle = "";

  aboutMeta.paragraph_2 = "";
  aboutMeta.paragraph_3 = "";
  aboutMeta.function_title = "";

  visionMeta.vision = "";
};

const fillSectionForm = (section) => {
  if (!section) return;

  sectionForm.badge = section.badge || "";
  sectionForm.title = section.title || "";
  sectionForm.title_highlight = section.title_highlight || "";
  sectionForm.description = section.description || "";
  sectionForm.primary_button_label = section.primary_button_label || "";
  sectionForm.primary_button_url = section.primary_button_url || "";
  sectionForm.is_active = Boolean(section.is_active);
  sectionForm.sort_order = Number(section.sort_order || 0);

  resetSectionMeta();

  if (section.key === "hero") {
    heroMeta.floating_top_title = section.meta?.floating_top_title || "";
    heroMeta.floating_top_subtitle = section.meta?.floating_top_subtitle || "";
    heroMeta.floating_bottom_title = section.meta?.floating_bottom_title || "";
    heroMeta.floating_bottom_subtitle = section.meta?.floating_bottom_subtitle || "";
  }

  if (section.key === "about") {
    aboutMeta.paragraph_2 = section.meta?.paragraph_2 || "";
    aboutMeta.paragraph_3 = section.meta?.paragraph_3 || "";
    aboutMeta.function_title = section.meta?.function_title || "";
  }

  if (section.key === "vision_mission") {
    visionMeta.vision = section.meta?.vision || "";
  }
};

watch(
  activeSection,
  (section) => {
    fillSectionForm(section);
  },
  {
    immediate: true,
  }
);

const buildSectionMeta = () => {
  if (activeSection.value?.key === "hero") {
    return { ...heroMeta };
  }

  if (activeSection.value?.key === "about") {
    return { ...aboutMeta };
  }

  if (activeSection.value?.key === "vision_mission") {
    return { ...visionMeta };
  }

  return {};
};

const updateSection = () => {
  if (!activeSection.value) return;

  sectionForm.meta = buildSectionMeta();

  sectionForm.put(`/admin/profil/sections/${activeSection.value.id}`, {
    preserveScroll: true,
  });
};

const revokePreviewUrl = (url) => {
  if (url && String(url).startsWith("blob:")) {
    URL.revokeObjectURL(url);
  }
};

const handleHeroImageFile = (event) => {
  const file = event.target.files?.[0] || null;

  revokePreviewUrl(heroImagePreview.value);
  heroImageForm.image_file = file;
  heroImagePreview.value = file ? URL.createObjectURL(file) : null;
};

const handleHeroImageEditFile = (event) => {
  const file = event.target.files?.[0] || null;

  revokePreviewUrl(heroImageEditPreview.value);
  heroImageEditForm.image_file = file;
  heroImageEditPreview.value = file ? URL.createObjectURL(file) : null;
};

const resetHeroImageForm = () => {
  revokePreviewUrl(heroImagePreview.value);
  heroImageForm.reset();
  heroImageForm.title = "";
  heroImageForm.alt_text = "";
  heroImageForm.image_file = null;
  heroImageForm.is_active = true;
  heroImageForm.sort_order = 0;
  heroImagePreview.value = null;
};

const storeHeroImage = () => {
  heroImageForm.post("/admin/profil/hero-images", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => resetHeroImageForm(),
  });
};

const editHeroImage = (item) => {
  editingHeroImage.value = item;

  heroImageEditForm.title = item.title || "";
  heroImageEditForm.alt_text = item.alt_text || "";
  heroImageEditForm.image_file = null;
  heroImageEditForm.is_active = Boolean(item.is_active);
  heroImageEditForm.sort_order = Number(item.sort_order || 0);
  heroImageEditPreview.value = null;
};

const cancelEditHeroImage = () => {
  revokePreviewUrl(heroImageEditPreview.value);
  editingHeroImage.value = null;
  heroImageEditForm.reset();
  heroImageEditForm.title = "";
  heroImageEditForm.alt_text = "";
  heroImageEditForm.image_file = null;
  heroImageEditForm.is_active = true;
  heroImageEditForm.sort_order = 0;
  heroImageEditPreview.value = null;
};

const updateHeroImage = () => {
  if (!editingHeroImage.value) return;

  heroImageEditForm.post(`/admin/profil/hero-images/${editingHeroImage.value.id}`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => cancelEditHeroImage(),
  });
};

const deleteHeroImage = (item) => {
  const title = item.title || "foto hero ini";

  if (!confirm(`Hapus ${title}?`)) return;

  router.delete(`/admin/profil/hero-images/${item.id}`, {
    preserveScroll: true,
  });
};

const resetItemForm = () => {
  itemForm.reset();
  itemForm.group = "ticker";
  itemForm.label = "";
  itemForm.title = "";
  itemForm.subtitle = "";
  itemForm.name = "";
  itemForm.position = "";
  itemForm.description = "";
  itemForm.meta = {
    ketua: "",
    wakil: "",
  };
  itemForm.is_active = true;
  itemForm.sort_order = 0;
};

const storeItem = () => {
  itemForm.post("/admin/profil/items", {
    preserveScroll: true,
    onSuccess: () => resetItemForm(),
  });
};

const editItem = (item) => {
  editingItem.value = item;

  itemEditForm.group = item.group || "ticker";
  itemEditForm.label = item.label || "";
  itemEditForm.title = item.title || "";
  itemEditForm.subtitle = item.subtitle || "";
  itemEditForm.name = item.name || "";
  itemEditForm.position = item.position || "";
  itemEditForm.description = item.description || "";
  itemEditForm.meta = {
    ketua: item.meta?.ketua || "",
    wakil: item.meta?.wakil || "",
  };
  itemEditForm.is_active = Boolean(item.is_active);
  itemEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditItem = () => {
  editingItem.value = null;
  itemEditForm.reset();
  itemEditForm.meta = {
    ketua: "",
    wakil: "",
  };
};

const updateItem = () => {
  if (!editingItem.value) return;

  itemEditForm.put(`/admin/profil/items/${editingItem.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditItem(),
  });
};

const deleteItem = (item) => {
  const title = item.title || item.label || item.description || "item ini";

  if (!confirm(`Hapus ${title}?`)) return;

  router.delete(`/admin/profil/items/${item.id}`, {
    preserveScroll: true,
  });
};

const displayItemTitle = (item) => {
  if (item.group === "ticker") return item.label || "Ticker tanpa teks";
  if (item.group === "history") return item.title || item.label || "Sejarah tanpa judul";
  if (item.group === "mission") return item.description || "Misi tanpa isi";
  if (item.group === "identity") return item.title || "Identitas tanpa judul";
  if (item.group === "function") return item.description || "Fungsi tanpa isi";

  return item.title || item.label || "Item Profil";
};

const displayItemSubtitle = (item) => {
  if (item.group === "history") {
    return `${item.label || "Tanpa periode"} • Ketua: ${
      item.meta?.ketua || "Belum diisi"
    } • Wakil: ${item.meta?.wakil || "Belum diisi"}`;
  }

  if (item.group === "identity") {
    return item.description || "Belum ada deskripsi";
  }

  return item.is_active ? "Aktif" : "Nonaktif";
};

onUnmounted(() => {
  revokePreviewUrl(heroImagePreview.value);
  revokePreviewUrl(heroImageEditPreview.value);
});
</script>

<template>
  <Head title="Custom Profil" />

  <div class="custom-profil-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8">
    <!-- Header -->
    <section
      id="profil-overview"
      data-aos="fade-up"
      class="relative overflow-hidden rounded-[2rem] border border-slate-800 bg-[radial-gradient(circle_at_top_right,rgba(239,68,68,0.30),transparent_32%),linear-gradient(135deg,#020617,#0f172a_46%,#450a0a)] p-5 text-white shadow-[0_28px_80px_rgba(2,6,23,0.28)] sm:p-8 lg:p-10"
    >
      <div
        class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-red-500/20 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-white/10 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"
      />

      <div class="relative z-10 grid gap-8 xl:grid-cols-[1fr_380px] xl:items-end">
        <div>
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-[0.7rem] font-black uppercase tracking-[0.14em] text-red-100 ring-1 ring-white/15"
          >
            <span
              class="h-2 w-2 rounded-full bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.16)]"
            />
            Admin / Custom Profil
          </div>

          <h1
            class="max-w-4xl text-[2.15rem] font-black leading-[1.04] tracking-[-0.055em] sm:text-[3rem] lg:text-[3.55rem]"
          >
            Kelola Profil Organisasi
            <span
              class="block bg-gradient-to-r from-red-200 via-red-300 to-white bg-clip-text text-transparent"
            >
              HMPS RPL
            </span>
          </h1>

          <p class="mt-5 max-w-3xl text-sm leading-8 text-slate-300 sm:text-base">
            Atur hero, foto kolase, tentang organisasi, sejarah kepemimpinan, visi misi,
            identitas, dan fungsi organisasi dalam satu panel yang rapi, konsisten, dan
            mudah dikelola.
          </p>

          <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a
              href="/profil"
              target="_blank"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-sm font-black text-slate-950 shadow-[0_16px_38px_rgba(255,255,255,0.14)] transition duration-300 hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700"
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
              <span class="relative z-10">Preview Profil</span>
            </a>

            <a
              href="#profil-section-editor"
              class="inline-flex min-h-[52px] items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 text-sm font-black text-white transition duration-300 hover:-translate-y-[2px] hover:bg-white/15"
            >
              Edit Section
            </a>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
          <div
            class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur-xl"
          >
            <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-300">
              Section Aktif
            </p>
            <p class="mt-2 text-2xl font-black tracking-[-0.04em] text-white">
              {{ sections.filter((section) => section.is_active).length }} /
              {{ sections.length }}
            </p>
          </div>
          <div
            class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur-xl"
          >
            <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-300">
              Foto Hero
            </p>
            <p class="mt-2 text-2xl font-black tracking-[-0.04em] text-white">
              {{ activeHeroImageCount }} / 4
            </p>
          </div>
          <div
            class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur-xl"
          >
            <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-300">
              Item Aktif
            </p>
            <p class="mt-2 text-2xl font-black tracking-[-0.04em] text-white">
              {{ activeItemCount }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Flash -->
    <div
      v-if="flashSuccess"
      data-aos="fade-up"
      class="flex items-start gap-3 rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_10px_28px_rgba(16,185,129,0.08)]"
    >
      <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-500" />
      <span>{{ flashSuccess }}</span>
    </div>

    <div
      v-if="flashError"
      data-aos="fade-up"
      class="flex items-start gap-3 rounded-[1.35rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_10px_28px_rgba(239,68,68,0.08)]"
    >
      <span class="mt-1 h-2.5 w-2.5 rounded-full bg-red-500" />
      <span>{{ flashError }}</span>
    </div>

    <!-- Stats -->
    <section data-aos="fade-up" class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
      <div
        v-for="item in stats"
        :key="item.label"
        class="group relative overflow-hidden rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-[0_16px_42px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_24px_58px_rgba(15,23,42,0.10)]"
      >
        <div
          class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-red-50 transition duration-300 group-hover:bg-red-100"
        />
        <div
          class="relative z-10 mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-[0_12px_24px_rgba(15,23,42,0.16)] transition group-hover:scale-105 group-hover:bg-red-700"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="item.icon === 'layout'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Zm0 5h16M9 21V10"
            />
            <path
              v-else-if="item.icon === 'image'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
            />
            <path
              v-else-if="item.icon === 'history'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 8v5l3 2m6-3a9 9 0 1 1-3-6.7M21 3v6h-6"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Zm4 4h8M8 15h5"
            />
          </svg>
        </div>
        <p
          class="relative z-10 text-xs font-black uppercase tracking-[0.12em] text-slate-400"
        >
          {{ item.label }}
        </p>
        <h3
          class="relative z-10 mt-3 text-3xl font-black tracking-[-0.04em] text-slate-950"
        >
          {{ item.value }}
        </h3>
        <p class="relative z-10 mt-2 text-xs font-semibold leading-5 text-slate-500">
          {{ item.helper }}
        </p>
      </div>
    </section>

    <!-- Quick Navigation -->
    <section data-aos="fade-up" class="profil-quick-nav grid gap-3 md:grid-cols-4">
      <a
        href="#profil-hero-images"
        class="rounded-[1.35rem] border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-1 hover:border-red-200 hover:bg-red-50 hover:text-red-700"
        >Foto Hero</a
      >
      <a
        href="#profil-section-editor"
        class="rounded-[1.35rem] border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-1 hover:border-red-200 hover:bg-red-50 hover:text-red-700"
        >Section Profil</a
      >
      <a
        href="#profil-add-item"
        class="rounded-[1.35rem] border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-1 hover:border-red-200 hover:bg-red-50 hover:text-red-700"
        >Tambah Item</a
      >
      <a
        href="#profil-item-list"
        class="rounded-[1.35rem] border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-1 hover:border-red-200 hover:bg-red-50 hover:text-red-700"
        >Daftar Item</a
      >
    </section>

    <!-- Hero Images Management -->
    <section
      id="profil-hero-images"
      data-aos="fade-up"
      class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_52px_rgba(15,23,42,0.07)]"
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
              Foto Hero Profil
            </div>
            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Kelola Foto Kolase Hero
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">
              Upload foto yang tampil pada kolase hero halaman Profil. Batasi maksimal 4
              foto aktif agar komposisi visual tetap rapi dan profesional.
            </p>
          </div>
          <span
            class="inline-flex w-fit rounded-full border border-red-100 bg-red-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            {{ activeHeroImageCount }} / 4 Foto Aktif
          </span>
        </div>

        <form class="grid gap-5 lg:grid-cols-2" @submit.prevent="storeHeroImage">
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Judul Foto</label
            >
            <input
              v-model="heroImageForm.title"
              type="text"
              placeholder="Contoh: Kegiatan HMPS RPL"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p
              v-if="heroImageForm.errors.title"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ heroImageForm.errors.title }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Alt Text</label
            >
            <input
              v-model="heroImageForm.alt_text"
              type="text"
              placeholder="Deskripsi gambar untuk aksesibilitas"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p
              v-if="heroImageForm.errors.alt_text"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ heroImageForm.errors.alt_text }}
            </p>
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Upload Foto</label
            >
            <label
              class="group relative flex min-h-[13rem] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.45rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/50"
            >
              <div
                class="absolute inset-0 opacity-0 transition duration-300 group-hover:opacity-100 bg-[radial-gradient(circle_at_center,rgba(239,68,68,0.08),transparent_55%)]"
              />
              <div
                class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm transition duration-300 group-hover:scale-105"
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
              <p class="relative mt-4 text-sm font-black text-slate-950">
                Klik untuk memilih foto hero
              </p>
              <p class="relative mt-1 max-w-md text-xs leading-5 text-slate-500">
                Format JPG, JPEG, PNG, atau WEBP. Maksimal 4MB.
              </p>
              <input
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="sr-only"
                @change="handleHeroImageFile"
              />
            </label>
            <p
              v-if="heroImageForm.errors.image_file"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ heroImageForm.errors.image_file }}
            </p>

            <div
              v-if="heroImagePreview"
              class="mt-4 overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white p-2 shadow-[0_14px_34px_rgba(15,23,42,0.08)]"
            >
              <img
                :src="heroImagePreview"
                alt="Preview foto hero profil"
                class="h-52 w-full rounded-[1.1rem] object-cover"
              />
              <p class="px-2 pt-2 text-xs font-bold text-slate-500">
                Preview foto hero profil
              </p>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Urutan Tampil</label
            >
            <input
              v-model="heroImageForm.sort_order"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p
              v-if="heroImageForm.errors.sort_order"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ heroImageForm.errors.sort_order }}
            </p>
          </div>

          <div class="flex items-end">
            <div
              class="flex w-full flex-col gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <label
                class="flex items-center gap-3 text-sm font-extrabold text-slate-700"
              >
                <input
                  v-model="heroImageForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Aktif
              </label>
              <button
                type="submit"
                :disabled="heroImageForm.processing"
                class="group relative inline-flex min-h-[48px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-slate-950 px-6 text-sm font-black text-white transition duration-300 hover:-translate-y-[2px] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />
                <span class="relative z-10">{{
                  heroImageForm.processing ? "Mengupload..." : "Tambah Foto"
                }}</span>
              </button>
            </div>
          </div>
        </form>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <article
            v-for="item in profileHeroImages"
            :key="item.id"
            class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
          >
            <template v-if="editingHeroImage?.id !== item.id">
              <div class="relative overflow-hidden">
                <img
                  :src="item.image_url"
                  :alt="item.alt_text || item.title || 'Foto hero profil'"
                  class="h-48 w-full object-cover transition duration-500 hover:scale-105"
                />
                <div
                  class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"
                />
                <span
                  class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-800"
                  >Hero</span
                >
              </div>

              <div class="p-4">
                <div class="mb-3 flex flex-wrap gap-2">
                  <span
                    class="rounded-full px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                    :class="heroImageStatusClass(item.is_active)"
                  >
                    {{ item.is_active ? "Aktif" : "Nonaktif" }}
                  </span>
                  <span
                    class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600 ring-1 ring-slate-200"
                  >
                    Urutan {{ item.sort_order }}
                  </span>
                </div>
                <h3 class="truncate text-base font-black text-slate-950">
                  {{ item.title || "Tanpa Judul" }}
                </h3>
                <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">
                  {{ item.alt_text || "Alt text belum diisi" }}
                </p>

                <div class="mt-4 grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                    @click="editHeroImage(item)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteHeroImage(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>
            </template>

            <form
              v-else
              class="grid gap-3 bg-white p-4"
              @submit.prevent="updateHeroImage"
            >
              <div
                class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3"
              >
                <div>
                  <span
                    class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >Mode Edit</span
                  >
                </div>
                <button
                  type="button"
                  class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-50"
                  @click="cancelEditHeroImage"
                >
                  Tutup
                </button>
              </div>
              <img
                :src="heroImageEditPreview || item.image_url"
                :alt="heroImageEditForm.alt_text || item.title || 'Preview foto hero'"
                class="h-40 w-full rounded-[1.25rem] object-cover"
              />
              <input
                v-model="heroImageEditForm.title"
                type="text"
                placeholder="Judul foto"
                class="h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroImageEditForm.alt_text"
                type="text"
                placeholder="Alt text"
                class="h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-red-600 file:px-3 file:py-2 file:text-xs file:font-black file:text-white"
                @change="handleHeroImageEditFile"
              />
              <input
                v-model="heroImageEditForm.sort_order"
                type="number"
                min="0"
                class="h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <label
                class="flex items-center gap-3 text-sm font-extrabold text-slate-700"
              >
                <input
                  v-model="heroImageEditForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Aktif
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="inline-flex min-h-[42px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                  @click="cancelEditHeroImage"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="heroImageEditForm.processing"
                  class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                >
                  {{ heroImageEditForm.processing ? "Menyimpan..." : "Simpan" }}
                </button>
              </div>
            </form>
          </article>
        </div>

        <div
          v-if="!profileHeroImages.length"
          class="mt-8 rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
        >
          <div
            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.25"
                d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
              />
            </svg>
          </div>
          <h3 class="mt-5 text-lg font-black text-slate-950">
            Belum ada foto hero profil
          </h3>
          <p class="mx-auto mt-2 max-w-xl text-sm leading-7 text-slate-500">
            Upload maksimal 4 foto aktif agar kolase hero halaman Profil tampil dinamis
            dari database.
          </p>
        </div>
      </div>
    </section>

    <!-- Section Editor -->
    <section
      id="profil-section-editor"
      data-aos="fade-up"
      class="grid gap-6 xl:grid-cols-[0.34fr_0.66fr]"
    >
      <div
        class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-[0_18px_52px_rgba(15,23,42,0.06)] sm:p-6"
      >
        <div class="mb-5">
          <div
            class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Section Profil
          </div>
          <h2 class="text-xl font-black tracking-[-0.03em] text-slate-950">
            Pilih Section
          </h2>
          <p class="mt-2 text-sm leading-7 text-slate-500">
            Pilih bagian halaman Profil yang ingin diatur kontennya.
          </p>
        </div>

        <div class="space-y-3">
          <button
            v-for="section in sections"
            :key="section.id"
            type="button"
            class="group flex w-full items-center justify-between gap-3 rounded-[1.35rem] border px-4 py-4 text-left transition duration-300"
            :class="sectionButtonClass(section)"
            @click="selectedSectionKey = section.key"
          >
            <div class="flex min-w-0 items-center gap-3">
              <span
                class="h-2.5 w-2.5 shrink-0 rounded-full transition"
                :class="sectionDotClass(section)"
              />
              <div class="min-w-0">
                <p
                  class="truncate text-[0.98rem] font-black tracking-[-0.02em]"
                  :class="
                    isSelectedSection(section.key) ? 'text-white' : 'text-slate-800'
                  "
                >
                  {{ sectionLabels[section.key] || section.key }}
                </p>
                <p
                  class="mt-1 text-xs font-semibold"
                  :class="
                    isSelectedSection(section.key) ? 'text-white/70' : 'text-slate-500'
                  "
                >
                  Section profil website
                </p>
              </div>
            </div>
            <span
              class="inline-flex shrink-0 rounded-full px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
              :class="sectionBadgeClass(section)"
            >
              {{ section.is_active ? "Aktif" : "Nonaktif" }}
            </span>
          </button>

          <div
            v-if="!sections.length"
            class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm font-bold text-slate-500"
          >
            Belum ada section profil.
          </div>
        </div>
      </div>

      <form
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_52px_rgba(15,23,42,0.06)]"
        @submit.prevent="updateSection"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />
        <div class="p-5 sm:p-6 lg:p-7">
          <div
            class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
          >
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Edit Section
              </div>
              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Edit
                {{ sectionLabels[activeSection?.key] || activeSection?.key || "Section" }}
              </h2>
              <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                Perubahan konten section akan tampil pada halaman Profil user setelah
                disimpan.
              </p>
            </div>
            <label
              class="inline-flex w-fit items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-700"
            >
              <input
                v-model="sectionForm.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
              />
              Aktif
            </label>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Badge</label
              >
              <input
                v-model="sectionForm.badge"
                type="text"
                placeholder="Contoh: Tentang HMPS"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.badge"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.badge }}
              </p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Urutan Tampil</label
              >
              <input
                v-model="sectionForm.sort_order"
                type="number"
                min="0"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                Angka kecil akan tampil lebih awal.
              </p>
              <p
                v-if="sectionForm.errors.sort_order"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.sort_order }}
              </p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Judul</label
              >
              <input
                v-model="sectionForm.title"
                type="text"
                placeholder="Judul section"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.title"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.title }}
              </p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Judul Highlight</label
              >
              <input
                v-model="sectionForm.title_highlight"
                type="text"
                placeholder="Teks yang diberi aksen warna"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.title_highlight"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.title_highlight }}
              </p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Label Tombol Utama</label
              >
              <input
                v-model="sectionForm.primary_button_label"
                type="text"
                placeholder="Contoh: Lihat Sejarah"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.primary_button_label"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.primary_button_label }}
              </p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >URL Tombol Utama</label
              >
              <input
                v-model="sectionForm.primary_button_url"
                type="text"
                placeholder="Contoh: #sejarah"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.primary_button_url"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.primary_button_url }}
              </p>
            </div>
            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Deskripsi</label
              >
              <textarea
                v-model="sectionForm.description"
                rows="5"
                placeholder="Tuliskan deskripsi section profil..."
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.description"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.description }}
              </p>
            </div>
          </div>

          <div
            v-if="activeSection?.key === 'hero'"
            class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5"
          >
            <div class="mb-4">
              <h3 class="text-sm font-black uppercase tracking-[0.12em] text-slate-500">
                Floating Badge Hero
              </h3>
              <p class="mt-2 text-sm leading-7 text-slate-500">
                Konten kecil yang muncul sebagai badge tambahan pada hero profil.
              </p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <input
                v-model="heroMeta.floating_top_title"
                type="text"
                placeholder="Badge atas - judul"
                class="h-[3.1rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroMeta.floating_top_subtitle"
                type="text"
                placeholder="Badge atas - subtitle"
                class="h-[3.1rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroMeta.floating_bottom_title"
                type="text"
                placeholder="Badge bawah - judul"
                class="h-[3.1rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroMeta.floating_bottom_subtitle"
                type="text"
                placeholder="Badge bawah - subtitle"
                class="h-[3.1rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
            </div>
          </div>

          <div
            v-if="activeSection?.key === 'about'"
            class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5"
          >
            <div class="mb-4">
              <h3 class="text-sm font-black uppercase tracking-[0.12em] text-slate-500">
                Paragraf Tambahan Tentang
              </h3>
              <p class="mt-2 text-sm leading-7 text-slate-500">
                Tambahkan narasi pendukung agar section Tentang HMPS lebih lengkap.
              </p>
            </div>
            <div class="grid gap-4">
              <textarea
                v-model="aboutMeta.paragraph_2"
                rows="3"
                placeholder="Paragraf kedua"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <textarea
                v-model="aboutMeta.paragraph_3"
                rows="3"
                placeholder="Paragraf ketiga"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <textarea
                v-model="aboutMeta.function_title"
                rows="2"
                placeholder="Judul box fungsi utama"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
            </div>
          </div>

          <div
            v-if="activeSection?.key === 'vision_mission'"
            class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5"
          >
            <div class="mb-4">
              <h3 class="text-sm font-black uppercase tracking-[0.12em] text-slate-500">
                Visi Organisasi
              </h3>
              <p class="mt-2 text-sm leading-7 text-slate-500">
                Konten visi utama yang tampil pada section Visi Misi.
              </p>
            </div>
            <textarea
              v-model="visionMeta.vision"
              rows="4"
              placeholder="Tulis visi organisasi"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="mt-7 flex justify-end border-t border-slate-200 pt-6">
            <button
              type="submit"
              :disabled="sectionForm.processing || !activeSection"
              class="group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 sm:w-auto"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                sectionForm.processing ? "Menyimpan..." : "Simpan Section"
              }}</span>
            </button>
          </div>
        </div>
      </form>
    </section>

    <!-- Add Item -->
    <section
      id="profil-add-item"
      data-aos="fade-up"
      class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_52px_rgba(15,23,42,0.06)]"
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
              Konten Dinamis Profil
            </div>
            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Tambah Item Profil
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">
              Kelola ticker, sejarah, misi, identitas, dan fungsi organisasi tanpa
              menyentuh backend.
            </p>
          </div>
          <span
            class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
            >{{ items.length }} Total Item</span
          >
        </div>

        <form class="grid gap-5 lg:grid-cols-2" @submit.prevent="storeItem">
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Jenis Item</label
            >
            <select
              v-model="itemForm.group"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option
                v-for="group in groupOptions"
                :key="group.value"
                :value="group.value"
              >
                {{ group.label }}
              </option>
            </select>
            <p v-if="itemForm.errors.group" class="mt-2 text-xs font-bold text-red-600">
              {{ itemForm.errors.group }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Urutan Tampil</label
            >
            <input
              v-model="itemForm.sort_order"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
              Angka kecil akan tampil lebih awal.
            </p>
            <p
              v-if="itemForm.errors.sort_order"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ itemForm.errors.sort_order }}
            </p>
          </div>

          <div v-if="['ticker', 'history'].includes(itemForm.group)">
            <label class="mb-2 block text-sm font-extrabold text-slate-800">{{
              itemForm.group === "history" ? "Periode" : "Teks Ticker"
            }}</label>
            <input
              v-model="itemForm.label"
              type="text"
              :placeholder="
                itemForm.group === 'history' ? '2025 / 2026' : 'Profil HMPS RPL'
              "
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p v-if="itemForm.errors.label" class="mt-2 text-xs font-bold text-red-600">
              {{ itemForm.errors.label }}
            </p>
          </div>

          <div v-if="['history', 'identity'].includes(itemForm.group)">
            <label class="mb-2 block text-sm font-extrabold text-slate-800">Judul</label>
            <input
              v-model="itemForm.title"
              type="text"
              placeholder="Judul item"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p v-if="itemForm.errors.title" class="mt-2 text-xs font-bold text-red-600">
              {{ itemForm.errors.title }}
            </p>
          </div>

          <template v-if="itemForm.group === 'history'">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Ketua</label
              >
              <input
                v-model="itemForm.meta.ketua"
                type="text"
                placeholder="Nama ketua"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Wakil Ketua</label
              >
              <input
                v-model="itemForm.meta.wakil"
                type="text"
                placeholder="Nama wakil ketua"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>
          </template>

          <div
            v-if="['history', 'mission', 'identity', 'function'].includes(itemForm.group)"
            class="lg:col-span-2"
          >
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Deskripsi / Isi</label
            >
            <textarea
              v-model="itemForm.description"
              rows="4"
              placeholder="Tuliskan isi item profil"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p
              v-if="itemForm.errors.description"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ itemForm.errors.description }}
            </p>
          </div>

          <div
            class="flex flex-col gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between"
          >
            <label class="flex items-center gap-3 text-sm font-extrabold text-slate-700">
              <input
                v-model="itemForm.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
              />
              Aktif
            </label>
            <button
              type="submit"
              :disabled="itemForm.processing"
              class="group relative inline-flex min-h-[50px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-slate-950 px-6 text-sm font-black text-white transition hover:-translate-y-[2px] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                itemForm.processing ? "Menyimpan..." : "Tambah Item"
              }}</span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Item List -->
    <section id="profil-item-list" data-aos="fade-up" class="space-y-5">
      <div
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_52px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />
        <div class="p-5 sm:p-6 lg:p-7">
          <div
            class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
          >
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Daftar Konten Profil
              </div>
              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Kelola Item Dinamis
              </h2>
              <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">
                Cari dan filter konten profil berdasarkan jenis item atau status aktif
                agar pengelolaan lebih cepat.
              </p>
            </div>
            <span
              class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
              >{{ items.length }} Data</span
            >
          </div>

          <div class="grid gap-3 lg:grid-cols-[1fr_220px_180px_auto]">
            <input
              v-model="itemSearch"
              type="search"
              placeholder="Cari judul, periode, ketua, isi, atau label..."
              class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <select
              v-model="selectedItemGroup"
              class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="all">Semua Jenis</option>
              <option
                v-for="group in groupOptions"
                :key="group.value"
                :value="group.value"
              >
                {{ group.label }}
              </option>
            </select>
            <select
              v-model="selectedItemStatus"
              class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="all">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
            <button
              type="button"
              :disabled="!hasItemFilters"
              class="inline-flex min-h-[3.15rem] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
              @click="clearItemFilters"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <div class="grid gap-6 xl:grid-cols-2">
        <div
          v-for="group in filteredGroupedItems"
          :key="group.value"
          class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
        >
          <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />
          <div class="p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <span
                  class="inline-flex rounded-full border px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em]"
                  :class="groupBadgeClass(group.value)"
                  >{{ group.value }}</span
                >
                <h2 class="mt-3 text-lg font-black tracking-[-0.02em] text-slate-950">
                  {{ group.label }}
                </h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">
                  {{ group.items.length }} item ditampilkan
                </p>
              </div>
              <span
                class="rounded-full bg-slate-50 px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200"
                >{{ group.items.length }} Data</span
              >
            </div>

            <div class="mt-5 space-y-3">
              <div
                v-for="item in group.items"
                :key="item.id"
                class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
              >
                <div v-if="editingItem?.id !== item.id" class="p-4">
                  <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
                  >
                    <div class="min-w-0">
                      <div class="flex flex-wrap gap-2">
                        <span
                          class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                          >Urutan {{ item.sort_order }}</span
                        >
                        <span
                          class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                          :class="itemStatusClass(item.is_active)"
                          >{{ item.is_active ? "Aktif" : "Nonaktif" }}</span
                        >
                      </div>
                      <h3
                        class="mt-3 line-clamp-2 text-base font-black leading-snug text-slate-950"
                      >
                        {{ displayItemTitle(item) }}
                      </h3>
                      <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">
                        {{ displayItemSubtitle(item) }}
                      </p>
                    </div>
                    <div class="grid shrink-0 grid-cols-2 gap-2 sm:flex sm:flex-col">
                      <button
                        type="button"
                        class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                        @click="editItem(item)"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                        @click="deleteItem(item)"
                      >
                        Hapus
                      </button>
                    </div>
                  </div>
                </div>

                <form v-else class="grid gap-4 bg-white p-4" @submit.prevent="updateItem">
                  <div
                    class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div>
                      <span
                        class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                        >Mode Edit Item</span
                      >
                      <h3
                        class="mt-2 text-lg font-black tracking-[-0.02em] text-slate-950"
                      >
                        Edit {{ groupLabels[itemEditForm.group] || itemEditForm.group }}
                      </h3>
                    </div>
                    <button
                      type="button"
                      class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditItem"
                    >
                      Batal Edit
                    </button>
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="mb-2 block text-sm font-extrabold text-slate-800"
                        >Jenis Item</label
                      >
                      <select
                        v-model="itemEditForm.group"
                        class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      >
                        <option
                          v-for="option in groupOptions"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="mb-2 block text-sm font-extrabold text-slate-800"
                        >Urutan</label
                      >
                      <input
                        v-model="itemEditForm.sort_order"
                        type="number"
                        min="0"
                        class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      />
                    </div>
                    <div>
                      <label class="mb-2 block text-sm font-extrabold text-slate-800"
                        >Label / Periode / Ticker</label
                      >
                      <input
                        v-model="itemEditForm.label"
                        type="text"
                        placeholder="Label / Periode / Ticker"
                        class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      />
                    </div>
                    <div>
                      <label class="mb-2 block text-sm font-extrabold text-slate-800"
                        >Judul</label
                      >
                      <input
                        v-model="itemEditForm.title"
                        type="text"
                        placeholder="Judul"
                        class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      />
                    </div>
                    <template v-if="itemEditForm.group === 'history'">
                      <div>
                        <label class="mb-2 block text-sm font-extrabold text-slate-800"
                          >Ketua</label
                        >
                        <input
                          v-model="itemEditForm.meta.ketua"
                          type="text"
                          placeholder="Ketua"
                          class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                        />
                      </div>
                      <div>
                        <label class="mb-2 block text-sm font-extrabold text-slate-800"
                          >Wakil Ketua</label
                        >
                        <input
                          v-model="itemEditForm.meta.wakil"
                          type="text"
                          placeholder="Wakil Ketua"
                          class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                        />
                      </div>
                    </template>
                  </div>

                  <div>
                    <label class="mb-2 block text-sm font-extrabold text-slate-800"
                      >Deskripsi / Isi</label
                    >
                    <textarea
                      v-model="itemEditForm.description"
                      rows="3"
                      placeholder="Deskripsi / isi"
                      class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />
                  </div>

                  <div
                    class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <label
                      class="flex items-center gap-3 text-sm font-extrabold text-slate-700"
                    >
                      <input
                        v-model="itemEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                    <div class="flex flex-col-reverse gap-2 sm:flex-row">
                      <button
                        type="button"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="cancelEditItem"
                      >
                        Batal
                      </button>
                      <button
                        type="submit"
                        :disabled="itemEditForm.processing"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                      >
                        {{ itemEditForm.processing ? "Menyimpan..." : "Simpan Item" }}
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <div
                v-if="!group.items.length"
                class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-7 text-center text-sm font-semibold text-slate-500"
              >
                Tidak ada item yang sesuai pada grup ini.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
html {
  scroll-behavior: smooth;
}

.profile-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(148, 163, 184, 0.55) rgba(226, 232, 240, 0.7);
}

.profile-scroll::-webkit-scrollbar {
  height: 7px;
  width: 7px;
}

.profile-scroll::-webkit-scrollbar-track {
  background: rgba(226, 232, 240, 0.7);
  border-radius: 999px;
}

.profile-scroll::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.55);
  border-radius: 999px;
}

.profile-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(100, 116, 139, 0.72);
}
</style>


<style scoped>
@media (max-width: 639px) {
  .custom-profil-mobile-page,
  .custom-profil-mobile-page * {
    box-sizing: border-box;
  }

  .custom-profil-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-profil-mobile-page :is(section, article, form, div) {
    min-width: 0;
  }

  .custom-profil-mobile-page :is(input, select, textarea, button, a, img) {
    max-width: 100%;
  }

  .custom-profil-mobile-page p {
    text-align: left !important;
  }

  .custom-profil-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-profil-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-profil-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-profil-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-profil-mobile-page > section:first-of-type .relative.z-10.grid {
    gap: 1rem !important;
  }

  .custom-profil-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-profil-mobile-page > section:first-of-type .mt-7.flex {
    margin-top: 1.15rem !important;
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
  }

  .custom-profil-mobile-page > section:first-of-type .mt-7.flex a {
    width: 100% !important;
    min-height: 46px !important;
    justify-content: center !important;
  }

  .custom-profil-mobile-page > section:first-of-type .grid.gap-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 0.55rem !important;
  }

  .custom-profil-mobile-page > section:first-of-type .grid.gap-3 > div {
    padding: 0.65rem !important;
    border-radius: 1rem !important;
  }

  .custom-profil-mobile-page > section:first-of-type .grid.gap-3 p:first-child {
    font-size: 0.56rem !important;
    line-height: 1.25 !important;
    letter-spacing: 0.08em !important;
  }

  .custom-profil-mobile-page > section:first-of-type .grid.gap-3 p:last-child {
    font-size: 1.05rem !important;
    line-height: 1.05 !important;
    margin-top: 0.35rem !important;
  }

  .custom-profil-mobile-page > section:nth-of-type(2) > div {
    border-radius: 1.1rem !important;
    padding: 0.85rem !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.055) !important;
  }

  .custom-profil-mobile-page > section:nth-of-type(2) h3 {
    font-size: 1.28rem !important;
    line-height: 1.05 !important;
  }

  .custom-profil-mobile-page > section:nth-of-type(2) .relative.z-10.mb-4.flex.h-12.w-12 {
    width: 2.55rem !important;
    height: 2.55rem !important;
    border-radius: 0.9rem !important;
    margin-bottom: 0.75rem !important;
  }

  .custom-profil-mobile-page .profil-quick-nav {
    display: flex !important;
    gap: 0.65rem !important;
    overflow-x: auto !important;
    padding-bottom: 0.35rem !important;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
  }

  .custom-profil-mobile-page .profil-quick-nav > a {
    min-width: 9.8rem !important;
    width: 9.8rem !important;
    padding: 0.85rem 1rem !important;
    border-radius: 1rem !important;
    scroll-snap-align: start;
    text-align: center;
  }

  .custom-profil-mobile-page > section:nth-of-type(n+4) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-profil-mobile-page > section > div:not(.h-1\.5),
  .custom-profil-mobile-page > section > form {
    padding: 1rem !important;
  }

  .custom-profil-mobile-page .rounded-\[1\.35rem\],
  .custom-profil-mobile-page .rounded-\[1\.4rem\],
  .custom-profil-mobile-page .rounded-\[1\.45rem\],
  .custom-profil-mobile-page .rounded-\[1\.5rem\],
  .custom-profil-mobile-page .rounded-\[1\.6rem\],
  .custom-profil-mobile-page .rounded-\[1\.8rem\],
  .custom-profil-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-profil-mobile-page label,
  .custom-profil-mobile-page .text-sm.font-bold,
  .custom-profil-mobile-page .text-sm.font-black,
  .custom-profil-mobile-page .text-sm.font-extrabold {
    font-size: 0.82rem !important;
  }

  .custom-profil-mobile-page input:not([type="checkbox"]):not([type="file"]),
  .custom-profil-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-profil-mobile-page input[type="file"] {
    width: 100% !important;
    font-size: 0.78rem !important;
  }

  .custom-profil-mobile-page textarea {
    min-height: 112px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-profil-mobile-page button,
  .custom-profil-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-profil-mobile-page button[type="submit"],
  .custom-profil-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-profil-mobile-page .grid.lg\:grid-cols-2,
  .custom-profil-mobile-page .grid.md\:grid-cols-2,
  .custom-profil-mobile-page .grid.sm\:grid-cols-2,
  .custom-profil-mobile-page .grid.sm\:grid-cols-3,
  .custom-profil-mobile-page .grid.md\:grid-cols-4,
  .custom-profil-mobile-page .grid.xl\:grid-cols-4,
  .custom-profil-mobile-page .grid.xl\:grid-cols-3,
  .custom-profil-mobile-page .grid.xl\:grid-cols-\[0\.34fr_0\.66fr\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-profil-mobile-page .lg\:col-span-2,
  .custom-profil-mobile-page .md\:col-span-2 {
    grid-column: auto !important;
  }

  .custom-profil-mobile-page .grid.grid-cols-2.gap-2,
  .custom-profil-mobile-page .grid.grid-cols-2.gap-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-profil-mobile-page .group.relative.flex.min-h-\[13rem\] {
    min-height: 10.5rem !important;
    padding: 1.25rem !important;
    border-radius: 1.05rem !important;
  }

  .custom-profil-mobile-page .h-52,
  .custom-profil-mobile-page .h-48,
  .custom-profil-mobile-page .h-40 {
    height: 11.5rem !important;
  }

  .custom-profil-mobile-page .h-14.w-14,
  .custom-profil-mobile-page .h-12.w-12 {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-profil-mobile-page .flex.items-end,
  .custom-profil-mobile-page .flex.justify-end,
  .custom-profil-mobile-page .flex.flex-col-reverse,
  .custom-profil-mobile-page .flex.flex-col.gap-3.rounded-\[1\.35rem\] {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
    align-items: stretch !important;
  }

  .custom-profil-mobile-page .flex.flex-col.gap-3.rounded-\[1\.35rem\] label,
  .custom-profil-mobile-page label.inline-flex.w-fit,
  .custom-profil-mobile-page .inline-flex.w-fit {
    width: 100% !important;
    justify-content: flex-start !important;
  }

  .custom-profil-mobile-page .space-y-3 > button {
    padding: 0.85rem !important;
    border-radius: 1rem !important;
  }

  .custom-profil-mobile-page .space-y-3 > button .truncate {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }

  .custom-profil-mobile-page .line-clamp-2 {
    display: block !important;
    -webkit-line-clamp: unset !important;
    overflow: visible !important;
  }

  .custom-profil-mobile-page .truncate {
    min-width: 0;
  }

  .custom-profil-mobile-page .break-all,
  .custom-profil-mobile-page .break-words,
  .custom-profil-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-profil-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-profil-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-profil-mobile-page .flex.flex-wrap.items-center.gap-2 > span,
  .custom-profil-mobile-page .mt-3.flex.flex-wrap.gap-2 > span {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-profil-mobile-page .p-5,
  .custom-profil-mobile-page .p-4,
  .custom-profil-mobile-page .sm\:p-6,
  .custom-profil-mobile-page .lg\:p-7 {
    padding: 1rem !important;
  }

  .custom-profil-mobile-page .mt-8,
  .custom-profil-mobile-page .mt-7,
  .custom-profil-mobile-page .mt-6 {
    margin-top: 1.25rem !important;
  }

  .custom-profil-mobile-page .mb-7,
  .custom-profil-mobile-page .mb-6,
  .custom-profil-mobile-page .mb-5 {
    margin-bottom: 1.25rem !important;
  }

  .custom-profil-mobile-page .gap-5,
  .custom-profil-mobile-page .gap-6 {
    gap: 1rem !important;
  }

  .custom-profil-mobile-page .gap-8 {
    gap: 1.25rem !important;
  }

  .custom-profil-mobile-page .shadow-\[0_18px_52px_rgba\(15\,23\,42\,0\.06\)\],
  .custom-profil-mobile-page .shadow-\[0_18px_52px_rgba\(15\,23\,42\,0\.07\)\],
  .custom-profil-mobile-page .shadow-\[0_16px_42px_rgba\(15\,23\,42\,0\.06\)\] {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055) !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-profil-mobile-page *,
  .custom-profil-mobile-page *::before,
  .custom-profil-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

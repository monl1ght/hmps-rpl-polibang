<script setup>
import { computed, reactive, ref, watch } from "vue";
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
  tickers: {
    type: Array,
    default: () => [],
  },
  highlights: {
    type: Array,
    default: () => [],
  },
  media: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const selectedSectionKey = ref("hero");
const editingTicker = ref(null);
const editingHighlight = ref(null);
const editingMedia = ref(null);

const sectionLabels = {
  hero: "Hero",
  about: "Tentang",
  proker: "Program Kerja",
  gallery: "Galeri",
};

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const sections = computed(() => (Array.isArray(props.sections) ? props.sections : []));
const tickers = computed(() => (Array.isArray(props.tickers) ? props.tickers : []));
const highlights = computed(() =>
  Array.isArray(props.highlights) ? props.highlights : []
);
const media = computed(() => (Array.isArray(props.media) ? props.media : []));

const activeSection = computed(() => {
  return sections.value.find((item) => item.key === selectedSectionKey.value) || null;
});

const heroMeta = reactive({
  top_badge_title: "",
  top_badge_subtitle: "",
  bottom_badge_title: "",
  bottom_badge_subtitle: "",
});

const aboutMeta = reactive({
  highlight_title: "",
});

const sectionForm = useForm({
  badge: "",
  title: "",
  title_highlight: "",
  description: "",
  primary_button_label: "",
  primary_button_url: "",
  secondary_button_label: "",
  secondary_button_url: "",
  meta: {},
  is_active: true,
  sort_order: 0,
});

const tickerForm = useForm({
  label: "",
  is_active: true,
  sort_order: 0,
});

const tickerEditForm = useForm({
  label: "",
  is_active: true,
  sort_order: 0,
});

const highlightForm = useForm({
  title: "",
  description: "",
  is_active: true,
  sort_order: 0,
});

const highlightEditForm = useForm({
  title: "",
  description: "",
  is_active: true,
  sort_order: 0,
});

const mediaForm = useForm({
  group: "hero",
  title: "",
  subtitle: "",
  category: "",
  image_file: null,
  layout: "normal",
  is_active: true,
  sort_order: 0,
});

const mediaEditForm = useForm({
  group: "hero",
  title: "",
  subtitle: "",
  category: "",
  image_file: null,
  layout: "normal",
  is_active: true,
  sort_order: 0,
});

const stats = computed(() => [
  {
    label: "Section Home",
    value: sections.value.length,
    helper: `${sections.value.filter((item) => item.is_active).length} section aktif`,
    icon: "section",
  },
  {
    label: "Ticker",
    value: tickers.value.length,
    helper: `${tickers.value.filter((item) => item.is_active).length} ticker aktif`,
    icon: "ticker",
  },
  {
    label: "Highlight",
    value: highlights.value.length,
    helper: `${highlights.value.filter((item) => item.is_active).length} highlight aktif`,
    icon: "highlight",
  },
  {
    label: "Media",
    value: media.value.length,
    helper: `${media.value.filter((item) => item.is_active).length} media aktif`,
    icon: "media",
  },
]);

const heroMedia = computed(() => media.value.filter((item) => item.group === "hero"));
const galleryMedia = computed(() =>
  media.value.filter((item) => item.group === "gallery")
);

const resetMeta = () => {
  heroMeta.top_badge_title = "";
  heroMeta.top_badge_subtitle = "";
  heroMeta.bottom_badge_title = "";
  heroMeta.bottom_badge_subtitle = "";

  aboutMeta.highlight_title = "";
};

const fillSectionForm = (section) => {
  if (!section) return;

  sectionForm.badge = section.badge || "";
  sectionForm.title = section.title || "";
  sectionForm.title_highlight = section.title_highlight || "";
  sectionForm.description = section.description || "";
  sectionForm.primary_button_label = section.primary_button_label || "";
  sectionForm.primary_button_url = section.primary_button_url || "";
  sectionForm.secondary_button_label = section.secondary_button_label || "";
  sectionForm.secondary_button_url = section.secondary_button_url || "";
  sectionForm.is_active = Boolean(section.is_active);
  sectionForm.sort_order = Number(section.sort_order || 0);

  resetMeta();

  if (section.key === "hero") {
    heroMeta.top_badge_title = section.meta?.top_badge_title || "";
    heroMeta.top_badge_subtitle = section.meta?.top_badge_subtitle || "";
    heroMeta.bottom_badge_title = section.meta?.bottom_badge_title || "";
    heroMeta.bottom_badge_subtitle = section.meta?.bottom_badge_subtitle || "";
  }

  if (section.key === "about") {
    aboutMeta.highlight_title = section.meta?.highlight_title || "";
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
      ? "bg-white/10 text-white ring-1 ring-white/10"
      : "bg-white/10 text-white/70 ring-1 ring-white/10";
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

const statusClass = (status) => {
  return status
    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
    : "border-slate-200 bg-slate-100 text-slate-600";
};

const groupClass = (group) => {
  if (group === "hero") return "border-red-200 bg-red-50 text-red-700";
  if (group === "gallery") return "border-blue-200 bg-blue-50 text-blue-700";

  return "border-slate-200 bg-slate-100 text-slate-700";
};

const resetTickerForm = () => {
  tickerForm.reset();
  tickerForm.is_active = true;
  tickerForm.sort_order = 0;
};

const resetHighlightForm = () => {
  highlightForm.reset();
  highlightForm.is_active = true;
  highlightForm.sort_order = 0;
};

const resetMediaForm = () => {
  mediaForm.reset();
  mediaForm.group = "hero";
  mediaForm.layout = "normal";
  mediaForm.is_active = true;
  mediaForm.sort_order = 0;
  mediaForm.image_file = null;
};

const resetMediaEditForm = () => {
  mediaEditForm.reset();
  mediaEditForm.group = "hero";
  mediaEditForm.layout = "normal";
  mediaEditForm.is_active = true;
  mediaEditForm.sort_order = 0;
  mediaEditForm.image_file = null;
};

const handleMediaFile = (event) => {
  mediaForm.image_file = event.target.files[0] || null;
};

const handleEditMediaFile = (event) => {
  mediaEditForm.image_file = event.target.files[0] || null;
};

const updateSection = () => {
  if (!activeSection.value) return;

  if (activeSection.value.key === "hero") {
    sectionForm.meta = { ...heroMeta };
  } else if (activeSection.value.key === "about") {
    sectionForm.meta = { ...aboutMeta };
  } else {
    sectionForm.meta = {};
  }

  sectionForm.put(`/admin/home/sections/${activeSection.value.id}`, {
    preserveScroll: true,
  });
};

const storeTicker = () => {
  tickerForm.post("/admin/home/tickers", {
    preserveScroll: true,
    onSuccess: () => resetTickerForm(),
  });
};

const editTicker = (item) => {
  editingTicker.value = item;
  tickerEditForm.label = item.label || "";
  tickerEditForm.is_active = Boolean(item.is_active);
  tickerEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditTicker = () => {
  editingTicker.value = null;
  tickerEditForm.reset();
};

const updateTicker = () => {
  if (!editingTicker.value) return;

  tickerEditForm.put(`/admin/home/tickers/${editingTicker.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditTicker(),
  });
};

const deleteTicker = (item) => {
  if (!confirm(`Hapus ticker "${item.label}"?`)) return;

  router.delete(`/admin/home/tickers/${item.id}`, {
    preserveScroll: true,
  });
};

const storeHighlight = () => {
  highlightForm.post("/admin/home/highlights", {
    preserveScroll: true,
    onSuccess: () => resetHighlightForm(),
  });
};

const editHighlight = (item) => {
  editingHighlight.value = item;
  highlightEditForm.title = item.title || "";
  highlightEditForm.description = item.description || "";
  highlightEditForm.is_active = Boolean(item.is_active);
  highlightEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditHighlight = () => {
  editingHighlight.value = null;
  highlightEditForm.reset();
};

const updateHighlight = () => {
  if (!editingHighlight.value) return;

  highlightEditForm.put(`/admin/home/highlights/${editingHighlight.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditHighlight(),
  });
};

const deleteHighlight = (item) => {
  if (!confirm(`Hapus highlight "${item.title}"?`)) return;

  router.delete(`/admin/home/highlights/${item.id}`, {
    preserveScroll: true,
  });
};

const storeMedia = () => {
  mediaForm.post("/admin/home/media", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => resetMediaForm(),
  });
};

const editMedia = (item) => {
  editingMedia.value = item;

  mediaEditForm.group = item.group || "hero";
  mediaEditForm.title = item.title || "";
  mediaEditForm.subtitle = item.subtitle || "";
  mediaEditForm.category = item.category || "";
  mediaEditForm.image_file = null;
  mediaEditForm.layout = item.layout || "normal";
  mediaEditForm.is_active = Boolean(item.is_active);
  mediaEditForm.sort_order = Number(item.sort_order || 0);
};

const updateMedia = () => {
  if (!editingMedia.value) return;

  mediaEditForm
    .transform((data) => ({
      ...data,
      _method: "PUT",
    }))
    .post(`/admin/home/media/${editingMedia.value.id}`, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        editingMedia.value = null;
        resetMediaEditForm();
      },
    });
};

const cancelEditMedia = () => {
  editingMedia.value = null;
  resetMediaEditForm();
};

const deleteMedia = (item) => {
  if (!confirm(`Hapus media "${item.title || item.image}"?`)) return;

  router.delete(`/admin/home/media/${item.id}`, {
    preserveScroll: true,
  });
};
</script>
<template>
  <Head title="Custom Home" />

  <div
    class="custom-home-mobile-page w-full max-w-full min-w-0 overflow-x-hidden space-y-8 pb-10"
  >
    <!-- Premium Header -->
    <section
      class="custom-home-hero relative isolate overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-950 p-5 text-white shadow-[0_28px_80px_rgba(2,6,23,0.28)] sm:p-7 lg:p-10"
      data-aos="fade-up"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(135deg,rgba(15,23,42,0.98),rgba(15,23,42,0.92)_48%,rgba(127,29,29,0.95))]"
      />
      <div
        class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-red-500/25 blur-3xl"
      />
      <div
        class="pointer-events-none absolute -bottom-28 left-8 h-64 w-64 rounded-full bg-white/10 blur-3xl"
      />
      <div
        class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/40 to-transparent"
      />

      <div class="relative z-10 grid gap-8 xl:grid-cols-[1fr_auto] xl:items-end">
        <div class="max-w-4xl">
          <div class="mb-5 flex flex-wrap items-center gap-3">
            <span
              class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3.5 py-2 text-[0.7rem] font-black uppercase tracking-[0.16em] text-red-100 backdrop-blur"
            >
              <span
                class="h-2 w-2 rounded-full bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.16)]"
              />
              Admin Panel
            </span>
            <span
              class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.14em] text-slate-300"
            >
              Custom Home
            </span>
          </div>

          <h1
            class="text-[2rem] font-black leading-[1.05] tracking-[-0.055em] text-white sm:text-[2.75rem] lg:text-[3.45rem]"
          >
            Atur Beranda
            <span
              class="block bg-gradient-to-r from-red-200 via-red-300 to-white bg-clip-text text-transparent"
            >
              lebih profesional.
            </span>
          </h1>

          <p
            class="mt-5 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base sm:leading-8"
          >
            Kelola hero, tentang, program kerja, ticker, highlight, dan media visual dari
            satu halaman yang lebih rapi, modern, responsif, serta nyaman digunakan di
            mobile maupun laptop.
          </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:w-[25rem]">
          <div
            class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur"
          >
            <p
              class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-slate-300"
            >
              Konten aktif
            </p>
            <p class="mt-2 text-2xl font-black tracking-[-0.04em] text-white">
              {{ sections.filter((item) => item.is_active).length }}/{{ sections.length }}
            </p>
            <p class="mt-1 text-xs font-semibold text-slate-400">section tampil</p>
          </div>

          <a
            href="/"
            target="_blank"
            class="group relative inline-flex min-h-[6.4rem] items-center justify-center overflow-hidden rounded-[1.4rem] bg-white px-5 text-center text-sm font-black text-slate-950 shadow-[0_16px_42px_rgba(255,255,255,0.12)] transition duration-300 hover:-translate-y-1 hover:bg-red-50 hover:text-red-700"
          >
            <span
              class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-red-100 to-transparent opacity-80 transition-transform duration-700 group-hover:translate-x-full"
            />
            <span class="relative inline-flex items-center gap-2">
              Preview Website
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.4"
                  d="M7 17 17 7m0 0H8m9 0v9"
                />
              </svg>
            </span>
          </a>
        </div>
      </div>
    </section>

    <!-- Flash Message -->
    <div class="grid gap-3" aria-live="polite">
      <div
        v-if="flashSuccess"
        class="flex items-start gap-3 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_14px_34px_rgba(16,185,129,0.08)]"
      >
        <span
          class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"
          >✓</span
        >
        <span>{{ flashSuccess }}</span>
      </div>

      <div
        v-if="flashError"
        class="flex items-start gap-3 rounded-[1.4rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_14px_34px_rgba(239,68,68,0.08)]"
      >
        <span
          class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700"
          >!</span
        >
        <span>{{ flashError }}</span>
      </div>
    </div>

    <!-- Stats -->
    <section
      class="custom-home-stats grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
      data-aos="fade-up"
      data-aos-delay="80"
    >
      <article
        v-for="item in stats"
        :key="item.label"
        class="group relative overflow-hidden rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-[0_16px_44px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_24px_58px_rgba(15,23,42,0.11)]"
      >
        <div
          class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-red-50 opacity-70 transition group-hover:bg-red-100"
        />
        <div class="relative flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">
              {{ item.label }}
            </p>
            <h3 class="mt-3 text-3xl font-black tracking-[-0.045em] text-slate-950">
              {{ item.value }}
            </h3>
            <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
              {{ item.helper }}
            </p>
          </div>

          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100 transition duration-300 group-hover:scale-105"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                v-if="item.icon === 'section'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Zm0 5h16M9 21V10"
              />
              <path
                v-else-if="item.icon === 'ticker'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M4 7h16M4 12h10M4 17h16"
              />
              <path
                v-else-if="item.icon === 'highlight'"
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
                d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
              />
            </svg>
          </div>
        </div>
      </article>
    </section>

    <!-- Section Editor -->
    <section
      class="custom-home-editor-grid grid gap-6 xl:grid-cols-[minmax(0,0.36fr)_minmax(0,0.64fr)]"
      data-aos="fade-up"
      data-aos-delay="120"
    >
      <aside
        class="custom-home-section-sidebar h-fit rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-6 xl:sticky xl:top-6"
      >
        <div class="mb-6">
          <span
            class="inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Navigasi Section
          </span>
          <h2 class="mt-4 text-xl font-black tracking-[-0.035em] text-slate-950">
            Pilih area konten
          </h2>
          <p class="mt-2 text-sm leading-7 text-slate-500">
            Gunakan panel ini untuk berpindah antar section tanpa membuat halaman terasa
            penuh.
          </p>
        </div>

        <div class="custom-home-section-nav space-y-3">
          <button
            v-for="section in sections"
            :key="section.id"
            type="button"
            class="custom-home-section-tab group flex w-full items-center justify-between gap-4 rounded-[1.35rem] border px-4 py-4 text-left transition duration-300 focus:outline-none focus:ring-4 focus:ring-red-100"
            :class="sectionButtonClass(section)"
            @click="selectedSectionKey = section.key"
          >
            <div class="flex min-w-0 items-center gap-3">
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black ring-1 transition"
                :class="
                  isSelectedSection(section.key)
                    ? 'bg-white/10 text-white ring-white/10'
                    : 'bg-white text-slate-700 ring-slate-200'
                "
              >
                {{ section.sort_order ?? 0 }}
              </span>
              <div class="min-w-0">
                <p
                  class="truncate text-[0.98rem] font-black tracking-[-0.02em]"
                  :class="
                    isSelectedSection(section.key) ? 'text-white' : 'text-slate-900'
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
                  {{ section.key }} section
                </p>
              </div>
            </div>

            <span
              class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
              :class="sectionBadgeClass(section)"
            >
              <span class="h-1.5 w-1.5 rounded-full" :class="sectionDotClass(section)" />
              {{ section.is_active ? "Aktif" : "Nonaktif" }}
            </span>
          </button>

          <div
            v-if="!sections.length"
            class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-7 text-center text-sm font-bold text-slate-500"
          >
            Belum ada section home.
          </div>
        </div>
      </aside>

      <form
        class="custom-home-section-form overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]"
        @submit.prevent="updateSection"
      >
        <div
          class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/80 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between"
        >
          <div>
            <span
              class="inline-flex items-center gap-2 rounded-full border border-red-100 bg-white px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Editor Section
            </span>
            <h2 class="mt-3 text-2xl font-black tracking-[-0.045em] text-slate-950">
              {{ sectionLabels[activeSection?.key] || activeSection?.key || "Section" }}
            </h2>
            <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
              Perbarui copywriting, tombol, urutan, dan status tampil section beranda.
            </p>
          </div>

          <label
            class="inline-flex w-fit items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm"
          >
            <input
              v-model="sectionForm.is_active"
              type="checkbox"
              class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
            />
            Section Aktif
          </label>
        </div>

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-black text-slate-800">Badge</label>
              <input
                v-model="sectionForm.badge"
                type="text"
                placeholder="Contoh: HMPS RPL"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="sectionForm.errors.badge"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ sectionForm.errors.badge }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800">Urutan</label>
              <input
                v-model="sectionForm.sort_order"
                type="number"
                min="0"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800">Judul</label>
              <input
                v-model="sectionForm.title"
                type="text"
                placeholder="Judul utama section"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800"
                >Judul Highlight</label
              >
              <input
                v-model="sectionForm.title_highlight"
                type="text"
                placeholder="Kata yang ingin ditekankan"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-black text-slate-800"
                >Deskripsi</label
              >
              <textarea
                v-model="sectionForm.description"
                rows="5"
                placeholder="Deskripsi section yang jelas dan mudah dipahami pengguna"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800"
                >Label Tombol Utama</label
              >
              <input
                v-model="sectionForm.primary_button_label"
                type="text"
                placeholder="Contoh: Lihat Program"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800"
                >URL Tombol Utama</label
              >
              <input
                v-model="sectionForm.primary_button_url"
                type="text"
                placeholder="/program-kerja atau #proker"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800"
                >Label Tombol Kedua</label
              >
              <input
                v-model="sectionForm.secondary_button_label"
                type="text"
                placeholder="Contoh: Hubungi Kami"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-black text-slate-800"
                >URL Tombol Kedua</label
              >
              <input
                v-model="sectionForm.secondary_button_url"
                type="text"
                placeholder="/kontak"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>
          </div>

          <div
            v-if="activeSection?.key === 'hero'"
            class="mt-6 rounded-[1.5rem] border border-red-100 bg-gradient-to-br from-red-50 to-white p-5"
          >
            <div
              class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <h3 class="text-sm font-black uppercase tracking-[0.14em] text-red-700">
                  Floating Badge Hero
                </h3>
                <p class="mt-2 text-sm leading-7 text-slate-500">
                  Badge kecil untuk memperkuat visual hero di halaman depan.
                </p>
              </div>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
              <input
                v-model="heroMeta.top_badge_title"
                type="text"
                placeholder="Badge atas - judul"
                class="h-12 rounded-2xl border border-red-100 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroMeta.top_badge_subtitle"
                type="text"
                placeholder="Badge atas - subtitle"
                class="h-12 rounded-2xl border border-red-100 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroMeta.bottom_badge_title"
                type="text"
                placeholder="Badge bawah - judul"
                class="h-12 rounded-2xl border border-red-100 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <input
                v-model="heroMeta.bottom_badge_subtitle"
                type="text"
                placeholder="Badge bawah - subtitle"
                class="h-12 rounded-2xl border border-red-100 bg-white px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
            </div>
          </div>

          <div
            v-if="activeSection?.key === 'about'"
            class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5"
          >
            <h3 class="text-sm font-black uppercase tracking-[0.14em] text-slate-500">
              Highlight Tentang
            </h3>
            <textarea
              v-model="aboutMeta.highlight_title"
              rows="3"
              placeholder="Teks highlight di section Tentang"
              class="mt-4 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div
            class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between"
          >
            <p class="text-xs font-semibold leading-5 text-slate-500">
              Perubahan akan disimpan ke data Home yang sudah terhubung dengan backend.
            </p>
            <button
              type="submit"
              :disabled="sectionForm.processing || !activeSection"
              class="group relative inline-flex min-h-[52px] items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 via-red-600 to-red-800 px-6 text-sm font-black text-white shadow-[0_16px_34px_rgba(220,38,38,0.24)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_22px_44px_rgba(220,38,38,0.30)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"
              />
              <span class="relative">{{
                sectionForm.processing ? "Menyimpan..." : "Simpan Section"
              }}</span>
            </button>
          </div>
        </div>
      </form>
    </section>

    <!-- Ticker & Highlight -->
    <section
      class="custom-home-two-column grid gap-6 xl:grid-cols-2"
      data-aos="fade-up"
      data-aos-delay="160"
    >
      <!-- Ticker -->
      <article
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]"
      >
        <div class="border-b border-slate-200 bg-slate-50/80 p-5 sm:p-6">
          <span
            class="inline-flex items-center gap-2 rounded-full border border-red-100 bg-white px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Ticker Beranda
          </span>
          <h2 class="mt-3 text-2xl font-black tracking-[-0.045em] text-slate-950">
            Running Text
          </h2>
          <p class="mt-2 text-sm leading-7 text-slate-500">
            Tambahkan teks singkat yang tampil berjalan di bawah hero.
          </p>
        </div>

        <div class="p-5 sm:p-6">
          <form
            class="grid gap-3 lg:grid-cols-[1fr_7rem_auto]"
            @submit.prevent="storeTicker"
          >
            <input
              v-model="tickerForm.label"
              type="text"
              placeholder="Contoh: HMPS RPL"
              class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <input
              v-model="tickerForm.sort_order"
              type="number"
              min="0"
              placeholder="Urutan"
              class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <button
              type="submit"
              :disabled="tickerForm.processing"
              class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ tickerForm.processing ? "Menyimpan..." : "Tambah" }}
            </button>
          </form>

          <label
            class="mt-3 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600"
          >
            <input
              v-model="tickerForm.is_active"
              type="checkbox"
              class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
            />
            Ticker baru aktif
          </label>

          <div class="mt-5 space-y-3">
            <div
              v-for="item in tickers"
              :key="item.id"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
            >
              <div
                v-if="editingTicker?.id !== item.id"
                class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between"
              >
                <div class="min-w-0">
                  <div class="mb-2 flex flex-wrap gap-2">
                    <span
                      class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                      >Urutan {{ item.sort_order }}</span
                    >
                    <span
                      class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="statusClass(item.is_active)"
                      >{{ item.is_active ? "Aktif" : "Nonaktif" }}</span
                    >
                  </div>
                  <p class="break-words font-black text-slate-950">{{ item.label }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0">
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                    @click="editTicker(item)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteTicker(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-4 bg-white p-4" @submit.prevent="updateTicker">
                <div
                  class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                      >Mode Edit</span
                    >
                    <h3 class="mt-2 text-lg font-black text-slate-950">Edit Ticker</h3>
                  </div>
                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditTicker"
                  >
                    Batal Edit
                  </button>
                </div>

                <div class="grid gap-3 md:grid-cols-[1fr_8rem]">
                  <input
                    v-model="tickerEditForm.label"
                    type="text"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                  <input
                    v-model="tickerEditForm.sort_order"
                    type="number"
                    min="0"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <label
                    class="flex h-12 w-fit items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                  >
                    <input
                      v-model="tickerEditForm.is_active"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                    />
                    Aktif
                  </label>
                  <div class="grid grid-cols-2 gap-2 sm:flex">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditTicker"
                    >
                      Batal
                    </button>
                    <button
                      type="submit"
                      :disabled="tickerEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                    >
                      {{ tickerEditForm.processing ? "Menyimpan..." : "Simpan" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!tickers.length"
              class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-7 text-center text-sm font-semibold text-slate-500"
            >
              Belum ada ticker beranda.
            </div>
          </div>
        </div>
      </article>

      <!-- Highlight -->
      <article
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]"
      >
        <div class="border-b border-slate-200 bg-slate-950 p-5 text-white sm:p-6">
          <span
            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-100"
          >
            <span class="h-2 w-2 rounded-full bg-red-400" />
            Highlight Beranda
          </span>
          <h2 class="mt-3 text-2xl font-black tracking-[-0.045em] text-white">
            Poin Penting
          </h2>
          <p class="mt-2 text-sm leading-7 text-slate-300">
            Kelola poin yang memperkuat section Tentang agar lebih mudah dipindai
            pengguna.
          </p>
        </div>

        <div class="p-5 sm:p-6">
          <form class="grid gap-4" @submit.prevent="storeHighlight">
            <input
              v-model="highlightForm.title"
              type="text"
              placeholder="Judul highlight"
              class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <textarea
              v-model="highlightForm.description"
              rows="3"
              placeholder="Deskripsi highlight"
              class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <div class="grid gap-3 md:grid-cols-[1fr_auto_auto] md:items-center">
              <input
                v-model="highlightForm.sort_order"
                type="number"
                min="0"
                placeholder="Urutan"
                class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <label
                class="inline-flex h-12 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
              >
                <input
                  v-model="highlightForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Aktif
              </label>
              <button
                type="submit"
                :disabled="highlightForm.processing"
                class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ highlightForm.processing ? "Menyimpan..." : "Tambah Highlight" }}
              </button>
            </div>
          </form>

          <div class="mt-5 space-y-3">
            <div
              v-for="item in highlights"
              :key="item.id"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
            >
              <div
                v-if="editingHighlight?.id !== item.id"
                class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-start"
              >
                <div class="min-w-0">
                  <div class="mb-2 flex flex-wrap gap-2">
                    <span
                      class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                      >Urutan {{ item.sort_order }}</span
                    >
                    <span
                      class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="statusClass(item.is_active)"
                      >{{ item.is_active ? "Aktif" : "Nonaktif" }}</span
                    >
                  </div>
                  <p class="font-black text-slate-950">{{ item.title }}</p>
                  <p class="mt-1 text-sm leading-7 text-slate-500">
                    {{ item.description }}
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0">
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                    @click="editHighlight(item)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteHighlight(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form
                v-else
                class="grid gap-4 bg-white p-4"
                @submit.prevent="updateHighlight"
              >
                <div
                  class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                      >Mode Edit</span
                    >
                    <h3 class="mt-2 text-lg font-black text-slate-950">Edit Highlight</h3>
                  </div>
                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditHighlight"
                  >
                    Batal Edit
                  </button>
                </div>

                <input
                  v-model="highlightEditForm.title"
                  type="text"
                  class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <textarea
                  v-model="highlightEditForm.description"
                  rows="3"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="flex flex-wrap items-center gap-3">
                    <input
                      v-model="highlightEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-12 w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />
                    <label
                      class="flex h-12 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                    >
                      <input
                        v-model="highlightEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                  </div>
                  <div class="grid grid-cols-2 gap-2 sm:flex">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditHighlight"
                    >
                      Batal
                    </button>
                    <button
                      type="submit"
                      :disabled="highlightEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                    >
                      {{ highlightEditForm.processing ? "Menyimpan..." : "Simpan" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!highlights.length"
              class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-7 text-center text-sm font-semibold text-slate-500"
            >
              Belum ada highlight beranda.
            </div>
          </div>
        </div>
      </article>
    </section>

    <!-- Media Home -->
    <section
      class="custom-home-media overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]"
      data-aos="fade-up"
      data-aos-delay="200"
    >
      <div
        class="border-b border-slate-200 bg-gradient-to-br from-slate-50 to-white p-5 sm:p-6 lg:p-7"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <span
              class="inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Media Home
            </span>
            <h2 class="mt-3 text-2xl font-black tracking-[-0.045em] text-slate-950">
              Manajemen Visual Beranda
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">
              Upload dan kelola gambar hero serta galeri agar tampilan Home terlihat lebih
              hidup, rapi, dan profesional.
            </p>
          </div>
          <div class="grid grid-cols-2 gap-3 sm:w-72">
            <div class="rounded-2xl border border-red-100 bg-red-50 p-4 text-center">
              <p class="text-2xl font-black text-red-700">{{ heroMedia.length }}</p>
              <p class="text-xs font-black uppercase tracking-[0.1em] text-red-600">
                Hero
              </p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-center">
              <p class="text-2xl font-black text-blue-700">{{ galleryMedia.length }}</p>
              <p class="text-xs font-black uppercase tracking-[0.1em] text-blue-600">
                Gallery
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="p-5 sm:p-6 lg:p-7">
        <form class="grid gap-5 lg:grid-cols-2" @submit.prevent="storeMedia">
          <div>
            <label class="mb-2 block text-sm font-black text-slate-800">Group</label>
            <select
              v-model="mediaForm.group"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="hero">Hero</option>
              <option value="gallery">Gallery</option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-black text-slate-800">Layout</label>
            <select
              v-model="mediaForm.layout"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="normal">Normal</option>
              <option value="large">Large</option>
              <option value="wide">Wide</option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-black text-slate-800"
              >Judul Media</label
            >
            <input
              v-model="mediaForm.title"
              type="text"
              placeholder="Judul media"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-black text-slate-800">Kategori</label>
            <input
              v-model="mediaForm.category"
              type="text"
              placeholder="Contoh: Kegiatan Besar"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-black text-slate-800"
              >Subtitle / Keterangan Singkat</label
            >
            <input
              v-model="mediaForm.subtitle"
              type="text"
              placeholder="Opsional, keterangan singkat media"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-black text-slate-800"
              >Upload Gambar</label
            >
            <label
              class="group relative flex min-h-[13rem] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.45rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/60"
            >
              <span
                class="absolute inset-0 bg-gradient-to-br from-white via-transparent to-red-50 opacity-80"
              />
              <span
                class="relative flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-red-600 shadow-sm ring-1 ring-slate-200 transition duration-300 group-hover:scale-105 group-hover:ring-red-200"
              >
                <svg
                  class="h-7 w-7"
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
              </span>
              <p class="relative mt-4 text-sm font-black text-slate-950">
                Klik untuk memilih gambar
              </p>
              <p class="relative mt-1 max-w-md text-xs leading-5 text-slate-500">
                Format JPG, JPEG, PNG, atau WEBP. Maksimal 4MB.
              </p>
              <input
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="sr-only"
                @change="handleMediaFile"
              />
            </label>
            <p v-if="mediaForm.image_file" class="mt-2 text-xs font-bold text-slate-600">
              File dipilih: {{ mediaForm.image_file.name }}
            </p>
            <p
              v-if="mediaForm.errors.image_file"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ mediaForm.errors.image_file }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-black text-slate-800"
              >Urutan Tampil</label
            >
            <input
              v-model="mediaForm.sort_order"
              type="number"
              min="0"
              placeholder="Contoh: 1"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p class="mt-2 text-xs font-semibold text-slate-500">
              Angka kecil tampil lebih dulu.
            </p>
          </div>

          <div class="flex items-end">
            <div
              class="flex w-full flex-col gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <label class="flex items-center gap-2 text-sm font-black text-slate-700">
                <input
                  v-model="mediaForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Aktif
              </label>
              <button
                type="submit"
                :disabled="mediaForm.processing"
                class="inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ mediaForm.processing ? "Mengupload..." : "Tambah Media" }}
              </button>
            </div>
          </div>
        </form>

        <div class="mt-8 grid gap-6 xl:grid-cols-2">
          <!-- Media Hero -->
          <div>
            <div class="mb-4 flex items-center justify-between gap-4">
              <h3 class="text-sm font-black uppercase tracking-[0.14em] text-slate-400">
                Media Hero
              </h3>
              <span
                class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700 ring-1 ring-red-100"
                >{{ heroMedia.length }} media</span
              >
            </div>

            <div class="space-y-3">
              <div
                v-for="item in heroMedia"
                :key="item.id"
                class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
              >
                <div
                  v-if="editingMedia?.id !== item.id"
                  class="flex flex-col gap-4 p-4 sm:flex-row"
                >
                  <img
                    :src="item.image_url"
                    :alt="item.alt_text || item.title"
                    class="h-48 w-full rounded-2xl object-cover shadow-sm sm:h-28 sm:w-32 sm:shrink-0"
                  />
                  <div class="min-w-0 flex-1">
                    <div class="mb-2 flex flex-wrap gap-2">
                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="groupClass(item.group)"
                        >{{ item.group }}</span
                      >
                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                        >{{ item.layout }}</span
                      >
                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="statusClass(item.is_active)"
                        >{{ item.is_active ? "Aktif" : "Nonaktif" }}</span
                      >
                    </div>
                    <p class="font-black text-slate-950">
                      {{ item.title || "Tanpa Judul" }}
                    </p>
                    <p v-if="item.subtitle" class="mt-1 text-sm leading-6 text-slate-500">
                      {{ item.subtitle }}
                    </p>
                    <p class="mt-1 truncate text-xs font-bold text-slate-500">
                      {{ item.image }}
                    </p>
                    <p class="mt-1 text-xs font-bold text-slate-500">
                      Urutan {{ item.sort_order }}
                    </p>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:flex">
                      <button
                        type="button"
                        class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                        @click="editMedia(item)"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                        @click="deleteMedia(item)"
                      >
                        Hapus
                      </button>
                    </div>
                  </div>
                </div>

                <form
                  v-else
                  class="grid gap-4 bg-white p-4"
                  @submit.prevent="updateMedia"
                >
                  <div
                    class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div>
                      <span
                        class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                        >Mode Edit Media</span
                      >
                      <h3 class="mt-2 text-lg font-black text-slate-950">Edit Media</h3>
                    </div>
                    <button
                      type="button"
                      class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditMedia"
                    >
                      Batal Edit
                    </button>
                  </div>

                  <div class="grid gap-3 md:grid-cols-2">
                    <select
                      v-model="mediaEditForm.group"
                      class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    >
                      <option value="hero">Hero</option>
                      <option value="gallery">Gallery</option>
                    </select>
                    <select
                      v-model="mediaEditForm.layout"
                      class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    >
                      <option value="normal">Normal</option>
                      <option value="large">Large</option>
                      <option value="wide">Wide</option>
                    </select>
                  </div>
                  <input
                    v-model="mediaEditForm.title"
                    type="text"
                    placeholder="Judul media"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                  <input
                    v-model="mediaEditForm.category"
                    type="text"
                    placeholder="Kategori"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                  <input
                    v-model="mediaEditForm.subtitle"
                    type="text"
                    placeholder="Subtitle / keterangan singkat"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <label
                    class="group flex cursor-pointer flex-col items-center justify-center rounded-[1.25rem] border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-red-300 hover:bg-red-50/40"
                  >
                    <p class="text-sm font-black text-slate-950">Ganti gambar</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">
                      Kosongkan jika tidak ingin mengganti gambar.
                    </p>
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp"
                      class="sr-only"
                      @change="handleEditMediaFile"
                    />
                  </label>
                  <p
                    v-if="mediaEditForm.image_file"
                    class="text-xs font-bold text-slate-600"
                  >
                    File dipilih: {{ mediaEditForm.image_file.name }}
                  </p>
                  <p
                    v-if="mediaEditForm.errors.image_file"
                    class="text-xs font-bold text-red-600"
                  >
                    {{ mediaEditForm.errors.image_file }}
                  </p>

                  <div
                    class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div class="flex flex-wrap gap-2">
                      <input
                        v-model="mediaEditForm.sort_order"
                        type="number"
                        min="0"
                        class="h-12 w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      />
                      <label
                        class="flex h-12 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                      >
                        <input
                          v-model="mediaEditForm.is_active"
                          type="checkbox"
                          class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                        />
                        Aktif
                      </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex">
                      <button
                        type="button"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="cancelEditMedia"
                      >
                        Batal
                      </button>
                      <button
                        type="submit"
                        :disabled="mediaEditForm.processing"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                      >
                        {{ mediaEditForm.processing ? "Menyimpan..." : "Simpan" }}
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <div
                v-if="!heroMedia.length"
                class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-7 text-center text-sm font-semibold text-slate-500"
              >
                Belum ada media hero.
              </div>
            </div>
          </div>

          <!-- Media Gallery -->
          <div>
            <div class="mb-4 flex items-center justify-between gap-4">
              <h3 class="text-sm font-black uppercase tracking-[0.14em] text-slate-400">
                Media Gallery
              </h3>
              <span
                class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100"
                >{{ galleryMedia.length }} media</span
              >
            </div>

            <div class="space-y-3">
              <div
                v-for="item in galleryMedia"
                :key="item.id"
                class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
              >
                <div
                  v-if="editingMedia?.id !== item.id"
                  class="flex flex-col gap-4 p-4 sm:flex-row"
                >
                  <img
                    :src="item.image_url"
                    :alt="item.alt_text || item.title"
                    class="h-48 w-full rounded-2xl object-cover shadow-sm sm:h-28 sm:w-32 sm:shrink-0"
                  />
                  <div class="min-w-0 flex-1">
                    <div class="mb-2 flex flex-wrap gap-2">
                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="groupClass(item.group)"
                        >{{ item.group }}</span
                      >
                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                        >{{ item.layout }}</span
                      >
                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="statusClass(item.is_active)"
                        >{{ item.is_active ? "Aktif" : "Nonaktif" }}</span
                      >
                    </div>
                    <p class="font-black text-slate-950">
                      {{ item.title || "Tanpa Judul" }}
                    </p>
                    <p v-if="item.subtitle" class="mt-1 text-sm leading-6 text-slate-500">
                      {{ item.subtitle }}
                    </p>
                    <p class="mt-1 truncate text-xs font-bold text-slate-500">
                      {{ item.image }}
                    </p>
                    <p class="mt-1 text-xs font-bold text-slate-500">
                      Urutan {{ item.sort_order }}
                    </p>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:flex">
                      <button
                        type="button"
                        class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                        @click="editMedia(item)"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                        @click="deleteMedia(item)"
                      >
                        Hapus
                      </button>
                    </div>
                  </div>
                </div>

                <form
                  v-else
                  class="grid gap-4 bg-white p-4"
                  @submit.prevent="updateMedia"
                >
                  <div
                    class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div>
                      <span
                        class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                        >Mode Edit Media</span
                      >
                      <h3 class="mt-2 text-lg font-black text-slate-950">Edit Media</h3>
                    </div>
                    <button
                      type="button"
                      class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditMedia"
                    >
                      Batal Edit
                    </button>
                  </div>

                  <div class="grid gap-3 md:grid-cols-2">
                    <select
                      v-model="mediaEditForm.group"
                      class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    >
                      <option value="hero">Hero</option>
                      <option value="gallery">Gallery</option>
                    </select>
                    <select
                      v-model="mediaEditForm.layout"
                      class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    >
                      <option value="normal">Normal</option>
                      <option value="large">Large</option>
                      <option value="wide">Wide</option>
                    </select>
                  </div>
                  <input
                    v-model="mediaEditForm.title"
                    type="text"
                    placeholder="Judul media"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                  <input
                    v-model="mediaEditForm.category"
                    type="text"
                    placeholder="Kategori"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />
                  <input
                    v-model="mediaEditForm.subtitle"
                    type="text"
                    placeholder="Subtitle / keterangan singkat"
                    class="h-12 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <label
                    class="group flex cursor-pointer flex-col items-center justify-center rounded-[1.25rem] border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-red-300 hover:bg-red-50/40"
                  >
                    <p class="text-sm font-black text-slate-950">Ganti gambar</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">
                      Kosongkan jika tidak ingin mengganti gambar.
                    </p>
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp"
                      class="sr-only"
                      @change="handleEditMediaFile"
                    />
                  </label>
                  <p
                    v-if="mediaEditForm.image_file"
                    class="text-xs font-bold text-slate-600"
                  >
                    File dipilih: {{ mediaEditForm.image_file.name }}
                  </p>
                  <p
                    v-if="mediaEditForm.errors.image_file"
                    class="text-xs font-bold text-red-600"
                  >
                    {{ mediaEditForm.errors.image_file }}
                  </p>

                  <div
                    class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div class="flex flex-wrap gap-2">
                      <input
                        v-model="mediaEditForm.sort_order"
                        type="number"
                        min="0"
                        class="h-12 w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      />
                      <label
                        class="flex h-12 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                      >
                        <input
                          v-model="mediaEditForm.is_active"
                          type="checkbox"
                          class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                        />
                        Aktif
                      </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex">
                      <button
                        type="button"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="cancelEditMedia"
                      >
                        Batal
                      </button>
                      <button
                        type="submit"
                        :disabled="mediaEditForm.processing"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                      >
                        {{ mediaEditForm.processing ? "Menyimpan..." : "Simpan" }}
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <div
                v-if="!galleryMedia.length"
                class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-7 text-center text-sm font-semibold text-slate-500"
              >
                Belum ada media gallery.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
@media (max-width: 639px) {
  .custom-home-mobile-page {
    display: flex !important;
    flex-direction: column !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    gap: 1.25rem !important;
    padding-bottom: 2rem !important;
    overflow-x: hidden !important;
  }

  .custom-home-mobile-page,
  .custom-home-mobile-page * {
    box-sizing: border-box !important;
    min-width: 0 !important;
  }

  .custom-home-mobile-page :is(section, article, aside, form, div) {
    max-width: 100% !important;
  }

  .custom-home-mobile-page :is(h1, h2, h3, h4, p, span, label, button, a) {
    overflow-wrap: anywhere !important;
    word-break: normal !important;
  }

  .custom-home-mobile-page :is(section, article, aside) {
    border-radius: 1.25rem !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-home-mobile-page form {
    max-width: 100% !important;
  }

  .custom-home-mobile-page .text-justify {
    text-align: left !important;
  }

  .custom-home-mobile-page :is(input:not([type="checkbox"]):not([type="file"]), select) {
    width: 100% !important;
    max-width: 100% !important;
    min-height: 3rem !important;
    height: 3rem !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.875rem !important;
  }

  .custom-home-mobile-page textarea {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.875rem !important;
    font-size: 0.875rem !important;
    line-height: 1.65 !important;
  }

  .custom-home-mobile-page label {
    font-size: 0.82rem !important;
    line-height: 1.4 !important;
  }

  .custom-home-mobile-page :is(button, a) {
    max-width: 100% !important;
    border-radius: 1rem !important;
    white-space: normal !important;
    text-align: center !important;
  }

  .custom-home-hero {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    overflow: hidden !important;
  }

  .custom-home-hero > div.relative {
    gap: 1.25rem !important;
  }

  .custom-home-hero h1 {
    font-size: 1.7rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-home-hero p {
    font-size: 0.82rem !important;
    line-height: 1.65 !important;
  }

  .custom-home-hero .mb-5 {
    margin-bottom: 0.875rem !important;
  }

  .custom-home-hero .rounded-\[1\.4rem\] {
    border-radius: 1rem !important;
  }

  .custom-home-hero a {
    min-height: 3.5rem !important;
    padding-inline: 1rem !important;
  }

  .custom-home-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 0.75rem !important;
  }

  .custom-home-stats article {
    min-height: 8.3rem !important;
    padding: 0.9rem !important;
    border-radius: 1.1rem !important;
  }

  .custom-home-stats article h3 {
    margin-top: 0.55rem !important;
    font-size: 1.55rem !important;
    line-height: 1 !important;
  }

  .custom-home-stats article p {
    font-size: 0.68rem !important;
    line-height: 1.45 !important;
  }

  .custom-home-stats article svg {
    width: 1rem !important;
    height: 1rem !important;
  }

  .custom-home-editor-grid {
    gap: 1rem !important;
  }

  .custom-home-section-sidebar {
    position: static !important;
    padding: 1rem !important;
    border-radius: 1.25rem !important;
    overflow: visible !important;
  }

  .custom-home-section-sidebar > div:first-child {
    margin-bottom: 1rem !important;
  }

  .custom-home-section-sidebar h2 {
    margin-top: 0.75rem !important;
    font-size: 1.05rem !important;
    line-height: 1.25 !important;
  }

  .custom-home-section-sidebar p.mt-2.text-sm.leading-7 {
    display: none !important;
  }

  .custom-home-section-nav {
    display: flex !important;
    width: 100% !important;
    max-width: 100% !important;
    gap: 0.75rem !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    padding-bottom: 0.25rem !important;
    scroll-snap-type: x mandatory !important;
    scrollbar-width: none !important;
  }

  .custom-home-section-nav::-webkit-scrollbar {
    display: none !important;
  }

  .custom-home-section-tab {
    flex: 0 0 13.25rem !important;
    width: 13.25rem !important;
    max-width: 82vw !important;
    scroll-snap-align: start !important;
    gap: 0.75rem !important;
    padding: 0.75rem !important;
    border-radius: 1rem !important;
  }

  .custom-home-section-tab p.mt-1 {
    display: none !important;
  }

  .custom-home-section-tab > span:first-child,
  .custom-home-section-tab .h-10.w-10 {
    width: 2.25rem !important;
    height: 2.25rem !important;
    border-radius: 0.85rem !important;
  }

  .custom-home-section-tab > span:last-child {
    padding: 0.34rem 0.55rem !important;
    font-size: 0.58rem !important;
    letter-spacing: 0.06em !important;
  }

  .custom-home-section-form {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 1.25rem !important;
    overflow: visible !important;
  }

  .custom-home-section-form > div:first-child {
    padding: 1rem !important;
  }

  .custom-home-section-form > div:first-child h2 {
    font-size: 1.35rem !important;
    line-height: 1.15 !important;
  }

  .custom-home-section-form > div:first-child p {
    font-size: 0.82rem !important;
    line-height: 1.6 !important;
  }

  .custom-home-section-form > div:last-child {
    padding: 1rem !important;
    overflow: visible !important;
  }

  .custom-home-section-form .grid {
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 1rem !important;
  }

  .custom-home-section-form .mt-6,
  .custom-home-section-form .mt-7 {
    width: 100% !important;
    max-width: 100% !important;
    overflow: visible !important;
  }

  .custom-home-section-form .mt-7 {
    margin-top: 1.25rem !important;
    padding-top: 1rem !important;
  }

  .custom-home-section-form button[type="submit"] {
    display: inline-flex !important;
    width: 100% !important;
    min-width: 0 !important;
    min-height: 3rem !important;
    padding-inline: 1rem !important;
    overflow: hidden !important;
  }

  .custom-home-section-form button[type="submit"] > span.relative {
    display: block !important;
    width: 100% !important;
    overflow: visible !important;
    white-space: normal !important;
    text-align: center !important;
  }

  .custom-home-two-column {
    gap: 1rem !important;
  }

  .custom-home-two-column article,
  .custom-home-media {
    overflow: visible !important;
    border-radius: 1.25rem !important;
  }

  .custom-home-two-column article > div:first-child,
  .custom-home-media > div:first-child {
    padding: 1rem !important;
  }

  .custom-home-two-column article > div:first-child h2,
  .custom-home-media > div:first-child h2 {
    font-size: 1.35rem !important;
    line-height: 1.15 !important;
  }

  .custom-home-two-column article > div:first-child p,
  .custom-home-media > div:first-child p {
    font-size: 0.82rem !important;
    line-height: 1.6 !important;
  }

  .custom-home-two-column article > div:last-child,
  .custom-home-media > div:last-child {
    padding: 1rem !important;
  }

  .custom-home-two-column form,
  .custom-home-media form {
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.875rem !important;
  }

  .custom-home-two-column .space-y-3 > div,
  .custom-home-media .space-y-3 > div {
    border-radius: 1rem !important;
    overflow: visible !important;
  }

  .custom-home-mobile-page .grid.grid-cols-2.gap-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-home-mobile-page .grid.grid-cols-2.gap-2 button,
  .custom-home-mobile-page .grid.grid-cols-2.gap-2 a {
    min-width: 0 !important;
    min-height: 2.65rem !important;
    padding-inline: 0.75rem !important;
  }

  .custom-home-media img {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 1rem !important;
  }

  .custom-home-media .min-h-\[13rem\] {
    min-height: 10.5rem !important;
  }

  .custom-home-media .h-16.w-16 {
    width: 3.25rem !important;
    height: 3.25rem !important;
    border-radius: 1rem !important;
  }

  .custom-home-mobile-page .rounded-\[1\.35rem\],
  .custom-home-mobile-page .rounded-\[1\.45rem\],
  .custom-home-mobile-page .rounded-\[1\.5rem\] {
    border-radius: 1rem !important;
  }

  .custom-home-mobile-page .text-2xl {
    font-size: 1.35rem !important;
    line-height: 1.15 !important;
  }

  .custom-home-mobile-page .p-7 {
    padding: 1.25rem !important;
  }

  .custom-home-mobile-page .p-6,
  .custom-home-mobile-page .p-5 {
    padding: 1rem !important;
  }

  .custom-home-mobile-page .px-5 {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}
</style>

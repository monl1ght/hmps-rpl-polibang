<script setup>
import { computed, onUnmounted, ref } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  heroSetting: {
    type: Object,
    default: () => ({}),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  programs: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const editingCategory = ref(null);
const editingProgram = ref(null);
const selectedCategoryFilter = ref("semua");
const searchQuery = ref("");

const previewImage = ref(null);
const editPreviewImage = ref(null);
const heroImagePreviews = ref({
  image_one_file: props.heroSetting?.image_one_url || null,
  image_two_file: props.heroSetting?.image_two_url || null,
  image_three_file: props.heroSetting?.image_three_url || null,
  image_four_file: props.heroSetting?.image_four_url || null,
});

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const categories = computed(() =>
  Array.isArray(props.categories) ? props.categories : []
);
const programs = computed(() => (Array.isArray(props.programs) ? props.programs : []));

const stats = computed(() => {
  const total = programs.value.length;
  const published = programs.value.filter((item) => item.is_published).length;
  const featured = programs.value.filter((item) => item.is_featured).length;

  return [
    {
      label: "Total Program",
      value: total,
      helper: "Seluruh program kerja",
      icon: "briefcase",
    },
    {
      label: "Published",
      value: published,
      helper: "Tampil di halaman user",
      icon: "check",
    },
    {
      label: "Featured",
      value: featured,
      helper: "Program unggulan",
      icon: "star",
    },
    {
      label: "Kategori",
      value: categories.value.length,
      helper: "Kelompok program kerja",
      icon: "grid",
    },
  ];
});

const filteredPrograms = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase();

  return programs.value.filter((program) => {
    const categoryMatch =
      selectedCategoryFilter.value === "semua" ||
      String(program.program_work_category_id) === String(selectedCategoryFilter.value);

    const searchMatch =
      !keyword ||
      String(program.title || "")
        .toLowerCase()
        .includes(keyword) ||
      String(program.division_name || "")
        .toLowerCase()
        .includes(keyword) ||
      String(program.person_in_charge || "")
        .toLowerCase()
        .includes(keyword) ||
      String(program.excerpt || "")
        .toLowerCase()
        .includes(keyword);

    return categoryMatch && searchMatch;
  });
});

const heroForm = useForm({
  badge: props.heroSetting?.badge || "Halaman Program Kerja HMPS RPL",
  title: props.heroSetting?.title || "Program Kerja",
  title_highlight: props.heroSetting?.title_highlight || "HMPS RPL",
  description:
    props.heroSetting?.description ||
    "Halaman ini menampilkan seluruh rancangan program kerja HMPS Rekayasa Perangkat Lunak secara rapi, modern, dan mudah dipahami berdasarkan kategori, divisi pelaksana, penanggung jawab, serta estimasi anggaran.",
  primary_button_label: props.heroSetting?.primary_button_label || "Lihat Semua Program",
  primary_button_url: props.heroSetting?.primary_button_url || "#program-list",
  secondary_button_label:
    props.heroSetting?.secondary_button_label || "Konsultasi Gratis",
  secondary_button_url: props.heroSetting?.secondary_button_url || "/konsultasi",
  image_one_file: null,
  image_one_alt:
    props.heroSetting?.image_one_alt || "Workshop dan program kerja mahasiswa",
  image_two_file: null,
  image_two_alt: props.heroSetting?.image_two_alt || "Seminar dan kegiatan mahasiswa",
  image_three_file: null,
  image_three_alt:
    props.heroSetting?.image_three_alt || "Upgrading dan koordinasi organisasi",
  image_four_file: null,
  image_four_alt: props.heroSetting?.image_four_alt || "Kolaborasi organisasi mahasiswa",
  floating_badge_top_icon: props.heroSetting?.floating_badge_top_icon || "📌",
  floating_badge_top_title:
    props.heroSetting?.floating_badge_top_title || "Program Tersusun",
  floating_badge_top_subtitle:
    props.heroSetting?.floating_badge_top_subtitle || "Berdasarkan kategori",
  floating_badge_bottom_icon: props.heroSetting?.floating_badge_bottom_icon || "🚀",
  floating_badge_bottom_title:
    props.heroSetting?.floating_badge_bottom_title || "Fokus Periode",
  floating_badge_bottom_subtitle:
    props.heroSetting?.floating_badge_bottom_subtitle || "Kegiatan berkelanjutan",
  is_active: props.heroSetting?.is_active ?? true,
});

const categoryForm = useForm({
  name: "",
  slug: "",
  title: "",
  description: "",
  sort_order: 0,
});

const categoryEditForm = useForm({
  name: "",
  slug: "",
  title: "",
  description: "",
  sort_order: 0,
});

const programForm = useForm({
  program_work_category_id: "",
  title: "",
  schedule_text: "",
  division_name: "",
  person_in_charge: "",
  target: "",
  budget: 0,
  image_file: null,
  excerpt: "",
  description: "",
  goals_text: "",
  benefits_text: "",
  funding_sources_text: "",
  is_featured: false,
  is_published: true,
  sort_order: 0,
});

const programEditForm = useForm({
  program_work_category_id: "",
  title: "",
  schedule_text: "",
  division_name: "",
  person_in_charge: "",
  target: "",
  budget: 0,
  image_file: null,
  excerpt: "",
  description: "",
  goals_text: "",
  benefits_text: "",
  funding_sources_text: "",
  is_featured: false,
  is_published: true,
  sort_order: 0,
});

const revokePreview = (url) => {
  if (url && String(url).startsWith("blob:")) {
    URL.revokeObjectURL(url);
  }
};

const formatCurrency = (value) => {
  const numberValue = Number(value || 0);

  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(numberValue);
};

const categoryName = (categoryId) => {
  return categories.value.find((category) => Number(category.id) === Number(categoryId))
    ?.name;
};

const categoryBadgeClass = (program) => {
  if (program.is_featured) {
    return "border-amber-200 bg-amber-50 text-amber-700";
  }

  if (program.is_published) {
    return "border-emerald-200 bg-emerald-50 text-emerald-700";
  }

  return "border-slate-200 bg-slate-100 text-slate-600";
};

const categoryLabelClass = (index) => {
  const styles = [
    "border-red-200 bg-red-50 text-red-700",
    "border-blue-200 bg-blue-50 text-blue-700",
    "border-violet-200 bg-violet-50 text-violet-700",
    "border-amber-200 bg-amber-50 text-amber-700",
  ];

  return styles[index % styles.length];
};

const heroImageFields = [
  {
    input: "image_one_file",
    alt: "image_one_alt",
    label: "Gambar Hero 1",
    helper: "Posisi kiri atas pada grid hero.",
  },
  {
    input: "image_two_file",
    alt: "image_two_alt",
    label: "Gambar Hero 2",
    helper: "Posisi kiri bawah pada grid hero.",
  },
  {
    input: "image_three_file",
    alt: "image_three_alt",
    label: "Gambar Hero 3",
    helper: "Posisi kanan atas pada grid hero.",
  },
  {
    input: "image_four_file",
    alt: "image_four_alt",
    label: "Gambar Hero 4",
    helper: "Posisi kanan bawah pada grid hero.",
  },
];

const handleHeroImageFile = (event, field) => {
  const file = event.target.files?.[0] || null;

  revokePreview(heroImagePreviews.value[field]);

  heroForm[field] = file;
  heroImagePreviews.value[field] = file
    ? URL.createObjectURL(file)
    : props.heroSetting?.[field.replace("_file", "_url")] || null;
};

const updateHeroSetting = () => {
  if (!props.heroSetting?.id) return;

  heroForm.post(`/admin/program-kerja/hero/${props.heroSetting.id}`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      heroForm.image_one_file = null;
      heroForm.image_two_file = null;
      heroForm.image_three_file = null;
      heroForm.image_four_file = null;
    },
  });
};

const resetCategoryForm = () => {
  categoryForm.reset();
  categoryForm.sort_order = 0;
};

const resetProgramForm = () => {
  revokePreview(previewImage.value);

  programForm.reset();
  programForm.program_work_category_id = "";
  programForm.budget = 0;
  programForm.image_file = null;
  programForm.is_featured = false;
  programForm.is_published = true;
  programForm.sort_order = 0;

  previewImage.value = null;
};

const handleImageFile = (event) => {
  const file = event.target.files[0] || null;

  revokePreview(previewImage.value);

  programForm.image_file = file;
  previewImage.value = file ? URL.createObjectURL(file) : null;
};

const handleEditImageFile = (event) => {
  const file = event.target.files[0] || null;

  revokePreview(editPreviewImage.value);

  programEditForm.image_file = file;
  editPreviewImage.value = file
    ? URL.createObjectURL(file)
    : editingProgram.value?.image_url || null;
};

const storeCategory = () => {
  categoryForm.post("/admin/program-kerja/categories", {
    preserveScroll: true,
    onSuccess: () => resetCategoryForm(),
  });
};

const editCategory = (category) => {
  editingCategory.value = category;

  categoryEditForm.name = category.name || "";
  categoryEditForm.slug = category.slug || "";
  categoryEditForm.title = category.title || "";
  categoryEditForm.description = category.description || "";
  categoryEditForm.sort_order = Number(category.sort_order || 0);
};

const cancelEditCategory = () => {
  editingCategory.value = null;
  categoryEditForm.reset();
};

const updateCategory = () => {
  if (!editingCategory.value) return;

  categoryEditForm.put(`/admin/program-kerja/categories/${editingCategory.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditCategory(),
  });
};

const deleteCategory = (category) => {
  if (!confirm(`Hapus kategori "${category.name}"?`)) return;

  router.delete(`/admin/program-kerja/categories/${category.id}`, {
    preserveScroll: true,
  });
};

const storeProgram = () => {
  programForm.post("/admin/program-kerja/programs", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => resetProgramForm(),
  });
};

const editProgram = (program) => {
  revokePreview(editPreviewImage.value);

  editingProgram.value = program;

  programEditForm.program_work_category_id = program.program_work_category_id || "";
  programEditForm.title = program.title || "";
  programEditForm.schedule_text = program.schedule_text || "";
  programEditForm.division_name = program.division_name || "";
  programEditForm.person_in_charge = program.person_in_charge || "";
  programEditForm.target = program.target || "";
  programEditForm.budget = Number(program.budget || 0);
  programEditForm.image_file = null;
  programEditForm.excerpt = program.excerpt || "";
  programEditForm.description = program.description || "";
  programEditForm.goals_text = program.goals_text || "";
  programEditForm.benefits_text = program.benefits_text || "";
  programEditForm.funding_sources_text = program.funding_sources_text || "";
  programEditForm.is_featured = Boolean(program.is_featured);
  programEditForm.is_published = Boolean(program.is_published);
  programEditForm.sort_order = Number(program.sort_order || 0);

  editPreviewImage.value = program.image_url || null;
};

const cancelEditProgram = () => {
  revokePreview(editPreviewImage.value);

  editingProgram.value = null;
  programEditForm.reset();
  editPreviewImage.value = null;
};

const updateProgram = () => {
  if (!editingProgram.value) return;

  programEditForm
    .transform((data) => ({
      ...data,
      _method: "PUT",
    }))
    .post(`/admin/program-kerja/programs/${editingProgram.value.id}`, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => cancelEditProgram(),
    });
};

const deleteProgram = (program) => {
  if (!confirm(`Hapus program kerja "${program.title}"?`)) return;

  router.delete(`/admin/program-kerja/programs/${program.id}`, {
    preserveScroll: true,
  });
};

onUnmounted(() => {
  revokePreview(previewImage.value);
  revokePreview(editPreviewImage.value);

  Object.values(heroImagePreviews.value).forEach((url) => revokePreview(url));
});
</script>

<template>
  <Head title="Custom Program Kerja" />

  <div
    class="custom-layanan-jasa-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8"
  >
    <!-- Header -->
    <section
      class="relative overflow-hidden rounded-[2rem] border border-slate-800 bg-[linear-gradient(135deg,#0f172a,#111827_52%,#7f1d1d)] p-6 text-white shadow-[0_24px_70px_rgba(2,6,23,0.22)] sm:p-8 lg:p-10"
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
            Admin / Custom Program Kerja
          </div>

          <h1
            class="max-w-3xl text-[2rem] font-black leading-[1.08] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.15rem]"
          >
            Kelola Program Kerja
            <span class="block text-red-300">HMPS RPL</span>
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300 sm:text-base"
          >
            Tambah, edit, publish, upload gambar, dan atur kategori program kerja agar
            halaman Program Kerja tampil dinamis, rapi, dan profesional.
          </p>
        </div>

        <a
          href="/program-kerja"
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
          <span class="relative z-10">Preview Program Kerja</span>
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

    <!-- Stats -->
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
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
              v-if="item.icon === 'briefcase'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm8 7h.01M10 13h.01"
            />
            <path
              v-else-if="item.icon === 'check'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
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
              d="M4 5a1 1 0 0 1 1-1h5v6H4V5Zm10-1h5a1 1 0 0 1 1 1v5h-6V4ZM4 14h6v6H5a1 1 0 0 1-1-1v-5Zm10 0h6v5a1 1 0 0 1-1 1h-5v-6Z"
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

    <!-- Hero Section Management -->
    <section
      class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

      <div
        class="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.08),transparent_32%),linear-gradient(135deg,#ffffff_0%,#f8fafc_50%,#fff1f2_100%)] px-5 py-6 sm:px-6 lg:px-7"
      >
        <div
          class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-red-500/10 blur-[70px]"
        />

        <div
          class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
        >
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-white/80 px-3.5 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-red-700 shadow-sm"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Custom Hero Section
            </div>

            <h2
              class="text-2xl font-black tracking-[-0.045em] text-slate-950 sm:text-3xl"
            >
              Tampilan Utama Program Kerja
            </h2>

            <p
              class="mt-3 max-w-3xl text-left text-sm leading-7 text-slate-500 sm:text-justify"
            >
              Atur badge, judul, tombol aksi, gambar hero, dan floating badge agar halaman
              Program Kerja tampil rapi, modern, serta konsisten dengan desain dashboard.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
            <span
              class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600 shadow-sm"
            >
              4 Gambar Hero
            </span>
            <span
              class="inline-flex items-center justify-center rounded-full border px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
              :class="
                heroForm.is_active
                  ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                  : 'border-slate-200 bg-slate-100 text-slate-600'
              "
            >
              {{ heroForm.is_active ? "Aktif" : "Nonaktif" }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid gap-0 xl:grid-cols-[0.34fr_0.66fr]">
        <!-- Preview Panel -->
        <aside
          class="border-b border-slate-200 bg-slate-50/80 p-5 sm:p-6 lg:p-7 xl:border-b-0 xl:border-r"
        >
          <div
            class="sticky top-6 rounded-[1.75rem] border border-slate-800 bg-[linear-gradient(135deg,#07111f,#0f172a_55%,#7f1d1d)] p-5 text-white shadow-[0_20px_54px_rgba(15,23,42,0.20)]"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-red-200"
                >
                  Live Preview
                </p>
                <h3 class="mt-1 text-xl font-black tracking-[-0.04em] text-white">
                  Grid Hero
                </h3>
              </div>

              <span
                class="rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[0.65rem] font-black uppercase tracking-[0.08em] text-slate-200"
              >
                User View
              </span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
              <div
                v-for="field in heroImageFields"
                :key="`preview-${field.input}`"
                class="group relative min-h-[8.5rem] overflow-hidden rounded-2xl border border-white/10 bg-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]"
              >
                <img
                  v-if="heroImagePreviews[field.input]"
                  :src="heroImagePreviews[field.input]"
                  :alt="heroForm[field.alt] || field.label"
                  class="h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-105"
                />
                <div
                  v-else
                  class="flex h-full min-h-[8.5rem] flex-col items-center justify-center gap-2 text-center text-xs font-bold text-slate-400"
                >
                  <svg
                    class="h-6 w-6 text-slate-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.2"
                      d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                    />
                  </svg>
                  Belum ada gambar
                </div>

                <div
                  class="absolute inset-0 bg-gradient-to-t from-slate-950/65 via-transparent to-transparent"
                />
                <span
                  class="absolute bottom-2 left-2 rounded-full bg-white/90 px-2.5 py-1 text-[0.62rem] font-black uppercase tracking-[0.08em] text-slate-800"
                >
                  {{ field.label.replace("Gambar Hero ", "Hero ") }}
                </span>
              </div>
            </div>

            <div class="mt-5 rounded-2xl border border-white/10 bg-white/[0.06] p-4">
              <p
                class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-200"
              >
                Catatan Tampilan
              </p>
              <p class="mt-2 text-left text-sm leading-7 text-slate-300 sm:text-justify">
                Gunakan gambar dengan rasio lanskap dan kualitas konsisten agar grid hero
                terlihat seimbang pada desktop maupun mobile.
              </p>
            </div>
          </div>
        </aside>

        <!-- Form Panel -->
        <form class="grid gap-5 p-5 sm:p-6 lg:p-7" @submit.prevent="updateHeroSetting">
          <!-- Text Content -->
          <div
            class="rounded-[1.55rem] border border-slate-200 bg-slate-50/70 p-4 sm:p-5"
          >
            <div
              class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
                >
                  Konten Hero
                </p>
                <h3 class="mt-1 text-lg font-black tracking-[-0.03em] text-slate-950">
                  Teks Utama dan Status
                </h3>
              </div>
              <span
                class="w-fit rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-500"
              >
                Section Utama
              </span>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Badge</label
                >
                <input
                  v-model="heroForm.badge"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  placeholder="Contoh: Halaman Program Kerja HMPS RPL"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Status Tampil</label
                >
                <select
                  v-model="heroForm.is_active"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                >
                  <option :value="true">Aktif</option>
                  <option :value="false">Nonaktif</option>
                </select>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Judul Utama</label
                >
                <input
                  v-model="heroForm.title"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  placeholder="Contoh: Program Kerja"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Highlight Judul</label
                >
                <input
                  v-model="heroForm.title_highlight"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  placeholder="Contoh: HMPS RPL"
                />
              </div>
            </div>

            <div class="mt-4">
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Deskripsi Hero</label
              >
              <textarea
                v-model="heroForm.description"
                rows="4"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                placeholder="Tuliskan deskripsi hero section..."
              />
            </div>
          </div>

          <!-- CTA -->
          <div
            class="rounded-[1.55rem] border border-slate-200 bg-white p-4 sm:p-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]"
          >
            <div class="mb-5">
              <p
                class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
              >
                Tombol Aksi
              </p>
              <h3 class="mt-1 text-lg font-black tracking-[-0.03em] text-slate-950">
                Primary dan Secondary Button
              </h3>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Label Tombol Utama</label
                >
                <input
                  v-model="heroForm.primary_button_label"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >URL Tombol Utama</label
                >
                <input
                  v-model="heroForm.primary_button_url"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="#program-list"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Label Tombol Kedua</label
                >
                <input
                  v-model="heroForm.secondary_button_label"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >URL Tombol Kedua</label
                >
                <input
                  v-model="heroForm.secondary_button_url"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="/konsultasi"
                />
              </div>
            </div>
          </div>

          <!-- Images -->
          <div
            class="rounded-[1.55rem] border border-slate-200 bg-slate-50/70 p-4 sm:p-5"
          >
            <div
              class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
                >
                  Media Hero
                </p>
                <h3 class="mt-1 text-lg font-black tracking-[-0.03em] text-slate-950">
                  Upload Gambar dan Alt Text
                </h3>
              </div>
              <span
                class="w-fit rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-500"
              >
                JPG / PNG / WEBP
              </span>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
              <div
                v-for="field in heroImageFields"
                :key="field.input"
                class="group overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.04)] transition duration-300 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
              >
                <div class="grid gap-0 sm:grid-cols-[7.5rem_1fr]">
                  <div
                    class="relative min-h-[9rem] overflow-hidden bg-slate-100 sm:min-h-full"
                  >
                    <img
                      v-if="heroImagePreviews[field.input]"
                      :src="heroImagePreviews[field.input]"
                      :alt="heroForm[field.alt] || field.label"
                      class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    />
                    <div
                      v-else
                      class="flex h-full min-h-[9rem] items-center justify-center bg-slate-100 text-slate-300"
                    >
                      <svg
                        class="h-8 w-8"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.2"
                          d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                        />
                      </svg>
                    </div>
                  </div>

                  <div class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                      <div>
                        <h4 class="text-sm font-black text-slate-950">
                          {{ field.label }}
                        </h4>
                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                          {{ field.helper }} Format JPG, PNG, WEBP. Maksimal 4MB.
                        </p>
                      </div>
                    </div>

                    <label
                      class="mt-3 flex min-h-[46px] cursor-pointer items-center justify-center gap-2 rounded-2xl border border-dashed border-red-200 bg-red-50 px-4 text-center text-xs font-black text-red-700 transition hover:border-red-300 hover:bg-red-100"
                    >
                      <svg
                        class="h-4 w-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.2"
                          d="M12 16V4m0 0-4 4m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                        />
                      </svg>
                      <span class="truncate">
                        {{ heroForm[field.input]?.name || "Pilih gambar" }}
                      </span>
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                        @change="handleHeroImageFile($event, field.input)"
                      />
                    </label>

                    <input
                      v-model="heroForm[field.alt]"
                      type="text"
                      class="mt-3 h-[3rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                      placeholder="Alt text gambar"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating Badges -->
          <div class="grid gap-4 2xl:grid-cols-2">
            <div
              class="min-w-0 overflow-hidden rounded-[1.55rem] border border-slate-200 bg-white p-4 shadow-[0_10px_30px_rgba(15,23,42,0.04)] sm:p-5"
            >
              <div class="mb-4 flex items-center gap-3">
                <div
                  class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-xl ring-1 ring-red-100"
                >
                  {{ heroForm.floating_badge_top_icon || "📌" }}
                </div>
                <div class="min-w-0">
                  <p
                    class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
                  >
                    Floating Badge Atas
                  </p>
                  <h3 class="mt-1 truncate text-base font-black text-slate-950">
                    Badge Informasi Atas
                  </h3>
                </div>
              </div>

              <div
                class="grid min-w-0 gap-3 sm:grid-cols-[minmax(4.5rem,0.28fr)_minmax(0,1fr)]"
              >
                <input
                  v-model="heroForm.floating_badge_top_icon"
                  type="text"
                  class="h-[3rem] w-full min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-center text-sm font-bold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="📌"
                />
                <input
                  v-model="heroForm.floating_badge_top_title"
                  type="text"
                  class="h-[3rem] w-full min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-bold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Program Tersusun"
                />
              </div>
              <input
                v-model="heroForm.floating_badge_top_subtitle"
                type="text"
                class="mt-3 h-[3rem] w-full min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-bold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="Berdasarkan kategori"
              />
            </div>

            <div
              class="min-w-0 overflow-hidden rounded-[1.55rem] border border-slate-200 bg-white p-4 shadow-[0_10px_30px_rgba(15,23,42,0.04)] sm:p-5"
            >
              <div class="mb-4 flex items-center gap-3">
                <div
                  class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-xl ring-1 ring-red-100"
                >
                  {{ heroForm.floating_badge_bottom_icon || "🚀" }}
                </div>
                <div class="min-w-0">
                  <p
                    class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700"
                  >
                    Floating Badge Bawah
                  </p>
                  <h3 class="mt-1 truncate text-base font-black text-slate-950">
                    Badge Informasi Bawah
                  </h3>
                </div>
              </div>

              <div
                class="grid min-w-0 gap-3 sm:grid-cols-[minmax(4.5rem,0.28fr)_minmax(0,1fr)]"
              >
                <input
                  v-model="heroForm.floating_badge_bottom_icon"
                  type="text"
                  class="h-[3rem] w-full min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-center text-sm font-bold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="🚀"
                />
                <input
                  v-model="heroForm.floating_badge_bottom_title"
                  type="text"
                  class="h-[3rem] w-full min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-bold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Fokus Periode"
                />
              </div>
              <input
                v-model="heroForm.floating_badge_bottom_subtitle"
                type="text"
                class="mt-3 h-[3rem] w-full min-w-0 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-bold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                placeholder="Kegiatan berkelanjutan"
              />
            </div>
          </div>

          <div
            class="sticky bottom-4 z-10 flex min-w-0 flex-col gap-3 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white/90 p-3 shadow-[0_18px_48px_rgba(15,23,42,0.12)] backdrop-blur-md sm:flex-row sm:items-center sm:justify-between"
          >
            <p class="min-w-0 px-2 text-xs font-semibold leading-5 text-slate-500">
              Perubahan hanya menyentuh tampilan hero section dan tetap memakai route
              backend yang sama.
            </p>

            <button
              type="submit"
              :disabled="heroForm.processing"
              class="group relative inline-flex min-h-[52px] w-full shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 sm:w-auto"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">
                {{ heroForm.processing ? "Menyimpan Hero..." : "Simpan Hero Section" }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Category Management -->
    <section class="grid gap-6 xl:grid-cols-[0.42fr_0.58fr]">
      <!-- Add Category -->
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
              Tambah Kategori
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Data Kategori Baru
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Kategori digunakan untuk mengelompokkan program seperti Unggulan, Rutin,
              Pengabdian, dan Pendukung.
            </p>
          </div>

          <form class="grid gap-5" @submit.prevent="storeCategory">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Nama Kategori
                <span class="text-red-600">*</span>
              </label>
              <input
                v-model="categoryForm.name"
                type="text"
                placeholder="Contoh: Unggulan"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="categoryForm.errors.name"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ categoryForm.errors.name }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">Slug</label>
              <input
                v-model="categoryForm.slug"
                type="text"
                placeholder="Opsional, contoh: unggulan"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                Boleh dikosongkan, sistem dapat membuat slug otomatis.
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Judul Section</label
              >
              <input
                v-model="categoryForm.title"
                type="text"
                placeholder="Contoh: Program Unggulan"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Deskripsi Kategori</label
              >
              <textarea
                v-model="categoryForm.description"
                rows="4"
                placeholder="Tuliskan deskripsi kategori..."
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Urutan Tampil</label
              >
              <input
                v-model="categoryForm.sort_order"
                type="number"
                min="0"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                Angka kecil akan tampil lebih awal pada halaman user.
              </p>
            </div>

            <button
              type="submit"
              :disabled="categoryForm.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                categoryForm.processing ? "Menyimpan..." : "Tambah Kategori"
              }}</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Category List -->
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
                Daftar Kategori
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Kelola Kategori
              </h2>

              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Kelola kategori yang digunakan oleh program kerja.
              </p>
            </div>

            <span
              class="w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
            >
              {{ categories.length }} Kategori
            </span>
          </div>

          <div class="space-y-3">
            <div
              v-for="(category, index) in categories"
              :key="category.id"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
            >
              <div v-if="editingCategory?.id !== category.id" class="p-4">
                <div
                  class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
                >
                  <div class="min-w-0">
                    <div class="flex flex-wrap gap-2">
                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="categoryLabelClass(index)"
                      >
                        {{ category.slug || "tanpa-slug" }}
                      </span>
                      <span
                        class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                      >
                        {{ category.programs_count }} Program
                      </span>
                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                      >
                        Urutan {{ category.sort_order }}
                      </span>
                    </div>

                    <h3 class="mt-3 text-base font-black text-slate-950">
                      {{ category.name }}
                    </h3>

                    <p class="mt-1 text-sm font-bold text-slate-600">
                      {{ category.title || "Tanpa judul section" }}
                    </p>

                    <p
                      class="mt-2 line-clamp-2 text-justify text-sm leading-7 text-slate-500"
                    >
                      {{ category.description || "Belum ada deskripsi kategori." }}
                    </p>
                  </div>

                  <div class="flex shrink-0 gap-2">
                    <button
                      type="button"
                      class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                      @click="editCategory(category)"
                    >
                      Edit
                    </button>

                    <button
                      type="button"
                      class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                      @click="deleteCategory(category)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>
              </div>

              <form
                v-else
                class="grid gap-4 bg-white p-4"
                @submit.prevent="updateCategory"
              >
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Kategori
                    </span>
                    <h3 class="mt-2 text-lg font-black text-slate-950">
                      Edit {{ category.name }}
                    </h3>
                  </div>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditCategory"
                  >
                    Batal Edit
                  </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="categoryEditForm.name"
                    type="text"
                    placeholder="Nama kategori"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <input
                    v-model="categoryEditForm.slug"
                    type="text"
                    placeholder="Slug"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <input
                  v-model="categoryEditForm.title"
                  type="text"
                  placeholder="Judul section"
                  class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <textarea
                  v-model="categoryEditForm.description"
                  rows="3"
                  placeholder="Deskripsi kategori"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <div
                  class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                  <input
                    v-model="categoryEditForm.sort_order"
                    type="number"
                    min="0"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:w-36"
                  />

                  <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditCategory"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="categoryEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {{
                        categoryEditForm.processing ? "Menyimpan..." : "Simpan Kategori"
                      }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!categories.length"
              class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
            >
              <h3 class="text-lg font-black text-slate-950">Belum ada kategori</h3>
              <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                Tambahkan kategori terlebih dahulu sebelum membuat program kerja.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Add Program -->
    <section
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
              Tambah Program
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Tambah Program Kerja
            </h2>

            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Isi data program secara lengkap agar halaman user menampilkan informasi yang
              profesional dan mudah dipahami.
            </p>
          </div>

          <span
            class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
          >
            JPG / PNG / WEBP
          </span>
        </div>

        <form class="grid gap-5 lg:grid-cols-2" @submit.prevent="storeProgram">
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Kategori</label
            >
            <select
              v-model="programForm.program_work_category_id"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="">Pilih Kategori</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <p
              v-if="programForm.errors.program_work_category_id"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ programForm.errors.program_work_category_id }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Judul Program</label
            >
            <input
              v-model="programForm.title"
              type="text"
              placeholder="Contoh: Workshop UI/UX"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p
              v-if="programForm.errors.title"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ programForm.errors.title }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">Jadwal</label>
            <input
              v-model="programForm.schedule_text"
              type="text"
              placeholder="Contoh: Maret 2026"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Divisi Pelaksana</label
            >
            <input
              v-model="programForm.division_name"
              type="text"
              placeholder="Contoh: Divisi Kominfo"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Penanggung Jawab</label
            >
            <input
              v-model="programForm.person_in_charge"
              type="text"
              placeholder="Nama penanggung jawab"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Target Peserta</label
            >
            <input
              v-model="programForm.target"
              type="text"
              placeholder="Contoh: Mahasiswa RPL semester 1-6"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Anggaran</label
            >
            <input
              v-model="programForm.budget"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p class="mt-2 text-xs font-semibold text-slate-500">
              Preview: {{ formatCurrency(programForm.budget) }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Urutan Tampil</label
            >
            <input
              v-model="programForm.sort_order"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Upload Gambar Program</label
            >
            <label
              class="group flex min-h-[13rem] cursor-pointer flex-col items-center justify-center rounded-[1.35rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/40"
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
                Klik untuk memilih gambar
              </p>
              <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
                Format JPG, JPEG, PNG, atau WEBP. Maksimal 4MB.
              </p>
              <input
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="sr-only"
                @change="handleImageFile"
              />
            </label>
            <p
              v-if="programForm.errors.image_file"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ programForm.errors.image_file }}
            </p>
          </div>

          <div>
            <div
              v-if="previewImage"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white p-2 shadow-[0_12px_30px_rgba(2,6,23,0.08)]"
            >
              <img
                :src="previewImage"
                alt="Preview gambar program"
                class="h-[13rem] w-full rounded-[1.05rem] object-cover"
              />
              <p class="px-2 pt-2 text-xs font-bold text-slate-500">
                Preview gambar program
              </p>
            </div>

            <div
              v-else
              class="flex min-h-[13rem] w-full flex-col items-center justify-center rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 px-5 text-center text-sm font-semibold text-slate-400"
            >
              <svg
                class="mb-3 h-8 w-8 text-slate-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                />
              </svg>
              Preview gambar akan tampil di sini
            </div>
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Ringkasan Singkat</label
            >
            <textarea
              v-model="programForm.excerpt"
              rows="3"
              placeholder="Ringkasan singkat untuk kartu program..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Deskripsi Lengkap</label
            >
            <textarea
              v-model="programForm.description"
              rows="5"
              placeholder="Deskripsi lengkap program kerja..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Tujuan Program</label
            >
            <textarea
              v-model="programForm.goals_text"
              rows="5"
              placeholder="Tulis satu tujuan per baris"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p class="mt-2 text-xs font-semibold text-slate-500">
              Satu baris akan menjadi satu poin tujuan.
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Manfaat Program</label
            >
            <textarea
              v-model="programForm.benefits_text"
              rows="5"
              placeholder="Tulis satu manfaat per baris"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
            <p class="mt-2 text-xs font-semibold text-slate-500">
              Satu baris akan menjadi satu poin manfaat.
            </p>
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800"
              >Sumber Dana</label
            >
            <textarea
              v-model="programForm.funding_sources_text"
              rows="3"
              placeholder="Contoh: Kas HMPS&#10;Sponsor&#10;Kontribusi peserta"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div
            class="flex flex-col gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between"
          >
            <div class="flex flex-wrap items-center gap-5">
              <label
                class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
              >
                <input
                  v-model="programForm.is_published"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Published
              </label>

              <label
                class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
              >
                <input
                  v-model="programForm.is_featured"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Featured
              </label>
            </div>

            <button
              type="submit"
              :disabled="programForm.processing"
              class="group relative inline-flex min-h-[50px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                programForm.processing ? "Menyimpan..." : "Tambah Program"
              }}</span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Program List -->
    <section
      class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Data Program
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Daftar Program Kerja
            </h2>
            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Kelola program yang sudah dibuat. Gunakan pencarian dan filter untuk
              mempermudah pengelolaan.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[520px]">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari program, divisi, PJ..."
              class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <select
              v-model="selectedCategoryFilter"
              class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="semua">Semua Kategori</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <span
            class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
          >
            {{ filteredPrograms.length }} hasil
          </span>
          <span
            v-if="searchQuery"
            class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 ring-1 ring-red-100"
          >
            Pencarian: “{{ searchQuery }}”
          </span>
        </div>

        <div class="mt-6 grid gap-5">
          <div
            v-for="program in filteredPrograms"
            :key="program.id"
            class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
          >
            <!-- Read Mode -->
            <div
              v-if="editingProgram?.id !== program.id"
              class="grid gap-4 p-4 lg:grid-cols-[200px_1fr_auto]"
            >
              <div class="relative overflow-hidden rounded-2xl bg-slate-200">
                <img
                  :src="program.image_url"
                  :alt="program.title"
                  class="h-44 w-full object-cover lg:h-full"
                />
                <div
                  class="absolute inset-0 bg-gradient-to-t from-slate-950/45 to-transparent"
                />
                <span
                  class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-800"
                >
                  Program
                </span>
              </div>

              <div class="min-w-0">
                <div class="flex flex-wrap gap-2">
                  <span
                    class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                  >
                    {{
                      program.category_name ||
                      categoryName(program.program_work_category_id) ||
                      "Tanpa Kategori"
                    }}
                  </span>

                  <span
                    class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                    :class="categoryBadgeClass(program)"
                  >
                    {{
                      program.is_featured
                        ? "Featured"
                        : program.is_published
                        ? "Published"
                        : "Draft"
                    }}
                  </span>

                  <span
                    class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                  >
                    Urutan {{ program.sort_order }}
                  </span>
                </div>

                <h3
                  class="mt-3 text-lg font-black leading-snug tracking-[-0.02em] text-slate-950"
                >
                  {{ program.title }}
                </h3>

                <p
                  class="mt-2 line-clamp-2 text-justify text-sm leading-7 text-slate-500"
                >
                  {{ program.excerpt || program.description || "Belum ada deskripsi." }}
                </p>

                <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                  <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">{{
                    program.schedule_text || "Tanpa jadwal"
                  }}</span>
                  <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">{{
                    program.division_name || "Tanpa divisi"
                  }}</span>
                  <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">{{
                    program.budget_text || formatCurrency(program.budget)
                  }}</span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                  <div class="rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-200/70">
                    <p
                      class="text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-400"
                    >
                      PJ
                    </p>
                    <p class="mt-1 line-clamp-1 text-sm font-bold text-slate-800">
                      {{ program.person_in_charge || "-" }}
                    </p>
                  </div>

                  <div class="rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-200/70">
                    <p
                      class="text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-400"
                    >
                      Target
                    </p>
                    <p class="mt-1 line-clamp-1 text-sm font-bold text-slate-800">
                      {{ program.target || "-" }}
                    </p>
                  </div>

                  <div class="rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-200/70">
                    <p
                      class="text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-400"
                    >
                      Poin
                    </p>
                    <p class="mt-1 text-sm font-bold text-slate-800">
                      {{ program.goals?.length || 0 }} tujuan •
                      {{ program.benefits?.length || 0 }} manfaat
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex gap-2 lg:flex-col">
                <button
                  type="button"
                  class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700 lg:flex-none"
                  @click="editProgram(program)"
                >
                  Edit
                </button>

                <button
                  type="button"
                  class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100 lg:flex-none"
                  @click="deleteProgram(program)"
                >
                  Hapus
                </button>
              </div>
            </div>

            <!-- Edit Mode -->
            <form
              v-else
              class="grid gap-5 bg-white p-5 lg:grid-cols-2 lg:p-6"
              @submit.prevent="updateProgram"
            >
              <div
                class="flex flex-col gap-3 border-b border-slate-200 pb-5 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <span
                    class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                  >
                    Mode Edit Program
                  </span>
                  <h3 class="mt-2 text-xl font-black tracking-[-0.03em] text-slate-950">
                    Edit Program Kerja
                  </h3>
                </div>

                <button
                  type="button"
                  class="inline-flex min-h-[42px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                  @click="cancelEditProgram"
                >
                  Batal Edit
                </button>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Kategori</label
                >
                <select
                  v-model="programEditForm.program_work_category_id"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                >
                  <option value="">Pilih Kategori</option>
                  <option
                    v-for="category in categories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Judul Program</label
                >
                <input
                  v-model="programEditForm.title"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Jadwal</label
                >
                <input
                  v-model="programEditForm.schedule_text"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Divisi Pelaksana</label
                >
                <input
                  v-model="programEditForm.division_name"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Penanggung Jawab</label
                >
                <input
                  v-model="programEditForm.person_in_charge"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Target</label
                >
                <input
                  v-model="programEditForm.target"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Anggaran</label
                >
                <input
                  v-model="programEditForm.budget"
                  type="number"
                  min="0"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <p class="mt-2 text-xs font-semibold text-slate-500">
                  Preview: {{ formatCurrency(programEditForm.budget) }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Urutan</label
                >
                <input
                  v-model="programEditForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Ganti Gambar</label
                >
                <label
                  class="group flex min-h-[12rem] cursor-pointer flex-col items-center justify-center rounded-[1.35rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center transition hover:border-red-300 hover:bg-red-50/40"
                >
                  <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm transition group-hover:scale-105"
                  >
                    <svg
                      class="h-5 w-5"
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
                  <p class="mt-3 text-sm font-black text-slate-950">Pilih gambar baru</p>
                  <p class="mt-1 text-xs leading-5 text-slate-500">
                    Kosongkan jika tidak ingin mengganti gambar.
                  </p>
                  <input
                    type="file"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="sr-only"
                    @change="handleEditImageFile"
                  />
                </label>
              </div>

              <div>
                <img
                  v-if="editPreviewImage"
                  :src="editPreviewImage"
                  alt="Preview gambar program"
                  class="h-[12rem] w-full rounded-2xl object-cover shadow-sm"
                />
                <div
                  v-else
                  class="flex h-[12rem] items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-sm font-semibold text-slate-400"
                >
                  Belum ada preview gambar
                </div>
              </div>

              <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Ringkasan</label
                >
                <textarea
                  v-model="programEditForm.excerpt"
                  rows="3"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Deskripsi Lengkap</label
                >
                <textarea
                  v-model="programEditForm.description"
                  rows="5"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Tujuan Program</label
                >
                <textarea
                  v-model="programEditForm.goals_text"
                  rows="5"
                  placeholder="Satu tujuan per baris"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Manfaat Program</label
                >
                <textarea
                  v-model="programEditForm.benefits_text"
                  rows="5"
                  placeholder="Satu manfaat per baris"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Sumber Dana</label
                >
                <textarea
                  v-model="programEditForm.funding_sources_text"
                  rows="3"
                  placeholder="Satu sumber dana per baris"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div
                class="flex flex-col gap-3 border-t border-slate-200 pt-5 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between"
              >
                <div class="flex flex-wrap items-center gap-5">
                  <label
                    class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
                  >
                    <input
                      v-model="programEditForm.is_published"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                    />
                    Published
                  </label>

                  <label
                    class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
                  >
                    <input
                      v-model="programEditForm.is_featured"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                    />
                    Featured
                  </label>
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                  <button
                    type="button"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditProgram"
                  >
                    Batal
                  </button>

                  <button
                    type="submit"
                    :disabled="programEditForm.processing"
                    class="inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.18)] transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {{ programEditForm.processing ? "Menyimpan..." : "Simpan Perubahan" }}
                  </button>
                </div>
              </div>
            </form>
          </div>

          <div
            v-if="!filteredPrograms.length"
            class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
          >
            <h3 class="text-xl font-black text-slate-950">
              Program kerja tidak ditemukan
            </h3>
            <p
              class="mx-auto mt-3 max-w-xl text-justify text-sm leading-7 text-slate-500"
            >
              Tambahkan program baru atau ubah filter pencarian agar data dapat
              ditampilkan kembali.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
@media (max-width: 639px) {
  .custom-layanan-jasa-mobile-page,
  .custom-layanan-jasa-mobile-page * {
    box-sizing: border-box;
  }

  .custom-layanan-jasa-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-layanan-jasa-mobile-page :is(section, article, aside, form, div) {
    min-width: 0;
  }

  .custom-layanan-jasa-mobile-page :is(input, select, textarea, button, a, img, label) {
    max-width: 100%;
  }

  .custom-layanan-jasa-mobile-page p {
    text-align: left !important;
  }

  .custom-layanan-jasa-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-layanan-jasa-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-layanan-jasa-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-layanan-jasa-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-layanan-jasa-mobile-page > section:first-of-type .relative.z-10.flex {
    gap: 1rem !important;
  }

  .custom-layanan-jasa-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-layanan-jasa-mobile-page > section:nth-of-type(2) > div {
    border-radius: 1.1rem !important;
    padding: 0.85rem !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.055) !important;
  }

  .custom-layanan-jasa-mobile-page > section:nth-of-type(2) h3 {
    font-size: 1.3rem !important;
    line-height: 1.05 !important;
  }

  .custom-layanan-jasa-mobile-page > section:nth-of-type(2) p {
    line-height: 1.45 !important;
  }

  .custom-layanan-jasa-mobile-page > section:nth-of-type(2) .mb-4.flex.h-11.w-11 {
    width: 2.45rem !important;
    height: 2.45rem !important;
    border-radius: 0.9rem !important;
    margin-bottom: 0.75rem !important;
  }

  .custom-layanan-jasa-mobile-page > section:nth-of-type(n + 3) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-layanan-jasa-mobile-page > section > div:not(.h-1\.5),
  .custom-layanan-jasa-mobile-page > section > form,
  .custom-layanan-jasa-mobile-page aside,
  .custom-layanan-jasa-mobile-page form.grid {
    padding: 1rem !important;
  }

  .custom-layanan-jasa-mobile-page .rounded-\[1\.35rem\],
  .custom-layanan-jasa-mobile-page .rounded-\[1\.45rem\],
  .custom-layanan-jasa-mobile-page .rounded-\[1\.5rem\],
  .custom-layanan-jasa-mobile-page .rounded-\[1\.55rem\],
  .custom-layanan-jasa-mobile-page .rounded-\[1\.75rem\],
  .custom-layanan-jasa-mobile-page .rounded-\[1\.8rem\],
  .custom-layanan-jasa-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-layanan-jasa-mobile-page label,
  .custom-layanan-jasa-mobile-page .text-sm.font-bold,
  .custom-layanan-jasa-mobile-page .text-sm.font-black,
  .custom-layanan-jasa-mobile-page .text-sm.font-extrabold {
    font-size: 0.82rem !important;
  }

  .custom-layanan-jasa-mobile-page input:not([type="checkbox"]):not([type="file"]),
  .custom-layanan-jasa-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-layanan-jasa-mobile-page input[type="file"] {
    width: 100% !important;
    font-size: 0.78rem !important;
  }

  .custom-layanan-jasa-mobile-page textarea {
    min-height: 112px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-layanan-jasa-mobile-page button,
  .custom-layanan-jasa-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-layanan-jasa-mobile-page button[type="submit"],
  .custom-layanan-jasa-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-layanan-jasa-mobile-page .sticky.bottom-4 {
    position: sticky !important;
    bottom: 0.75rem !important;
    border-radius: 1.1rem !important;
    padding: 0.75rem !important;
  }

  .custom-layanan-jasa-mobile-page .sticky.top-6 {
    position: static !important;
  }

  .custom-layanan-jasa-mobile-page .grid.xl\:grid-cols-\[0\.34fr_0\.66fr\],
  .custom-layanan-jasa-mobile-page .grid.xl\:grid-cols-\[0\.42fr_0\.58fr\],
  .custom-layanan-jasa-mobile-page .grid.lg\:grid-cols-2,
  .custom-layanan-jasa-mobile-page .grid.lg\:grid-cols-3,
  .custom-layanan-jasa-mobile-page .grid.sm\:grid-cols-2,
  .custom-layanan-jasa-mobile-page .grid.sm\:grid-cols-3,
  .custom-layanan-jasa-mobile-page .grid.md\:grid-cols-2,
  .custom-layanan-jasa-mobile-page .grid.xl\:grid-cols-3,
  .custom-layanan-jasa-mobile-page .grid.xl\:grid-cols-4,
  .custom-layanan-jasa-mobile-page .grid.\32 xl\:grid-cols-2,
  .custom-layanan-jasa-mobile-page .grid.sm\:grid-cols-\[7\.5rem_1fr\],
  .custom-layanan-jasa-mobile-page
    .grid.sm\:grid-cols-\[minmax\(4\.5rem\,0\.28fr\)_minmax\(0\,1fr\)\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-layanan-jasa-mobile-page .lg\:col-span-2,
  .custom-layanan-jasa-mobile-page .md\:col-span-2 {
    grid-column: auto !important;
  }

  .custom-layanan-jasa-mobile-page .grid.grid-cols-2.gap-2,
  .custom-layanan-jasa-mobile-page .grid.grid-cols-2.gap-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-layanan-jasa-mobile-page .grid.grid-cols-2.gap-2.sm\:flex,
  .custom-layanan-jasa-mobile-page .grid.grid-cols-2.gap-2.sm\:flex.sm\:flex-wrap {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-layanan-jasa-mobile-page .group.flex.min-h-\[13rem\],
  .custom-layanan-jasa-mobile-page .group.flex.min-h-\[12rem\] {
    min-height: 10.5rem !important;
    padding: 1.25rem !important;
    border-radius: 1.05rem !important;
  }

  .custom-layanan-jasa-mobile-page .h-14.w-14,
  .custom-layanan-jasa-mobile-page .h-12.w-12,
  .custom-layanan-jasa-mobile-page .h-11.w-11 {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-layanan-jasa-mobile-page .min-h-\[8\.5rem\],
  .custom-layanan-jasa-mobile-page .min-h-\[9rem\] {
    min-height: 7.5rem !important;
  }

  .custom-layanan-jasa-mobile-page .h-48,
  .custom-layanan-jasa-mobile-page .h-44,
  .custom-layanan-jasa-mobile-page .h-40 {
    height: 11rem !important;
  }

  .custom-layanan-jasa-mobile-page .flex.justify-end,
  .custom-layanan-jasa-mobile-page .flex.flex-col-reverse,
  .custom-layanan-jasa-mobile-page .flex.shrink-0.gap-2,
  .custom-layanan-jasa-mobile-page .flex.gap-2.lg\:flex-col,
  .custom-layanan-jasa-mobile-page .flex.flex-col.gap-3.rounded-\[1\.35rem\],
  .custom-layanan-jasa-mobile-page .flex.flex-col.gap-3.overflow-hidden {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
    align-items: stretch !important;
  }

  .custom-layanan-jasa-mobile-page .flex.shrink-0.gap-2,
  .custom-layanan-jasa-mobile-page .flex.gap-2.lg\:flex-col {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    width: 100% !important;
  }

  .custom-layanan-jasa-mobile-page .line-clamp-2,
  .custom-layanan-jasa-mobile-page .line-clamp-3 {
    display: block !important;
    -webkit-line-clamp: unset !important;
    overflow: visible !important;
  }

  .custom-layanan-jasa-mobile-page .truncate {
    min-width: 0;
  }

  .custom-layanan-jasa-mobile-page .break-all,
  .custom-layanan-jasa-mobile-page .break-words,
  .custom-layanan-jasa-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-layanan-jasa-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-layanan-jasa-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-layanan-jasa-mobile-page .flex.flex-wrap.items-center.gap-2 > span,
  .custom-layanan-jasa-mobile-page .mt-3.flex.flex-wrap.gap-2 > span {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-layanan-jasa-mobile-page .w-fit,
  .custom-layanan-jasa-mobile-page .inline-flex.w-fit {
    max-width: 100%;
  }

  .custom-layanan-jasa-mobile-page .sm\:w-36,
  .custom-layanan-jasa-mobile-page .w-28,
  .custom-layanan-jasa-mobile-page .w-24 {
    width: 100% !important;
  }

  .custom-layanan-jasa-mobile-page .p-5,
  .custom-layanan-jasa-mobile-page .p-4,
  .custom-layanan-jasa-mobile-page .sm\:p-6,
  .custom-layanan-jasa-mobile-page .lg\:p-7 {
    padding: 1rem !important;
  }

  .custom-layanan-jasa-mobile-page .mt-8,
  .custom-layanan-jasa-mobile-page .mt-7,
  .custom-layanan-jasa-mobile-page .mt-6,
  .custom-layanan-jasa-mobile-page .mt-5 {
    margin-top: 1.25rem !important;
  }

  .custom-layanan-jasa-mobile-page .mb-7,
  .custom-layanan-jasa-mobile-page .mb-6,
  .custom-layanan-jasa-mobile-page .mb-5 {
    margin-bottom: 1.25rem !important;
  }

  .custom-layanan-jasa-mobile-page .gap-5,
  .custom-layanan-jasa-mobile-page .gap-6 {
    gap: 1rem !important;
  }

  .custom-layanan-jasa-mobile-page .gap-8 {
    gap: 1.25rem !important;
  }

  .custom-layanan-jasa-mobile-page .shadow-\[0_16px_44px_rgba\(15\,23\,42\,0\.06\)\],
  .custom-layanan-jasa-mobile-page .shadow-\[0_20px_60px_rgba\(15\,23\,42\,0\.08\)\],
  .custom-layanan-jasa-mobile-page .shadow-\[0_14px_36px_rgba\(15\,23\,42\,0\.05\)\] {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055) !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-layanan-jasa-mobile-page *,
  .custom-layanan-jasa-mobile-page *::before,
  .custom-layanan-jasa-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

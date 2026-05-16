<script setup>
import { computed, onUnmounted, ref } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  documentations: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const ADMIN_DOCUMENTATION_ENDPOINT = "/admin/dokumentasi";
const documentationEndpoint = (id) => `${ADMIN_DOCUMENTATION_ENDPOINT}/${id}`;
const documentationImageEndpoint = (id) => `${ADMIN_DOCUMENTATION_ENDPOINT}/images/${id}`;

const editingDocumentation = ref(null);

const previewCover = ref(null);
const previewGallery = ref([]);

const editPreviewCover = ref(null);
const editPreviewGallery = ref([]);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const currentYear = new Date().getFullYear();
const documentations = computed(() =>
  Array.isArray(props.documentations) ? props.documentations : []
);

const featuredSizeOptions = [
  {
    value: "large",
    label: "Besar",
    helper: "Foto tampil dominan di hero section.",
  },
  {
    value: "medium",
    label: "Sedang",
    helper: "Foto tampil seimbang bersama dokumentasi lain.",
  },
  {
    value: "small",
    label: "Kecil",
    helper: "Foto tampil ringkas sebagai pendukung.",
  },
];

const featuredSizeLabel = (size) => {
  return featuredSizeOptions.find((item) => item.value === size)?.label || "Sedang";
};

const stats = computed(() => {
  const total = documentations.value.length;
  const published = documentations.value.filter((item) => item.is_published).length;
  const featured = documentations.value.filter((item) => item.is_featured).length;

  const galleryImages = documentations.value.reduce(
    (totalImages, item) => totalImages + (item.images?.length || 0),
    0
  );

  return [
    {
      label: "Total Dokumentasi",
      value: total,
      helper: "Semua arsip kegiatan",
      icon: "archive",
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
      helper: "Dokumentasi unggulan",
      icon: "star",
    },
    {
      label: "Foto Galeri",
      value: galleryImages,
      helper: "Total foto tambahan",
      icon: "image",
    },
  ];
});

const form = useForm({
  title: "",
  category: "kegiatan",
  year: currentYear,
  event_date: "",
  location: "",
  description: "",
  cover_file: null,
  gallery_files: [],
  is_featured: false,
  featured_size: "medium",
  is_published: true,
  sort_order: 0,
});

const editForm = useForm({
  title: "",
  category: "kegiatan",
  year: currentYear,
  event_date: "",
  location: "",
  description: "",
  cover_file: null,
  gallery_files: [],
  is_featured: false,
  featured_size: "medium",
  is_published: true,
  sort_order: 0,
});

const formatFileSize = (size) => {
  if (!size) return "0 KB";

  const kb = size / 1024;

  if (kb < 1024) {
    return `${kb.toFixed(1)} KB`;
  }

  return `${(kb / 1024).toFixed(2)} MB`;
};

const revokePreview = (preview) => {
  if (preview && String(preview).startsWith("blob:")) {
    URL.revokeObjectURL(preview);
  }
};

const revokePreviewList = (items) => {
  items.forEach((item) => {
    if (item?.url) {
      revokePreview(item.url);
    }
  });
};

const makeFilePreview = (file) => ({
  name: file.name,
  size: file.size,
  url: URL.createObjectURL(file),
});

const resetForm = () => {
  revokePreview(previewCover.value);
  revokePreviewList(previewGallery.value);

  form.reset();
  form.category = "kegiatan";
  form.year = currentYear;
  form.event_date = "";
  form.cover_file = null;
  form.gallery_files = [];
  form.is_featured = false;
  form.featured_size = "medium";
  form.is_published = true;
  form.sort_order = 0;

  previewCover.value = null;
  previewGallery.value = [];
};

const handleCoverFile = (event) => {
  const file = event.target.files[0] || null;

  revokePreview(previewCover.value);

  form.cover_file = file;
  previewCover.value = file ? URL.createObjectURL(file) : null;
};

const handleGalleryFiles = (event) => {
  const files = Array.from(event.target.files || []);

  revokePreviewList(previewGallery.value);

  form.gallery_files = files;
  previewGallery.value = files.map((file) => makeFilePreview(file));
};

const handleEditCoverFile = (event) => {
  const file = event.target.files[0] || null;

  revokePreview(editPreviewCover.value);

  editForm.cover_file = file;
  editPreviewCover.value = file
    ? URL.createObjectURL(file)
    : editingDocumentation.value?.cover_url || null;
};

const handleEditGalleryFiles = (event) => {
  const files = Array.from(event.target.files || []);

  revokePreviewList(editPreviewGallery.value);

  editForm.gallery_files = files;
  editPreviewGallery.value = files.map((file) => makeFilePreview(file));
};

const storeDocumentation = () => {
  form.post(ADMIN_DOCUMENTATION_ENDPOINT, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      resetForm();
    },
  });
};

const editDocumentation = (item) => {
  revokePreview(editPreviewCover.value);
  revokePreviewList(editPreviewGallery.value);

  editingDocumentation.value = item;

  editForm.title = item.title || "";
  editForm.category = item.category || "kegiatan";
  editForm.year = item.year || currentYear;
  editForm.event_date = item.event_date || "";
  editForm.location = item.location || "";
  editForm.description = item.description || "";
  editForm.cover_file = null;
  editForm.gallery_files = [];
  editForm.is_featured = Boolean(item.is_featured);
  editForm.featured_size = item.featured_size || "medium";
  editForm.is_published = Boolean(item.is_published);
  editForm.sort_order = Number(item.sort_order || 0);

  editPreviewCover.value = item.cover_url || null;
  editPreviewGallery.value = [];
};

const cancelEdit = () => {
  revokePreview(editPreviewCover.value);
  revokePreviewList(editPreviewGallery.value);

  editingDocumentation.value = null;
  editForm.reset();

  editPreviewCover.value = null;
  editPreviewGallery.value = [];
};

const updateDocumentation = () => {
  if (!editingDocumentation.value || editForm.processing) return;

  editForm.post(documentationEndpoint(editingDocumentation.value.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      cancelEdit();
    },
  });
};

const deleteDocumentation = (item) => {
  if (!confirm(`Hapus dokumentasi "${item.title}"?`)) return;

  router.delete(documentationEndpoint(item.id), {
    preserveScroll: true,
  });
};

const deleteGalleryImage = (image) => {
  if (!confirm("Hapus foto galeri ini?")) return;

  router.delete(documentationImageEndpoint(image.id), {
    preserveScroll: true,
  });
};

const categoryLabel = (category) => {
  if (category === "proker") return "Program Kerja";
  if (category === "kegiatan") return "Kegiatan";
  return "Lainnya";
};

const categoryClass = (category) => {
  if (category === "proker") {
    return "border-blue-200 bg-blue-50 text-blue-700";
  }

  if (category === "kegiatan") {
    return "border-red-200 bg-red-50 text-red-700";
  }

  return "border-violet-200 bg-violet-50 text-violet-700";
};

const statusClass = (isPublished) =>
  isPublished
    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
    : "border-slate-200 bg-slate-100 text-slate-600";

onUnmounted(() => {
  revokePreview(previewCover.value);
  revokePreview(editPreviewCover.value);
  revokePreviewList(previewGallery.value);
  revokePreviewList(editPreviewGallery.value);
});
</script>

<template>
  <Head title="Custom Dokumentasi" />

  <div
    class="custom-dokumentasi-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8"
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
            Admin / Custom Dokumentasi
          </div>

          <h1
            class="max-w-3xl text-[2rem] font-black leading-[1.08] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.15rem]"
          >
            Kelola Dokumentasi
            <span class="block text-red-300">HMPS RPL</span>
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300 sm:text-base"
          >
            Tambah, edit, dan kelola dokumentasi kegiatan atau program kerja lengkap
            dengan cover utama, galeri foto, kategori, status publish, dan penanda
            featured.
          </p>
        </div>

        <a
          href="/dokumentasi"
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
          <span class="relative z-10">Preview Dokumentasi</span>
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
              v-if="item.icon === 'archive'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M4 7h16M5 7l1 12h12l1-12M9 11h6"
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
              d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
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

    <!-- Add Form -->
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
              Tambah Data Baru
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Tambah Dokumentasi Baru
            </h2>

            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Satu dokumentasi dapat memiliki satu cover utama dan banyak foto galeri.
              Foto galeri akan tampil sebagai kumpulan gambar pada detail dokumentasi
              user.
            </p>
          </div>

          <span
            class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
          >
            PNG / JPG / WEBP
          </span>
        </div>

        <form class="grid gap-5 lg:grid-cols-2" @submit.prevent="storeDocumentation">
          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Judul Dokumentasi
              <span class="text-red-600">*</span>
            </label>

            <input
              v-model="form.title"
              type="text"
              placeholder="Contoh: Workshop UI/UX HMPS RPL"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p v-if="form.errors.title" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.title }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Kategori
              <span class="text-red-600">*</span>
            </label>

            <select
              v-model="form.category"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="kegiatan">Kegiatan</option>
              <option value="proker">Program Kerja</option>
              <option value="lainnya">Lainnya</option>
            </select>

            <p v-if="form.errors.category" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.category }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Tahun
              <span class="text-red-600">*</span>
            </label>

            <input
              v-model="form.year"
              type="number"
              min="2000"
              max="2100"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p v-if="form.errors.year" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.year }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Tanggal Kegiatan
            </label>

            <input
              v-model="form.event_date"
              type="date"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p v-if="form.errors.event_date" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.event_date }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Lokasi
            </label>

            <input
              v-model="form.location"
              type="text"
              placeholder="Contoh: Aula Kampus"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p v-if="form.errors.location" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.location }}
            </p>
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Deskripsi
              <span class="text-red-600">*</span>
            </label>

            <textarea
              v-model="form.description"
              rows="5"
              placeholder="Tuliskan deskripsi singkat dokumentasi..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p v-if="form.errors.description" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.description }}
            </p>
          </div>

          <!-- Cover -->
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Cover Utama
            </label>

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

              <p class="mt-4 text-sm font-black text-slate-950">Pilih cover utama</p>
              <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
                Cover utama akan tampil sebagai gambar utama kartu dokumentasi.
              </p>

              <input
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="sr-only"
                @change="handleCoverFile"
              />
            </label>

            <p v-if="form.errors.cover_file" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.cover_file }}
            </p>

            <div
              v-if="previewCover"
              class="mt-4 overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white p-2 shadow-[0_12px_30px_rgba(2,6,23,0.08)]"
            >
              <img
                :src="previewCover"
                alt="Preview cover"
                class="h-48 w-full rounded-[1rem] object-cover"
              />
              <p class="px-2 pt-2 text-xs font-bold text-slate-500">
                Preview cover utama
              </p>
            </div>
          </div>

          <!-- Multiple Gallery -->
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Foto Galeri Tambahan
            </label>

            <label
              class="group flex min-h-[13rem] cursor-pointer flex-col items-center justify-center rounded-[1.35rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-slate-500 hover:bg-slate-100/70"
            >
              <div
                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-950 shadow-sm transition duration-300 group-hover:scale-105"
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
                    d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                  />
                </svg>
              </div>

              <p class="mt-4 text-sm font-black text-slate-950">
                Pilih banyak foto galeri
              </p>
              <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
                Bisa upload banyak foto sekaligus. Maksimal 4MB per foto.
              </p>

              <input
                type="file"
                multiple
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="sr-only"
                @change="handleGalleryFiles"
              />
            </label>

            <p
              v-if="form.errors['gallery_files.0']"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ form.errors["gallery_files.0"] }}
            </p>
          </div>

          <div v-if="previewGallery.length" class="lg:col-span-2">
            <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4">
              <div
                class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <h3 class="text-base font-black text-slate-950">Preview Foto Galeri</h3>
                  <p class="mt-1 text-xs font-semibold text-slate-500">
                    {{ previewGallery.length }} foto dipilih untuk dokumentasi ini.
                  </p>
                </div>

                <span
                  class="w-fit rounded-full bg-red-50 px-3 py-1 text-xs font-black uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                >
                  Banyak Foto
                </span>
              </div>

              <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div
                  v-for="(image, index) in previewGallery"
                  :key="`${image.name}-${index}`"
                  class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                  <img
                    :src="image.url"
                    :alt="image.name"
                    class="h-28 w-full object-cover"
                  />

                  <div class="p-2">
                    <p class="truncate text-xs font-black text-slate-700">
                      {{ image.name }}
                    </p>
                    <p class="mt-1 text-[0.68rem] font-semibold text-slate-400">
                      {{ formatFileSize(image.size) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Urutan Tampil
            </label>

            <input
              v-model="form.sort_order"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p class="mt-2 text-xs font-semibold text-slate-500">
              Angka kecil akan tampil lebih awal.
            </p>

            <p v-if="form.errors.sort_order" class="mt-2 text-xs font-bold text-red-600">
              {{ form.errors.sort_order }}
            </p>
          </div>

          <div
            class="flex flex-col justify-center gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4"
          >
            <div
              class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                <input
                  v-model="form.is_published"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Published
              </label>

              <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                <input
                  v-model="form.is_featured"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Featured
              </label>
            </div>

            <div
              v-if="form.is_featured"
              class="rounded-2xl border border-amber-200 bg-amber-50 p-3"
            >
              <label
                class="mb-2 block text-xs font-black uppercase tracking-[0.1em] text-amber-700"
              >
                Ukuran Foto Hero
              </label>
              <select
                v-model="form.featured_size"
                class="h-11 w-full rounded-xl border border-amber-200 bg-white px-3 text-sm font-black text-slate-800 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="option in featuredSizeOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <p class="mt-2 text-xs font-semibold leading-5 text-amber-700">
                Ukuran hanya mengatur besar kartu di hero section. Maksimal 4 dokumentasi
                Featured.
              </p>
              <p
                v-if="form.errors.featured_size"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ form.errors.featured_size }}
              </p>
              <p
                v-if="form.errors.is_featured"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ form.errors.is_featured }}
              </p>
            </div>
          </div>

          <div class="flex justify-end lg:col-span-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />

              <svg
                v-if="form.processing"
                class="relative z-10 h-4 w-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                />
              </svg>

              <span class="relative z-10">
                {{ form.processing ? "Menyimpan..." : "Tambah Dokumentasi" }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- List -->
    <section
      class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Data Dokumentasi
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Daftar Dokumentasi
            </h2>

            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Kelola data dokumentasi yang sudah ditambahkan. Anda dapat mengubah detail
              dokumentasi, mengganti cover, menambah foto galeri baru, atau menghapus foto
              galeri lama.
            </p>
          </div>

          <span
            class="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
          >
            {{ documentations.length }} Data
          </span>
        </div>

        <div class="grid gap-5">
          <div
            v-for="item in documentations"
            :key="item.id"
            class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 shadow-sm transition duration-300 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
          >
            <!-- Read Mode -->
            <div
              v-if="editingDocumentation?.id !== item.id"
              class="grid gap-4 p-4 lg:grid-cols-[190px_1fr_auto] lg:items-start"
            >
              <div class="relative overflow-hidden rounded-2xl bg-slate-200">
                <img
                  :src="item.cover_url"
                  :alt="item.title"
                  class="h-44 w-full object-cover lg:h-36"
                />
                <div
                  class="absolute inset-0 bg-gradient-to-t from-slate-950/45 to-transparent"
                />
                <span
                  class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-800"
                >
                  Cover
                </span>
              </div>

              <div class="min-w-0">
                <div class="flex flex-wrap gap-2">
                  <span
                    class="rounded-full border px-3 py-1 text-[0.7rem] font-black uppercase tracking-[0.08em]"
                    :class="categoryClass(item.category)"
                  >
                    {{ categoryLabel(item.category) }}
                  </span>

                  <span
                    class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.7rem] font-black uppercase tracking-[0.08em] text-slate-600"
                  >
                    {{ item.year }}
                  </span>

                  <span
                    class="rounded-full border px-3 py-1 text-[0.7rem] font-black uppercase tracking-[0.08em]"
                    :class="statusClass(item.is_published)"
                  >
                    {{ item.is_published ? "Published" : "Draft" }}
                  </span>

                  <span
                    v-if="item.is_featured"
                    class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[0.7rem] font-black uppercase tracking-[0.08em] text-amber-700"
                  >
                    Featured
                  </span>
                  <span
                    v-if="item.is_featured"
                    class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.7rem] font-black uppercase tracking-[0.08em] text-slate-600"
                  >
                    {{ featuredSizeLabel(item.featured_size) }}
                  </span>
                </div>

                <h3
                  class="mt-3 text-xl font-black leading-snug tracking-[-0.02em] text-slate-950"
                >
                  {{ item.title }}
                </h3>

                <p
                  class="mt-2 line-clamp-2 text-justify text-sm leading-7 text-slate-500"
                >
                  {{ item.description }}
                </p>

                <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                  <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">
                    {{ item.date_label || "Tanpa tanggal" }}
                  </span>
                  <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">
                    {{ item.location || "Tanpa lokasi" }}
                  </span>
                  <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200">
                    {{ item.images?.length || 0 }} foto galeri
                  </span>
                </div>

                <div
                  v-if="item.images?.length"
                  class="mt-4 flex gap-2 overflow-x-auto pb-1 documentation-scroll"
                >
                  <img
                    v-for="image in item.images.slice(0, 8)"
                    :key="image.id"
                    :src="image.image_url"
                    :alt="image.caption || item.title"
                    class="h-16 w-24 shrink-0 rounded-xl border border-white object-cover shadow-sm"
                  />

                  <div
                    v-if="item.images.length > 8"
                    class="flex h-16 w-24 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-xs font-black text-slate-600"
                  >
                    +{{ item.images.length - 8 }}
                  </div>
                </div>
              </div>

              <div class="flex gap-2 lg:flex-col">
                <button
                  type="button"
                  class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700 lg:flex-none"
                  @click="editDocumentation(item)"
                >
                  Edit
                </button>

                <button
                  type="button"
                  class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100 lg:flex-none"
                  @click="deleteDocumentation(item)"
                >
                  Hapus
                </button>
              </div>
            </div>

            <!-- Edit Mode -->
            <form
              v-else
              class="grid gap-5 bg-white p-5 lg:grid-cols-2 lg:p-6"
              @submit.prevent="updateDocumentation"
            >
              <div
                class="flex flex-col gap-3 border-b border-slate-200 pb-5 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <span
                    class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                  >
                    Mode Edit
                  </span>
                  <h3 class="mt-2 text-xl font-black tracking-[-0.03em] text-slate-950">
                    Edit Dokumentasi
                  </h3>
                </div>

                <button
                  type="button"
                  class="inline-flex min-h-[42px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                  @click="cancelEdit"
                >
                  Batal Edit
                </button>
              </div>

              <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Judul</label
                >

                <input
                  v-model="editForm.title"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="editForm.errors.title"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ editForm.errors.title }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Kategori</label
                >

                <select
                  v-model="editForm.category"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                >
                  <option value="kegiatan">Kegiatan</option>
                  <option value="proker">Program Kerja</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Tahun</label
                >

                <input
                  v-model="editForm.year"
                  type="number"
                  min="2000"
                  max="2100"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Tanggal</label
                >

                <input
                  v-model="editForm.event_date"
                  type="date"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Lokasi</label
                >

                <input
                  v-model="editForm.location"
                  type="text"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Deskripsi</label
                >

                <textarea
                  v-model="editForm.description"
                  rows="5"
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Ganti Cover</label
                >

                <label
                  class="group flex min-h-[12rem] cursor-pointer flex-col items-center justify-center rounded-[1.35rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/40"
                >
                  <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm transition duration-300 group-hover:scale-105"
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
                  <p class="mt-3 text-sm font-black text-slate-950">Pilih cover baru</p>
                  <p class="mt-1 text-xs leading-5 text-slate-500">
                    Kosongkan jika tidak ingin mengganti cover.
                  </p>

                  <input
                    type="file"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="sr-only"
                    @change="handleEditCoverFile"
                  />
                </label>

                <img
                  v-if="editPreviewCover"
                  :src="editPreviewCover"
                  alt="Preview cover"
                  class="mt-4 h-44 w-full rounded-2xl object-cover shadow-sm"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Tambah Banyak Foto Galeri</label
                >

                <label
                  class="group flex min-h-[12rem] cursor-pointer flex-col items-center justify-center rounded-[1.35rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-slate-500 hover:bg-slate-100/70"
                >
                  <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-950 shadow-sm transition duration-300 group-hover:scale-105"
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
                        d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                      />
                    </svg>
                  </div>
                  <p class="mt-3 text-sm font-black text-slate-950">
                    Pilih foto tambahan
                  </p>
                  <p class="mt-1 text-xs leading-5 text-slate-500">
                    Foto akan ditambahkan ke galeri yang sudah ada.
                  </p>

                  <input
                    type="file"
                    multiple
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="sr-only"
                    @change="handleEditGalleryFiles"
                  />
                </label>

                <p
                  v-if="editForm.errors['gallery_files.0']"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ editForm.errors["gallery_files.0"] }}
                </p>
              </div>

              <div v-if="editPreviewGallery.length" class="lg:col-span-2">
                <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4">
                  <div
                    class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div>
                      <h3 class="text-base font-black text-slate-950">
                        Preview Foto Baru
                      </h3>
                      <p class="mt-1 text-xs font-semibold text-slate-500">
                        {{ editPreviewGallery.length }} foto baru akan ditambahkan.
                      </p>
                    </div>

                    <span
                      class="w-fit rounded-full bg-slate-950 px-3 py-1 text-xs font-black uppercase tracking-[0.08em] text-white"
                    >
                      Akan Ditambahkan
                    </span>
                  </div>

                  <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <div
                      v-for="(image, index) in editPreviewGallery"
                      :key="`${image.name}-${index}`"
                      class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                    >
                      <img
                        :src="image.url"
                        :alt="image.name"
                        class="h-28 w-full object-cover"
                      />

                      <div class="p-2">
                        <p class="truncate text-xs font-black text-slate-700">
                          {{ image.name }}
                        </p>
                        <p class="mt-1 text-[0.68rem] font-semibold text-slate-400">
                          {{ formatFileSize(image.size) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="lg:col-span-2">
                <label class="mb-3 block text-sm font-extrabold text-slate-800"
                  >Foto Galeri Saat Ini</label
                >

                <div
                  v-if="item.images?.length"
                  class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"
                >
                  <div
                    v-for="image in item.images"
                    :key="image.id"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                  >
                    <img
                      :src="image.image_url"
                      :alt="image.caption || item.title"
                      class="h-28 w-full object-cover transition duration-300 group-hover:scale-105"
                    />

                    <button
                      type="button"
                      class="absolute right-2 top-2 rounded-xl bg-red-600 px-2 py-1 text-[0.65rem] font-black text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700"
                      @click="deleteGalleryImage(image)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>

                <p
                  v-else
                  class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm font-semibold text-slate-500"
                >
                  Belum ada foto galeri tambahan.
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Urutan</label
                >

                <input
                  v-model="editForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div
                class="flex flex-col justify-center gap-3 rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4"
              >
                <div
                  class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                  <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                    <input
                      v-model="editForm.is_published"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                    />
                    Published
                  </label>

                  <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                    <input
                      v-model="editForm.is_featured"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                    />
                    Featured
                  </label>
                </div>

                <div
                  v-if="editForm.is_featured"
                  class="rounded-2xl border border-amber-200 bg-amber-50 p-3"
                >
                  <label
                    class="mb-2 block text-xs font-black uppercase tracking-[0.1em] text-amber-700"
                  >
                    Ukuran Foto Hero
                  </label>
                  <select
                    v-model="editForm.featured_size"
                    class="h-11 w-full rounded-xl border border-amber-200 bg-white px-3 text-sm font-black text-slate-800 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="option in featuredSizeOptions"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>
                  <p class="mt-2 text-xs font-semibold leading-5 text-amber-700">
                    Ukuran hanya mengatur besar kartu di hero section. Maksimal 4
                    dokumentasi Featured.
                  </p>
                  <p
                    v-if="editForm.errors.featured_size"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ editForm.errors.featured_size }}
                  </p>
                  <p
                    v-if="editForm.errors.is_featured"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ editForm.errors.is_featured }}
                  </p>
                </div>
              </div>

              <div
                class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 lg:col-span-2 sm:flex-row sm:justify-end"
              >
                <button
                  type="button"
                  class="inline-flex min-h-[48px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="editForm.processing"
                  @click="cancelEdit"
                >
                  Batal
                </button>

                <button
                  type="submit"
                  :disabled="editForm.processing"
                  class="group relative inline-flex min-h-[48px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-5 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[1px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                >
                  <span
                    class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                  />

                  <svg
                    v-if="editForm.processing"
                    class="relative z-10 h-4 w-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    />
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                    />
                  </svg>

                  <span class="relative z-10">
                    {{ editForm.processing ? "Menyimpan..." : "Simpan Perubahan" }}
                  </span>
                </button>
              </div>
            </form>
          </div>

          <div
            v-if="!documentations.length"
            class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
          >
            <div
              class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm"
            >
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                />
              </svg>
            </div>

            <h3 class="mt-5 text-xl font-black text-slate-950">Belum ada dokumentasi</h3>

            <p
              class="mx-auto mt-3 max-w-xl text-justify text-sm leading-7 text-slate-500"
            >
              Tambahkan dokumentasi pertama melalui form di atas agar halaman dokumentasi
              user mulai memiliki arsip kegiatan.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.documentation-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(148, 163, 184, 0.5) rgba(226, 232, 240, 0.7);
}

.documentation-scroll::-webkit-scrollbar {
  height: 7px;
}

.documentation-scroll::-webkit-scrollbar-track {
  background: rgba(226, 232, 240, 0.7);
  border-radius: 999px;
}

.documentation-scroll::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 999px;
}

.documentation-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(100, 116, 139, 0.7);
}
</style>

<style scoped>
@media (max-width: 639px) {
  .custom-dokumentasi-mobile-page,
  .custom-dokumentasi-mobile-page * {
    box-sizing: border-box;
  }

  .custom-dokumentasi-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-dokumentasi-mobile-page :is(section, article, form, div) {
    min-width: 0;
  }

  .custom-dokumentasi-mobile-page :is(input, select, textarea, button, a, img) {
    max-width: 100%;
  }

  .custom-dokumentasi-mobile-page p {
    text-align: left !important;
  }

  .custom-dokumentasi-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-dokumentasi-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-dokumentasi-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-dokumentasi-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-dokumentasi-mobile-page > section:first-of-type .relative.z-10.flex {
    gap: 1rem !important;
  }

  .custom-dokumentasi-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-dokumentasi-mobile-page > section:nth-of-type(2) > div {
    border-radius: 1.1rem !important;
    padding: 0.85rem !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.055) !important;
  }

  .custom-dokumentasi-mobile-page > section:nth-of-type(2) h3 {
    font-size: 1.3rem !important;
    line-height: 1.05 !important;
  }

  .custom-dokumentasi-mobile-page > section:nth-of-type(2) .mb-4.flex.h-11.w-11 {
    width: 2.45rem !important;
    height: 2.45rem !important;
    border-radius: 0.9rem !important;
    margin-bottom: 0.75rem !important;
  }

  .custom-dokumentasi-mobile-page > section:nth-of-type(3),
  .custom-dokumentasi-mobile-page > section:nth-of-type(4) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-dokumentasi-mobile-page > section:nth-of-type(3) > div:not(.h-1\.5),
  .custom-dokumentasi-mobile-page > section:nth-of-type(4) > div:not(.h-1\.5) {
    padding: 1rem !important;
  }

  .custom-dokumentasi-mobile-page .rounded-\[1\.35rem\],
  .custom-dokumentasi-mobile-page .rounded-\[1\.4rem\],
  .custom-dokumentasi-mobile-page .rounded-\[1\.45rem\],
  .custom-dokumentasi-mobile-page .rounded-\[1\.5rem\],
  .custom-dokumentasi-mobile-page .rounded-\[1\.8rem\],
  .custom-dokumentasi-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-dokumentasi-mobile-page label,
  .custom-dokumentasi-mobile-page .text-sm.font-extrabold,
  .custom-dokumentasi-mobile-page .text-sm.font-bold,
  .custom-dokumentasi-mobile-page .text-sm.font-black {
    font-size: 0.82rem !important;
  }

  .custom-dokumentasi-mobile-page input:not([type="checkbox"]):not([type="file"]),
  .custom-dokumentasi-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-dokumentasi-mobile-page textarea {
    min-height: 118px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-dokumentasi-mobile-page button,
  .custom-dokumentasi-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-dokumentasi-mobile-page button[type="submit"],
  .custom-dokumentasi-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-dokumentasi-mobile-page .grid.lg\:grid-cols-2,
  .custom-dokumentasi-mobile-page .grid.sm\:grid-cols-2,
  .custom-dokumentasi-mobile-page .grid.sm\:grid-cols-3,
  .custom-dokumentasi-mobile-page .grid.lg\:grid-cols-\[190px_1fr_auto\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-dokumentasi-mobile-page .lg\:col-span-2 {
    grid-column: auto !important;
  }

  .custom-dokumentasi-mobile-page .grid.grid-cols-2.gap-3,
  .custom-dokumentasi-mobile-page .grid.grid-cols-2.gap-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-dokumentasi-mobile-page .flex.justify-end,
  .custom-dokumentasi-mobile-page .flex.flex-col-reverse {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
  }

  .custom-dokumentasi-mobile-page .flex.gap-2.lg\:flex-col {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 0.65rem !important;
  }

  .custom-dokumentasi-mobile-page .flex.flex-col.justify-center.gap-3 {
    align-items: stretch !important;
  }

  .custom-dokumentasi-mobile-page .flex.flex-col.justify-center.gap-3 label {
    min-height: 44px;
    border-radius: 1rem;
    background: white;
    padding: 0.75rem;
    border: 1px solid rgb(226 232 240);
  }

  .custom-dokumentasi-mobile-page .group.flex.min-h-\[13rem\],
  .custom-dokumentasi-mobile-page .group.flex.min-h-\[12rem\] {
    min-height: 10.5rem !important;
    padding: 1.25rem !important;
    border-radius: 1.05rem !important;
  }

  .custom-dokumentasi-mobile-page .h-14.w-14,
  .custom-dokumentasi-mobile-page .h-12.w-12 {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-dokumentasi-mobile-page img.h-48,
  .custom-dokumentasi-mobile-page img.h-44 {
    height: 11rem !important;
  }

  .custom-dokumentasi-mobile-page .relative.overflow-hidden.rounded-2xl.bg-slate-200 img {
    height: 11.25rem !important;
  }

  .custom-dokumentasi-mobile-page .line-clamp-2 {
    display: block !important;
    -webkit-line-clamp: unset !important;
    overflow: visible !important;
  }

  .custom-dokumentasi-mobile-page .truncate {
    min-width: 0;
  }

  .custom-dokumentasi-mobile-page .break-all,
  .custom-dokumentasi-mobile-page .break-words,
  .custom-dokumentasi-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-dokumentasi-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-dokumentasi-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-dokumentasi-mobile-page .flex.flex-wrap.gap-2 > .rounded-full,
  .custom-dokumentasi-mobile-page .mt-3.flex.flex-wrap.gap-2 > span {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-dokumentasi-mobile-page .p-5,
  .custom-dokumentasi-mobile-page .sm\:p-6,
  .custom-dokumentasi-mobile-page .lg\:p-7,
  .custom-dokumentasi-mobile-page .lg\:p-6 {
    padding: 1rem !important;
  }

  .custom-dokumentasi-mobile-page .mt-7,
  .custom-dokumentasi-mobile-page .mt-6 {
    margin-top: 1.25rem !important;
  }

  .custom-dokumentasi-mobile-page .mb-7,
  .custom-dokumentasi-mobile-page .mb-6 {
    margin-bottom: 1.25rem !important;
  }

  .custom-dokumentasi-mobile-page .gap-5 {
    gap: 1rem !important;
  }

  .custom-dokumentasi-mobile-page .gap-8 {
    gap: 1.25rem !important;
  }

  .custom-dokumentasi-mobile-page .documentation-scroll {
    -webkit-overflow-scrolling: touch;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-dokumentasi-mobile-page *,
  .custom-dokumentasi-mobile-page *::before,
  .custom-dokumentasi-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

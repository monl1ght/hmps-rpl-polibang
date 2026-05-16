<script setup>
import { computed, ref } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  services: {
    type: Array,
    default: () => [],
  },
  packages: {
    type: Array,
    default: () => [],
  },
  testimonials: {
    type: Array,
    default: () => [],
  },
  iconOptions: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const editingService = ref(null);
const editingPackage = ref(null);

const serviceSearch = ref("");
const packageSearch = ref("");
const selectedPackageService = ref("semua");
const testimonialFilter = ref("semua");

const services = computed(() => (Array.isArray(props.services) ? props.services : []));
const packages = computed(() => (Array.isArray(props.packages) ? props.packages : []));
const testimonials = computed(() =>
  Array.isArray(props.testimonials) ? props.testimonials : []
);
const iconOptions = computed(() =>
  Array.isArray(props.iconOptions) ? props.iconOptions : []
);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const stats = computed(() => {
  const activeServices = services.value.filter((item) => item.is_active).length;
  const activePackages = packages.value.filter((item) => item.is_active).length;
  const approvedTestimonials = testimonials.value.filter((item) => item.is_approved)
    .length;
  const pendingTestimonials = testimonials.value.filter((item) => !item.is_approved)
    .length;

  return [
    {
      label: "Layanan Aktif",
      value: activeServices,
      helper: `${services.value.length} total layanan`,
      icon: "service",
    },
    {
      label: "Paket Aktif",
      value: activePackages,
      helper: `${packages.value.length} total paket`,
      icon: "package",
    },
    {
      label: "Testimoni Approved",
      value: approvedTestimonials,
      helper: "Tampil di halaman user",
      icon: "check",
    },
    {
      label: "Menunggu Review",
      value: pendingTestimonials,
      helper: "Testimoni belum disetujui",
      icon: "clock",
    },
  ];
});

const filteredServices = computed(() => {
  const keyword = serviceSearch.value.trim().toLowerCase();

  if (!keyword) {
    return services.value;
  }

  return services.value.filter((service) => {
    return (
      String(service.title || "")
        .toLowerCase()
        .includes(keyword) ||
      String(service.slug || "")
        .toLowerCase()
        .includes(keyword) ||
      String(service.badge || "")
        .toLowerCase()
        .includes(keyword) ||
      String(service.summary || "")
        .toLowerCase()
        .includes(keyword)
    );
  });
});

const rowsToEditableText = (rows = []) => {
  if (!Array.isArray(rows)) return "";

  return rows
    .map((row) => {
      if (!row) return "";

      if (typeof row === "string") {
        return row.trim();
      }

      const label = String(row.label || "").trim();
      const value = String(row.value || "").trim();

      if (!label && !value) return "";
      if (!label) return `Keterangan | ${value}`;
      if (!value) return label;

      return `${label} | ${value}`;
    })
    .filter(Boolean)
    .join("\n");
};

const packageDetailText = (packageItem) => {
  const directText = String(packageItem?.rows_text || "").trim();

  if (directText) return directText;

  return rowsToEditableText(packageItem?.rows || []);
};

const filteredPackages = computed(() => {
  const keyword = packageSearch.value.trim().toLowerCase();

  return packages.value.filter((packageItem) => {
    const serviceMatch =
      selectedPackageService.value === "semua" ||
      String(packageItem.service_catalog_id) === String(selectedPackageService.value);

    const searchMatch =
      !keyword ||
      String(packageItem.title || "")
        .toLowerCase()
        .includes(keyword) ||
      String(packageItem.subtitle || "")
        .toLowerCase()
        .includes(keyword) ||
      String(packageItem.service_title || "")
        .toLowerCase()
        .includes(keyword) ||
      packageDetailText(packageItem).toLowerCase().includes(keyword);

    return serviceMatch && searchMatch;
  });
});

const filteredTestimonials = computed(() => {
  if (testimonialFilter.value === "approved") {
    return testimonials.value.filter((item) => item.is_approved);
  }

  if (testimonialFilter.value === "pending") {
    return testimonials.value.filter((item) => !item.is_approved);
  }

  return testimonials.value;
});

const serviceForm = useForm({
  title: "",
  slug: "",
  badge: "",
  icon: "globe",
  summary: "",
  features_text: "",
  cta: "",
  whatsapp_text: "",
  is_active: true,
  sort_order: 0,
});

const serviceEditForm = useForm({
  title: "",
  slug: "",
  badge: "",
  icon: "globe",
  summary: "",
  features_text: "",
  cta: "",
  whatsapp_text: "",
  is_active: true,
  sort_order: 0,
});

const packageForm = useForm({
  service_catalog_id: "",
  title: "",
  subtitle: "",
  rows_text: "",
  is_active: true,
  sort_order: 0,
});

const packageEditForm = useForm({
  service_catalog_id: "",
  title: "",
  subtitle: "",
  rows_text: "",
  is_active: true,
  sort_order: 0,
});

const resetServiceForm = () => {
  serviceForm.reset();
  serviceForm.icon = "globe";
  serviceForm.is_active = true;
  serviceForm.sort_order = 0;
};

const resetPackageForm = () => {
  packageForm.reset();
  packageForm.service_catalog_id = "";
  packageForm.is_active = true;
  packageForm.sort_order = 0;
};

const storeService = () => {
  serviceForm.post("/admin/layanan-jasa/services", {
    preserveScroll: true,
    onSuccess: () => resetServiceForm(),
  });
};

const editService = (service) => {
  editingService.value = service;

  serviceEditForm.title = service.title || "";
  serviceEditForm.slug = service.slug || "";
  serviceEditForm.badge = service.badge || "";
  serviceEditForm.icon = service.icon || "globe";
  serviceEditForm.summary = service.summary || "";
  serviceEditForm.features_text = service.features_text || "";
  serviceEditForm.cta = service.cta || "";
  serviceEditForm.whatsapp_text = service.whatsapp_text || "";
  serviceEditForm.is_active = Boolean(service.is_active);
  serviceEditForm.sort_order = Number(service.sort_order || 0);
};

const cancelEditService = () => {
  editingService.value = null;
  serviceEditForm.reset();
};

const updateService = () => {
  if (!editingService.value) return;

  serviceEditForm.put(`/admin/layanan-jasa/services/${editingService.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditService(),
  });
};

const deleteService = (service) => {
  if (!confirm(`Hapus layanan "${service.title}"?`)) return;

  router.delete(`/admin/layanan-jasa/services/${service.id}`, {
    preserveScroll: true,
  });
};

const storePackage = () => {
  packageForm.post("/admin/layanan-jasa/packages", {
    preserveScroll: true,
    onSuccess: () => resetPackageForm(),
  });
};

const editPackage = (packageItem) => {
  editingPackage.value = packageItem;

  packageEditForm.service_catalog_id = packageItem.service_catalog_id || "";
  packageEditForm.title = packageItem.title || "";
  packageEditForm.subtitle = packageItem.subtitle || "";
  packageEditForm.rows_text = packageDetailText(packageItem);
  packageEditForm.is_active = Boolean(packageItem.is_active);
  packageEditForm.sort_order = Number(packageItem.sort_order || 0);
};

const cancelEditPackage = () => {
  editingPackage.value = null;
  packageEditForm.reset();
};

const updatePackage = () => {
  if (!editingPackage.value) return;

  packageEditForm.put(`/admin/layanan-jasa/packages/${editingPackage.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditPackage(),
  });
};

const deletePackage = (packageItem) => {
  if (!confirm(`Hapus paket "${packageItem.title}"?`)) return;

  router.delete(`/admin/layanan-jasa/packages/${packageItem.id}`, {
    preserveScroll: true,
  });
};

const updateTestimonialStatus = (testimonial, status) => {
  router.put(
    `/admin/layanan-jasa/testimonials/${testimonial.id}`,
    {
      is_approved: status,
    },
    {
      preserveScroll: true,
    }
  );
};

const deleteTestimonial = (testimonial) => {
  if (!confirm(`Hapus testimoni dari "${testimonial.name}"?`)) return;

  router.delete(`/admin/layanan-jasa/testimonials/${testimonial.id}`, {
    preserveScroll: true,
  });
};

const iconLabel = (icon) => {
  return iconOptions.value.find((item) => item.value === icon)?.label || icon || "Icon";
};

const serviceName = (serviceId) => {
  return services.value.find((service) => Number(service.id) === Number(serviceId))
    ?.title;
};

const serviceBadgeClass = (service) => {
  if (service.is_active) {
    return "border-emerald-200 bg-emerald-50 text-emerald-700";
  }

  return "border-slate-200 bg-slate-100 text-slate-600";
};

const packageBadgeClass = (packageItem) => {
  if (packageItem.is_active) {
    return "border-emerald-200 bg-emerald-50 text-emerald-700";
  }

  return "border-slate-200 bg-slate-100 text-slate-600";
};

const testimonialBadgeClass = (testimonial) => {
  if (testimonial.is_approved) {
    return "border-emerald-200 bg-emerald-50 text-emerald-700";
  }

  return "border-amber-200 bg-amber-50 text-amber-700";
};
</script>

<template>
  <Head title="Custom Layanan Jasa" />

  <div
    class="relative isolate space-y-8 overflow-hidden pb-8 before:pointer-events-none before:absolute before:-left-28 before:top-32 before:-z-10 before:h-72 before:w-72 before:rounded-full before:bg-red-100/70 before:blur-[90px] after:pointer-events-none after:absolute after:right-0 after:top-[38rem] after:-z-10 after:h-80 after:w-80 after:rounded-full after:bg-slate-200/70 after:blur-[100px]"
  >
    <!-- Header -->
    <section
      data-aos="fade-up"
      class="relative overflow-hidden rounded-[2rem] border border-slate-800/90 bg-[radial-gradient(circle_at_top_right,rgba(239,68,68,0.32),transparent_34%),linear-gradient(135deg,#020617,#0f172a_48%,#450a0a)] p-6 text-white shadow-[0_28px_80px_rgba(2,6,23,0.28)] sm:p-8 lg:p-10"
    >
      <div
        class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-red-500/25 blur-[80px]"
      />
      <div
        class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-white/12 blur-[80px]"
      />

      <div class="relative z-10 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
        <div>
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.12em] text-red-100 ring-1 ring-white/15 backdrop-blur"
          >
            <span
              class="h-2 w-2 rounded-full bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.14)]"
            />
            Admin / Custom Layanan Jasa
          </div>

          <h1
            class="max-w-3xl text-[2.1rem] font-black leading-[1.05] tracking-[-0.055em] sm:text-[2.85rem] lg:text-[3.35rem]"
          >
            Kelola Layanan Jasa
            <span class="block text-red-300">HMPS RPL</span>
          </h1>

          <p class="mt-5 max-w-2xl text-sm leading-8 text-slate-300 sm:text-base">
            Atur layanan utama, paket berdasarkan layanan induk, dan testimoni client agar
            halaman Layanan Jasa tampil dinamis, rapi, dan profesional.
          </p>

          <div class="mt-7 grid max-w-2xl gap-3 sm:grid-cols-3">
            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10 backdrop-blur">
              <p class="text-2xl font-black tracking-[-0.04em]">{{ services.length }}</p>
              <p
                class="mt-1 text-[0.7rem] font-bold uppercase tracking-[0.1em] text-slate-300"
              >
                Layanan
              </p>
            </div>
            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10 backdrop-blur">
              <p class="text-2xl font-black tracking-[-0.04em]">{{ packages.length }}</p>
              <p
                class="mt-1 text-[0.7rem] font-bold uppercase tracking-[0.1em] text-slate-300"
              >
                Paket
              </p>
            </div>
            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10 backdrop-blur">
              <p class="text-2xl font-black tracking-[-0.04em]">
                {{ testimonials.length }}
              </p>
              <p
                class="mt-1 text-[0.7rem] font-bold uppercase tracking-[0.1em] text-slate-300"
              >
                Testimoni
              </p>
            </div>
          </div>
        </div>

        <a
          href="/layananJasa"
          target="_blank"
          class="group relative inline-flex min-h-[54px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-sm font-black text-slate-950 shadow-[0_16px_38px_rgba(255,255,255,0.14)] ring-1 ring-white/40 transition duration-300 hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700 sm:w-fit"
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
          <span class="relative z-10">Preview Layanan Jasa</span>
        </a>
      </div>
    </section>

    <!-- Flash -->
    <div
      v-if="flashSuccess"
      data-aos="fade-up"
      class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_10px_28px_rgba(16,185,129,0.08)]"
    >
      {{ flashSuccess }}
    </div>

    <div
      v-if="flashError"
      data-aos="fade-up"
      class="rounded-[1.35rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_10px_28px_rgba(239,68,68,0.08)]"
    >
      {{ flashError }}
    </div>

    <!-- Quick Navigation -->
    <nav
      data-aos="fade-up"
      class="sticky top-4 z-20 rounded-[1.35rem] border border-slate-200/90 bg-white/90 p-2 shadow-[0_16px_44px_rgba(15,23,42,0.08)] ring-1 ring-white/70 backdrop-blur"
    >
      <div class="grid gap-2 sm:grid-cols-3">
        <a
          href="#layanan"
          class="group inline-flex min-h-[44px] items-center justify-center rounded-2xl px-4 text-sm font-black text-slate-600 transition hover:bg-red-50 hover:text-red-700"
        >
          Layanan
        </a>
        <a
          href="#paket"
          class="group inline-flex min-h-[44px] items-center justify-center rounded-2xl px-4 text-sm font-black text-slate-600 transition hover:bg-red-50 hover:text-red-700"
        >
          Paket
        </a>
        <a
          href="#testimoni"
          class="group inline-flex min-h-[44px] items-center justify-center rounded-2xl px-4 text-sm font-black text-slate-600 transition hover:bg-red-50 hover:text-red-700"
        >
          Testimoni
        </a>
      </div>
    </nav>

    <!-- Stats -->
    <section data-aos="fade-up" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="item in stats"
        :key="item.label"
        class="group relative overflow-hidden rounded-[1.6rem] border border-slate-200/90 bg-white/95 p-5 shadow-[0_16px_42px_rgba(15,23,42,0.06)] ring-1 ring-white/70 transition duration-300 before:absolute before:right-0 before:top-0 before:h-24 before:w-24 before:translate-x-8 before:-translate-y-8 before:rounded-full before:bg-red-50 before:transition before:duration-300 hover:-translate-y-1 hover:border-red-200 hover:bg-white hover:shadow-[0_24px_58px_rgba(15,23,42,0.12)] hover:before:scale-150"
      >
        <div
          class="relative z-10 mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100 transition duration-300 group-hover:scale-105 group-hover:bg-red-600 group-hover:text-white"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              v-if="item.icon === 'service'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z"
            />
            <path
              v-else-if="item.icon === 'package'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M20 7.5 12 3 4 7.5m16 0-8 4.5m8-4.5v9L12 21m0-9L4 7.5m8 4.5v9M4 7.5v9L12 21"
            />
            <path
              v-else-if="item.icon === 'check'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 8v5l3 2m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
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

    <!-- Service Management -->
    <section
      id="layanan"
      data-aos="fade-up"
      class="grid gap-6 xl:grid-cols-[0.42fr_0.58fr]"
    >
      <!-- Add Service -->
      <div
        class="overflow-hidden rounded-[1.85rem] border border-slate-200/90 bg-white/95 shadow-[0_18px_52px_rgba(15,23,42,0.07)] ring-1 ring-white/70 backdrop-blur"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Tambah Layanan
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Data Layanan Baru
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Tambahkan layanan induk seperti Jasa Website, Instalasi Software & OS, atau
              Jasa Desain & Video.
            </p>
          </div>

          <form class="grid gap-5" @submit.prevent="storeService">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Judul Layanan
                <span class="text-red-600">*</span>
              </label>
              <input
                v-model="serviceForm.title"
                type="text"
                placeholder="Contoh: Jasa Website"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="serviceForm.errors.title"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ serviceForm.errors.title }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">Slug</label>
              <input
                v-model="serviceForm.slug"
                type="text"
                placeholder="Opsional, contoh: website"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                class="relative z-10 mt-2 text-xs font-semibold leading-5 text-slate-500"
              >
                Boleh dikosongkan. Sistem akan membuat slug otomatis.
              </p>
              <p
                v-if="serviceForm.errors.slug"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ serviceForm.errors.slug }}
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Badge</label
                >
                <input
                  v-model="serviceForm.badge"
                  type="text"
                  placeholder="Layanan Utama"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Icon</label
                >
                <select
                  v-model="serviceForm.icon"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                >
                  <option
                    v-for="icon in iconOptions"
                    :key="icon.value"
                    :value="icon.value"
                  >
                    {{ icon.label }}
                  </option>
                </select>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Ringkasan</label
              >
              <textarea
                v-model="serviceForm.summary"
                rows="4"
                placeholder="Deskripsi singkat layanan..."
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Fitur Layanan</label
              >
              <textarea
                v-model="serviceForm.features_text"
                rows="5"
                placeholder="Tulis satu fitur per baris"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                class="relative z-10 mt-2 text-xs font-semibold leading-5 text-slate-500"
              >
                Satu baris akan menjadi satu poin fitur layanan.
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Label Tombol CTA</label
              >
              <input
                v-model="serviceForm.cta"
                type="text"
                placeholder="Contoh: Buat Website Saya"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Teks WhatsApp</label
              >
              <textarea
                v-model="serviceForm.whatsapp_text"
                rows="3"
                placeholder="Halo, saya ingin konsultasi layanan..."
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Urutan Tampil</label
                >
                <input
                  v-model="serviceForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="flex items-end">
                <label
                  class="flex h-[3.25rem] w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-slate-700"
                >
                  <input
                    v-model="serviceForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Aktif
                </label>
              </div>
            </div>

            <button
              type="submit"
              :disabled="serviceForm.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                serviceForm.processing ? "Menyimpan..." : "Tambah Layanan"
              }}</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Service List -->
      <div
        class="overflow-hidden rounded-[1.85rem] border border-slate-200/90 bg-white/95 shadow-[0_18px_52px_rgba(15,23,42,0.07)] ring-1 ring-white/70 backdrop-blur"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#020617,#334155,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Daftar Layanan
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Kelola Layanan
              </h2>
              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Data layanan ini akan tampil sebagai layanan utama di halaman user.
              </p>
            </div>

            <input
              v-model="serviceSearch"
              type="text"
              placeholder="Cari layanan..."
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold outline-none transition hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:w-72"
            />
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <span
              class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
            >
              {{ filteredServices.length }} hasil
            </span>
            <span
              v-if="serviceSearch"
              class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 ring-1 ring-red-100"
            >
              Pencarian: “{{ serviceSearch }}”
            </span>
          </div>

          <div class="mt-6 space-y-3">
            <div
              v-for="service in filteredServices"
              :key="service.id"
              class="group overflow-hidden rounded-[1.45rem] border border-slate-200 bg-slate-50/90 shadow-sm transition duration-300 hover:-translate-y-[2px] hover:border-red-200 hover:bg-white hover:shadow-[0_18px_44px_rgba(15,23,42,0.08)]"
            >
              <div v-if="editingService?.id !== service.id" class="p-4">
                <div
                  class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                  <div class="min-w-0">
                    <div class="flex flex-wrap gap-2">
                      <span
                        class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                      >
                        {{ service.badge || "Layanan" }}
                      </span>

                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                      >
                        {{ iconLabel(service.icon) }}
                      </span>

                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="serviceBadgeClass(service)"
                      >
                        {{ service.is_active ? "Aktif" : "Nonaktif" }}
                      </span>

                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                      >
                        Urutan {{ service.sort_order }}
                      </span>
                    </div>

                    <h3 class="mt-3 text-base font-black text-slate-950">
                      {{ service.title }}
                    </h3>

                    <p
                      class="mt-1 text-xs font-bold uppercase tracking-[0.08em] text-slate-400"
                    >
                      /{{ service.slug }}
                    </p>

                    <p
                      class="mt-2 line-clamp-2 text-justify text-sm leading-7 text-slate-500"
                    >
                      {{ service.summary || "Belum ada ringkasan layanan." }}
                    </p>

                    <div
                      class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-500"
                    >
                      <span
                        class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200"
                      >
                        {{ service.features?.length || 0 }} fitur
                      </span>
                      <span
                        class="rounded-full bg-white px-3 py-1.5 ring-1 ring-slate-200"
                      >
                        CTA: {{ service.cta || "-" }}
                      </span>
                    </div>
                  </div>

                  <div class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col">
                    <button
                      type="button"
                      class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-[1px] hover:bg-red-50 hover:text-red-700 sm:flex-none"
                      @click="editService(service)"
                    >
                      Edit
                    </button>

                    <button
                      type="button"
                      class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:-translate-y-[1px] hover:bg-red-100 sm:flex-none"
                      @click="deleteService(service)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>
              </div>

              <form
                v-else
                class="grid gap-4 bg-white p-4 sm:p-5"
                @submit.prevent="updateService"
              >
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Layanan
                    </span>
                    <h3 class="mt-2 text-lg font-black text-slate-950">
                      Edit {{ service.title }}
                    </h3>
                  </div>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditService"
                  >
                    Batal Edit
                  </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="serviceEditForm.title"
                    type="text"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    placeholder="Judul layanan"
                  />

                  <input
                    v-model="serviceEditForm.slug"
                    type="text"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    placeholder="Slug"
                  />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="serviceEditForm.badge"
                    type="text"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    placeholder="Badge"
                  />

                  <select
                    v-model="serviceEditForm.icon"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="icon in iconOptions"
                      :key="icon.value"
                      :value="icon.value"
                    >
                      {{ icon.label }}
                    </option>
                  </select>
                </div>

                <textarea
                  v-model="serviceEditForm.summary"
                  rows="3"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Ringkasan layanan"
                />

                <textarea
                  v-model="serviceEditForm.features_text"
                  rows="4"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Satu fitur per baris"
                />

                <input
                  v-model="serviceEditForm.cta"
                  type="text"
                  class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Label tombol CTA"
                />

                <textarea
                  v-model="serviceEditForm.whatsapp_text"
                  rows="3"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Teks WhatsApp"
                />

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="flex flex-wrap items-center gap-3">
                    <input
                      v-model="serviceEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <label
                      class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                    >
                      <input
                        v-model="serviceEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                  </div>

                  <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditService"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="serviceEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {{ serviceEditForm.processing ? "Menyimpan..." : "Simpan Layanan" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!filteredServices.length"
              class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50/90 p-8 text-center"
            >
              <h3 class="text-lg font-black text-slate-950">Layanan tidak ditemukan</h3>
              <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                Tambahkan layanan baru atau ubah kata kunci pencarian.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Package Management -->
    <section
      id="paket"
      data-aos="fade-up"
      class="grid gap-6 xl:grid-cols-[0.38fr_0.62fr]"
    >
      <!-- Add Package -->
      <div
        class="overflow-hidden rounded-[1.85rem] border border-slate-200/90 bg-white/95 shadow-[0_18px_52px_rgba(15,23,42,0.07)] ring-1 ring-white/70 backdrop-blur"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Tambah Paket
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Paket Layanan Baru
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Paket wajib terhubung ke layanan induk. Format detail paket memakai pola
              <span class="font-black text-slate-700">Label | Value</span>.
            </p>
          </div>

          <form class="grid gap-5" @submit.prevent="storePackage">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Layanan Induk
                <span class="text-red-600">*</span>
              </label>
              <select
                v-model="packageForm.service_catalog_id"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              >
                <option value="">Pilih Layanan</option>
                <option v-for="service in services" :key="service.id" :value="service.id">
                  {{ service.title }}
                </option>
              </select>
              <p
                v-if="packageForm.errors.service_catalog_id"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ packageForm.errors.service_catalog_id }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Judul Paket
                <span class="text-red-600">*</span>
              </label>
              <input
                v-model="packageForm.title"
                type="text"
                placeholder="Contoh: Website Company Profile"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="packageForm.errors.title"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ packageForm.errors.title }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Subtitle</label
              >
              <input
                v-model="packageForm.subtitle"
                type="text"
                placeholder="Contoh: Bisnis Menengah"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800"
                >Detail Paket</label
              >
              <textarea
                v-model="packageForm.rows_text"
                rows="8"
                placeholder="Keterangan | Membangun citra bisnis yang lebih profesional&#10;Harga | Rp1.000.000 – Rp7.000.000&#10;Tujuan | Company profile profesional&#10;Struktur Halaman | Home, Tentang, Layanan, Kontak&#10;Cocok Untuk | Bisnis menengah dan organisasi"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                Gunakan 1 baris untuk 1 data. Contoh:
                <span class="font-black">Harga | Rp500.000</span>,
                <span class="font-black">Keterangan | Deskripsi paket</span>. Label
                <span class="font-black">Harga</span> akan tampil sebagai harga utama di
                modal user.
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800"
                  >Urutan Tampil</label
                >
                <input
                  v-model="packageForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="flex items-end">
                <label
                  class="flex h-[3.25rem] w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-slate-700"
                >
                  <input
                    v-model="packageForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Aktif
                </label>
              </div>
            </div>

            <button
              type="submit"
              :disabled="packageForm.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">{{
                packageForm.processing ? "Menyimpan..." : "Tambah Paket"
              }}</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Package List -->
      <div
        class="overflow-hidden rounded-[1.85rem] border border-slate-200/90 bg-white/95 shadow-[0_18px_52px_rgba(15,23,42,0.07)] ring-1 ring-white/70 backdrop-blur"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#020617,#334155,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Daftar Paket
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Kelola Paket Layanan
              </h2>
              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Paket ini akan muncul pada modal konsultasi sesuai layanan induknya.
              </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[120px]">
              <input
                v-model="packageSearch"
                type="text"
                placeholder="Cari paket..."
                class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold outline-none transition hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <select
                v-model="selectedPackageService"
                class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/80 px-4 text-sm font-semibold outline-none transition hover:border-slate-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              >
                <option value="semua">Semua Layanan</option>
                <option v-for="service in services" :key="service.id" :value="service.id">
                  {{ service.title }}
                </option>
              </select>
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <span
              class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
            >
              {{ filteredPackages.length }} hasil
            </span>
            <span
              v-if="packageSearch"
              class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 ring-1 ring-red-100"
            >
              Pencarian: “{{ packageSearch }}”
            </span>
          </div>

          <div class="mt-6 space-y-3">
            <div
              v-for="packageItem in filteredPackages"
              :key="packageItem.id"
              class="group overflow-hidden rounded-[1.45rem] border border-slate-200 bg-slate-50/90 shadow-sm transition duration-300 hover:-translate-y-[2px] hover:border-red-200 hover:bg-white hover:shadow-[0_18px_44px_rgba(15,23,42,0.08)]"
            >
              <div v-if="editingPackage?.id !== packageItem.id" class="p-4">
                <div
                  class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                  <div class="min-w-0">
                    <div class="flex flex-wrap gap-2">
                      <span
                        class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                      >
                        {{
                          packageItem.service_title ||
                          serviceName(packageItem.service_catalog_id) ||
                          "Tanpa Layanan"
                        }}
                      </span>

                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                      >
                        {{ packageItem.rows?.length || 0 }} Detail
                      </span>

                      <span
                        class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                        :class="packageBadgeClass(packageItem)"
                      >
                        {{ packageItem.is_active ? "Aktif" : "Nonaktif" }}
                      </span>

                      <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                      >
                        Urutan {{ packageItem.sort_order }}
                      </span>
                    </div>

                    <h3 class="mt-3 text-base font-black text-slate-950">
                      {{ packageItem.title }}
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-slate-500">
                      {{ packageItem.subtitle || "Tanpa subtitle" }}
                    </p>

                    <div
                      v-if="packageItem.rows?.length"
                      class="mt-3 grid gap-2 sm:grid-cols-2"
                    >
                      <div
                        v-for="row in packageItem.rows.slice(0, 4)"
                        :key="`${packageItem.id}-${row.label}-${row.value}`"
                        class="rounded-xl bg-white px-3 py-2 text-xs leading-5 text-slate-600 ring-1 ring-slate-200/70"
                      >
                        <span class="font-black text-slate-800">{{ row.label }}:</span>
                        {{ row.value }}
                      </div>
                    </div>
                  </div>

                  <div class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col">
                    <button
                      type="button"
                      class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-[1px] hover:bg-red-50 hover:text-red-700 sm:flex-none"
                      @click="editPackage(packageItem)"
                    >
                      Edit
                    </button>

                    <button
                      type="button"
                      class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:-translate-y-[1px] hover:bg-red-100 sm:flex-none"
                      @click="deletePackage(packageItem)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>
              </div>

              <form
                v-else
                class="grid gap-4 bg-white p-4 sm:p-5"
                @submit.prevent="updatePackage"
              >
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Paket
                    </span>
                    <h3 class="mt-2 text-lg font-black text-slate-950">
                      Edit {{ packageItem.title }}
                    </h3>
                  </div>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditPackage"
                  >
                    Batal Edit
                  </button>
                </div>

                <select
                  v-model="packageEditForm.service_catalog_id"
                  class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                >
                  <option value="">Pilih Layanan</option>
                  <option
                    v-for="service in services"
                    :key="service.id"
                    :value="service.id"
                  >
                    {{ service.title }}
                  </option>
                </select>

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="packageEditForm.title"
                    type="text"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    placeholder="Judul paket"
                  />

                  <input
                    v-model="packageEditForm.subtitle"
                    type="text"
                    class="h-[3.1rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    placeholder="Subtitle"
                  />
                </div>

                <textarea
                  v-model="packageEditForm.rows_text"
                  rows="7"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  placeholder="Keterangan | Deskripsi paket&#10;Harga | Rp1.000.000 – Rp7.000.000&#10;Tujuan | Isi tujuan paket&#10;Cocok Untuk | Target pengguna"
                />
                <p class="text-xs font-semibold leading-5 text-slate-500">
                  Edit semua isi paket di sini: keterangan, harga, tujuan, struktur
                  halaman, dan detail lain. Format terbaik:
                  <span class="font-black">Label | Value</span>.
                </p>

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="flex flex-wrap items-center gap-3">
                    <input
                      v-model="packageEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <label
                      class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                    >
                      <input
                        v-model="packageEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                  </div>

                  <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditPackage"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="packageEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {{ packageEditForm.processing ? "Menyimpan..." : "Simpan Paket" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!filteredPackages.length"
              class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50/90 p-8 text-center"
            >
              <h3 class="text-lg font-black text-slate-950">
                Paket layanan tidak ditemukan
              </h3>
              <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                Tambahkan paket baru atau ubah filter layanan.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section
      id="testimoni"
      data-aos="fade-up"
      class="overflow-hidden rounded-[1.85rem] border border-slate-200/90 bg-white/95 shadow-[0_18px_52px_rgba(15,23,42,0.07)] ring-1 ring-white/70 backdrop-blur"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#020617,#334155,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Testimoni Client
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Kelola Testimoni Client
            </h2>
            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Testimoni baru tidak langsung tampil. Admin harus approve terlebih dahulu
              sebelum ditampilkan pada halaman user.
            </p>
          </div>

          <select
            v-model="testimonialFilter"
            class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-bold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
          >
            <option value="semua">Semua Testimoni</option>
            <option value="approved">Approved</option>
            <option value="pending">Menunggu Review</option>
          </select>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <span
            class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
          >
            {{ filteredTestimonials.length }} testimoni
          </span>
          <span
            class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700 ring-1 ring-emerald-100"
          >
            {{ testimonials.filter((item) => item.is_approved).length }} approved
          </span>
          <span
            class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700 ring-1 ring-amber-100"
          >
            {{ testimonials.filter((item) => !item.is_approved).length }} pending
          </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article
            v-for="testimonial in filteredTestimonials"
            :key="testimonial.id"
            class="group relative overflow-hidden rounded-[1.55rem] border border-slate-200 bg-slate-50/90 p-5 shadow-sm transition duration-300 before:absolute before:right-0 before:top-0 before:h-24 before:w-24 before:translate-x-10 before:-translate-y-10 before:rounded-full before:bg-red-100/60 before:blur-2xl hover:-translate-y-[2px] hover:border-red-200 hover:bg-white hover:shadow-[0_20px_48px_rgba(15,23,42,0.10)]"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-base font-black text-slate-950">
                  {{ testimonial.name }}
                </h3>

                <p class="mt-1 text-sm font-semibold text-slate-500">
                  {{ testimonial.business_name || testimonial.service_type }}
                </p>
              </div>

              <span
                class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                :class="testimonialBadgeClass(testimonial)"
              >
                {{ testimonial.is_approved ? "Approved" : "Pending" }}
              </span>
            </div>

            <div class="mt-4 flex gap-1 text-red-500">
              <span v-for="star in 5" :key="star">
                {{ star <= testimonial.rating ? "★" : "☆" }}
              </span>
            </div>

            <p class="mt-4 text-justify text-sm leading-7 text-slate-600">
              “{{ testimonial.message }}”
            </p>

            <p class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
              {{ testimonial.service_type }} • {{ testimonial.created_at }}
            </p>

            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
              <button
                v-if="!testimonial.is_approved"
                type="button"
                class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-emerald-50 px-4 text-xs font-black text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-100"
                @click="updateTestimonialStatus(testimonial, true)"
              >
                Approve
              </button>

              <button
                v-if="testimonial.is_approved"
                type="button"
                class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-amber-50 px-4 text-xs font-black text-amber-700 ring-1 ring-amber-100 transition hover:bg-amber-100"
                @click="updateTestimonialStatus(testimonial, false)"
              >
                Unapprove
              </button>

              <button
                type="button"
                class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                @click="deleteTestimonial(testimonial)"
              >
                Hapus
              </button>
            </div>
          </article>

          <div
            v-if="!filteredTestimonials.length"
            class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50/90 p-8 text-center md:col-span-2 xl:col-span-3"
          >
            <h3 class="text-xl font-black text-slate-950">Belum ada testimoni</h3>

            <p
              class="mx-auto mt-3 max-w-xl text-justify text-sm leading-7 text-slate-500"
            >
              Testimoni client akan muncul setelah user mengirim form dari halaman Layanan
              Jasa.
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

button,
a,
input,
select,
textarea {
  -webkit-tap-highlight-color: transparent;
}

input,
select,
textarea {
  min-width: 0;
}

@media (max-width: 640px) {
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}
</style>

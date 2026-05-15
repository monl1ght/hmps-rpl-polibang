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
  admins: {
    type: Array,
    default: () => [],
  },
  steps: {
    type: Array,
    default: () => [],
  },
  tickers: {
    type: Array,
    default: () => [],
  },
  testimonials: {
    type: Array,
    default: () => [],
  },
  serviceDayOptions: {
    type: Array,
    default: () => [],
  },
  dayOptions: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const editingAdmin = ref(null);
const editingStep = ref(null);
const editingTicker = ref(null);

const adminSearch = ref("");
const testimonialSearch = ref("");
const testimonialRatingFilter = ref("all");
const testimonialStatusFilter = ref("all");
const stepSearch = ref("");
const tickerSearch = ref("");

const defaultDayOptions = [
  "Senin",
  "Selasa",
  "Rabu",
  "Kamis",
  "Jumat",
  "Sabtu",
  "Minggu",
];

const ratingOptions = [5, 4, 3, 2, 1];

const quickSkillPresets = [
  "Website",
  "Desain Grafis",
  "Editing Video",
  "Instalasi Software",
  "Teknis",
  "Problem Solving",
  "UI/UX",
  "Konsultasi Umum",
];

const defaultWhatsappTemplate =
  "Halo {admin_name}, saya ingin konsultasi melalui layanan HMPS RPL.\n\nSaya memilih konsultasi dengan:\nNama Admin: {admin_name}\nBidang/Skill: {admin_skills}\n\nMohon informasinya ya. Terima kasih.";

const sections = computed(() => (Array.isArray(props.sections) ? props.sections : []));
const admins = computed(() => (Array.isArray(props.admins) ? props.admins : []));
const steps = computed(() => (Array.isArray(props.steps) ? props.steps : []));
const tickers = computed(() => (Array.isArray(props.tickers) ? props.tickers : []));
const testimonials = computed(() =>
  Array.isArray(props.testimonials) ? props.testimonials : []
);

const dayOptions = computed(() => {
  if (Array.isArray(props.serviceDayOptions) && props.serviceDayOptions.length) {
    return props.serviceDayOptions;
  }

  if (Array.isArray(props.dayOptions) && props.dayOptions.length) {
    return props.dayOptions;
  }

  return defaultDayOptions;
});

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const activeHeroSection = computed(() => {
  return sections.value.find((section) => section.key === "hero") || null;
});

const normalizeBoolean = (value) => {
  if (value === true || value === 1 || value === "1") return true;
  if (value === false || value === 0 || value === "0") return false;

  return Boolean(value);
};

const normalizeTimeValue = (value, fallback = "08:00") => {
  const raw = String(value || "").trim();

  if (/^\d{2}:\d{2}/.test(raw)) return raw.slice(0, 5);
  if (/^\d{2}\.\d{2}/.test(raw)) return raw.slice(0, 5).replace(".", ":");

  return fallback;
};

const skillsTextToArray = (value) => {
  return String(value || "")
    .split(/[\n,;|]/)
    .map((item) => item.trim())
    .filter(Boolean)
    .filter((item) => !/^hari\s+/i.test(item));
};

const skillsArrayToText = (skills) => {
  return Array.isArray(skills) ? skills.filter(Boolean).join("\n") : "";
};

const skillList = (admin) => {
  if (Array.isArray(admin.skills) && admin.skills.length) {
    return admin.skills.filter(Boolean);
  }

  return skillsTextToArray(admin.skills_text);
};

const statusClass = (status) => {
  return normalizeBoolean(status)
    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
    : "border-amber-200 bg-amber-50 text-amber-700";
};

const statusText = (status) => {
  return normalizeBoolean(status) ? "Approved / Tampil" : "Pending Review";
};

const ratingClass = (rating, activeRating) => {
  return Number(rating) === Number(activeRating)
    ? "border-amber-200 bg-amber-500 text-white shadow-sm"
    : "border-slate-200 bg-white text-slate-600 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700";
};

const heroMeta = reactive({
  status_card_title: "Status Layanan",
  status_card_subtitle: "Layanan konsultasi dibuka sesuai jadwal operasional.",
  schedule_card_title: "Jam Operasional",
  schedule_card_subtitle: "Senin - Jumat, 08.00 - 20.00 WIB",
  service_days: ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"],
  service_day_label: "Senin - Jumat",
  service_start_time: "08:00",
  service_end_time: "20:00",
  admin_section_badge: "Pilih Admin",
  admin_section_title: "Pilih admin berdasarkan skill",
  admin_section_highlight: "yang kamu butuhkan",
  admin_section_description:
    "Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda.",
  whatsapp_template: defaultWhatsappTemplate,
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

const fillSectionForm = (section) => {
  if (!section) return;

  const meta = section.meta || {};

  sectionForm.badge = section.badge || "";
  sectionForm.title = section.title || "";
  sectionForm.title_highlight = section.title_highlight || "";
  sectionForm.description = section.description || "";
  sectionForm.primary_button_label = section.primary_button_label || "";
  sectionForm.primary_button_url = section.primary_button_url || "";
  sectionForm.secondary_button_label = section.secondary_button_label || "";
  sectionForm.secondary_button_url = section.secondary_button_url || "";
  sectionForm.is_active = normalizeBoolean(section.is_active);
  sectionForm.sort_order = Number(section.sort_order || 0);

  heroMeta.status_card_title = meta.status_card_title || "Status Layanan";
  heroMeta.status_card_subtitle =
    meta.status_card_subtitle || "Layanan konsultasi dibuka sesuai jadwal operasional.";
  heroMeta.schedule_card_title = meta.schedule_card_title || "Jam Operasional";
  heroMeta.schedule_card_subtitle =
    meta.schedule_card_subtitle || "Senin - Jumat, 08.00 - 20.00 WIB";
  heroMeta.service_days =
    Array.isArray(meta.service_days) && meta.service_days.length
      ? [...meta.service_days]
      : ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"];
  heroMeta.service_day_label = meta.service_day_label || "Senin - Jumat";
  heroMeta.service_start_time = normalizeTimeValue(meta.service_start_time, "08:00");
  heroMeta.service_end_time = normalizeTimeValue(meta.service_end_time, "20:00");
  heroMeta.admin_section_badge = meta.admin_section_badge || "Pilih Admin";
  heroMeta.admin_section_title =
    meta.admin_section_title || "Pilih admin berdasarkan skill";
  heroMeta.admin_section_highlight = meta.admin_section_highlight || "yang kamu butuhkan";
  heroMeta.admin_section_description =
    meta.admin_section_description ||
    "Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda.";
  heroMeta.whatsapp_template = meta.whatsapp_template || defaultWhatsappTemplate;
};

watch(
  activeHeroSection,
  (section) => {
    fillSectionForm(section);
  },
  { immediate: true }
);

const serviceScheduleText = computed(() => {
  const start = String(heroMeta.service_start_time || "08:00").replace(":", ".");
  const end = String(heroMeta.service_end_time || "20:00").replace(":", ".");

  return `${heroMeta.service_day_label || "Senin - Jumat"}, ${start} - ${end} WIB`;
});

const activeAdminsCount = computed(() => {
  return admins.value.filter((admin) => normalizeBoolean(admin.is_active)).length;
});

const approvedTestimonialsCount = computed(() => {
  return testimonials.value.filter((item) => normalizeBoolean(item.is_approved)).length;
});

const pendingTestimonialsCount = computed(() => {
  return testimonials.value.filter((item) => !normalizeBoolean(item.is_approved)).length;
});

const totalSkillsCount = computed(() => {
  const skills = new Set();

  admins.value.forEach((admin) => {
    const list = Array.isArray(admin.skills)
      ? admin.skills
      : skillsTextToArray(admin.skills_text);

    list.forEach((skill) => skills.add(skill));
  });

  return skills.size;
});

const averageRating = computed(() => {
  const approvedTestimonials = testimonials.value.filter((item) =>
    normalizeBoolean(item.is_approved)
  );

  if (!approvedTestimonials.length) return "0.0";

  const total = approvedTestimonials.reduce(
    (sum, item) => sum + Number(item.rating || 0),
    0
  );

  return (total / approvedTestimonials.length).toFixed(1);
});

const stats = computed(() => [
  {
    label: "Hero Section",
    value: activeHeroSection.value?.is_active ? "Aktif" : "Nonaktif",
    helper: activeHeroSection.value
      ? "Bagian utama halaman konsultasi"
      : "Belum ada data hero",
    icon: "hero",
  },
  {
    label: "Admin Aktif",
    value: activeAdminsCount.value,
    helper: `${admins.value.length} total admin konsultasi`,
    icon: "admin",
  },
  {
    label: "Approved",
    value: approvedTestimonialsCount.value,
    helper: `${pendingTestimonialsCount.value} pending review`,
    icon: "testimonial",
  },
  {
    label: "Rata-rata Rating",
    value: averageRating.value,
    helper: "Hanya dari testimoni approved",
    icon: "rating",
  },
]);

const quickNavigation = computed(() => [
  {
    label: "Hero & Jadwal",
    href: "#hero-jadwal",
    helper: serviceScheduleText.value,
    icon: "🎯",
  },
  {
    label: "Admin Konsultasi",
    href: "#admin-konsultasi",
    helper: `${activeAdminsCount.value} admin aktif`,
    icon: "👥",
  },
  {
    label: "Testimoni",
    href: "#testimoni-konsultasi",
    helper: `${approvedTestimonialsCount.value} approved`,
    icon: "⭐",
  },
  {
    label: "Alur & Ticker",
    href: "#alur-ticker",
    helper: `${steps.value.length} step • ${tickers.value.length} ticker`,
    icon: "🧭",
  },
]);

const operationalSummary = computed(() => [
  {
    label: "Jadwal Aktif",
    value: serviceScheduleText.value,
    tone: "red",
  },
  {
    label: "Admin Siap",
    value: `${activeAdminsCount.value}/${admins.value.length} aktif`,
    tone: "emerald",
  },
  {
    label: "Skill Tersedia",
    value: `${totalSkillsCount.value} bidang`,
    tone: "slate",
  },
]);

const resetConsultationFilters = () => {
  adminSearch.value = "";
  testimonialSearch.value = "";
  testimonialRatingFilter.value = "all";
  testimonialStatusFilter.value = "all";
  stepSearch.value = "";
  tickerSearch.value = "";
};

const filteredAdmins = computed(() => {
  const keyword = adminSearch.value.trim().toLowerCase();

  return admins.value.filter((admin) => {
    const skillText = Array.isArray(admin.skills)
      ? admin.skills.join(" ")
      : admin.skills_text || "";

    return (
      !keyword ||
      String(admin.name || "")
        .toLowerCase()
        .includes(keyword) ||
      String(admin.role || "")
        .toLowerCase()
        .includes(keyword) ||
      String(admin.phone_wa || "")
        .toLowerCase()
        .includes(keyword) ||
      String(admin.phone_display || "")
        .toLowerCase()
        .includes(keyword) ||
      String(admin.label || "")
        .toLowerCase()
        .includes(keyword) ||
      String(skillText || "")
        .toLowerCase()
        .includes(keyword)
    );
  });
});

const filteredTestimonials = computed(() => {
  const keyword = testimonialSearch.value.trim().toLowerCase();

  return testimonials.value.filter((testimonial) => {
    const matchKeyword =
      !keyword ||
      String(testimonial.name || "")
        .toLowerCase()
        .includes(keyword) ||
      String(testimonial.role || "")
        .toLowerCase()
        .includes(keyword) ||
      String(testimonial.service_type || "")
        .toLowerCase()
        .includes(keyword) ||
      String(testimonial.message || "")
        .toLowerCase()
        .includes(keyword);

    const matchRating =
      testimonialRatingFilter.value === "all" ||
      Number(testimonial.rating) === Number(testimonialRatingFilter.value);

    const isApproved = normalizeBoolean(testimonial.is_approved);

    const matchStatus =
      testimonialStatusFilter.value === "all" ||
      (testimonialStatusFilter.value === "approved" && isApproved) ||
      (testimonialStatusFilter.value === "pending" && !isApproved);

    return matchKeyword && matchRating && matchStatus;
  });
});

const filteredSteps = computed(() => {
  const keyword = stepSearch.value.trim().toLowerCase();

  if (!keyword) return steps.value;

  return steps.value.filter((step) => {
    return (
      String(step.number || "")
        .toLowerCase()
        .includes(keyword) ||
      String(step.title || "")
        .toLowerCase()
        .includes(keyword) ||
      String(step.description || "")
        .toLowerCase()
        .includes(keyword)
    );
  });
});

const filteredTickers = computed(() => {
  const keyword = tickerSearch.value.trim().toLowerCase();

  if (!keyword) return tickers.value;

  return tickers.value.filter((ticker) => {
    return String(ticker.label || "")
      .toLowerCase()
      .includes(keyword);
  });
});

const adminForm = useForm({
  label: "",
  name: "",
  phone_display: "",
  phone_wa: "",
  role: "Admin Konsultasi",
  emoji: "💬",
  badge: "",
  skills_text: "",
  is_active: true,
  sort_order: 0,
  day: "Senin",
  start_time: "08:00",
  end_time: "20:00",
});

const adminEditForm = useForm({
  label: "",
  name: "",
  phone_display: "",
  phone_wa: "",
  role: "Admin Konsultasi",
  emoji: "💬",
  badge: "",
  skills_text: "",
  is_active: true,
  sort_order: 0,
  day: "Senin",
  start_time: "08:00",
  end_time: "20:00",
});

const stepForm = useForm({
  number: "",
  title: "",
  description: "",
  is_active: true,
  sort_order: 0,
});

const stepEditForm = useForm({
  number: "",
  title: "",
  description: "",
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

const resetAdminForm = () => {
  adminForm.reset();
  adminForm.label = "";
  adminForm.name = "";
  adminForm.phone_display = "";
  adminForm.phone_wa = "";
  adminForm.role = "Admin Konsultasi";
  adminForm.emoji = "💬";
  adminForm.badge = "";
  adminForm.skills_text = "";
  adminForm.is_active = true;
  adminForm.sort_order = 0;
  adminForm.day = "Senin";
  adminForm.start_time = "08:00";
  adminForm.end_time = "20:00";
};

const resetStepForm = () => {
  stepForm.reset();
  stepForm.number = "";
  stepForm.title = "";
  stepForm.description = "";
  stepForm.is_active = true;
  stepForm.sort_order = 0;
};

const resetTickerForm = () => {
  tickerForm.reset();
  tickerForm.label = "";
  tickerForm.is_active = true;
  tickerForm.sort_order = 0;
};

const updateHeroSection = () => {
  if (!activeHeroSection.value) return;

  sectionForm.meta = {
    status_card_title: heroMeta.status_card_title,
    status_card_subtitle: heroMeta.status_card_subtitle,
    schedule_card_title: heroMeta.schedule_card_title,
    schedule_card_subtitle: heroMeta.schedule_card_subtitle,
    service_days: [...heroMeta.service_days],
    service_day_label: heroMeta.service_day_label,
    service_start_time: heroMeta.service_start_time,
    service_end_time: heroMeta.service_end_time,
    admin_section_badge: heroMeta.admin_section_badge,
    admin_section_title: heroMeta.admin_section_title,
    admin_section_highlight: heroMeta.admin_section_highlight,
    admin_section_description: heroMeta.admin_section_description,
    whatsapp_template: heroMeta.whatsapp_template,
  };

  sectionForm.put(`/admin/konsultasi/sections/${activeHeroSection.value.id}`, {
    preserveScroll: true,
  });
};

const storeAdmin = () => {
  adminForm.post("/admin/konsultasi/admins", {
    preserveScroll: true,
    onSuccess: () => resetAdminForm(),
  });
};

const editAdmin = (admin) => {
  editingAdmin.value = admin;

  adminEditForm.label = admin.label || "";
  adminEditForm.name = admin.name || "";
  adminEditForm.phone_display = admin.phone_display || "";
  adminEditForm.phone_wa = admin.phone_wa || "";
  adminEditForm.role = admin.role || "Admin Konsultasi";
  adminEditForm.emoji = admin.emoji || "💬";
  adminEditForm.badge = admin.badge || "";
  adminEditForm.skills_text = admin.skills_text || skillsArrayToText(admin.skills);
  adminEditForm.is_active = normalizeBoolean(admin.is_active);
  adminEditForm.sort_order = Number(admin.sort_order || 0);
  adminEditForm.day = admin.day || "Senin";
  adminEditForm.start_time = admin.start_time || "08:00";
  adminEditForm.end_time = admin.end_time || "20:00";
};

const cancelEditAdmin = () => {
  editingAdmin.value = null;
  adminEditForm.reset();
};

const updateAdmin = () => {
  if (!editingAdmin.value) return;

  adminEditForm.put(`/admin/konsultasi/admins/${editingAdmin.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditAdmin(),
  });
};

const deleteAdmin = (admin) => {
  if (!confirm(`Hapus admin konsultasi "${admin.name}"?`)) return;

  router.delete(`/admin/konsultasi/admins/${admin.id}`, {
    preserveScroll: true,
  });
};

const approveTestimonial = (testimonial) => {
  if (
    !confirm(
      `Approve dan tampilkan testimoni dari "${testimonial.name}" di halaman user?`
    )
  ) {
    return;
  }

  router.patch(
    `/admin/konsultasi/testimonials/${testimonial.id}/approve`,
    {},
    {
      preserveScroll: true,
    }
  );
};

const unapproveTestimonial = (testimonial) => {
  if (!confirm(`Sembunyikan testimoni dari "${testimonial.name}" dari halaman user?`)) {
    return;
  }

  router.patch(
    `/admin/konsultasi/testimonials/${testimonial.id}/unapprove`,
    {},
    {
      preserveScroll: true,
    }
  );
};

const deleteTestimonial = (testimonial) => {
  if (!confirm(`Hapus permanen testimoni dari "${testimonial.name}"?`)) return;

  router.delete(`/admin/konsultasi/testimonials/${testimonial.id}`, {
    preserveScroll: true,
  });
};

const storeStep = () => {
  stepForm.post("/admin/konsultasi/steps", {
    preserveScroll: true,
    onSuccess: () => resetStepForm(),
  });
};

const editStep = (step) => {
  editingStep.value = step;

  stepEditForm.number = step.number || "";
  stepEditForm.title = step.title || "";
  stepEditForm.description = step.description || "";
  stepEditForm.is_active = normalizeBoolean(step.is_active);
  stepEditForm.sort_order = Number(step.sort_order || 0);
};

const cancelEditStep = () => {
  editingStep.value = null;
  stepEditForm.reset();
};

const updateStep = () => {
  if (!editingStep.value) return;

  stepEditForm.put(`/admin/konsultasi/steps/${editingStep.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditStep(),
  });
};

const deleteStep = (step) => {
  if (!confirm(`Hapus step "${step.title}"?`)) return;

  router.delete(`/admin/konsultasi/steps/${step.id}`, {
    preserveScroll: true,
  });
};

const storeTicker = () => {
  tickerForm.post("/admin/konsultasi/tickers", {
    preserveScroll: true,
    onSuccess: () => resetTickerForm(),
  });
};

const editTicker = (ticker) => {
  editingTicker.value = ticker;

  tickerEditForm.label = ticker.label || "";
  tickerEditForm.is_active = normalizeBoolean(ticker.is_active);
  tickerEditForm.sort_order = Number(ticker.sort_order || 0);
};

const cancelEditTicker = () => {
  editingTicker.value = null;
  tickerEditForm.reset();
};

const updateTicker = () => {
  if (!editingTicker.value) return;

  tickerEditForm.put(`/admin/konsultasi/tickers/${editingTicker.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditTicker(),
  });
};

const deleteTicker = (ticker) => {
  if (!confirm(`Hapus ticker "${ticker.label}"?`)) return;

  router.delete(`/admin/konsultasi/tickers/${ticker.id}`, {
    preserveScroll: true,
  });
};

const addSkillToForm = (form, skill) => {
  const current = skillsTextToArray(form.skills_text);

  if (!current.includes(skill)) {
    current.push(skill);
  }

  form.skills_text = current.join("\n");
};
</script>

<template>
  <Head title="Custom Konsultasi" />

  <div class="custom-konsultasi-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8">
    <!-- Header -->
    <section
      class="relative overflow-hidden rounded-[2.25rem] border border-slate-900 bg-[radial-gradient(circle_at_top_right,rgba(248,113,113,0.28),transparent_32%),linear-gradient(135deg,#020617,#0f172a_46%,#7f1d1d)] p-5 text-white shadow-[0_28px_80px_rgba(2,6,23,0.26)] sm:p-8 lg:p-10"
      data-aos="fade-up"
    >
      <div
        class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-red-500/25 blur-[92px]"
      />
      <div
        class="pointer-events-none absolute -bottom-28 -left-20 h-72 w-72 rounded-full bg-white/10 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute left-1/2 top-0 h-px w-2/3 -translate-x-1/2 bg-gradient-to-r from-transparent via-white/30 to-transparent"
      />

      <div
        class="relative z-10 grid gap-8 xl:grid-cols-[minmax(0,1fr)_25rem] xl:items-end"
      >
        <div>
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.12em] text-red-100 ring-1 ring-white/15 backdrop-blur"
          >
            <span
              class="h-2 w-2 rounded-full bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.16)]"
            />
            Admin / Custom Konsultasi
          </div>

          <h1
            class="max-w-4xl text-[2.05rem] font-black leading-[1.05] tracking-[-0.05em] sm:text-[2.75rem] lg:text-[3.35rem]"
          >
            Kelola pengalaman
            <span class="block text-red-300">Konsultasi HMPS RPL</span>
          </h1>

          <p
            class="mt-5 max-w-3xl text-left text-sm leading-8 text-slate-300 sm:text-justify sm:text-base"
          >
            Atur hero section, jadwal global, template WhatsApp, skill admin, approval
            testimoni, alur konsultasi, dan ticker dalam satu dashboard yang rapi, cepat
            dibaca, dan nyaman digunakan.
          </p>

          <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a
              href="/konsultasi"
              target="_blank"
              rel="noopener noreferrer"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-sm font-black text-slate-950 shadow-[0_14px_34px_rgba(255,255,255,0.12)] transition hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(239,68,68,0.14),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">Preview Konsultasi</span>
            </a>

            <button
              type="button"
              class="inline-flex min-h-[52px] items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 text-sm font-black text-white backdrop-blur transition hover:-translate-y-[2px] hover:bg-white/15"
              @click="resetConsultationFilters"
            >
              Reset Semua Filter
            </button>
          </div>
        </div>

        <div
          class="rounded-[1.75rem] border border-white/15 bg-white/10 p-4 shadow-[0_18px_46px_rgba(0,0,0,0.18)] backdrop-blur-xl sm:p-5"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-black uppercase tracking-[0.14em] text-red-100">
                Ringkasan Layanan
              </p>
              <h2 class="mt-2 text-xl font-black tracking-[-0.035em] text-white">
                Operasional Konsultasi
              </h2>
            </div>
            <span
              class="rounded-2xl bg-red-500/20 px-3 py-2 text-xl ring-1 ring-red-300/20"
            >
              💬
            </span>
          </div>

          <div class="mt-5 grid gap-3">
            <div
              v-for="item in operationalSummary"
              :key="item.label"
              class="rounded-2xl border border-white/10 bg-white/10 p-4"
            >
              <p
                class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-300"
              >
                {{ item.label }}
              </p>
              <p class="mt-1 text-sm font-black leading-6 text-white">
                {{ item.value }}
              </p>
            </div>
          </div>
        </div>
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
        data-aos="fade-up"
      >
        <div
          class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-xl text-red-700 ring-1 ring-red-100 transition group-hover:scale-105"
        >
          <span v-if="item.icon === 'hero'">🎯</span>
          <span v-else-if="item.icon === 'admin'">👥</span>
          <span v-else-if="item.icon === 'testimonial'">💬</span>
          <span v-else>⭐</span>
        </div>

        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
          {{ item.label }}
        </p>

        <h3 class="mt-3 truncate text-2xl font-black tracking-[-0.04em] text-slate-950">
          {{ item.value }}
        </h3>

        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
          {{ item.helper }}
        </p>
      </div>
    </section>

    <!-- Quick Navigation -->
    <section
      class="konsultasi-quick-nav grid gap-3 rounded-[2rem] border border-slate-200 bg-white p-4 shadow-[0_16px_44px_rgba(15,23,42,0.055)] sm:grid-cols-2 lg:grid-cols-4"
      data-aos="fade-up"
    >
      <a
        v-for="item in quickNavigation"
        :key="item.href"
        :href="item.href"
        class="group flex items-center gap-4 rounded-[1.45rem] border border-slate-200 bg-slate-50/80 p-4 transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:bg-white hover:shadow-[0_18px_40px_rgba(15,23,42,0.08)]"
      >
        <span
          class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-xl shadow-sm ring-1 ring-slate-200 transition group-hover:scale-105"
        >
          {{ item.icon }}
        </span>
        <span class="min-w-0">
          <span class="block text-sm font-black tracking-[-0.02em] text-slate-950">
            {{ item.label }}
          </span>
          <span class="mt-1 block truncate text-xs font-bold text-slate-500">
            {{ item.helper }}
          </span>
        </span>
      </a>
    </section>

    <!-- Hero and Global Schedule -->
    <section
      id="hero-jadwal"
      class="scroll-mt-28 overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      data-aos="fade-up"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Hero & Jadwal Global
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Pengaturan Halaman Konsultasi
            </h2>

            <p
              class="mt-2 max-w-3xl text-left text-sm leading-7 text-slate-500 sm:text-justify"
            >
              Jadwal konsultasi berlaku global untuk semua admin. User dapat memilih admin
              berdasarkan skill, sementara jam layanan dan template pesan WhatsApp diatur
              melalui bagian ini.
            </p>
          </div>

          <span
            class="inline-flex w-fit rounded-full border border-red-200 bg-red-50 px-4 py-2 text-xs font-black uppercase tracking-[0.08em] text-red-700"
          >
            {{ serviceScheduleText }}
          </span>
        </div>

        <div
          v-if="!activeHeroSection"
          class="mt-6 rounded-[1.35rem] border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold text-amber-700"
        >
          Data hero section belum tersedia. Pastikan controller admin membuat section
          default dengan key <span class="font-black">hero</span>.
        </div>

        <form class="mt-6 grid gap-5 lg:grid-cols-2" @submit.prevent="updateHeroSection">
          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">Badge</label>
            <input
              v-model="sectionForm.badge"
              type="text"
              placeholder="Layanan Konsultasi HMPS RPL"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Urutan Tampil
            </label>
            <input
              v-model="sectionForm.sort_order"
              type="number"
              min="0"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">Judul</label>
            <input
              v-model="sectionForm.title"
              type="text"
              placeholder="Pilih admin konsultasi"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Judul Highlight
            </label>
            <input
              v-model="sectionForm.title_highlight"
              type="text"
              placeholder="sesuai kebutuhanmu"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Deskripsi
            </label>
            <textarea
              v-model="sectionForm.description"
              rows="4"
              placeholder="Tuliskan deskripsi hero konsultasi..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Label Tombol Utama
            </label>
            <input
              v-model="sectionForm.primary_button_label"
              type="text"
              placeholder="Pilih Admin Konsultasi"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              URL Tombol Utama
            </label>
            <input
              v-model="sectionForm.primary_button_url"
              type="text"
              placeholder="#pilih-admin"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              Label Tombol Kedua
            </label>
            <input
              v-model="sectionForm.secondary_button_label"
              type="text"
              placeholder="Konsultasi via WhatsApp"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-extrabold text-slate-800">
              URL Tombol Kedua
            </label>
            <input
              v-model="sectionForm.secondary_button_url"
              type="text"
              placeholder="whatsapp-selected"
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div
            class="rounded-[1.5rem] border border-red-100 bg-red-50/60 p-5 lg:col-span-2"
          >
            <div
              class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
            >
              <div>
                <p class="text-xs font-black uppercase tracking-[0.12em] text-red-700">
                  Jadwal Global Konsultasi
                </p>
                <p class="mt-1 text-sm font-semibold leading-7 text-slate-600">
                  Jadwal ini berlaku untuk semua admin. Tidak ada jadwal per admin.
                </p>
              </div>

              <span
                class="inline-flex w-fit rounded-full border border-red-200 bg-white px-3 py-1 text-xs font-black text-red-700"
              >
                {{ serviceScheduleText }}
              </span>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1fr_0.8fr]">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Hari Layanan
                </label>

                <div class="flex flex-wrap gap-2">
                  <label
                    v-for="day in dayOptions"
                    :key="day"
                    class="flex cursor-pointer items-center gap-2 rounded-2xl border px-3.5 py-2 text-sm font-black transition"
                    :class="
                      heroMeta.service_days.includes(day)
                        ? 'border-red-200 bg-red-600 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-700'
                    "
                  >
                    <input
                      v-model="heroMeta.service_days"
                      type="checkbox"
                      :value="day"
                      class="sr-only"
                    />
                    {{ day }}
                  </label>
                </div>
              </div>

              <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                <div>
                  <label class="mb-2 block text-sm font-extrabold text-slate-800">
                    Label Hari
                  </label>
                  <input
                    v-model="heroMeta.service_day_label"
                    type="text"
                    placeholder="Senin - Jumat"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <div>
                  <label class="mb-2 block text-sm font-extrabold text-slate-800">
                    Jam Mulai
                  </label>
                  <input
                    v-model="heroMeta.service_start_time"
                    type="time"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <div>
                  <label class="mb-2 block text-sm font-extrabold text-slate-800">
                    Jam Selesai
                  </label>
                  <input
                    v-model="heroMeta.service_end_time"
                    type="time"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />
                </div>
              </div>
            </div>
          </div>

          <div
            class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/70 p-5 lg:col-span-2"
          >
            <div
              class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
            >
              <div>
                <p
                  class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700"
                >
                  Template Pesan WhatsApp
                </p>
                <p class="mt-1 text-sm font-semibold leading-7 text-slate-600">
                  Template ini dipakai saat user menekan tombol Konsultasikan Sekarang.
                </p>
              </div>

              <span
                class="inline-flex w-fit rounded-full border border-emerald-200 bg-white px-3 py-1 text-xs font-black text-emerald-700"
              >
                Custom WA Text
              </span>
            </div>

            <textarea
              v-model="heroMeta.whatsapp_template"
              rows="8"
              placeholder="Halo {admin_name}, saya ingin konsultasi..."
              class="w-full rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
            />

            <div class="mt-4 rounded-2xl border border-emerald-200 bg-white p-4">
              <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                Placeholder yang bisa digunakan
              </p>

              <div class="mt-3 flex flex-wrap gap-2">
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {admin_name}
                </span>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {admin_skills}
                </span>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {admin_role}
                </span>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {service_days}
                </span>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {service_time}
                </span>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {current_day}
                </span>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-700"
                >
                  {current_time}
                </span>
              </div>
            </div>
          </div>

          <div
            class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 lg:col-span-2"
          >
            <p class="mb-4 text-xs font-black uppercase tracking-[0.12em] text-slate-500">
              Section Pilih Admin di Halaman User
            </p>

            <div class="grid gap-4 lg:grid-cols-2">
              <input
                v-model="heroMeta.admin_section_badge"
                type="text"
                placeholder="Pilih Admin"
                class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <input
                v-model="heroMeta.admin_section_highlight"
                type="text"
                placeholder="yang kamu butuhkan"
                class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <input
                v-model="heroMeta.admin_section_title"
                type="text"
                placeholder="Pilih admin berdasarkan skill"
                class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100 lg:col-span-2"
              />

              <textarea
                v-model="heroMeta.admin_section_description"
                rows="3"
                placeholder="Deskripsi section pilih admin..."
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100 lg:col-span-2"
              />
            </div>
          </div>

          <div
            class="flex flex-col gap-3 border-t border-slate-200 pt-5 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between"
          >
            <label
              class="flex h-[3.1rem] w-fit items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-slate-700"
            >
              <input
                v-model="sectionForm.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
              />
              Aktif
            </label>

            <button
              type="submit"
              :disabled="sectionForm.processing || !activeHeroSection"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">
                {{
                  sectionForm.processing
                    ? "Menyimpan..."
                    : "Simpan Hero, Jadwal & Template WA"
                }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </section>
    <!-- Admin Management -->
    <section
      id="admin-konsultasi"
      class="scroll-mt-28 grid gap-6 xl:grid-cols-[0.42fr_0.58fr]"
      data-aos="fade-up"
    >
      <!-- Add Admin -->
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
              Tambah Admin
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Tambah Admin Konsultasi
            </h2>

            <p class="mt-2 text-left text-sm leading-7 text-slate-500 sm:text-justify">
              Tambahkan admin beserta skill-nya. Jadwal tidak lagi diatur per admin,
              karena jadwal layanan sudah dibuat global di bagian Hero & Jadwal.
            </p>
          </div>

          <form class="grid gap-5" @submit.prevent="storeAdmin">
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Label
                </label>

                <input
                  v-model="adminForm.label"
                  type="text"
                  placeholder="Admin 1"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Emoji
                </label>

                <input
                  v-model="adminForm.emoji"
                  type="text"
                  placeholder="💬"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Nama Admin
                <span class="text-red-600">*</span>
              </label>

              <input
                v-model="adminForm.name"
                type="text"
                placeholder="Nama lengkap admin"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <p v-if="adminForm.errors.name" class="mt-2 text-xs font-bold text-red-600">
                {{ adminForm.errors.name }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Role
              </label>

              <input
                v-model="adminForm.role"
                type="text"
                placeholder="Admin Konsultasi"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <label class="block text-sm font-extrabold text-slate-800">
                  Skill Admin
                </label>

                <span class="text-xs font-bold text-slate-400">
                  Pisahkan dengan enter, koma, titik koma, atau garis vertikal
                </span>
              </div>

              <textarea
                v-model="adminForm.skills_text"
                rows="5"
                placeholder="Website&#10;Desain Grafis&#10;Editing Video"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="adminForm.errors.skills_text"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ adminForm.errors.skills_text }}
              </p>

              <div class="mt-3 flex flex-wrap gap-2">
                <button
                  v-for="skill in quickSkillPresets"
                  :key="skill"
                  type="button"
                  class="rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 transition hover:border-red-200 hover:bg-red-100"
                  @click="addSkillToForm(adminForm, skill)"
                >
                  + {{ skill }}
                </button>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Nomor Display
                </label>

                <input
                  v-model="adminForm.phone_display"
                  type="text"
                  placeholder="087879175646"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Nomor WhatsApp
                  <span class="text-red-600">*</span>
                </label>

                <input
                  v-model="adminForm.phone_wa"
                  type="text"
                  placeholder="6287879175646"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="adminForm.errors.phone_wa"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ adminForm.errors.phone_wa }}
                </p>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Badge Opsional
              </label>

              <input
                v-model="adminForm.badge"
                type="text"
                placeholder="Admin Utama / Website Specialist"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Urutan Tampil
                </label>

                <input
                  v-model="adminForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="flex items-end">
                <label
                  class="flex h-[3.25rem] w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-slate-700"
                >
                  <input
                    v-model="adminForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Aktif
                </label>
              </div>
            </div>

            <button
              type="submit"
              :disabled="adminForm.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <span class="relative z-10">
                {{ adminForm.processing ? "Menyimpan..." : "Tambah Admin" }}
              </span>
            </button>
          </form>
        </div>
      </div>

      <!-- Admin List -->
      <div
        class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_18px_52px_rgba(15,23,42,0.065)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="grid gap-5">
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Daftar Admin
              </div>

              <div
                class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between"
              >
                <div class="min-w-0">
                  <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                    Daftar Admin Konsultasi
                  </h2>

                  <p
                    class="mt-2 max-w-3xl text-left text-sm leading-7 text-slate-500 sm:text-justify"
                  >
                    Kelola admin dan skill-nya. Skill akan tampil di halaman user agar
                    pengunjung bisa memilih admin yang paling sesuai dengan kebutuhan.
                  </p>
                </div>

                <span
                  class="inline-flex w-fit shrink-0 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600"
                >
                  {{ admins.length }} Total Admin
                </span>
              </div>
            </div>

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
                  stroke-width="2.35"
                  d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                />
              </svg>

              <input
                v-model="adminSearch"
                type="text"
                placeholder="Cari nama, role, skill, label, atau nomor WhatsApp..."
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 pl-11 pr-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div class="flex flex-wrap gap-2">
              <span
                class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
              >
                {{ filteredAdmins.length }} hasil
              </span>

              <span
                class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700 ring-1 ring-emerald-100"
              >
                {{ activeAdminsCount }} aktif
              </span>

              <span
                class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 ring-1 ring-red-100"
              >
                {{ totalSkillsCount }} skill
              </span>
            </div>
          </div>

          <div class="mt-4 flex justify-end">
            <button
              type="button"
              class="inline-flex min-h-[40px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
              @click="adminSearch = ''"
            >
              Reset Pencarian Admin
            </button>
          </div>

          <div class="mt-6 grid gap-4">
            <div
              v-for="admin in filteredAdmins"
              :key="admin.id"
              class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-slate-50/80 transition duration-300 hover:border-red-200 hover:bg-white hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
            >
              <div v-if="editingAdmin?.id !== admin.id" class="p-4 sm:p-5">
                <div
                  class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_10.5rem] 2xl:items-start"
                >
                  <div class="min-w-0">
                    <div class="flex items-start gap-4">
                      <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-slate-200"
                      >
                        {{ admin.emoji || "💬" }}
                      </div>

                      <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap gap-2">
                          <span
                            v-if="admin.label"
                            class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                          >
                            {{ admin.label }}
                          </span>

                          <span
                            class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                            :class="
                              normalizeBoolean(admin.is_active)
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-slate-200 bg-slate-100 text-slate-600'
                            "
                          >
                            {{ normalizeBoolean(admin.is_active) ? "Aktif" : "Nonaktif" }}
                          </span>

                          <span
                            class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                          >
                            Urutan {{ admin.sort_order }}
                          </span>
                        </div>

                        <h3
                          class="mt-3 truncate text-lg font-black tracking-[-0.03em] text-slate-950"
                        >
                          {{ admin.name }}
                        </h3>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                          {{ admin.role || "Admin Konsultasi" }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                          <span
                            v-for="skill in skillList(admin)"
                            :key="`${admin.id}-${skill}`"
                            class="rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-[0.72rem] font-black text-red-700"
                          >
                            {{ skill }}
                          </span>

                          <span
                            v-if="!skillList(admin).length"
                            class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[0.72rem] font-black text-slate-500"
                          >
                            Belum ada skill
                          </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                          <span
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600"
                          >
                            WhatsApp:
                            <span class="font-black text-slate-950">
                              {{ admin.phone_display || admin.phone_wa || "-" }}
                            </span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    class="grid grid-cols-2 gap-2 sm:flex sm:flex-row 2xl:w-[10.5rem] 2xl:flex-col"
                  >
                    <button
                      type="button"
                      class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-[1px] hover:bg-red-50 hover:text-red-700"
                      @click="editAdmin(admin)"
                    >
                      Edit
                    </button>

                    <button
                      type="button"
                      class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:-translate-y-[1px] hover:bg-red-100"
                      @click="deleteAdmin(admin)"
                    >
                      Hapus
                    </button>
                  </div>
                </div>
              </div>

              <form
                v-else
                class="grid gap-4 bg-white p-4 sm:p-5"
                @submit.prevent="updateAdmin"
              >
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Admin
                    </span>

                    <h3 class="mt-2 text-lg font-black text-slate-950">
                      Edit {{ admin.name }}
                    </h3>
                  </div>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditAdmin"
                  >
                    Batal Edit
                  </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="adminEditForm.label"
                    type="text"
                    placeholder="Label"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <input
                    v-model="adminEditForm.emoji"
                    type="text"
                    placeholder="Emoji"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <input
                  v-model="adminEditForm.name"
                  type="text"
                  placeholder="Nama admin"
                  class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <input
                  v-model="adminEditForm.role"
                  type="text"
                  placeholder="Role"
                  class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <div>
                  <textarea
                    v-model="adminEditForm.skills_text"
                    rows="5"
                    placeholder="Website&#10;Desain Grafis&#10;Editing Video"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <div class="mt-3 flex flex-wrap gap-2">
                    <button
                      v-for="skill in quickSkillPresets"
                      :key="`edit-${skill}`"
                      type="button"
                      class="rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 transition hover:border-red-200 hover:bg-red-100"
                      @click="addSkillToForm(adminEditForm, skill)"
                    >
                      + {{ skill }}
                    </button>
                  </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="adminEditForm.phone_display"
                    type="text"
                    placeholder="Nomor display"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <input
                    v-model="adminEditForm.phone_wa"
                    type="text"
                    placeholder="Nomor WhatsApp"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <input
                  v-model="adminEditForm.badge"
                  type="text"
                  placeholder="Badge opsional"
                  class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="flex flex-wrap items-center gap-3">
                    <input
                      v-model="adminEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <label
                      class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                    >
                      <input
                        v-model="adminEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                  </div>

                  <div class="flex w-full flex-col-reverse gap-2 sm:w-auto sm:flex-row">
                    <button
                      type="button"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditAdmin"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="adminEditForm.processing"
                      class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                    >
                      {{ adminEditForm.processing ? "Menyimpan..." : "Simpan Admin" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!filteredAdmins.length"
              class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
            >
              <h3 class="text-lg font-black text-slate-950">
                Admin konsultasi tidak ditemukan
              </h3>

              <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                Tambahkan admin baru atau ubah pencarian.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Testimonial Approval Management -->
    <section
      id="testimoni-konsultasi"
      class="scroll-mt-28 overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_18px_52px_rgba(15,23,42,0.065)]"
      data-aos="fade-up"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#f59e0b,#ef4444)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="mb-6">
          <div
            class="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/15 bg-amber-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-amber-700"
          >
            <span class="h-2 w-2 rounded-full bg-amber-500" />
            Approval Testimoni
          </div>

          <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Kelola Approval Testimoni Konsultasi
              </h2>

              <p
                class="mt-2 max-w-4xl text-left text-sm leading-7 text-slate-500 sm:text-justify"
              >
                Testimoni hanya bisa dikirim oleh user melalui halaman Konsultasi. Admin
                tidak dapat menambah atau mengedit isi testimoni agar keaslian ulasan
                tetap terjaga. Admin hanya dapat menyetujui, menyembunyikan, atau
                menghapus testimoni.
              </p>
            </div>

            <div class="flex flex-wrap gap-2">
              <span
                class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
              >
                {{ testimonials.length }} Total
              </span>

              <span
                class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700 ring-1 ring-emerald-100"
              >
                {{ approvedTestimonialsCount }} Approved
              </span>

              <span
                class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700 ring-1 ring-amber-100"
              >
                {{ pendingTestimonialsCount }} Pending
              </span>

              <span
                class="rounded-full bg-orange-50 px-3 py-1.5 text-xs font-black text-orange-700 ring-1 ring-orange-100"
              >
                {{ averageRating }} Rating
              </span>
            </div>
          </div>
        </div>

        <div class="grid gap-3 xl:grid-cols-[1fr_auto_auto]">
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
                stroke-width="2.35"
                d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
              />
            </svg>

            <input
              v-model="testimonialSearch"
              type="text"
              placeholder="Cari nama, role, jenis konsultasi, atau isi testimoni..."
              class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <select
            v-model="testimonialStatusFilter"
            class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-black text-slate-700 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
          >
            <option value="all">Semua Status</option>
            <option value="approved">Approved / Tampil</option>
            <option value="pending">Pending Review</option>
          </select>

          <select
            v-model="testimonialRatingFilter"
            class="h-[3.25rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-black text-slate-700 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
          >
            <option value="all">Semua Rating</option>
            <option
              v-for="rating in ratingOptions"
              :key="`filter-${rating}`"
              :value="rating"
            >
              {{ rating }} Bintang
            </option>
          </select>
        </div>

        <div class="mt-4 flex justify-end">
          <button
            type="button"
            class="inline-flex min-h-[40px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 shadow-sm transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700"
            @click="
              testimonialSearch = '';
              testimonialRatingFilter = 'all';
              testimonialStatusFilter = 'all';
            "
          >
            Reset Filter Testimoni
          </button>
        </div>

        <div class="mt-6 grid gap-4">
          <div
            v-for="testimonial in filteredTestimonials"
            :key="testimonial.id"
            class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-slate-50/80 transition duration-300 hover:border-amber-200 hover:bg-white hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
          >
            <div class="p-4 sm:p-5">
              <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_13rem] xl:items-start">
                <div class="min-w-0">
                  <div class="flex items-start gap-4">
                    <div
                      class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-slate-200"
                    >
                      {{ testimonial.emoji || "💬" }}
                    </div>

                    <div class="min-w-0 flex-1">
                      <div class="flex flex-wrap gap-2">
                        <span
                          class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[0.68rem] font-black text-amber-700"
                        >
                          {{ testimonial.rating }} ⭐
                        </span>

                        <span
                          class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                          :class="statusClass(testimonial.is_approved)"
                        >
                          {{ statusText(testimonial.is_approved) }}
                        </span>

                        <span
                          class="rounded-full border border-red-100 bg-red-50 px-3 py-1 text-[0.68rem] font-black text-red-700"
                        >
                          {{ testimonial.service_type }}
                        </span>
                      </div>

                      <h3
                        class="mt-3 text-lg font-black tracking-[-0.03em] text-slate-950"
                      >
                        {{ testimonial.name }}
                      </h3>

                      <p class="mt-1 text-sm font-bold text-slate-500">
                        {{ testimonial.role || "Pengguna Layanan" }}
                      </p>

                      <p class="mt-3 text-justify text-sm leading-7 text-slate-600">
                        “{{ testimonial.message }}”
                      </p>

                      <div class="mt-4 flex flex-wrap gap-2">
                        <span
                          class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"
                        >
                          Dikirim: {{ testimonial.created_at || "Baru saja" }}
                        </span>

                        <span
                          class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"
                        >
                          Urutan {{ testimonial.sort_order }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="grid gap-2">
                  <button
                    v-if="!normalizeBoolean(testimonial.is_approved)"
                    type="button"
                    class="inline-flex min-h-[46px] w-full items-center justify-center rounded-2xl bg-emerald-50 px-4 text-xs font-black text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-100"
                    @click="approveTestimonial(testimonial)"
                  >
                    Approve / Tampilkan
                  </button>

                  <button
                    v-else
                    type="button"
                    class="inline-flex min-h-[46px] w-full items-center justify-center rounded-2xl bg-amber-50 px-4 text-xs font-black text-amber-700 ring-1 ring-amber-100 transition hover:bg-amber-100"
                    @click="unapproveTestimonial(testimonial)"
                  >
                    Unapprove / Sembunyikan
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[46px] w-full items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteTestimonial(testimonial)"
                  >
                    Hapus Testimoni
                  </button>

                  <div
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold leading-6 text-slate-500"
                  >
                    <span class="font-black text-slate-700">Catatan:</span>
                    admin hanya dapat mengubah status publikasi atau menghapus testimoni.
                    Isi ulasan tidak dapat diedit.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="!filteredTestimonials.length"
            class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
          >
            <h3 class="text-lg font-black text-slate-950">Testimoni tidak ditemukan</h3>

            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
              Belum ada testimoni dari user atau filter pencarian/status tidak sesuai.
            </p>
          </div>
        </div>
      </div>
    </section>
    <!-- Steps and Tickers -->
    <section
      id="alur-ticker"
      class="scroll-mt-28 grid gap-6 xl:grid-cols-2"
      data-aos="fade-up"
    >
      <!-- Steps -->
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
              Alur Konsultasi
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Kelola Alur Konsultasi
            </h2>

            <p class="mt-2 text-left text-sm leading-7 text-slate-500 sm:text-justify">
              Atur langkah-langkah yang menjelaskan cara user memilih admin, memahami
              jadwal layanan, dan memulai konsultasi melalui WhatsApp.
            </p>
          </div>

          <!-- Add Step -->
          <form
            class="grid gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4"
            @submit.prevent="storeStep"
          >
            <div class="grid gap-4 sm:grid-cols-[0.35fr_0.65fr]">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Nomor
                </label>

                <input
                  v-model="stepForm.number"
                  type="text"
                  placeholder="01"
                  class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="stepForm.errors.number"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ stepForm.errors.number }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Judul Step
                  <span class="text-red-600">*</span>
                </label>

                <input
                  v-model="stepForm.title"
                  type="text"
                  placeholder="Lihat skill admin"
                  class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="stepForm.errors.title"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ stepForm.errors.title }}
                </p>
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Deskripsi
              </label>

              <textarea
                v-model="stepForm.description"
                rows="3"
                placeholder="Deskripsi alur konsultasi..."
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="stepForm.errors.description"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ stepForm.errors.description }}
              </p>
            </div>

            <div
              class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex flex-wrap items-center gap-3">
                <div>
                  <input
                    v-model="stepForm.sort_order"
                    type="number"
                    min="0"
                    placeholder="Urutan"
                    class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="stepForm.errors.sort_order"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ stepForm.errors.sort_order }}
                  </p>
                </div>

                <label
                  class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600"
                >
                  <input
                    v-model="stepForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Aktif
                </label>
              </div>

              <button
                type="submit"
                :disabled="stepForm.processing"
                class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ stepForm.processing ? "Menyimpan..." : "Tambah Step" }}
              </button>
            </div>
          </form>

          <!-- Search Step -->
          <div class="relative mt-5">
            <svg
              class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
              />
            </svg>

            <input
              v-model="stepSearch"
              type="text"
              placeholder="Cari step..."
              class="h-[3rem] w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="mt-3 flex justify-end">
            <button
              type="button"
              class="inline-flex min-h-[38px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
              @click="stepSearch = ''"
            >
              Reset Pencarian Step
            </button>
          </div>

          <!-- Step List -->
          <div class="mt-5 space-y-3">
            <div
              v-for="step in filteredSteps"
              :key="step.id"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
            >
              <div
                v-if="editingStep?.id !== step.id"
                class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-start"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                    >
                      Step {{ step.number || step.sort_order }}
                    </span>

                    <span
                      class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="
                        normalizeBoolean(step.is_active)
                          ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                          : 'border-slate-200 bg-slate-100 text-slate-600'
                      "
                    >
                      {{ normalizeBoolean(step.is_active) ? "Aktif" : "Nonaktif" }}
                    </span>

                    <span
                      class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                    >
                      Urutan {{ step.sort_order }}
                    </span>
                  </div>

                  <h3 class="mt-3 text-base font-black text-slate-950">
                    {{ step.title }}
                  </h3>

                  <p
                    class="mt-2 text-left text-sm leading-7 text-slate-500 sm:text-justify"
                  >
                    {{ step.description }}
                  </p>
                </div>

                <div class="flex shrink-0 gap-2">
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                    @click="editStep(step)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteStep(step)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-4 bg-white p-4" @submit.prevent="updateStep">
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Step
                    </span>

                    <h3 class="mt-2 text-lg font-black text-slate-950">
                      Edit {{ step.title }}
                    </h3>
                  </div>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditStep"
                  >
                    Batal Edit
                  </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-[0.35fr_0.65fr]">
                  <div>
                    <input
                      v-model="stepEditForm.number"
                      type="text"
                      placeholder="Nomor"
                      class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <p
                      v-if="stepEditForm.errors.number"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ stepEditForm.errors.number }}
                    </p>
                  </div>

                  <div>
                    <input
                      v-model="stepEditForm.title"
                      type="text"
                      placeholder="Judul"
                      class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <p
                      v-if="stepEditForm.errors.title"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ stepEditForm.errors.title }}
                    </p>
                  </div>
                </div>

                <div>
                  <textarea
                    v-model="stepEditForm.description"
                    rows="3"
                    placeholder="Deskripsi"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="stepEditForm.errors.description"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ stepEditForm.errors.description }}
                  </p>
                </div>

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="flex flex-wrap items-center gap-3">
                    <input
                      v-model="stepEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <label
                      class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                    >
                      <input
                        v-model="stepEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="button"
                      class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditStep"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="stepEditForm.processing"
                      class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {{ stepEditForm.processing ? "Menyimpan..." : "Simpan" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!filteredSteps.length"
              class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
              <p class="text-sm font-black text-slate-700">Step tidak ditemukan.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tickers -->
      <div
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Ticker Konsultasi
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Kelola Teks Berjalan
            </h2>

            <p class="mt-2 text-left text-sm leading-7 text-slate-500 sm:text-justify">
              Atur teks berjalan yang tampil di halaman konsultasi user.
            </p>
          </div>

          <!-- Add Ticker -->
          <form
            class="grid gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4"
            @submit.prevent="storeTicker"
          >
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Label Ticker
                <span class="text-red-600">*</span>
              </label>

              <input
                v-model="tickerForm.label"
                type="text"
                placeholder="KONSULTASI HMPS RPL"
                class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="tickerForm.errors.label"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ tickerForm.errors.label }}
              </p>
            </div>

            <div
              class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex flex-wrap items-center gap-3">
                <div>
                  <input
                    v-model="tickerForm.sort_order"
                    type="number"
                    min="0"
                    class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="tickerForm.errors.sort_order"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ tickerForm.errors.sort_order }}
                  </p>
                </div>

                <label
                  class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600"
                >
                  <input
                    v-model="tickerForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Aktif
                </label>
              </div>

              <button
                type="submit"
                :disabled="tickerForm.processing"
                class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ tickerForm.processing ? "Menyimpan..." : "Tambah Ticker" }}
              </button>
            </div>
          </form>

          <!-- Search Ticker -->
          <div class="relative mt-5">
            <svg
              class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
              />
            </svg>

            <input
              v-model="tickerSearch"
              type="text"
              placeholder="Cari ticker..."
              class="h-[3rem] w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />
          </div>

          <div class="mt-3 flex justify-end">
            <button
              type="button"
              class="inline-flex min-h-[38px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
              @click="tickerSearch = ''"
            >
              Reset Pencarian Ticker
            </button>
          </div>

          <!-- Ticker List -->
          <div class="mt-5 space-y-3">
            <div
              v-for="ticker in filteredTickers"
              :key="ticker.id"
              class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_14px_34px_rgba(15,23,42,0.06)]"
            >
              <div
                v-if="editingTicker?.id !== ticker.id"
                class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                    >
                      Urutan {{ ticker.sort_order }}
                    </span>

                    <span
                      class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="
                        normalizeBoolean(ticker.is_active)
                          ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                          : 'border-slate-200 bg-slate-100 text-slate-600'
                      "
                    >
                      {{ normalizeBoolean(ticker.is_active) ? "Aktif" : "Nonaktif" }}
                    </span>
                  </div>

                  <h3 class="mt-3 text-base font-black text-slate-950">
                    {{ ticker.label }}
                  </h3>
                </div>

                <div class="flex shrink-0 gap-2">
                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700"
                    @click="editTicker(ticker)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[42px] items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteTicker(ticker)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-4 bg-white p-4" @submit.prevent="updateTicker">
                <div
                  class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div>
                    <span
                      class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                    >
                      Mode Edit Ticker
                    </span>

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

                <div>
                  <input
                    v-model="tickerEditForm.label"
                    type="text"
                    placeholder="Label ticker"
                    class="h-[3.1rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="tickerEditForm.errors.label"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ tickerEditForm.errors.label }}
                  </p>
                </div>

                <div
                  class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="flex flex-wrap items-center gap-3">
                    <input
                      v-model="tickerEditForm.sort_order"
                      type="number"
                      min="0"
                      class="h-[3.1rem] w-28 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-900 outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <label
                      class="flex h-[3.1rem] items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-600"
                    >
                      <input
                        v-model="tickerEditForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      Aktif
                    </label>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="button"
                      class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                      @click="cancelEditTicker"
                    >
                      Batal
                    </button>

                    <button
                      type="submit"
                      :disabled="tickerEditForm.processing"
                      class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {{ tickerEditForm.processing ? "Menyimpan..." : "Simpan" }}
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div
              v-if="!filteredTickers.length"
              class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
              <p class="text-sm font-black text-slate-700">Ticker tidak ditemukan.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
:deep(input),
:deep(textarea),
:deep(select),
:deep(button),
:deep(a) {
  -webkit-tap-highlight-color: transparent;
}

:global(html) {
  scroll-behavior: smooth;
}

@media (max-width: 640px) {
  .scroll-mt-28 {
    scroll-margin-top: 5rem;
  }
}
</style>


<style scoped>
@media (max-width: 639px) {
  .custom-konsultasi-mobile-page,
  .custom-konsultasi-mobile-page * {
    box-sizing: border-box;
  }

  .custom-konsultasi-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-konsultasi-mobile-page :is(section, article, form, div, aside) {
    min-width: 0;
  }

  .custom-konsultasi-mobile-page :is(input, select, textarea, button, a, img, label) {
    max-width: 100%;
  }

  .custom-konsultasi-mobile-page p {
    text-align: left !important;
  }

  .custom-konsultasi-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-konsultasi-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-konsultasi-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .relative.z-10.grid {
    gap: 1rem !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .mt-6.flex {
    margin-top: 1.15rem !important;
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .mt-6.flex a,
  .custom-konsultasi-mobile-page > section:first-of-type .mt-6.flex button {
    width: 100% !important;
    min-height: 46px !important;
    justify-content: center !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .rounded-\[1\.75rem\] {
    border-radius: 1.1rem !important;
    padding: 0.9rem !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .rounded-\[1\.75rem\] .mt-5 {
    margin-top: 0.9rem !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .rounded-\[1\.75rem\] .grid.gap-3 {
    gap: 0.55rem !important;
  }

  .custom-konsultasi-mobile-page > section:first-of-type .rounded-\[1\.75rem\] .grid.gap-3 > div {
    padding: 0.75rem !important;
    border-radius: 1rem !important;
  }

  .custom-konsultasi-mobile-page > section:nth-of-type(2) > div {
    border-radius: 1.1rem !important;
    padding: 0.85rem !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.055) !important;
  }

  .custom-konsultasi-mobile-page > section:nth-of-type(2) h3 {
    font-size: 1.18rem !important;
    line-height: 1.05 !important;
  }

  .custom-konsultasi-mobile-page > section:nth-of-type(2) .mb-4.flex.h-11.w-11 {
    width: 2.45rem !important;
    height: 2.45rem !important;
    border-radius: 0.9rem !important;
    margin-bottom: 0.75rem !important;
  }

  .custom-konsultasi-mobile-page .konsultasi-quick-nav {
    display: flex !important;
    gap: 0.65rem !important;
    overflow-x: auto !important;
    padding: 0.85rem !important;
    border-radius: 1.15rem !important;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
  }

  .custom-konsultasi-mobile-page .konsultasi-quick-nav > a {
    min-width: 13rem !important;
    width: 13rem !important;
    scroll-snap-align: start;
    padding: 0.85rem !important;
    border-radius: 1rem !important;
    gap: 0.75rem !important;
  }

  .custom-konsultasi-mobile-page .konsultasi-quick-nav > a > span:first-child {
    width: 2.55rem !important;
    height: 2.55rem !important;
    border-radius: 0.9rem !important;
  }

  .custom-konsultasi-mobile-page > section:nth-of-type(n+4) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-konsultasi-mobile-page > section > div:not(.h-1\.5),
  .custom-konsultasi-mobile-page > section > form,
  .custom-konsultasi-mobile-page section form.grid {
    padding: 1rem !important;
  }

  .custom-konsultasi-mobile-page .rounded-\[1\.35rem\],
  .custom-konsultasi-mobile-page .rounded-\[1\.45rem\],
  .custom-konsultasi-mobile-page .rounded-\[1\.5rem\],
  .custom-konsultasi-mobile-page .rounded-\[1\.55rem\],
  .custom-konsultasi-mobile-page .rounded-\[1\.75rem\],
  .custom-konsultasi-mobile-page .rounded-\[1\.8rem\],
  .custom-konsultasi-mobile-page .rounded-\[2rem\],
  .custom-konsultasi-mobile-page .rounded-\[2\.25rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-konsultasi-mobile-page label,
  .custom-konsultasi-mobile-page .text-sm.font-bold,
  .custom-konsultasi-mobile-page .text-sm.font-black,
  .custom-konsultasi-mobile-page .text-sm.font-extrabold {
    font-size: 0.82rem !important;
  }

  .custom-konsultasi-mobile-page input:not([type="checkbox"]):not([type="file"]),
  .custom-konsultasi-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-konsultasi-mobile-page textarea {
    min-height: 112px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-konsultasi-mobile-page button,
  .custom-konsultasi-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-konsultasi-mobile-page button[type="submit"],
  .custom-konsultasi-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-konsultasi-mobile-page .grid.xl\:grid-cols-\[minmax\(0\,1fr\)_25rem\],
  .custom-konsultasi-mobile-page .grid.xl\:grid-cols-\[0\.42fr_0\.58fr\],
  .custom-konsultasi-mobile-page .grid.\32 xl\:grid-cols-\[minmax\(0\,1fr\)_10\.5rem\],
  .custom-konsultasi-mobile-page .grid.lg\:grid-cols-2,
  .custom-konsultasi-mobile-page .grid.lg\:grid-cols-4,
  .custom-konsultasi-mobile-page .grid.lg\:grid-cols-\[1fr_0\.8fr\],
  .custom-konsultasi-mobile-page .grid.sm\:grid-cols-2,
  .custom-konsultasi-mobile-page .grid.sm\:grid-cols-3,
  .custom-konsultasi-mobile-page .grid.md\:grid-cols-2,
  .custom-konsultasi-mobile-page .grid.xl\:grid-cols-3,
  .custom-konsultasi-mobile-page .grid.xl\:grid-cols-4,
  .custom-konsultasi-mobile-page .grid.\32 xl\:flex-row,
  .custom-konsultasi-mobile-page .grid.\32 xl\:grid-cols-2 {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-konsultasi-mobile-page .lg\:col-span-2,
  .custom-konsultasi-mobile-page .md\:col-span-2 {
    grid-column: auto !important;
  }

  .custom-konsultasi-mobile-page .grid.grid-cols-2.gap-2,
  .custom-konsultasi-mobile-page .grid.grid-cols-2.gap-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-konsultasi-mobile-page .flex.flex-wrap.gap-2,
  .custom-konsultasi-mobile-page .mt-3.flex.flex-wrap.gap-2 {
    gap: 0.45rem !important;
  }

  .custom-konsultasi-mobile-page .rounded-\[1\.5rem\].border.border-red-100,
  .custom-konsultasi-mobile-page .rounded-\[1\.5rem\].border.border-emerald-100,
  .custom-konsultasi-mobile-page .rounded-\[1\.5rem\].border.border-slate-200 {
    padding: 1rem !important;
  }

  .custom-konsultasi-mobile-page .flex.flex-col.gap-3.border-t,
  .custom-konsultasi-mobile-page .flex.items-end,
  .custom-konsultasi-mobile-page .flex.justify-end,
  .custom-konsultasi-mobile-page .flex.flex-col-reverse,
  .custom-konsultasi-mobile-page .flex.shrink-0.gap-2,
  .custom-konsultasi-mobile-page .flex.gap-2,
  .custom-konsultasi-mobile-page .grid.grid-cols-2.gap-2.sm\:flex {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.65rem !important;
    align-items: stretch !important;
  }

  .custom-konsultasi-mobile-page .flex.shrink-0.gap-2,
  .custom-konsultasi-mobile-page .grid.grid-cols-2.gap-2.sm\:flex,
  .custom-konsultasi-mobile-page .grid.grid-cols-2.gap-2.sm\:flex.sm\:flex-row {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    width: 100% !important;
  }

  .custom-konsultasi-mobile-page label.flex.h-\[3\.1rem\].w-fit,
  .custom-konsultasi-mobile-page .inline-flex.w-fit,
  .custom-konsultasi-mobile-page .w-fit {
    width: 100% !important;
    max-width: 100% !important;
  }

  .custom-konsultasi-mobile-page .h-12.w-12,
  .custom-konsultasi-mobile-page .h-11.w-11 {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-konsultasi-mobile-page .line-clamp-2,
  .custom-konsultasi-mobile-page .line-clamp-3 {
    display: block !important;
    -webkit-line-clamp: unset !important;
    overflow: visible !important;
  }

  .custom-konsultasi-mobile-page .truncate {
    min-width: 0;
  }

  .custom-konsultasi-mobile-page .break-all,
  .custom-konsultasi-mobile-page .break-words,
  .custom-konsultasi-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-konsultasi-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-konsultasi-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-konsultasi-mobile-page .flex.flex-wrap.items-center.gap-2 > span,
  .custom-konsultasi-mobile-page .mt-3.flex.flex-wrap.gap-2 > span {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-konsultasi-mobile-page .p-5,
  .custom-konsultasi-mobile-page .p-4,
  .custom-konsultasi-mobile-page .sm\:p-5,
  .custom-konsultasi-mobile-page .sm\:p-6,
  .custom-konsultasi-mobile-page .lg\:p-7 {
    padding: 1rem !important;
  }

  .custom-konsultasi-mobile-page .mt-8,
  .custom-konsultasi-mobile-page .mt-7,
  .custom-konsultasi-mobile-page .mt-6,
  .custom-konsultasi-mobile-page .mt-5 {
    margin-top: 1.25rem !important;
  }

  .custom-konsultasi-mobile-page .mb-7,
  .custom-konsultasi-mobile-page .mb-6,
  .custom-konsultasi-mobile-page .mb-5 {
    margin-bottom: 1.25rem !important;
  }

  .custom-konsultasi-mobile-page .gap-5,
  .custom-konsultasi-mobile-page .gap-6 {
    gap: 1rem !important;
  }

  .custom-konsultasi-mobile-page .gap-8 {
    gap: 1.25rem !important;
  }

  .custom-konsultasi-mobile-page .shadow-\[0_16px_44px_rgba\(15\,23\,42\,0\.06\)\],
  .custom-konsultasi-mobile-page .shadow-\[0_18px_52px_rgba\(15\,23\,42\,0\.065\)\],
  .custom-konsultasi-mobile-page .shadow-\[0_14px_36px_rgba\(15\,23\,42\,0\.05\)\] {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055) !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-konsultasi-mobile-page *,
  .custom-konsultasi-mobile-page *::before,
  .custom-konsultasi-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

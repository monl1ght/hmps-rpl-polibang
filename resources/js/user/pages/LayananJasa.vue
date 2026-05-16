<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  services: {
    type: Array,
    default: () => [],
  },
  servicePackages: {
    type: Array,
    default: () => [],
  },
  websitePackages: {
    type: Array,
    default: () => [],
  },
  testimonials: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const WHATSAPP_NUMBER = "6287879175646";

const flashSuccess = computed(() => page.props?.flash?.success || null);

const services = computed(() => (Array.isArray(props.services) ? props.services : []));
const servicePackages = computed(() =>
  Array.isArray(props.servicePackages) ? props.servicePackages : []
);
const websitePackages = computed(() =>
  Array.isArray(props.websitePackages) ? props.websitePackages : []
);
const testimonials = computed(() =>
  Array.isArray(props.testimonials) ? props.testimonials : []
);

const testimonialScrollRef = ref(null);
const selectedTestimonialRating = ref("all");
const selectedTestimonial = ref(null);

const normalizeRating = (rating) => {
  const value = Number.parseInt(rating, 10);

  if (Number.isNaN(value)) return 0;

  return Math.min(Math.max(value, 1), 5);
};

const getTestimonialInitial = (name) => {
  const value = String(name || "Client").trim();

  return value ? value.slice(0, 1).toUpperCase() : "C";
};

const getRatingLabel = (rating) => `${normalizeRating(rating)} dari 5 bintang`;

const testimonialRatingOptions = computed(() => {
  const total = testimonials.value.length;

  return [
    {
      label: "Semua",
      value: "all",
      count: total,
    },
    ...[5, 4, 3, 2, 1].map((rating) => ({
      label: `${rating} Bintang`,
      value: rating,
      count: testimonials.value.filter((item) => normalizeRating(item.rating) === rating)
        .length,
    })),
  ];
});

const filteredTestimonials = computed(() => {
  if (selectedTestimonialRating.value === "all") {
    return testimonials.value;
  }

  return testimonials.value.filter(
    (item) => normalizeRating(item.rating) === Number(selectedTestimonialRating.value)
  );
});

const hasTestimonials = computed(() => testimonials.value.length > 0);
const hasFilteredTestimonials = computed(() => filteredTestimonials.value.length > 0);
const isTestimonialsLoading = computed(() => !Array.isArray(props.testimonials));

const isClient = typeof window !== "undefined";

const prefersReducedMotion = () => {
  if (!isClient || typeof window.matchMedia !== "function") return false;

  return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
};

const isMobileViewport = () => {
  if (!isClient) return false;

  return window.innerWidth < 768;
};

const aosAnimation = (desktopAnimation = "fade-up", mobileAnimation = "fade-up") => {
  if (prefersReducedMotion()) return "fade";

  return isMobileViewport() ? mobileAnimation : desktopAnimation;
};

const aosDuration = (desktopDuration = 780, mobileDuration = 560) => {
  if (prefersReducedMotion()) return 1;

  return isMobileViewport() ? mobileDuration : desktopDuration;
};

const aosOffset = (desktopOffset = 90, mobileOffset = 46) => {
  return isMobileViewport() ? mobileOffset : desktopOffset;
};

const aosDelay = (index = 0, step = 80, maxDelay = 320) => {
  if (prefersReducedMotion()) return 0;

  const safeIndex = Number.isFinite(Number(index)) ? Number(index) : 0;
  const safeStep = isMobileViewport() ? Math.min(step, 45) : step;
  const safeMaxDelay = isMobileViewport() ? Math.min(maxDelay, 160) : maxDelay;

  return Math.min(safeIndex * safeStep, safeMaxDelay);
};

const refreshAos = () => {
  if (!isClient) return;

  nextTick(() => {
    window.requestAnimationFrame(() => {
      if (window.AOS?.refreshHard) {
        window.AOS.refreshHard();
        return;
      }

      if (window.AOS?.refresh) {
        window.AOS.refresh();
      }
    });
  });
};

const initPageAos = () => {
  if (!isClient || !window.AOS) return;

  window.AOS.init({
    once: true,
    mirror: false,
    easing: "ease-out-cubic",
    duration: aosDuration(760, 540),
    offset: aosOffset(86, 44),
    anchorPlacement: "top-bottom",
    disable: () => prefersReducedMotion(),
  });

  refreshAos();
};

const scrollTestimonials = (direction = "right") => {
  const container = testimonialScrollRef.value;

  if (!container) return;

  const scrollAmount = Math.min(container.clientWidth * 0.86, 620);

  container.scrollBy({
    left: direction === "left" ? -scrollAmount : scrollAmount,
    behavior: "smooth",
  });
};

const resetTestimonialFilter = () => {
  selectedTestimonialRating.value = "all";
};

const openTestimonialDetail = (testimonial) => {
  selectedTestimonial.value = testimonial;
  document.body.style.overflow = "hidden";
};

const closeTestimonialDetail = () => {
  selectedTestimonial.value = null;

  if (!consultationState.value.open && !testimonialModalOpen.value) {
    document.body.style.overflow = "";
  }
};

const consultationOptions = computed(() => {
  const options = services.value.map((service) => service.title).filter(Boolean);

  return options.length
    ? options
    : ["Jasa Website", "Instalasi Software & OS", "Jasa Desain & Video"];
});

const ticks = [
  "LAYANAN HMPS RPL",
  "WEBSITE",
  "INSTALASI",
  "DESAIN",
  "KONSULTASI",
  "RESPONSIF",
  "PROFESIONAL",
];

const heroHighlights = [
  {
    title: "Konsultasi Cepat",
    desc: "Pilih layanan dan lanjut langsung ke WhatsApp.",
  },
  {
    title: "Paket Fleksibel",
    desc: "Pilihan layanan mengikuti data terbaru dari admin.",
  },
  {
    title: "Tampilan Profesional",
    desc: "Cocok untuk personal, organisasi, UMKM, dan bisnis.",
  },
];

const serviceIcons = {
  globe: "🌐",
  monitor: "💻",
  sparkles: "🎬",
};

const getServiceFeatures = (service) => {
  return Array.isArray(service?.features) ? service.features.filter(Boolean) : [];
};

const getPackageRows = (packageItem) => {
  return Array.isArray(packageItem?.rows) ? packageItem.rows : [];
};

const consultationState = ref({
  open: false,
  type: null,
  serviceId: null,
  service: null,
  title: "",
  subtitle: "",
});

const testimonialModalOpen = ref(false);

const consultationConfig = {
  website: {
    title: "Pilih Paket Website",
    subtitle:
      "Pilih paket website yang paling sesuai, lalu Anda akan langsung diarahkan ke WhatsApp.",
  },
  instalasi: {
    title: "Pilih Paket Instalasi",
    subtitle:
      "Pilih jenis layanan instalasi yang dibutuhkan, lalu lanjut konsultasi via WhatsApp.",
  },
  desain: {
    title: "Pilih Paket Desain & Video",
    subtitle:
      "Pilih kebutuhan desain atau video yang Anda butuhkan, lalu lanjut konsultasi via WhatsApp.",
  },
};

const testimonialForm = useForm({
  name: "",
  business_name: "",
  service_type: consultationOptions.value[0] || "Jasa Website",
  rating: 5,
  message: "",
});

watch(
  consultationOptions,
  (options) => {
    if (!testimonialForm.service_type && options.length) {
      testimonialForm.service_type = options[0];
    }
  },
  { immediate: true }
);

const detectServiceType = (service) => {
  const slug = String(service?.slug || "").toLowerCase();
  const title = String(service?.title || "").toLowerCase();
  const badge = String(service?.badge || "").toLowerCase();
  const summary = String(service?.summary || "").toLowerCase();
  const icon = String(service?.icon || "").toLowerCase();
  const text = `${slug} ${title} ${badge} ${summary} ${icon}`;

  if (
    text.includes("website") ||
    text.includes("web") ||
    text.includes("landing") ||
    text.includes("company profile") ||
    text.includes("company") ||
    icon === "globe"
  ) {
    return "website";
  }

  if (
    text.includes("instal") ||
    text.includes("install") ||
    text.includes("software") ||
    text.includes("os") ||
    text.includes("setup") ||
    text.includes("driver") ||
    text.includes("windows") ||
    text.includes("linux") ||
    text.includes("teknis") ||
    icon === "monitor"
  ) {
    return "instalasi";
  }

  return "desain";
};

const getServiceTypeLabel = (type) => {
  if (type === "website") return "Website";
  if (type === "instalasi") return "Instalasi";
  return "Desain & Video";
};

const getConsultationEmoji = (type) => {
  if (type === "website") return "🌐";
  if (type === "instalasi") return "💻";
  return "🎨";
};

const getConsultationShortLabel = (type) => {
  if (type === "website") return "Konsultasi Website";
  if (type === "instalasi") return "Konsultasi Instalasi";
  return "Konsultasi Desain & Video";
};

const getConsultationHint = (type) => {
  if (type === "website") {
    return "Pilih paket website yang sesuai dengan kebutuhan Anda, lalu lanjutkan konsultasi via WhatsApp.";
  }

  if (type === "instalasi") {
    return "Pilih jenis instalasi yang dibutuhkan, lalu kami arahkan ke WhatsApp.";
  }

  return "Pilih paket desain atau video yang Anda butuhkan, lalu lanjutkan konsultasi via WhatsApp.";
};

const getConsultationButtonLabel = (service) => {
  if (service?.cta) return service.cta;

  const type = detectServiceType(service);

  if (type === "website") return "Buat Website Saya";
  if (type === "instalasi") return "Minta Bantuan Instalasi";
  return "Pesan Desain Sekarang";
};

const getPackageRowValue = (packageItem, labels = []) => {
  const normalizedLabels = labels.map((label) => String(label).toLowerCase());

  return (
    getPackageRows(packageItem).find((row) =>
      normalizedLabels.includes(String(row.label || "").toLowerCase())
    )?.value || ""
  );
};

const mapPackageToOption = (packageItem) => {
  const price =
    getPackageRowValue(packageItem, [
      "Kisaran Biaya",
      "Biaya Freelancer",
      "Biaya Agency",
      "Biaya",
      "Harga",
      "Tarif",
    ]) || "Menyesuaikan kebutuhan";

  const description =
    getPackageRowValue(packageItem, [
      "Keterangan",
      "Deskripsi",
      "Ringkasan",
      "Penjelasan",
      "Tujuan",
      "Kebutuhan",
      "Cocok Untuk",
      "Catatan",
    ]) ||
    packageItem.subtitle ||
    "Paket layanan ini dapat disesuaikan dengan kebutuhan Anda.";

  return {
    id: packageItem.id,
    service_catalog_id: packageItem.service_catalog_id,
    service_title: packageItem.service_title,
    service_slug: packageItem.service_slug,
    title: packageItem.title,
    subtitle: packageItem.subtitle,
    price,
    description,
    rows: getPackageRows(packageItem),
    features: getPackageRows(packageItem)
      .filter((row) => row.label && row.value)
      .slice(0, 5)
      .map((row) => `${row.label}: ${row.value}`),
    whatsapp_text: `Halo, saya ingin konsultasi paket ${packageItem.title}${
      packageItem.service_title ? ` untuk layanan ${packageItem.service_title}` : ""
    }.`,
  };
};

const mapServiceToOption = (service) => ({
  id: service.id,
  service_catalog_id: service.id,
  service_title: service.title,
  service_slug: service.slug,
  title: service.title,
  subtitle: service.badge || "Layanan",
  price: "Menyesuaikan kebutuhan",
  description: service.summary || "Layanan dapat dikonsultasikan sesuai kebutuhan Anda.",
  rows: [],
  features: getServiceFeatures(service),
  whatsapp_text:
    service.whatsapp_text || `Halo, saya ingin konsultasi layanan ${service.title}.`,
});

const getServicePackages = (service) => {
  if (!service) return [];

  const packagesFromService = Array.isArray(service.packages) ? service.packages : [];

  if (packagesFromService.length) {
    return packagesFromService;
  }

  return servicePackages.value.filter(
    (packageItem) => Number(packageItem.service_catalog_id) === Number(service.id)
  );
};

const preferredWebsiteService = computed(() => {
  return (
    services.value.find((service) => detectServiceType(service) === "website") ||
    services.value[0] ||
    null
  );
});

const currentConsultationOptions = computed(() => {
  const selectedService = consultationState.value.service;
  const type = consultationState.value.type;

  if (selectedService) {
    const packages = getServicePackages(selectedService);

    if (packages.length) {
      return packages.map((packageItem) => mapPackageToOption(packageItem));
    }

    return [mapServiceToOption(selectedService)];
  }

  if (type === "website" && websitePackages.value.length) {
    return websitePackages.value.map((packageItem) => mapPackageToOption(packageItem));
  }

  return [];
});

const openConsultationModal = (serviceOrType) => {
  const selectedService =
    typeof serviceOrType === "object"
      ? serviceOrType
      : services.value.find((service) => detectServiceType(service) === serviceOrType) ||
        preferredWebsiteService.value;

  const type = selectedService
    ? detectServiceType(selectedService)
    : serviceOrType || "website";
  const config = consultationConfig[type] || consultationConfig.website;

  consultationState.value = {
    open: true,
    type,
    serviceId: selectedService?.id || null,
    service: selectedService || null,
    title: config.title,
    subtitle: config.subtitle,
  };

  document.body.style.overflow = "hidden";
};

const closeConsultationModal = () => {
  consultationState.value = {
    open: false,
    type: null,
    serviceId: null,
    service: null,
    title: "",
    subtitle: "",
  };

  if (!selectedTestimonial.value && !testimonialModalOpen.value) {
    document.body.style.overflow = "";
  }
};

const openTestimonialModal = () => {
  testimonialModalOpen.value = true;
  document.body.style.overflow = "hidden";
};

const closeTestimonialModal = () => {
  testimonialModalOpen.value = false;

  if (!selectedTestimonial.value && !consultationState.value.open) {
    document.body.style.overflow = "";
  }
};

const chooseConsultationOption = (option) => {
  const serviceName =
    option.service_title ||
    consultationState.value.service?.title ||
    getServiceTypeLabel(consultationState.value.type);

  const rowText = option.rows?.length
    ? option.rows
        .slice(0, 6)
        .map((row) => `- ${row.label}: ${row.value}`)
        .join("\n")
    : "";

  const message =
    option.whatsapp_text ||
    `Halo, saya ingin konsultasi layanan ${serviceName}.
Saya tertarik dengan pilihan:
- ${option.title}${option.subtitle ? ` (${option.subtitle})` : ""}
- ${option.price}

${rowText ? `Detail pilihan:\n${rowText}\n\n` : ""}Mohon info lebih lanjut ya.
Terima kasih.`;

  const waUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;

  window.open(waUrl, "_blank", "noopener,noreferrer");
  closeConsultationModal();
};

const scrollToSection = (id) => {
  const target = document.getElementById(id);

  if (target) {
    target.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
};

const submitTestimonial = () => {
  testimonialForm.post("/layananJasa/testimoni", {
    preserveScroll: true,
    onSuccess: () => {
      testimonialForm.reset();
      testimonialForm.rating = 5;
      testimonialForm.service_type = consultationOptions.value[0] || "Jasa Website";
      closeTestimonialModal();
    },
  });
};

const handleEscape = (event) => {
  if (event.key !== "Escape") return;

  if (selectedTestimonial.value) {
    closeTestimonialDetail();
    return;
  }

  if (consultationState.value.open) {
    closeConsultationModal();
    return;
  }

  if (testimonialModalOpen.value) {
    closeTestimonialModal();
  }
};

watch(
  [
    () => services.value.length,
    () => filteredTestimonials.value.length,
    () => currentConsultationOptions.value.length,
    () => selectedTestimonialRating.value,
    () => consultationState.value.open,
    () => testimonialModalOpen.value,
  ],
  () => refreshAos(),
  { flush: "post" }
);

onMounted(() => {
  window.addEventListener("keydown", handleEscape);
  initPageAos();
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleEscape);
  document.body.style.overflow = "";
});
</script>

<template>
  <Head title="Layanan Jasa">
    <meta
      name="description"
      content="Layanan jasa HMPS RPL untuk pembuatan website, instalasi software, desain, video, dan konsultasi digital yang responsif, profesional, serta mudah dihubungi melalui WhatsApp."
    />
    <meta
      name="keywords"
      content="Layanan HMPS RPL, jasa website, instalasi software, desain, video, konsultasi website, HMPS Rekayasa Perangkat Lunak"
    />
    <meta property="og:title" content="Layanan Jasa HMPS RPL" />
    <meta
      property="og:description"
      content="Pilih layanan jasa HMPS RPL untuk kebutuhan website, instalasi, desain, video, dan konsultasi digital secara profesional."
    />
    <meta property="og:type" content="website" />
  </Head>

  <div
    class="layanan-jasa-mobile-page overflow-x-hidden bg-white font-['Plus_Jakarta_Sans',sans-serif] text-slate-950"
  >
    <!-- Hero -->
    <section
      class="relative overflow-hidden bg-[radial-gradient(circle_at_12%_18%,rgba(239,68,68,0.12),transparent_28%),radial-gradient(circle_at_88%_20%,rgba(153,27,27,0.10),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fff7f7_52%,#ffffff_100%)] pb-14 pt-24 sm:pb-16 sm:pt-28 lg:min-h-[92svh] lg:pt-32"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.09)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.09)_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:linear-gradient(180deg,rgba(255,255,255,0.88),transparent_90%)]"
      />
      <div
        class="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-red-500/10 blur-[78px] lg:h-96 lg:w-96"
      />
      <div
        class="pointer-events-none absolute -bottom-20 -left-24 h-64 w-64 rounded-full bg-rose-900/10 blur-[78px] lg:h-80 lg:w-80"
      />

      <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="grid grid-cols-1 items-center gap-10 lg:grid-cols-[1fr_0.92fr] xl:gap-14"
        >
          <div
            class="lcp-hero-content"
            :data-aos="aosAnimation('fade-right')"
            :data-aos-duration="aosDuration(820, 580)"
            :data-aos-offset="aosOffset(70, 36)"
          >
            <div
              class="mb-5 inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/80 px-3 py-2 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 shadow-[0_12px_35px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:text-[0.75rem]"
            >
              <span
                class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
              />
              <span class="truncate">Layanan Profesional HMPS RPL</span>
            </div>

            <h1
              class="max-w-3xl text-[2.35rem] font-black leading-[1.05] tracking-[-0.05em] text-slate-950 sm:text-[3.2rem] md:text-[3.8rem] xl:text-[4.8rem]"
            >
              Layanan Jasa
              <span
                class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent sm:inline"
              >
                HMPS RPL
              </span>
            </h1>

            <p
              class="mt-6 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-600 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              Kami menyediakan layanan yang rapi, modern, dan responsif untuk kebutuhan
              personal, organisasi, UMKM, maupun bisnis yang ingin tampil lebih dipercaya
              dan lebih siap berkembang.
            </p>

            <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
              <button
                type="button"
                aria-label="Lihat daftar layanan jasa HMPS RPL"
                @click="scrollToSection('layanan-list')"
                class="premium-black-btn group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-5 text-sm font-extrabold text-white shadow-[0_16px_34px_rgba(220,38,38,0.22)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] sm:min-h-[54px]"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />
                <span class="relative z-10">Lihat Layanan</span>
              </button>

              <button
                type="button"
                aria-label="Buka pilihan konsultasi website"
                @click="openConsultationModal(preferredWebsiteService || 'website')"
                class="premium-black-btn inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl border border-slate-900/10 bg-white/[0.85] px-5 text-sm font-extrabold text-white shadow-[0_14px_36px_rgba(2,6,23,0.07)] backdrop-blur-xl transition-all duration-300 hover:-translate-y-[2px] hover:border-red-500/25 hover:text-white hover:shadow-[0_22px_50px_rgba(2,6,23,0.11)] sm:min-h-[54px]"
              >
                Konsultasi Website
              </button>

              <button
                type="button"
                aria-label="Buka formulir kirim testimoni"
                @click="openTestimonialModal"
                class="premium-black-btn inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-2xl border border-slate-900/10 bg-white/[0.85] px-5 text-sm font-extrabold text-white shadow-[0_14px_36px_rgba(2,6,23,0.07)] backdrop-blur-xl transition-all duration-300 hover:-translate-y-[2px] hover:border-red-500/25 hover:text-white hover:shadow-[0_22px_50px_rgba(2,6,23,0.11)] sm:min-h-[54px]"
              >
                Kirim Testimoni
              </button>
            </div>
          </div>

          <div
            :data-aos="aosAnimation('fade-left')"
            :data-aos-delay="aosDelay(1, 110, 140)"
            :data-aos-duration="aosDuration(820, 580)"
            :data-aos-offset="aosOffset(70, 36)"
          >
            <div
              class="relative overflow-hidden rounded-[1.9rem] border border-slate-800 bg-[linear-gradient(155deg,#0f172a,#111827_55%,#1e293b)] p-5 text-white shadow-[0_34px_100px_rgba(2,6,23,0.28)] ring-1 ring-slate-700/60 sm:p-6"
            >
              <div
                class="pointer-events-none absolute -right-14 -top-14 h-52 w-52 rounded-full bg-red-500/20 blur-[70px]"
              />
              <div
                class="pointer-events-none absolute -bottom-16 -left-16 h-52 w-52 rounded-full bg-white/10 blur-[80px]"
              />

              <div class="relative z-10">
                <div
                  class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-100 ring-1 ring-white/10"
                >
                  <span class="h-2 w-2 rounded-full bg-red-400" />
                  Alur Konsultasi
                </div>

                <h2
                  class="mt-5 text-2xl font-black tracking-[-0.03em] text-white sm:text-3xl"
                >
                  Mudah, cepat, dan langsung terhubung ke WhatsApp
                </h2>

                <div class="mt-6 space-y-4">
                  <div
                    v-for="(item, index) in heroHighlights"
                    :key="item.title"
                    class="flex items-start gap-4 rounded-[1.25rem] border border-white/10 bg-white/[0.07] p-4"
                  >
                    <div
                      class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black text-white"
                      :class="
                        index === 2
                          ? 'bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]'
                          : 'bg-white/10'
                      "
                    >
                      {{ index + 1 }}
                    </div>
                    <div>
                      <h3 class="text-base font-black text-white">{{ item.title }}</h3>
                      <p class="mt-1 text-justify text-sm leading-7 text-slate-300">
                        {{ item.desc }}
                      </p>
                    </div>
                  </div>
                </div>

                <button
                  type="button"
                  aria-label="Konsultasikan website melalui pilihan layanan"
                  @click="openConsultationModal(preferredWebsiteService || 'website')"
                  class="group relative mt-6 inline-flex min-h-[52px] w-full items-center justify-center overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-5 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(220,38,38,0.24)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_22px_42px_rgba(220,38,38,0.32)] active:scale-[0.98]"
                >
                  <span
                    class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                  />
                  <span class="relative z-10">Konsultasikan Website Anda</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ticker -->
    <div
      class="overflow-hidden border-y border-white/5 bg-[linear-gradient(135deg,#0f172a,#172033_60%,#111827)] py-3 sm:py-4"
    >
      <div class="ticker-wrap w-full overflow-hidden">
        <div class="ticker-inner flex w-max">
          <div v-for="dup in 2" :key="dup" class="flex items-center">
            <span
              v-for="item in ticks"
              :key="`${dup}-${item}`"
              class="mx-5 inline-flex whitespace-nowrap text-[0.72rem] font-extrabold uppercase tracking-[0.16em] text-white sm:mx-8 sm:text-[0.8rem] sm:tracking-[0.2em]"
            >
              {{ item }}
              <span class="ml-4 text-sm text-red-400/70 sm:ml-5 sm:text-base">✦</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Services -->
    <section
      id="layanan-list"
      class="cv-auto scroll-mt-24 bg-white py-16 sm:py-20 lg:py-24"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mx-auto mb-12 max-w-3xl text-center sm:mb-14"
          :data-aos="aosAnimation('fade-up')"
          :data-aos-duration="aosDuration(760, 540)"
          :data-aos-offset="aosOffset(82, 44)"
        >
          <div
            class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-4 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Layanan Utama
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem]"
          >
            Solusi layanan yang
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              rapi dan profesional
            </span>
          </h2>

          <p
            class="mx-auto mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem]"
          >
            Setiap layanan dan paketnya dikelola melalui admin, sehingga informasi yang
            tampil selalu mengikuti database terbaru.
          </p>
        </div>

        <div v-if="services.length" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <article
            v-for="(service, index) in services"
            :key="service.id || service.slug || service.title"
            class="group relative flex h-full flex-col overflow-hidden rounded-[1.65rem] border border-slate-900/5 bg-white p-6 shadow-[0_14px_40px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-2 hover:border-red-500/20 hover:shadow-[0_30px_75px_rgba(2,6,23,0.14)] sm:p-7"
            :data-aos="aosAnimation('zoom-in-up')"
            :data-aos-delay="aosDelay(index, 90, 270)"
            :data-aos-duration="aosDuration(760, 540)"
            :data-aos-offset="aosOffset(86, 42)"
          >
            <div
              class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-[linear-gradient(180deg,rgba(239,68,68,0.06),transparent)] opacity-0 transition duration-300 group-hover:opacity-100"
            />

            <div class="relative z-10 flex items-start justify-between gap-4">
              <div
                class="flex h-16 w-16 items-center justify-center rounded-[1.4rem] border border-red-100 bg-[linear-gradient(180deg,#fff5f5,#fff)] text-[1.7rem] shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]"
              >
                {{ serviceIcons[service.icon] || "⭐" }}
              </div>

              <span
                class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-slate-700"
              >
                {{ service.badge || "Layanan" }}
              </span>
            </div>

            <div class="relative z-10 mt-8">
              <h3
                class="text-[1.7rem] font-black leading-tight tracking-[-0.04em] text-slate-950 sm:text-[2rem]"
              >
                {{ service.title }}
              </h3>

              <p
                class="mt-4 text-justify text-[0.95rem] leading-8 text-slate-600 sm:text-[1rem]"
              >
                {{ service.summary }}
              </p>
            </div>

            <div class="relative z-10 mt-7 space-y-3">
              <div
                v-for="feature in getServiceFeatures(service)"
                :key="feature"
                class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3.5 transition duration-300 group-hover:border-red-100 group-hover:bg-red-50/40"
              >
                <span
                  class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-[linear-gradient(135deg,#ef4444,#dc2626)]"
                />
                <span
                  class="text-justify text-[0.92rem] leading-7 text-slate-700 sm:text-[0.98rem]"
                >
                  {{ feature }}
                </span>
              </div>
            </div>

            <div
              class="relative z-10 mt-7 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
            >
              <p class="text-xs font-black uppercase tracking-[0.08em] text-slate-400">
                Paket tersedia
              </p>
              <p class="mt-1 text-sm font-bold text-slate-700">
                {{ getServicePackages(service).length }} paket layanan
              </p>
            </div>

            <div class="relative z-10 mt-auto pt-7">
              <div
                class="mb-6 h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent"
              />

              <button
                type="button"
                @click="openConsultationModal(service)"
                class="premium-black-btn group/btn relative inline-flex min-h-[54px] w-full items-center justify-center overflow-hidden rounded-[1.3rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-6 text-[0.95rem] font-extrabold text-white shadow-[0_14px_30px_rgba(220,38,38,0.20)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_20px_38px_rgba(220,38,38,0.28)]"
              >
                <span
                  class="absolute inset-0 -translate-x-[130%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.16),transparent)] transition-transform duration-500 group-hover/btn:translate-x-[130%]"
                />
                <span class="relative z-10 flex items-center gap-2">
                  {{ getConsultationButtonLabel(service) }}
                  <svg
                    class="h-4 w-4 transition-transform duration-300 group-hover/btn:translate-x-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.5"
                      d="M5 12h14m-6-6 6 6-6 6"
                    />
                  </svg>
                </span>
              </button>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center"
        >
          <p class="text-base font-bold text-slate-700">Layanan belum tersedia.</p>
          <p class="mx-auto mt-2 max-w-xl text-justify text-sm text-slate-500">
            Tambahkan data layanan dari menu admin agar daftar jasa tampil.
          </p>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section
      id="testimoni-layanan-jasa"
      class="cv-auto relative scroll-mt-24 overflow-hidden bg-[radial-gradient(circle_at_12%_16%,rgba(239,68,68,0.08),transparent_26%),radial-gradient(circle_at_88%_18%,rgba(15,23,42,0.07),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fff8f8_48%,#f8fafc_100%)] py-16 sm:py-20 lg:py-28"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.07)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.07)_1px,transparent_1px)] bg-[size:44px_44px] [mask-image:linear-gradient(180deg,rgba(255,255,255,0.72),transparent_86%)]"
      />
      <div
        class="pointer-events-none absolute -left-28 top-14 h-80 w-80 rounded-full bg-red-500/10 blur-[92px]"
      />
      <div
        class="pointer-events-none absolute -right-28 bottom-8 h-80 w-80 rounded-full bg-slate-950/10 blur-[98px]"
      />

      <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-10 max-w-4xl text-center sm:mb-12 lg:mb-14">
          <div
            class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-white/[0.85] px-4 py-2 text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-700 shadow-[0_12px_32px_rgba(239,68,68,0.10)] backdrop-blur-xl sm:text-[0.72rem]"
            :data-aos="aosAnimation('fade-down')"
            :data-aos-duration="aosDuration(700, 520)"
            :data-aos-offset="aosOffset(80, 42)"
          >
            <span class="relative flex h-2.5 w-2.5">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-30"
              />
              <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-600" />
            </span>
            Testimoni Client
          </div>

          <h2
            class="text-[2rem] font-black leading-[1.06] tracking-[-0.055em] text-slate-950 sm:text-[2.65rem] md:text-[3.25rem] lg:text-[3.65rem]"
            :data-aos="aosAnimation('fade-up')"
            :data-aos-delay="aosDelay(1, 70, 90)"
            :data-aos-duration="aosDuration(780, 540)"
            :data-aos-offset="aosOffset(80, 42)"
          >
            Kepercayaan
            <span
              class="relative inline-block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              dari client
              <span
                class="absolute -bottom-1 left-2 right-2 -z-10 h-3 rounded-full bg-red-200/70 blur-sm"
              />
            </span>
          </h2>

          <p
            class="mx-auto mt-5 max-w-2xl text-center text-[0.95rem] leading-[1.85] text-slate-600 sm:text-[1.02rem]"
            :data-aos="aosAnimation('fade-up')"
            :data-aos-delay="aosDelay(2, 70, 140)"
            :data-aos-duration="aosDuration(780, 540)"
            :data-aos-offset="aosOffset(80, 42)"
          >
            Testimoni yang tampil di halaman ini adalah testimoni layanan jasa yang sudah
            disetujui admin. Anda dapat memfilter berdasarkan rating, menggeser daftar
            testimoni, dan membuka detail ulasan client.
          </p>
        </div>

        <div
          class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/[0.92] p-4 shadow-[0_28px_80px_rgba(15,23,42,0.09)] backdrop-blur-xl sm:p-5 lg:p-6"
          :data-aos="aosAnimation('fade-up')"
          :data-aos-delay="aosDelay(1, 80, 120)"
          :data-aos-duration="aosDuration(820, 560)"
          :data-aos-offset="aosOffset(84, 42)"
        >
          <div
            class="relative overflow-hidden rounded-[1.7rem] border border-white/70 bg-[radial-gradient(circle_at_8%_10%,rgba(239,68,68,0.09),transparent_24%),radial-gradient(circle_at_92%_8%,rgba(16,185,129,0.09),transparent_24%),linear-gradient(135deg,#ffffff_0%,#fff8f8_44%,#f8fafc_100%)] p-4 sm:p-5 lg:p-6"
          >
            <div
              class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.06)_1px,transparent_1px)] bg-[size:34px_34px] opacity-80"
            />
            <div
              class="pointer-events-none absolute -left-20 top-0 h-52 w-52 rounded-full bg-red-500/[0.08] blur-3xl"
            />
            <div
              class="pointer-events-none absolute -right-20 bottom-0 h-56 w-56 rounded-full bg-slate-950/[0.06] blur-3xl"
            />

            <div class="relative z-10">
              <div
                v-if="hasTestimonials"
                class="rounded-[1.5rem] border border-slate-200/80 bg-white/95 p-4 shadow-[0_16px_40px_rgba(15,23,42,0.05)] sm:p-5"
                :data-aos="aosAnimation('fade-up')"
                :data-aos-delay="aosDelay(1, 80, 130)"
                :data-aos-duration="aosDuration(760, 540)"
                :data-aos-offset="aosOffset(74, 38)"
              >
                <div
                  class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
                >
                  <div class="flex items-start gap-4">
                    <div
                      class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-[0_12px_26px_rgba(15,23,42,0.22)]"
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
                          stroke-width="2.4"
                          d="M4 7h16M7 12h10m-7 5h4"
                        />
                      </svg>
                    </div>

                    <div>
                      <h3 class="text-lg font-black tracking-[-0.03em] text-slate-950">
                        Filter rating testimoni
                      </h3>
                      <p
                        class="mt-1 max-w-md text-sm font-semibold leading-7 text-slate-500"
                      >
                        Menampilkan
                        <span class="font-black text-red-600">
                          {{ filteredTestimonials.length }}
                        </span>
                        dari
                        <span class="font-black text-slate-700">
                          {{ testimonials.length }}
                        </span>
                        testimoni approved.
                      </p>
                    </div>
                  </div>

                  <div
                    class="testimonial-filter-scroll -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 xl:mx-0 xl:pb-0"
                  >
                    <button
                      v-for="item in testimonialRatingOptions"
                      :key="item.value"
                      type="button"
                      @click="selectedTestimonialRating = item.value"
                      :class="[
                        'inline-flex min-h-[44px] shrink-0 items-center gap-2 rounded-full border px-5 text-sm font-black transition-all duration-300',
                        selectedTestimonialRating === item.value
                          ? 'border-red-500 bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-white shadow-[0_14px_26px_rgba(220,38,38,0.24)]'
                          : 'border-slate-200 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700',
                      ]"
                    >
                      <span>{{ item.label }}</span>
                      <span
                        :class="[
                          'inline-flex min-w-[1.55rem] items-center justify-center rounded-full px-1.5 py-0.5 text-[0.68rem] font-black',
                          selectedTestimonialRating === item.value
                            ? 'bg-white/20 text-white'
                            : 'bg-slate-100 text-slate-500',
                        ]"
                      >
                        {{ item.count }}
                      </span>
                    </button>
                  </div>
                </div>
              </div>

              <div
                class="my-6 h-px bg-[linear-gradient(90deg,rgba(239,68,68,0.10),rgba(148,163,184,0.24),rgba(15,23,42,0.08))]"
              />

              <div
                class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
              >
                <div :data-aos="aosAnimation('fade-up')" :data-aos-delay="aosDelay(1, 70, 120)" :data-aos-duration="aosDuration(740, 520)" :data-aos-offset="aosOffset(70, 36)">
                  <h4
                    class="mt-2 text-lg font-black tracking-[-0.03em] text-slate-950 sm:text-[1.35rem]"
                  >
                    Ulasan terbaru dari client layanan jasa
                  </h4>
                </div>

                <div
                  class="hidden items-center gap-3 sm:flex"
                  :data-aos="aosAnimation('fade-left')"
                  :data-aos-delay="aosDelay(2, 70, 150)"
                  :data-aos-duration="aosDuration(740, 520)"
                  :data-aos-offset="aosOffset(70, 36)"
                >
                  <button
                    type="button"
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_20px_36px_rgba(220,38,38,0.22)] active:scale-95 active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                    aria-label="Geser testimoni ke kiri"
                    @click="scrollTestimonials('left')"
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
                        stroke-width="2.5"
                        d="M15 19l-7-7 7-7"
                      />
                    </svg>
                  </button>

                  <button
                    type="button"
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_20px_36px_rgba(220,38,38,0.22)] active:scale-95 active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                    aria-label="Geser testimoni ke kanan"
                    @click="scrollTestimonials('right')"
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
                        stroke-width="2.5"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="mt-6">
                <div v-if="isTestimonialsLoading" class="grid gap-5 lg:grid-cols-3">
                  <div
                    v-for="item in 3"
                    :key="`testimonial-loading-${item}`"
                    class="min-h-[355px] animate-pulse rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-5 shadow-[0_18px_52px_rgba(15,23,42,0.05)]"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-slate-200" />
                        <div class="space-y-2">
                          <div class="h-4 w-32 rounded-full bg-slate-200" />
                          <div class="h-3 w-24 rounded-full bg-slate-100" />
                        </div>
                      </div>
                      <div class="h-7 w-14 rounded-full bg-slate-100" />
                    </div>
                    <div class="mt-5 flex gap-1">
                      <div
                        v-for="star in 5"
                        :key="star"
                        class="h-4 w-4 rounded bg-slate-100"
                      />
                    </div>
                    <div class="mt-6 space-y-3">
                      <div class="h-3 w-full rounded-full bg-slate-100" />
                      <div class="h-3 w-11/12 rounded-full bg-slate-100" />
                      <div class="h-3 w-10/12 rounded-full bg-slate-100" />
                      <div class="h-3 w-8/12 rounded-full bg-slate-100" />
                    </div>
                    <div class="mt-8 h-20 rounded-2xl bg-slate-100" />
                  </div>
                </div>

                <div v-else-if="hasFilteredTestimonials" class="relative">
                  <div
                    ref="testimonialScrollRef"
                    class="testimonial-scroll flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-5 pr-2"
                  >
                    <article
                      v-for="(testimonial, index) in filteredTestimonials"
                      :key="testimonial.id"
                      role="button"
                      tabindex="0"
                      :aria-label="`Lihat detail testimoni dari ${testimonial.name}`"
                      @click="openTestimonialDetail(testimonial)"
                      @keydown.enter="openTestimonialDetail(testimonial)"
                      @keydown.space.prevent="openTestimonialDetail(testimonial)"
                      class="group relative flex min-h-[355px] w-[88%] shrink-0 cursor-pointer snap-start flex-col overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white p-5 text-left shadow-[0_18px_52px_rgba(15,23,42,0.07)] outline-none transition-all duration-300 hover:-translate-y-1.5 hover:border-red-500/25 hover:shadow-[0_28px_68px_rgba(15,23,42,0.12)] focus:border-red-300 focus:ring-4 focus:ring-red-100 sm:w-[410px] lg:w-[430px]"
                      :data-aos="aosAnimation('zoom-in-up')"
                      :data-aos-delay="aosDelay(index, 65, 260)"
                      :data-aos-duration="aosDuration(720, 520)"
                      :data-aos-offset="aosOffset(78, 40)"
                    >
                      <div
                        class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-[linear-gradient(180deg,rgba(239,68,68,0.08),transparent)] opacity-0 transition duration-300 group-hover:opacity-100"
                      />
                      <div
                        class="pointer-events-none absolute -right-12 -top-12 h-32 w-32 rounded-full bg-red-500/10 blur-3xl transition duration-300 group-hover:bg-red-500/20"
                      />

                      <div class="relative z-10 flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                          <div
                            class="flex h-[3.25rem] w-[3.25rem] shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-base font-black uppercase text-white shadow-[0_14px_28px_rgba(220,38,38,0.22)] ring-4 ring-red-50"
                          >
                            {{ getTestimonialInitial(testimonial.name) }}
                          </div>

                          <div class="min-w-0">
                            <h3
                              class="truncate text-base font-black text-slate-950 sm:text-lg"
                            >
                              {{ testimonial.name }}
                            </h3>
                            <p
                              class="mt-1 truncate text-xs font-bold text-slate-500 sm:text-sm"
                            >
                              {{ testimonial.business_name || testimonial.service_type }}
                            </p>
                          </div>
                        </div>

                        <div
                          class="shrink-0 rounded-full bg-red-50 px-3 py-1.5 text-xs font-black text-red-700 ring-1 ring-red-100"
                          :aria-label="getRatingLabel(testimonial.rating)"
                        >
                          {{ normalizeRating(testimonial.rating) }}/5
                        </div>
                      </div>

                      <div
                        class="relative z-10 mt-5 flex items-center gap-1 text-base text-red-500"
                        :aria-label="getRatingLabel(testimonial.rating)"
                      >
                        <span v-for="star in 5" :key="star">
                          {{ star <= normalizeRating(testimonial.rating) ? "★" : "☆" }}
                        </span>
                      </div>

                      <p
                        class="relative z-10 mt-4 line-clamp-6 flex-1 text-sm leading-8 text-slate-600"
                      >
                        “{{ testimonial.message }}”
                      </p>

                      <div
                        class="relative z-10 mt-6 rounded-2xl border border-slate-100 bg-slate-50/80 p-4"
                      >
                        <div class="flex items-start justify-between gap-3">
                          <div class="min-w-0">
                            <p
                              class="truncate text-[0.68rem] font-black uppercase tracking-[0.09em] text-slate-400"
                            >
                              {{ testimonial.service_type || "Layanan Jasa" }}
                            </p>
                            <p class="mt-1 truncate text-xs font-bold text-slate-500">
                              {{ testimonial.created_at || "Baru saja" }}
                            </p>
                          </div>

                          <span
                            class="shrink-0 text-[0.72rem] font-black uppercase tracking-[0.08em] text-red-600 transition duration-300 group-hover:translate-x-1"
                          >
                            Detail →
                          </span>
                        </div>
                      </div>
                    </article>
                  </div>

                  <div class="mt-2 flex items-center justify-center gap-3 sm:hidden">
                    <button
                      type="button"
                      @click="scrollTestimonials('left')"
                      class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 active:scale-95 active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                      aria-label="Geser testimoni ke kiri"
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
                          stroke-width="2.5"
                          d="M15 19l-7-7 7-7"
                        />
                      </svg>
                    </button>

                    <button
                      type="button"
                      @click="scrollTestimonials('right')"
                      class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 active:scale-95 active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                      aria-label="Geser testimoni ke kanan"
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
                          stroke-width="2.5"
                          d="M9 5l7 7-7 7"
                        />
                      </svg>
                    </button>
                  </div>
                </div>

                <div
                  v-else-if="hasTestimonials"
                  class="rounded-[1.85rem] border border-dashed border-red-200 bg-white/90 px-6 py-12 text-center shadow-[0_18px_52px_rgba(15,23,42,0.06)] backdrop-blur-xl"
                  :data-aos="aosAnimation('fade-up')"
                  :data-aos-duration="aosDuration(720, 520)"
                  :data-aos-offset="aosOffset(70, 36)"
                >
                  <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-red-50 text-red-600 ring-1 ring-red-100"
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
                        d="M12 9v4m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"
                      />
                    </svg>
                  </div>

                  <p class="mt-5 text-lg font-black text-slate-900">
                    Tidak ada testimoni dengan filter ini.
                  </p>
                  <p
                    class="mx-auto mt-2 max-w-xl text-center text-sm leading-7 text-slate-500"
                  >
                    Silakan pilih filter rating lain atau tampilkan semua testimoni agar
                    daftar ulasan kembali terlihat.
                  </p>

                  <button
                    type="button"
                    @click="resetTestimonialFilter"
                    class="premium-black-btn mt-6 inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-red-700"
                  >
                    Tampilkan Semua
                  </button>
                </div>

                <div
                  v-else
                  class="rounded-[1.85rem] border border-dashed border-slate-300 bg-white/90 px-6 py-12 text-center shadow-[0_18px_52px_rgba(15,23,42,0.06)] backdrop-blur-xl"
                  :data-aos="aosAnimation('fade-up')"
                  :data-aos-duration="aosDuration(720, 520)"
                  :data-aos-offset="aosOffset(70, 36)"
                >
                  <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-600 ring-1 ring-slate-200"
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
                        d="M8 10h8M8 14h5m-9 5.25V6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v7.5A2.75 2.75 0 0117.25 17H9l-5 2.25z"
                      />
                    </svg>
                  </div>

                  <p class="mt-5 text-lg font-black text-slate-900">
                    Belum ada testimoni yang ditampilkan.
                  </p>
                  <p
                    class="mx-auto mt-2 max-w-xl text-center text-sm leading-7 text-slate-500"
                  >
                    Kirim testimoni terlebih dahulu, lalu approve melalui menu admin.
                  </p>
                </div>
              </div>

              <div
                class="mt-8 flex justify-center"
                :data-aos="aosAnimation('fade-up')"
                :data-aos-delay="aosDelay(2, 70, 160)"
                :data-aos-duration="aosDuration(760, 540)"
                :data-aos-offset="aosOffset(74, 38)"
              >
                <button
                  type="button"
                  @click="openTestimonialModal"
                  class="premium-black-btn group relative inline-flex min-h-[56px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-7 text-sm font-black text-white shadow-[0_18px_42px_rgba(220,38,38,0.24)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_54px_rgba(220,38,38,0.32)]"
                >
                  <span
                    class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                  />
                  <svg
                    class="relative z-10 h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.3"
                      d="M12 4v16m8-8H4"
                    />
                  </svg>
                  <span class="relative z-10">Kirim Testimoni</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonial Detail Modal -->
    <Teleport to="body">
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="selectedTestimonial"
          class="fixed inset-0 z-[1001] overflow-y-auto bg-slate-950/75 backdrop-blur-md"
          role="dialog"
          aria-modal="true"
          @click.self="closeTestimonialDetail"
        >
          <div
            class="flex min-h-full items-center justify-center px-4 py-6 sm:px-6 lg:px-8"
          >
            <div
              class="relative w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-[0_40px_120px_rgba(2,6,23,0.35)]"
            >
              <button
                type="button"
                aria-label="Tutup detail testimoni"
                @click="closeTestimonialDetail"
                class="absolute right-4 top-4 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md transition hover:bg-red-50 hover:text-red-700"
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
                    stroke-width="2.4"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>

              <div
                class="grid max-h-[92vh] grid-cols-1 overflow-y-auto lg:grid-cols-[0.85fr_1.15fr]"
              >
                <aside
                  class="relative overflow-hidden bg-[linear-gradient(160deg,#0f172a,#111827_55%,#1e293b)] p-6 text-white sm:p-8 lg:p-10"
                >
                  <div
                    class="pointer-events-none absolute -right-12 -top-12 h-52 w-52 rounded-full bg-red-500/20 blur-[70px]"
                  />
                  <div
                    class="pointer-events-none absolute -bottom-16 -left-16 h-52 w-52 rounded-full bg-white/10 blur-[80px]"
                  />

                  <div class="relative z-10">
                    <div
                      class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-200 ring-1 ring-white/10"
                    >
                      <span class="h-2 w-2 rounded-full bg-red-400" />
                      Detail Testimoni
                    </div>

                    <div
                      class="mt-8 flex h-24 w-24 items-center justify-center rounded-[2rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-3xl font-black uppercase text-white shadow-[0_20px_45px_rgba(220,38,38,0.30)]"
                    >
                      {{ getTestimonialInitial(selectedTestimonial.name) }}
                    </div>

                    <h3
                      class="mt-6 text-3xl font-black leading-tight tracking-[-0.04em] text-white"
                    >
                      {{ selectedTestimonial.name }}
                    </h3>

                    <p class="mt-2 text-base font-semibold text-slate-300">
                      {{
                        selectedTestimonial.business_name ||
                        selectedTestimonial.service_type
                      }}
                    </p>

                    <div class="mt-6 flex items-center gap-2 text-2xl text-red-400">
                      <span v-for="star in 5" :key="star">
                        {{
                          star <= normalizeRating(selectedTestimonial.rating) ? "★" : "☆"
                        }}
                      </span>
                    </div>

                    <p
                      class="mt-3 text-sm font-bold uppercase tracking-[0.12em] text-red-100"
                    >
                      {{ getRatingLabel(selectedTestimonial.rating) }}
                    </p>

                    <div class="mt-8 grid grid-cols-1 gap-3">
                      <div
                        class="rounded-[1.25rem] bg-white/[0.08] p-4 ring-1 ring-white/10"
                      >
                        <p
                          class="text-xs font-extrabold uppercase tracking-[0.08em] text-red-200"
                        >
                          Layanan
                        </p>
                        <p class="mt-2 text-sm font-bold leading-7 text-white">
                          {{ selectedTestimonial.service_type }}
                        </p>
                      </div>

                      <div
                        class="rounded-[1.25rem] bg-white/[0.08] p-4 ring-1 ring-white/10"
                      >
                        <p
                          class="text-xs font-extrabold uppercase tracking-[0.08em] text-red-200"
                        >
                          Tanggal
                        </p>
                        <p class="mt-2 text-sm font-bold leading-7 text-white">
                          {{ selectedTestimonial.created_at || "Tidak tersedia" }}
                        </p>
                      </div>
                    </div>
                  </div>
                </aside>

                <div class="bg-white p-6 sm:p-8 lg:p-10">
                  <div>
                    <p
                      class="text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
                    >
                      Pengalaman Client
                    </p>

                    <h4
                      class="mt-2 text-2xl font-black tracking-[-0.03em] text-slate-950 sm:text-3xl"
                    >
                      Ulasan lengkap dari client
                    </h4>

                    <p class="mt-3 text-justify text-sm leading-7 text-slate-500">
                      Detail berikut merupakan testimoni yang sudah disetujui admin dan
                      ditampilkan secara publik pada halaman layanan.
                    </p>
                  </div>

                  <div
                    class="mt-8 rounded-[1.75rem] border border-red-100 bg-[linear-gradient(180deg,#fff7f7,#ffffff)] p-5 shadow-[0_14px_40px_rgba(2,6,23,0.05)] sm:p-6"
                  >
                    <div class="flex items-center justify-between gap-4">
                      <div>
                        <p class="text-sm font-black text-slate-950">Isi Testimoni</p>
                        <p
                          class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-400"
                        >
                          Pesan asli dari client
                        </p>
                      </div>

                      <div
                        class="shrink-0 rounded-full bg-red-50 px-4 py-2 text-sm font-black text-red-700 ring-1 ring-red-100"
                      >
                        {{ normalizeRating(selectedTestimonial.rating) }}/5
                      </div>
                    </div>

                    <p class="mt-6 text-justify text-base leading-9 text-slate-700">
                      “{{ selectedTestimonial.message }}”
                    </p>
                  </div>

                  <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div
                      class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4"
                    >
                      <p
                        class="text-xs font-extrabold uppercase tracking-[0.08em] text-slate-400"
                      >
                        Client
                      </p>
                      <p class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ selectedTestimonial.name }}
                      </p>
                    </div>

                    <div
                      class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4"
                    >
                      <p
                        class="text-xs font-extrabold uppercase tracking-[0.08em] text-slate-400"
                      >
                        Instansi
                      </p>
                      <p class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ selectedTestimonial.business_name || "-" }}
                      </p>
                    </div>

                    <div
                      class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4"
                    >
                      <p
                        class="text-xs font-extrabold uppercase tracking-[0.08em] text-slate-400"
                      >
                        Rating
                      </p>
                      <p class="mt-2 text-sm font-black text-slate-900">
                        {{ getRatingLabel(selectedTestimonial.rating) }}
                      </p>
                    </div>
                  </div>

                  <div
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <p class="text-xs leading-6 text-slate-400">
                      Tekan tombol tutup atau tombol ESC untuk kembali ke daftar
                      testimoni.
                    </p>

                    <button
                      type="button"
                      @click="closeTestimonialDetail"
                      class="premium-black-btn inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-extrabold text-white transition hover:-translate-y-[2px] hover:bg-red-700"
                    >
                      Tutup Detail
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Consultation Modal -->
    <Teleport to="body">
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="consultationState.open"
          class="fixed inset-0 z-[999] overflow-y-auto bg-slate-950/75 backdrop-blur-md"
          role="dialog"
          aria-modal="true"
          @click.self="closeConsultationModal"
        >
          <div
            class="flex min-h-full items-center justify-center px-4 py-6 sm:px-6 lg:px-8"
          >
            <div
              class="relative w-full max-w-6xl overflow-hidden rounded-[2rem] bg-white shadow-[0_40px_120px_rgba(2,6,23,0.35)]"
            >
              <button
                type="button"
                aria-label="Tutup modal konsultasi"
                @click="closeConsultationModal"
                class="absolute right-4 top-4 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md transition hover:bg-red-50 hover:text-red-700"
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
                    stroke-width="2.4"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>

              <div
                class="grid max-h-[92vh] grid-cols-1 overflow-y-auto lg:grid-cols-[0.88fr_1.12fr]"
              >
                <div
                  class="relative overflow-hidden bg-[linear-gradient(160deg,#0f172a,#111827_55%,#1e293b)] p-6 text-white sm:p-8 lg:p-10"
                >
                  <div
                    class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-red-500/20 blur-3xl"
                  />
                  <div
                    class="pointer-events-none absolute bottom-0 left-0 h-32 w-32 rounded-full bg-white/10 blur-3xl"
                  />

                  <div class="relative z-10">
                    <div
                      class="inline-flex items-center gap-3 rounded-full bg-white/10 px-4 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-200 ring-1 ring-white/10"
                    >
                      <span class="text-base">
                        {{ getConsultationEmoji(consultationState.type) }}
                      </span>
                      {{ getConsultationShortLabel(consultationState.type) }}
                    </div>

                    <h3
                      class="mt-5 text-2xl font-black leading-tight tracking-[-0.03em] sm:text-3xl"
                    >
                      {{ consultationState.title }}
                    </h3>

                    <p
                      class="mt-4 max-w-md text-justify text-sm leading-8 text-slate-300 sm:text-base"
                    >
                      {{
                        consultationState.subtitle ||
                        getConsultationHint(consultationState.type)
                      }}
                    </p>

                    <div class="mt-8 space-y-4">
                      <div
                        v-for="(item, index) in [
                          'Layanan Dipilih',
                          'Pilih Paket',
                          'Hubungi WhatsApp',
                        ]"
                        :key="item"
                        class="flex items-start gap-4 rounded-[1.25rem] bg-white/[0.06] p-4 ring-1 ring-white/10"
                      >
                        <div
                          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black text-white"
                          :class="
                            index === 2
                              ? 'bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]'
                              : 'bg-white/10'
                          "
                        >
                          {{ index + 1 }}
                        </div>

                        <div>
                          <h4
                            class="text-sm font-black uppercase tracking-[0.08em] text-white"
                          >
                            {{ item }}
                          </h4>

                          <p class="mt-1 text-justify text-sm leading-7 text-slate-300">
                            <template v-if="index === 0">
                              {{
                                consultationState.service?.title ||
                                getServiceTypeLabel(consultationState.type)
                              }}
                            </template>

                            <template v-else-if="index === 1">
                              Paket diambil dari database berdasarkan layanan induk.
                            </template>

                            <template v-else> Nomor tujuan: 087879175646 </template>
                          </p>
                        </div>
                      </div>
                    </div>

                    <div
                      class="mt-8 rounded-[1.25rem] bg-white/[0.08] p-4 ring-1 ring-white/10"
                    >
                      <p
                        class="text-xs font-extrabold uppercase tracking-[0.08em] text-red-200"
                      >
                        Catatan
                      </p>

                      <p class="mt-2 text-justify text-sm leading-7 text-slate-300">
                        Jika paket belum tampil, pastikan admin sudah menambahkan paket
                        dan memilih layanan induknya.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="bg-white p-5 sm:p-7 lg:p-8">
                  <div class="mb-6">
                    <p
                      class="text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
                    >
                      Pilihan Tersedia
                    </p>

                    <h4
                      class="mt-2 text-2xl font-black tracking-[-0.03em] text-slate-950"
                    >
                      Silakan pilih paket layanan
                    </h4>

                    <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                      Setelah memilih paket, Anda akan langsung diarahkan ke WhatsApp.
                    </p>
                  </div>

                  <div
                    v-if="currentConsultationOptions.length"
                    class="grid grid-cols-1 gap-5 lg:grid-cols-2"
                  >
                    <article
                      v-for="option in currentConsultationOptions"
                      :key="`${option.service_catalog_id}-${option.id || option.title}`"
                      class="group rounded-[1.45rem] border border-slate-200 bg-white p-5 shadow-[0_10px_30px_rgba(2,6,23,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_20px_50px_rgba(2,6,23,0.10)]"
                    >
                      <div class="flex items-start justify-between gap-4">
                        <div>
                          <span
                            class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
                          >
                            {{
                              option.service_title ||
                              getServiceTypeLabel(consultationState.type)
                            }}
                          </span>

                          <h5 class="mt-3 text-xl font-black leading-snug text-slate-950">
                            {{ option.title }}
                          </h5>
                        </div>

                        <div
                          class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-xl"
                        >
                          {{ getConsultationEmoji(consultationState.type) }}
                        </div>
                      </div>

                      <p
                        v-if="option.subtitle"
                        class="mt-3 text-sm font-bold uppercase tracking-[0.08em] text-slate-500"
                      >
                        {{ option.subtitle }}
                      </p>

                      <div class="mt-5">
                        <p
                          class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                        >
                          Harga
                        </p>

                        <p class="mt-2 text-2xl font-black text-slate-950">
                          {{ option.price }}
                        </p>
                      </div>

                      <p class="mt-4 text-justify text-sm leading-7 text-slate-600">
                        {{ option.description }}
                      </p>

                      <ul v-if="option.features?.length" class="mt-5 space-y-2">
                        <li
                          v-for="feature in option.features.slice(0, 4)"
                          :key="feature"
                          class="flex items-start gap-3 text-sm leading-7 text-slate-600"
                        >
                          <span
                            class="mt-[0.55rem] h-2 w-2 shrink-0 rounded-full bg-red-500"
                          />
                          <span class="text-justify">{{ feature }}</span>
                        </li>
                      </ul>

                      <button
                        type="button"
                        @click="chooseConsultationOption(option)"
                        class="premium-black-btn group relative mt-6 inline-flex min-h-[50px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-5 text-sm font-extrabold text-white shadow-[0_10px_24px_rgba(220,38,38,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_18px_34px_rgba(220,38,38,0.24)]"
                      >
                        <span
                          class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
                        />
                        <span class="relative z-10">Pilih & Hubungi WhatsApp</span>
                      </button>
                    </article>
                  </div>

                  <div
                    v-else
                    class="rounded-[1.4rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
                  >
                    <p class="text-base font-black text-slate-800">
                      Paket layanan belum tersedia.
                    </p>

                    <p
                      class="mx-auto mt-2 max-w-xl text-justify text-sm leading-7 text-slate-500"
                    >
                      Tambahkan paket di admin dan pastikan memilih layanan induk yang
                      sesuai.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Testimonial Form Modal -->
    <Teleport to="body">
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="testimonialModalOpen"
          class="fixed inset-0 z-[1000] overflow-y-auto bg-slate-950/75 backdrop-blur-md"
          role="dialog"
          aria-modal="true"
          @click.self="closeTestimonialModal"
        >
          <div
            class="flex min-h-full items-center justify-center px-4 py-6 sm:px-6 lg:px-8"
          >
            <div
              class="relative w-full max-w-6xl overflow-hidden rounded-[2rem] bg-white shadow-[0_40px_120px_rgba(2,6,23,0.35)]"
            >
              <button
                type="button"
                aria-label="Tutup modal testimoni"
                @click="closeTestimonialModal"
                class="absolute right-4 top-4 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md transition hover:bg-red-50 hover:text-red-700"
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
                    stroke-width="2.4"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>

              <div
                class="grid max-h-[92vh] grid-cols-1 overflow-y-auto lg:grid-cols-[0.9fr_1.1fr]"
              >
                <div
                  class="relative overflow-hidden bg-[linear-gradient(160deg,#0f172a,#111827_55%,#1e293b)] p-6 text-white sm:p-8 lg:p-10"
                >
                  <div
                    class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-red-500/20 blur-3xl"
                  />
                  <div
                    class="pointer-events-none absolute bottom-0 left-0 h-32 w-32 rounded-full bg-white/10 blur-3xl"
                  />

                  <div class="relative z-10">
                    <div
                      class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-200 ring-1 ring-white/10"
                    >
                      <span class="h-2 w-2 rounded-full bg-red-400" />
                      Form Testimoni
                    </div>

                    <h3
                      class="mt-5 text-[2rem] font-black leading-[1.02] tracking-[-0.04em] sm:text-[2.5rem]"
                    >
                      Kirim
                      <span class="text-red-400">testimoni Anda</span>
                    </h3>

                    <p
                      class="mt-4 max-w-md text-justify text-sm leading-8 text-slate-300 sm:text-base"
                    >
                      Testimoni akan masuk ke admin terlebih dahulu. Setelah disetujui,
                      testimoni akan tampil di halaman layanan.
                    </p>

                    <div class="mt-8 space-y-4">
                      <div
                        v-for="(item, index) in [
                          'Isi data',
                          'Review admin',
                          'Tampil di website',
                        ]"
                        :key="item"
                        class="flex items-start gap-4 rounded-[1.25rem] bg-white/[0.06] p-4 ring-1 ring-white/10"
                      >
                        <div
                          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black text-white"
                          :class="
                            index === 2
                              ? 'bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]'
                              : 'bg-white/10'
                          "
                        >
                          {{ index + 1 }}
                        </div>

                        <div>
                          <h4
                            class="text-sm font-black uppercase tracking-[0.08em] text-white"
                          >
                            {{ item }}
                          </h4>

                          <p class="mt-1 text-justify text-sm leading-7 text-slate-300">
                            <template v-if="index === 0">
                              Masukkan nama, layanan, rating, dan pengalaman Anda.
                            </template>

                            <template v-else-if="index === 1">
                              Admin melakukan approve dari menu Custom Layanan Jasa.
                            </template>

                            <template v-else>
                              Testimoni approved akan muncul di halaman user.
                            </template>
                          </p>
                        </div>
                      </div>
                    </div>

                    <div
                      class="mt-8 rounded-[1.25rem] bg-white/[0.08] p-4 ring-1 ring-white/10"
                    >
                      <p
                        class="text-xs font-extrabold uppercase tracking-[0.08em] text-red-200"
                      >
                        Informasi
                      </p>

                      <p class="mt-2 text-justify text-sm leading-7 text-slate-300">
                        Data testimoni tidak langsung dipublikasikan untuk menjaga
                        kualitas konten dan kepercayaan pengunjung website.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="bg-white p-5 sm:p-7 lg:p-8">
                  <div class="mb-6">
                    <p
                      class="text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
                    >
                      Testimoni Client
                    </p>

                    <h4
                      class="mt-2 text-2xl font-black tracking-[-0.03em] text-slate-950"
                    >
                      Form testimoni layanan
                    </h4>

                    <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                      Pilihan jenis layanan diambil langsung dari data layanan aktif.
                    </p>
                  </div>

                  <div
                    v-if="flashSuccess || testimonialForm.recentlySuccessful"
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-700"
                  >
                    {{ flashSuccess || "Testimoni berhasil dikirim." }}
                  </div>

                  <form class="space-y-6" @submit.prevent="submitTestimonial">
                    <div class="grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">
                          Nama Client
                        </label>

                        <input
                          v-model="testimonialForm.name"
                          type="text"
                          autocomplete="name"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-sm text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                          placeholder="Masukkan nama"
                        />

                        <p
                          v-if="testimonialForm.errors.name"
                          class="mt-2 text-sm text-red-600"
                        >
                          {{ testimonialForm.errors.name }}
                        </p>
                      </div>

                      <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">
                          Nama Usaha / Instansi
                        </label>

                        <input
                          v-model="testimonialForm.business_name"
                          type="text"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-sm text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                          placeholder="Opsional"
                        />

                        <p
                          v-if="testimonialForm.errors.business_name"
                          class="mt-2 text-sm text-red-600"
                        >
                          {{ testimonialForm.errors.business_name }}
                        </p>
                      </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">
                          Jenis Layanan
                        </label>

                        <select
                          v-model="testimonialForm.service_type"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                        >
                          <option
                            v-for="option in consultationOptions"
                            :key="option"
                            :value="option"
                          >
                            {{ option }}
                          </option>
                        </select>

                        <p
                          v-if="testimonialForm.errors.service_type"
                          class="mt-2 text-sm text-red-600"
                        >
                          {{ testimonialForm.errors.service_type }}
                        </p>
                      </div>

                      <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">
                          Rating
                        </label>

                        <div class="grid grid-cols-5 gap-2">
                          <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            @click="testimonialForm.rating = star"
                            :aria-label="`Beri rating ${star} bintang`"
                            :class="[
                              'flex h-14 items-center justify-center rounded-2xl border text-lg font-black transition-all duration-300',
                              testimonialForm.rating >= star
                                ? 'border-red-200 bg-red-50 text-red-600 shadow-[0_8px_20px_rgba(239,68,68,0.10)]'
                                : 'border-slate-200 bg-slate-50/60 text-slate-400 hover:border-red-200 hover:bg-red-50/50 hover:text-red-500',
                            ]"
                          >
                            ★
                          </button>
                        </div>

                        <p
                          v-if="testimonialForm.errors.rating"
                          class="mt-2 text-sm text-red-600"
                        >
                          {{ testimonialForm.errors.rating }}
                        </p>
                      </div>
                    </div>

                    <div>
                      <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="block text-sm font-bold text-slate-800">
                          Isi Testimoni
                        </label>

                        <span class="text-xs font-semibold text-slate-400">
                          {{
                            testimonialForm.message ? testimonialForm.message.length : 0
                          }}
                          karakter
                        </span>
                      </div>

                      <textarea
                        v-model="testimonialForm.message"
                        rows="7"
                        class="w-full rounded-[1.5rem] border border-slate-200 bg-slate-50/60 px-4 py-4 text-sm leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                        placeholder="Ceritakan pengalaman Anda menggunakan layanan kami..."
                      />

                      <p
                        v-if="testimonialForm.errors.message"
                        class="mt-2 text-sm text-red-600"
                      >
                        {{ testimonialForm.errors.message }}
                      </p>
                    </div>

                    <button
                      type="submit"
                      :disabled="testimonialForm.processing"
                      class="premium-black-btn group relative inline-flex min-h-[56px] w-full items-center justify-center overflow-hidden rounded-[1.3rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-6 text-[1rem] font-extrabold text-white shadow-[0_16px_34px_rgba(220,38,38,0.22)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_46px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-70"
                    >
                      <span
                        class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
                      />

                      <span class="relative z-10">
                        {{
                          testimonialForm.processing
                            ? "Mengirim Testimoni..."
                            : "Kirim Testimoni"
                        }}
                      </span>
                    </button>

                    <p class="text-center text-xs leading-6 text-slate-400">
                      Testimoni tidak langsung tampil sebelum di-approve admin.
                    </p>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<style scoped>
.premium-black-btn {
  background: linear-gradient(135deg, #081120 0%, #0f172a 55%, #111827 100%) !important;
  color: #ffffff !important;
}

.premium-black-btn:hover,
.premium-black-btn:active,
.premium-black-btn:focus-visible {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 56%, #991b1b 100%) !important;
}

.premium-black-btn:focus-visible {
  outline: 3px solid rgba(239, 68, 68, 0.28);
  outline-offset: 3px;
}

.layanan-jasa-mobile-page :deep([data-aos]) {
  will-change: transform, opacity;
  backface-visibility: hidden;
}

.layanan-jasa-mobile-page :deep(.aos-animate) {
  will-change: auto;
}

.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 920px;
}

.lcp-hero-content {
  content-visibility: visible;
  contain: none;
}

.layanan-jasa-mobile-page :deep([aria-hidden="true"]) {
  pointer-events: none;
}

.ticker-inner {
  animation: tickerMove 30s linear infinite;
}

.ticker-wrap:hover .ticker-inner {
  animation-play-state: paused;
}

.testimonial-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(220, 38, 38, 0.45) rgba(241, 245, 249, 0.9);
}

.testimonial-scroll::-webkit-scrollbar {
  height: 10px;
}

.testimonial-scroll::-webkit-scrollbar-track {
  border-radius: 999px;
  background: rgba(241, 245, 249, 0.9);
}

.testimonial-scroll::-webkit-scrollbar-thumb {
  border-radius: 999px;
  background: linear-gradient(135deg, #ef4444, #dc2626, #991b1b);
}

.testimonial-scroll::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c, #7f1d1d);
}

.testimonial-filter-scroll {
  scrollbar-width: none;
}

.testimonial-filter-scroll::-webkit-scrollbar {
  display: none;
}

@keyframes tickerMove {
  0% {
    transform: translateX(0);
  }

  100% {
    transform: translateX(-50%);
  }
}

.testimonial-scroll {
  -webkit-overflow-scrolling: touch;
}

@media (prefers-reduced-motion: reduce) {
  .layanan-jasa-mobile-page :deep([data-aos]),
  .layanan-jasa-mobile-page :deep([data-aos].aos-animate) {
    opacity: 1 !important;
    transform: none !important;
    transition-duration: 0.01ms !important;
  }

  .ticker-inner,
  .absolute.inset-0.-translate-x-\[140\%\],
  .absolute.inset-0.-translate-x-\[130\%\] {
    animation: none;
    transition-duration: 0.01ms !important;
  }
}
</style>

<style scoped>
/* Layanan Jasa Mobile Professional — frontend-only, mobile-first refinements */
@media (max-width: 639px) {
  .layanan-jasa-mobile-page,
  .layanan-jasa-mobile-page * {
    box-sizing: border-box;
  }

  .layanan-jasa-mobile-page {
    width: 100%;
    max-width: 100%;
    overflow-x: clip;
    background: #ffffff;
  }

  .layanan-jasa-mobile-page :is(section, article, div, aside, form, main) {
    min-width: 0;
  }

  .layanan-jasa-mobile-page :is(button, a, input, select, textarea, img, label) {
    max-width: 100%;
  }

  .layanan-jasa-mobile-page :is(button, a) {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
  }

  .layanan-jasa-mobile-page p {
    text-align: left !important;
  }

  .layanan-jasa-mobile-page h1 {
    font-size: clamp(2rem, 10vw, 2.45rem) !important;
    line-height: 1.04 !important;
    letter-spacing: -0.055em !important;
  }

  .layanan-jasa-mobile-page h2 {
    font-size: clamp(1.55rem, 7.2vw, 1.9rem) !important;
    line-height: 1.1 !important;
    letter-spacing: -0.048em !important;
  }

  .layanan-jasa-mobile-page h3 {
    font-size: 1.16rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .layanan-jasa-mobile-page .premium-black-btn {
    border-radius: 1rem !important;
  }

  /* Hero */
  .layanan-jasa-mobile-page section:first-of-type {
    padding-top: 5.55rem !important;
    padding-bottom: 2.6rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mx-auto.w-full.max-w-7xl {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .grid.grid-cols-1.items-center {
    gap: 1.55rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mb-5.inline-flex {
    margin-bottom: 1rem !important;
    max-width: 100% !important;
    padding: 0.55rem 0.78rem !important;
    border-radius: 999px !important;
    font-size: 0.62rem !important;
    letter-spacing: 0.06em !important;
    box-shadow: 0 10px 26px rgba(2, 6, 23, 0.055) !important;
  }

  .layanan-jasa-mobile-page section:first-of-type p.mt-6 {
    margin-top: 1rem !important;
    font-size: 0.9rem !important;
    line-height: 1.72 !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-8.grid {
    margin-top: 1.25rem !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.72rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-8.grid button,
  .layanan-jasa-mobile-page section:first-of-type .relative.z-10 > button {
    min-height: 48px !important;
    border-radius: 1rem !important;
    font-size: 0.86rem !important;
    padding-inline: 1rem !important;
  }

  .layanan-jasa-mobile-page
    section:first-of-type
    .relative.overflow-hidden.rounded-\[1\.9rem\] {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 20px 56px rgba(2, 6, 23, 0.22) !important;
  }

  .layanan-jasa-mobile-page
    section:first-of-type
    .relative.overflow-hidden.rounded-\[1\.9rem\]
    h3 {
    margin-top: 1rem !important;
    font-size: 1.28rem !important;
    line-height: 1.18 !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-6.space-y-4 {
    margin-top: 1rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-6.space-y-4 > div {
    gap: 0.75rem !important;
    padding: 0.85rem !important;
    border-radius: 1rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-6.space-y-4 .h-10.w-10 {
    width: 2.35rem !important;
    height: 2.35rem !important;
    border-radius: 0.9rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-6.space-y-4 h4 {
    font-size: 0.92rem !important;
  }

  .layanan-jasa-mobile-page section:first-of-type .mt-6.space-y-4 p {
    font-size: 0.82rem !important;
    line-height: 1.55 !important;
  }

  /* Ticker */
  .layanan-jasa-mobile-page .ticker-inner span {
    margin-left: 1rem !important;
    margin-right: 1rem !important;
    font-size: 0.64rem !important;
    letter-spacing: 0.12em !important;
  }

  /* Services */
  .layanan-jasa-mobile-page #layanan-list {
    padding-top: 3.25rem !important;
    padding-bottom: 3.25rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list .mx-auto.max-w-7xl {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list .mx-auto.mb-12 {
    margin-bottom: 1.75rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list .mx-auto.mb-5.inline-flex,
  .layanan-jasa-mobile-page #testimoni-layanan-jasa .mx-auto.mb-5.inline-flex {
    margin-bottom: 1rem !important;
    padding: 0.55rem 0.8rem !important;
    font-size: 0.62rem !important;
    letter-spacing: 0.07em !important;
  }

  .layanan-jasa-mobile-page #layanan-list h2 span,
  .layanan-jasa-mobile-page #testimoni-layanan-jasa h2 span {
    display: inline !important;
  }

  .layanan-jasa-mobile-page #layanan-list .mx-auto.mt-4,
  .layanan-jasa-mobile-page #testimoni-layanan-jasa .mx-auto.mt-5 {
    margin-top: 0.8rem !important;
    font-size: 0.9rem !important;
    line-height: 1.65 !important;
  }

  .layanan-jasa-mobile-page #layanan-list .grid.grid-cols-1.gap-6 {
    gap: 1rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 14px 34px rgba(2, 6, 23, 0.065) !important;
  }

  .layanan-jasa-mobile-page
    #layanan-list
    article
    .relative.z-10.flex.items-start.justify-between {
    align-items: flex-start !important;
    gap: 0.75rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .h-16.w-16 {
    width: 3rem !important;
    height: 3rem !important;
    border-radius: 1rem !important;
    font-size: 1.25rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .inline-flex.rounded-full {
    padding: 0.45rem 0.7rem !important;
    font-size: 0.6rem !important;
    letter-spacing: 0.06em !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .relative.z-10.mt-8 {
    margin-top: 1.2rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article h3 {
    font-size: 1.32rem !important;
    line-height: 1.15 !important;
  }

  .layanan-jasa-mobile-page #layanan-list article p.mt-4 {
    margin-top: 0.75rem !important;
    font-size: 0.9rem !important;
    line-height: 1.65 !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .relative.z-10.mt-7.space-y-3 {
    margin-top: 1rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .relative.z-10.mt-7.space-y-3 > div {
    border-radius: 1rem !important;
    padding: 0.75rem !important;
    gap: 0.65rem !important;
  }

  .layanan-jasa-mobile-page
    #layanan-list
    article
    .relative.z-10.mt-7.space-y-3
    span.text-justify {
    font-size: 0.84rem !important;
    line-height: 1.55 !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .relative.z-10.mt-7.rounded-2xl {
    margin-top: 1rem !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article .relative.z-10.mt-auto.pt-7 {
    padding-top: 1.15rem !important;
  }

  .layanan-jasa-mobile-page #layanan-list article button {
    min-height: 48px !important;
    border-radius: 1rem !important;
    font-size: 0.86rem !important;
  }

  /* Testimonials */
  .layanan-jasa-mobile-page #testimoni-layanan-jasa {
    padding-top: 3.25rem !important;
    padding-bottom: 3.25rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .mx-auto.max-w-7xl {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .mx-auto.mb-10 {
    margin-bottom: 1.6rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .overflow-hidden.rounded-\[2rem\] {
    border-radius: 1.2rem !important;
    padding: 0.72rem !important;
    box-shadow: 0 16px 44px rgba(15, 23, 42, 0.08) !important;
  }

  .layanan-jasa-mobile-page
    #testimoni-layanan-jasa
    .relative.overflow-hidden.rounded-\[1\.7rem\] {
    border-radius: 1rem !important;
    padding: 0.85rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .rounded-\[1\.5rem\] {
    border-radius: 1rem !important;
    padding: 0.85rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .flex.items-start.gap-4 {
    gap: 0.75rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .h-12.w-12 {
    width: 2.75rem !important;
    height: 2.75rem !important;
    border-radius: 0.95rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa h4 {
    font-size: 1rem !important;
    line-height: 1.2 !important;
  }

  .layanan-jasa-mobile-page .testimonial-filter-scroll {
    margin-left: -0.25rem !important;
    margin-right: -0.25rem !important;
    padding-left: 0.25rem !important;
    padding-right: 0.25rem !important;
    padding-bottom: 0.35rem !important;
    -webkit-overflow-scrolling: touch;
  }

  .layanan-jasa-mobile-page .testimonial-filter-scroll button {
    min-height: 40px !important;
    padding-left: 0.9rem !important;
    padding-right: 0.9rem !important;
    font-size: 0.78rem !important;
  }

  .layanan-jasa-mobile-page .testimonial-scroll {
    gap: 0.85rem !important;
    padding-bottom: 1rem !important;
    padding-right: 0 !important;
    -webkit-overflow-scrolling: touch;
  }

  .layanan-jasa-mobile-page .testimonial-scroll article {
    width: 92% !important;
    min-height: 0 !important;
    border-radius: 1.2rem !important;
    padding: 1rem !important;
    box-shadow: 0 16px 42px rgba(15, 23, 42, 0.08) !important;
  }

  .layanan-jasa-mobile-page .testimonial-scroll article .h-\[3\.25rem\].w-\[3\.25rem\] {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 1rem !important;
  }

  .layanan-jasa-mobile-page .testimonial-scroll article h3 {
    font-size: 0.95rem !important;
  }

  .layanan-jasa-mobile-page .testimonial-scroll article p.relative.z-10.mt-4 {
    margin-top: 0.8rem !important;
    font-size: 0.86rem !important;
    line-height: 1.65 !important;
  }

  .layanan-jasa-mobile-page .testimonial-scroll article .relative.z-10.mt-6.rounded-2xl {
    margin-top: 1rem !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
  }

  .layanan-jasa-mobile-page #testimoni-layanan-jasa .mt-8.flex.justify-center button {
    width: 100% !important;
    min-height: 50px !important;
    border-radius: 1rem !important;
  }

  /* General mobile safety */
  .layanan-jasa-mobile-page :is(input, select, textarea) {
    width: 100% !important;
    border-radius: 1rem !important;
    font-size: 0.86rem !important;
  }

  .layanan-jasa-mobile-page textarea {
    line-height: 1.65 !important;
  }

  .layanan-jasa-mobile-page .rounded-\[1\.25rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.3rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.4rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.5rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.65rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.7rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.75rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.85rem\],
  .layanan-jasa-mobile-page .rounded-\[1\.9rem\],
  .layanan-jasa-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .layanan-jasa-mobile-page .grid.lg\:grid-cols-3,
  .layanan-jasa-mobile-page .grid.lg\:grid-cols-\[0\.88fr_1\.12fr\],
  .layanan-jasa-mobile-page .grid.lg\:grid-cols-\[0\.85fr_1\.15fr\],
  .layanan-jasa-mobile-page .grid.sm\:grid-cols-3 {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .layanan-jasa-mobile-page .truncate {
    min-width: 0;
  }

  .layanan-jasa-mobile-page .line-clamp-6 {
    display: block !important;
    -webkit-line-clamp: unset !important;
    overflow: visible !important;
  }

  .layanan-jasa-mobile-page .break-all,
  .layanan-jasa-mobile-page .break-words,
  .layanan-jasa-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }
}

/* Mobile modal polish: Teleport renders outside the page root, so this is mobile-only global targeting. */
@media (max-width: 639px) {
  body > div.fixed[role="dialog"] .flex.min-h-full {
    align-items: flex-end !important;
    justify-content: center !important;
    padding: 0.75rem !important;
  }

  body > div.fixed[role="dialog"] .relative.w-full.max-w-5xl,
  body > div.fixed[role="dialog"] .relative.w-full.max-w-6xl,
  body > div.fixed[role="dialog"] .relative.w-full.max-w-3xl {
    max-width: 100% !important;
    max-height: 92svh !important;
    border-radius: 1.25rem !important;
    overflow: hidden !important;
  }

  body > div.fixed[role="dialog"] .grid.max-h-\[92vh\] {
    max-height: 92svh !important;
    grid-template-columns: minmax(0, 1fr) !important;
    overflow-y: auto !important;
  }

  body > div.fixed[role="dialog"] aside,
  body > div.fixed[role="dialog"] .bg-white.p-6,
  body
    > div.fixed[role="dialog"]
    .relative.overflow-hidden.bg-\[linear-gradient\(160deg\,\#0f172a\,\#111827_55\%\,\#1e293b\)\],
  body > div.fixed[role="dialog"] .bg-white {
    padding: 1rem !important;
  }

  body > div.fixed[role="dialog"] h3 {
    font-size: 1.4rem !important;
    line-height: 1.15 !important;
  }

  body > div.fixed[role="dialog"] h4 {
    font-size: 1.2rem !important;
    line-height: 1.2 !important;
  }

  body > div.fixed[role="dialog"] p {
    text-align: left !important;
    line-height: 1.65 !important;
  }

  body > div.fixed[role="dialog"] button.absolute.right-4.top-4 {
    right: 0.75rem !important;
    top: 0.75rem !important;
    width: 2.5rem !important;
    height: 2.5rem !important;
  }

  body > div.fixed[role="dialog"] button:not(.absolute) {
    width: 100% !important;
    min-height: 46px !important;
    border-radius: 1rem !important;
    justify-content: center !important;
  }

  body > div.fixed[role="dialog"] input,
  body > div.fixed[role="dialog"] select,
  body > div.fixed[role="dialog"] textarea {
    width: 100% !important;
    min-height: 46px !important;
    border-radius: 1rem !important;
    font-size: 0.86rem !important;
  }

  body > div.fixed[role="dialog"] textarea {
    min-height: 108px !important;
    line-height: 1.6 !important;
  }

  body > div.fixed[role="dialog"] .grid.sm\:grid-cols-3,
  body > div.fixed[role="dialog"] .grid.lg\:grid-cols-\[0\.88fr_1\.12fr\],
  body > div.fixed[role="dialog"] .grid.lg\:grid-cols-\[0\.85fr_1\.15fr\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  body > div.fixed[role="dialog"] .rounded-\[1\.25rem\],
  body > div.fixed[role="dialog"] .rounded-\[1\.75rem\],
  body > div.fixed[role="dialog"] .rounded-\[2rem\] {
    border-radius: 1rem !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .layanan-jasa-mobile-page *,
  .layanan-jasa-mobile-page *::before,
  .layanan-jasa-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

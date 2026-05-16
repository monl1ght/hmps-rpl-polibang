<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import AOS from "aos";
import "aos/dist/aos.css";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  consultationHero: {
    type: Object,
    default: () => ({}),
  },
  admins: {
    type: Array,
    default: () => [],
  },
  ticks: {
    type: Array,
    default: () => [],
  },
  steps: {
    type: Array,
    default: () => [],
  },
  testimonials: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const defaultWhatsappTemplate = `Halo {admin_name}, saya ingin konsultasi melalui layanan HMPS RPL.

Saya memilih konsultasi dengan:
Nama Admin: {admin_name}
Bidang/Skill: {admin_skills}

Mohon informasinya ya. Terima kasih.`;

const fallbackHero = {
  badge: "Layanan Konsultasi HMPS RPL",
  title: "Pilih admin konsultasi",
  titleHighlight: "sesuai kebutuhanmu",
  description:
    "Pilih admin HMPS RPL berdasarkan bidang keahlian seperti website, desain grafis, editing video, instalasi software, atau kebutuhan konsultasi lainnya. Layanan konsultasi tersedia pada hari kerja sesuai jam operasional.",
  primaryButtonLabel: "Pilih Admin Konsultasi",
  primaryButtonUrl: "#pilih-admin",
  secondaryButtonLabel: "Konsultasi via WhatsApp",
  secondaryButtonUrl: "whatsapp-selected",
  meta: {
    status_card_title: "Status Layanan",
    status_card_subtitle:
      "Layanan konsultasi dibuka sesuai jadwal operasional yang ditentukan admin.",
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
      "Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda agar komunikasi lebih tepat sasaran.",
    whatsapp_template: defaultWhatsappTemplate,
  },
};

const hero = computed(() => ({
  ...fallbackHero,
  ...(props.consultationHero || {}),
  meta: {
    ...fallbackHero.meta,
    ...(props.consultationHero?.meta || {}),
  },
}));

const fallbackTicks = [
  "KONSULTASI HMPS RPL",
  "PILIH ADMIN",
  "BERDASARKAN SKILL",
  "SENIN - JUMAT",
  "08.00 - 20.00 WIB",
  "WEBSITE",
  "DESAIN GRAFIS",
  "EDITOR VIDEO",
  "LANGSUNG WHATSAPP",
];

const fallbackSteps = [
  {
    number: "01",
    title: "Lihat skill admin",
    description:
      "Pengguna dapat melihat bidang keahlian setiap admin, misalnya Website, Desain Grafis, Editor Video, Instalasi Software, atau bidang lainnya.",
    icon: "🧩",
  },
  {
    number: "02",
    title: "Pilih admin konsultasi",
    description:
      "Pengguna dapat memilih admin yang paling sesuai dengan kebutuhan konsultasi sebelum diarahkan ke WhatsApp.",
    icon: "👤",
  },
  {
    number: "03",
    title: "Konsultasi sesuai jam layanan",
    description:
      "Layanan konsultasi dibuka sesuai jadwal operasional global yang dapat diubah melalui menu Custom Konsultasi.",
    icon: "⏰",
  },
];

const dayMap = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
const dayOrder = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];

const getJakartaDate = () => {
  return new Date(
    new Date().toLocaleString("en-US", {
      timeZone: "Asia/Jakarta",
    })
  );
};

const now = ref(getJakartaDate());
const selectedAdminId = ref(null);
const selectedRatingFilter = ref("all");
const testimonialsTrack = ref(null);
const selectedTestimonial = ref(null);
const testimonialModalOpen = ref(false);

let timer = null;

const testimonialForm = useForm({
  name: "",
  role: "",
  service_type: "Konsultasi HMPS RPL",
  rating: 5,
  message: "",
  emoji: "💬",
});

const ticks = computed(() =>
  Array.isArray(props.ticks) && props.ticks.length ? props.ticks : fallbackTicks
);

const steps = computed(() => {
  const source =
    Array.isArray(props.steps) && props.steps.length ? props.steps : fallbackSteps;

  return source.map((step, index) => ({
    ...step,
    number: step.number || String(index + 1).padStart(2, "0"),
    icon: step.icon || fallbackSteps[index % fallbackSteps.length]?.icon || "✨",
  }));
});

const normalizePhoneNumber = (phone) => {
  if (!phone) return "";

  const number = String(phone).replace(/[^0-9]/g, "");

  if (number.startsWith("0")) return `62${number.slice(1)}`;
  if (number.startsWith("8")) return `62${number}`;

  return number;
};

const normalizeSkillList = (admin) => {
  const directSkill =
    admin?.skills ??
    admin?.skill ??
    admin?.skillsText ??
    admin?.skillText ??
    admin?.expertise ??
    admin?.specialties ??
    "";

  if (Array.isArray(directSkill)) {
    const parsedArray = directSkill
      .map((item) => String(item || "").trim())
      .filter(Boolean)
      .filter((item) => !/^hari\s+/i.test(item))
      .slice(0, 10);

    if (parsedArray.length) return parsedArray;
  }

  const parsedDirect = String(directSkill || "")
    .split(/[,;|\n]/)
    .map((item) => item.trim())
    .filter(Boolean)
    .filter((item) => !/^hari\s+/i.test(item))
    .slice(0, 10);

  if (parsedDirect.length) return parsedDirect;

  const roleText = String(admin?.role || "")
    .replace(/^admin\s+konsultasi\s*/i, "")
    .trim();

  const parsedFromRole = roleText
    .split(/[,;|\n]/)
    .map((item) => item.trim())
    .filter(Boolean)
    .filter((item) => !/^hari\s+/i.test(item))
    .slice(0, 10);

  if (parsedFromRole.length) return parsedFromRole;

  return ["Konsultasi Umum"];
};

const admins = computed(() => {
  const source = Array.isArray(props.admins) ? props.admins : [];

  return source.map((admin, index) => ({
    ...admin,
    id: admin.id ?? `admin-${index}`,
    label: admin.label || `Admin ${index + 1}`,
    name: admin.name || "Admin HMPS RPL",
    role: admin.role || "Admin Konsultasi HMPS RPL",
    emoji: admin.emoji || "👤",
    phoneDisplay:
      admin.phoneDisplay || admin.phone_display || admin.phoneWa || admin.phone_wa || "",
    phoneWa: admin.phoneWa || admin.phone_wa || admin.phone || "",
    skills: normalizeSkillList(admin),
    sortOrder: Number(admin.sortOrder ?? admin.sort_order ?? index),
  }));
});

const sortedAdmins = computed(() => {
  return [...admins.value].sort((a, b) => {
    if (a.sortOrder !== b.sortOrder) return a.sortOrder - b.sortOrder;

    return String(a.name).localeCompare(String(b.name));
  });
});

const selectedAdmin = computed(() => {
  if (!sortedAdmins.value.length) return null;

  return (
    sortedAdmins.value.find(
      (admin) => String(admin.id) === String(selectedAdminId.value)
    ) || sortedAdmins.value[0]
  );
});

const currentDay = computed(() => dayMap[now.value.getDay()]);
const currentHour = computed(() => now.value.getHours());
const currentMinute = computed(() => now.value.getMinutes());
const currentMinutes = computed(() => currentHour.value * 60 + currentMinute.value);

const currentTimeDisplay = computed(() => {
  const hh = String(currentHour.value).padStart(2, "0");
  const mm = String(currentMinute.value).padStart(2, "0");

  return `${hh}.${mm}`;
});

const timeToMinutes = (time) => {
  if (!time) return 0;

  const normalized = String(time).replace(".", ":");
  const [hour, minute] = normalized.split(":").map((item) => Number(item || 0));

  return hour * 60 + minute;
};

const formatTime = (time) => {
  if (!time) return "00.00";

  const normalized = String(time).replace(".", ":");
  const [hour, minute] = normalized.split(":");

  return `${String(hour || "00").padStart(2, "0")}.${String(minute || "00").padStart(
    2,
    "0"
  )}`;
};

const parseServiceDays = (value) => {
  if (Array.isArray(value)) {
    const validDays = value.filter((day) => dayOrder.includes(day));
    return validDays.length ? validDays : fallbackHero.meta.service_days;
  }

  if (!value) return fallbackHero.meta.service_days;

  const text = String(value).trim();

  if (text.includes("-")) {
    const [startRaw, endRaw] = text.split("-").map((item) => item.trim());

    const startIndex = dayOrder.findIndex(
      (day) => day.toLowerCase() === startRaw.toLowerCase()
    );
    const endIndex = dayOrder.findIndex(
      (day) => day.toLowerCase() === endRaw.toLowerCase()
    );

    if (startIndex >= 0 && endIndex >= 0 && startIndex <= endIndex) {
      return dayOrder.slice(startIndex, endIndex + 1);
    }
  }

  const parsed = text
    .split(/[,;|]/)
    .map((item) => item.trim())
    .filter((day) => dayOrder.includes(day));

  return parsed.length ? parsed : fallbackHero.meta.service_days;
};

const serviceConfig = computed(() => {
  const meta = hero.value.meta || {};

  const days = parseServiceDays(meta.service_days || meta.service_days_text);
  const startTime = meta.service_start_time || meta.start_time || "08:00";
  const endTime = meta.service_end_time || meta.end_time || "20:00";

  return {
    days,
    dayLabel: meta.service_day_label || "Senin - Jumat",
    startTime,
    endTime,
    startText: formatTime(startTime),
    endText: formatTime(endTime),
  };
});

const serviceHoursText = computed(() => {
  return `${serviceConfig.value.dayLabel}, ${serviceConfig.value.startText} - ${serviceConfig.value.endText} WIB`;
});

const isServiceDay = computed(() => serviceConfig.value.days.includes(currentDay.value));

const isWithinServiceHours = computed(() => {
  const start = timeToMinutes(serviceConfig.value.startTime);
  const end = timeToMinutes(serviceConfig.value.endTime);

  return currentMinutes.value >= start && currentMinutes.value <= end;
});

const isServiceOpen = computed(() => isServiceDay.value && isWithinServiceHours.value);

const statusLabel = computed(() => (isServiceOpen.value ? "Online" : "Offline"));

const nextScheduleText = computed(() => {
  const startText = serviceConfig.value.startText;

  if (isServiceOpen.value) {
    return `Layanan konsultasi sedang dibuka sampai pukul ${serviceConfig.value.endText} WIB.`;
  }

  if (
    isServiceDay.value &&
    currentMinutes.value < timeToMinutes(serviceConfig.value.startTime)
  ) {
    return `Layanan konsultasi akan dibuka hari ini pukul ${startText} WIB.`;
  }

  for (let offset = 1; offset <= 7; offset += 1) {
    const nextDate = new Date(now.value);
    nextDate.setDate(now.value.getDate() + offset);

    const nextDay = dayMap[nextDate.getDay()];

    if (serviceConfig.value.days.includes(nextDay)) {
      return `Layanan konsultasi berikutnya dibuka pada ${nextDay}, pukul ${startText} WIB.`;
    }
  }

  return "Jadwal layanan belum tersedia.";
});

const normalizeRating = (value) => {
  const rating = Number(value || 0);

  if (Number.isNaN(rating)) return 5;

  return Math.min(Math.max(Math.round(rating), 1), 5);
};

const getTestimonialInitial = (name) => {
  const value = String(name || "Pengguna").trim();

  return value ? value.slice(0, 1).toUpperCase() : "P";
};

const getRatingLabel = (rating) => `${normalizeRating(rating)} dari 5 bintang`;

const getTestimonialService = (testimonial) => {
  return (
    testimonial?.service ||
    testimonial?.service_type ||
    testimonial?.serviceType ||
    "Konsultasi HMPS RPL"
  );
};

const getTestimonialDate = (testimonial) => {
  return testimonial?.createdAt || testimonial?.created_at || "Baru saja";
};

const normalizedTestimonials = computed(() => {
  const source = Array.isArray(props.testimonials) ? props.testimonials : [];

  return source
    .filter((item) => item?.is_approved !== false)
    .map((item, index) => ({
      id: item.id ?? `testimonial-${index}`,
      name:
        item.name ||
        item.clientName ||
        item.client_name ||
        item.author ||
        "Pengguna Konsultasi",
      role: item.role || item.position || item.status || "Pengguna Layanan",
      service:
        item.service ||
        item.serviceType ||
        item.service_type ||
        item.category ||
        "Konsultasi HMPS RPL",
      service_type:
        item.service_type ||
        item.serviceType ||
        item.service ||
        item.category ||
        "Konsultasi HMPS RPL",
      rating: normalizeRating(item.rating || item.stars || item.star),
      message:
        item.message ||
        item.comment ||
        item.testimonial ||
        item.content ||
        "Testimoni belum memiliki isi.",
      createdAt:
        item.createdAt || item.created_at || item.date || item.time || "Baru saja",
      created_at:
        item.created_at || item.createdAt || item.date || item.time || "Baru saja",
      emoji: item.emoji || item.icon || "💬",
    }))
    .filter((item) => item.name && item.message);
});

const testimonialServiceOptions = computed(() => {
  const services = new Set(["Konsultasi HMPS RPL"]);

  sortedAdmins.value.forEach((admin) => {
    admin.skills.forEach((skill) => services.add(skill));
  });

  normalizedTestimonials.value.forEach((testimonial) => {
    if (testimonial.service) services.add(testimonial.service);
  });

  return [...services].filter(Boolean);
});

const ratingFilterOptions = computed(() => {
  const total = normalizedTestimonials.value.length;

  const options = [
    {
      value: "all",
      label: "Semua",
      count: total,
    },
  ];

  [5, 4, 3, 2, 1].forEach((rating) => {
    options.push({
      value: rating,
      label: `${rating} Bintang`,
      count: normalizedTestimonials.value.filter((item) => item.rating === rating).length,
    });
  });

  return options;
});

const filteredTestimonials = computed(() => {
  if (selectedRatingFilter.value === "all") {
    return normalizedTestimonials.value;
  }

  return normalizedTestimonials.value.filter(
    (item) => item.rating === Number(selectedRatingFilter.value)
  );
});

const isTestimonialsLoading = computed(() => !Array.isArray(props.testimonials));
const hasTestimonials = computed(() => normalizedTestimonials.value.length > 0);
const hasFilteredTestimonials = computed(() => filteredTestimonials.value.length > 0);

const averageRating = computed(() => {
  if (!normalizedTestimonials.value.length) return "0.0";

  const total = normalizedTestimonials.value.reduce((sum, item) => sum + item.rating, 0);

  return (total / normalizedTestimonials.value.length).toFixed(1);
});

const highestRatingCount = computed(() => {
  return normalizedTestimonials.value.filter((item) => item.rating === 5).length;
});

const selectAdmin = (admin) => {
  selectedAdminId.value = admin?.id ?? null;
};

const buildWhatsappMessage = (admin) => {
  const template = hero.value?.meta?.whatsapp_template || defaultWhatsappTemplate;

  const replacements = {
    "{admin_name}": admin?.name || "Admin HMPS RPL",
    "{admin_skills}": admin?.skills?.join(", ") || "Konsultasi Umum",
    "{admin_role}": admin?.role || "Admin Konsultasi",
    "{service_days}": serviceConfig.value.dayLabel || "Senin - Jumat",
    "{service_time}": `${serviceConfig.value.startText} - ${serviceConfig.value.endText} WIB`,
    "{current_day}": currentDay.value,
    "{current_time}": `${currentTimeDisplay.value} WIB`,
  };

  return Object.entries(replacements).reduce((message, [key, value]) => {
    return message.replaceAll(key, value);
  }, template);
};

const openWhatsApp = (admin = selectedAdmin.value) => {
  if (!admin) return;

  if (!isServiceOpen.value) {
    scrollToSection("jadwal-layanan");
    return;
  }

  const phone = normalizePhoneNumber(admin.phoneWa || admin.phoneDisplay);

  if (!phone) return;

  const message = buildWhatsappMessage(admin);
  const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

  window.open(url, "_blank", "noopener,noreferrer");
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

const scrollTestimonials = (direction = "right") => {
  const container = testimonialsTrack.value;

  if (!container) return;

  const normalizedDirection = direction === "left" || direction === -1 ? -1 : 1;
  const scrollAmount = Math.min(container.clientWidth * 0.86, 620);

  container.scrollBy({
    left: normalizedDirection * scrollAmount,
    behavior: "smooth",
  });
};

const resetTestimonialFilter = () => {
  selectedRatingFilter.value = "all";
};

const openTestimonialDetail = (testimonial) => {
  selectedTestimonial.value = testimonial;
  document.body.style.overflow = "hidden";
};

const closeTestimonialDetail = () => {
  selectedTestimonial.value = null;

  if (!testimonialModalOpen.value) {
    document.body.style.overflow = "";
  }
};

const openTestimonialModal = () => {
  testimonialModalOpen.value = true;
  document.body.style.overflow = "hidden";
};

const closeTestimonialModal = () => {
  testimonialModalOpen.value = false;

  if (!selectedTestimonial.value) {
    document.body.style.overflow = "";
  }
};

const submitTestimonial = () => {
  testimonialForm.post("/konsultasi/testimoni", {
    preserveScroll: true,
    onSuccess: () => {
      testimonialForm.reset();
      testimonialForm.role = "";
      testimonialForm.service_type = "Konsultasi HMPS RPL";
      testimonialForm.rating = 5;
      testimonialForm.emoji = "💬";
      closeTestimonialModal();
    },
  });
};

const handleHeroAction = (url, fallbackId = "pilih-admin") => {
  if (!url) {
    scrollToSection(fallbackId);
    return;
  }

  if (url === "whatsapp-active" || url === "whatsapp-selected") {
    if (selectedAdmin.value && isServiceOpen.value) {
      openWhatsApp(selectedAdmin.value);
      return;
    }

    scrollToSection(isServiceOpen.value ? "pilih-admin" : "jadwal-layanan");
    return;
  }

  if (url.startsWith("#")) {
    scrollToSection(url.replace("#", ""));
    return;
  }

  window.location.href = url;
};

const handleEscape = (event) => {
  if (event.key !== "Escape") return;

  if (selectedTestimonial.value) {
    closeTestimonialDetail();
    return;
  }

  if (testimonialModalOpen.value) {
    closeTestimonialModal();
  }
};

const prefersReducedMotion = () =>
  typeof window !== "undefined" &&
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const isMobileViewport = () =>
  typeof window !== "undefined" && window.matchMedia("(max-width: 767px)").matches;

const aosAnimation = (desktopAnimation = "fade-up", mobileAnimation = "fade-up") => {
  if (prefersReducedMotion()) return "fade-up";

  return isMobileViewport() ? mobileAnimation : desktopAnimation;
};

const aosDuration = (desktopDuration = 760, mobileDuration = 560) => {
  if (prefersReducedMotion()) return 0;

  return isMobileViewport() ? mobileDuration : desktopDuration;
};

const aosDelay = (index = 0, step = 80, maxDelay = 260) => {
  if (prefersReducedMotion()) return 0;

  const safeIndex = Math.max(Number(index) || 0, 0);
  const safeStep = isMobileViewport() ? Math.min(step, 55) : step;
  const safeMax = isMobileViewport() ? Math.min(maxDelay, 160) : maxDelay;

  return Math.min(safeIndex * safeStep, safeMax);
};

const aosOffset = (desktopOffset = 78, mobileOffset = 46) =>
  isMobileViewport() ? mobileOffset : desktopOffset;

let aosRefreshFrame = null;
let aosResizeTimer = null;

const refreshAosSafely = async () => {
  await nextTick();

  if (typeof window === "undefined") return;

  if (aosRefreshFrame) {
    window.cancelAnimationFrame(aosRefreshFrame);
  }

  aosRefreshFrame = window.requestAnimationFrame(() => {
    AOS.refreshHard?.();
    AOS.refresh();
    aosRefreshFrame = null;
  });
};

const handleAosResize = () => {
  if (typeof window === "undefined") return;

  if (aosResizeTimer) {
    window.clearTimeout(aosResizeTimer);
  }

  aosResizeTimer = window.setTimeout(() => {
    refreshAosSafely();
    aosResizeTimer = null;
  }, 160);
};

onMounted(() => {
  AOS.init({
    duration: aosDuration(720, 540),
    easing: "ease-out-cubic",
    once: true,
    offset: aosOffset(82, 48),
    delay: 0,
    mirror: false,
    anchorPlacement: "top-bottom",
    disable: () => prefersReducedMotion(),
  });

  if (typeof window !== "undefined") {
    window.AOS = AOS;
  }

  if (!selectedAdminId.value && sortedAdmins.value.length) {
    selectedAdminId.value = sortedAdmins.value[0].id;
  }

  timer = setInterval(() => {
    now.value = getJakartaDate();
  }, 60000);

  window.addEventListener("keydown", handleEscape, { passive: true });
  window.addEventListener("resize", handleAosResize, { passive: true });

  refreshAosSafely();
});

watch(
  [
    filteredTestimonials,
    selectedRatingFilter,
    testimonialModalOpen,
    selectedTestimonial,
    sortedAdmins,
    steps,
  ],
  refreshAosSafely
);

onUnmounted(() => {
  if (timer) clearInterval(timer);

  if (aosRefreshFrame) {
    window.cancelAnimationFrame(aosRefreshFrame);
  }

  if (aosResizeTimer) {
    window.clearTimeout(aosResizeTimer);
  }

  window.removeEventListener("keydown", handleEscape);
  window.removeEventListener("resize", handleAosResize);
  document.body.style.overflow = "";
});
</script>

<template>
  <Head>
    <title>Konsultasi</title>
    <meta
      name="description"
      content="Halaman konsultasi HMPS RPL untuk memilih admin berdasarkan skill, melihat jadwal layanan, dan menghubungi admin melalui WhatsApp secara cepat dan terarah."
    />
    <meta
      name="keywords"
      content="Konsultasi HMPS RPL, konsultasi website, konsultasi desain, konsultasi software, admin HMPS RPL"
    />
    <meta property="og:title" content="Konsultasi HMPS RPL" />
    <meta
      property="og:description"
      content="Pilih admin konsultasi HMPS RPL sesuai kebutuhan dan lanjutkan komunikasi melalui WhatsApp pada jam layanan yang tersedia."
    />
    <meta property="og:type" content="website" />
    <meta name="robots" content="index, follow" />
  </Head>

  <div
    class="konsultasi-page overflow-x-hidden bg-white font-['Plus_Jakarta_Sans',sans-serif] text-slate-950 antialiased"
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
          class="grid grid-cols-1 items-center gap-10 lg:grid-cols-[1fr_0.92fr] xl:gap-16"
        >
          <div
            class="hero-lcp-safe"
            :data-aos="aosAnimation('fade-right')"
            :data-aos-duration="aosDuration(760, 560)"
            :data-aos-offset="aosOffset(70, 36)"
          >
            <div class="mb-5 flex flex-wrap items-center gap-3">
              <div
                class="inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/80 px-3 py-2 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 shadow-[0_12px_35px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:text-[0.75rem]"
              >
                <span
                  class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
                />
                <span class="truncate">{{ hero.badge }}</span>
              </div>

              <div
                :class="
                  isServiceOpen
                    ? 'border-emerald-400/30 bg-emerald-50 text-emerald-700'
                    : 'border-slate-200 bg-white/80 text-slate-500'
                "
                class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] shadow-sm backdrop-blur-xl"
              >
                <span
                  :class="
                    isServiceOpen
                      ? 'bg-emerald-500 shadow-[0_0_0_5px_rgba(16,185,129,0.16)]'
                      : 'bg-slate-400'
                  "
                  class="h-1.5 w-1.5 rounded-full"
                />
                {{ isServiceOpen ? "Online Sekarang" : "Offline" }}
              </div>
            </div>

            <h1
              class="max-w-3xl text-[2.35rem] font-black leading-[1.05] tracking-[-0.05em] text-slate-950 sm:text-[3.2rem] md:text-[3.8rem] xl:text-[4.8rem]"
            >
              {{ hero.title }}
              <span
                class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ hero.titleHighlight }}
              </span>
            </h1>

            <p
              class="mt-6 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-600 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              {{ hero.description }}
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
              <!-- Tombol Pilih Admin -->
              <button
                type="button"
                @click="handleHeroAction(hero.primaryButtonUrl, 'pilih-admin')"
                class="group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-6 text-sm font-extrabold text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.26)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] sm:min-h-[54px] sm:w-auto sm:text-[0.95rem]"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />

                <span
                  class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/15 transition duration-300 group-hover:bg-white/15"
                  aria-hidden="true"
                >
                  <svg
                    class="h-4.5 w-4.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M4.5 20.25a7.5 7.5 0 0 1 15 0"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M18.75 8.25v4.5m2.25-2.25h-4.5"
                    />
                  </svg>
                </span>

                <span class="relative z-10">
                  {{ hero.primaryButtonLabel || "Pilih Admin Konsultasi" }}
                </span>
              </button>

              <!-- Tombol Jadwal Konsultasi / Secondary -->
              <button
                type="button"
                @click="handleHeroAction(hero.secondaryButtonUrl, 'pilih-admin')"
                :class="
                  isServiceOpen && selectedAdmin
                    ? 'border-slate-950 bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.26)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]'
                    : 'border-slate-200 bg-white/[0.85] text-slate-600 shadow-[0_10px_24px_rgba(2,6,23,0.05)] hover:border-red-200 hover:text-red-700'
                "
                class="group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-2xl border px-6 text-sm font-extrabold transition-all duration-300 hover:-translate-y-[2px] sm:min-h-[54px] sm:w-auto sm:text-[0.95rem]"
              >
                <span
                  class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 ring-1 ring-slate-200 transition duration-300 group-hover:bg-red-50 group-hover:text-red-700 group-hover:ring-red-100"
                  :class="
                    isServiceOpen && selectedAdmin
                      ? 'bg-white/10 text-white ring-white/15 group-hover:bg-white/15 group-hover:text-white group-hover:ring-white/20'
                      : ''
                  "
                  aria-hidden="true"
                >
                  <svg
                    class="h-4.5 w-4.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M7 3v3M17 3v3M4.75 8.5h14.5"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M5.75 5h12.5A2.75 2.75 0 0 1 21 7.75v10.5A2.75 2.75 0 0 1 18.25 21H5.75A2.75 2.75 0 0 1 3 18.25V7.75A2.75 2.75 0 0 1 5.75 5Z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M12 12v3l2 1.25"
                    />
                  </svg>
                </span>

                <span>
                  {{ hero.secondaryButtonLabel || "Jadwal Konsultasi" }}
                </span>
              </button>
            </div>

            <div class="mt-8 grid grid-cols-3 gap-2.5 sm:max-w-xl sm:gap-3">
              <div
                class="rounded-2xl border border-slate-900/5 bg-white/75 p-3 shadow-[0_12px_32px_rgba(2,6,23,0.05)] backdrop-blur-xl sm:p-4"
              >
                <div
                  class="text-lg font-black tracking-[-0.04em] text-slate-950 sm:text-2xl"
                >
                  {{ sortedAdmins.length }}
                </div>
                <div
                  class="mt-1 text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-red-700 sm:text-[0.72rem]"
                >
                  Admin
                </div>
              </div>

              <div
                class="rounded-2xl border border-slate-900/5 bg-white/75 p-3 shadow-[0_12px_32px_rgba(2,6,23,0.05)] backdrop-blur-xl sm:p-4"
              >
                <div
                  class="text-[0.9rem] font-black tracking-[-0.04em] sm:text-xl"
                  :class="isServiceOpen ? 'text-emerald-700' : 'text-slate-950'"
                >
                  {{ statusLabel }}
                </div>
                <div
                  class="mt-1 text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-red-700 sm:text-[0.72rem]"
                >
                  Status
                </div>
              </div>

              <div
                class="rounded-2xl border border-slate-900/5 bg-white/75 p-3 shadow-[0_12px_32px_rgba(2,6,23,0.05)] backdrop-blur-xl sm:p-4"
              >
                <div
                  class="text-[0.72rem] font-black leading-tight tracking-[-0.02em] text-slate-950 sm:text-[0.82rem]"
                >
                  {{ serviceConfig.dayLabel }}
                </div>
                <div
                  class="mt-1 text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-red-700 sm:text-[0.72rem]"
                >
                  {{ serviceConfig.startText }} - {{ serviceConfig.endText }}
                </div>
              </div>
            </div>
          </div>

          <div
            class="hero-lcp-safe"
            :data-aos="aosAnimation('fade-left')"
            :data-aos-delay="aosDelay(1, 90, 160)"
            :data-aos-duration="aosDuration(780, 560)"
            :data-aos-offset="aosOffset(70, 36)"
          >
            <div
              class="relative overflow-hidden rounded-[1.9rem] border border-slate-800 bg-[linear-gradient(155deg,#0f172a,#111827_55%,#1e293b)] text-white shadow-[0_34px_100px_rgba(2,6,23,0.34)] ring-1 ring-slate-700/60"
            >
              <div class="border-b border-white/10 bg-slate-950/70 px-5 py-4 sm:px-6">
                <div class="flex items-center justify-between gap-4">
                  <div
                    class="inline-flex items-center gap-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-white/90"
                  >
                    <span
                      :class="
                        isServiceOpen
                          ? 'bg-emerald-500 shadow-[0_0_0_5px_rgba(16,185,129,0.14)]'
                          : 'bg-slate-400'
                      "
                      class="h-2 w-2 rounded-full transition-colors duration-500"
                    />
                    {{ hero.meta.status_card_title || "Status Layanan" }}
                  </div>

                  <div class="font-mono text-sm font-black tabular-nums text-red-200">
                    {{ currentTimeDisplay }} WIB
                  </div>
                </div>
              </div>

              <div class="p-5 sm:p-6">
                <div
                  class="overflow-hidden rounded-[1.45rem] bg-[linear-gradient(155deg,#0f172a,#1a1035_55%,#111827)] text-white"
                >
                  <div
                    class="h-1.5 w-full bg-[linear-gradient(90deg,#ef4444,#dc2626,#991b1b)]"
                  />

                  <div class="p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                      <div>
                        <p
                          class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-300"
                        >
                          Admin Terpilih
                        </p>

                        <h3
                          class="mt-1.5 text-xl font-black tracking-[-0.03em] text-white sm:text-2xl"
                        >
                          {{ selectedAdmin?.name || "Belum ada admin" }}
                        </h3>

                        <p class="mt-1 text-[0.82rem] font-medium text-slate-400">
                          {{ selectedAdmin?.role || "Pilih admin untuk konsultasi" }}
                        </p>
                      </div>

                      <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.1rem] bg-white/[0.08] text-2xl ring-1 ring-white/10"
                      >
                        {{ selectedAdmin?.emoji || "👤" }}
                      </div>
                    </div>

                    <div
                      class="mt-5 rounded-xl bg-white/[0.08] px-3.5 py-3 ring-1 ring-white/[0.06]"
                    >
                      <p
                        class="text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-slate-400"
                      >
                        Skill / Bidang Konsultasi
                      </p>

                      <div class="mt-2 flex flex-wrap gap-2">
                        <span
                          v-for="skill in selectedAdmin?.skills || ['Konsultasi Umum']"
                          :key="skill"
                          class="rounded-full border border-red-300/20 bg-red-500/10 px-3 py-1 text-[0.72rem] font-black text-red-100"
                        >
                          {{ skill }}
                        </span>
                      </div>
                    </div>

                    <div
                      class="mt-4 rounded-xl bg-white/[0.08] px-3.5 py-3 ring-1 ring-white/[0.06]"
                    >
                      <p
                        class="text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-slate-400"
                      >
                        Jadwal Layanan
                      </p>

                      <p class="mt-1 text-sm font-black text-white">
                        {{ serviceHoursText }}
                      </p>
                    </div>

                    <button
                      type="button"
                      :disabled="!selectedAdmin || !isServiceOpen"
                      @click="openWhatsApp(selectedAdmin)"
                      class="group relative mt-5 inline-flex min-h-[50px] w-full items-center justify-center gap-2.5 overflow-hidden rounded-2xl px-5 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(220,38,38,0.22)] transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-60"
                      :class="
                        isServiceOpen
                          ? 'bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:-translate-y-[2px] hover:shadow-[0_18px_38px_rgba(220,38,38,0.30)]'
                          : 'bg-slate-600'
                      "
                    >
                      <span class="relative z-10">
                        {{
                          isServiceOpen
                            ? `Konsultasi dengan ${selectedAdmin?.name || "Admin"}`
                            : "Layanan Sedang Offline"
                        }}
                      </span>
                    </button>
                  </div>
                </div>

                <div
                  class="mt-4 flex items-start gap-3 rounded-[1.1rem] border border-slate-200 bg-white px-4 py-3.5 shadow-[0_14px_34px_rgba(2,6,23,0.10)]"
                >
                  <p class="text-justify text-[0.8rem] leading-[1.7] text-slate-600">
                    {{
                      isServiceOpen
                        ? "Layanan sedang online. Pilih admin berdasarkan skill yang paling sesuai dengan kebutuhan konsultasi Anda."
                        : nextScheduleText
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Ticker -->
    <div
      class="overflow-hidden border-y border-white/5 bg-[linear-gradient(135deg,#0f172a,#172033_60%,#111827)] py-3.5 sm:py-4"
    >
      <div class="ticker-wrap w-full overflow-hidden">
        <div class="ticker-inner flex w-max" aria-hidden="true">
          <div v-for="dup in 2" :key="dup" class="flex items-center">
            <span
              v-for="item in ticks"
              :key="`${dup}-${item}`"
              class="mx-5 inline-flex whitespace-nowrap text-[0.75rem] font-extrabold uppercase tracking-[0.16em] text-white sm:mx-8 sm:text-[0.82rem] sm:tracking-[0.20em]"
            >
              {{ item }}
              <span class="ml-4 text-red-400/70 sm:ml-6">✦</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Steps -->
    <section
      class="cv-auto bg-[linear-gradient(180deg,#ffffff_0%,#fafafa_100%)] py-16 sm:py-20 lg:py-28"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mx-auto mb-12 max-w-3xl text-center sm:mb-16"
          :data-aos="aosAnimation('fade-up')"
          :data-aos-duration="aosDuration(800, 560)"
          :data-aos-offset="aosOffset(76, 44)"
        >
          <div
            class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3.5 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Alur Konsultasi
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem]"
          >
            Cara memilih
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              admin konsultasi
            </span>
          </h2>

          <p
            class="mx-auto mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem]"
          >
            Pilih admin berdasarkan skill, cek status layanan, lalu lanjutkan konsultasi
            melalui WhatsApp saat jam operasional sedang dibuka.
          </p>
        </div>

        <div class="relative grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-6">
          <div
            class="pointer-events-none absolute left-0 right-0 top-[3.25rem] z-0 hidden lg:block"
          >
            <div
              class="h-px bg-gradient-to-r from-transparent via-red-200 to-transparent"
            />
          </div>

          <article
            v-for="(step, index) in steps"
            :key="step.id || step.number || index"
            class="group relative z-10 overflow-hidden rounded-[1.6rem] border border-slate-100 bg-white p-6 shadow-[0_10px_32px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-1.5 hover:border-red-500/15 hover:shadow-[0_24px_56px_rgba(2,6,23,0.11)] sm:p-7"
            :data-aos="aosAnimation('fade-up')"
            :data-aos-delay="aosDelay(index, 90, 240)"
            :data-aos-duration="aosDuration(800, 540)"
            :data-aos-offset="aosOffset(72, 42)"
          >
            <div
              class="absolute inset-x-0 top-0 h-0.5 origin-left scale-x-0 bg-[linear-gradient(90deg,#ef4444,#dc2626)] transition-transform duration-500 group-hover:scale-x-100"
            />

            <div class="flex items-center gap-3">
              <div
                class="flex h-[3.25rem] w-[3.25rem] items-center justify-center rounded-[1rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-base font-black text-white shadow-[0_10px_22px_rgba(220,38,38,0.20)]"
                style="height: 3.25rem; width: 3.25rem"
              >
                {{ step.number }}
              </div>

              <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-xl text-red-600 transition-colors duration-300 group-hover:bg-red-100"
              >
                {{ step.icon }}
              </div>
            </div>

            <h3
              class="mt-5 text-[1.05rem] font-black leading-snug tracking-[-0.025em] text-slate-950 sm:text-[1.1rem]"
            >
              {{ step.title }}
            </h3>

            <p
              class="mt-3 text-justify text-[0.87rem] leading-[1.8] text-slate-500 sm:text-sm"
            >
              {{ step.description }}
            </p>
          </article>
        </div>
      </div>
    </section>

    <!-- Pilih Admin -->
    <section
      id="pilih-admin"
      class="cv-auto relative scroll-mt-24 overflow-hidden bg-[radial-gradient(circle_at_14%_18%,rgba(239,68,68,0.07),transparent_26%),radial-gradient(circle_at_86%_10%,rgba(15,23,42,0.06),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fbfbfc_100%)] py-16 sm:py-20 lg:py-28"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.06)_1px,transparent_1px)] bg-[size:42px_42px] [mask-image:linear-gradient(180deg,rgba(255,255,255,0.72),transparent_90%)]"
      />
      <div
        class="pointer-events-none absolute -left-28 top-16 h-72 w-72 rounded-full bg-red-500/10 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute -right-28 bottom-10 h-72 w-72 rounded-full bg-slate-950/10 blur-[96px]"
      />

      <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mx-auto mb-12 max-w-3xl text-center sm:mb-14"
          :data-aos="aosAnimation('fade-up')"
          :data-aos-duration="aosDuration(800, 560)"
          :data-aos-offset="aosOffset(76, 44)"
        >
          <div
            class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-white/[0.88] px-4 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700 shadow-[0_12px_34px_rgba(239,68,68,0.10)] backdrop-blur-xl"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            {{ hero.meta.admin_section_badge || "Pilih Admin" }}
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem]"
          >
            {{ hero.meta.admin_section_title || "Pilih admin berdasarkan skill" }}
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              {{ hero.meta.admin_section_highlight || "yang kamu butuhkan" }}
            </span>
          </h2>

          <p
            class="mx-auto mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem]"
          >
            {{
              hero.meta.admin_section_description ||
              "Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda."
            }}
          </p>
        </div>

        <div
          v-if="sortedAdmins.length"
          class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
          <article
            v-for="(admin, index) in sortedAdmins"
            :key="admin.id"
            role="button"
            tabindex="0"
            :aria-pressed="String(selectedAdmin?.id) === String(admin.id)"
            class="group relative flex h-full min-h-[25rem] cursor-pointer flex-col overflow-hidden rounded-[2rem] border bg-white outline-none transition-all duration-300 hover:-translate-y-1.5 focus:ring-4 focus:ring-red-100"
            :data-aos="aosAnimation('zoom-in-up')"
            :data-aos-delay="aosDelay(index, 70, 260)"
            :data-aos-duration="aosDuration(760, 540)"
            :data-aos-offset="aosOffset(76, 42)"
            :class="
              String(selectedAdmin?.id) === String(admin.id)
                ? 'border-red-200 shadow-[0_28px_80px_rgba(239,68,68,0.16)] ring-4 ring-red-50'
                : 'border-slate-200/80 shadow-[0_18px_54px_rgba(15,23,42,0.07)] hover:border-red-100 hover:shadow-[0_30px_80px_rgba(15,23,42,0.12)]'
            "
            @click="selectAdmin(admin)"
            @keydown.enter.prevent="selectAdmin(admin)"
            @keydown.space.prevent="selectAdmin(admin)"
          >
            <!-- Top Gradient Accent -->
            <div
              class="absolute inset-x-0 top-0 h-1.5"
              :class="
                String(selectedAdmin?.id) === String(admin.id)
                  ? 'bg-[linear-gradient(90deg,#ef4444,#dc2626,#991b1b)]'
                  : 'bg-[linear-gradient(90deg,#e2e8f0,#f1f5f9)] group-hover:bg-[linear-gradient(90deg,#fecaca,#ef4444)]'
              "
            />

            <!-- Soft Glow -->
            <div
              class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full blur-3xl transition duration-300"
              :class="
                String(selectedAdmin?.id) === String(admin.id)
                  ? 'bg-red-500/15'
                  : 'bg-slate-950/[0.035] group-hover:bg-red-500/[0.08]'
              "
            />

            <div class="relative z-10 flex h-full flex-col p-5 sm:p-6">
              <!-- Header Card -->
              <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-4">
                  <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.25rem] border text-xl shadow-sm transition duration-300"
                    :class="
                      String(selectedAdmin?.id) === String(admin.id)
                        ? 'border-red-100 bg-red-50 text-red-600'
                        : 'border-slate-200 bg-slate-50 text-slate-500 group-hover:border-red-100 group-hover:bg-red-50 group-hover:text-red-600'
                    "
                  >
                    <span class="leading-none">
                      {{ admin.emoji || "👤" }}
                    </span>
                  </div>

                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <span
                        class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-[0.62rem] font-black uppercase tracking-[0.12em] text-red-700 ring-1 ring-red-100"
                      >
                        {{ admin.label || "Admin Konsultasi" }}
                      </span>

                      <span
                        v-if="String(selectedAdmin?.id) === String(admin.id)"
                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-950 px-2.5 py-1 text-[0.62rem] font-black uppercase tracking-[0.1em] text-white shadow-[0_10px_20px_rgba(15,23,42,0.16)]"
                      >
                        <span class="h-1.5 w-1.5 rounded-full bg-red-400" />
                        Dipilih
                      </span>
                    </div>

                    <h3
                      class="mt-3 line-clamp-2 text-xl font-black leading-[1.15] tracking-[-0.035em] text-slate-950"
                    >
                      {{ admin.name }}
                    </h3>

                    <p
                      class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-slate-500"
                    >
                      {{ admin.role }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Skills -->
              <div class="mt-6">
                <div class="flex items-center justify-between gap-3">
                  <p
                    class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-slate-400"
                  >
                    Skill Konsultasi
                  </p>

                  <span
                    class="rounded-full bg-slate-50 px-2.5 py-1 text-[0.62rem] font-black text-slate-500 ring-1 ring-slate-200"
                  >
                    {{ admin.skills?.length || 0 }} Skill
                  </span>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                  <span
                    v-for="skill in admin.skills"
                    :key="`${admin.id}-${skill}`"
                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[0.72rem] font-black text-slate-700 transition duration-300 group-hover:border-red-100 group-hover:bg-red-50 group-hover:text-red-700"
                  >
                    {{ skill }}
                  </span>
                </div>
              </div>

              <!-- Contact Box -->
              <div
                class="mt-6 rounded-[1.35rem] border border-slate-200 bg-[linear-gradient(135deg,#f8fafc,#ffffff)] p-4 transition duration-300 group-hover:border-red-100"
              >
                <div class="flex items-center justify-between gap-4">
                  <div class="min-w-0">
                    <p
                      class="text-[0.66rem] font-black uppercase tracking-[0.14em] text-slate-400"
                    >
                      Kontak WhatsApp
                    </p>

                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                      {{ admin.phoneDisplay || admin.phoneWa || "Nomor belum tersedia" }}
                    </p>
                  </div>

                  <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition duration-300 group-hover:bg-red-50 group-hover:text-red-600 group-hover:ring-red-100"
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
                        d="M3 5.5A2.5 2.5 0 0 1 5.5 3h2.1a1.5 1.5 0 0 1 1.42 1.03l.82 2.47a1.5 1.5 0 0 1-.35 1.53l-1.1 1.1a12.1 12.1 0 0 0 5.48 5.48l1.1-1.1a1.5 1.5 0 0 1 1.53-.35l2.47.82A1.5 1.5 0 0 1 20 16.4v2.1a2.5 2.5 0 0 1-2.5 2.5H17C9.27 21 3 14.73 3 7v-.5Z"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Bottom Action -->
              <div class="mt-auto grid grid-cols-1 gap-3 pt-6 sm:grid-cols-[1fr_auto]">
                <button
                  type="button"
                  @click.stop="
                    selectAdmin(admin);
                    openWhatsApp(admin);
                  "
                  class="group/btn relative inline-flex min-h-[3rem] items-center justify-center overflow-hidden rounded-2xl border border-slate-950 bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-xs font-black text-white shadow-[0_14px_30px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-0.5 hover:border-red-600 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_20px_42px_rgba(220,38,38,0.25)]"
                >
                  <span
                    class="absolute inset-0 -translate-x-[130%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-700 group-hover/btn:translate-x-[130%]"
                  />

                  <span class="relative z-10 flex items-center gap-2">
                    Konsultasikan
                    <svg
                      class="h-4 w-4 transition-transform duration-300 group-hover/btn:translate-x-1"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.4"
                        d="M5 12h14m-6-6 6 6-6 6"
                      />
                    </svg>
                  </span>
                </button>

                <div
                  class="inline-flex min-h-[3rem] items-center justify-center gap-2 rounded-2xl border px-4 text-xs font-black"
                  :class="
                    isServiceOpen
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                      : 'border-slate-200 bg-slate-100 text-slate-500'
                  "
                >
                  <span
                    class="h-2 w-2 rounded-full"
                    :class="isServiceOpen ? 'bg-emerald-500' : 'bg-slate-400'"
                  />
                  {{ isServiceOpen ? "Online" : "Offline" }}
                </div>
              </div>
            </div>
          </article>
        </div>

        <div
          v-else
          class="mx-auto max-w-3xl rounded-[1.65rem] border border-dashed border-slate-200 bg-white/[0.9] p-8 text-center shadow-[0_16px_46px_rgba(15,23,42,0.05)] backdrop-blur-xl"
          :data-aos="aosAnimation('fade-up')"
        >
          <div
            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-2xl shadow-[0_14px_30px_rgba(15,23,42,0.18)]"
          >
            👤
          </div>

          <h3 class="mt-4 text-lg font-black text-slate-950">
            Admin konsultasi belum tersedia
          </h3>

          <p class="mt-2 text-sm leading-7 text-slate-500">
            Data admin dapat ditambahkan melalui menu Custom Konsultasi pada halaman
            admin.
          </p>
        </div>
      </div>
    </section>

    <!-- Jadwal Layanan -->
    <section
      id="jadwal-layanan"
      class="cv-auto scroll-mt-24 bg-[linear-gradient(180deg,#fafafa_0%,#f5f5f5_100%)] py-16 sm:py-20 lg:py-24"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="overflow-hidden rounded-[1.85rem] border border-slate-200/70 bg-white shadow-[0_16px_50px_rgba(2,6,23,0.07)]"
          :data-aos="aosAnimation('fade-up')"
          :data-aos-duration="aosDuration(800, 560)"
          :data-aos-offset="aosOffset(76, 44)"
        >
          <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#991b1b)]" />

          <div class="p-6 sm:p-8 lg:p-10">
            <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
              <div>
                <div
                  class="inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3.5 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
                >
                  <span class="h-2 w-2 rounded-full bg-red-500" />
                  Jadwal Layanan
                </div>

                <h3
                  class="mt-5 text-[1.75rem] font-black leading-[1.12] tracking-[-0.04em] text-slate-950 sm:text-[2.1rem]"
                >
                  Jadwal konsultasi
                  <span
                    class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
                  >
                    berlaku untuk semua admin
                  </span>
                </h3>

                <p
                  class="mt-4 max-w-md text-justify text-[0.9rem] leading-[1.85] text-slate-600"
                >
                  Jadwal tidak lagi diatur per admin. Semua admin mengikuti jam
                  operasional layanan konsultasi yang sama, dan jadwal ini dapat diubah
                  melalui menu Custom Konsultasi.
                </p>
              </div>

              <div class="grid gap-4 sm:grid-cols-2">
                <div
                  class="rounded-[1.4rem] border border-red-100 bg-red-50 p-5 shadow-[0_12px_34px_rgba(220,38,38,0.08)]"
                >
                  <p
                    class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
                  >
                    Hari Layanan
                  </p>

                  <h4 class="mt-2 text-2xl font-black tracking-[-0.04em] text-slate-950">
                    {{ serviceConfig.dayLabel }}
                  </h4>
                </div>

                <div
                  class="rounded-[1.4rem] border border-slate-100 bg-slate-50 p-5 shadow-[0_12px_34px_rgba(2,6,23,0.05)]"
                >
                  <p
                    class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-slate-500"
                  >
                    Jam Layanan
                  </p>

                  <h4 class="mt-2 text-2xl font-black tracking-[-0.04em] text-slate-950">
                    {{ serviceConfig.startText }} - {{ serviceConfig.endText }}
                  </h4>

                  <p class="mt-1 text-sm font-bold text-slate-500">WIB</p>
                </div>

                <div
                  class="rounded-[1.4rem] border p-5 sm:col-span-2"
                  :class="
                    isServiceOpen
                      ? 'border-emerald-100 bg-emerald-50'
                      : 'border-slate-100 bg-white'
                  "
                >
                  <div class="flex items-start gap-3">
                    <span
                      :class="
                        isServiceOpen
                          ? 'bg-emerald-500 shadow-[0_0_0_5px_rgba(16,185,129,0.14)]'
                          : 'bg-slate-400'
                      "
                      class="mt-1 h-3 w-3 rounded-full"
                    />

                    <div>
                      <p
                        class="text-[0.68rem] font-extrabold uppercase tracking-[0.1em]"
                        :class="isServiceOpen ? 'text-emerald-700' : 'text-slate-500'"
                      >
                        Status Saat Ini
                      </p>

                      <h4
                        class="mt-1 text-xl font-black tracking-[-0.03em]"
                        :class="isServiceOpen ? 'text-emerald-800' : 'text-slate-950'"
                      >
                        {{
                          isServiceOpen
                            ? "Layanan sedang online"
                            : "Layanan sedang offline"
                        }}
                      </h4>

                      <p class="mt-2 text-justify text-sm leading-7 text-slate-600">
                        {{ nextScheduleText }}
                      </p>

                      <p class="mt-2 font-mono text-sm font-black text-slate-950">
                        Sekarang: {{ currentDay }}, {{ currentTimeDisplay }} WIB
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Testimoni Konsultasi -->
    <section
      id="testimoni-konsultasi"
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
            :data-aos-duration="aosDuration(700)"
          >
            <span class="relative flex h-2.5 w-2.5">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-30"
              />
              <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-600" />
            </span>
            Testimoni Konsultasi
          </div>

          <h2
            class="text-[2rem] font-black leading-[1.06] tracking-[-0.055em] text-slate-950 sm:text-[2.65rem] md:text-[3.25rem] lg:text-[3.65rem]"
            :data-aos="aosAnimation('fade-up')"
            :data-aos-delay="aosDelay(1, 70, 140)"
            :data-aos-duration="aosDuration(800)"
          >
            Pengalaman pengguna setelah
            <span
              class="relative inline-block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              berkonsultasi
              <span
                class="absolute -bottom-1 left-2 right-2 -z-10 h-3 rounded-full bg-red-200/70 blur-sm"
              />
            </span>
          </h2>

          <p
            class="mx-auto mt-5 max-w-2xl text-center text-[0.95rem] leading-[1.85] text-slate-600 sm:text-[1.02rem]"
            :data-aos="aosAnimation('fade-up')"
            :data-aos-delay="aosDelay(2, 70, 180)"
            :data-aos-duration="aosDuration(800)"
          >
            Testimoni yang tampil di halaman ini adalah testimoni konsultasi yang sudah
            disetujui admin. Anda dapat memfilter berdasarkan rating, menggeser daftar
            testimoni, dan membuka detail ulasan lengkap.
          </p>
        </div>

        <div
          class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/[0.92] p-4 shadow-[0_28px_80px_rgba(15,23,42,0.09)] backdrop-blur-xl sm:p-5 lg:p-6"
          :data-aos="aosAnimation('fade-up')"
          :data-aos-delay="aosDelay(1, 80, 160)"
          :data-aos-duration="aosDuration(850)"
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
                :data-aos-delay="aosDelay(1, 90, 180)"
                :data-aos-duration="aosDuration(800)"
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
                      <h4 class="text-lg font-black tracking-[-0.03em] text-slate-950">
                        Filter rating testimoni
                      </h4>
                      <p
                        class="mt-1 max-w-md text-sm font-semibold leading-7 text-slate-500"
                      >
                        Menampilkan
                        <span class="font-black text-red-600">
                          {{ filteredTestimonials.length }}
                        </span>
                        dari
                        <span class="font-black text-slate-700">
                          {{ normalizedTestimonials.length }}
                        </span>
                        testimoni approved.
                      </p>
                    </div>
                  </div>

                  <div
                    class="testimonial-filter-scroll -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 xl:mx-0 xl:pb-0"
                  >
                    <button
                      v-for="item in ratingFilterOptions"
                      :key="item.value"
                      type="button"
                      @click="selectedRatingFilter = item.value"
                      :class="[
                        'inline-flex min-h-[44px] shrink-0 items-center gap-2 rounded-full border px-5 text-sm font-black transition-all duration-300',
                        selectedRatingFilter === item.value
                          ? 'border-red-500 bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-white shadow-[0_14px_26px_rgba(220,38,38,0.24)]'
                          : 'border-slate-200 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700',
                      ]"
                    >
                      <span>{{ item.label }}</span>
                      <span
                        :class="[
                          'inline-flex min-w-[1.55rem] items-center justify-center rounded-full px-1.5 py-0.5 text-[0.68rem] font-black',
                          selectedRatingFilter === item.value
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
                <div :data-aos="aosAnimation('fade-up')" :data-aos-delay="aosDelay(2, 75, 180)" :data-aos-duration="aosDuration(780)">
                  <h4
                    class="mt-2 text-lg font-black tracking-[-0.03em] text-slate-950 sm:text-[1.35rem]"
                  >
                    Ulasan terbaru dari pengguna layanan konsultasi
                  </h4>
                </div>

                <div
                  class="hidden items-center gap-3 sm:flex"
                  :data-aos="aosAnimation('fade-left')"
                  :data-aos-delay="aosDelay(2, 90, 200)"
                  :data-aos-duration="aosDuration(780)"
                >
                  <button
                    type="button"
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_20px_36px_rgba(220,38,38,0.22)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
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
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_20px_36px_rgba(220,38,38,0.22)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
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
                    ref="testimonialsTrack"
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
                      :data-aos-delay="aosDelay(index, 65, 220)"
                      :data-aos-duration="aosDuration(750, 520)"
                      :data-aos-offset="aosOffset(70, 40)"
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
                            style="height: 3.25rem; width: 3.25rem"
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
                              {{ testimonial.role || getTestimonialService(testimonial) }}
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
                        class="relative z-10 mt-4 line-clamp-6 flex-1 text-justify text-sm leading-8 text-slate-600"
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
                              {{ getTestimonialService(testimonial) }}
                            </p>
                            <p class="mt-1 truncate text-xs font-bold text-slate-500">
                              {{ getTestimonialDate(testimonial) }}
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

                  <div class="mt-2 flex items-center justify-center gap-2 sm:hidden">
                    <button
                      type="button"
                      @click="scrollTestimonials('left')"
                      aria-label="Geser testimoni ke kiri"
                      class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_12px_26px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
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
                      aria-label="Geser testimoni ke kanan"
                      class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] text-white shadow-[0_12px_26px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
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
                    class="mt-6 inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(15,23,42,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-red-700"
                  >
                    Tampilkan Semua
                  </button>
                </div>

                <div
                  v-else
                  class="rounded-[1.85rem] border border-dashed border-slate-300 bg-white/90 px-6 py-12 text-center shadow-[0_18px_52px_rgba(15,23,42,0.06)] backdrop-blur-xl"
                  :data-aos="aosAnimation('fade-up')"
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
                :data-aos-delay="aosDelay(2, 90, 210)"
                :data-aos-duration="aosDuration(800)"
              >
                <button
                  type="button"
                  @click="openTestimonialModal"
                  class="group relative inline-flex min-h-[56px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-7 text-sm font-black text-white shadow-[0_18px_42px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_54px_rgba(220,38,38,0.28)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
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
                        selectedTestimonial.role ||
                        getTestimonialService(selectedTestimonial)
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
                          Jenis Konsultasi
                        </p>
                        <p class="mt-2 text-sm font-bold leading-7 text-white">
                          {{ getTestimonialService(selectedTestimonial) }}
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
                          {{ getTestimonialDate(selectedTestimonial) }}
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
                      Pengalaman Konsultasi
                    </p>

                    <h4
                      class="mt-2 text-2xl font-black tracking-[-0.03em] text-slate-950 sm:text-3xl"
                    >
                      Ulasan lengkap dari pengguna
                    </h4>

                    <p class="mt-3 text-justify text-sm leading-7 text-slate-500">
                      Detail berikut merupakan testimoni konsultasi yang sudah disetujui
                      admin dan ditampilkan secara publik pada halaman konsultasi.
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
                          Pesan asli dari pengguna
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
                        Pengguna
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
                        Status / Role
                      </p>
                      <p class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ selectedTestimonial.role || "Pengguna Layanan" }}
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
                      class="inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-extrabold text-white transition hover:-translate-y-[2px] hover:bg-red-700"
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
                      testimoni akan tampil di halaman konsultasi.
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
                              Masukkan nama, status, jenis konsultasi, rating, dan
                              pengalaman Anda.
                            </template>

                            <template v-else-if="index === 1">
                              Admin melakukan approve dari menu Custom Konsultasi.
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
                      Testimoni Konsultasi
                    </p>

                    <h4
                      class="mt-2 text-2xl font-black tracking-[-0.03em] text-slate-950"
                    >
                      Form testimoni konsultasi
                    </h4>

                    <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                      Pilihan jenis konsultasi mengikuti data skill admin dan testimoni
                      yang tersedia.
                    </p>
                  </div>

                  <div
                    v-if="flashSuccess || testimonialForm.recentlySuccessful"
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-700"
                  >
                    {{ flashSuccess || "Testimoni berhasil dikirim." }}
                  </div>

                  <div
                    v-if="flashError"
                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm font-semibold text-red-700"
                  >
                    {{ flashError }}
                  </div>

                  <form class="space-y-6" @submit.prevent="submitTestimonial">
                    <div class="grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">
                          Nama
                          <span class="text-red-600">*</span>
                        </label>

                        <input
                          v-model="testimonialForm.name"
                          type="text"
                          autocomplete="name"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-sm text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                          placeholder="Masukkan nama Anda"
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
                          Status / Role
                        </label>

                        <input
                          v-model="testimonialForm.role"
                          type="text"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-sm text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                          placeholder="Mahasiswa / Client / Pengguna Layanan"
                        />

                        <p
                          v-if="testimonialForm.errors.role"
                          class="mt-2 text-sm text-red-600"
                        >
                          {{ testimonialForm.errors.role }}
                        </p>
                      </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-[1fr_auto]">
                      <div>
                        <label class="mb-2 block text-sm font-bold text-slate-800">
                          Jenis Konsultasi
                          <span class="text-red-600">*</span>
                        </label>

                        <select
                          v-model="testimonialForm.service_type"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                        >
                          <option
                            v-for="service in testimonialServiceOptions"
                            :key="service"
                            :value="service"
                          >
                            {{ service }}
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
                          Emoji
                        </label>

                        <input
                          v-model="testimonialForm.emoji"
                          type="text"
                          maxlength="20"
                          class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-4 text-center text-xl text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:w-28"
                          placeholder="💬"
                        />

                        <p
                          v-if="testimonialForm.errors.emoji"
                          class="mt-2 text-sm text-red-600"
                        >
                          {{ testimonialForm.errors.emoji }}
                        </p>
                      </div>
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-bold text-slate-800">
                        Rating
                        <span class="text-red-600">*</span>
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

                    <div>
                      <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="block text-sm font-bold text-slate-800">
                          Isi Testimoni
                          <span class="text-red-600">*</span>
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
                        placeholder="Ceritakan pengalaman konsultasi Anda secara singkat..."
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
                      class="group relative inline-flex min-h-[56px] w-full items-center justify-center overflow-hidden rounded-[1.3rem] bg-[linear-gradient(135deg,#081120,#0f172a)] px-6 text-[1rem] font-extrabold text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_46px_rgba(220,38,38,0.26)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] disabled:cursor-not-allowed disabled:opacity-70"
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
.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 900px;
}

.hero-lcp-safe {
  opacity: 1;
  transform: none;
}

.konsultasi-page :is(button, a, input, select, textarea) {
  -webkit-tap-highlight-color: transparent;
}

.konsultasi-page :is(button, a, input, select, textarea):focus-visible {
  outline: 3px solid rgba(239, 68, 68, 0.28);
  outline-offset: 3px;
}

.ticker-inner {
  animation: tickerMove 30s linear infinite;
  will-change: transform;
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

.testimonial-track {
  scrollbar-width: thin;
  scrollbar-color: rgba(220, 38, 38, 0.5) rgba(241, 245, 249, 0.9);
}

.testimonial-track::-webkit-scrollbar {
  height: 10px;
}

.testimonial-track::-webkit-scrollbar-track {
  border-radius: 999px;
  background: rgba(241, 245, 249, 0.9);
}

.testimonial-track::-webkit-scrollbar-thumb {
  border-radius: 999px;
  background: rgba(220, 38, 38, 0.5);
}

.testimonial-track::-webkit-scrollbar-thumb:hover {
  background: rgba(185, 28, 28, 0.72);
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

@media (prefers-reduced-motion: reduce) {
  .ticker-inner,
  .animate-pulse {
    animation: none;
  }

  .konsultasi-page *,
  .konsultasi-page *::before,
  .konsultasi-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

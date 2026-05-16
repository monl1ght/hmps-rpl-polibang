<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  programHero: {
    type: Object,
    default: () => ({}),
  },
  programs: {
    type: Array,
    default: () => [],
  },
  categoryInfo: {
    type: Array,
    default: () => [],
  },
  ticks: {
    type: Array,
    default: () => [],
  },
});

const fallbackTicks = [
  "PROGRAM KERJA HMPS RPL",
  "TERSTRUKTUR",
  "KOLABORATIF",
  "PROFESIONAL",
  "AKADEMIK",
  "NON AKADEMIK",
  "BERDAMPAK",
];

const fallbackCategories = [
  {
    key: "Semua",
    title: "Semua Program",
    desc: "Menampilkan seluruh program kerja yang tersedia pada periode kepengurusan.",
  },
];

const fallbackHeroImages = [
  {
    src:
      "https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=600&fit=crop&auto=format",
    alt: "Workshop dan program kerja mahasiswa",
  },
  {
    src:
      "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=900&fit=crop&auto=format",
    alt: "Seminar dan kegiatan mahasiswa",
  },
  {
    src:
      "https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=700&h=900&fit=crop&auto=format",
    alt: "Upgrading dan koordinasi organisasi",
  },
  {
    src:
      "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop&auto=format",
    alt: "Kolaborasi organisasi mahasiswa",
  },
];

const fallbackProgramHero = {
  badge: "Halaman Program Kerja HMPS RPL",
  title: "Program Kerja",
  titleHighlight: "HMPS RPL",
  description:
    "Halaman ini menampilkan seluruh rancangan program kerja HMPS Rekayasa Perangkat Lunak secara rapi, modern, dan mudah dipahami berdasarkan kategori, divisi pelaksana, penanggung jawab, serta estimasi anggaran.",
  primaryButtonLabel: "Lihat Semua Program",
  primaryButtonUrl: "#program-list",
  secondaryButtonLabel: "Konsultasi Gratis",
  secondaryButtonUrl: "/konsultasi",
  images: fallbackHeroImages,
  floatingBadgeTopIcon: "📌",
  floatingBadgeTopTitle: "Program Tersusun",
  floatingBadgeTopSubtitle: "Berdasarkan kategori",
  floatingBadgeBottomIcon: "🚀",
  floatingBadgeBottomTitle: "Fokus Periode",
  floatingBadgeBottomSubtitle: "Kegiatan berkelanjutan",
};

const isEmptyLikeValue = (value) => {
  const text = String(value ?? "").trim();
  return !text || ["null", "undefined", "false", "nan"].includes(text.toLowerCase());
};

const getFirstValue = (...values) => {
  const value = values.find(
    (item) => typeof item === "string" && !isEmptyLikeValue(item)
  );

  return value ? value.trim() : "";
};

const getSafeUrl = (value, fallback = "#") => {
  const url = getFirstValue(value);
  return url || fallback;
};

const isHttpUrl = (url) => /^https?:\/\//i.test(String(url || ""));
const isUnsplashUrl = (url) =>
  isHttpUrl(url) && String(url).includes("images.unsplash.com");

const withQueryParams = (url, params = {}) => {
  const source = String(url || "").trim();
  if (!source) return "";

  try {
    const parsedUrl = new URL(source, "https://local.invalid");

    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") {
        parsedUrl.searchParams.set(key, value);
      }
    });

    return isHttpUrl(source)
      ? parsedUrl.toString()
      : `${parsedUrl.pathname}${parsedUrl.search}${parsedUrl.hash}`;
  } catch (error) {
    return source;
  }
};

const optimizeImageUrl = (url, options = {}) => {
  const source = String(url || "").trim();
  if (!source) return "";

  if (isUnsplashUrl(source)) {
    return withQueryParams(source, {
      auto: "format",
      fit: "crop",
      q: String(options.quality || 70),
      w: String(options.width || 720),
      ...(options.height ? { h: String(options.height) } : {}),
    });
  }

  return source;
};

const resolveImageSource = (item, fallback = "") => {
  if (typeof item === "string") return item.trim() || fallback;

  return getFirstValue(
    item?.src,
    item?.url,
    item?.image,
    item?.image_url,
    item?.imageUrl,
    item?.cover,
    item?.cover_url,
    item?.coverUrl,
    item?.thumbnail,
    item?.thumbnail_url,
    item?.thumb_url,
    fallback
  );
};

const getResponsiveSources = (item) => {
  if (!item || typeof item === "string") return {};

  return {
    small: getFirstValue(
      item.image_sm,
      item.imageSmall,
      item.image_small,
      item.image_small_url,
      item.imageSmallUrl,
      item.small_url,
      item.smallUrl,
      item.cover_sm,
      item.coverSmall,
      item.cover_small_url,
      item.thumbnail,
      item.thumbnail_url,
      item.thumb_url
    ),
    medium: getFirstValue(
      item.image_md,
      item.imageMedium,
      item.image_medium,
      item.image_medium_url,
      item.imageMediumUrl,
      item.medium_url,
      item.mediumUrl,
      item.cover_md,
      item.coverMedium,
      item.cover_medium_url
    ),
    large: getFirstValue(
      item.image_lg,
      item.imageLarge,
      item.image_large,
      item.image_large_url,
      item.imageLargeUrl,
      item.large_url,
      item.largeUrl,
      item.cover_lg,
      item.coverLarge,
      item.cover_large_url
    ),
  };
};

const buildUnsplashSrcset = (url, widths = [360, 480, 720, 960], quality = 70) => {
  const source = String(url || "").trim();
  if (!isUnsplashUrl(source)) return "";

  return widths
    .map((width) => `${optimizeImageUrl(source, { width, quality })} ${width}w`)
    .join(", ");
};

const buildResponsiveSrcset = (item, fallbackSource = "", options = {}) => {
  const widths = options.widths || [360, 480, 720, 960];
  const responsiveSources = getResponsiveSources(item);
  const candidates = [
    [responsiveSources.small, widths[0] || 360],
    [responsiveSources.medium, widths[1] || 720],
    [responsiveSources.large, widths[2] || 960],
  ].filter(([source]) => Boolean(source));

  if (candidates.length) {
    return candidates
      .map(
        ([source, width]) =>
          `${optimizeImageUrl(source, {
            width,
            quality: options.quality || 72,
          })} ${width}w`
      )
      .join(", ");
  }

  return buildUnsplashSrcset(fallbackSource, widths, options.quality || 70);
};

const normalizeResponsiveImage = (item, fallback, options = {}) => {
  const fallbackSource = fallback?.src || "";
  const baseSource = resolveImageSource(item, fallbackSource);
  const responsiveSources = getResponsiveSources(item);
  const src = optimizeImageUrl(
    responsiveSources.medium || responsiveSources.small || baseSource || fallbackSource,
    {
      width: options.width || 720,
      height: options.height,
      quality: options.quality || 72,
    }
  );

  return {
    src,
    srcset: buildResponsiveSrcset(item, baseSource || fallbackSource, options),
    alt:
      typeof item === "string"
        ? fallback.alt
        : getFirstValue(
            item?.alt,
            item?.alt_text,
            item?.caption,
            item?.title,
            fallback.alt
          ),
  };
};

const normalizeKey = (value) =>
  String(value || "")
    .trim()
    .toLowerCase()
    .replace(/\s+/g, "-");

const normalizeTickerItem = (item) => {
  if (typeof item === "string") return item;
  return getFirstValue(item?.title, item?.label, item?.text, item?.name);
};

const normalizeCategory = (item, index = 0) => {
  if (typeof item === "string") {
    return {
      key: item,
      title: item,
      desc: "Program kerja berdasarkan kategori yang tersedia.",
    };
  }

  const title = getFirstValue(
    item?.title,
    item?.name,
    item?.label,
    item?.key,
    `Kategori ${index + 1}`
  );
  const key = getFirstValue(item?.key, item?.slug, item?.name, item?.title, title);

  return {
    ...item,
    key,
    title,
    desc: getFirstValue(
      item?.desc,
      item?.description,
      item?.body,
      "Program kerja berdasarkan kategori yang tersedia."
    ),
  };
};

const normalizeHeroImage = (image, fallback, index = 0) =>
  normalizeResponsiveImage(image, fallback, {
    widths: index % 2 === 0 ? [320, 480, 640, 800] : [360, 520, 720, 900],
    width: index % 2 === 0 ? 520 : 640,
    quality: 70,
  });

const programHero = computed(() => {
  const hero =
    props.programHero && typeof props.programHero === "object" ? props.programHero : {};

  const mergedHero = {
    ...fallbackProgramHero,
    ...hero,
  };

  return {
    ...mergedHero,
    primaryButtonUrl: getSafeUrl(
      mergedHero.primaryButtonUrl || mergedHero.primary_button_url,
      fallbackProgramHero.primaryButtonUrl
    ),
    secondaryButtonUrl: getSafeUrl(
      mergedHero.secondaryButtonUrl || mergedHero.secondary_button_url,
      fallbackProgramHero.secondaryButtonUrl
    ),
  };
});

const heroImages = computed(() => {
  const hero = programHero.value;
  const imageItems =
    Array.isArray(hero.images) && hero.images.length
      ? hero.images
      : [
          {
            src: getFirstValue(hero.imageOneUrl, hero.image_one_url),
            alt: getFirstValue(hero.imageOneAlt, hero.image_one_alt),
          },
          {
            src: getFirstValue(hero.imageTwoUrl, hero.image_two_url),
            alt: getFirstValue(hero.imageTwoAlt, hero.image_two_alt),
          },
          {
            src: getFirstValue(hero.imageThreeUrl, hero.image_three_url),
            alt: getFirstValue(hero.imageThreeAlt, hero.image_three_alt),
          },
          {
            src: getFirstValue(hero.imageFourUrl, hero.image_four_url),
            alt: getFirstValue(hero.imageFourAlt, hero.image_four_alt),
          },
        ];

  return [0, 1, 2, 3].map((index) =>
    normalizeHeroImage(imageItems[index], fallbackHeroImages[index], index)
  );
});

const primaryHeroImage = computed(() => heroImages.value[0]?.src || "");
const primaryHeroSrcset = computed(() => heroImages.value[0]?.srcset || "");

const defaultProgramImage =
  "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=720&h=520&fit=crop&auto=format&q=70";

const selectedCategory = ref("Semua");
const selectedProgram = ref(null);
const showHeroVisual = ref(false);
const isMobileViewport = ref(false);
const prefersReducedMotion = ref(false);

let heroVisualMediaQuery = null;
let mobileViewportMediaQuery = null;
let reducedMotionMediaQuery = null;

const ticks = computed(() => {
  const items =
    Array.isArray(props.ticks) && props.ticks.length ? props.ticks : fallbackTicks;
  return items.map(normalizeTickerItem).filter(Boolean);
});

const categoryInfo = computed(() => {
  const categories = Array.isArray(props.categoryInfo) ? props.categoryInfo : [];
  const normalized = (categories.length ? categories : fallbackCategories)
    .map((item, index) => normalizeCategory(item, index))
    .filter((item) => item.key && item.title);

  if (!normalized.some((item) => item.key === "Semua")) {
    normalized.unshift(fallbackCategories[0]);
  }

  return normalized;
});

const prokers = computed(() => (Array.isArray(props.programs) ? props.programs : []));

const formatRupiah = (value) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(Number(value || 0));

const getNumericValue = (value) => {
  if (typeof value === "number") return Number.isFinite(value) ? value : 0;
  const numericValue = Number(String(value || "").replace(/[^0-9.-]/g, ""));
  return Number.isFinite(numericValue) ? numericValue : 0;
};

const getProgramCategory = (program) => {
  const category = program?.category;

  if (category && typeof category === "object") {
    return getFirstValue(category.name, category.title, category.key, "Program");
  }

  return getFirstValue(
    category,
    program?.categoryName,
    program?.category_name,
    program?.categoryTitle,
    program?.category_title,
    "Program"
  );
};

const getProgramTitle = (program) =>
  getFirstValue(
    program?.title,
    program?.name,
    program?.program_name,
    "Program Kerja HMPS RPL"
  );

const getProgramShortDescription = (program) =>
  getFirstValue(
    program?.desc,
    program?.description,
    program?.summary,
    "Deskripsi program kerja belum tersedia."
  );

const getProgramDescription = (program) =>
  getFirstValue(
    program?.description,
    program?.desc,
    program?.summary,
    "Deskripsi program kerja belum tersedia."
  );

const getProgramDate = (program) =>
  getFirstValue(
    program?.date,
    program?.schedule,
    program?.period,
    program?.timeline,
    "Terjadwal"
  );

const getProgramDivision = (program) =>
  getFirstValue(
    program?.division,
    program?.division_name,
    program?.divisionName,
    "HMPS RPL"
  );

const getProgramPerson = (program) =>
  getFirstValue(
    program?.person,
    program?.person_in_charge,
    program?.personInCharge,
    program?.pic,
    program?.responsible,
    "Belum diisi"
  );

const getProgramBudgetValue = (program) =>
  getNumericValue(program?.budget ?? program?.budget_value ?? program?.budgetValue);

const totalBudget = computed(() =>
  prokers.value.reduce((sum, item) => sum + getProgramBudgetValue(item), 0)
);

const stats = computed(() => [
  {
    label: "Total Program",
    value: prokers.value.length || "—",
  },
  {
    label: "Kategori",
    value: categoryInfo.value.filter((item) => item.key !== "Semua").length || "—",
  },
  {
    label: "Total Anggaran",
    value: totalBudget.value ? formatRupiah(totalBudget.value) : "Menyesuaikan",
  },
]);

const filteredProkers = computed(() => {
  if (selectedCategory.value === "Semua") return prokers.value;
  return prokers.value.filter(
    (item) => getProgramCategory(item) === selectedCategory.value
  );
});

const featuredPrograms = computed(() => {
  const featured = prokers.value.filter((item) =>
    Boolean(item.isFeatured || item.is_featured)
  );
  return (featured.length ? featured : prokers.value).slice(0, 3);
});

const tagClassMap = {
  unggulan: "bg-sky-500",
  akademik: "bg-sky-500",
  pendidikan: "bg-sky-500",
  pengabdian: "bg-emerald-500",
  sosial: "bg-emerald-500",
  pendukung: "bg-violet-600",
  organisasi: "bg-violet-600",
  kompetisi: "bg-amber-500",
  "non-akademik": "bg-amber-500",
  teknologi: "bg-indigo-600",
  default: "bg-slate-900/90",
};

const allowedTagClasses = new Set([
  "bg-sky-500",
  "bg-blue-500",
  "bg-blue-600",
  "bg-emerald-500",
  "bg-green-500",
  "bg-violet-600",
  "bg-purple-600",
  "bg-amber-500",
  "bg-orange-500",
  "bg-indigo-600",
  "bg-rose-600",
  "bg-red-600",
  "bg-slate-900/90",
]);

const getProgramRawImage = (program) =>
  getFirstValue(
    program?.img,
    program?.image,
    program?.image_url,
    program?.imageUrl,
    program?.cover,
    program?.cover_url,
    program?.coverUrl,
    program?.src,
    program?.url,
    defaultProgramImage
  );

const getProgramImage = (program) =>
  optimizeImageUrl(getResponsiveSources(program).medium || getProgramRawImage(program), {
    width: 720,
    height: 520,
    quality: 72,
  });

const getProgramImageSrcset = (program) =>
  buildResponsiveSrcset(program, getProgramRawImage(program), {
    widths: [360, 480, 720, 960],
    quality: 72,
  });

const getProgramTagClass = (program) => {
  const rawClass = getFirstValue(program?.tagClass, program?.tag_class);

  if (allowedTagClasses.has(rawClass)) {
    return rawClass;
  }

  return tagClassMap[normalizeKey(getProgramCategory(program))] || tagClassMap.default;
};

const getProgramBudgetText = (program) =>
  getFirstValue(program?.budgetText, program?.budget_text) ||
  (getProgramBudgetValue(program)
    ? formatRupiah(getProgramBudgetValue(program))
    : "Menyesuaikan");

const getSafeList = (value) => {
  if (Array.isArray(value)) return value.filter(Boolean);
  if (typeof value === "string" && value.trim()) return [value.trim()];
  return [];
};

const getProgramGoals = (program) => getSafeList(program?.goals || program?.objectives);
const getProgramBenefits = (program) => getSafeList(program?.benefits || program?.impact);
const getProgramSources = (program) =>
  getSafeList(program?.sources || program?.fundingSources || program?.funding_sources);

const clampNumber = (value, min = 0, max = 300) => {
  const number = Number(value || 0);
  if (!Number.isFinite(number)) return min;
  return Math.min(Math.max(number, min), max);
};

const aosAnimation = (desktopAnimation = "fade-up", mobileAnimation = "fade-up") => {
  if (prefersReducedMotion.value) return "fade-up";
  return isMobileViewport.value ? mobileAnimation : desktopAnimation;
};

const aosDelay = (index = 0, step = 70, maxDelay = 240) => {
  if (prefersReducedMotion.value) return 0;
  const safeStep = isMobileViewport.value ? Math.min(step, 45) : step;
  const safeMax = isMobileViewport.value ? Math.min(maxDelay, 135) : maxDelay;
  return clampNumber(index * safeStep, 0, safeMax);
};

const aosDuration = (desktopDuration = 780, mobileDuration = 620) => {
  if (prefersReducedMotion.value) return 1;
  return isMobileViewport.value ? mobileDuration : desktopDuration;
};

const aosOffset = (desktopOffset = 90, mobileOffset = 44) =>
  isMobileViewport.value ? mobileOffset : desktopOffset;

const refreshAos = () => {
  if (typeof window === "undefined") return;

  nextTick(() => {
    const aos = window.AOS;

    if (aos && typeof aos.refreshHard === "function") {
      aos.refreshHard();
      return;
    }

    if (aos && typeof aos.refresh === "function") {
      aos.refresh();
    }
  });
};

const updateMotionPreferences = () => {
  if (typeof window === "undefined") return;

  mobileViewportMediaQuery =
    mobileViewportMediaQuery || window.matchMedia("(max-width: 767px)");
  reducedMotionMediaQuery =
    reducedMotionMediaQuery || window.matchMedia("(prefers-reduced-motion: reduce)");

  isMobileViewport.value = mobileViewportMediaQuery.matches;
  prefersReducedMotion.value = reducedMotionMediaQuery.matches;
};

const updateHeroVisualVisibility = () => {
  if (typeof window === "undefined") return;

  heroVisualMediaQuery = heroVisualMediaQuery || window.matchMedia("(min-width: 768px)");
  showHeroVisual.value = heroVisualMediaQuery.matches;
};

const handleHeroAction = (url, fallbackSection = "program-list") => {
  const targetUrl = String(url || "").trim();

  if (!targetUrl || targetUrl.startsWith("#")) {
    scrollToSection(targetUrl ? targetUrl.replace("#", "") : fallbackSection);
    return;
  }

  window.location.href = targetUrl;
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

const openDetail = (item) => {
  selectedProgram.value = item;
  document.body.style.overflow = "hidden";
};

const closeDetail = () => {
  selectedProgram.value = null;
  document.body.style.overflow = "";
};

const handleEscape = (event) => {
  if (event.key === "Escape" && selectedProgram.value) {
    closeDetail();
  }
};

watch([filteredProkers, featuredPrograms, categoryInfo, heroImages], refreshAos, {
  flush: "post",
});

watch(selectedCategory, refreshAos, { flush: "post" });
watch([isMobileViewport, prefersReducedMotion], refreshAos, { flush: "post" });

onMounted(() => {
  window.addEventListener("keydown", handleEscape);
  updateMotionPreferences();
  updateHeroVisualVisibility();
  refreshAos();

  if (heroVisualMediaQuery) {
    if (typeof heroVisualMediaQuery.addEventListener === "function") {
      heroVisualMediaQuery.addEventListener("change", updateHeroVisualVisibility);
    } else if (typeof heroVisualMediaQuery.addListener === "function") {
      heroVisualMediaQuery.addListener(updateHeroVisualVisibility);
    }
  }

  if (mobileViewportMediaQuery) {
    if (typeof mobileViewportMediaQuery.addEventListener === "function") {
      mobileViewportMediaQuery.addEventListener("change", updateMotionPreferences);
    } else if (typeof mobileViewportMediaQuery.addListener === "function") {
      mobileViewportMediaQuery.addListener(updateMotionPreferences);
    }
  }

  if (reducedMotionMediaQuery) {
    if (typeof reducedMotionMediaQuery.addEventListener === "function") {
      reducedMotionMediaQuery.addEventListener("change", updateMotionPreferences);
    } else if (typeof reducedMotionMediaQuery.addListener === "function") {
      reducedMotionMediaQuery.addListener(updateMotionPreferences);
    }
  }
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleEscape);

  if (heroVisualMediaQuery) {
    if (typeof heroVisualMediaQuery.removeEventListener === "function") {
      heroVisualMediaQuery.removeEventListener("change", updateHeroVisualVisibility);
    } else if (typeof heroVisualMediaQuery.removeListener === "function") {
      heroVisualMediaQuery.removeListener(updateHeroVisualVisibility);
    }
  }

  if (mobileViewportMediaQuery) {
    if (typeof mobileViewportMediaQuery.removeEventListener === "function") {
      mobileViewportMediaQuery.removeEventListener("change", updateMotionPreferences);
    } else if (typeof mobileViewportMediaQuery.removeListener === "function") {
      mobileViewportMediaQuery.removeListener(updateMotionPreferences);
    }
  }

  if (reducedMotionMediaQuery) {
    if (typeof reducedMotionMediaQuery.removeEventListener === "function") {
      reducedMotionMediaQuery.removeEventListener("change", updateMotionPreferences);
    } else if (typeof reducedMotionMediaQuery.removeListener === "function") {
      reducedMotionMediaQuery.removeListener(updateMotionPreferences);
    }
  }

  document.body.style.overflow = "";
});
</script>

<template>
  <Head title="Program Kerja HMPS RPL">
    <meta
      name="description"
      content="Program Kerja HMPS RPL menampilkan daftar program, kategori kegiatan, penanggung jawab, estimasi anggaran, tujuan, manfaat, dan sumber pendanaan secara rapi dan profesional."
    />
    <meta
      name="keywords"
      content="program kerja HMPS RPL, proker RPL, kegiatan mahasiswa RPL, HMPS Rekayasa Perangkat Lunak"
    />
    <meta property="og:title" content="Program Kerja HMPS RPL" />
    <meta
      property="og:description"
      content="Daftar program kerja HMPS RPL yang tersusun berdasarkan kategori, pelaksana, penanggung jawab, dan estimasi anggaran."
    />
    <meta property="og:type" content="website" />
    <meta name="robots" content="index, follow" />
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin />
    <link
      v-if="primaryHeroImage && primaryHeroSrcset"
      rel="preload"
      as="image"
      :href="primaryHeroImage"
      :imagesrcset="primaryHeroSrcset"
      imagesizes="(min-width: 768px) 260px, 1px"
      media="(min-width: 768px)"
      fetchpriority="high"
    />
    <link
      v-else-if="primaryHeroImage"
      rel="preload"
      as="image"
      :href="primaryHeroImage"
      media="(min-width: 768px)"
      fetchpriority="high"
    />
  </Head>

  <div
    class="program-page overflow-x-hidden bg-white font-['Plus_Jakarta_Sans',sans-serif] text-slate-950 antialiased"
  >
    <!-- Hero -->
    <section
      id="hero"
      class="relative overflow-hidden bg-[radial-gradient(circle_at_12%_18%,rgba(239,68,68,0.12),transparent_28%),radial-gradient(circle_at_88%_20%,rgba(153,27,27,0.10),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fff7f7_52%,#ffffff_100%)] pb-10 pt-20 sm:pb-14 sm:pt-24 lg:flex lg:min-h-[92svh] lg:items-center lg:pt-32"
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
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 xl:gap-14">
          <div
            class="rounded-[1.75rem] border border-white/80 bg-white/68 p-4 shadow-[0_18px_52px_rgba(2,6,23,0.08)] backdrop-blur-xl sm:p-5 lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none lg:backdrop-blur-0"
            :data-aos="aosAnimation('fade-right', 'fade-up')"
            :data-aos-duration="aosDuration(820, 640)"
            :data-aos-offset="aosOffset(80, 34)"
          >
            <div
              class="mb-4 inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/88 px-3 py-2 text-[0.64rem] font-extrabold uppercase tracking-[0.07em] text-red-700 shadow-[0_12px_35px_rgba(2,6,23,0.06)] backdrop-blur-xl min-[390px]:text-[0.68rem] sm:mb-5 sm:text-[0.75rem]"
            >
              <span
                class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
              />
              <span class="min-w-0 leading-snug">{{ programHero.badge }}</span>
            </div>

            <h1
              class="max-w-3xl break-words text-[2.05rem] font-black leading-[1.06] tracking-[-0.045em] text-slate-950 min-[390px]:text-[2.25rem] sm:text-[3.2rem] md:text-[3.8rem] xl:text-[4.8rem]"
            >
              {{ programHero.title }}
              <span
                class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent sm:inline"
              >
                {{ programHero.titleHighlight }}
              </span>
            </h1>

            <p
              class="mt-4 max-w-2xl text-justify text-[0.94rem] leading-[1.78] text-slate-600 sm:mt-6 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              {{ programHero.description }}
            </p>

            <div
              class="mt-6 grid grid-cols-1 gap-3 min-[430px]:grid-cols-2 sm:mt-8 sm:flex sm:flex-row"
            >
              <button
                type="button"
                @click="handleHeroAction(programHero.primaryButtonUrl, 'program-list')"
                class="program-hero-action group relative inline-flex min-h-[52px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl px-5 text-sm font-extrabold text-white shadow-[0_14px_30px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.26)] active:scale-[0.98] sm:min-h-[54px] sm:w-auto sm:px-6 sm:text-[0.95rem]"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />
                <svg
                  class="relative z-10 h-4 w-4 shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.4"
                    d="M12 16V8m0 8-4-4m4 4 4-4"
                  />
                </svg>
                <span class="relative z-10">{{ programHero.primaryButtonLabel }}</span>
              </button>

              <a
                :href="programHero.secondaryButtonUrl || '/konsultasi'"
                class="program-hero-action inline-flex min-h-[52px] w-full touch-manipulation items-center justify-center gap-2 rounded-2xl px-5 text-sm font-extrabold text-white shadow-[0_14px_30px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.26)] active:scale-[0.98] sm:min-h-[54px] sm:w-auto sm:px-6 sm:text-[0.95rem]"
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
                    stroke-width="2.4"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 4v-4z"
                  />
                </svg>
                {{ programHero.secondaryButtonLabel }}
              </a>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-2 sm:mt-8 sm:max-w-2xl sm:gap-3">
              <div
                v-for="(item, index) in stats"
                :key="item.label"
                class="rounded-2xl border border-slate-900/5 bg-white/82 p-2.5 shadow-[0_10px_28px_rgba(2,6,23,0.05)] backdrop-blur-xl sm:p-4"
                data-aos="fade-up"
                :data-aos-delay="aosDelay(index, 55, 150)"
                :data-aos-duration="aosDuration(640, 520)"
                :data-aos-offset="aosOffset(46, 24)"
              >
                <p
                  class="text-[0.58rem] font-extrabold uppercase tracking-[0.06em] text-red-700 min-[390px]:text-[0.62rem] sm:text-[0.72rem]"
                >
                  {{ item.label }}
                </p>
                <h3
                  class="mt-1.5 line-clamp-2 break-words text-[0.82rem] font-black leading-tight tracking-[-0.03em] text-slate-950 min-[390px]:text-[0.95rem] sm:mt-2 sm:text-[1.1rem] lg:text-xl"
                >
                  {{ item.value }}
                </h3>
              </div>
            </div>
          </div>

          <div
            v-if="showHeroVisual"
            class="relative mx-auto hidden w-full max-w-[31rem] sm:max-w-[37rem] md:block lg:mx-auto"
            :data-aos="aosAnimation('fade-left', 'fade-up')"
            :data-aos-delay="aosDelay(1, 95, 140)"
            :data-aos-duration="aosDuration(860, 620)"
            :data-aos-offset="aosOffset(80, 34)"
          >
            <div
              class="absolute -left-4 top-10 hidden h-24 w-24 rounded-full border border-red-500/20 lg:block"
            />
            <div
              class="absolute -right-5 bottom-12 hidden h-32 w-32 rounded-full border border-slate-900/10 lg:block"
            />

            <div
              class="floating-badge hidden w-fit items-center gap-3 rounded-2xl border border-slate-900/5 bg-white/[0.92] px-4 py-3 shadow-[0_18px_44px_rgba(2,6,23,0.12)] backdrop-blur-xl lg:absolute lg:-left-7 lg:top-7 lg:z-20 lg:flex"
            >
              <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-lg text-white"
              >
                {{ programHero.floatingBadgeTopIcon }}
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ programHero.floatingBadgeTopTitle }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ programHero.floatingBadgeTopSubtitle }}
                </div>
              </div>
            </div>

            <div
              class="relative rounded-[1.45rem] border border-slate-900/5 bg-white/80 p-2 shadow-[0_18px_48px_rgba(2,6,23,0.10)] backdrop-blur-xl sm:rounded-[2rem] sm:p-4 lg:shadow-[0_28px_80px_rgba(2,6,23,0.14)]"
            >
              <div
                class="pointer-events-none absolute inset-0 rounded-[1.65rem] [background:linear-gradient(135deg,rgba(239,68,68,0.22),rgba(255,255,255,0.26),rgba(153,27,27,0.16))] [mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:exclude] p-px sm:rounded-[2rem]"
              />

              <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
                <div class="flex flex-col gap-2.5 pt-6 sm:gap-4 sm:pt-10 lg:pt-12">
                  <div
                    class="group h-24 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <img
                      :src="heroImages[0].src"
                      :srcset="heroImages[0].srcset || undefined"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[0].alt"
                      width="640"
                      height="460"
                      loading="eager"
                      fetchpriority="high"
                      decoding="async"
                      class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                  <div
                    class="group h-36 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <img
                      :src="heroImages[1].src"
                      :srcset="heroImages[1].srcset || undefined"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[1].alt"
                      width="640"
                      height="720"
                      loading="lazy"
                      fetchpriority="low"
                      decoding="async"
                      class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                </div>

                <div class="flex flex-col gap-2.5 sm:gap-4">
                  <div
                    class="group h-36 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <img
                      :src="heroImages[2].src"
                      :srcset="heroImages[2].srcset || undefined"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[2].alt"
                      width="640"
                      height="720"
                      loading="lazy"
                      fetchpriority="low"
                      decoding="async"
                      class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                  <div
                    class="group h-24 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <img
                      :src="heroImages[3].src"
                      :srcset="heroImages[3].srcset || undefined"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[3].alt"
                      width="640"
                      height="720"
                      loading="lazy"
                      fetchpriority="low"
                      decoding="async"
                      class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div
              class="floating-badge hidden w-fit items-center gap-3 rounded-2xl border border-slate-900/5 bg-white/[0.92] px-4 py-3 shadow-[0_18px_44px_rgba(2,6,23,0.12)] backdrop-blur-xl lg:absolute lg:-right-7 lg:bottom-8 lg:z-20 lg:flex"
            >
              <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-rose-700 text-lg text-white"
              >
                {{ programHero.floatingBadgeBottomIcon }}
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ programHero.floatingBadgeBottomTitle }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ programHero.floatingBadgeBottomSubtitle }}
                </div>
              </div>
            </div>
          </div>

          <div
            v-else
            class="relative mx-auto w-full max-w-[34rem] rounded-[1.65rem] border border-slate-900/5 bg-white/86 p-4 shadow-[0_18px_48px_rgba(2,6,23,0.08)] backdrop-blur-xl md:hidden"
            data-aos="fade-up"
            :data-aos-delay="aosDelay(1, 70, 90)"
            :data-aos-duration="aosDuration(700, 560)"
            :data-aos-offset="aosOffset(70, 28)"
          >
            <div
              class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-red-500/10 blur-3xl"
              aria-hidden="true"
            />
            <div class="relative grid gap-3">
              <div
                class="rounded-[1.25rem] bg-[linear-gradient(135deg,#0f172a,#111827_55%,#991b1b)] p-4 text-white shadow-[0_14px_34px_rgba(2,6,23,0.16)]"
              >
                <p
                  class="text-[0.66rem] font-extrabold uppercase tracking-[0.12em] text-red-100"
                >
                  Ringkasan Proker
                </p>
                <h2 class="mt-2 text-xl font-black tracking-[-0.03em]">
                  Program tersusun, terukur, dan mudah dipantau.
                </h2>
                <p class="mt-3 text-sm leading-7 text-slate-200">
                  Data program ditampilkan berdasarkan kategori, divisi, penanggung jawab,
                  dan kebutuhan anggaran.
                </p>
              </div>

              <div class="grid grid-cols-3 gap-2">
                <div
                  v-for="item in stats"
                  :key="`mobile-${item.label}`"
                  class="rounded-2xl border border-slate-900/5 bg-slate-50 p-3 text-center"
                >
                  <div class="text-sm font-black text-slate-950">
                    {{ item.value }}
                  </div>
                  <div
                    class="mt-1 text-[0.56rem] font-extrabold uppercase tracking-[0.05em] text-red-700"
                  >
                    {{ item.label }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ticker -->
    <div
      class="overflow-hidden border-y border-white/5 bg-[linear-gradient(135deg,#0f172a,#172033_60%,#111827)] py-3 sm:py-4"
      data-aos="fade-in"
      :data-aos-duration="aosDuration(620, 460)"
      :data-aos-offset="aosOffset(32, 16)"
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

    <!-- Kategori -->
    <section id="kategori" class="cv-auto scroll-mt-24 bg-white py-12 sm:py-20 lg:py-24">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-10 max-w-3xl sm:mb-14 lg:mb-16"
          data-aos="fade-up"
          :data-aos-duration="aosDuration(760, 580)"
          :data-aos-offset="aosOffset(80, 34)"
        >
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Kategori Proker
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            Struktur
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              Program Kerja
            </span>
          </h2>

          <p
            class="mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem]"
          >
            Program kerja dikelompokkan agar lebih mudah dipahami, ditelusuri, dan
            dikaitkan dengan fokus pelaksanaannya.
          </p>
        </div>

        <div
          class="grid grid-cols-1 gap-3 min-[430px]:grid-cols-2 sm:gap-5 sm:grid-cols-2 xl:grid-cols-5"
        >
          <article
            v-for="(item, index) in categoryInfo"
            :key="item.key"
            class="group rounded-[1.25rem] border border-slate-900/5 bg-white p-4 shadow-[0_12px_32px_rgba(2,6,23,0.05)] transition-all duration-300 hover:-translate-y-1 hover:border-red-500/20 hover:shadow-[0_26px_60px_rgba(2,6,23,0.12)] sm:rounded-[1.6rem] sm:p-5"
            data-aos="fade-up"
            :data-aos-delay="aosDelay(index, 65, 220)"
            :data-aos-duration="aosDuration(720, 560)"
            :data-aos-offset="aosOffset(76, 32)"
          >
            <div
              class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-r from-red-500 to-red-700 text-white shadow-md shadow-red-500/20 transition-transform duration-300 group-hover:scale-105"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.4"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <h3 class="text-base font-black text-slate-950">
              {{ item.title }}
            </h3>
            <p class="mt-3 text-justify text-sm leading-7 text-slate-600">
              {{ item.desc }}
            </p>
          </article>
        </div>
      </div>
    </section>

    <!-- Program List -->
    <section
      id="program-list"
      class="cv-auto scroll-mt-24 bg-[linear-gradient(180deg,#fff_0%,#fafafa_100%)] py-12 sm:py-20 lg:py-28"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-10 flex flex-col justify-between gap-5 sm:mb-14 lg:mb-16 md:flex-row md:items-end"
        >
          <div
            data-aos="fade-up"
            :data-aos-duration="aosDuration(760, 580)"
            :data-aos-offset="aosOffset(80, 34)"
          >
            <div
              class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Semua Program
            </div>

            <h2
              class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
            >
              Daftar
              <span
                class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                Program Kerja
              </span>
            </h2>

            <p
              class="mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem]"
            >
              Gunakan filter kategori untuk melihat program kerja sesuai fokus
              pelaksanaannya.
            </p>
          </div>

          <div
            class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0"
            :data-aos="aosAnimation('fade-left', 'fade-up')"
            :data-aos-delay="aosDelay(1, 90, 120)"
            :data-aos-duration="aosDuration(720, 540)"
            :data-aos-offset="aosOffset(72, 30)"
          >
            <button
              v-for="filter in categoryInfo"
              :key="filter.key"
              type="button"
              @click="selectedCategory = filter.key"
              :class="
                selectedCategory === filter.key
                  ? 'bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-white shadow-[0_10px_24px_rgba(220,38,38,0.18)]'
                  : 'border border-slate-900/10 bg-white text-slate-700 hover:border-red-500/20 hover:text-red-700'
              "
              class="inline-flex min-h-[44px] shrink-0 touch-manipulation items-center justify-center rounded-xl px-4 text-sm font-extrabold transition-all duration-300 active:scale-[0.98] sm:min-h-[48px] sm:rounded-2xl"
            >
              {{ filter.key }}
            </button>
          </div>
        </div>

        <div
          v-if="filteredProkers.length"
          class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-7 xl:grid-cols-3 lg:gap-8"
        >
          <article
            v-for="(proker, index) in filteredProkers"
            :key="proker.id || proker.title || index"
            class="group cursor-pointer overflow-hidden rounded-[1.45rem] border border-slate-900/5 bg-white shadow-[0_14px_40px_rgba(2,6,23,0.06)] outline-none transition-all duration-300 hover:-translate-y-2 hover:border-red-500/20 hover:shadow-[0_30px_75px_rgba(2,6,23,0.14)] focus-visible:ring-4 focus-visible:ring-red-500/20 sm:rounded-[1.6rem]"
            data-aos="fade-up"
            :data-aos-delay="aosDelay(index, 70, 240)"
            :data-aos-duration="aosDuration(760, 560)"
            :data-aos-offset="aosOffset(86, 34)"
            role="button"
            tabindex="0"
            @click="openDetail(proker)"
            @keydown.enter.prevent="openDetail(proker)"
            @keydown.space.prevent="openDetail(proker)"
          >
            <div
              class="relative h-44 overflow-hidden bg-slate-100 min-[390px]:h-48 sm:h-56 lg:h-60"
            >
              <img
                :src="getProgramImage(proker)"
                :srcset="getProgramImageSrcset(proker) || undefined"
                sizes="(max-width: 640px) 92vw, (max-width: 1280px) 45vw, 360px"
                :alt="getProgramTitle(proker) || 'Program Kerja HMPS RPL'"
                width="720"
                height="520"
                loading="lazy"
                fetchpriority="low"
                decoding="async"
                class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              />
              <div
                class="absolute inset-0 bg-[linear-gradient(to_top,rgba(6,11,22,0.68),rgba(6,11,22,0.08)_58%,transparent)]"
              />
              <span
                :class="getProgramTagClass(proker)"
                class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[0.66rem] font-extrabold uppercase tracking-[0.06em] text-white shadow-[0_12px_24px_rgba(2,6,23,0.16)] sm:text-[0.68rem]"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-white/80" />
                {{ getProgramCategory(proker) }}
              </span>
            </div>

            <div class="p-4 min-[390px]:p-5 sm:p-6">
              <div
                class="mb-4 flex flex-wrap gap-2 text-[0.72rem] font-bold text-slate-500 sm:text-[0.74rem]"
              >
                <span
                  class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1.5 ring-1 ring-slate-900/5"
                >
                  <svg
                    class="h-3.5 w-3.5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  {{ getProgramDate(proker) }}
                </span>

                <span
                  class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1.5 ring-1 ring-slate-900/5"
                >
                  <svg
                    class="h-3.5 w-3.5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"
                    />
                  </svg>
                  {{ getProgramDivision(proker) }}
                </span>
              </div>

              <h3
                class="line-clamp-2 text-lg font-black leading-snug tracking-[-0.02em] text-slate-950 sm:text-xl"
              >
                {{ getProgramTitle(proker) }}
              </h3>

              <p class="mt-3 line-clamp-3 text-justify text-sm leading-7 text-slate-500">
                {{ getProgramShortDescription(proker) }}
              </p>

              <div class="mt-5 rounded-[1.15rem] border border-slate-100 bg-slate-50 p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <p
                      class="text-[0.65rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                    >
                      Anggaran
                    </p>
                    <h4 class="mt-1 text-sm font-black text-slate-950 sm:text-base">
                      {{ getProgramBudgetText(proker) }}
                    </h4>
                  </div>
                  <div class="sm:text-right">
                    <p
                      class="text-[0.65rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                    >
                      PJ
                    </p>
                    <h4 class="mt-1 text-sm font-bold text-slate-950">
                      {{ getProgramPerson(proker) }}
                    </h4>
                  </div>
                </div>
              </div>

              <div
                class="relative mt-6 inline-flex min-h-[50px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_10px_24px_rgba(2,6,23,0.18)] transition-all duration-300 group-hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] group-hover:shadow-[0_18px_34px_rgba(220,38,38,0.24)]"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
                />
                <span class="relative z-10">Lihat Detail</span>
                <svg
                  class="relative z-10 h-4 w-4 shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.4"
                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                  />
                </svg>
              </div>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-[0_14px_36px_rgba(2,6,23,0.04)]"
          data-aos="fade-up"
          :data-aos-duration="aosDuration(700, 540)"
          :data-aos-offset="aosOffset(72, 30)"
        >
          <div
            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-2xl"
          >
            📌
          </div>
          <h3
            class="mt-5 text-xl font-black tracking-[-0.03em] text-slate-950 sm:text-2xl"
          >
            Program kerja belum tersedia
          </h3>
          <p class="mx-auto mt-3 max-w-xl text-justify text-sm leading-7 text-slate-600">
            Tambahkan data program kerja melalui admin agar daftar program dapat
            ditampilkan pada halaman ini.
          </p>
        </div>
      </div>
    </section>

    <!-- Highlight -->
    <section id="highlight" class="cv-auto scroll-mt-24 bg-white py-12 sm:py-20 lg:py-24">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mx-auto mb-10 max-w-3xl text-center sm:mb-14 lg:mb-16"
          data-aos="fade-up"
          :data-aos-duration="aosDuration(760, 580)"
          :data-aos-offset="aosOffset(80, 34)"
        >
          <div
            class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            Program Pilihan
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            Sorotan
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              Program Kerja
            </span>
          </h2>

          <p
            class="mx-auto mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-center sm:text-[1rem]"
          >
            Beberapa program kerja yang paling menonjol dan merepresentasikan arah gerak
            organisasi.
          </p>
        </div>

        <div
          v-if="featuredPrograms.length"
          class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 lg:gap-8"
        >
          <article
            v-for="(proker, index) in featuredPrograms"
            :key="`featured-${proker.id || proker.title || index}`"
            class="group relative cursor-pointer overflow-hidden rounded-[1.55rem] border border-slate-900/5 bg-slate-900 shadow-[0_18px_42px_rgba(2,6,23,0.16)] outline-none transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_28px_70px_rgba(2,6,23,0.22)] focus-visible:ring-4 focus-visible:ring-red-500/20 sm:rounded-[1.75rem]"
            data-aos="zoom-in-up"
            :data-aos-delay="aosDelay(index, 80, 220)"
            :data-aos-duration="aosDuration(780, 560)"
            :data-aos-offset="aosOffset(86, 34)"
            role="button"
            tabindex="0"
            @click="openDetail(proker)"
            @keydown.enter.prevent="openDetail(proker)"
            @keydown.space.prevent="openDetail(proker)"
          >
            <img
              :src="getProgramImage(proker)"
              :srcset="getProgramImageSrcset(proker) || undefined"
              sizes="(max-width: 640px) 92vw, (max-width: 1280px) 45vw, 360px"
              :alt="getProgramTitle(proker) || 'Sorotan Program Kerja'"
              width="720"
              height="720"
              loading="lazy"
              fetchpriority="low"
              decoding="async"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div
              class="absolute inset-0 bg-[linear-gradient(to_top,rgba(2,6,23,0.90),rgba(2,6,23,0.42),rgba(2,6,23,0.10))]"
            />

            <div
              class="relative z-10 flex min-h-[300px] flex-col justify-end p-5 min-[390px]:min-h-[330px] sm:min-h-[360px] sm:p-6"
            >
              <span
                :class="getProgramTagClass(proker)"
                class="mb-4 inline-flex w-fit rounded-full px-3 py-1.5 text-[0.72rem] font-extrabold tracking-[0.04em] text-white"
              >
                {{ getProgramCategory(proker) }}
              </span>

              <h3 class="text-xl font-black leading-tight text-white">
                {{ getProgramTitle(proker) }}
              </h3>

              <p
                class="mt-3 line-clamp-3 text-justify text-sm leading-7 text-slate-200/90"
              >
                {{ getProgramShortDescription(proker) }}
              </p>

              <div
                class="mt-5 inline-flex w-fit items-center gap-2 text-sm font-extrabold text-white transition-colors group-hover:text-red-200"
              >
                Lihat Detail
                <svg
                  class="h-4 w-4 shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.3"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </div>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
          data-aos="fade-up"
          :data-aos-duration="aosDuration(700, 540)"
          :data-aos-offset="aosOffset(72, 30)"
        >
          <p class="text-sm font-bold text-slate-500">Belum ada program pilihan.</p>
        </div>
      </div>
    </section>

    <!-- Modal Detail -->
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
          v-if="selectedProgram"
          class="fixed inset-0 z-[999] flex items-end justify-center bg-slate-950/75 px-3 pb-3 pt-12 backdrop-blur-md sm:items-center sm:p-4"
          role="dialog"
          aria-modal="true"
          @click.self="closeDetail"
        >
          <div
            class="max-h-[92svh] w-full max-w-5xl overflow-hidden rounded-t-[1.8rem] bg-white shadow-[0_40px_120px_rgba(2,6,23,0.35)] sm:rounded-[1.8rem]"
          >
            <div class="max-h-[92svh] overflow-y-auto overscroll-contain">
              <div class="relative h-52 min-[390px]:h-60 sm:h-80">
                <img
                  :src="getProgramImage(selectedProgram)"
                  :srcset="getProgramImageSrcset(selectedProgram) || undefined"
                  sizes="(max-width: 640px) 100vw, 960px"
                  :alt="getProgramTitle(selectedProgram)"
                  width="960"
                  height="540"
                  loading="lazy"
                  decoding="async"
                  class="h-full w-full object-cover"
                />
                <div
                  class="absolute inset-0 bg-[linear-gradient(to_top,rgba(2,6,23,0.88),rgba(2,6,23,0.26))]"
                />

                <button
                  type="button"
                  aria-label="Tutup detail program"
                  @click="closeDetail"
                  class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-lg transition hover:bg-red-50 hover:text-red-700"
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

                <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-8">
                  <span
                    :class="getProgramTagClass(selectedProgram)"
                    class="mb-4 inline-flex rounded-full px-3 py-1.5 text-[0.72rem] font-extrabold tracking-[0.04em] text-white"
                  >
                    {{ getProgramCategory(selectedProgram) }}
                  </span>

                  <h3
                    class="max-w-3xl text-2xl font-black leading-tight text-white sm:text-3xl"
                  >
                    {{ getProgramTitle(selectedProgram) }}
                  </h3>
                </div>
              </div>

              <div class="p-5 sm:p-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                  <div class="rounded-[1.1rem] border border-slate-100 bg-slate-50 p-4">
                    <p
                      class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                    >
                      Waktu
                    </p>
                    <p class="mt-2 text-sm font-bold text-slate-950">
                      {{ getProgramDate(selectedProgram) }}
                    </p>
                  </div>

                  <div class="rounded-[1.1rem] border border-slate-100 bg-slate-50 p-4">
                    <p
                      class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                    >
                      Divisi
                    </p>
                    <p class="mt-2 text-sm font-bold text-slate-950">
                      {{ getProgramDivision(selectedProgram) }}
                    </p>
                  </div>

                  <div class="rounded-[1.1rem] border border-slate-100 bg-slate-50 p-4">
                    <p
                      class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                    >
                      Penanggung Jawab
                    </p>
                    <p class="mt-2 text-sm font-bold text-slate-950">
                      {{ getProgramPerson(selectedProgram) }}
                    </p>
                  </div>

                  <div class="rounded-[1.1rem] border border-slate-100 bg-slate-50 p-4">
                    <p
                      class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-slate-500"
                    >
                      Anggaran
                    </p>
                    <p class="mt-2 text-sm font-bold text-slate-950">
                      {{ getProgramBudgetText(selectedProgram) }}
                    </p>
                  </div>
                </div>

                <div
                  class="mt-6 rounded-[1.35rem] border border-slate-100 bg-slate-50 p-5 sm:p-6"
                >
                  <h4 class="text-lg font-black text-slate-950">Deskripsi Program</h4>
                  <p
                    class="mt-3 text-justify text-sm leading-8 text-slate-600 sm:text-base"
                  >
                    {{ getProgramDescription(selectedProgram) }}
                  </p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                  <div
                    class="rounded-[1.35rem] border border-slate-200 bg-white p-5 sm:p-6"
                  >
                    <h4 class="text-lg font-black text-slate-950">Tujuan</h4>
                    <ul
                      v-if="getProgramGoals(selectedProgram).length"
                      class="mt-4 space-y-3"
                    >
                      <li
                        v-for="goal in getProgramGoals(selectedProgram)"
                        :key="goal"
                        class="flex items-start gap-3 text-sm leading-7 text-slate-600"
                      >
                        <span
                          class="mt-[0.55rem] h-2 w-2 shrink-0 rounded-full bg-red-500"
                        />
                        <span class="text-justify">{{ goal }}</span>
                      </li>
                    </ul>
                    <p v-else class="mt-3 text-sm leading-7 text-slate-500">
                      Tujuan belum tersedia.
                    </p>
                  </div>

                  <div
                    class="rounded-[1.35rem] border border-slate-200 bg-white p-5 sm:p-6"
                  >
                    <h4 class="text-lg font-black text-slate-950">Manfaat</h4>
                    <ul
                      v-if="getProgramBenefits(selectedProgram).length"
                      class="mt-4 space-y-3"
                    >
                      <li
                        v-for="benefit in getProgramBenefits(selectedProgram)"
                        :key="benefit"
                        class="flex items-start gap-3 text-sm leading-7 text-slate-600"
                      >
                        <span
                          class="mt-[0.55rem] h-2 w-2 shrink-0 rounded-full bg-red-500"
                        />
                        <span class="text-justify">{{ benefit }}</span>
                      </li>
                    </ul>
                    <p v-else class="mt-3 text-sm leading-7 text-slate-500">
                      Manfaat belum tersedia.
                    </p>
                  </div>
                </div>

                <div
                  class="mt-6 rounded-[1.35rem] border border-slate-200 bg-white p-5 sm:p-6"
                >
                  <h4 class="text-lg font-black text-slate-950">Sumber Anggaran</h4>
                  <ul
                    v-if="getProgramSources(selectedProgram).length"
                    class="mt-4 space-y-3"
                  >
                    <li
                      v-for="source in getProgramSources(selectedProgram)"
                      :key="source"
                      class="flex items-start gap-3 text-sm leading-7 text-slate-600"
                    >
                      <span
                        class="mt-[0.55rem] h-2 w-2 shrink-0 rounded-full bg-red-500"
                      />
                      <span class="text-justify">{{ source }}</span>
                    </li>
                  </ul>
                  <p v-else class="mt-3 text-sm leading-7 text-slate-500">
                    Sumber anggaran belum tersedia.
                  </p>
                </div>

                <div class="mt-8 flex justify-end">
                  <button
                    type="button"
                    @click="closeDetail"
                    class="group relative inline-flex min-h-[50px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_10px_24px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_18px_34px_rgba(220,38,38,0.24)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                  >
                    <span
                      class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
                    />
                    <span class="relative z-10">Tutup Detail</span>
                  </button>
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
.ticker-inner {
  animation: tickerMove 30s linear infinite;
}

.ticker-wrap:hover .ticker-inner {
  animation-play-state: paused;
}

@keyframes tickerMove {
  0% {
    transform: translateX(0);
  }

  100% {
    transform: translateX(-50%);
  }
}

@media (min-width: 1024px) {
  .floating-badge {
    animation: floatY 4.2s ease-in-out infinite;
  }
}

@keyframes floatY {
  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-7px);
  }
}

@media (prefers-reduced-motion: reduce) {
  .ticker-inner,
  .floating-badge {
    animation: none;
  }

  .program-page :deep([data-aos]) {
    opacity: 1 !important;
    transform: none !important;
    transition-duration: 1ms !important;
  }
}

.program-page :deep([data-aos]) {
  backface-visibility: hidden;
  transform-style: preserve-3d;
}

.program-page :deep(p) {
  text-align: justify;
}

.program-page :deep(img) {
  max-width: 100%;
}

.program-page :deep(a),
.program-page :deep(button) {
  -webkit-tap-highlight-color: transparent;
}

.program-hero-action {
  background: linear-gradient(135deg, #081120, #0f172a) !important;
}

.program-hero-action:hover,
.program-hero-action:focus-visible,
.program-hero-action:active {
  background: linear-gradient(135deg, #ef4444, #dc2626, #991b1b) !important;
}

.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 920px;
}

@media (max-width: 640px) {
  .cv-auto {
    contain-intrinsic-size: 1px 760px;
  }
}

.no-scrollbar {
  scrollbar-width: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>

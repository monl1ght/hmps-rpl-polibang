<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  managementHero: {
    type: Object,
    default: () => ({}),
  },
  topLeaders: {
    type: Array,
    default: () => [],
  },
  divisions: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Array,
    default: () => [],
  },
});

const ticks = [
  "Kepengurusan HMPS RPL",
  "Pengurus Inti",
  "Koordinator Divisi",
  "Anggota Divisi",
  "Kolaboratif",
  "Profesional",
  "Aktif dan Berdampak",
];

const fallbackHero = {
  badge: "Struktur Kepengurusan HMPS RPL",
  title: "Kepengurusan",
  titleHighlight: "HMPS RPL",
  description:
    "Halaman ini menampilkan susunan kepengurusan HMPS Rekayasa Perangkat Lunak, mulai dari pengurus inti, koordinator divisi, hingga anggota pada masing-masing divisi secara rapi dan profesional.",
  primaryButtonLabel: "Inti",
  primaryButtonUrl: "#pengurus-inti",
  secondaryButtonLabel: "Divisi",
  secondaryButtonUrl: "#divisi",
  tertiaryButtonLabel: "Semangat",
  tertiaryButtonUrl: "#closing",
  floatingBadgeTopIcon: "👥",
  floatingBadgeTopTitle: "Struktur Tersusun",
  floatingBadgeTopSubtitle: "Pengurus dan divisi",
  floatingBadgeBottomIcon: "🚀",
  floatingBadgeBottomTitle: "Aktif & Profesional",
  floatingBadgeBottomSubtitle: "HMPS Rekayasa Perangkat Lunak",
};

const fallbackHeroImages = [
  {
    src:
      "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop&auto=format",
    alt: "Kolaborasi pengurus HMPS RPL",
  },
  {
    src:
      "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=700&h=900&fit=crop&auto=format",
    alt: "Diskusi pengurus organisasi",
  },
  {
    src:
      "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=900&fit=crop&auto=format",
    alt: "Kegiatan organisasi mahasiswa",
  },
  {
    src:
      "https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=600&fit=crop&auto=format",
    alt: "Koordinasi kegiatan HMPS RPL",
  },
];

const fallbackPhoto = "https://i.pravatar.cc/600?img=32";

const selectedPerson = ref(null);
const isMobileViewport = ref(
  typeof window !== "undefined" && window.matchMedia("(max-width: 767px)").matches
);
const prefersReducedMotion = ref(
  typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches
);
const isDesktopHeroVisualVisible = ref(
  typeof window !== "undefined" && window.matchMedia("(min-width: 768px)").matches
);

let heroVisualMediaQuery = null;
let reducedMotionMediaQuery = null;

const updateHeroVisualVisibility = () => {
  if (typeof window === "undefined") return;

  isMobileViewport.value = window.matchMedia("(max-width: 767px)").matches;
  isDesktopHeroVisualVisible.value = window.matchMedia("(min-width: 768px)").matches;
};

const updateMotionPreference = () => {
  if (typeof window === "undefined") return;

  prefersReducedMotion.value = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
};

const aosAnimation = (desktopAnimation = "fade-up", mobileAnimation = "fade-up") => {
  if (prefersReducedMotion.value) return "fade-up";

  return isMobileViewport.value ? mobileAnimation : desktopAnimation;
};

const aosDuration = (desktopDuration = 760, mobileDuration = 540) => {
  if (prefersReducedMotion.value) return 1;

  return isMobileViewport.value ? mobileDuration : desktopDuration;
};

const aosOffset = (desktopOffset = 88, mobileOffset = 42) => {
  if (prefersReducedMotion.value) return 0;

  return isMobileViewport.value ? mobileOffset : desktopOffset;
};

const aosDelay = (index = 0, step = 70, maxDelay = 280) => {
  if (prefersReducedMotion.value) return 0;

  const safeIndex = Number.isFinite(Number(index)) ? Number(index) : 0;
  const safeStep = Math.max(0, Number(step) || 0);
  const mobileStep = Math.min(safeStep, 42);
  const safeMax = Math.max(0, Number(maxDelay) || 0);
  const mobileMax = Math.min(safeMax, 150);

  return Math.min(safeIndex * (isMobileViewport.value ? mobileStep : safeStep), isMobileViewport.value ? mobileMax : safeMax);
};

const refreshAos = () => {
  if (typeof window === "undefined") return;

  nextTick(() => {
    const runRefresh = () => {
      const aos = window.AOS;

      if (!aos) return;

      if (typeof aos.refreshHard === "function") {
        aos.refreshHard();
        return;
      }

      if (typeof aos.refresh === "function") {
        aos.refresh();
      }
    };

    if (typeof window.requestAnimationFrame === "function") {
      window.requestAnimationFrame(runRefresh);
      return;
    }

    window.setTimeout(runRefresh, 0);
  });
};

const leaders = computed(() => (Array.isArray(props.topLeaders) ? props.topLeaders : []));
const divisions = computed(() => (Array.isArray(props.divisions) ? props.divisions : []));

const heroConfig = computed(() => ({
  ...fallbackHero,
  ...(props.managementHero || {}),
}));

const heroImages = computed(() => [
  createHeroImage({
    source: {
      image: heroConfig.value.imageOneUrl || heroConfig.value.image_one_url,
    },
    fallback: fallbackHeroImages[0],
    alt: heroConfig.value.imageOneAlt || heroConfig.value.image_one_alt,
    variantGroup: "one",
    index: 0,
  }),
  createHeroImage({
    source: {
      image: heroConfig.value.imageTwoUrl || heroConfig.value.image_two_url,
    },
    fallback: fallbackHeroImages[1],
    alt: heroConfig.value.imageTwoAlt || heroConfig.value.image_two_alt,
    variantGroup: "two",
    index: 1,
  }),
  createHeroImage({
    source: {
      image: heroConfig.value.imageThreeUrl || heroConfig.value.image_three_url,
    },
    fallback: fallbackHeroImages[2],
    alt: heroConfig.value.imageThreeAlt || heroConfig.value.image_three_alt,
    variantGroup: "three",
    index: 2,
  }),
  createHeroImage({
    source: {
      image: heroConfig.value.imageFourUrl || heroConfig.value.image_four_url,
    },
    fallback: fallbackHeroImages[3],
    alt: heroConfig.value.imageFourAlt || heroConfig.value.image_four_alt,
    variantGroup: "four",
    index: 3,
  }),
]);

const totalMembers = computed(() => {
  return divisions.value.reduce((total, division) => {
    return total + (Array.isArray(division.members) ? division.members.length : 0);
  }, 0);
});

const computedStats = computed(() => {
  if (Array.isArray(props.stats) && props.stats.length) {
    return props.stats;
  }

  return [
    {
      label: "Pengurus Inti",
      value: leaders.value.length || "—",
    },
    {
      label: "Divisi",
      value: divisions.value.length || "—",
    },
    {
      label: "Anggota",
      value: totalMembers.value || "—",
    },
  ];
});

const imageSourceKeys = [
  "image",
  "image_url",
  "imageUrl",
  "cover_url",
  "coverUrl",
  "photo_url",
  "photoUrl",
  "photo",
  "avatar_url",
  "avatarUrl",
  "avatar",
  "src",
  "url",
];

const imageVariantKeys = {
  sm: [
    "image_sm",
    "imageSmall",
    "image_small",
    "small_url",
    "smallUrl",
    "photo_sm",
    "photoSmall",
    "avatar_sm",
    "avatarSmall",
    "thumbnail",
    "thumbnail_url",
    "thumb_url",
  ],
  md: [
    "image_md",
    "imageMedium",
    "image_medium",
    "medium_url",
    "mediumUrl",
    "photo_md",
    "photoMedium",
    "avatar_md",
    "avatarMedium",
  ],
  lg: [
    "image_lg",
    "imageLarge",
    "image_large",
    "large_url",
    "largeUrl",
    "photo_lg",
    "photoLarge",
    "avatar_lg",
    "avatarLarge",
  ],
};

const heroVariantKeys = {
  one: {
    sm: ["imageOneSmUrl", "image_one_sm_url", "imageOneSmallUrl", "image_one_small_url"],
    md: [
      "imageOneMdUrl",
      "image_one_md_url",
      "imageOneMediumUrl",
      "image_one_medium_url",
    ],
    lg: ["imageOneLgUrl", "image_one_lg_url", "imageOneLargeUrl", "image_one_large_url"],
  },
  two: {
    sm: ["imageTwoSmUrl", "image_two_sm_url", "imageTwoSmallUrl", "image_two_small_url"],
    md: [
      "imageTwoMdUrl",
      "image_two_md_url",
      "imageTwoMediumUrl",
      "image_two_medium_url",
    ],
    lg: ["imageTwoLgUrl", "image_two_lg_url", "imageTwoLargeUrl", "image_two_large_url"],
  },
  three: {
    sm: [
      "imageThreeSmUrl",
      "image_three_sm_url",
      "imageThreeSmallUrl",
      "image_three_small_url",
    ],
    md: [
      "imageThreeMdUrl",
      "image_three_md_url",
      "imageThreeMediumUrl",
      "image_three_medium_url",
    ],
    lg: [
      "imageThreeLgUrl",
      "image_three_lg_url",
      "imageThreeLargeUrl",
      "image_three_large_url",
    ],
  },
  four: {
    sm: [
      "imageFourSmUrl",
      "image_four_sm_url",
      "imageFourSmallUrl",
      "image_four_small_url",
    ],
    md: [
      "imageFourMdUrl",
      "image_four_md_url",
      "imageFourMediumUrl",
      "image_four_medium_url",
    ],
    lg: [
      "imageFourLgUrl",
      "image_four_lg_url",
      "imageFourLargeUrl",
      "image_four_large_url",
    ],
  },
};

const invalidUrlValues = new Set(["", "null", "undefined", "false", "none", "nan"]);

const normalizeUrl = (value) => {
  if (typeof value !== "string") return "";

  const normalized = value.trim();

  return invalidUrlValues.has(normalized.toLowerCase()) ? "" : normalized;
};

const pickFirstUrl = (source, keys = []) => {
  if (!source || typeof source !== "object") return "";

  for (const key of keys) {
    const value = normalizeUrl(source[key]);
    if (value) return value;
  }

  return "";
};

const resolveImageSource = (source, fallback = "") => {
  if (typeof source === "string") return normalizeUrl(source) || fallback;

  return pickFirstUrl(source, imageSourceKeys) || fallback;
};

const isUnsplashImage = (url) => /images\.unsplash\.com/i.test(String(url || ""));
const isPravatarImage = (url) => /i\.pravatar\.cc/i.test(String(url || ""));

const withUrlParams = (url, params = {}) => {
  const normalizedUrl = normalizeUrl(url);
  if (!normalizedUrl) return "";

  try {
    const parsedUrl = new URL(
      normalizedUrl,
      typeof window === "undefined" ? "http://localhost" : window.location.origin
    );

    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") {
        parsedUrl.searchParams.set(key, value);
      }
    });

    return parsedUrl.href;
  } catch (error) {
    return normalizedUrl;
  }
};

const optimizeRemoteImage = (url, width = 720, height = 540, quality = 72) => {
  const normalizedUrl = normalizeUrl(url);
  if (!normalizedUrl) return "";

  if (isUnsplashImage(normalizedUrl)) {
    return withUrlParams(normalizedUrl, {
      w: width,
      h: height,
      fit: "crop",
      auto: "format",
      q: quality,
    });
  }

  if (isPravatarImage(normalizedUrl)) {
    return normalizedUrl.replace(/\/\d+(?=\?|$)/, `/${width}`);
  }

  return normalizedUrl;
};

const createImageSrcset = ({
  source,
  fallback = "",
  widths = [360, 640, 960],
  heightRatio = 0.75,
}) => {
  const baseImage = resolveImageSource(source, fallback);

  if (!baseImage) return "";

  const variants = [
    pickFirstUrl(source, imageVariantKeys.sm) ||
      optimizeRemoteImage(baseImage, widths[0], Math.round(widths[0] * heightRatio), 72),
    pickFirstUrl(source, imageVariantKeys.md) ||
      optimizeRemoteImage(baseImage, widths[1], Math.round(widths[1] * heightRatio), 74),
    pickFirstUrl(source, imageVariantKeys.lg) ||
      optimizeRemoteImage(baseImage, widths[2], Math.round(widths[2] * heightRatio), 76),
  ];

  return variants
    .map((url, index) => (url ? `${url} ${widths[index]}w` : ""))
    .filter(Boolean)
    .join(", ");
};

const createHeroImage = ({ source, fallback, alt, variantGroup, index = 0 }) => {
  const sourceObject =
    typeof source === "object" && source !== null ? source : { src: source };
  const variantKeys = heroVariantKeys[variantGroup] || {};
  const enrichedSource = {
    ...sourceObject,
    image: resolveImageSource(sourceObject, fallback?.src || ""),
    image_sm: pickFirstUrl(heroConfig.value, variantKeys.sm || []),
    image_md: pickFirstUrl(heroConfig.value, variantKeys.md || []),
    image_lg: pickFirstUrl(heroConfig.value, variantKeys.lg || []),
  };
  const width = index === 0 || index === 3 ? 640 : 720;
  const height = index === 0 || index === 3 ? 460 : 900;
  const image = resolveImageSource(enrichedSource, fallback?.src || "");

  return {
    src: optimizeRemoteImage(image, width, height, 74),
    srcset: createImageSrcset({
      source: enrichedSource,
      fallback: fallback?.src || "",
      widths: index === 0 || index === 3 ? [320, 520, 720] : [360, 640, 900],
      heightRatio: index === 0 || index === 3 ? 0.72 : 1.25,
    }),
    alt: alt || fallback?.alt || "Foto kepengurusan HMPS RPL",
  };
};

const getPhotoSrcset = (photo, fallback = fallbackPhoto) =>
  createImageSrcset({
    source: photo,
    fallback,
    widths: [320, 520, 760],
    heightRatio: 1.25,
  });

const primaryHeroImage = computed(() => heroImages.value[0]?.src || "");
const primaryHeroSrcset = computed(() => heroImages.value[0]?.srcset || "");

const getPhoto = (photo, fallback = fallbackPhoto) => {
  const image = resolveImageSource(photo, fallback);

  return optimizeRemoteImage(image, 760, 950, 76) || fallback;
};

const getInitials = (name) => {
  const words = String(name || "HMPS RPL")
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  return (
    words
      .slice(0, 2)
      .map((word) => word.charAt(0).toUpperCase())
      .join("") || "HR"
  );
};

const normalizeRoleLabel = (role, fallback = "Pengurus Inti") => {
  const value = String(role || fallback).trim();

  if (!value) return fallback;

  const normalized = value.toLowerCase();

  if (normalized === "kettua" || normalized === "ketuaa") return "Ketua";
  if (normalized === "ketua") return "Ketua";
  if (normalized === "sekretaris") return "Sekretaris";
  if (normalized === "bendahara") return "Bendahara";

  return value;
};

const getDivisionMembers = (division) => {
  return Array.isArray(division?.members) ? division.members : [];
};

const getCoreTaskDescription = (leader) => {
  const customDescription = String(
    leader?.task_description || leader?.taskDescription || leader?.description || ""
  ).trim();

  if (customDescription) return customDescription;

  const role = normalizeRoleLabel(leader?.role || leader?.position || "Pengurus Inti");
  const normalizedRole = role.toLowerCase();

  if (normalizedRole.includes("ketua")) {
    return "Bertanggung jawab memimpin arah organisasi, mengoordinasikan pengurus, mengambil keputusan strategis, serta memastikan seluruh program kerja berjalan selaras dengan visi HMPS RPL.";
  }

  if (normalizedRole.includes("sekretaris")) {
    return "Bertanggung jawab mengelola administrasi organisasi, surat-menyurat, notulensi, arsip, dan dokumentasi resmi agar tata kelola HMPS RPL berjalan tertib dan profesional.";
  }

  if (normalizedRole.includes("bendahara")) {
    return "Bertanggung jawab mengelola keuangan organisasi, menyusun pencatatan pemasukan dan pengeluaran, serta menjaga transparansi laporan keuangan HMPS RPL.";
  }

  return "Pengurus inti HMPS RPL yang berperan aktif dalam koordinasi dan pelaksanaan kegiatan organisasi.";
};

const formatPersonPayload = ({
  name,
  role,
  division = null,
  photo = null,
  description = null,
}) => ({
  name: name || "Nama belum tersedia",
  role: role || "Pengurus HMPS RPL",
  division,
  photo: getPhoto(photo),
  description,
});

const openPersonDetail = (person) => {
  selectedPerson.value = person;

  if (typeof document !== "undefined") {
    document.body.style.overflow = "hidden";
  }
};

const closePersonDetail = () => {
  selectedPerson.value = null;

  if (typeof document !== "undefined") {
    document.body.style.overflow = "";
  }
};

const scrollToSection = (id) => {
  if (typeof document === "undefined") return;

  const target = document.getElementById(id);

  if (target) {
    target.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
};

const handleHeroAction = (url, fallbackId) => {
  const target = String(url || "").trim();

  if (!target) {
    scrollToSection(fallbackId);
    return;
  }

  if (target.startsWith("#")) {
    scrollToSection(target.slice(1));
    return;
  }

  if (target.startsWith("http://") || target.startsWith("https://")) {
    window.open(target, "_blank", "noopener,noreferrer");
    return;
  }

  window.location.href = target;
};

const handleEscape = (event) => {
  if (event.key === "Escape" && selectedPerson.value) {
    closePersonDetail();
  }
};

watch(
  () => [
    leaders.value.length,
    divisions.value.length,
    totalMembers.value,
    computedStats.value.length,
    heroImages.value.map((image) => image.src).join("|"),
    isDesktopHeroVisualVisible.value,
  ],
  refreshAos,
  { flush: "post" }
);

onMounted(() => {
  window.addEventListener("keydown", handleEscape);

  heroVisualMediaQuery = window.matchMedia("(min-width: 768px)");
  reducedMotionMediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

  updateHeroVisualVisibility();
  updateMotionPreference();

  if (typeof heroVisualMediaQuery.addEventListener === "function") {
    heroVisualMediaQuery.addEventListener("change", updateHeroVisualVisibility);
  } else if (typeof heroVisualMediaQuery.addListener === "function") {
    heroVisualMediaQuery.addListener(updateHeroVisualVisibility);
  }

  if (typeof reducedMotionMediaQuery.addEventListener === "function") {
    reducedMotionMediaQuery.addEventListener("change", updateMotionPreference);
  } else if (typeof reducedMotionMediaQuery.addListener === "function") {
    reducedMotionMediaQuery.addListener(updateMotionPreference);
  }

  refreshAos();
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

  if (reducedMotionMediaQuery) {
    if (typeof reducedMotionMediaQuery.removeEventListener === "function") {
      reducedMotionMediaQuery.removeEventListener("change", updateMotionPreference);
    } else if (typeof reducedMotionMediaQuery.removeListener === "function") {
      reducedMotionMediaQuery.removeListener(updateMotionPreference);
    }
  }

  if (typeof document !== "undefined") {
    document.body.style.overflow = "";
  }
});
</script>

<template>
  <Head title="Kepengurusan HMPS RPL">
    <meta
      name="description"
      :content="
        heroConfig.description ||
        'Struktur kepengurusan HMPS Rekayasa Perangkat Lunak yang menampilkan pengurus inti, divisi, dan anggota secara profesional.'
      "
    />
    <meta
      name="keywords"
      content="HMPS RPL, kepengurusan HMPS RPL, pengurus inti, divisi HMPS, Rekayasa Perangkat Lunak"
    />
    <meta property="og:title" content="Kepengurusan HMPS RPL" />
    <meta
      property="og:description"
      :content="
        heroConfig.description ||
        'Struktur kepengurusan HMPS Rekayasa Perangkat Lunak yang ditampilkan secara rapi dan profesional.'
      "
    />
    <meta property="og:type" content="website" />
    <meta name="robots" content="index, follow" />
    <link
      v-if="primaryHeroImage"
      rel="preload"
      as="image"
      :href="primaryHeroImage"
      :imagesrcset="primaryHeroSrcset || null"
      imagesizes="(min-width: 1024px) 320px, 260px"
      media="(min-width: 768px)"
      fetchpriority="high"
    />
  </Head>

  <div
    class="kepengurusan-page overflow-x-hidden bg-white font-['Plus_Jakarta_Sans',sans-serif] text-slate-950 antialiased"
  >
    <!-- Hero -->
    <section
      class="relative overflow-hidden bg-[radial-gradient(circle_at_12%_18%,rgba(239,68,68,0.12),transparent_28%),radial-gradient(circle_at_88%_20%,rgba(153,27,27,0.10),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fff7f7_52%,#ffffff_100%)] pb-10 pt-20 sm:pb-14 sm:pt-24 lg:flex lg:min-h-[92svh] lg:items-center lg:pt-32"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.09)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.09)_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:linear-gradient(180deg,rgba(255,255,255,0.88),transparent_90%)]"
        aria-hidden="true"
      />
      <div
        class="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-red-500/10 blur-[78px] lg:h-96 lg:w-96"
        aria-hidden="true"
      />
      <div
        class="pointer-events-none absolute -bottom-20 -left-24 h-64 w-64 rounded-full bg-rose-900/10 blur-[78px] lg:h-80 lg:w-80"
        aria-hidden="true"
      />

      <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="grid grid-cols-1 items-center gap-8 sm:gap-10 lg:grid-cols-2 xl:gap-14"
        >
          <div
            :data-aos="aosAnimation('fade-right', 'fade-up')"
            :data-aos-duration="aosDuration(860, 560)"
            :data-aos-offset="aosOffset(90, 36)"
            data-aos-easing="ease-out-cubic"
          >
            <div
              class="mb-4 inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/85 px-3 py-2 text-[0.66rem] font-extrabold uppercase tracking-[0.07em] text-red-700 shadow-[0_12px_35px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:mb-5 sm:text-[0.75rem]"
            >
              <span
                class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
                aria-hidden="true"
              />
              <span class="min-w-0 leading-snug">{{ heroConfig.badge }}</span>
            </div>

            <h1
              class="max-w-2xl break-words text-[2.1rem] font-black leading-[1.06] tracking-[-0.045em] text-slate-950 min-[390px]:text-[2.3rem] sm:text-[3.2rem] md:text-[3.8rem] xl:text-[4.8rem]"
            >
              <span
                class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ heroConfig.titleHighlight }}
              </span>
              {{ heroConfig.title }}
            </h1>

            <p
              class="management-copy mt-4 max-w-2xl text-[0.94rem] leading-[1.78] text-slate-600 sm:mt-6 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              {{ heroConfig.description }}
            </p>

            <div
              class="mt-8 grid grid-cols-3 gap-2 sm:max-w-xl sm:gap-3"
              :data-aos="aosAnimation('fade-up', 'fade-up')"
              :data-aos-delay="aosDelay(1, 90, 180)"
              :data-aos-duration="aosDuration(680, 500)"
              :data-aos-offset="aosOffset(72, 32)"
              data-aos-easing="ease-out-cubic"
            >
              <button
                type="button"
                @click="handleHeroAction(heroConfig.primaryButtonUrl, 'pengurus-inti')"
                class="management-hero-action group relative inline-flex min-h-[52px] touch-manipulation items-center justify-center gap-1.5 overflow-hidden rounded-2xl px-3 text-[0.72rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:px-5 sm:text-[0.95rem]"
              >
                <span
                  class="management-hero-shine absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                  aria-hidden="true"
                />
                <svg
                  class="relative z-10 h-3.5 w-3.5 shrink-0 sm:h-4 sm:w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.4"
                    d="M12 16V8m0 8-4-4m4 4 4-4"
                  />
                </svg>
                <span class="relative z-10 truncate">{{
                  heroConfig.primaryButtonLabel
                }}</span>
              </button>

              <button
                type="button"
                @click="handleHeroAction(heroConfig.secondaryButtonUrl, 'divisi')"
                class="management-hero-action group relative inline-flex min-h-[52px] touch-manipulation items-center justify-center gap-1.5 overflow-hidden rounded-2xl px-3 text-[0.72rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:px-5 sm:text-[0.95rem]"
              >
                <span
                  class="management-hero-shine absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                  aria-hidden="true"
                />
                <svg
                  class="relative z-10 h-3.5 w-3.5 shrink-0 sm:h-4 sm:w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.4"
                    d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"
                  />
                </svg>
                <span class="relative z-10 truncate">{{
                  heroConfig.secondaryButtonLabel
                }}</span>
              </button>

              <button
                type="button"
                @click="handleHeroAction(heroConfig.tertiaryButtonUrl, 'closing')"
                class="management-hero-action group relative inline-flex min-h-[52px] touch-manipulation items-center justify-center gap-1.5 overflow-hidden rounded-2xl px-3 text-[0.72rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:px-5 sm:text-[0.95rem]"
              >
                <span
                  class="management-hero-shine absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                  aria-hidden="true"
                />
                <svg
                  class="relative z-10 h-3.5 w-3.5 shrink-0 sm:h-4 sm:w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.4"
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
                <span class="relative z-10 truncate">{{
                  heroConfig.tertiaryButtonLabel
                }}</span>
              </button>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-2 sm:mt-8 sm:max-w-xl sm:gap-3">
              <div
                v-for="(item, index) in computedStats"
                :key="item.label"
                :data-aos="aosAnimation('zoom-in-up', 'fade-up')"
                :data-aos-delay="aosDelay(index, 55, 140)"
                :data-aos-duration="aosDuration(620, 480)"
                :data-aos-offset="aosOffset(62, 28)"
                data-aos-easing="ease-out-cubic"
                class="rounded-2xl border border-slate-900/5 bg-white/80 p-2.5 shadow-[0_10px_28px_rgba(2,6,23,0.05)] backdrop-blur-xl sm:p-4"
              >
                <div
                  class="text-base font-black tracking-[-0.04em] text-slate-950 min-[390px]:text-lg sm:text-2xl"
                >
                  {{ item.value }}
                </div>
                <div
                  class="mt-1 text-[0.58rem] font-extrabold uppercase tracking-[0.05em] text-red-700 min-[390px]:text-[0.62rem] sm:text-[0.72rem]"
                >
                  {{ item.label }}
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="isDesktopHeroVisualVisible"
            :data-aos="aosAnimation('fade-left', 'fade-up')"
            :data-aos-duration="aosDuration(880, 560)"
            :data-aos-offset="aosOffset(100, 38)"
            data-aos-easing="ease-out-cubic"
            class="relative mx-auto w-full max-w-[31rem] sm:max-w-[37rem] lg:mx-auto"
          >
            <div
              class="absolute -left-4 top-10 hidden h-24 w-24 rounded-full border border-red-500/20 lg:block"
              aria-hidden="true"
            />
            <div
              class="absolute -right-5 bottom-12 hidden h-32 w-32 rounded-full border border-slate-900/10 lg:block"
              aria-hidden="true"
            />

            <div
              class="floating-badge hidden w-fit items-center gap-3 rounded-2xl border border-slate-900/5 bg-white/[0.92] px-4 py-3 shadow-[0_18px_44px_rgba(2,6,23,0.12)] backdrop-blur-xl lg:absolute lg:-left-7 lg:top-7 lg:z-20 lg:flex"
            >
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-base text-white sm:h-11 sm:w-11 sm:text-lg"
              >
                {{ heroConfig.floatingBadgeTopIcon }}
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ heroConfig.floatingBadgeTopTitle }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ heroConfig.floatingBadgeTopSubtitle }}
                </div>
              </div>
            </div>

            <div
              class="relative rounded-[1.45rem] border border-slate-900/5 bg-white/80 p-2 shadow-[0_18px_48px_rgba(2,6,23,0.10)] backdrop-blur-xl sm:rounded-[2rem] sm:p-4 lg:shadow-[0_28px_80px_rgba(2,6,23,0.14)]"
            >
              <div
                class="pointer-events-none absolute inset-0 rounded-[1.65rem] [background:linear-gradient(135deg,rgba(239,68,68,0.22),rgba(255,255,255,0.26),rgba(153,27,27,0.16))] [mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:exclude] p-px sm:rounded-[2rem]"
                aria-hidden="true"
              />

              <div class="grid grid-cols-2 gap-2 sm:gap-4">
                <div class="flex flex-col gap-2 pt-5 sm:gap-4 sm:pt-10 lg:pt-12">
                  <div
                    class="group h-24 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <img
                      :src="heroImages[0].src"
                      :srcset="heroImages[0].srcset || null"
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
                      :srcset="heroImages[1].srcset || null"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[1].alt"
                      width="720"
                      height="900"
                      loading="lazy"
                      fetchpriority="low"
                      decoding="async"
                      class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                </div>

                <div class="flex flex-col gap-2 sm:gap-4">
                  <div
                    class="group h-36 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <img
                      :src="heroImages[2].src"
                      :srcset="heroImages[2].srcset || null"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[2].alt"
                      width="720"
                      height="900"
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
                      :srcset="heroImages[3].srcset || null"
                      sizes="(max-width: 640px) 46vw, (max-width: 1024px) 260px, 320px"
                      :alt="heroImages[3].alt"
                      width="640"
                      height="460"
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
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-rose-700 text-base text-white sm:h-11 sm:w-11 sm:text-lg"
              >
                {{ heroConfig.floatingBadgeBottomIcon }}
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ heroConfig.floatingBadgeBottomTitle }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ heroConfig.floatingBadgeBottomSubtitle }}
                </div>
              </div>
            </div>
          </div>

          <div
            v-else
            :data-aos="aosAnimation('fade-up', 'fade-up')"
            :data-aos-delay="aosDelay(1, 60, 120)"
            :data-aos-duration="aosDuration(640, 500)"
            :data-aos-offset="aosOffset(70, 32)"
            data-aos-easing="ease-out-cubic"
            class="relative mx-auto w-full max-w-[31rem] lg:hidden"
            aria-label="Ringkasan struktur kepengurusan HMPS RPL"
          >
            <div
              class="relative overflow-hidden rounded-[1.55rem] border border-slate-900/5 bg-white/88 p-4 shadow-[0_18px_48px_rgba(2,6,23,0.08)] backdrop-blur-xl"
            >
              <div
                class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-red-500/10 blur-3xl"
                aria-hidden="true"
              />
              <div
                class="pointer-events-none absolute -bottom-12 -left-10 h-28 w-28 rounded-full bg-rose-900/10 blur-3xl"
                aria-hidden="true"
              />

              <div class="relative z-10 flex items-start gap-3">
                <div
                  class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] text-xl text-white shadow-[0_12px_26px_rgba(220,38,38,0.24)]"
                  aria-hidden="true"
                >
                  👥
                </div>
                <div class="min-w-0">
                  <p
                    class="text-[0.68rem] font-extrabold uppercase tracking-[0.12em] text-red-700"
                  >
                    Struktur Organisasi
                  </p>
                  <h2
                    class="mt-1 text-xl font-black leading-tight tracking-[-0.035em] text-slate-950"
                  >
                    Pengurus tersusun rapi berdasarkan peran dan divisi.
                  </h2>
                </div>
              </div>

              <div class="relative z-10 mt-5 grid grid-cols-3 gap-2">
                <div
                  v-for="(item, index) in computedStats"
                  :key="`mobile-${item.label}`"
                  :data-aos="aosAnimation('zoom-in-up', 'fade-up')"
                  :data-aos-delay="aosDelay(index, 42, 120)"
                  :data-aos-duration="aosDuration(560, 440)"
                  :data-aos-offset="aosOffset(54, 24)"
                  data-aos-easing="ease-out-cubic"
                  class="rounded-2xl border border-slate-900/[0.06] bg-slate-50/80 px-3 py-3"
                >
                  <div class="text-lg font-black tracking-[-0.04em] text-slate-950">
                    {{ item.value }}
                  </div>
                  <div
                    class="mt-1 text-[0.58rem] font-extrabold uppercase leading-snug tracking-[0.05em] text-slate-500"
                  >
                    {{ item.label }}
                  </div>
                </div>
              </div>

              <div
                class="relative z-10 mt-4 rounded-2xl border border-red-500/10 bg-red-50/70 px-4 py-3 text-xs font-semibold leading-6 text-slate-700"
              >
                Informasi kepengurusan disajikan ringkas agar mudah dipahami dan nyaman
                diakses melalui perangkat mobile.
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

    <!-- Pengurus Inti -->
    <section
      id="pengurus-inti"
      class="cv-auto scroll-mt-24 bg-white py-12 sm:py-20 lg:py-24"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-8 max-w-3xl sm:mb-14 lg:mb-16"
          :data-aos="aosAnimation('fade-up', 'fade-up')"
          :data-aos-duration="aosDuration(760, 520)"
          :data-aos-offset="aosOffset(84, 38)"
          data-aos-easing="ease-out-cubic"
        >
          <div
            class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700 sm:mb-5"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" aria-hidden="true" />
            Pengurus Inti
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            Struktur inti
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              kepengurusan
            </span>
          </h2>

          <p
            class="management-copy mt-3 max-w-2xl text-[0.94rem] leading-[1.78] text-slate-500 sm:mt-4 sm:text-[1rem] lg:text-[1.02rem]"
          >
            Fondasi utama dalam menjalankan roda organisasi dan mengoordinasikan seluruh
            program kerja HMPS RPL.
          </p>
        </div>

        <div
          v-if="leaders.length"
          class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-7 xl:grid-cols-3 lg:gap-8"
        >
          <article
            v-for="(leader, index) in leaders"
            :key="leader.name || index"
            :data-aos="aosAnimation('fade-up', 'fade-up')"
            :data-aos-delay="aosDelay(index, 75, 300)"
            :data-aos-duration="aosDuration(720, 520)"
            :data-aos-offset="aosOffset(74, 34)"
            data-aos-easing="ease-out-cubic"
            class="group overflow-hidden rounded-[1.35rem] border border-slate-900/5 bg-white text-left shadow-[0_12px_34px_rgba(2,6,23,0.06)] outline-none transition-all duration-300 hover:-translate-y-2 hover:border-red-500/20 hover:shadow-[0_30px_75px_rgba(2,6,23,0.14)] sm:rounded-[1.6rem]"
          >
            <button
              type="button"
              class="block w-full text-left focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/15"
              @click="
                openPersonDetail(
                  formatPersonPayload({
                    name: leader.name,
                    role: normalizeRoleLabel(leader.role),
                    photo: getPhoto(leader),
                    description: getCoreTaskDescription(leader),
                  })
                )
              "
            >
              <div
                class="relative h-52 overflow-hidden bg-slate-100 min-[390px]:h-56 sm:h-72"
              >
                <img
                  :src="getPhoto(leader)"
                  :srcset="getPhotoSrcset(leader) || null"
                  sizes="(max-width: 640px) 92vw, (max-width: 1024px) 44vw, 360px"
                  :alt="leader.name || 'Pengurus HMPS RPL'"
                  width="760"
                  height="950"
                  :loading="index === 0 ? 'eager' : 'lazy'"
                  :fetchpriority="index === 0 ? 'high' : 'low'"
                  decoding="async"
                  class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div
                  class="absolute inset-0 bg-[linear-gradient(to_top,rgba(6,11,22,0.76),rgba(6,11,22,0.12)_58%,transparent)]"
                  aria-hidden="true"
                />
                <span
                  class="absolute left-4 top-4 inline-flex max-w-[calc(100%-2rem)] rounded-full bg-red-600 px-3 py-1.5 text-[0.66rem] font-extrabold uppercase tracking-[0.06em] text-white shadow-[0_12px_24px_rgba(2,6,23,0.16)] sm:text-[0.68rem]"
                >
                  {{ normalizeRoleLabel(leader.role) }}
                </span>
              </div>

              <div class="p-4 min-[390px]:p-5 sm:p-6">
                <h3
                  class="line-clamp-2 text-lg font-black leading-snug tracking-[-0.02em] text-slate-950 sm:text-2xl"
                >
                  {{ leader.name || "Nama Pengurus" }}
                </h3>

                <p
                  class="management-copy mt-3 line-clamp-3 text-sm leading-7 text-slate-500"
                >
                  {{ getCoreTaskDescription(leader) }}
                </p>

                <span
                  class="mt-5 inline-flex min-h-[46px] w-full touch-manipulation items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white transition-all duration-300 group-hover:-translate-y-[2px] group-hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:scale-[0.98] group-hover:shadow-[0_18px_36px_rgba(220,38,38,0.22)]"
                >
                  Lihat Detail
                  <svg
                    class="h-4 w-4 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M17 8l4 4m0 0-4 4m4-4H3"
                    />
                  </svg>
                </span>
              </div>
            </button>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500 shadow-[0_14px_36px_rgba(2,6,23,0.04)]"
        >
          Data pengurus inti belum tersedia.
        </div>
      </div>
    </section>

    <!-- Divisi -->
    <section
      id="divisi"
      class="cv-auto scroll-mt-24 bg-[linear-gradient(180deg,#fff_0%,#fafafa_100%)] py-12 sm:py-20 lg:py-28"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-8 max-w-3xl sm:mb-14 lg:mb-16"
          :data-aos="aosAnimation('fade-up', 'fade-up')"
          :data-aos-duration="aosDuration(760, 520)"
          :data-aos-offset="aosOffset(84, 38)"
          data-aos-easing="ease-out-cubic"
        >
          <div
            class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700 sm:mb-5"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" aria-hidden="true" />
            Divisi Organisasi
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            Susunan anggota
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              per divisi
            </span>
          </h2>

          <p
            class="management-copy mt-3 max-w-2xl text-[0.94rem] leading-[1.78] text-slate-500 sm:mt-4 sm:text-[1rem] lg:text-[1.02rem]"
          >
            Setiap divisi memiliki peran strategis dalam mendukung jalannya organisasi,
            mulai dari perencanaan, koordinasi, dokumentasi, pengembangan mahasiswa,
            hingga pelaksanaan program kerja.
          </p>
        </div>

        <div v-if="divisions.length" class="space-y-6 sm:space-y-10">
          <article
            v-for="(division, index) in divisions"
            :key="division.name || index"
            :data-aos="aosAnimation('fade-up', 'fade-up')"
            :data-aos-delay="aosDelay(index, 70, 280)"
            :data-aos-duration="aosDuration(740, 520)"
            :data-aos-offset="aosOffset(78, 34)"
            data-aos-easing="ease-out-cubic"
            class="overflow-hidden rounded-[1.55rem] border border-slate-900/5 bg-white shadow-[0_14px_40px_rgba(2,6,23,0.06)] transition-all duration-300 hover:shadow-[0_28px_70px_rgba(2,6,23,0.11)] sm:rounded-[1.85rem]"
          >
            <div
              class="h-1.5 w-full bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
              aria-hidden="true"
            />

            <div class="p-4 min-[390px]:p-5 sm:p-7 lg:p-8">
              <div
                class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
              >
                <div class="max-w-3xl">
                  <div
                    class="inline-flex items-center rounded-full bg-red-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-red-700 ring-1 ring-red-100 sm:text-[11px]"
                  >
                    {{ division.short || "Divisi" }}
                  </div>

                  <h3
                    class="mt-4 text-[1.45rem] font-black leading-tight tracking-[-0.035em] text-slate-950 sm:text-3xl"
                  >
                    {{ division.name || "Nama Divisi" }}
                  </h3>

                  <p
                    class="management-copy mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                  >
                    {{ division.description || "Deskripsi divisi belum tersedia." }}
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-2.5 sm:w-fit sm:gap-3">
                  <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center"
                  >
                    <p
                      class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500"
                    >
                      Koordinator
                    </p>
                    <p class="mt-1 text-xl font-black text-slate-950">
                      {{ division.coordinator ? 1 : 0 }}
                    </p>
                  </div>

                  <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center"
                  >
                    <p
                      class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500"
                    >
                      Anggota
                    </p>
                    <p class="mt-1 text-xl font-black text-slate-950">
                      {{ getDivisionMembers(division).length }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Koordinator -->
              <div class="mt-7 sm:mt-8">
                <div class="mb-4 flex items-center justify-between">
                  <h4
                    class="text-[0.72rem] font-extrabold uppercase tracking-[0.12em] text-slate-500 sm:text-sm"
                  >
                    Koordinator Divisi
                  </h4>
                </div>

                <div
                  v-if="division.coordinator"
                  :data-aos="aosAnimation('fade-up', 'fade-up')"
                  :data-aos-delay="aosDelay(1, 55, 110)"
                  :data-aos-duration="aosDuration(660, 500)"
                  :data-aos-offset="aosOffset(62, 28)"
                  data-aos-easing="ease-out-cubic"
                  class="overflow-hidden rounded-[1.35rem] border border-red-100 bg-gradient-to-br from-red-50 via-white to-white shadow-[0_14px_40px_rgba(2,6,23,0.04)] sm:rounded-[1.55rem]"
                >
                  <article
                    class="grid cursor-pointer grid-cols-1 lg:grid-cols-[280px_minmax(0,1fr)]"
                    role="button"
                    tabindex="0"
                    @click="
                      openPersonDetail(
                        formatPersonPayload({
                          name: division.coordinator.name,
                          role: `Koordinator ${division.short || ''}`,
                          division: division.name,
                          photo: getPhoto(division.coordinator),
                          description: `Koordinator divisi ${
                            division.short || division.name
                          } yang bertugas mengarahkan jalannya program dan koordinasi internal divisi.`,
                        })
                      )
                    "
                    @keyup.enter="
                      openPersonDetail(
                        formatPersonPayload({
                          name: division.coordinator.name,
                          role: `Koordinator ${division.short || ''}`,
                          division: division.name,
                          photo: getPhoto(division.coordinator),
                          description: `Koordinator divisi ${
                            division.short || division.name
                          } yang bertugas mengarahkan jalannya program dan koordinasi internal divisi.`,
                        })
                      )
                    "
                  >
                    <div
                      class="relative h-56 overflow-hidden bg-slate-100 min-[390px]:h-64 sm:h-80 lg:h-full lg:min-h-[320px]"
                    >
                      <img
                        :src="
                          getPhoto(
                            division.coordinator,
                            'https://i.pravatar.cc/600?img=20'
                          )
                        "
                        :srcset="
                          getPhotoSrcset(
                            division.coordinator,
                            'https://i.pravatar.cc/600?img=20'
                          ) || null
                        "
                        sizes="(max-width: 640px) 92vw, (max-width: 1024px) 80vw, 280px"
                        :alt="division.coordinator.name || 'Koordinator Divisi HMPS RPL'"
                        width="760"
                        height="950"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                        class="h-full w-full object-cover transition duration-700 hover:scale-[1.04]"
                      />
                      <div
                        class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/35 to-transparent"
                        aria-hidden="true"
                      />
                    </div>

                    <div class="flex flex-col justify-center p-5 sm:p-8 lg:p-10">
                      <div
                        class="inline-flex w-fit items-center rounded-full bg-red-600 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-white shadow-sm sm:text-[11px]"
                      >
                        Koordinator Divisi
                      </div>

                      <h4
                        class="mt-4 text-[1.35rem] font-black tracking-[-0.03em] text-slate-950 sm:text-3xl"
                      >
                        {{ division.coordinator.name || "Nama Koordinator" }}
                      </h4>

                      <p
                        class="mt-2 text-xs font-semibold uppercase tracking-[0.12em] text-red-700 sm:text-sm"
                      >
                        {{ division.short }} • {{ division.name }}
                      </p>

                      <p
                        class="management-copy mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                      >
                        Koordinator divisi {{ division.short || division.name }} yang
                        bertugas mengarahkan jalannya program, menjaga koordinasi
                        internal, dan memastikan setiap agenda divisi berjalan secara
                        efektif.
                      </p>

                      <div
                        class="mt-5 inline-flex min-h-[46px] w-full items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_18px_36px_rgba(220,38,38,0.22)] sm:w-fit"
                      >
                        Lihat Detail Profil
                        <svg
                          class="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                          aria-hidden="true"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.4"
                            d="M17 8l4 4m0 0-4 4m4-4H3"
                          />
                        </svg>
                      </div>
                    </div>
                  </article>
                </div>

                <div
                  v-else
                  class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-4 text-sm text-slate-500"
                >
                  Koordinator belum tersedia.
                </div>
              </div>

              <!-- Anggota -->
              <div class="mt-8 sm:mt-10">
                <div class="mb-4 flex items-center justify-between gap-4">
                  <h4
                    class="text-[0.72rem] font-extrabold uppercase tracking-[0.12em] text-slate-500 sm:text-sm"
                  >
                    Anggota Divisi
                  </h4>

                  <span
                    class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"
                  >
                    {{ getDivisionMembers(division).length }} anggota
                  </span>
                </div>

                <div
                  v-if="getDivisionMembers(division).length"
                  class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-5 xl:grid-cols-4"
                >
                  <article
                    v-for="(member, memberIndex) in getDivisionMembers(division)"
                    :key="member.name"
                    :data-aos="aosAnimation('fade-up', 'fade-up')"
                    :data-aos-delay="aosDelay(memberIndex % 4, 42, 126)"
                    :data-aos-duration="aosDuration(620, 460)"
                    :data-aos-offset="aosOffset(56, 24)"
                    data-aos-easing="ease-out-cubic"
                    class="group overflow-hidden rounded-[1.1rem] border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_18px_40px_rgba(15,23,42,0.10)] sm:rounded-[1.5rem]"
                  >
                    <button
                      type="button"
                      class="block w-full text-left focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/15"
                      @click="
                        openPersonDetail(
                          formatPersonPayload({
                            name: member.name,
                            role: `Anggota ${division.short || ''}`,
                            division: division.name,
                            photo: getPhoto(member),
                            description: `Anggota divisi ${
                              division.short || division.name
                            } yang mendukung pelaksanaan program kerja dan kegiatan organisasi.`,
                          })
                        )
                      "
                    >
                      <div class="relative overflow-hidden bg-slate-100">
                        <img
                          :src="getPhoto(member, 'https://i.pravatar.cc/500?img=40')"
                          :srcset="
                            getPhotoSrcset(member, 'https://i.pravatar.cc/500?img=40') ||
                            null
                          "
                          sizes="(max-width: 640px) 46vw, (max-width: 1024px) 44vw, 260px"
                          :alt="member.name || 'Anggota HMPS RPL'"
                          width="520"
                          height="650"
                          loading="lazy"
                          fetchpriority="low"
                          decoding="async"
                          class="h-40 w-full object-cover transition duration-700 group-hover:scale-[1.04] min-[390px]:h-44 sm:h-72"
                        />
                        <div
                          class="absolute inset-x-0 top-0 h-1 bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                          aria-hidden="true"
                        />
                      </div>

                      <div class="p-3.5 sm:p-5">
                        <span
                          class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[0.6rem] font-extrabold uppercase tracking-[0.12em] text-slate-700 sm:px-3 sm:text-[11px]"
                        >
                          Anggota
                        </span>

                        <h5
                          class="mt-3 line-clamp-2 text-[0.95rem] font-black leading-snug tracking-[-0.02em] text-slate-950 sm:text-lg"
                        >
                          {{ member.name || "Nama Anggota" }}
                        </h5>

                        <p
                          class="mt-2 hidden text-sm leading-7 text-slate-500 sm:line-clamp-3 sm:block sm:text-justify"
                        >
                          Mendukung pelaksanaan program kerja dan kegiatan organisasi pada
                          divisi {{ division.short || division.name }}.
                        </p>

                        <div
                          class="mt-3 inline-flex items-center gap-1.5 text-[0.72rem] font-extrabold text-red-700 sm:mt-4 sm:text-sm"
                        >
                          Detail
                          <svg
                            class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2.4"
                              d="M9 5l7 7-7 7"
                            />
                          </svg>
                        </div>
                      </div>
                    </button>
                  </article>
                </div>

                <div
                  v-else
                  class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-4 text-sm text-slate-500"
                >
                  Belum ada anggota pada divisi ini.
                </div>
              </div>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white px-6 py-10 text-center shadow-[0_14px_36px_rgba(2,6,23,0.04)]"
        >
          <p class="text-base font-semibold text-slate-700">
            Data divisi belum tersedia.
          </p>
          <p
            class="management-copy mx-auto mt-2 max-w-xl text-sm leading-7 text-slate-500"
          >
            Silakan tambahkan data divisi untuk menampilkan susunan anggota.
          </p>
        </div>
      </div>
    </section>

    <!-- Closing -->
    <section id="closing" class="cv-auto bg-white py-12 sm:py-20">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          :data-aos="aosAnimation('zoom-in-up', 'fade-up')"
          :data-aos-duration="aosDuration(780, 540)"
          :data-aos-offset="aosOffset(86, 38)"
          data-aos-easing="ease-out-cubic"
          class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-[linear-gradient(135deg,#0f172a,#111827_55%,#1e293b)] px-5 py-7 text-white shadow-[0_20px_60px_rgba(2,6,23,0.16)] sm:rounded-[2rem] sm:px-8 sm:py-10 lg:px-12 lg:py-12"
        >
          <div
            class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-red-500/20 blur-[70px]"
            aria-hidden="true"
          />

          <div class="relative z-10 max-w-3xl">
            <div
              class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-red-200 sm:text-[11px]"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-red-400" aria-hidden="true" />
              HMPS RPL
            </div>

            <h3 class="text-2xl font-black tracking-[-0.04em] sm:text-3xl lg:text-4xl">
              Bersama membangun organisasi yang
              <span class="text-red-400">aktif, profesional, dan berdampak</span>
            </h3>

            <p
              class="management-copy mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base sm:leading-8"
            >
              Seluruh pengurus dan anggota HMPS RPL memiliki peran penting dalam menjaga
              semangat kolaborasi, pelayanan, dan pengembangan mahasiswa Rekayasa
              Perangkat Lunak.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Detail -->
    <Teleport to="body">
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="selectedPerson"
          class="fixed inset-0 z-[999] flex items-end justify-center bg-slate-950/70 px-3 pb-3 pt-14 backdrop-blur-sm sm:items-center sm:p-6"
          role="dialog"
          aria-modal="true"
          @click.self="closePersonDetail"
        >
          <div
            class="relative max-h-[88svh] w-full max-w-4xl overflow-hidden rounded-t-[1.75rem] bg-white shadow-[0_30px_100px_rgba(2,6,23,0.35)] ring-1 ring-white/30 sm:rounded-[2rem]"
          >
            <button
              type="button"
              aria-label="Tutup detail"
              @click="closePersonDetail"
              class="absolute right-3 top-3 z-20 inline-flex h-11 w-11 touch-manipulation items-center justify-center rounded-2xl bg-white/95 text-slate-700 shadow-[0_12px_28px_rgba(2,6,23,0.14)] backdrop-blur transition hover:-translate-y-0.5 hover:bg-red-50 hover:text-red-700 active:scale-95 focus:outline-none focus:ring-4 focus:ring-red-500/15 sm:right-4 sm:top-4"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.4"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>

            <div
              class="grid max-h-[88svh] grid-cols-1 overflow-y-auto overscroll-contain lg:grid-cols-[1.02fr_.98fr]"
            >
              <div class="bg-slate-100">
                <img
                  :src="getPhoto(selectedPerson.photo)"
                  :srcset="getPhotoSrcset(selectedPerson.photo) || null"
                  sizes="(max-width: 1024px) 92vw, 460px"
                  :alt="selectedPerson.name"
                  width="760"
                  height="950"
                  class="h-[20rem] w-full object-cover sm:h-[28rem] lg:h-full"
                  loading="lazy"
                  fetchpriority="low"
                  decoding="async"
                />
              </div>

              <div class="flex flex-col justify-center p-5 sm:p-8 lg:p-10">
                <span
                  class="inline-flex w-fit rounded-full bg-red-50 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-red-700"
                >
                  {{ selectedPerson.role }}
                </span>

                <h3
                  class="mt-4 text-2xl font-black leading-tight tracking-[-0.03em] text-slate-950 sm:text-4xl"
                >
                  {{ selectedPerson.name }}
                </h3>

                <p
                  v-if="selectedPerson.division"
                  class="mt-3 text-sm font-bold uppercase tracking-[0.12em] text-slate-500"
                >
                  {{ selectedPerson.division }}
                </p>

                <p
                  class="management-copy mt-5 text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                >
                  {{
                    selectedPerson.description ||
                    "Anggota kepengurusan HMPS Rekayasa Perangkat Lunak."
                  }}
                </p>

                <div class="mt-7 flex flex-wrap gap-3 sm:mt-8">
                  <button
                    type="button"
                    @click="closePersonDetail"
                    class="group relative inline-flex min-h-[50px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_10px_24px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_18px_34px_rgba(220,38,38,0.24)] active:scale-[0.98] sm:w-auto"
                  >
                    <span
                      class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.18),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
                      aria-hidden="true"
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
.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 900px;
}

.management-copy {
  text-align: justify;
  text-justify: inter-word;
}

.kepengurusan-page :is(a, button, input, select, textarea):focus-visible {
  outline: 3px solid rgba(239, 68, 68, 0.35);
  outline-offset: 3px;
}

@media (max-width: 640px) {
  .management-copy {
    text-align: left;
  }
}

.management-hero-action {
  background: linear-gradient(135deg, #081120, #0f172a) !important;
  color: #ffffff !important;
}

.management-hero-action:hover,
.management-hero-action:focus-visible,
.management-hero-action:active {
  background: linear-gradient(135deg, #ef4444, #dc2626, #991b1b) !important;
  color: #ffffff !important;
}

.management-hero-action:focus-visible {
  outline: 2px solid rgba(239, 68, 68, 0.45);
  outline-offset: 3px;
}

:deep(img) {
  display: block;
  max-width: 100%;
}

:deep(a),
:deep(button) {
  -webkit-tap-highlight-color: transparent;
}

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

  .management-hero-shine {
    display: none;
  }

  :deep([data-aos]),
  :deep([data-aos].aos-animate) {
    opacity: 1 !important;
    transform: none !important;
    transition: none !important;
  }

  * {
    scroll-behavior: auto !important;
  }
}
</style>

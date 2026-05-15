<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  homeHero: {
    type: Object,
    default: () => ({}),
  },
  homeAbout: {
    type: Object,
    default: () => ({}),
  },
  homeProgram: {
    type: Object,
    default: () => ({}),
  },
  homeGallerySection: {
    type: Object,
    default: () => ({}),
  },
  heroImages: {
    type: Array,
    default: () => [],
  },
  gallery: {
    type: Array,
    default: () => [],
  },
  ticks: {
    type: Array,
    default: () => [],
  },
  highlights: {
    type: Array,
    default: () => [],
  },
  prokers: {
    type: Array,
    default: () => [],
  },
});

const fallbackHero = {
  badge: "Organisasi Mahasiswa Rekayasa Perangkat Lunak",
  titleHighlight: "HMPS",
  title: "Rekayasa Perangkat Lunak",
  description:
    "Wadah aspirasi, kolaborasi, pengembangan skill, dan program kerja mahasiswa RPL yang aktif, kreatif, dan profesional.",
  primaryButtonLabel: "Lihat Program Kerja",
  primaryButtonUrl: "#proker",
  secondaryButtonLabel: "Konsultasi Gratis",
  secondaryButtonUrl: "/konsultasi",
  meta: {
    top_badge_title: "Aktif & Produktif",
    top_badge_subtitle: "Bergerak dalam kolaborasi",
    bottom_badge_title: "Kegiatan Berkelanjutan",
    bottom_badge_subtitle: "Satu periode penuh",
  },
};

const fallbackAbout = {
  badge: "Tentang Kami",
  title: "Sekilas tentang",
  titleHighlight: "HMPS RPL",
  description:
    "HMPS Rekayasa Perangkat Lunak merupakan organisasi mahasiswa yang menjadi wadah aspirasi, pengembangan potensi, dan kolaborasi mahasiswa RPL. Melalui berbagai kegiatan, HMPS RPL berupaya membangun lingkungan yang aktif, kreatif, dan profesional.",
  primaryButtonLabel: "Lihat Profil Lengkap",
  primaryButtonUrl: "/profil",
  meta: {
    highlight_title:
      "Organisasi mahasiswa yang fokus pada pengembangan, kebersamaan, dan kontribusi nyata.",
  },
};

const fallbackProgram = {
  badge: "Agenda Kami",
  title: "Program Kerja",
  titleHighlight: "Unggulan",
  description:
    "Berbagai kegiatan yang dirancang untuk memperkuat kompetensi, solidaritas, dan pengalaman mahasiswa Rekayasa Perangkat Lunak.",
  primaryButtonLabel: "Lihat Semua Program",
  primaryButtonUrl: "/program-kerja",
};

const fallbackGallerySection = {
  badge: "Dokumentasi Kegiatan",
  title: "Galeri",
  titleHighlight: "HMPS RPL",
  description:
    "Potret momen, kebersamaan, dan aktivitas yang menunjukkan semangat HMPS RPL dalam bergerak dan berkarya.",
  primaryButtonLabel: "Lihat Semua Dokumentasi",
  primaryButtonUrl: "/dokumentasi",
};

const fallbackHeroImages = [
  {
    id: "fallback-1",
    image:
      "https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=900&h=650&fit=crop&auto=format",
    alt: "Kolaborasi tim HMPS RPL",
  },
  {
    id: "fallback-2",
    image:
      "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=700&h=900&fit=crop&auto=format",
    alt: "Diskusi kegiatan mahasiswa",
  },
  {
    id: "fallback-3",
    image:
      "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=700&h=900&fit=crop&auto=format",
    alt: "Rapat dan koordinasi HMPS",
  },
  {
    id: "fallback-4",
    image:
      "https://images.unsplash.com/photo-1552664730-d307ca884978?w=900&h=650&fit=crop&auto=format",
    alt: "Workshop dan pengembangan skill",
  },
];

const fallbackGalleryImage =
  "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200&h=800&fit=crop&auto=format";


const IMAGE_WIDTHS = {
  hero: [320, 480, 640, 820],
  card: [320, 480, 720],
  gallery: [280, 420, 640, 900],
};

const INVALID_URL_VALUES = new Set([
  "",
  "null",
  "undefined",
  "false",
  "true",
  "nan",
  "#",
]);

const transparentPixel =
  "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";

const desktopHeroSizes =
  "(min-width: 1280px) 300px, (min-width: 1024px) 260px, 50vw";

const normalizeUrl = (value, fallback = "") => {
  if (value === null || value === undefined) return fallback;

  const text = String(value).trim();
  if (!text || INVALID_URL_VALUES.has(text.toLowerCase())) return fallback;

  return text;
};

const safeHref = (value, fallback = "/") => {
  const href = normalizeUrl(value, "");

  if (!href || /^javascript:/i.test(href)) return fallback;

  return href;
};

const isAbsoluteUrl = (value) => /^https?:\/\//i.test(normalizeUrl(value));

const isUnsplashUrl = (value) => {
  const source = normalizeUrl(value);
  if (!isAbsoluteUrl(source)) return false;

  try {
    return new URL(source).hostname.includes("images.unsplash.com");
  } catch (error) {
    return false;
  }
};

const optimizedImageUrl = (value, width = 760, quality = 72) => {
  const source = normalizeUrl(value);

  if (!source) return fallbackGalleryImage;

  if (!isUnsplashUrl(source)) return source;

  try {
    const url = new URL(source);
    url.searchParams.set("auto", "format");
    url.searchParams.set("fit", "crop");
    url.searchParams.set("q", String(quality));
    url.searchParams.set("w", String(width));
    return url.toString();
  } catch (error) {
    return source;
  }
};

const explicitSrcsetFromItem = (item) => {
  if (!item) return "";

  const directSrcset = normalizeUrl(item.srcset || item.image_srcset);
  if (directSrcset) return directSrcset;

  const variants = [
    [item.image_xs || item.thumb_url || item.thumbnail, 320],
    [item.image_sm || item.small_url, 480],
    [item.image_md || item.medium_url, 720],
    [item.image_lg || item.large_url, 960],
  ]
    .map(([url, width]) => [normalizeUrl(url), width])
    .filter(([url]) => Boolean(url));

  return variants.map(([url, width]) => `${url} ${width}w`).join(", ");
};

const buildSrcset = (item, fallback, widths = IMAGE_WIDTHS.card) => {
  const explicit = explicitSrcsetFromItem(item);
  if (explicit) return explicit;

  const source = pickImage(item, fallback);
  if (!isUnsplashUrl(source)) return "";

  return widths
    .map((width) => `${optimizedImageUrl(source, width, width <= 480 ? 66 : 72)} ${width}w`)
    .join(", ");
};

const fallbackTicks = [
  "Aspiratif",
  "Kolaboratif",
  "Produktif",
  "Kreatif",
  "Profesional",
  "Inovatif",
];

const fallbackHighlights = [
  {
    id: "fallback-highlight-1",
    title: "Ruang aspirasi mahasiswa",
    desc:
      "Menjadi jembatan komunikasi antara mahasiswa, program studi, dan lingkungan organisasi.",
  },
  {
    id: "fallback-highlight-2",
    title: "Pengembangan kompetensi",
    desc:
      "Mendorong peningkatan skill teknis, kreativitas, kepemimpinan, dan kerja sama tim.",
  },
  {
    id: "fallback-highlight-3",
    title: "Kegiatan yang berdampak",
    desc: "Menghadirkan program kerja yang relevan dengan kebutuhan mahasiswa RPL.",
  },
];

const mergeSection = (section, fallback) => ({
  ...fallback,
  ...(section || {}),
  meta: {
    ...(fallback.meta || {}),
    ...((section && section.meta) || {}),
  },
});

const pickImage = (item, fallback = fallbackGalleryImage) => {
  if (!item) return fallback;

  return (
    normalizeUrl(item.image) ||
    normalizeUrl(item.image_url) ||
    normalizeUrl(item.cover_url) ||
    normalizeUrl(item.cover) ||
    normalizeUrl(item.img) ||
    fallback
  );
};

const normalizeText = (value, fallback = "") => {
  if (typeof value === "string") return value.trim() || fallback;
  if (typeof value === "number") return String(value);
  return fallback;
};

const isHashUrl = (url) => normalizeUrl(url).startsWith("#");
const resolveHashId = (url) =>
  normalizeUrl(url, "#proker")
    .replace(/^#/, "")
    .trim();

const homeHero = computed(() => mergeSection(props.homeHero, fallbackHero));
const homeAbout = computed(() => mergeSection(props.homeAbout, fallbackAbout));
const homeProgram = computed(() => mergeSection(props.homeProgram, fallbackProgram));
const homeGallerySection = computed(() =>
  mergeSection(props.homeGallerySection, fallbackGallerySection)
);

const heroImages = computed(() => {
  const sourceImages = Array.isArray(props.heroImages) ? props.heroImages : [];
  const mixedImages = [...sourceImages, ...fallbackHeroImages].slice(0, 4);

  return mixedImages.map((item, index) => {
    const fallback = fallbackHeroImages[index]?.image;
    const rawImage = pickImage(item, fallback);

    return {
      ...fallbackHeroImages[index],
      ...(item || {}),
      image: optimizedImageUrl(rawImage, index === 0 ? 640 : 520, index === 0 ? 72 : 68),
      srcset: buildSrcset(item, fallback, IMAGE_WIDTHS.hero),
      alt: item?.alt || item?.alt_text || item?.title || fallbackHeroImages[index]?.alt,
    };
  });
});

const primaryHeroImage = computed(() => normalizeUrl(heroImages.value[0]?.image));
const primaryHeroSrcset = computed(() => normalizeUrl(heroImages.value[0]?.srcset));
const getPictureSrcset = (item) => normalizeUrl(item?.srcset) || normalizeUrl(item?.image);
const seoDescription = computed(() =>
  normalizeText(
    homeHero.value.description,
    "Website resmi HMPS Rekayasa Perangkat Lunak sebagai pusat informasi profil, program kerja, dokumentasi, dan layanan konsultasi mahasiswa RPL."
  ).slice(0, 155)
);
const heroQuickPoints = computed(() =>
  highlightItems.value.slice(0, 3).map((item, index) => ({
    ...item,
    number: String(index + 1).padStart(2, "0"),
  }))
);

const tickerItems = computed(() => {
  const items =
    Array.isArray(props.ticks) && props.ticks.length ? props.ticks : fallbackTicks;

  return items
    .map((item) => {
      if (typeof item === "string") return item;

      return item?.title || item?.label || item?.text || item?.name || "";
    })
    .filter(Boolean);
});

const highlightItems = computed(() => {
  const items =
    Array.isArray(props.highlights) && props.highlights.length
      ? props.highlights.slice(0, 3)
      : fallbackHighlights;

  return items.map((item, index) => ({
    id: item?.id || `highlight-${index}`,
    title: normalizeText(
      item?.title,
      fallbackHighlights[index]?.title || "Highlight HMPS RPL"
    ),
    desc: normalizeText(
      item?.desc || item?.description || item?.body,
      fallbackHighlights[index]?.desc ||
        "Informasi highlight organisasi akan ditampilkan di sini."
    ),
  }));
});

const prokers = computed(() => (Array.isArray(props.prokers) ? props.prokers : []));
const displayedProkers = computed(() => prokers.value.slice(0, 3));

const gallery = computed(() => (Array.isArray(props.gallery) ? props.gallery : []));
const displayedGallery = computed(() => gallery.value.slice(0, 6));

const homeStats = computed(() => [
  {
    value: prokers.value.length || "—",
    label: "Program",
    desc: "Ditampilkan",
  },
  {
    value: gallery.value.length || "—",
    label: "Dokumentasi",
    desc: "Kegiatan",
  },
  {
    value: highlightItems.value.length,
    label: "Fokus",
    desc: "Pengembangan",
  },
]);

const getGalleryImage = (item) => optimizedImageUrl(pickImage(item), 720, 70);
const getGallerySrcset = (item) => buildSrcset(item, fallbackGalleryImage, IMAGE_WIDTHS.gallery);
const getGalleryCategory = (item) => item?.category || item?.tag || "Dokumentasi";
const getGalleryAlt = (item) =>
  item?.alt || item?.alt_text || item?.title || "Dokumentasi HMPS RPL";

const safeTagClassMap = {
  "bg-slate-900/90": "bg-slate-900/90",
  "bg-red-500": "bg-red-500",
  "bg-red-600": "bg-red-600",
  "bg-red-700": "bg-red-700",
  "bg-rose-500": "bg-rose-500",
  "bg-rose-600": "bg-rose-600",
  "bg-rose-700": "bg-rose-700",
  "bg-blue-500": "bg-blue-500",
  "bg-blue-600": "bg-blue-600",
  "bg-blue-700": "bg-blue-700",
  "bg-sky-500": "bg-sky-500",
  "bg-sky-600": "bg-sky-600",
  "bg-cyan-500": "bg-cyan-500",
  "bg-cyan-600": "bg-cyan-600",
  "bg-emerald-500": "bg-emerald-500",
  "bg-emerald-600": "bg-emerald-600",
  "bg-green-500": "bg-green-500",
  "bg-green-600": "bg-green-600",
  "bg-teal-500": "bg-teal-500",
  "bg-teal-600": "bg-teal-600",
  "bg-amber-500": "bg-amber-500",
  "bg-amber-600": "bg-amber-600",
  "bg-orange-500": "bg-orange-500",
  "bg-orange-600": "bg-orange-600",
  "bg-indigo-500": "bg-indigo-500",
  "bg-indigo-600": "bg-indigo-600",
  "bg-violet-500": "bg-violet-500",
  "bg-violet-600": "bg-violet-600",
  "bg-purple-500": "bg-purple-500",
  "bg-purple-600": "bg-purple-600",
  "bg-fuchsia-500": "bg-fuchsia-500",
  "bg-fuchsia-600": "bg-fuchsia-600",
  "bg-pink-500": "bg-pink-500",
  "bg-pink-600": "bg-pink-600",
};

const tagToneClassMap = {
  unggulan: "bg-sky-500",
  pengabdian: "bg-emerald-500",
  pendukung: "bg-violet-600",
  kompetisi: "bg-sky-500",
  edukasi: "bg-emerald-500",
  karir: "bg-violet-600",
  workshop: "bg-blue-600",
  seminar: "bg-purple-600",
  sosial: "bg-emerald-600",
  internal: "bg-slate-900/90",
};

const selectedProker = ref(null);

const getProkerImage = (item) => optimizedImageUrl(pickImage(item), 720, 70);
const getProkerSrcset = (item) => buildSrcset(item, fallbackGalleryImage, IMAGE_WIDTHS.card);
const getProkerTag = (item) => item?.tag || item?.category || "Program Kerja";
const getProkerDate = (item) =>
  item?.date || item?.periode || item?.schedule || "Terjadwal";
const getProkerDivision = (item) =>
  item?.division || item?.divisi || item?.division_name || "HMPS RPL";
const getProkerLocation = (item) =>
  item?.location || item?.lokasi || item?.place || "Menyesuaikan";
const getProkerBudget = (item) =>
  item?.budgetText || item?.budget_text || item?.budget || "Menyesuaikan";
const getProkerDescription = (item) =>
  normalizeText(
    item?.desc || item?.description || item?.body || item?.summary,
    "Informasi program kerja HMPS RPL akan ditampilkan secara berkala melalui halaman ini."
  );

const normalizeList = (value) => {
  if (Array.isArray(value)) {
    return value.map((item) => normalizeText(item)).filter(Boolean);
  }

  if (typeof value === "string") {
    return value
      .split(/\r?\n|,/)
      .map((item) => item.trim())
      .filter(Boolean);
  }

  return [];
};

const getProkerGoals = (item) => normalizeList(item?.goals || item?.tujuan);
const getProkerBenefits = (item) => normalizeList(item?.benefits || item?.manfaat);

const getProkerTagClass = (item) => {
  const className = normalizeText(item?.tagClass || item?.tag_class);

  if (safeTagClassMap[className]) {
    return safeTagClassMap[className];
  }

  const tagName = normalizeText(getProkerTag(item)).toLowerCase();
  const matchedTone = Object.keys(tagToneClassMap).find((keyword) =>
    tagName.includes(keyword)
  );

  return matchedTone ? tagToneClassMap[matchedTone] : "bg-slate-900/90";
};

const openProkerDetail = (item) => {
  selectedProker.value = item;
};

const closeProkerDetail = () => {
  selectedProker.value = null;
};

const handleEscape = (event) => {
  if (event.key === "Escape") closeProkerDetail();
};

watch(selectedProker, (value) => {
  if (typeof document === "undefined") return;

  document.body.style.overflow = value ? "hidden" : "";

  if (value) {
    document.addEventListener("keydown", handleEscape);
  } else {
    document.removeEventListener("keydown", handleEscape);
  }
});

onBeforeUnmount(() => {
  if (typeof document !== "undefined") {
    document.body.style.overflow = "";
    document.removeEventListener("keydown", handleEscape);
  }
});

const galleryCardClass = (item, index) => {
  if (item?.layout === "wide") {
    return "col-span-2 h-40 sm:col-span-2 sm:h-56 lg:h-64";
  }

  if (item?.layout === "large" || index === 0) {
    return "col-span-2 h-52 sm:col-span-2 sm:row-span-2 sm:h-[26rem] lg:h-[30rem]";
  }

  return "col-span-1 h-36 min-[390px]:h-40 sm:h-48 lg:h-[14.25rem]";
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
</script>

<template>
  <Head title="Beranda | HMPS RPL">
    <meta name="description" :content="seoDescription" />
    <meta
      name="keywords"
      content="HMPS RPL, Himpunan Mahasiswa Rekayasa Perangkat Lunak, Politeknik Balekambang, program kerja RPL, dokumentasi HMPS"
    />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="Beranda HMPS Rekayasa Perangkat Lunak" />
    <meta property="og:description" :content="seoDescription" />
    <meta property="og:type" content="website" />
    <link
      v-if="isUnsplashUrl(primaryHeroImage)"
      rel="preconnect"
      href="https://images.unsplash.com"
      crossorigin
    />
    <link
      v-if="isUnsplashUrl(primaryHeroImage)"
      rel="dns-prefetch"
      href="https://images.unsplash.com"
    />
    <link
      v-if="primaryHeroImage"
      rel="preload"
      as="image"
      :href="primaryHeroImage"
      :imagesrcset="primaryHeroSrcset || null"
      :imagesizes="desktopHeroSizes"
      media="(min-width: 768px)"
      fetchpriority="high"
    />
  </Head>

  <div
    class="home-justify-page overflow-x-hidden font-['Plus_Jakarta_Sans',sans-serif] text-slate-950 antialiased"
  >
    <!-- Hero -->
    <section
      id="beranda"
      class="relative overflow-hidden bg-[radial-gradient(circle_at_18%_12%,rgba(239,68,68,0.13),transparent_30%),radial-gradient(circle_at_90%_18%,rgba(153,27,27,0.10),transparent_32%),linear-gradient(180deg,#ffffff_0%,#fff7f7_48%,#ffffff_100%)] pb-8 pt-[5.6rem] sm:pb-14 sm:pt-24 lg:flex lg:min-h-[92svh] lg:items-center lg:pt-32"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:linear-gradient(180deg,rgba(255,255,255,0.92),transparent_88%)] sm:bg-[size:40px_40px]"
      />
      <div
        class="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-red-500/10 blur-[78px] lg:h-96 lg:w-96"
      />
      <div
        class="pointer-events-none absolute -bottom-20 -left-24 h-64 w-64 rounded-full bg-rose-900/10 blur-[78px] lg:h-80 lg:w-80"
      />

      <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="grid grid-cols-1 items-center gap-5 sm:gap-10 lg:grid-cols-2 xl:gap-14"
        >
          <div
            class="rounded-[1.75rem] border border-white/70 bg-white/78 p-3.5 shadow-[0_18px_55px_rgba(15,23,42,0.08)] backdrop-blur-xl sm:p-0 sm:shadow-none sm:backdrop-blur-0 lg:rounded-none lg:border-0 lg:bg-transparent"
          >
            <div
              class="mb-3 inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/90 px-3 py-2 text-[0.62rem] font-black uppercase tracking-[0.08em] text-red-700 shadow-[0_12px_32px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:mb-5 sm:text-[0.75rem]"
            >
              <span
                class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
              />
              <span class="min-w-0 leading-snug line-clamp-2 sm:line-clamp-1">
                {{ homeHero.badge }}
              </span>
            </div>

            <h1
              class="max-w-2xl break-words text-[2rem] font-black leading-[1.02] tracking-[-0.055em] text-slate-950 min-[390px]:text-[2.35rem] sm:text-[3.2rem] md:text-[3.8rem] xl:text-[4.8rem]"
            >
              <span
                class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ homeHero.titleHighlight }}
              </span>
              {{ homeHero.title }}
            </h1>

            <p
              class="mt-3 max-w-2xl text-[0.88rem] leading-[1.72] text-slate-600 min-[390px]:text-[0.92rem] sm:mt-6 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              {{ homeHero.description }}
            </p>

            <div
              class="mt-5 grid grid-cols-1 gap-2.5 min-[390px]:grid-cols-2 sm:mt-8 sm:flex sm:flex-row sm:gap-3"
            >
              <button
                v-if="isHashUrl(homeHero.primaryButtonUrl)"
                type="button"
                @click="scrollToSection(resolveHashId(homeHero.primaryButtonUrl))"
                class="group relative inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-4 text-[0.78rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:w-auto sm:px-6 sm:text-[0.95rem]"
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
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                  />
                </svg>
                <span class="relative z-10 min-w-0 truncate">{{
                  homeHero.primaryButtonLabel
                }}</span>
              </button>

              <Link
                v-else
                :href="safeHref(homeHero.primaryButtonUrl, '/program-kerja')"
                class="group relative inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-4 text-[0.78rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:w-auto sm:px-6 sm:text-[0.95rem]"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />
                <span class="relative z-10 min-w-0 truncate">{{
                  homeHero.primaryButtonLabel
                }}</span>
              </Link>

              <button
                v-if="isHashUrl(homeHero.secondaryButtonUrl)"
                type="button"
                @click="scrollToSection(resolveHashId(homeHero.secondaryButtonUrl))"
                class="group relative inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl border border-white/10 bg-[linear-gradient(135deg,#081120,#0f172a)] px-4 text-[0.78rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:w-auto sm:px-6 sm:text-[0.95rem]"
              >
                <span class="min-w-0 truncate">{{ homeHero.secondaryButtonLabel }}</span>
              </button>

              <Link
                v-else
                :href="safeHref(homeHero.secondaryButtonUrl, '/konsultasi')"
                class="group relative inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl border border-white/10 bg-[linear-gradient(135deg,#081120,#0f172a)] px-4 text-[0.78rem] font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[54px] sm:w-auto sm:px-6 sm:text-[0.95rem]"
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
                <span class="min-w-0 truncate">{{ homeHero.secondaryButtonLabel }}</span>
              </Link>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 sm:mt-8 sm:max-w-xl sm:gap-3">
              <div
                v-for="stat in homeStats"
                :key="stat.label"
                class="rounded-2xl border border-slate-900/5 bg-white/90 p-3 shadow-[0_12px_30px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:p-4"
              >
                <div
                  class="text-base font-black tracking-[-0.04em] text-slate-950 min-[390px]:text-lg sm:text-2xl"
                >
                  {{ stat.value }}
                </div>
                <div
                  class="mt-1 text-[0.56rem] font-black uppercase tracking-[0.07em] text-red-700 min-[390px]:text-[0.62rem] sm:text-[0.72rem]"
                >
                  {{ stat.label }}
                </div>
                <div class="mt-0.5 hidden text-xs font-semibold text-slate-500 sm:block">
                  {{ stat.desc }}
                </div>
              </div>
            </div>

            <div
              class="mt-4 overflow-hidden rounded-[1.45rem] border border-slate-900/[0.06] bg-[linear-gradient(135deg,#0f172a,#172033_64%,#111827)] p-4 text-white shadow-[0_18px_45px_rgba(15,23,42,0.16)] md:hidden"
              aria-label="Ringkasan cepat HMPS RPL"
            >
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[0.64rem] font-black uppercase tracking-[0.13em] text-red-200">
                    Fokus Organisasi
                  </p>
                  <h2 class="mt-1 text-lg font-black tracking-[-0.035em] text-white">
                    Bergerak aktif, rapi, dan berdampak.
                  </h2>
                </div>
                <span
                  class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-xl ring-1 ring-white/15"
                  aria-hidden="true"
                >
                  ✦
                </span>
              </div>

              <div class="mt-4 grid gap-2.5">
                <div
                  v-for="item in heroQuickPoints"
                  :key="item.id || item.title"
                  class="rounded-2xl border border-white/10 bg-white/[0.06] p-3"
                >
                  <div class="flex items-start gap-3">
                    <span
                      class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-red-500 text-[0.68rem] font-black text-white"
                    >
                      {{ item.number }}
                    </span>
                    <div class="min-w-0">
                      <p class="text-sm font-black leading-snug text-white">
                        {{ item.title }}
                      </p>
                      <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-300">
                        {{ item.desc }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="relative mx-auto hidden w-full max-w-[31rem] md:block sm:max-w-[37rem] lg:mx-auto"
            data-aos="fade-up"
            data-aos-delay="120"
            data-aos-duration="800"
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
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-base text-white sm:h-11 sm:w-11 sm:text-lg"
              >
                🏆
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ homeHero.meta?.top_badge_title }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ homeHero.meta?.top_badge_subtitle }}
                </div>
              </div>
            </div>

            <div
              class="relative overflow-hidden rounded-[1.55rem] border border-white/80 bg-white/88 p-2.5 shadow-[0_20px_55px_rgba(2,6,23,0.10)] backdrop-blur-xl sm:rounded-[2rem] sm:p-4 lg:shadow-[0_28px_80px_rgba(2,6,23,0.14)]"
            >
              <div
                class="pointer-events-none absolute inset-0 rounded-[1.55rem] [background:linear-gradient(135deg,rgba(239,68,68,0.22),rgba(255,255,255,0.30),rgba(153,27,27,0.16))] [mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:exclude] p-px sm:rounded-[2rem]"
              />

              <div class="grid grid-cols-2 gap-2 sm:gap-4">
                <div class="flex flex-col gap-2 pt-4 sm:gap-4 sm:pt-10 lg:pt-12">
                  <div
                    class="group h-24 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] min-[390px]:h-28 sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 768px)"
                        :srcset="getPictureSrcset(heroImages[0])"
                        :sizes="desktopHeroSizes"
                      />
                      <img
                        :src="transparentPixel"
                        :alt="heroImages[0]?.alt"
                        width="640"
                        height="460"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                      />
                    </picture>
                  </div>
                  <div
                    class="group h-32 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] min-[390px]:h-40 sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 768px)"
                        :srcset="getPictureSrcset(heroImages[1])"
                        :sizes="desktopHeroSizes"
                      />
                      <img
                        :src="transparentPixel"
                        :alt="heroImages[1]?.alt"
                        width="520"
                        height="680"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                      />
                    </picture>
                  </div>
                </div>

                <div class="flex flex-col gap-2 sm:gap-4">
                  <div
                    class="group h-32 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] min-[390px]:h-40 sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 768px)"
                        :srcset="getPictureSrcset(heroImages[2])"
                        :sizes="desktopHeroSizes"
                      />
                      <img
                        :src="transparentPixel"
                        :alt="heroImages[2]?.alt"
                        width="520"
                        height="680"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                      />
                    </picture>
                  </div>
                  <div
                    class="group h-24 overflow-hidden rounded-[1rem] bg-slate-100 shadow-[0_12px_26px_rgba(2,6,23,0.09)] min-[390px]:h-28 sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 768px)"
                        :srcset="getPictureSrcset(heroImages[3])"
                        :sizes="desktopHeroSizes"
                      />
                      <img
                        :src="transparentPixel"
                        :alt="heroImages[3]?.alt"
                        width="520"
                        height="460"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                      />
                    </picture>
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
                🚀
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ homeHero.meta?.bottom_badge_title }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ homeHero.meta?.bottom_badge_subtitle }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ticker -->
    <div
      class="overflow-hidden border-y border-white/5 bg-[linear-gradient(135deg,#0f172a,#172033_60%,#111827)] py-2.5 sm:py-4"
    >
      <div class="ticker-wrap w-full overflow-hidden">
        <div class="ticker-inner flex w-max">
          <div v-for="dup in 2" :key="dup" class="flex items-center">
            <span
              v-for="item in tickerItems"
              :key="`${dup}-${item}`"
              class="mx-4 inline-flex whitespace-nowrap text-[0.68rem] font-black uppercase tracking-[0.15em] text-white sm:mx-8 sm:text-[0.8rem] sm:tracking-[0.2em]"
            >
              {{ item }}
              <span class="ml-4 text-sm text-red-400/70 sm:ml-5 sm:text-base">✦</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tentang -->
    <section id="tentang" class="cv-auto scroll-mt-24 bg-white py-10 sm:py-20 lg:py-24">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2 lg:gap-12 xl:gap-16"
        >
          <div
            data-aos="fade-up"
            data-aos-duration="800"
            class="rounded-[1.75rem] border border-slate-900/[0.06] bg-white p-4 shadow-[0_14px_40px_rgba(2,6,23,0.05)] sm:rounded-none sm:border-0 sm:p-0 sm:shadow-none"
          >
            <div
              class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 sm:mb-5 sm:text-[0.7rem]"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              {{ homeAbout.badge }}
            </div>

            <h2
              class="max-w-3xl text-[1.65rem] font-black leading-[1.08] tracking-[-0.045em] text-slate-950 min-[390px]:text-[1.9rem] sm:text-[2.35rem] md:text-[2.8rem] xl:text-[3.2rem]"
            >
              {{ homeAbout.title }}
              <span
                class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ homeAbout.titleHighlight }}
              </span>
            </h2>

            <p
              class="mt-3 max-w-3xl text-[0.9rem] leading-[1.78] text-slate-600 sm:mt-5 sm:text-justify sm:text-[1rem] lg:text-[1.04rem] lg:leading-[1.95]"
            >
              {{ homeAbout.description }}
            </p>

            <div class="mt-5 flex flex-col gap-3 sm:mt-8 sm:flex-row sm:items-center">
              <Link
                :href="safeHref(homeAbout.primaryButtonUrl, '/profil')"
                class="group relative inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_12px_28px_rgba(2,6,23,0.16)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_22px_46px_rgba(220,38,38,0.28)] active:scale-[0.98] sm:min-h-[52px] sm:w-auto sm:text-[0.95rem]"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />
                <span class="relative z-10">{{ homeAbout.primaryButtonLabel }}</span>
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
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
              </Link>

              <span
                class="text-center text-[0.66rem] font-black uppercase tracking-[0.13em] text-slate-400 sm:text-left sm:text-xs"
              >
                Bergerak • Bertumbuh • Berdampak
              </span>
            </div>
          </div>

          <div data-aos="fade-up" data-aos-delay="120" data-aos-duration="800">
            <div
              class="relative overflow-hidden rounded-[1.55rem] bg-[linear-gradient(160deg,#0b1220,#111827_58%,#1e1b4b)] p-4 text-white shadow-[0_18px_44px_rgba(2,6,23,0.16)] sm:rounded-[1.75rem] sm:p-6 lg:p-7"
            >
              <div
                class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-red-500/20 blur-[58px]"
              />
              <div
                class="pointer-events-none absolute -bottom-20 -left-16 h-48 w-48 rounded-full bg-white/10 blur-[70px]"
              />

              <div class="relative z-10">
                <div
                  class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-red-100 sm:mb-5 sm:px-4 sm:text-[11px]"
                >
                  Highlight Organisasi
                </div>

                <h3
                  class="max-w-[28ch] text-[1.04rem] font-black leading-[1.42] tracking-[-0.02em] text-white min-[390px]:text-[1.12rem] sm:text-[1.35rem] lg:text-[1.42rem]"
                >
                  {{ homeAbout.meta?.highlight_title }}
                </h3>

                <div class="mt-4 divide-y divide-white/10 sm:mt-5">
                  <div
                    v-for="item in highlightItems"
                    :key="item.id || item.title"
                    class="flex gap-3 py-3 first:pt-0 last:pb-0 sm:py-4"
                  >
                    <span
                      class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-500/15 text-red-200 ring-1 ring-white/10"
                    >
                      <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.4"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </span>
                    <div>
                      <h4
                        class="text-sm font-extrabold leading-snug text-white sm:text-[0.98rem]"
                      >
                        {{ item.title }}
                      </h4>
                      <p
                        class="mt-1 text-xs leading-6 text-slate-300 sm:text-justify sm:text-[0.92rem] sm:leading-7"
                      >
                        {{ item.desc }}
                      </p>
                    </div>
                  </div>
                </div>

                <Link
                  :href="safeHref(homeAbout.primaryButtonUrl, '/profil')"
                  class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-red-100 transition-colors hover:text-white sm:mt-6 sm:text-base"
                >
                  Selengkapnya di halaman Profil
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
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Program Kerja -->
    <section
      id="proker"
      class="cv-auto scroll-mt-24 bg-[linear-gradient(180deg,#fff_0%,#fafafa_100%)] py-10 sm:py-20 lg:py-28"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-6 flex flex-col justify-between gap-4 sm:mb-14 lg:mb-16 md:flex-row md:items-end"
        >
          <div data-aos="fade-up" data-aos-duration="800">
            <div
              class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 sm:mb-5 sm:text-[0.7rem]"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              {{ homeProgram.badge }}
            </div>

            <h2
              class="text-[1.7rem] font-black leading-[1.08] tracking-[-0.045em] text-slate-950 min-[390px]:text-[1.95rem] sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
            >
              {{ homeProgram.title }}
              <span
                class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ homeProgram.titleHighlight }}
              </span>
            </h2>

            <p
              class="mt-3 max-w-2xl text-[0.9rem] leading-[1.76] text-slate-500 sm:mt-4 sm:text-justify sm:text-[1rem] lg:text-[1.02rem]"
            >
              {{ homeProgram.description }}
            </p>
          </div>

          <Link
            :href="safeHref(homeProgram.primaryButtonUrl, '/program-kerja')"
            data-aos="fade-up"
            data-aos-delay="80"
            data-aos-duration="800"
            class="inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 rounded-2xl border border-slate-900/10 bg-white px-5 text-sm font-extrabold text-slate-900 shadow-[0_12px_34px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-[2px] hover:border-red-500/25 hover:text-red-700 hover:shadow-[0_22px_50px_rgba(2,6,23,0.11)] active:scale-[0.98] sm:min-h-[52px] sm:w-auto sm:text-[0.95rem] md:shrink-0"
          >
            {{ homeProgram.primaryButtonLabel }}
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
                d="M17 8l4 4m0 0l-4 4m4-4H3"
              />
            </svg>
          </Link>
        </div>

        <div
          v-if="displayedProkers.length"
          class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-7 xl:grid-cols-3 lg:gap-8"
        >
          <article
            v-for="(proker, index) in displayedProkers"
            :key="proker.id || proker.title || index"
            role="button"
            tabindex="0"
            :aria-label="`Lihat detail ${proker.title || 'Program Kerja HMPS RPL'}`"
            class="group cursor-pointer overflow-hidden rounded-[1.35rem] border border-slate-900/5 bg-white text-left shadow-[0_12px_34px_rgba(2,6,23,0.06)] outline-none transition-all duration-300 hover:-translate-y-2 hover:border-red-500/20 hover:shadow-[0_30px_75px_rgba(2,6,23,0.14)] active:scale-[0.99] focus-visible:border-red-500/35 focus-visible:ring-4 focus-visible:ring-red-500/15 sm:rounded-[1.6rem]"
            data-aos="fade-up"
            :data-aos-delay="index * 90"
            data-aos-duration="800"
            @click="openProkerDetail(proker)"
            @keyup.enter="openProkerDetail(proker)"
            @keyup.space.prevent="openProkerDetail(proker)"
          >
            <div
              class="relative h-40 overflow-hidden bg-slate-100 min-[390px]:h-44 sm:h-56 lg:h-60"
            >
              <img
                :src="getProkerImage(proker)"
                :srcset="getProkerSrcset(proker) || null"
                sizes="(max-width: 640px) 92vw, (max-width: 1280px) 44vw, 360px"
                :alt="proker.title || 'Program Kerja HMPS RPL'"
                width="720"
                height="480"
                loading="lazy"
                fetchpriority="low"
                decoding="async"
                class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              />
              <div
                class="absolute inset-0 bg-[linear-gradient(to_top,rgba(6,11,22,0.72),rgba(6,11,22,0.08)_58%,transparent)]"
              />
              <span
                :class="getProkerTagClass(proker)"
                class="absolute left-3 top-3 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[0.62rem] font-black uppercase tracking-[0.06em] text-white shadow-[0_12px_24px_rgba(2,6,23,0.16)] sm:left-4 sm:top-4 sm:text-[0.68rem]"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-white/80" />
                {{ getProkerTag(proker) }}
              </span>
            </div>

            <div class="p-4 min-[390px]:p-5 sm:p-6">
              <div
                class="mb-3 flex flex-wrap gap-2 text-[0.68rem] font-bold text-slate-500 sm:mb-4 sm:text-[0.74rem]"
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
                  {{ getProkerDate(proker) }}
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
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  {{ getProkerDivision(proker) }}
                </span>
              </div>

              <h3
                class="line-clamp-2 text-[1.02rem] font-black leading-snug tracking-[-0.02em] text-slate-950 sm:text-xl"
              >
                {{ proker.title || "Program Kerja HMPS RPL" }}
              </h3>

              <p
                class="mt-2 line-clamp-3 text-sm leading-7 text-slate-500 sm:mt-3 sm:text-justify"
              >
                {{ getProkerDescription(proker) }}
              </p>

              <span
                class="mt-4 inline-flex min-h-[46px] w-full touch-manipulation items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white transition-all duration-300 group-hover:-translate-y-[2px] group-hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:scale-[0.98] group-hover:shadow-[0_18px_36px_rgba(220,38,38,0.22)] sm:mt-5 sm:min-h-[48px]"
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
                    stroke-width="2.4"
                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                  />
                </svg>
              </span>
            </div>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-[0_14px_36px_rgba(2,6,23,0.04)] sm:rounded-[2rem]"
        >
          <div
            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-2xl"
          >
            📌
          </div>
          <h3
            class="mt-5 text-xl font-black tracking-[-0.03em] text-slate-950 sm:text-2xl"
          >
            Belum ada program kerja yang ditampilkan.
          </h3>
          <p
            class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600 sm:text-center"
          >
            Isi data program kerja dan pastikan statusnya published agar tampil di
            Beranda.
          </p>
        </div>
      </div>
    </section>

    <!-- Modal Detail Program Kerja -->
    <div
      v-if="selectedProker"
      class="fixed inset-0 z-[80] flex items-end justify-center bg-slate-950/70 px-3 pb-3 pt-14 backdrop-blur-sm sm:items-center sm:p-6"
      role="dialog"
      aria-modal="true"
      @click.self="closeProkerDetail"
      @keyup.esc="closeProkerDetail"
    >
      <div
        class="relative max-h-[88svh] w-full max-w-4xl overflow-hidden rounded-t-[1.75rem] bg-white shadow-[0_30px_90px_rgba(2,6,23,0.35)] ring-1 ring-white/30 sm:rounded-[2rem]"
      >
        <button
          type="button"
          class="absolute right-3 top-3 z-20 inline-flex h-11 w-11 touch-manipulation items-center justify-center rounded-2xl bg-white/95 text-slate-700 shadow-[0_12px_28px_rgba(2,6,23,0.14)] backdrop-blur transition hover:-translate-y-0.5 hover:bg-red-50 hover:text-red-700 active:scale-95 focus:outline-none focus:ring-4 focus:ring-red-500/15 sm:right-4 sm:top-4"
          aria-label="Tutup detail program kerja"
          @click="closeProkerDetail"
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

        <div class="max-h-[88svh] overflow-y-auto overscroll-contain">
          <div
            class="relative h-52 overflow-hidden bg-slate-100 min-[390px]:h-56 sm:h-72 lg:h-80"
          >
            <img
              :src="getProkerImage(selectedProker)"
              :srcset="getProkerSrcset(selectedProker) || null"
              sizes="(max-width: 640px) 96vw, 860px"
              :alt="selectedProker.title || 'Detail Program Kerja HMPS RPL'"
              class="block h-full w-full object-cover"
              width="900"
              height="520"
              loading="lazy"
              fetchpriority="low"
              decoding="async"
            />
            <div
              class="absolute inset-0 bg-[linear-gradient(to_top,rgba(6,11,22,0.82),rgba(6,11,22,0.20)_58%,transparent)]"
            />
            <div
              class="absolute bottom-5 left-5 right-5 text-white sm:bottom-7 sm:left-7 sm:right-7"
            >
              <span
                :class="getProkerTagClass(selectedProker)"
                class="mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.07em] text-white shadow-[0_12px_24px_rgba(2,6,23,0.20)]"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-white/80" />
                {{ getProkerTag(selectedProker) }}
              </span>

              <h3
                class="max-w-3xl text-2xl font-black leading-tight tracking-[-0.04em] sm:text-3xl lg:text-4xl"
              >
                {{ selectedProker.title || "Program Kerja HMPS RPL" }}
              </h3>
            </div>
          </div>

          <div class="grid gap-6 p-5 sm:p-7 lg:grid-cols-[1.25fr_0.75fr] lg:gap-8 lg:p-8">
            <div>
              <div
                class="mb-5 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-2 text-[0.68rem] font-black uppercase tracking-[0.09em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Detail Program
              </div>

              <p
                class="text-[0.95rem] leading-8 text-slate-600 sm:text-justify sm:text-base"
              >
                {{ getProkerDescription(selectedProker) }}
              </p>

              <div v-if="getProkerGoals(selectedProker).length" class="mt-7">
                <h4 class="text-sm font-black uppercase tracking-[0.1em] text-slate-950">
                  Tujuan Program
                </h4>
                <ul class="mt-3 space-y-2.5">
                  <li
                    v-for="goal in getProkerGoals(selectedProker)"
                    :key="goal"
                    class="flex gap-3 text-sm leading-7 text-slate-600"
                  >
                    <span
                      class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600"
                    >
                      <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.5"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </span>
                    <span>{{ goal }}</span>
                  </li>
                </ul>
              </div>

              <div v-if="getProkerBenefits(selectedProker).length" class="mt-7">
                <h4 class="text-sm font-black uppercase tracking-[0.1em] text-slate-950">
                  Manfaat
                </h4>
                <ul class="mt-3 space-y-2.5">
                  <li
                    v-for="benefit in getProkerBenefits(selectedProker)"
                    :key="benefit"
                    class="flex gap-3 text-sm leading-7 text-slate-600"
                  >
                    <span
                      class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                    >
                      <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.5"
                          d="M5 13l4 4L19 7"
                        />
                      </svg>
                    </span>
                    <span>{{ benefit }}</span>
                  </li>
                </ul>
              </div>
            </div>

            <aside
              class="h-fit rounded-[1.45rem] border border-slate-900/5 bg-slate-50 p-4 shadow-[0_14px_34px_rgba(2,6,23,0.05)] sm:p-5"
            >
              <h4 class="text-base font-black tracking-[-0.02em] text-slate-950">
                Ringkasan Kegiatan
              </h4>

              <div class="mt-5 space-y-3">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-900/5">
                  <div
                    class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400"
                  >
                    Waktu
                  </div>
                  <div class="mt-1 text-sm font-extrabold text-slate-900">
                    {{ getProkerDate(selectedProker) }}
                  </div>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-900/5">
                  <div
                    class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400"
                  >
                    Penanggung Jawab
                  </div>
                  <div class="mt-1 text-sm font-extrabold text-slate-900">
                    {{ getProkerDivision(selectedProker) }}
                  </div>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-900/5">
                  <div
                    class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400"
                  >
                    Lokasi
                  </div>
                  <div class="mt-1 text-sm font-extrabold text-slate-900">
                    {{ getProkerLocation(selectedProker) }}
                  </div>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-900/5">
                  <div
                    class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400"
                  >
                    Anggaran
                  </div>
                  <div class="mt-1 text-sm font-extrabold text-slate-900">
                    {{ getProkerBudget(selectedProker) }}
                  </div>
                </div>
              </div>

              <Link
                :href="safeHref(homeProgram.primaryButtonUrl, '/program-kerja')"
                class="mt-5 inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_18px_36px_rgba(220,38,38,0.22)]"
              >
                Buka Halaman Program Kerja
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
                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                  />
                </svg>
              </Link>
            </aside>
          </div>
        </div>
      </div>
    </div>

    <!-- Galeri -->
    <section id="galeri" class="cv-auto scroll-mt-24 bg-white py-10 sm:py-20 lg:py-28">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mx-auto mb-6 max-w-3xl text-center sm:mb-14 lg:mb-16"
          data-aos="fade-up"
          data-aos-duration="800"
        >
          <div
            class="mx-auto mb-4 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700 sm:mb-5 sm:text-[0.7rem]"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            {{ homeGallerySection.badge }}
          </div>

          <h2
            class="text-[1.7rem] font-black leading-[1.08] tracking-[-0.045em] text-slate-950 min-[390px]:text-[1.95rem] sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            {{ homeGallerySection.title }}
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              {{ homeGallerySection.titleHighlight }}
            </span>
          </h2>

          <p
            class="mx-auto mt-3 max-w-2xl text-[0.9rem] leading-[1.76] text-slate-500 sm:mt-4 sm:text-center sm:text-[1rem] lg:text-[1.02rem]"
          >
            {{ homeGallerySection.description }}
          </p>
        </div>

        <div
          v-if="displayedGallery.length"
          class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 md:grid-cols-4"
          data-aos="fade-up"
          data-aos-delay="100"
          data-aos-duration="800"
        >
          <div
            v-for="(item, index) in displayedGallery"
            :key="item.id || `${item.title}-${index}`"
            class="group relative overflow-hidden rounded-[1.15rem] bg-slate-100 shadow-[0_14px_40px_rgba(2,6,23,0.10)] ring-1 ring-slate-900/5 sm:rounded-[1.65rem]"
            :class="galleryCardClass(item, index)"
          >
            <img
              :src="getGalleryImage(item)"
              :srcset="getGallerySrcset(item) || null"
              sizes="(max-width: 640px) 46vw, (max-width: 1024px) 44vw, 290px"
              :alt="getGalleryAlt(item)"
              width="720"
              height="520"
              loading="lazy"
              fetchpriority="low"
              decoding="async"
              class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div
              class="absolute inset-0 bg-[linear-gradient(to_top,rgba(6,11,22,0.78),rgba(6,11,22,0.10)_48%,transparent_78%)]"
            />
            <div
              class="absolute bottom-3 left-3 right-3 z-[2] text-white sm:bottom-4 sm:left-4 sm:right-4"
            >
              <small
                class="mb-1 block text-[0.6rem] font-black uppercase tracking-[0.09em] text-white/75 sm:text-[0.72rem]"
              >
                {{ getGalleryCategory(item) }}
              </small>
              <div class="line-clamp-2 text-sm font-black leading-snug sm:text-lg">
                {{ item.title || "Dokumentasi HMPS RPL" }}
              </div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-[0_14px_36px_rgba(2,6,23,0.04)] sm:rounded-[2rem]"
        >
          <div
            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl"
          >
            📂
          </div>
          <h3 class="mt-5 text-2xl font-black tracking-[-0.03em] text-slate-950">
            Belum ada galeri di Beranda
          </h3>
          <p
            class="mx-auto mt-4 max-w-xl text-sm leading-8 text-slate-600 sm:text-center sm:text-base"
          >
            Upload gambar melalui Admin → Custom Home → Media Home, pilih
            <strong>Group: Gallery</strong>, lalu pastikan statusnya aktif.
          </p>
        </div>

        <div
          class="mt-7 text-center sm:mt-12"
          data-aos="fade-up"
          data-aos-delay="160"
          data-aos-duration="800"
        >
          <Link
            :href="safeHref(homeGallerySection.primaryButtonUrl, '/dokumentasi')"
            class="inline-flex min-h-[48px] w-full touch-manipulation items-center justify-center gap-2 rounded-2xl border border-slate-900/10 bg-white px-5 text-sm font-extrabold text-slate-900 shadow-[0_12px_34px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-[2px] hover:border-red-500/25 hover:text-red-700 hover:shadow-[0_22px_50px_rgba(2,6,23,0.11)] active:scale-[0.98] sm:min-h-[52px] sm:w-auto sm:text-[0.95rem]"
          >
            {{ homeGallerySection.primaryButtonLabel }}
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
                d="M17 8l4 4m0 0l-4 4m4-4H3"
              />
            </svg>
          </Link>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
:deep(img) {
  max-width: 100%;
}

:deep(a),
:deep(button),
[role="button"] {
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

  * {
    scroll-behavior: auto !important;
  }
}

/* Full justify text — aman untuk halaman Home/Beranda */
.home-justify-page p,
.home-justify-page li span {
  text-align: justify !important;
  text-align-last: left;
  overflow-wrap: break-word;
  word-break: normal;
  hyphens: auto;
}

.home-justify-page .text-center p,
.home-justify-page p.text-center {
  text-align: justify !important;
  text-align-last: center;
}

.home-justify-page .line-clamp-2,
.home-justify-page .line-clamp-3,
.home-justify-page .line-clamp-6 {
  text-align: justify;
  text-align-last: left;
}


.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 900px;
}

@media (max-width: 767px) {
  .ticker-inner {
    animation-duration: 42s;
  }
}

@media (prefers-reduced-data: reduce) {
  .ticker-inner,
  .floating-badge {
    animation: none !important;
  }
}



.home-justify-page :where(a, button, input, select, textarea):focus-visible {
  outline: 3px solid rgba(239, 68, 68, 0.38);
  outline-offset: 4px;
}

@media (prefers-reduced-motion: reduce) {
  .home-justify-page *,
  .home-justify-page *::before,
  .home-justify-page *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
  }
}
</style>

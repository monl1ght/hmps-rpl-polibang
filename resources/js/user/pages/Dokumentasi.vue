<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { Head } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  documentations: {
    type: Array,
    default: () => [],
  },
});

const pageTitle = "Dokumentasi HMPS RPL";
const pageDescription =
  "Arsip dokumentasi kegiatan dan program kerja HMPS Rekayasa Perangkat Lunak yang tersusun rapi, profesional, mudah dicari, dan dapat difilter berdasarkan tahun maupun kategori.";

const ticks = [
  "DOKUMENTASI HMPS RPL",
  "KEGIATAN ORGANISASI",
  "PROGRAM KERJA",
  "ARSIP KEGIATAN",
  "PROFESIONAL",
  "TERSTRUKTUR",
  "TERDOKUMENTASI",
];

const selectedYear = ref("Semua");
const selectedCategory = ref("semua");
const searchQuery = ref("");
const selectedDocumentation = ref(null);
const selectedImageIndex = ref(0);

const fallbackImage =
  "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=960&h=640&fit=crop&auto=format&q=72";

const toText = (value, fallback = "") => {
  if (value === null || value === undefined) return fallback;

  if (typeof value === "string") {
    const trimmed = value.trim();
    return trimmed || fallback;
  }

  if (typeof value === "number") return String(value);

  if (typeof value === "object") {
    return (
      value.title ||
      value.label ||
      value.name ||
      value.text ||
      value.description ||
      value.desc ||
      fallback
    );
  }

  return fallback;
};

const isUnsplashUrl = (url) => /images\.unsplash\.com/i.test(String(url || ""));
const hasQueryString = (url) => String(url || "").includes("?");

const optimizeExternalImage = (url, width = 960, height = 640, quality = 72) => {
  const source = String(url || fallbackImage).trim();

  if (!source) return fallbackImage;

  if (!isUnsplashUrl(source)) return source;

  const base = source.split("?")[0];
  return `${base}?w=${width}&h=${height}&fit=crop&auto=format&q=${quality}`;
};

const pickImageField = (source, fallback = fallbackImage) => {
  if (!source) return fallback;

  if (typeof source === "string") {
    return optimizeExternalImage(source, 960, 640, 72);
  }

  return optimizeExternalImage(
    source.image_sm ||
      source.small_url ||
      source.thumbnail ||
      source.thumb_url ||
      source.cover_sm ||
      source.image_md ||
      source.medium_url ||
      source.cover_md ||
      source.cover ||
      source.image ||
      source.image_url ||
      source.cover_url ||
      source.src ||
      source.url ||
      fallback,
    960,
    640,
    72
  );
};

const getCoverSource = (item) =>
  pickImageField(
    item?.cover || item?.cover_url || item?.image || item?.image_url || item,
    fallbackImage
  );

const getImage = (image) => pickImageField(image, fallbackImage);

const buildSrcsetFromObject = (source) => {
  if (!source || typeof source === "string") return "";

  const entries = [
    [
      source.image_sm ||
        source.small_url ||
        source.thumbnail ||
        source.thumb_url ||
        source.cover_sm,
      "480w",
    ],
    [source.image_md || source.medium_url || source.cover_md, "768w"],
    [
      source.image_lg ||
        source.large_url ||
        source.cover_lg ||
        source.cover ||
        source.image ||
        source.image_url ||
        source.cover_url ||
        source.src ||
        source.url,
      "1100w",
    ],
  ].filter(([url]) => Boolean(url));

  return entries
    .map(
      ([url, width]) =>
        `${optimizeExternalImage(
          url,
          Number.parseInt(width, 10) || 960,
          640,
          72
        )} ${width}`
    )
    .join(", ");
};

const buildUnsplashSrcset = (url) => {
  if (!isUnsplashUrl(url)) return "";

  const base = String(url).split("?")[0];

  return [480, 768, 1100, 1400]
    .map(
      (width) =>
        `${base}?w=${width}&h=${Math.round(
          width * 0.66
        )}&fit=crop&auto=format&q=72 ${width}w`
    )
    .join(", ");
};

const getImageSrcset = (source) => {
  if (source && typeof source === "object") {
    return buildSrcsetFromObject(source) || buildUnsplashSrcset(pickImageField(source));
  }

  return buildUnsplashSrcset(source);
};

const getItemSrcset = (item) => {
  if (!item) return "";

  return (
    buildSrcsetFromObject(item) ||
    buildSrcsetFromObject(item.cover) ||
    buildUnsplashSrcset(getCoverSource(item))
  );
};

const getItemAlt = (item, fallback = "Dokumentasi HMPS RPL") =>
  toText(item?.alt || item?.alt_text || item?.title, fallback);

const normalizeCategory = (value) => {
  const text = String(value || "").toLowerCase();

  if (text.includes("proker") || text.includes("program")) {
    return "proker";
  }

  if (text.includes("kegiatan")) {
    return "kegiatan";
  }

  return "lainnya";
};

const getCategoryLabel = (category) => {
  const normalized = normalizeCategory(category);

  if (normalized === "proker") return "Program Kerja";
  if (normalized === "kegiatan") return "Kegiatan";

  return "Dokumentasi";
};

const categoryOptions = [
  { value: "semua", label: "Semua Kategori" },
  { value: "kegiatan", label: "Kegiatan" },
  { value: "proker", label: "Program Kerja" },
  { value: "lainnya", label: "Dokumentasi Lainnya" },
];

const documentations = computed(() =>
  Array.isArray(props.documentations) ? props.documentations : []
);

const getGalleryImages = (item) => {
  if (!item) return [];

  const images = [
    item.cover || item.cover_url || item.image || item.image_url,
    ...(Array.isArray(item.gallery) ? item.gallery : []),
  ].filter(Boolean);

  return [
    ...new Set(
      images.map((image) => (typeof image === "string" ? image : JSON.stringify(image)))
    ),
  ].map((image) => {
    try {
      return image.startsWith("{") ? JSON.parse(image) : image;
    } catch (error) {
      return image;
    }
  });
};

const selectedGalleryImages = computed(() =>
  getGalleryImages(selectedDocumentation.value)
);

const selectedMainImage = computed(
  () =>
    selectedGalleryImages.value[selectedImageIndex.value] ||
    selectedDocumentation.value?.cover ||
    selectedDocumentation.value?.cover_url ||
    fallbackImage
);

const setSelectedImage = (index) => {
  selectedImageIndex.value = index;
};

const nextImage = () => {
  if (!selectedGalleryImages.value.length) return;

  selectedImageIndex.value =
    selectedImageIndex.value >= selectedGalleryImages.value.length - 1
      ? 0
      : selectedImageIndex.value + 1;
};

const prevImage = () => {
  if (!selectedGalleryImages.value.length) return;

  selectedImageIndex.value =
    selectedImageIndex.value <= 0
      ? selectedGalleryImages.value.length - 1
      : selectedImageIndex.value - 1;
};

const yearOptions = computed(() => {
  const years = documentations.value
    .map((item) => String(item.year || ""))
    .filter(Boolean);

  return ["Semua", ...new Set(years).values()].sort((a, b) => {
    if (a === "Semua") return -1;
    if (b === "Semua") return 1;

    return Number(b) - Number(a);
  });
});

const documentationStats = computed(() => {
  const total = documentations.value.length;
  const kegiatan = documentations.value.filter(
    (item) => normalizeCategory(item.category) === "kegiatan"
  ).length;
  const proker = documentations.value.filter(
    (item) => normalizeCategory(item.category) === "proker"
  ).length;

  return [
    { label: "Total Dokumentasi", value: total || 0, desc: "Arsip tersimpan" },
    { label: "Kegiatan", value: kegiatan || 0, desc: "Aktivitas HMPS" },
    { label: "Program Kerja", value: proker || 0, desc: "Proker terdokumentasi" },
  ];
});

const filteredDocumentations = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return documentations.value.filter((item) => {
    const yearMatch =
      selectedYear.value === "Semua" || String(item.year) === String(selectedYear.value);

    const categoryMatch =
      selectedCategory.value === "semua" ||
      normalizeCategory(item.category) === selectedCategory.value;

    const searchableText = [item.title, item.description, item.location, item.year]
      .map((value) => String(value || "").toLowerCase())
      .join(" ");

    const searchMatch = !query || searchableText.includes(query);

    return yearMatch && categoryMatch && searchMatch;
  });
});

const normalizeFeaturedSize = (size) =>
  ["large", "medium", "small"].includes(size) ? size : "medium";

const featuredDocumentation = computed(() =>
  documentations.value.filter((item) => Boolean(item?.is_featured)).slice(0, 4)
);

const primaryFeatured = computed(
  () => featuredDocumentation.value[0] || documentations.value[0] || null
);

const featuredCardClass = (item) => {
  const size = normalizeFeaturedSize(item?.featured_size);

  if (size === "large") {
    return "sm:col-span-2";
  }

  return "sm:col-span-1";
};

const featuredImageClass = (item) => {
  const size = normalizeFeaturedSize(item?.featured_size);

  if (size === "large") {
    return "h-60 min-[390px]:h-72 sm:h-[23rem] lg:h-[25rem]";
  }

  if (size === "small") {
    return "h-36 sm:h-44";
  }

  return "h-48 sm:h-56";
};

const featuredLabel = () => "Unggulan";

const primaryCover = computed(() => getCoverSource(primaryFeatured.value));
const primaryCoverSrcset = computed(() => getItemSrcset(primaryFeatured.value));

const hasActiveFilter = computed(
  () =>
    selectedYear.value !== "Semua" ||
    selectedCategory.value !== "semua" ||
    Boolean(searchQuery.value.trim())
);

const resetFilters = () => {
  selectedYear.value = "Semua";
  selectedCategory.value = "semua";
  searchQuery.value = "";
};

const openDetail = (item) => {
  selectedDocumentation.value = item;
  selectedImageIndex.value = 0;

  if (typeof document !== "undefined") {
    document.body.style.overflow = "hidden";
  }
};

const closeDetail = () => {
  selectedDocumentation.value = null;
  selectedImageIndex.value = 0;

  if (typeof document !== "undefined") {
    document.body.style.overflow = "";
  }
};

const handleEscape = (event) => {
  if (event.key === "Escape" && selectedDocumentation.value) {
    closeDetail();
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleEscape);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleEscape);

  if (typeof document !== "undefined") {
    document.body.style.overflow = "";
  }
});
</script>

<template>
  <Head :title="pageTitle">
    <meta name="description" :content="pageDescription" />
    <meta
      name="keywords"
      content="Dokumentasi HMPS RPL, Galeri HMPS RPL, Dokumentasi Kegiatan, Program Kerja HMPS RPL, Rekayasa Perangkat Lunak"
    />
    <meta property="og:title" :content="pageTitle" />
    <meta property="og:description" :content="pageDescription" />
    <meta property="og:type" content="website" />
    <meta name="robots" content="index, follow" />
    <link
      v-if="primaryCover"
      rel="preload"
      as="image"
      :href="primaryCover"
      :imagesrcset="primaryCoverSrcset || null"
      imagesizes="(max-width: 640px) 92vw, (max-width: 1024px) 520px, 560px"
      fetchpriority="high"
    />
  </Head>

  <div
    class="documentation-page overflow-x-hidden bg-white font-['Plus_Jakarta_Sans',sans-serif] text-slate-900 antialiased"
  >
    <!-- Hero -->
    <section
      class="relative overflow-hidden bg-[radial-gradient(circle_at_14%_14%,rgba(239,68,68,0.13),transparent_28%),radial-gradient(circle_at_88%_20%,rgba(153,27,27,0.10),transparent_30%),linear-gradient(180deg,#ffffff_0%,#fff7f7_52%,#ffffff_100%)] pb-10 pt-[5.5rem] sm:pb-16 sm:pt-28 lg:flex lg:min-h-[92svh] lg:items-center lg:pb-20 lg:pt-32"
    >
      <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:linear-gradient(180deg,rgba(255,255,255,0.92),transparent_90%)] sm:bg-[size:40px_40px]"
      />
      <div
        aria-hidden="true"
        class="pointer-events-none absolute -right-24 top-12 h-72 w-72 rounded-full bg-red-500/10 blur-[76px] lg:h-96 lg:w-96"
      />
      <div
        aria-hidden="true"
        class="pointer-events-none absolute -bottom-20 -left-24 h-64 w-64 rounded-full bg-rose-900/10 blur-[76px] lg:h-80 lg:w-80"
      />

      <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="grid grid-cols-1 items-center gap-6 sm:gap-10 lg:grid-cols-[1fr_.95fr] xl:gap-14"
        >
          <div
            class="rounded-[1.75rem] border border-white/75 bg-white/74 p-4 shadow-[0_18px_52px_rgba(2,6,23,0.08)] backdrop-blur-xl sm:p-5 lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none lg:backdrop-blur-0"
          >
            <div
              class="mb-4 inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/90 px-3 py-2 text-[0.64rem] font-black uppercase tracking-[0.08em] text-red-700 shadow-[0_12px_32px_rgba(2,6,23,0.06)] min-[390px]:text-[0.68rem] sm:mb-5 sm:text-[0.75rem]"
            >
              <span
                aria-hidden="true"
                class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
              />
              <span class="min-w-0 truncate">Dokumentasi HMPS RPL</span>
            </div>

            <h1
              class="max-w-4xl break-words text-[2.08rem] font-black leading-[1.04] tracking-[-0.05em] text-slate-950 min-[390px]:text-[2.32rem] sm:text-[3.15rem] md:text-[3.8rem] xl:text-[4.7rem]"
            >
              Dokumentasi
              <span
                class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent sm:inline"
              >
                HMPS RPL
              </span>
            </h1>

            <p
              class="mt-4 max-w-2xl text-justify text-[0.92rem] leading-[1.78] text-slate-600 sm:mt-6 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              Halaman dokumentasi ini menampilkan arsip kegiatan dan program kerja HMPS
              RPL secara rapi, terstruktur, serta mudah ditelusuri melalui filter tahun,
              kategori, dan pencarian kegiatan.
            </p>

            <div class="mt-6 grid grid-cols-3 gap-2 sm:mt-8 sm:max-w-xl sm:gap-3">
              <div
                v-for="item in documentationStats"
                :key="item.label"
                class="rounded-2xl border border-slate-900/5 bg-white/90 p-3 shadow-[0_12px_30px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:p-4"
              >
                <div
                  class="text-base font-black tracking-[-0.04em] text-slate-950 min-[390px]:text-lg sm:text-2xl"
                >
                  {{ item.value }}
                </div>
                <div
                  class="mt-1 text-[0.56rem] font-black uppercase tracking-[0.07em] text-red-700 min-[390px]:text-[0.62rem] sm:text-[0.72rem]"
                >
                  {{ item.label }}
                </div>
                <div class="mt-1 hidden text-xs font-semibold text-slate-500 sm:block">
                  {{ item.desc }}
                </div>
              </div>
            </div>
          </div>

          <div
            class="relative mx-auto w-full max-w-[35rem]"
            data-aos="fade-up"
            data-aos-delay="100"
            data-aos-duration="800"
          >
            <div
              class="relative overflow-hidden rounded-[1.7rem] border border-white/80 bg-white/90 p-3 shadow-[0_24px_70px_rgba(2,6,23,0.12)] backdrop-blur-xl sm:rounded-[2rem] sm:p-4"
            >
              <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 rounded-[1.7rem] [background:linear-gradient(135deg,rgba(239,68,68,0.22),rgba(255,255,255,0.25),rgba(153,27,27,0.16))] [mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:exclude] p-px sm:rounded-[2rem]"
              />

              <div
                v-if="featuredDocumentation.length"
                class="relative z-10 grid grid-cols-1 gap-4 sm:grid-cols-2"
              >
                <button
                  v-for="(item, index) in featuredDocumentation"
                  :key="item.id || `${item.title}-${index}`"
                  type="button"
                  class="group relative overflow-hidden rounded-[1.35rem] bg-white text-left shadow-[0_18px_46px_rgba(15,23,42,0.12)] outline-none ring-1 ring-white/60 transition duration-300 hover:-translate-y-1 hover:shadow-[0_26px_60px_rgba(15,23,42,0.18)] focus-visible:ring-4 focus-visible:ring-red-500/20 sm:rounded-[1.65rem]"
                  :class="featuredCardClass(item)"
                  :aria-label="`Lihat detail ${item.title || 'Dokumentasi HMPS RPL'}`"
                  @click="openDetail(item)"
                >
                  <div class="relative overflow-hidden" :class="featuredImageClass(item)">
                    <img
                      :src="getCoverSource(item)"
                      :srcset="getItemSrcset(item) || null"
                      sizes="(max-width: 640px) 92vw, (max-width: 1024px) 520px, 560px"
                      :alt="getItemAlt(item)"
                      width="720"
                      height="480"
                      :loading="index === 0 ? 'eager' : 'lazy'"
                      :fetchpriority="index === 0 ? 'high' : 'low'"
                      decoding="async"
                      class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                    />
                    <div
                      class="absolute inset-0 bg-[linear-gradient(to_top,rgba(6,11,22,0.84),rgba(6,11,22,0.18)_58%,transparent)]"
                    />
                    <div
                      class="absolute left-3 right-3 top-3 flex flex-wrap gap-2 sm:left-4 sm:right-4 sm:top-4"
                    >
                      <span
                        class="rounded-full bg-white/90 px-3 py-1.5 text-[0.62rem] font-black uppercase tracking-[0.08em] text-slate-900 shadow-sm"
                      >
                        {{ item.year || "Arsip" }}
                      </span>
                      <span
                        class="rounded-full bg-red-600 px-3 py-1.5 text-[0.62rem] font-black uppercase tracking-[0.08em] text-white shadow-sm"
                      >
                        {{ getCategoryLabel(item.category) }}
                      </span>
                    </div>
                    <div
                      class="absolute bottom-4 left-4 right-4 text-white sm:bottom-5 sm:left-5 sm:right-5"
                    >
                      <p
                        class="text-[0.64rem] font-black uppercase tracking-[0.12em] text-red-100"
                      >
                        {{ featuredLabel(item) }}
                      </p>
                      <h2
                        class="mt-2 line-clamp-2 font-black leading-tight tracking-[-0.04em]"
                        :class="
                          normalizeFeaturedSize(item.featured_size) === 'large'
                            ? 'text-2xl sm:text-3xl'
                            : 'text-base sm:text-xl'
                        "
                      >
                        {{ item.title || "Dokumentasi HMPS RPL" }}
                      </h2>
                      <p
                        v-if="normalizeFeaturedSize(item.featured_size) !== 'small'"
                        class="mt-2 line-clamp-2 text-sm leading-6 text-slate-200"
                      >
                        {{ item.description || "Arsip dokumentasi kegiatan HMPS RPL." }}
                      </p>
                    </div>
                  </div>
                </button>
              </div>

              <div
                v-else
                class="relative z-10 rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
              >
                <div
                  class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm"
                >
                  📂
                </div>
                <h2 class="mt-4 text-xl font-black tracking-[-0.03em] text-slate-950">
                  Belum ada dokumentasi Unggulan yang ditampilkan
                </h2>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ticker -->
    <div
      class="overflow-hidden border-y border-white/5 bg-[linear-gradient(135deg,#0f172a,#172033_60%,#111827)] py-2.5 sm:py-4"
      aria-label="Highlight dokumentasi"
    >
      <div class="ticker-wrap w-full overflow-hidden">
        <div class="ticker-inner flex w-max">
          <div v-for="dup in 2" :key="dup" class="flex items-center">
            <span
              v-for="item in ticks"
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

    <!-- Filters -->
    <section class="cv-auto bg-white py-10 sm:py-14">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="rounded-[1.65rem] border border-slate-900/[0.06] bg-white p-4 shadow-[0_14px_40px_rgba(2,6,23,0.06)] sm:rounded-[1.9rem] sm:p-6"
          data-aos="fade-up"
          data-aos-duration="800"
        >
          <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
              <p
                class="text-[0.7rem] font-black uppercase tracking-[0.12em] text-red-700"
              >
                Filter Dokumentasi
              </p>
              <h2
                class="mt-2 text-2xl font-black tracking-[-0.04em] text-slate-950 sm:text-3xl"
              >
                Temukan arsip lebih cepat
              </h2>
            </div>
            <button
              v-if="hasActiveFilter"
              type="button"
              class="inline-flex min-h-[42px] items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/15"
              @click="resetFilters"
            >
              Reset Filter
            </button>
          </div>

          <div class="grid gap-4 lg:grid-cols-[1.2fr_.8fr_.8fr]">
            <div>
              <label
                for="documentation-search"
                class="mb-2 block text-sm font-bold text-slate-800"
              >
                Cari Dokumentasi
              </label>
              <input
                id="documentation-search"
                v-model="searchQuery"
                type="search"
                placeholder="Cari judul, deskripsi, tahun, atau lokasi..."
                class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
            </div>

            <div>
              <label
                for="documentation-year"
                class="mb-2 block text-sm font-bold text-slate-800"
              >
                Filter Tahun
              </label>
              <select
                id="documentation-year"
                v-model="selectedYear"
                class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              >
                <option v-for="year in yearOptions" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>

            <div>
              <label
                for="documentation-category"
                class="mb-2 block text-sm font-bold text-slate-800"
              >
                Kategori
              </label>
              <select
                id="documentation-category"
                v-model="selectedCategory"
                class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="category in categoryOptions"
                  :key="category.value"
                  :value="category.value"
                >
                  {{ category.label }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Grid Dokumentasi -->
    <section
      class="cv-auto bg-[linear-gradient(180deg,#fff_0%,#fafafa_100%)] pb-14 sm:pb-20 lg:pb-24"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-7 flex flex-col justify-between gap-3 sm:mb-10 sm:flex-row sm:items-end"
        >
          <div>
            <p class="text-[0.7rem] font-black uppercase tracking-[0.12em] text-red-700">
              Arsip Kegiatan
            </p>
            <h2
              class="mt-2 text-2xl font-black tracking-[-0.04em] text-slate-950 sm:text-3xl"
            >
              Daftar Dokumentasi
            </h2>
            <p class="mt-2 text-sm leading-7 text-slate-500 sm:text-base">
              Menampilkan {{ filteredDocumentations.length }} dokumentasi yang sesuai
              filter.
            </p>
          </div>
        </div>

        <div
          v-if="filteredDocumentations.length"
          class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
          <article
            v-for="(item, index) in filteredDocumentations"
            :key="item.id || `${item.title}-${index}`"
            class="group overflow-hidden rounded-[1.5rem] border border-slate-900/[0.06] bg-white shadow-[0_14px_40px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-2 hover:border-red-500/20 hover:shadow-[0_26px_64px_rgba(2,6,23,0.12)] sm:rounded-[1.75rem]"
            data-aos="fade-up"
            :data-aos-delay="Math.min(index * 60, 240)"
            data-aos-duration="800"
          >
            <button
              type="button"
              class="block w-full text-left outline-none focus-visible:ring-4 focus-visible:ring-red-500/20"
              :aria-label="`Lihat detail ${item.title || 'Dokumentasi HMPS RPL'}`"
              @click="openDetail(item)"
            >
              <div class="relative h-52 overflow-hidden bg-slate-100 sm:h-60">
                <img
                  :src="getCoverSource(item)"
                  :srcset="getItemSrcset(item) || null"
                  sizes="(max-width: 640px) 92vw, (max-width: 1280px) 46vw, 370px"
                  :alt="getItemAlt(item)"
                  width="640"
                  height="426"
                  loading="lazy"
                  fetchpriority="low"
                  decoding="async"
                  class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                />

                <div
                  class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-slate-950/70 via-slate-950/25 to-transparent"
                />

                <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                  <span
                    class="rounded-full bg-white/90 px-3 py-1.5 text-[0.65rem] font-black uppercase tracking-[0.08em] text-slate-900 shadow-sm"
                  >
                    {{ item.year || "Arsip" }}
                  </span>
                  <span
                    class="rounded-full bg-red-600 px-3 py-1.5 text-[0.65rem] font-black uppercase tracking-[0.08em] text-white shadow-sm"
                  >
                    {{ getCategoryLabel(item.category) }}
                  </span>
                </div>
              </div>

              <div class="p-5 sm:p-6">
                <h3
                  class="line-clamp-2 text-xl font-black leading-snug tracking-[-0.02em] text-slate-950"
                >
                  {{ item.title || "Dokumentasi HMPS RPL" }}
                </h3>

                <p
                  class="mt-3 line-clamp-3 text-justify text-sm leading-7 text-slate-600"
                >
                  {{ item.description || "Deskripsi dokumentasi belum tersedia." }}
                </p>

                <div class="mt-5 grid gap-3">
                  <div
                    v-if="item.date"
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                  >
                    <p
                      class="text-[0.65rem] font-black uppercase tracking-[0.1em] text-slate-500"
                    >
                      Tanggal
                    </p>
                    <p class="mt-1 text-sm font-bold text-slate-900">
                      {{ item.date }}
                    </p>
                  </div>

                  <div
                    v-if="item.location"
                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                  >
                    <p
                      class="text-[0.65rem] font-black uppercase tracking-[0.1em] text-slate-500"
                    >
                      Lokasi
                    </p>
                    <p class="mt-1 line-clamp-2 text-sm font-bold text-slate-900">
                      {{ item.location }}
                    </p>
                  </div>
                </div>

                <div
                  class="mt-6 inline-flex min-h-[46px] w-full items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white transition-all duration-300 group-hover:-translate-y-1 group-hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] group-hover:shadow-[0_18px_36px_rgba(220,38,38,0.22)]"
                >
                  Lihat Detail
                  <svg
                    class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.5"
                      d="M5 12h14m-6-6 6 6-6 6"
                    />
                  </svg>
                </div>
              </div>
            </button>
          </article>
        </div>

        <div
          v-else
          class="rounded-[1.8rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-[0_10px_30px_rgba(2,6,23,0.04)]"
        >
          <div
            class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.2rem] bg-slate-100 text-2xl"
          >
            📂
          </div>

          <h3 class="mt-5 text-2xl font-black tracking-[-0.03em] text-slate-950">
            Dokumentasi tidak ditemukan
          </h3>

          <p class="mx-auto mt-4 max-w-xl text-sm leading-8 text-slate-600 sm:text-base">
            Coba ubah filter tahun, kategori, atau kata pencarian agar hasil dokumentasi
            dapat ditampilkan.
          </p>

          <button
            v-if="hasActiveFilter"
            type="button"
            class="mt-6 inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white transition hover:-translate-y-1 hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/20"
            @click="resetFilters"
          >
            Reset Filter
          </button>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cv-auto bg-white pb-16 pt-2 sm:pb-20">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="overflow-hidden rounded-[2rem] border border-slate-900/10 bg-[linear-gradient(135deg,#0f172a,#111827_55%,#1e293b)] px-6 py-8 text-white shadow-[0_20px_60px_rgba(2,6,23,0.16)] sm:px-8 sm:py-10 lg:px-12 lg:py-12"
          data-aos="fade-up"
          data-aos-duration="800"
        >
          <div class="max-w-3xl">
            <div
              class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-red-200 sm:text-[11px]"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-red-400" />
              Dokumentasi HMPS RPL
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] sm:text-3xl lg:text-4xl">
              Seluruh kegiatan dan program kerja
              <span class="text-red-400">terarsip lebih rapi dan profesional</span>
            </h2>

            <p
              class="mt-4 text-justify text-sm leading-7 text-slate-300 sm:text-base sm:leading-8"
            >
              Dengan halaman dokumentasi ini, pengunjung dapat melihat perjalanan kegiatan
              HMPS RPL secara tertata, terstruktur, dan mudah ditelusuri berdasarkan tahun
              pelaksanaan.
            </p>
          </div>
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
          v-if="selectedDocumentation"
          class="fixed inset-0 z-[999] overflow-y-auto bg-slate-950/75 px-3 py-4 backdrop-blur-md sm:px-6 sm:py-6"
          role="dialog"
          aria-modal="true"
          :aria-label="selectedDocumentation.title || 'Detail dokumentasi HMPS RPL'"
          @click.self="closeDetail"
        >
          <div class="flex min-h-full items-center justify-center">
            <div
              class="relative w-full max-w-7xl overflow-hidden rounded-[1.75rem] bg-white shadow-[0_40px_120px_rgba(2,6,23,0.35)] sm:rounded-[2rem]"
            >
              <button
                type="button"
                aria-label="Tutup detail dokumentasi"
                class="absolute right-3 top-3 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-md transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/20 sm:right-4 sm:top-4"
                @click="closeDetail"
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

              <div class="grid grid-cols-1">
                <div
                  class="relative flex min-h-[54vh] items-center justify-center bg-slate-950 sm:min-h-[68vh]"
                >
                  <img
                    :src="getImage(selectedMainImage)"
                    :srcset="getImageSrcset(selectedMainImage) || null"
                    sizes="(max-width: 640px) 94vw, (max-width: 1024px) 90vw, 1100px"
                    :alt="selectedDocumentation.title || 'Detail dokumentasi HMPS RPL'"
                    width="1200"
                    height="800"
                    loading="eager"
                    decoding="async"
                    class="max-h-[78vh] w-full bg-slate-950 object-contain"
                  />

                  <div
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-44 bg-gradient-to-t from-slate-950/85 to-transparent"
                  />

                  <div
                    class="absolute bottom-5 left-5 right-5 z-10 flex items-end justify-between gap-4 text-white sm:bottom-6 sm:left-6 sm:right-6"
                  >
                    <div class="min-w-0">
                      <p
                        class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-200"
                      >
                        Foto {{ selectedImageIndex + 1 }} dari
                        {{ selectedGalleryImages.length || 1 }}
                      </p>

                      <h3
                        class="mt-1 line-clamp-2 text-2xl font-black leading-tight tracking-[-0.04em] sm:text-4xl"
                      >
                        {{ selectedDocumentation.title }}
                      </h3>
                    </div>

                    <div
                      v-if="selectedGalleryImages.length > 1"
                      class="flex shrink-0 items-center gap-2"
                    >
                      <button
                        type="button"
                        aria-label="Foto sebelumnya"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/25 sm:h-12 sm:w-12"
                        @click="prevImage"
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
                            stroke-width="2.5"
                            d="M15 19l-7-7 7-7"
                          />
                        </svg>
                      </button>

                      <button
                        type="button"
                        aria-label="Foto berikutnya"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/25 sm:h-12 sm:w-12"
                        @click="nextImage"
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
                            stroke-width="2.5"
                            d="M9 5l7 7-7 7"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="p-5 sm:p-8 lg:p-10">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full bg-red-600 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-white"
                    >
                      {{ getCategoryLabel(selectedDocumentation.category) }}
                    </span>
                    <span
                      class="rounded-full bg-slate-100 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-700"
                    >
                      {{ selectedDocumentation.year || "Arsip" }}
                    </span>
                  </div>

                  <h3
                    class="mt-5 text-2xl font-black leading-tight tracking-[-0.04em] text-slate-950 sm:text-4xl"
                  >
                    {{ selectedDocumentation.title }}
                  </h3>

                  <p
                    class="mt-5 text-justify text-sm leading-8 text-slate-600 sm:text-base"
                  >
                    {{
                      selectedDocumentation.description ||
                      "Deskripsi dokumentasi belum tersedia."
                    }}
                  </p>

                  <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div
                      v-if="selectedDocumentation.date"
                      class="rounded-[1.2rem] border border-slate-200 bg-slate-50 p-4"
                    >
                      <p
                        class="text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-500"
                      >
                        Tanggal
                      </p>
                      <p class="mt-2 text-sm font-bold text-slate-900">
                        {{ selectedDocumentation.date }}
                      </p>
                    </div>

                    <div
                      v-if="selectedDocumentation.location"
                      class="rounded-[1.2rem] border border-slate-200 bg-slate-50 p-4"
                    >
                      <p
                        class="text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-500"
                      >
                        Lokasi
                      </p>
                      <p class="mt-2 text-sm font-bold text-slate-900">
                        {{ selectedDocumentation.location }}
                      </p>
                    </div>
                  </div>

                  <div v-if="selectedGalleryImages.length" class="mt-8">
                    <div class="mb-4 flex items-end justify-between gap-4">
                      <div>
                        <h4 class="text-lg font-black text-slate-950">
                          Galeri Dokumentasi
                        </h4>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                          Geser ke samping untuk melihat semua foto kegiatan.
                        </p>
                      </div>

                      <span
                        class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 sm:inline-flex"
                      >
                        {{ selectedGalleryImages.length }} Foto
                      </span>
                    </div>

                    <div class="scrollbar-thin flex snap-x gap-4 overflow-x-auto pb-4">
                      <button
                        v-for="(image, index) in selectedGalleryImages"
                        :key="`${getImage(image)}-${index}`"
                        type="button"
                        class="group relative h-28 w-40 shrink-0 snap-start overflow-hidden rounded-[1.1rem] border bg-slate-100 transition focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/20 sm:h-32 sm:w-48"
                        :class="
                          selectedImageIndex === index
                            ? 'border-red-500 ring-4 ring-red-100'
                            : 'border-slate-200 hover:border-red-300'
                        "
                        :aria-label="`Tampilkan foto dokumentasi ke-${index + 1}`"
                        @click="setSelectedImage(index)"
                      >
                        <img
                          :src="getImage(image)"
                          :srcset="getImageSrcset(image) || null"
                          sizes="180px"
                          :alt="`${
                            selectedDocumentation.title || 'Dokumentasi HMPS RPL'
                          } ${index + 1}`"
                          width="240"
                          height="160"
                          loading="lazy"
                          fetchpriority="low"
                          decoding="async"
                          class="h-full w-full bg-slate-950 object-cover transition duration-300 group-hover:scale-105"
                        />

                        <div
                          class="absolute inset-0 bg-gradient-to-t from-slate-950/55 to-transparent opacity-80"
                        />

                        <div
                          class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-white"
                        >
                          <span class="text-xs font-black">Foto {{ index + 1 }}</span>
                          <span
                            v-if="selectedImageIndex === index"
                            class="rounded-full bg-red-600 px-2 py-0.5 text-[0.6rem] font-black uppercase tracking-[0.08em]"
                          >
                            Aktif
                          </span>
                        </div>
                      </button>
                    </div>
                  </div>

                  <button
                    type="button"
                    class="mt-8 inline-flex min-h-[50px] w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_10px_24px_rgba(2,6,23,0.16)] transition hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_18px_34px_rgba(220,38,38,0.24)] focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/20 sm:w-auto"
                    @click="closeDetail"
                  >
                    Tutup Detail
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
:deep(img) {
  max-width: 100%;
}

:deep(a),
:deep(button),
:deep(input),
:deep(select) {
  -webkit-tap-highlight-color: transparent;
}

.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 900px;
}

.ticker-inner {
  animation: tickerMove 28s linear infinite;
  will-change: transform;
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

.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: rgba(220, 38, 38, 0.55) rgba(226, 232, 240, 0.8);
}

.scrollbar-thin::-webkit-scrollbar {
  height: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: rgba(226, 232, 240, 0.8);
  border-radius: 999px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(220, 38, 38, 0.55);
  border-radius: 999px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: rgba(185, 28, 28, 0.75);
}

@media (prefers-reduced-motion: reduce) {
  .ticker-inner {
    animation: none;
  }

  *,
  *::before,
  *::after {
    scroll-behavior: auto !important;
    transition-duration: 1ms !important;
    animation-duration: 1ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

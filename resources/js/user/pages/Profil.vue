<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import UserLayout from "@/user/layouts/UserLayout.vue";

defineOptions({
  layout: UserLayout,
});

const props = defineProps({
  profileHero: {
    type: Object,
    default: () => ({}),
  },
  profileAbout: {
    type: Object,
    default: () => ({}),
  },
  profileHistory: {
    type: Object,
    default: () => ({}),
  },
  profileVisionMission: {
    type: Object,
    default: () => ({}),
  },
  profileIdentity: {
    type: Object,
    default: () => ({}),
  },
  profileFunction: {
    type: Object,
    default: () => ({}),
  },
  profileCabinetLogo: {
    type: Object,
    default: () => ({}),
  },
  cabinetLogoImage: {
    type: [Object, String],
    default: () => ({}),
  },
  profileHeroImages: {
    type: Array,
    default: () => [],
  },
  ticks: {
    type: Array,
    default: () => [],
  },
  periods: {
    type: Array,
    default: () => [],
  },
  missions: {
    type: Array,
    default: () => [],
  },
  identityCards: {
    type: Array,
    default: () => [],
  },
  functionPoints: {
    type: Array,
    default: () => [],
  },
});

const fallbackHero = {
  badge: "Profil HMPS Rekayasa Perangkat Lunak",
  title: "Mengenal lebih dekat",
  titleHighlight: "HMPS RPL",
  description:
    "Halaman ini memuat profil organisasi, sejarah kepemimpinan, visi dan misi, serta nilai-nilai utama yang menjadi arah gerak HMPS Rekayasa Perangkat Lunak.",
  meta: {
    floating_top_title: "Identitas Organisasi",
    floating_top_subtitle: "Tersusun lebih jelas",
    floating_bottom_title: "Aktif & Berkembang",
    floating_bottom_subtitle: "Organisasi mahasiswa RPL",
  },
};

const fallbackAbout = {
  badge: "Tentang Kami",
  title: "Sekilas tentang",
  titleHighlight: "HMPS RPL",
  description:
    "HMPS Rekayasa Perangkat Lunak merupakan organisasi mahasiswa yang menjadi wadah aspirasi, pengembangan potensi, penguatan kebersamaan, serta ruang pembelajaran organisasi bagi mahasiswa RPL.",
  primaryButtonLabel: "Lihat Kepengurusan",
  primaryButtonUrl: "/kepengurusan",
  meta: {
    paragraph_2:
      "HMPS RPL tidak hanya berperan sebagai pelaksana program kerja, tetapi juga menjadi jembatan komunikasi antara mahasiswa dengan program studi, serta ruang pembinaan kepemimpinan, tanggung jawab, dan budaya kerja sama.",
    paragraph_3:
      "Melalui kegiatan akademik, sosial, dan kolaboratif, HMPS RPL berupaya menciptakan lingkungan mahasiswa yang aktif, suportif, inovatif, dan berorientasi pada kemajuan bersama.",
    function_title:
      "HMPS hadir sebagai ruang aspirasi, pengembangan, dan kontribusi nyata mahasiswa RPL.",
  },
};

const fallbackHistory = {
  badge: "Sejarah HMPS",
  title: "Perjalanan organisasi",
  titleHighlight: "per periode",
  description:
    "Gambaran perkembangan HMPS RPL dari masa perintisan hingga periode terkini.",
};

const fallbackVisionMission = {
  badge: "Visi & Misi",
  title: "Arah gerak",
  titleHighlight: "HMPS RPL",
  description:
    "Visi dan misi menjadi fondasi utama dalam menjalankan program kerja dan budaya organisasi HMPS RPL.",
  meta: {
    vision:
      "Menjadikan HIMA RPL sebagai wadah membentuk mahasiswa RPL yang kompeten, aktif, kolaboratif, dan inovatif di bidang Teknologi Informasi dengan tetap menjunjung tinggi akhlak dan adab yang baik.",
  },
};

const fallbackIdentity = {
  badge: "Identitas Organisasi",
  title: "Nilai utama",
  titleHighlight: "HMPS RPL",
  description:
    "Nilai yang menjadi identitas dan budaya organisasi HMPS RPL dalam menjalankan peran dan kontribusinya.",
};

const fallbackFunction = {
  badge: "Fungsi Utama",
  title: "Peran strategis",
  titleHighlight: "HMPS RPL",
  description:
    "HMPS RPL berperan sebagai penghubung, penggerak, dan wadah pengembangan mahasiswa.",
};

const fallbackCabinetLogo = {
  badge: "Logo Kabinet",
  title: "Arti Logo",
  titleHighlight: "Raksa Devarya",
  description:
    "Logo Raksa Devarya menjadi identitas visual kabinet yang mencerminkan semangat menjaga, menguatkan, dan mengembangkan ruang kolaborasi mahasiswa RPL. Setiap bentuk visual di dalamnya merepresentasikan keberanian untuk bergerak maju, keterbukaan terhadap ide baru, serta komitmen membangun organisasi yang aktif, kreatif, profesional, dan berdampak.",
  meta: {
    cabinet_name: "Raksa Devarya",
    period: "Periode 2025/2026",
    section_title: "Arti Logo",
    section_highlight: "Raksa Devarya",
    logo_tagline: "Aktif, Kreatif, Kolaboratif",
    logo_caption: "Identitas visual kabinet HMPS RPL",
    meaning_title: "Makna Identitas Kabinet",
    meaning_subtitle:
      "Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.",
  },
};

const fallbackCabinetLogoImage = {
  id: "cabinet-logo-fallback",
  image: "/Images/logo/hmps-rpl-logo.png",
  alt: "Logo Kabinet Raksa Devarya",
  title: "Raksa Devarya",
};

const fallbackTicks = [
  "Profil HMPS RPL",
  "Sejarah Organisasi",
  "Visi & Misi",
  "Kolaboratif",
  "Profesional",
  "Inovatif",
  "Mahasiswa Aktif dan Berkarya",
];

const fallbackPeriods = [
  {
    periode: "2022 / 2023",
    title: "Masa Perintisan HMPS RPL",
    ketua: "Ahmad Fikri Ramadhan",
    wakil: "Nabila Aulia Putri",
    description:
      "Periode awal pembentukan HMPS RPL sebagai wadah resmi mahasiswa Rekayasa Perangkat Lunak. Fokus utama diarahkan pada pembentukan identitas organisasi, penyusunan program awal, dan penguatan kebersamaan antar mahasiswa.",
  },
  {
    periode: "2023 / 2024",
    title: "Penguatan Internal Organisasi",
    ketua: "Muhammad Rafli Pratama",
    wakil: "Siti Nurjanah",
    description:
      "Pada periode ini HMPS RPL mulai menata sistem kerja organisasi dengan lebih terarah. Program kerja difokuskan pada kaderisasi, peningkatan partisipasi mahasiswa, dan penguatan koordinasi internal antar divisi.",
  },
  {
    periode: "2024 / 2025",
    title: "Perluasan Program dan Kolaborasi",
    ketua: "Dimas Alfarizi",
    wakil: "Nadya Khairunnisa",
    description:
      "HMPS RPL berkembang dengan menghadirkan program yang lebih variatif, baik akademik maupun non-akademik. Pada masa ini kolaborasi dengan pihak internal dan eksternal mulai diperluas untuk memperbesar dampak organisasi.",
  },
  {
    periode: "2025 / 2026",
    title: "Akselerasi Inovasi dan Profesionalisme",
    ketua: "Rizky Maulana",
    wakil: "Aisyah Maharani",
    description:
      "Fokus kepengurusan diarahkan pada inovasi program, peningkatan kualitas pelaksanaan kegiatan, serta pembentukan budaya organisasi yang aktif, adaptif, profesional, dan berdampak bagi mahasiswa Rekayasa Perangkat Lunak.",
  },
];

const fallbackMissions = [
  "Mengembangkan potensi mahasiswa RPL melalui berbagai kegiatan baik akademik maupun non-akademik agar skill dan pengalaman makin terasah.",
  "Membangun kolaborasi dengan industri maupun alumni untuk membuka peluang magang, project, dan pelatihan yang bermanfaat untuk mahasiswa RPL.",
  "Menciptakan lingkungan yang penuh kekeluargaan antar mahasiswa, serta menumbuhkan tanggung jawab dan kepemimpinan bagi anggota HIMA.",
  "Mengoptimalkan media sosial sebagai wadah dokumentasi, komunikasi, dan promosi HIMA RPL agar lebih informatif dan menarik.",
  "Melestarikan budaya santri di lingkungan mahasiswa RPL dengan menjunjung tinggi adab dan sopan santun baik terhadap dosen maupun antar mahasiswa.",
];

const fallbackIdentityCards = [
  {
    title: "Wadah Aspirasi",
    desc:
      "Menjadi ruang mahasiswa RPL untuk menyampaikan gagasan, kebutuhan, dan ide secara terbuka dan terarah.",
  },
  {
    title: "Pengembangan Potensi",
    desc:
      "Mendorong peningkatan kemampuan akademik, organisasi, komunikasi, dan kepemimpinan mahasiswa.",
  },
  {
    title: "Budaya Kolaboratif",
    desc: "Membangun kebersamaan, komunikasi sehat, dan kerja tim antar mahasiswa RPL.",
  },
  {
    title: "Kontribusi Nyata",
    desc:
      "Menghadirkan kegiatan yang relevan, bermanfaat, dan berdampak bagi lingkungan kampus.",
  },
];

const fallbackFunctionPoints = [
  "Menjadi wadah aspirasi mahasiswa RPL.",
  "Menyelenggarakan kegiatan yang relevan dan bermanfaat.",
  "Mengembangkan hard skill dan soft skill mahasiswa.",
  "Membangun budaya organisasi yang kolaboratif dan profesional.",
];

const fallbackProfileHeroImages = [
  {
    id: "fallback-1",
    image:
      "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop&auto=format",
    alt: "Kolaborasi mahasiswa HMPS RPL",
  },
  {
    id: "fallback-2",
    image:
      "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=700&h=900&fit=crop&auto=format",
    alt: "Diskusi organisasi mahasiswa",
  },
  {
    id: "fallback-3",
    image:
      "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=900&fit=crop&auto=format",
    alt: "Kegiatan mahasiswa HMPS RPL",
  },
  {
    id: "fallback-4",
    image:
      "https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=600&fit=crop&auto=format",
    alt: "Workshop dan pengembangan mahasiswa",
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

const hero = computed(() => mergeSection(props.profileHero, fallbackHero));
const about = computed(() => mergeSection(props.profileAbout, fallbackAbout));
const historySection = computed(() =>
  mergeSection(props.profileHistory, fallbackHistory)
);
const visionMission = computed(() =>
  mergeSection(props.profileVisionMission, fallbackVisionMission)
);
const identitySection = computed(() =>
  mergeSection(props.profileIdentity, fallbackIdentity)
);
const functionSection = computed(() =>
  mergeSection(props.profileFunction, fallbackFunction)
);
const cabinetLogoSection = computed(() =>
  mergeSection(props.profileCabinetLogo, fallbackCabinetLogo)
);

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
      value.text ||
      value.name ||
      value.description ||
      value.desc ||
      fallback
    );
  }

  return fallback;
};

const resolveArray = (items, fallback = []) =>
  Array.isArray(items) && items.length ? items : fallback;

const invalidUrlValues = new Set(["", "null", "undefined", "false", "nan"]);

const normalizeUrlValue = (value) => {
  if (value === null || value === undefined) return "";

  const normalized = String(value).trim();

  if (invalidUrlValues.has(normalized.toLowerCase())) return "";

  return normalized;
};

const resolveImageSource = (item) => {
  if (typeof item === "string") return normalizeUrlValue(item);

  return (
    normalizeUrlValue(item?.image) ||
    normalizeUrlValue(item?.image_url) ||
    normalizeUrlValue(item?.cover_url) ||
    normalizeUrlValue(item?.photo_url) ||
    normalizeUrlValue(item?.src) ||
    normalizeUrlValue(item?.url) ||
    ""
  );
};

const isExternalUrl = (url) => /^https?:\/\//i.test(normalizeUrlValue(url));

const getImageVariant = (item, keys = []) => {
  if (!item || typeof item !== "object") return "";

  return keys
    .map((key) => normalizeUrlValue(item?.[key]))
    .find(Boolean) || "";
};

const appendImageParams = (url, params = {}) => {
  const source = normalizeUrlValue(url);
  if (!source) return "";

  try {
    const parsedUrl = new URL(
      source,
      typeof window !== "undefined" ? window.location.origin : "http://localhost"
    );

    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") {
        parsedUrl.searchParams.set(key, value);
      }
    });

    if (!isExternalUrl(source)) {
      return `${parsedUrl.pathname}${parsedUrl.search}`;
    }

    return parsedUrl.toString();
  } catch (error) {
    return source;
  }
};

const buildUnsplashVariant = (url, width, height) =>
  appendImageParams(url, {
    w: width,
    h: height,
    fit: "crop",
    auto: "format",
    q: 72,
  });

const isUnsplashImage = (url) => /images\.unsplash\.com/i.test(String(url || ""));

const uniqueSrcset = (items = []) => {
  const seen = new Set();

  return items
    .filter(([url]) => typeof url === "string" && url.trim())
    .filter(([url, width]) => {
      const key = `${url}-${width}`;
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    })
    .map(([url, width]) => `${url} ${width}w`)
    .join(", ");
};

const normalizeHeroImage = (item, index) => {
  const fallback = fallbackProfileHeroImages[index] || fallbackProfileHeroImages[0];
  const original = resolveImageSource(item) || fallback?.image || "";
  const alt = toText(
    item?.alt || item?.alt_text || item?.title,
    fallback?.alt || `Foto profil HMPS RPL ${index + 1}`
  );

  if (isUnsplashImage(original)) {
    const isTall = index === 1 || index === 2;
    const small = buildUnsplashVariant(original, 360, isTall ? 520 : 300);
    const medium = buildUnsplashVariant(original, 560, isTall ? 760 : 420);
    const large = buildUnsplashVariant(original, 760, isTall ? 980 : 560);

    return {
      id: item?.id || `hero-image-${index}`,
      image: medium,
      srcset: uniqueSrcset([
        [small, 360],
        [medium, 560],
        [large, 760],
      ]),
      alt,
    };
  }

  const small = getImageVariant(item, [
    "image_sm",
    "imageSmall",
    "image_small",
    "small_url",
    "thumb_url",
    "thumbnail",
  ]);
  const medium = getImageVariant(item, [
    "image_md",
    "imageMedium",
    "image_medium",
    "medium_url",
    "optimized_url",
  ]);
  const large = getImageVariant(item, [
    "image_lg",
    "imageLarge",
    "image_large",
    "large_url",
  ]);

  return {
    id: item?.id || `hero-image-${index}`,
    image: medium || small || original,
    srcset: uniqueSrcset([
      [small, 480],
      [medium, 720],
      [large || original, 960],
    ]),
    alt,
  };
};

const ticks = computed(() =>
  resolveArray(props.ticks, fallbackTicks)
    .map((item) => toText(item))
    .filter(Boolean)
);

const periods = computed(() =>
  resolveArray(props.periods, fallbackPeriods).map((item, index) => {
    if (typeof item === "string") {
      return {
        id: `period-${index}`,
        periode: item,
        title: "Periode Kepengurusan",
        ketua: "",
        wakil: "",
        description: "Informasi detail periode kepengurusan akan ditampilkan di sini.",
      };
    }

    return {
      ...item,
      id: item?.id || `period-${index}`,
      periode: toText(
        item?.periode || item?.period,
        fallbackPeriods[index]?.periode || `Periode ${index + 1}`
      ),
      title: toText(
        item?.title || item?.name,
        fallbackPeriods[index]?.title || "Periode Kepengurusan"
      ),
      ketua: toText(item?.ketua || item?.leader || item?.chairman, "Belum diisi"),
      wakil: toText(item?.wakil || item?.vice || item?.vice_chairman, "Belum diisi"),
      description: toText(
        item?.description || item?.desc || item?.body,
        fallbackPeriods[index]?.description ||
          "Informasi periode kepengurusan akan ditampilkan di sini."
      ),
    };
  })
);

const normalizeMissionLines = (value) => {
  if (Array.isArray(value)) {
    return value
      .map((item) => toText(item))
      .filter(Boolean);
  }

  return String(value || "")
    .split(/\r?\n|\|/)
    .map((item) => item.trim())
    .filter(Boolean);
};

const missions = computed(() => {
  const meta = visionMission.value?.meta || {};
  const customMissions = normalizeMissionLines(
    meta.missions || meta.missions_text || meta.mission_text || meta.misi
  );

  if (customMissions.length) {
    return customMissions;
  }

  return resolveArray(props.missions, fallbackMissions)
    .map((item) => toText(item))
    .filter(Boolean);
});

const identityCards = computed(() =>
  resolveArray(props.identityCards, fallbackIdentityCards).map((item, index) => {
    if (typeof item === "string") {
      return {
        id: `identity-${index}`,
        title: item,
        desc:
          "Nilai organisasi HMPS RPL yang mendukung budaya kerja aktif, rapi, dan kolaboratif.",
      };
    }

    return {
      ...item,
      id: item?.id || `identity-${index}`,
      title: toText(
        item?.title || item?.label || item?.name,
        fallbackIdentityCards[index]?.title || "Nilai Organisasi"
      ),
      desc: toText(
        item?.desc || item?.description || item?.body,
        fallbackIdentityCards[index]?.desc ||
          "Informasi nilai organisasi akan ditampilkan di sini."
      ),
    };
  })
);

const functionPoints = computed(() =>
  resolveArray(props.functionPoints, fallbackFunctionPoints)
    .map((item) => toText(item))
    .filter(Boolean)
);

const heroImages = computed(() => {
  const uploadedImages = Array.isArray(props.profileHeroImages)
    ? props.profileHeroImages.filter((item) => item && resolveImageSource(item))
    : [];

  const mergedImages = [...uploadedImages, ...fallbackProfileHeroImages];

  return mergedImages.slice(0, 4).map((item, index) => normalizeHeroImage(item, index));
});

const cabinetLogoSource = computed(() => {
  return props.cabinetLogoImage && typeof props.cabinetLogoImage === "object"
    ? props.cabinetLogoImage
    : {};
});

const cabinetLogoData = computed(() => {
  const logo = cabinetLogoSource.value;
  const sectionMeta = cabinetLogoSection.value.meta || {};
  const logoMeta = logo.meta && typeof logo.meta === "object" ? logo.meta : {};
  const mergedMeta = {
    ...sectionMeta,
    ...logoMeta,
  };

  const cabinetName = toText(
    logo.cabinet_name || logo.cabinetName || mergedMeta.cabinet_name,
    "Raksa Devarya"
  );

  const sectionTitle = toText(
    logo.section_title || mergedMeta.section_title || cabinetLogoSection.value.title,
    fallbackCabinetLogo.meta.section_title
  );

  const sectionHighlight = toText(
    logo.section_highlight || mergedMeta.section_highlight || cabinetLogoSection.value.titleHighlight,
    cabinetName
  );

  const description = toText(
    logo.description || logo.meaning || mergedMeta.meaning || mergedMeta.short_meaning || cabinetLogoSection.value.description,
    fallbackCabinetLogo.description
  );

  return {
    id: logo.id || fallbackCabinetLogoImage.id,
    title: toText(logo.title || cabinetName, fallbackCabinetLogoImage.title),
    cabinetName,
    period: toText(logo.period || mergedMeta.period, "Periode 2025 / 2026"),
    description,
    sectionTitle,
    sectionHighlight,
    logoCaption: toText(
      logo.logo_caption || mergedMeta.logo_caption,
      fallbackCabinetLogo.meta.logo_caption
    ),
    logoTagline: toText(
      logo.logo_tagline || mergedMeta.logo_tagline || mergedMeta.tagline,
      fallbackCabinetLogo.meta.logo_tagline
    ),
    meaningBadge: toText(
      logo.meaning_title || mergedMeta.meaning_title,
      fallbackCabinetLogo.meta.meaning_title
    ),
    meaningHeadline: toText(
      logo.meaning_subtitle || mergedMeta.meaning_subtitle,
      fallbackCabinetLogo.meta.meaning_subtitle
    ),
    alt: toText(
      logo.alt || logo.alt_text || logo.title || cabinetName,
      fallbackCabinetLogoImage.alt
    ),
    image:
      resolveImageSource(props.cabinetLogoImage) ||
      normalizeUrlValue(mergedMeta.logo_image) ||
      fallbackCabinetLogoImage.image,
    meta: mergedMeta,
  };
});

const cabinetLogoImage = computed(() => ({
  id: cabinetLogoData.value.id,
  image: cabinetLogoData.value.image,
  alt: cabinetLogoData.value.alt,
  title: cabinetLogoData.value.title,
}));

const cabinetLogoMeaning = computed(() => cabinetLogoData.value.description);

const cabinetLogoDisplayTitle = computed(() => cabinetLogoData.value.sectionTitle);

const cabinetLogoDisplayHighlight = computed(() => cabinetLogoData.value.sectionHighlight);

const cabinetLogoCaption = computed(() => cabinetLogoData.value.logoCaption);

const cabinetMeaningBadge = computed(() => cabinetLogoData.value.meaningBadge);

const cabinetMeaningHeadline = computed(() => cabinetLogoData.value.meaningHeadline);

const cabinetLogoFacts = computed(() => [
  {
    label: "Nama Kabinet",
    value: cabinetLogoData.value.cabinetName,
  },
  {
    label: "Periode",
    value: cabinetLogoData.value.period,
  },
  {
    label: "Tagline",
    value: cabinetLogoData.value.logoTagline,
  },
]);

const primaryHeroImage = computed(() => heroImages.value[0]?.image || "");
const primaryHeroSrcset = computed(() => heroImages.value[0]?.srcset || "");
const seoDescription = computed(() =>
  toText(
    hero.value.description,
    "Profil HMPS Rekayasa Perangkat Lunak, sejarah organisasi, visi misi, dan nilai utama HMPS RPL."
  ).slice(0, 155)
);

const stats = computed(() => [
  {
    label: "Periode",
    value: `${periods.value.length}+`,
  },
  {
    label: "Misi Organisasi",
    value: `${missions.value.length}`,
  },
  {
    label: "Nilai Utama",
    value: `${identityCards.value.length}`,
  },
]);

const infoPoints = computed(() => [
  "Aspiratif",
  "Kolaboratif",
  "Profesional",
  "Inovatif",
]);

const tinyImagePlaceholder =
  "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 16 10%22%3E%3Crect width=%2216%22 height=%2210%22 fill=%22%23f8fafc%22/%3E%3C/svg%3E";

const scrollToSection = (id) => {
  const section = document.getElementById(id);

  if (section) {
    section.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
};
</script>

<template>
  <Head title="Profil HMPS RPL">
    <meta name="description" :content="seoDescription" />
    <meta
      name="keywords"
      content="Profil HMPS RPL, HMPS Rekayasa Perangkat Lunak, Himpunan Mahasiswa RPL, Politeknik Balekambang"
    />
    <meta property="og:title" content="Profil HMPS RPL" />
    <meta property="og:description" :content="seoDescription" />
    <meta property="og:type" content="website" />
    <meta name="robots" content="index, follow" />
    <link
      v-if="primaryHeroImage"
      rel="preload"
      as="image"
      :href="primaryHeroImage"
      :imagesrcset="primaryHeroSrcset || null"
      imagesizes="(max-width: 1024px) 280px, 340px"
      media="(min-width: 640px)"
      fetchpriority="high"
    />
  </Head>

  <div
    class="profil-mobile-page profil-logo-professional-page overflow-x-hidden bg-white font-['Plus_Jakarta_Sans',sans-serif] text-slate-950"
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
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 xl:gap-14">
          <div>
            <div
              class="mb-5 inline-flex max-w-full items-center gap-2 rounded-full border border-red-500/10 bg-white/80 px-3 py-2 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 shadow-[0_12px_35px_rgba(2,6,23,0.06)] backdrop-blur-xl sm:text-[0.75rem]"
            >
              <span
                class="h-2 w-2 shrink-0 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.10)]"
              />
              <span class="truncate">{{ hero.badge }}</span>
            </div>

            <h1
              class="max-w-2xl text-[2.35rem] font-black leading-[1.05] tracking-[-0.05em] text-slate-950 sm:text-[3.2rem] md:text-[3.8rem] xl:text-[4.8rem]"
            >
              {{ hero.title }}
              <span
                class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent sm:inline"
              >
                {{ hero.titleHighlight }}
              </span>
            </h1>

            <p
              class="mt-6 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-600 sm:text-[1rem] md:text-[1.03rem] lg:leading-[1.9]"
            >
              {{ hero.description }}
            </p>

            <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <button
                type="button"
                @click="scrollToSection('tentang')"
                class="group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:shadow-[0_18px_38px_rgba(220,38,38,0.24)] sm:min-h-[54px]"
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
                <span class="relative z-10">Tentang</span>
              </button>

              <button
                type="button"
                @click="scrollToSection('logo-kabinet')"
                class="group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl border border-white/10 bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:shadow-[0_18px_38px_rgba(220,38,38,0.24)] sm:min-h-[54px]"
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
                    d="M12 3l2.35 4.76 5.25.76-3.8 3.7.9 5.23L12 14.98l-4.7 2.47.9-5.23-3.8-3.7 5.25-.76L12 3z"
                  />
                </svg>
                Logo
              </button>

              <button
                type="button"
                @click="scrollToSection('sejarah')"
                class="group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl border border-white/10 bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:shadow-[0_18px_38px_rgba(220,38,38,0.24)] sm:min-h-[54px]"
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
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"
                  />
                </svg>
                Sejarah
              </button>

              <button
                type="button"
                @click="scrollToSection('visi-misi')"
                class="group relative inline-flex min-h-[52px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl border border-white/10 bg-[linear-gradient(135deg,#081120,#0f172a)] px-5 text-sm font-extrabold text-white shadow-[0_16px_34px_rgba(2,6,23,0.18)] transition-all duration-300 hover:-translate-y-[2px] hover:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] active:bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] active:shadow-[0_18px_38px_rgba(220,38,38,0.24)] sm:min-h-[54px]"
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
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
                Visi Misi
              </button>
            </div>

            <div class="mt-8 grid grid-cols-3 gap-2.5 sm:max-w-xl sm:gap-3">
              <div
                v-for="item in stats"
                :key="item.label"
                class="rounded-2xl border border-slate-900/5 bg-white/75 p-3 shadow-[0_12px_32px_rgba(2,6,23,0.05)] backdrop-blur-xl sm:p-4"
              >
                <p
                  class="text-[0.62rem] font-extrabold uppercase tracking-[0.08em] text-red-700 sm:text-[0.72rem]"
                >
                  {{ item.label }}
                </p>
                <h3
                  class="mt-2 text-lg font-black tracking-[-0.04em] text-slate-950 sm:text-2xl"
                >
                  {{ item.value }}
                </h3>
              </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2.5 sm:gap-3">
              <div
                v-for="(point, index) in infoPoints"
                :key="point"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3.5 py-2 text-xs font-bold text-slate-600 shadow-sm backdrop-blur-sm sm:px-4 sm:text-sm"
              >
                <span
                  class="h-2 w-2 rounded-full"
                  :class="index === 0 ? 'bg-red-500' : 'bg-slate-300'"
                />
                {{ point }}
              </div>
            </div>
          </div>

          <div
            class="relative mx-auto w-full max-w-[35rem] sm:max-w-[37rem] lg:mx-auto"
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
                🏛️
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ hero.meta?.floating_top_title }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ hero.meta?.floating_top_subtitle }}
                </div>
              </div>
            </div>

            <div
              class="sm:hidden rounded-[1.45rem] border border-slate-900/5 bg-white/85 p-4 shadow-[0_18px_46px_rgba(2,6,23,0.10)] backdrop-blur-xl"
              aria-label="Ringkasan profil HMPS RPL"
            >
              <div
                class="rounded-[1.15rem] bg-[linear-gradient(145deg,#0f172a,#111827_56%,#1e1b4b)] p-4 text-white shadow-[0_14px_34px_rgba(2,6,23,0.18)]"
              >
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p
                      class="text-[0.66rem] font-extrabold uppercase tracking-[0.16em] text-red-200"
                    >
                      Profil Organisasi
                    </p>
                    <h2 class="mt-2 text-[1.35rem] font-black leading-tight tracking-[-0.04em]">
                      HMPS Rekayasa Perangkat Lunak
                    </h2>
                  </div>
                  <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-xl ring-1 ring-white/15"
                    aria-hidden="true"
                  >
                    🏛️
                  </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-2">
                  <div
                    v-for="item in stats"
                    :key="`mobile-${item.label}`"
                    class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10"
                  >
                    <p class="text-[0.58rem] font-bold uppercase tracking-[0.08em] text-slate-300">
                      {{ item.label }}
                    </p>
                    <p class="mt-1 text-lg font-black tracking-[-0.04em] text-white">
                      {{ item.value }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-3 grid grid-cols-2 gap-2.5">
                <div
                  v-for="point in infoPoints"
                  :key="`mobile-info-${point}`"
                  class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-extrabold text-slate-700"
                >
                  <span class="mr-2 inline-block h-2 w-2 rounded-full bg-red-500" />
                  {{ point }}
                </div>
              </div>
            </div>

            <div
              class="hidden sm:block relative rounded-[1.65rem] border border-slate-900/5 bg-white/75 p-2.5 shadow-[0_24px_64px_rgba(2,6,23,0.12)] backdrop-blur-xl sm:rounded-[2rem] sm:p-4 lg:shadow-[0_28px_80px_rgba(2,6,23,0.14)]"
            >
              <div
                class="pointer-events-none absolute inset-0 rounded-[1.65rem] [background:linear-gradient(135deg,rgba(239,68,68,0.22),rgba(255,255,255,0.26),rgba(153,27,27,0.16))] [mask:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:exclude] p-px sm:rounded-[2rem]"
              />

              <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
                <div class="flex flex-col gap-2.5 pt-6 sm:gap-4 sm:pt-10 lg:pt-12">
                  <div
                    class="group h-32 overflow-hidden rounded-[1.2rem] bg-slate-100 shadow-[0_14px_32px_rgba(2,6,23,0.10)] sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 640px)"
                        :srcset="heroImages[0]?.srcset || heroImages[0]?.image"
                        sizes="(max-width: 1024px) 280px, 340px"
                      />
                      <img
                        :src="tinyImagePlaceholder"
                        :alt="heroImages[0]?.alt"
                        width="680"
                        height="460"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                      />
                    </picture>
                  </div>

                  <div
                    class="group h-48 overflow-hidden rounded-[1.2rem] bg-slate-100 shadow-[0_14px_32px_rgba(2,6,23,0.10)] sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 640px)"
                        :srcset="heroImages[1]?.srcset || heroImages[1]?.image"
                        sizes="(max-width: 1024px) 280px, 340px"
                      />
                      <img
                        :src="tinyImagePlaceholder"
                        :alt="heroImages[1]?.alt"
                        width="680"
                        height="920"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                      />
                    </picture>
                  </div>
                </div>

                <div class="flex flex-col gap-2.5 sm:gap-4">
                  <div
                    class="group h-48 overflow-hidden rounded-[1.2rem] bg-slate-100 shadow-[0_14px_32px_rgba(2,6,23,0.10)] sm:h-64 sm:rounded-[1.5rem] md:h-72"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 640px)"
                        :srcset="heroImages[2]?.srcset || heroImages[2]?.image"
                        sizes="(max-width: 1024px) 280px, 340px"
                      />
                      <img
                        :src="tinyImagePlaceholder"
                        :alt="heroImages[2]?.alt"
                        width="680"
                        height="920"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
                      />
                    </picture>
                  </div>

                  <div
                    class="group h-32 overflow-hidden rounded-[1.2rem] bg-slate-100 shadow-[0_14px_32px_rgba(2,6,23,0.10)] sm:h-40 sm:rounded-[1.5rem] md:h-44"
                  >
                    <picture class="block h-full w-full">
                      <source
                        media="(min-width: 640px)"
                        :srcset="heroImages[3]?.srcset || heroImages[3]?.image"
                        sizes="(max-width: 1024px) 280px, 340px"
                      />
                      <img
                        :src="tinyImagePlaceholder"
                        :alt="heroImages[3]?.alt"
                        width="680"
                        height="460"
                        class="block h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                        fetchpriority="low"
                        decoding="async"
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
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-rose-700 text-lg text-white"
              >
                🚀
              </span>
              <div>
                <div class="text-sm font-black leading-tight text-slate-950">
                  {{ hero.meta?.floating_bottom_title }}
                </div>
                <div class="text-xs font-semibold text-slate-500">
                  {{ hero.meta?.floating_bottom_subtitle }}
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

    <!-- Tentang -->
    <section id="tentang" class="cv-auto scroll-mt-24 bg-white py-16 sm:py-20 lg:py-24">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="grid grid-cols-1 items-start gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16"
        >
          <div data-aos="fade-up" data-aos-duration="800">
            <div
              class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              {{ about.badge }}
            </div>

            <h2
              class="max-w-3xl text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.8rem] xl:text-[3.2rem]"
            >
              {{ about.title }}
              <span
                class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ about.titleHighlight }}
              </span>
            </h2>

            <div
              class="mt-5 max-w-3xl space-y-4 text-justify text-[0.95rem] leading-[1.9] text-slate-600 sm:text-[1rem] lg:text-[1.04rem] lg:leading-[1.95]"
            >
              <p>{{ about.description }}</p>
              <p v-if="about.meta?.paragraph_2">{{ about.meta.paragraph_2 }}</p>
              <p v-if="about.meta?.paragraph_3">{{ about.meta.paragraph_3 }}</p>
            </div>
          </div>

          <div data-aos="fade-up" data-aos-delay="120" data-aos-duration="800">
            <div
              class="relative overflow-hidden rounded-[1.75rem] bg-[linear-gradient(160deg,#0b1220,#111827_58%,#1e1b4b)] p-5 text-white shadow-[0_22px_54px_rgba(2,6,23,0.18)] sm:p-6 lg:p-7"
            >
              <div
                class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-red-500/20 blur-[58px]"
              />
              <div
                class="pointer-events-none absolute -bottom-20 -left-16 h-48 w-48 rounded-full bg-white/10 blur-[70px]"
              />

              <div class="relative z-10">
                <div
                  class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-[10px] font-extrabold uppercase tracking-[0.14em] text-red-100 sm:px-4 sm:text-[11px]"
                >
                  {{ functionSection.badge }}
                </div>

                <h3
                  class="max-w-[28ch] text-[1.1rem] font-black leading-[1.45] tracking-[-0.02em] text-white sm:text-[1.35rem] lg:text-[1.42rem]"
                >
                  {{ about.meta?.function_title || functionSection.description }}
                </h3>

                <div class="mt-5 divide-y divide-white/10">
                  <div
                    v-for="point in functionPoints"
                    :key="point"
                    class="flex gap-3 py-4 first:pt-0 last:pb-0"
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
                    <p
                      class="text-justify text-sm leading-7 text-slate-300 sm:text-[0.95rem]"
                    >
                      {{ point }}
                    </p>
                  </div>
                </div>

                <button
                  type="button"
                  @click="scrollToSection('visi-misi')"
                  class="mt-6 inline-flex items-center gap-2 text-sm font-extrabold text-red-100 transition-colors hover:text-white sm:text-base"
                >
                  Lihat arah gerak organisasi
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
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Identity Cards -->
        <div class="mt-12" data-aos="fade-up" data-aos-duration="800">
          <div class="mb-6 max-w-3xl">
            <div
              class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              {{ identitySection.badge }}
            </div>

            <h2
              class="text-[1.8rem] font-black leading-[1.12] tracking-[-0.04em] text-slate-950 sm:text-[2.25rem] lg:text-[2.7rem]"
            >
              {{ identitySection.title }}
              <span
                class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
              >
                {{ identitySection.titleHighlight }}
              </span>
            </h2>

            <p
              class="mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem]"
            >
              {{ identitySection.description }}
            </p>
          </div>

          <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <article
              v-for="(item, index) in identityCards"
              :key="item.id || item.title"
              :data-aos-delay="index * 80"
              data-aos="fade-up"
              data-aos-duration="800"
              class="group rounded-[1.45rem] border border-slate-900/5 bg-white p-5 shadow-[0_14px_40px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-red-500/20 hover:shadow-[0_26px_60px_rgba(2,6,23,0.12)] sm:rounded-[1.6rem]"
            >
              <div
                class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-r from-red-500 to-red-700 text-white shadow-md shadow-red-500/20 transition-transform duration-300 group-hover:scale-105"
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
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>

              <h3 class="text-lg font-black tracking-[-0.02em] text-slate-950">
                {{ item.title }}
              </h3>
              <p class="mt-3 text-justify text-sm leading-7 text-slate-600">
                {{ item.desc }}
              </p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- Logo Kabinet -->
    <section
      id="logo-kabinet"
      class="cabinet-logo-professional cv-auto relative scroll-mt-24 overflow-hidden bg-[radial-gradient(circle_at_15%_8%,rgba(239,68,68,0.10),transparent_28%),radial-gradient(circle_at_86%_72%,rgba(15,23,42,0.07),transparent_32%),linear-gradient(180deg,#ffffff_0%,#fff7f7_54%,#ffffff_100%)] py-16 sm:py-20 lg:py-28"
    >
      <div
        class="pointer-events-none absolute left-0 top-0 h-40 w-40 -translate-x-1/2 rounded-full bg-red-500/10 blur-3xl sm:h-64 sm:w-64"
        aria-hidden="true"
      />
      <div
        class="pointer-events-none absolute bottom-10 right-0 h-44 w-44 translate-x-1/3 rounded-full bg-slate-900/10 blur-3xl sm:h-72 sm:w-72"
        aria-hidden="true"
      />

      <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-10 max-w-3xl lg:mb-14"
          data-aos="fade-up"
          data-aos-duration="800"
        >
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            {{ cabinetLogoSection.badge }}
          </div>

          <h2
            class="break-words text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 [overflow-wrap:anywhere] sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            {{ cabinetLogoDisplayTitle }}
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              {{ cabinetLogoDisplayHighlight }}
            </span>
          </h2>
        </div>

        <div
          class="cabinet-logo-layout grid grid-cols-1 gap-6 lg:grid-cols-[0.88fr_1.12fr] lg:gap-8 xl:gap-10"
        >
          <div data-aos="fade-right" data-aos-duration="850">
            <div
              class="cabinet-logo-card group relative overflow-hidden rounded-[2rem] border border-slate-900/5 bg-white p-5 shadow-[0_24px_70px_rgba(2,6,23,0.10)] sm:p-7 lg:p-8"
            >
              <div
                class="pointer-events-none absolute inset-x-0 top-0 h-1.5 bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
                aria-hidden="true"
              />
              <div
                class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-red-500/10 blur-3xl"
                aria-hidden="true"
              />
              <div
                class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-slate-900/10 blur-3xl"
                aria-hidden="true"
              />

              <div
                class="relative z-10 mx-auto flex aspect-square max-w-[23rem] items-center justify-center rounded-[1.7rem] border border-slate-200 bg-[linear-gradient(145deg,#fff7f7,#ffffff_44%,#f8fafc)] p-8 shadow-inner sm:max-w-[26rem] sm:p-10"
              >
                <div
                  class="absolute inset-4 rounded-[1.35rem] border border-dashed border-red-200/80"
                  aria-hidden="true"
                />
                <img
                  :src="cabinetLogoImage.image"
                  :alt="cabinetLogoImage.alt"
                  class="relative z-10 h-full max-h-[18rem] w-full object-contain transition-transform duration-700 group-hover:scale-[1.035] sm:max-h-[21rem]"
                  loading="lazy"
                  decoding="async"
                />
              </div>

              <div class="relative z-10 mt-6 text-center">
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.16em] text-red-700"
                >
                  {{ cabinetLogoCaption }}
                </p>
                <h3
                  class="mt-2 text-2xl font-black tracking-[-0.04em] text-slate-950 sm:text-3xl"
                >
                  {{ cabinetLogoData.cabinetName }}
                </h3>
                <p class="mt-2 text-sm font-semibold text-slate-500">
                  {{ cabinetLogoData.period }}
                </p>
              </div>
            </div>
          </div>

          <div data-aos="fade-left" data-aos-duration="850">
            <div
              class="cabinet-meaning-card relative h-full min-w-0 overflow-hidden rounded-[2rem] bg-[linear-gradient(160deg,#0b1220,#111827_58%,#450a0a)] p-5 text-white shadow-[0_24px_70px_rgba(2,6,23,0.18)] sm:p-7 lg:p-8"
            >
              <div
                class="pointer-events-none absolute -right-20 -top-20 h-60 w-60 rounded-full bg-red-500/20 blur-[70px]"
                aria-hidden="true"
              />
              <div
                class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-white/10 blur-[80px]"
                aria-hidden="true"
              />

              <div class="relative z-10">
                <div
                  class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-[0.68rem] font-black uppercase tracking-[0.14em] text-red-100 ring-1 ring-white/15"
                >
                  <span class="h-2 w-2 rounded-full bg-red-300" />
                  {{ cabinetMeaningBadge }}
                </div>

                <h3
                  class="mt-5 max-w-2xl break-words text-[1.55rem] font-black leading-tight tracking-[-0.04em] text-white [overflow-wrap:anywhere] sm:text-[2rem] lg:text-[2.35rem]"
                >
                  {{ cabinetMeaningHeadline }}
                </h3>

                <p
                  class="mt-5 break-words text-justify text-sm leading-8 text-slate-300 [overflow-wrap:anywhere] sm:text-base sm:leading-9"
                >
                  {{ cabinetLogoMeaning }}
                </p>

                <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">
                  <div
                    v-for="item in cabinetLogoFacts"
                    :key="item.label"
                    class="rounded-[1.25rem] border border-white/10 bg-white/[0.075] p-4 backdrop-blur-xl"
                  >
                    <p
                      class="text-[0.64rem] font-black uppercase tracking-[0.14em] text-red-200"
                    >
                      {{ item.label }}
                    </p>
                    <p class="mt-2 break-words text-sm font-extrabold leading-6 text-white [overflow-wrap:anywhere]">
                      {{ item.value }}
                    </p>
                  </div>
                </div>

                <div
                  class="mt-7 rounded-[1.35rem] border border-white/10 bg-white/[0.06] p-4 sm:p-5"
                >
                  <div class="flex items-start gap-3">
                    <span
                      class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-500/20 text-red-100 ring-1 ring-white/10"
                      aria-hidden="true"
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
                          stroke-width="2.35"
                          d="M12 3l2.35 4.76 5.25.76-3.8 3.7.9 5.23L12 14.98l-4.7 2.47.9-5.23-3.8-3.7 5.25-.76L12 3z"
                        />
                      </svg>
                    </span>

                    <p class="break-words text-justify text-sm leading-7 text-slate-300 [overflow-wrap:anywhere]">
                      Bagian ini disiapkan agar konten logo kabinet dapat dikelola dari
                      backend tanpa mengubah layout halaman profil.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Visi Misi -->
    <section
      id="visi-misi"
      class="cv-auto scroll-mt-24 bg-[linear-gradient(180deg,#fff_0%,#fafafa_100%)] py-16 sm:py-20 lg:py-28"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-10 max-w-3xl sm:mb-14 lg:mb-16"
          data-aos="fade-up"
          data-aos-duration="800"
        >
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            {{ visionMission.badge }}
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            {{ visionMission.title }}
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              {{ visionMission.titleHighlight }}
            </span>
          </h2>

          <p
            class="mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem] lg:text-[1.02rem]"
          >
            {{ visionMission.description }}
          </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[0.9fr_1.1fr] xl:gap-10">
          <div
            data-aos="fade-right"
            data-aos-duration="800"
            class="relative overflow-hidden rounded-[1.75rem] bg-[linear-gradient(160deg,#0b1220,#111827_58%,#1e1b4b)] p-6 text-white shadow-[0_22px_54px_rgba(2,6,23,0.18)] sm:p-8"
          >
            <div
              class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-red-500/20 blur-[58px]"
            />
            <h3 class="text-2xl font-black tracking-[-0.03em]">Visi</h3>

            <p class="mt-4 text-justify text-sm leading-8 text-slate-300 sm:text-base">
              {{ visionMission.meta?.vision }}
            </p>
          </div>

          <div
            data-aos="fade-left"
            data-aos-duration="800"
            class="rounded-[1.75rem] border border-slate-900/5 bg-white p-6 shadow-[0_14px_40px_rgba(2,6,23,0.06)] sm:p-8"
          >
            <h3 class="text-2xl font-black tracking-[-0.03em] text-slate-950">
              Misi
            </h3>

            <div class="mt-5 divide-y divide-slate-200">
              <div
                v-for="(mission, index) in missions"
                :key="`mission-${index}-${mission}`"
                :data-aos-delay="50 + index * 40"
                data-aos="fade-up"
                data-aos-duration="800"
                class="flex items-start gap-4 py-4 first:pt-0 last:pb-0"
              >
                <div
                  class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-red-500 to-red-700 text-sm font-black text-white shadow-md shadow-red-500/20"
                >
                  {{ index + 1 }}
                </div>
                <p
                  class="text-justify text-sm leading-8 text-slate-600 sm:text-[0.98rem]"
                >
                  {{ mission }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sejarah -->
    <section id="sejarah" class="cv-auto scroll-mt-24 bg-white py-16 sm:py-20 lg:py-24">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="mb-10 max-w-3xl sm:mb-14 lg:mb-16"
          data-aos="fade-up"
          data-aos-duration="800"
        >
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-2 text-[0.7rem] font-extrabold uppercase tracking-[0.08em] text-red-700"
          >
            <span class="h-2 w-2 rounded-full bg-red-500" />
            {{ historySection.badge }}
          </div>

          <h2
            class="text-[1.9rem] font-black leading-[1.1] tracking-[-0.04em] text-slate-950 sm:text-[2.35rem] md:text-[2.85rem] xl:text-[3.35rem]"
          >
            {{ historySection.title }}
            <span
              class="bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              {{ historySection.titleHighlight }}
            </span>
          </h2>

          <p
            class="mt-4 max-w-2xl text-justify text-[0.95rem] leading-[1.85] text-slate-500 sm:text-[1rem] lg:text-[1.02rem]"
          >
            {{ historySection.description }}
          </p>
        </div>

        <div class="relative">
          <div
            class="absolute bottom-2 left-4 top-2 hidden w-px bg-gradient-to-b from-red-300 via-red-100 to-transparent md:block"
          />

          <div class="space-y-5 sm:space-y-6">
            <article
              v-for="(item, index) in periods"
              :key="item.id || item.periode"
              :data-aos-delay="index * 80"
              data-aos="fade-up"
              data-aos-duration="800"
              class="relative overflow-hidden rounded-[1.55rem] border border-slate-900/5 bg-white shadow-[0_14px_40px_rgba(2,6,23,0.06)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_26px_60px_rgba(2,6,23,0.12)] md:ml-10"
            >
              <div
                class="absolute -left-[2.45rem] top-9 hidden h-5 w-5 rounded-full bg-gradient-to-r from-red-500 to-red-700 shadow-[0_0_0_8px_rgba(239,68,68,0.10)] md:block"
              />
              <div
                class="h-1.5 w-full bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)]"
              />

              <div class="grid grid-cols-1 lg:grid-cols-[1.08fr_.92fr]">
                <div class="p-5 sm:p-6 md:p-8 lg:border-r lg:border-slate-200">
                  <div
                    class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.12em] text-red-700 sm:px-4 sm:py-2 sm:text-[11px]"
                  >
                    {{ item.periode }}
                  </div>

                  <h3
                    class="mt-5 text-[1.35rem] font-black leading-snug tracking-[-0.03em] text-slate-950 sm:text-2xl"
                  >
                    {{ item.title }}
                  </h3>

                  <p
                    class="mt-4 text-justify text-sm leading-8 text-slate-600 sm:text-base"
                  >
                    {{ item.description }}
                  </p>

                  <div class="mt-6 flex flex-wrap gap-3">
                    <span
                      class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 sm:text-sm"
                    >
                      <span class="h-2 w-2 rounded-full bg-red-500" />
                      Periode Kepengurusan
                    </span>
                    <span
                      class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 sm:text-sm"
                    >
                      <span class="h-2 w-2 rounded-full bg-slate-400" />
                      HMPS RPL
                    </span>
                  </div>
                </div>

                <div
                  class="grid grid-cols-1 gap-4 border-t border-slate-200 p-5 sm:p-6 md:p-8 lg:border-t-0"
                >
                  <div
                    class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-5 transition duration-300 hover:bg-white hover:shadow-sm"
                  >
                    <div class="flex items-start gap-3">
                      <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-r from-red-500 to-red-700 text-white shadow-md shadow-red-500/20"
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
                            stroke-width="2.3"
                            d="M5.121 17.804A11.953 11.953 0 0112 15.5c2.577 0 4.965.81 6.879 2.304M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                          />
                        </svg>
                      </div>

                      <div class="min-w-0 flex-1">
                        <p
                          class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-red-700"
                        >
                          Ketua
                        </p>
                        <h4 class="mt-2 text-lg font-black text-slate-950">
                          {{ item.ketua || "Belum diisi" }}
                        </h4>
                        <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                          Memimpin arah organisasi dan pelaksanaan program kerja pada
                          periode ini.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div
                    class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-5 transition duration-300 hover:bg-white hover:shadow-sm"
                  >
                    <div class="flex items-start gap-3">
                      <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-r from-slate-800 to-slate-950 text-white shadow-md"
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
                            stroke-width="2.3"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"
                          />
                        </svg>
                      </div>

                      <div class="min-w-0 flex-1">
                        <p
                          class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-red-700"
                        >
                          Wakil Ketua
                        </p>
                        <h4 class="mt-2 text-lg font-black text-slate-950">
                          {{ item.wakil || "Belum diisi" }}
                        </h4>
                        <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                          Mendampingi ketua dan mendukung koordinasi antar divisi dan
                          kegiatan organisasi.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </article>

            <div
              v-if="!periods.length"
              class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
            >
              <h3 class="text-xl font-black text-slate-950">
                Belum ada sejarah kepengurusan
              </h3>
              <p
                class="mx-auto mt-3 max-w-xl text-justify text-sm leading-7 text-slate-500"
              >
                Tambahkan data sejarah melalui database atau halaman admin profil.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.cv-auto {
  content-visibility: auto;
  contain-intrinsic-size: 1px 920px;
}

.profil-mobile-page img {
  max-width: 100%;
  height: auto;
}

.profil-mobile-page img.block.h-full.w-full.object-cover {
  height: 100%;
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


.profil-mobile-page :is(a, button):focus-visible {
  outline: 3px solid rgba(239, 68, 68, 0.45);
  outline-offset: 3px;
}

@media (max-width: 639px) {
  .profil-mobile-page [data-aos] {
    transition-duration: 420ms !important;
  }
}
@media (prefers-reduced-motion: reduce) {
  .ticker-inner,
  .floating-badge,
  .profil-mobile-page [data-aos] {
    animation: none !important;
    transition-duration: 1ms !important;
  }
}
</style>

<style scoped>
/* Profil Mobile Professional — frontend-only, mobile-first refinements */
@media (max-width: 639px) {
  .profil-mobile-page,
  .profil-mobile-page * {
    box-sizing: border-box;
  }

  .profil-mobile-page {
    width: 100%;
    max-width: 100%;
    overflow-x: clip;
    background: #ffffff;
  }

  .profil-mobile-page :is(section, article, div, aside, form, main) {
    min-width: 0;
  }

  .profil-mobile-page :is(button, a, input, select, textarea, img, label) {
    max-width: 100%;
  }

  .profil-mobile-page :is(button, a) {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
  }

  .profil-mobile-page p {
    text-align: justify !important;
    text-align-last: left;
    overflow-wrap: break-word;
    hyphens: auto;
  }

  .profil-mobile-page h1 {
    font-size: clamp(2rem, 10vw, 2.45rem) !important;
    line-height: 1.04 !important;
    letter-spacing: -0.055em !important;
  }

  .profil-mobile-page h2 {
    font-size: clamp(1.55rem, 7.2vw, 1.9rem) !important;
    line-height: 1.1 !important;
    letter-spacing: -0.048em !important;
  }

  .profil-mobile-page h3 {
    font-size: 1.12rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .profil-mobile-page button {
    border-radius: 1rem !important;
  }

  /* Hero */
  .profil-mobile-page section:first-of-type {
    padding-top: 5.55rem !important;
    padding-bottom: 2.6rem !important;
  }

  .profil-mobile-page section:first-of-type .mx-auto.w-full.max-w-7xl {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .profil-mobile-page section:first-of-type .grid.grid-cols-1.items-center {
    gap: 1.45rem !important;
  }

  .profil-mobile-page section:first-of-type .mb-5.inline-flex {
    margin-bottom: 1rem !important;
    max-width: 100% !important;
    padding: 0.55rem 0.78rem !important;
    border-radius: 999px !important;
    font-size: 0.62rem !important;
    letter-spacing: 0.06em !important;
    box-shadow: 0 10px 26px rgba(2, 6, 23, 0.055) !important;
  }

  .profil-mobile-page section:first-of-type p.mt-6 {
    margin-top: 1rem !important;
    font-size: 0.9rem !important;
    line-height: 1.72 !important;
  }

  .profil-mobile-page section:first-of-type .mt-8.grid.grid-cols-1 {
    margin-top: 1.25rem !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 0.72rem !important;
  }

  .profil-mobile-page section:first-of-type .mt-8.grid.grid-cols-1 button {
    min-height: 48px !important;
    border-radius: 1rem !important;
    font-size: 0.86rem !important;
    padding-inline: 1rem !important;
  }

  .profil-mobile-page section:first-of-type .mt-8.grid.grid-cols-3 {
    margin-top: 1.25rem !important;
    gap: 0.55rem !important;
  }

  .profil-mobile-page section:first-of-type .mt-8.grid.grid-cols-3 > div {
    padding: 0.7rem !important;
    border-radius: 1rem !important;
  }

  .profil-mobile-page section:first-of-type .mt-8.grid.grid-cols-3 p {
    font-size: 0.56rem !important;
    line-height: 1.25 !important;
    letter-spacing: 0.055em !important;
  }

  .profil-mobile-page section:first-of-type .mt-8.grid.grid-cols-3 h3 {
    margin-top: 0.35rem !important;
    font-size: 1.12rem !important;
    line-height: 1.05 !important;
  }

  .profil-mobile-page section:first-of-type .mt-6.flex.flex-wrap {
    margin-top: 1rem !important;
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 0.55rem !important;
    overflow-x: auto !important;
    padding-bottom: 0.35rem !important;
    -webkit-overflow-scrolling: touch;
  }

  .profil-mobile-page section:first-of-type .mt-6.flex.flex-wrap > div {
    flex: 0 0 auto !important;
    padding: 0.5rem 0.75rem !important;
    font-size: 0.78rem !important;
  }

  /* Hero image collage */
  .profil-mobile-page section:first-of-type .relative.mx-auto.w-full.max-w-\[35rem\] {
    max-width: 100% !important;
  }

  .profil-mobile-page section:first-of-type .relative.rounded-\[1\.65rem\] {
    border-radius: 1.2rem !important;
    padding: 0.65rem !important;
    box-shadow: 0 16px 42px rgba(2, 6, 23, 0.1) !important;
  }

  .profil-mobile-page section:first-of-type .grid.grid-cols-2.gap-2\.5 {
    gap: 0.55rem !important;
  }

  .profil-mobile-page section:first-of-type .flex.flex-col.gap-2\.5 {
    gap: 0.55rem !important;
  }

  .profil-mobile-page section:first-of-type .h-32 {
    height: 7.5rem !important;
  }

  .profil-mobile-page section:first-of-type .h-48 {
    height: 10.25rem !important;
  }

  .profil-mobile-page section:first-of-type .group.h-32,
  .profil-mobile-page section:first-of-type .group.h-48 {
    border-radius: 0.95rem !important;
    box-shadow: 0 10px 24px rgba(2, 6, 23, 0.1) !important;
  }

  /* Ticker */
  .profil-mobile-page .ticker-inner span {
    margin-left: 1rem !important;
    margin-right: 1rem !important;
    font-size: 0.64rem !important;
    letter-spacing: 0.12em !important;
  }

  /* General section rhythm */
  .profil-mobile-page section:not(:first-of-type) {
    padding-top: 3.25rem !important;
    padding-bottom: 3.25rem !important;
  }

  .profil-mobile-page section:not(:first-of-type) .mx-auto.max-w-7xl {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .profil-mobile-page section:not(:first-of-type) .mb-10,
  .profil-mobile-page section:not(:first-of-type) .mb-12,
  .profil-mobile-page section:not(:first-of-type) .mb-6 {
    margin-bottom: 1.55rem !important;
  }

  .profil-mobile-page section:not(:first-of-type) .mb-5.inline-flex,
  .profil-mobile-page section:not(:first-of-type) .mb-4.inline-flex {
    margin-bottom: 1rem !important;
    padding: 0.55rem 0.78rem !important;
    font-size: 0.62rem !important;
    letter-spacing: 0.07em !important;
  }

  .profil-mobile-page section:not(:first-of-type) h2 span,
  .profil-mobile-page section:not(:first-of-type) h3 span {
    display: inline !important;
  }

  .profil-mobile-page section:not(:first-of-type) .mt-4,
  .profil-mobile-page section:not(:first-of-type) .mt-5 {
    margin-top: 0.85rem !important;
  }

  .profil-mobile-page section:not(:first-of-type) p {
    font-size: 0.9rem !important;
    line-height: 1.68 !important;
  }

  /* Tentang */
  .profil-mobile-page #tentang {
    padding-top: 3.25rem !important;
    padding-bottom: 3.25rem !important;
  }

  .profil-mobile-page #tentang .grid.grid-cols-1.items-start {
    gap: 1.45rem !important;
  }

  .profil-mobile-page #tentang .mt-5.max-w-3xl.space-y-4 {
    margin-top: 1rem !important;
    gap: 0.7rem !important;
  }

  .profil-mobile-page #tentang .relative.overflow-hidden.rounded-\[1\.75rem\] {
    border-radius: 1.2rem !important;
    padding: 1rem !important;
    box-shadow: 0 16px 42px rgba(2, 6, 23, 0.18) !important;
  }

  .profil-mobile-page #tentang .relative.overflow-hidden.rounded-\[1\.75rem\] h3 {
    font-size: 1.08rem !important;
    line-height: 1.42 !important;
  }

  .profil-mobile-page #tentang .mt-5.divide-y {
    margin-top: 1rem !important;
  }

  .profil-mobile-page #tentang .mt-5.divide-y > div {
    padding-top: 0.85rem !important;
    padding-bottom: 0.85rem !important;
    gap: 0.75rem !important;
  }

  .profil-mobile-page #tentang .mt-5.divide-y .h-8.w-8 {
    width: 2rem !important;
    height: 2rem !important;
    border-radius: 0.75rem !important;
  }

  .profil-mobile-page #tentang button.mt-6 {
    margin-top: 1rem !important;
    min-height: 44px !important;
    width: 100% !important;
    justify-content: center !important;
    border-radius: 1rem !important;
    background: rgba(255, 255, 255, 0.08) !important;
    padding: 0.75rem 1rem !important;
  }

  .profil-mobile-page #tentang .mt-12 {
    margin-top: 2rem !important;
  }

  .profil-mobile-page #tentang .grid.grid-cols-1.gap-5 {
    gap: 0.95rem !important;
  }

  .profil-mobile-page #tentang article {
    border-radius: 1.15rem !important;
    padding: 1rem !important;
    box-shadow: 0 12px 30px rgba(2, 6, 23, 0.06) !important;
  }

  .profil-mobile-page #tentang article .mb-4.inline-flex.h-11.w-11 {
    width: 2.65rem !important;
    height: 2.65rem !important;
    margin-bottom: 0.85rem !important;
    border-radius: 0.95rem !important;
  }

  .profil-mobile-page #tentang article h3 {
    font-size: 1.03rem !important;
  }

  .profil-mobile-page #tentang article p {
    margin-top: 0.65rem !important;
    font-size: 0.86rem !important;
  }

  /* Visi Misi */
  .profil-mobile-page #visi-misi {
    padding-top: 3.25rem !important;
    padding-bottom: 3.25rem !important;
  }

  .profil-mobile-page #visi-misi .grid.grid-cols-1.gap-6 {
    gap: 1rem !important;
  }

  .profil-mobile-page #visi-misi .relative.overflow-hidden.rounded-\[1\.75rem\],
  .profil-mobile-page #visi-misi .rounded-\[1\.75rem\].border {
    border-radius: 1.2rem !important;
    padding: 1rem !important;
    box-shadow: 0 14px 36px rgba(2, 6, 23, 0.12) !important;
  }

  .profil-mobile-page #visi-misi .inline-flex.h-12.w-12 {
    width: 2.85rem !important;
    height: 2.85rem !important;
    border-radius: 1rem !important;
  }

  .profil-mobile-page #visi-misi h3 {
    margin-top: 1rem !important;
    font-size: 1.25rem !important;
  }

  .profil-mobile-page #visi-misi .mt-5.divide-y {
    margin-top: 1rem !important;
  }

  .profil-mobile-page #visi-misi .mt-5.divide-y > div {
    padding-top: 0.85rem !important;
    padding-bottom: 0.85rem !important;
    gap: 0.75rem !important;
  }

  .profil-mobile-page #visi-misi .mt-5.divide-y .h-8.w-8 {
    width: 2rem !important;
    height: 2rem !important;
    border-radius: 0.75rem !important;
  }

  /* Sejarah */
  .profil-mobile-page #sejarah {
    padding-top: 3.25rem !important;
    padding-bottom: 3.25rem !important;
  }

  .profil-mobile-page #sejarah .space-y-5 > article {
    border-radius: 1.18rem !important;
    box-shadow: 0 14px 34px rgba(2, 6, 23, 0.065) !important;
  }

  .profil-mobile-page #sejarah article .grid.grid-cols-1 {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .profil-mobile-page #sejarah article .p-5 {
    padding: 1rem !important;
  }

  .profil-mobile-page #sejarah article .inline-flex.items-center.gap-2.rounded-full {
    padding: 0.45rem 0.75rem !important;
    font-size: 0.6rem !important;
    letter-spacing: 0.08em !important;
  }

  .profil-mobile-page #sejarah article h3 {
    margin-top: 1rem !important;
    font-size: 1.2rem !important;
  }

  .profil-mobile-page #sejarah article p {
    margin-top: 0.75rem !important;
    font-size: 0.86rem !important;
    line-height: 1.68 !important;
  }

  .profil-mobile-page #sejarah article .mt-6.flex.flex-wrap {
    margin-top: 1rem !important;
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 0.5rem !important;
    overflow-x: auto !important;
    padding-bottom: 0.35rem !important;
    -webkit-overflow-scrolling: touch;
  }

  .profil-mobile-page #sejarah article .mt-6.flex.flex-wrap > span {
    flex: 0 0 auto !important;
    padding: 0.5rem 0.75rem !important;
    font-size: 0.75rem !important;
  }

  .profil-mobile-page #sejarah article .grid.grid-cols-1.gap-4.border-t {
    gap: 0.8rem !important;
    padding: 1rem !important;
  }

  .profil-mobile-page #sejarah article .rounded-\[1\.25rem\].border {
    padding: 0.85rem !important;
    border-radius: 1rem !important;
  }

  .profil-mobile-page #sejarah article .rounded-\[1\.25rem\].border .flex.items-start {
    gap: 0.75rem !important;
  }

  .profil-mobile-page #sejarah article .flex.h-11.w-11 {
    width: 2.65rem !important;
    height: 2.65rem !important;
    border-radius: 0.95rem !important;
  }

  .profil-mobile-page #sejarah article h4 {
    margin-top: 0.55rem !important;
    font-size: 1rem !important;
    line-height: 1.2 !important;
    overflow-wrap: anywhere;
  }

  /* Responsive utility overrides */
  .profil-mobile-page .grid.lg\:grid-cols-2,
  .profil-mobile-page .grid.lg\:grid-cols-\[0\.9fr_1\.1fr\],
  .profil-mobile-page .grid.lg\:grid-cols-\[1\.08fr_\.92fr\],
  .profil-mobile-page .grid.sm\:grid-cols-2,
  .profil-mobile-page .grid.sm\:grid-cols-3,
  .profil-mobile-page .grid.xl\:grid-cols-4 {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .profil-mobile-page .rounded-\[1\.2rem\],
  .profil-mobile-page .rounded-\[1\.25rem\],
  .profil-mobile-page .rounded-\[1\.45rem\],
  .profil-mobile-page .rounded-\[1\.55rem\],
  .profil-mobile-page .rounded-\[1\.6rem\],
  .profil-mobile-page .rounded-\[1\.65rem\],
  .profil-mobile-page .rounded-\[1\.75rem\],
  .profil-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .profil-mobile-page .truncate {
    min-width: 0;
  }

  .profil-mobile-page .line-clamp-2,
  .profil-mobile-page .line-clamp-3,
  .profil-mobile-page .line-clamp-6 {
    display: block !important;
    -webkit-line-clamp: unset !important;
    overflow: visible !important;
  }

  .profil-mobile-page .break-all,
  .profil-mobile-page .break-words,
  .profil-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }
}

@media (prefers-reduced-motion: reduce) {
  .profil-mobile-page *,
  .profil-mobile-page *::before,
  .profil-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}


/* Logo kabinet: cegah teks custom panjang merusak layout */
#logo-kabinet,
#logo-kabinet * {
  min-width: 0;
}

#logo-kabinet :is(p, h2, h3, span, div) {
  overflow-wrap: anywhere;
  word-break: break-word;
}

#logo-kabinet img {
  max-width: 100%;
}
</style>


<style scoped>
/* Professional Logo Kabinet UI/UX polish
   Scope aman: hanya memperbaiki tampilan section Logo Kabinet pada halaman Profil. */
.profil-logo-professional-page .cabinet-logo-professional {
  position: relative;
  isolation: isolate;
}

.profil-logo-professional-page .cabinet-logo-professional::before {
  content: "";
  pointer-events: none;
  position: absolute;
  inset: 0;
  z-index: 0;
  background:
    radial-gradient(circle at 14% 18%, rgba(239, 68, 68, 0.08), transparent 26rem),
    radial-gradient(circle at 82% 18%, rgba(15, 23, 42, 0.055), transparent 28rem),
    linear-gradient(180deg, rgba(255,255,255,0.96), rgba(255,247,247,0.70) 48%, #ffffff 100%);
}

.profil-logo-professional-page .cabinet-logo-professional > .relative {
  position: relative;
  z-index: 2;
}

.profil-logo-professional-page .cabinet-logo-professional h2 {
  text-wrap: balance;
}

.profil-logo-professional-page .cabinet-logo-layout {
  align-items: stretch;
}

.profil-logo-professional-page .cabinet-logo-card,
.profil-logo-professional-page .cabinet-meaning-card {
  border-radius: 2.15rem !important;
}

.profil-logo-professional-page .cabinet-logo-card {
  background:
    radial-gradient(circle at 50% 0%, rgba(239, 68, 68, 0.08), transparent 42%),
    linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96)) !important;
  box-shadow: 0 24px 68px rgba(15, 23, 42, 0.10) !important;
}

.profil-logo-professional-page .cabinet-logo-card::after {
  content: "";
  pointer-events: none;
  position: absolute;
  inset: 0;
  border-radius: inherit;
  border: 1px solid rgba(255, 255, 255, 0.78);
}

.profil-logo-professional-page .cabinet-logo-card .aspect-square {
  background:
    radial-gradient(circle at 50% 42%, rgba(239, 68, 68, 0.055), transparent 48%),
    linear-gradient(145deg, #ffffff 0%, #fff7f7 52%, #f8fafc 100%) !important;
  border-color: rgba(226, 232, 240, 0.92) !important;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,0.92),
    inset 0 -16px 34px rgba(15,23,42,0.035),
    0 18px 45px rgba(15,23,42,0.07) !important;
}

.profil-logo-professional-page .cabinet-logo-card img {
  filter: drop-shadow(0 18px 30px rgba(15, 23, 42, 0.10));
}

.profil-logo-professional-page .cabinet-logo-card h3 {
  text-wrap: balance;
}

.profil-logo-professional-page .cabinet-meaning-card {
  background:
    radial-gradient(circle at 100% 0%, rgba(239, 68, 68, 0.20), transparent 30%),
    radial-gradient(circle at 0% 100%, rgba(255,255,255,0.10), transparent 34%),
    linear-gradient(145deg, #080f1f 0%, #111827 54%, #4c0b12 100%) !important;
  box-shadow: 0 28px 78px rgba(2, 6, 23, 0.24) !important;
}

.profil-logo-professional-page .cabinet-meaning-card::after {
  content: "";
  pointer-events: none;
  position: absolute;
  inset: 0;
  border-radius: inherit;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.profil-logo-professional-page .cabinet-meaning-card h3 {
  text-wrap: balance;
}

.profil-logo-professional-page .cabinet-meaning-card p {
  letter-spacing: 0.005em;
}

.profil-logo-professional-page .cabinet-meaning-card .grid > div {
  background: rgba(255, 255, 255, 0.075) !important;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
}

.profil-logo-professional-page .cabinet-meaning-card .grid > div:hover {
  background: rgba(255, 255, 255, 0.105) !important;
}

@media (min-width: 1024px) {
  .profil-logo-professional-page .cabinet-logo-professional {
    padding-top: 7rem !important;
    padding-bottom: 7rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-layout {
    grid-template-columns: minmax(22rem, 0.86fr) minmax(0, 1.14fr) !important;
    gap: 2.25rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-card {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .profil-logo-professional-page .cabinet-logo-card .aspect-square {
    max-width: 24rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card {
    display: flex;
    align-items: center;
    min-height: 100%;
  }

  .profil-logo-professional-page .cabinet-meaning-card > .relative {
    width: 100%;
  }

  .profil-logo-professional-page .cabinet-meaning-card p.mt-5 {
    max-width: 68ch;
    font-size: 0.98rem !important;
    line-height: 2.05 !important;
  }
}

@media (min-width: 1280px) {
  .profil-logo-professional-page .cabinet-logo-layout {
    gap: 2.75rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .aspect-square {
    max-width: 25.5rem !important;
  }
}

@media (max-width: 1023px) {
  .profil-logo-professional-page .cabinet-logo-professional {
    padding-top: 4.5rem !important;
    padding-bottom: 4.5rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-layout {
    gap: 1.25rem !important;
  }
}

@media (max-width: 639px) {
  .profil-logo-professional-page .cabinet-logo-professional {
    padding-top: 2.75rem !important;
    padding-bottom: 3rem !important;
    background:
      radial-gradient(circle at 16% 4%, rgba(239,68,68,0.085), transparent 16rem),
      linear-gradient(180deg, #ffffff 0%, #fff8f8 52%, #ffffff 100%) !important;
  }

  .profil-logo-professional-page .cabinet-logo-professional > .relative {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-professional .mb-10 {
    margin-bottom: 1.25rem !important;
    text-align: left !important;
  }

  .profil-logo-professional-page .cabinet-logo-professional h2 {
    font-size: clamp(1.55rem, 8vw, 2.08rem) !important;
    line-height: 1.08 !important;
    letter-spacing: -0.048em !important;
  }

  .profil-logo-professional-page .cabinet-logo-card,
  .profil-logo-professional-page .cabinet-meaning-card {
    border-radius: 1.25rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-card {
    padding: 0.9rem !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.075) !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .aspect-square {
    max-width: 100% !important;
    border-radius: 1rem !important;
    padding: 1.15rem !important;
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,0.92),
      0 10px 28px rgba(15,23,42,0.06) !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .aspect-square > div {
    inset: 0.75rem !important;
    border-radius: 0.9rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-card img {
    max-height: 13.5rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .mt-6 {
    margin-top: 1.15rem !important;
  }

  .profil-logo-professional-page .cabinet-logo-card p {
    font-size: 0.62rem !important;
    line-height: 1.35 !important;
    text-align: center !important;
    letter-spacing: 0.13em !important;
  }

  .profil-logo-professional-page .cabinet-logo-card h3 {
    font-size: 1.2rem !important;
    line-height: 1.18 !important;
    margin-top: 0.45rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card {
    padding: 1rem !important;
    box-shadow: 0 16px 38px rgba(2, 6, 23, 0.22) !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .inline-flex {
    max-width: 100%;
    font-size: 0.58rem !important;
    letter-spacing: 0.105em !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card h3 {
    margin-top: 1rem !important;
    font-size: 1.28rem !important;
    line-height: 1.18 !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card p.mt-5 {
    margin-top: 0.85rem !important;
    font-size: 0.84rem !important;
    line-height: 1.82 !important;
    text-align: justify !important;
    text-align-last: left;
  }

  .profil-logo-professional-page .cabinet-meaning-card .mt-7 {
    margin-top: 1rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .grid {
    grid-template-columns: 1fr !important;
    gap: 0.65rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .grid > div {
    border-radius: 0.95rem !important;
    padding: 0.8rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .grid > div p:first-child {
    font-size: 0.56rem !important;
    letter-spacing: 0.12em !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .grid > div p:last-child {
    font-size: 0.82rem !important;
    line-height: 1.45 !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .rounded-\[1\.35rem\] {
    margin-top: 1rem !important;
    border-radius: 1rem !important;
    padding: 0.9rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .rounded-\[1\.35rem\] .flex {
    gap: 0.75rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .rounded-\[1\.35rem\] span {
    width: 2.35rem !important;
    height: 2.35rem !important;
    border-radius: 0.85rem !important;
  }

  .profil-logo-professional-page .cabinet-meaning-card .rounded-\[1\.35rem\] p {
    font-size: 0.82rem !important;
    line-height: 1.65 !important;
  }

  .profil-logo-professional-page .cabinet-logo-card:hover img,
  .profil-logo-professional-page .cabinet-meaning-card:hover {
    transform: none !important;
  }

  .profil-logo-professional-page .cabinet-logo-professional [class*="blur-"],
  .profil-logo-professional-page .cabinet-logo-professional [class*="backdrop-blur"] {
    filter: none !important;
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
  }
}
</style>


<style scoped>
/* Mobile-only fix:
   Menghapus ornamen lingkaran/blob yang mengganggu di HP dan membuat identitas logo tetap center.
   Desktop/laptop tidak tersentuh. */
@media (max-width: 639px) {
  .profil-logo-professional-page .cabinet-logo-card > .pointer-events-none.absolute.-right-16,
  .profil-logo-professional-page .cabinet-logo-card > .pointer-events-none.absolute.-bottom-16,
  .profil-logo-professional-page .cabinet-meaning-card > .pointer-events-none.absolute.-right-20,
  .profil-logo-professional-page .cabinet-meaning-card > .pointer-events-none.absolute.-bottom-24,
  .profil-logo-professional-page .cabinet-logo-professional > .pointer-events-none.absolute {
    display: none !important;
  }

  .profil-logo-professional-page .cabinet-logo-card {
    background: linear-gradient(180deg, #ffffff 0%, #fff7f7 48%, #ffffff 100%) !important;
  }

  .profil-logo-professional-page .cabinet-logo-card::before,
  .profil-logo-professional-page .cabinet-logo-card::after {
    opacity: 0 !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .relative.z-10.mt-6.text-center {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    width: 100% !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .relative.z-10.mt-6.text-center p,
  .profil-logo-professional-page .cabinet-logo-card .relative.z-10.mt-6.text-center h3 {
    width: 100% !important;
    text-align: center !important;
    text-align-last: center !important;
    margin-left: auto !important;
    margin-right: auto !important;
  }

  .profil-logo-professional-page .cabinet-logo-card .relative.z-10.mt-6.text-center p:last-child {
    display: block !important;
    text-align: center !important;
    letter-spacing: 0.14em !important;
  }
}
</style>


<style scoped>
/* Hide only the large Visi/Misi heading icons.
   Nomor misi 1, 2, 3, 4 tetap tampil. */
@media (min-width: 0px) {
  .profil-logo-professional-page #visi-misi > div > .grid > div:first-child > div.inline-flex.h-12.w-12,
  .profil-logo-professional-page #visi-misi > div > .grid > div:nth-child(2) > div.inline-flex.h-12.w-12 {
    display: none !important;
  }

  .profil-logo-professional-page #visi-misi > div > .grid > div:first-child > h3,
  .profil-logo-professional-page #visi-misi > div > .grid > div:nth-child(2) > h3 {
    margin-top: 0 !important;
  }
}
</style>


<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

// Salam dinamis
const greeting = computed(() => {
  const h = new Date().getHours();
  if (h < 11) return "Selamat Pagi";
  if (h < 15) return "Selamat Siang";
  if (h < 18) return "Selamat Sore";
  return "Selamat Malam";
});

const todayLabel = computed(() => {
  return new Date().toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
});

const quickActions = [
  {
    title: "Custom Home",
    desc: "Atur hero, about, program kerja, ticker, highlight, dan media beranda.",
    href: "/admin/home",
    icon: "home",
    badge: "Utama",
    color: "red",
  },
  {
    title: "Custom Profil",
    desc: "Kelola profil organisasi, sejarah, visi misi, dan identitas HMPS RPL.",
    href: "/admin/profil",
    icon: "profile",
    badge: "Profil",
    color: "violet",
  },
  {
    title: "Custom Kepengurusan",
    desc: "Kelola struktur pengurus inti, divisi, koordinator, dan anggota divisi.",
    href: "/admin/kepengurusan",
    icon: "users",
    badge: "Pengurus",
    color: "blue",
  },
  {
    title: "Custom Program Kerja",
    desc: "Atur daftar program kerja, kategori, anggaran, tujuan, dan manfaat.",
    href: "/admin/program-kerja",
    icon: "briefcase",
    badge: "Proker",
    color: "amber",
  },
  {
    title: "Custom Dokumentasi",
    desc: "Kelola dokumentasi kegiatan, galeri foto, kategori, dan arsip tahunan.",
    href: "/admin/dokumentasi",
    icon: "gallery",
    badge: "Galeri",
    color: "emerald",
  },
  {
    title: "Custom Layanan Jasa",
    desc: "Kelola layanan, paket jasa, harga, testimoni, dan alur konsultasi.",
    href: "/admin/layanan-jasa",
    icon: "service",
    badge: "Layanan",
    color: "orange",
  },
  {
    title: "Custom Konsultasi",
    desc: "Atur admin konsultasi, jadwal layanan, jam aktif, dan nomor WhatsApp.",
    href: "/admin/konsultasi",
    icon: "chat",
    badge: "Konsultasi",
    color: "teal",
  },
];

// Peta Tailwind per warna card
const colorMap = {
  red: {
    icon: "bg-red-50 text-red-600 ring-red-100",
    badge: "bg-red-50 text-red-700 ring-red-100",
    arrow: "text-red-600",
    glow: "from-red-500/10",
  },
  violet: {
    icon: "bg-violet-50 text-violet-600 ring-violet-100",
    badge: "bg-violet-50 text-violet-700 ring-violet-100",
    arrow: "text-violet-600",
    glow: "from-violet-500/10",
  },
  blue: {
    icon: "bg-blue-50 text-blue-600 ring-blue-100",
    badge: "bg-blue-50 text-blue-700 ring-blue-100",
    arrow: "text-blue-600",
    glow: "from-blue-500/10",
  },
  amber: {
    icon: "bg-amber-50 text-amber-600 ring-amber-100",
    badge: "bg-amber-50 text-amber-700 ring-amber-100",
    arrow: "text-amber-600",
    glow: "from-amber-500/10",
  },
  emerald: {
    icon: "bg-emerald-50 text-emerald-600 ring-emerald-100",
    badge: "bg-emerald-50 text-emerald-700 ring-emerald-100",
    arrow: "text-emerald-600",
    glow: "from-emerald-500/10",
  },
  orange: {
    icon: "bg-orange-50 text-orange-600 ring-orange-100",
    badge: "bg-orange-50 text-orange-700 ring-orange-100",
    arrow: "text-orange-600",
    glow: "from-orange-500/10",
  },
  teal: {
    icon: "bg-teal-50 text-teal-600 ring-teal-100",
    badge: "bg-teal-50 text-teal-700 ring-teal-100",
    arrow: "text-teal-600",
    glow: "from-teal-500/10",
  },
};

const overviewCards = [
  {
    label: "Modul Admin",
    value: "7",
    desc: "Menu custom aktif",
    icon: "grid",
  },
  {
    label: "Halaman User",
    value: "6+",
    desc: "Konten website publik",
    icon: "screen",
  },
  {
    label: "Status Panel",
    value: "Aktif",
    desc: "Siap mengelola konten",
    icon: "shield",
  },
];

const managementSteps = [
  {
    title: "Lengkapi Konten Utama",
    desc:
      "Mulai dari halaman Home, Profil, dan Kepengurusan agar identitas website terlihat kuat.",
  },
  {
    title: "Rapikan Data Pendukung",
    desc: "Lanjutkan dengan Program Kerja, Dokumentasi, Layanan Jasa, dan Konsultasi.",
  },
  {
    title: "Preview Sebelum Publish",
    desc:
      "Setelah menyimpan data, selalu cek halaman user agar tampilan tetap rapi dan konsisten.",
  },
];

const contentChecklist = [
  "Pastikan judul dan deskripsi tidak terlalu panjang.",
  "Gunakan foto yang jelas, rapi, dan tidak pecah.",
  "Periksa status aktif/nonaktif sebelum konten ditampilkan.",
  "Cek tampilan mobile setelah melakukan perubahan data.",
];
</script>

<template>
  <Head title="Admin Dashboard" />

  <div class="space-y-8">
    <!-- Hero Dashboard -->
    <section
      class="relative overflow-hidden rounded-[2rem] border border-slate-800/80 bg-[linear-gradient(135deg,#07111f_0%,#0f172a_45%,#7f1d1d_100%)] p-6 text-white shadow-[0_28px_80px_rgba(2,6,23,0.28)] sm:p-8 lg:p-10"
    >
      <!-- Glow decorations -->
      <div
        class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-red-600/25 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute -bottom-20 -left-16 h-72 w-72 rounded-full bg-blue-800/20 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute bottom-0 right-1/3 h-48 w-48 rounded-full bg-red-900/30 blur-[60px]"
      />
      <!-- Grid dots pattern -->
      <div
        class="pointer-events-none absolute inset-0 opacity-[0.04]"
        style="
          background-image: radial-gradient(circle, #fff 1px, transparent 1px);
          background-size: 28px 28px;
        "
      />

      <div class="relative z-10 grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
        <div>
          <!-- Top badge row -->
          <div class="mb-4 flex flex-wrap items-center gap-2">
            <div
              class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.14em] text-red-100 ring-1 ring-white/10"
            >
              <span class="relative flex h-2 w-2">
                <span
                  class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-70"
                />
                <span class="relative inline-flex h-2 w-2 rounded-full bg-red-400" />
              </span>
              Admin HMPS RPL
            </div>
            <div
              class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.07] px-3 py-1.5 text-[0.68rem] font-semibold text-slate-300 ring-1 ring-white/10"
            >
              <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.5"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              {{ todayLabel }}
            </div>
          </div>

          <!-- Greeting + Title -->
          <p class="mb-1 text-sm font-bold text-red-300/80 tracking-wide">
            {{ greeting }},
          </p>
          <h1
            class="max-w-3xl text-[2rem] font-black leading-[1.06] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.1rem]"
          >
            Dashboard Admin
            <span
              class="block bg-[linear-gradient(90deg,#fca5a5,#f87171)] bg-clip-text text-transparent"
              >Website HMPS RPL</span
            >
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300/90 sm:text-[0.95rem]"
          >
            Kelola seluruh konten website HMPS RPL dari satu panel admin — mulai dari
            beranda, profil, kepengurusan, program kerja, dokumentasi, layanan jasa,
            hingga konsultasi.
          </p>

          <!-- CTA Buttons -->
          <div class="mt-7 flex flex-wrap gap-3">
            <Link
              href="/admin/home"
              class="group relative inline-flex min-h-[50px] items-center gap-2 overflow-hidden rounded-2xl bg-white px-6 text-sm font-black text-slate-950 shadow-[0_14px_34px_rgba(255,255,255,0.14)] transition hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700"
            >
              <span
                class="absolute inset-0 -translate-x-full bg-[linear-gradient(90deg,transparent,rgba(239,68,68,0.12),transparent)] transition-transform duration-700 group-hover:translate-x-full"
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
                  d="M3 11.5 12 4l9 7.5M5 10.5V20h5v-5h4v5h5v-9.5"
                />
              </svg>
              <span class="relative z-10">Mulai dari Custom Home</span>
            </Link>
            <Link
              href="/"
              class="inline-flex min-h-[50px] items-center gap-2 rounded-2xl border border-white/15 bg-white/[0.08] px-5 text-sm font-black text-white backdrop-blur transition hover:-translate-y-[2px] hover:bg-white/15"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              Preview Website
            </Link>
          </div>
        </div>

        <!-- Overview Cards -->
        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
          <div
            v-for="card in overviewCards"
            :key="card.label"
            class="group flex items-center gap-4 rounded-[1.35rem] border border-white/10 bg-white/[0.07] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] backdrop-blur-xl transition hover:bg-white/[0.11]"
          >
            <div
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-red-200 ring-1 ring-white/10"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  v-if="card.icon === 'grid'"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M4 5a1 1 0 0 1 1-1h5v6H4V5Zm10-1h5a1 1 0 0 1 1 1v5h-6V4ZM4 14h6v6H5a1 1 0 0 1-1-1v-5Zm10 0h6v5a1 1 0 0 1-1 1h-5v-6Z"
                />
                <path
                  v-else-if="card.icon === 'screen'"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm8 11v3m-4 0h8"
                />
                <path
                  v-else
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z"
                />
              </svg>
            </div>
            <div class="min-w-0">
              <p
                class="text-[0.67rem] font-extrabold uppercase tracking-[0.12em] text-red-300/80"
              >
                {{ card.label }}
              </p>
              <h3
                class="mt-0.5 text-[1.6rem] font-black tracking-[-0.04em] leading-none text-white"
              >
                {{ card.value }}
              </h3>
              <p class="mt-1 text-xs font-medium text-slate-400">{{ card.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Actions -->
    <section class="grid gap-6 xl:grid-cols-[1.18fr_0.82fr]">
      <div
        class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_16px_44px_rgba(15,23,42,0.06)] sm:p-6 lg:p-7"
      >
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Akses Cepat
            </div>
            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950 sm:text-3xl">
              Kelola konten website
            </h2>
            <p class="mt-2 max-w-2xl text-justify text-sm leading-7 text-slate-500">
              Pilih modul admin yang ingin dikelola. Setiap menu terhubung langsung dengan
              halaman custom masing-masing.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <Link
            v-for="item in quickActions"
            :key="item.href"
            :href="item.href"
            class="group relative overflow-hidden rounded-[1.45rem] border border-slate-200/80 bg-white p-5 shadow-[0_4px_18px_rgba(15,23,42,0.05)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_22px_50px_rgba(15,23,42,0.11)]"
          >
            <!-- Top color glow -->
            <div
              class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b opacity-0 transition-opacity duration-300 group-hover:opacity-100"
              :class="colorMap[item.color]?.glow ?? 'from-slate-500/10'"
            />

            <div class="relative z-10 flex items-start gap-4">
              <!-- Icon -->
              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 transition-all duration-300"
                :class="
                  colorMap[item.color]?.icon ??
                  'bg-slate-100 text-slate-600 ring-slate-200'
                "
              >
                <svg
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    v-if="item.icon === 'home'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M3 11.5 12 4l9 7.5M5 10.5V20h5v-5h4v5h5v-9.5"
                  />
                  <path
                    v-else-if="item.icon === 'profile'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0m14 0H5"
                  />
                  <path
                    v-else-if="item.icon === 'users'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 1-8 0m8 0v1a4 4 0 0 0 4 4m-8-5v1a4 4 0 0 1-4 4m8 0H8m0 0a5 5 0 0 0-5 5h18a5 5 0 0 0-5-5"
                  />
                  <path
                    v-else-if="item.icon === 'briefcase'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm8 7h.01M10 13h.01"
                  />
                  <path
                    v-else-if="item.icon === 'gallery'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01"
                  />
                  <path
                    v-else-if="item.icon === 'service'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z"
                  />
                  <path
                    v-else
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M7 8h10M7 12h6m8-1a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 11Z"
                  />
                </svg>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="text-[0.95rem] font-black tracking-[-0.02em] text-slate-950">
                    {{ item.title }}
                  </h3>
                  <span
                    class="shrink-0 rounded-full px-2.5 py-1 text-[0.6rem] font-extrabold uppercase tracking-[0.08em] ring-1"
                    :class="
                      colorMap[item.color]?.badge ??
                      'bg-slate-50 text-slate-700 ring-slate-100'
                    "
                    >{{ item.badge }}</span
                  >
                </div>

                <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                  {{ item.desc }}
                </p>

                <div
                  class="mt-4 inline-flex items-center gap-1.5 text-[0.82rem] font-black transition-all duration-200 group-hover:gap-2.5"
                  :class="colorMap[item.color]?.arrow ?? 'text-slate-700'"
                >
                  Buka Modul
                  <svg
                    class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.5"
                      d="M13 7l5 5m0 0-5 5m5-5H6"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- Side Panels -->
      <div class="space-y-5">
        <!-- Panel: Alur Pengelolaan -->
        <div
          class="rounded-[2rem] border border-slate-200/80 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,0.06)] sm:p-6"
        >
          <div
            class="mb-1 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.67rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
          >
            <span class="h-1.5 w-1.5 rounded-full bg-red-500" />
            Alur Pengelolaan
          </div>
          <h2 class="mt-2 text-[1.15rem] font-black tracking-[-0.03em] text-slate-950">
            Prioritas Admin
          </h2>

          <div class="mt-5 space-y-0">
            <div
              v-for="(step, index) in managementSteps"
              :key="step.title"
              class="relative flex gap-4"
            >
              <!-- Connector line -->
              <div class="flex flex-col items-center">
                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[linear-gradient(135deg,#ef4444,#dc2626)] text-[0.8rem] font-black text-white shadow-md shadow-red-500/20"
                >
                  {{ index + 1 }}
                </div>
                <div
                  v-if="index < managementSteps.length - 1"
                  class="mt-1 w-px flex-1 bg-gradient-to-b from-red-200 to-transparent"
                  style="min-height: 20px"
                />
              </div>
              <div
                class="min-w-0 pb-5"
                :class="{ 'pb-1': index === managementSteps.length - 1 }"
              >
                <h3 class="text-sm font-black leading-tight text-slate-950">
                  {{ step.title }}
                </h3>
                <p class="mt-1 text-justify text-xs leading-[1.7] text-slate-500">
                  {{ step.desc }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel: Checklist Konten -->
        <div
          class="relative overflow-hidden rounded-[2rem] border border-slate-800/80 bg-[linear-gradient(155deg,#07111f_0%,#0f172a_55%,#1a1f2e_100%)] p-5 text-white shadow-[0_20px_54px_rgba(2,6,23,0.22)] sm:p-6"
        >
          <!-- Glow -->
          <div
            class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-red-600/15 blur-[50px]"
          />

          <div class="relative z-10">
            <div
              class="mb-1 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[0.67rem] font-extrabold uppercase tracking-[0.1em] text-red-100 ring-1 ring-white/10"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-red-400" />
              Checklist Konten
            </div>
            <h2 class="mt-2 text-[1.15rem] font-black tracking-[-0.03em] text-white">
              Sebelum Konten Dipublish
            </h2>

            <div class="mt-4 space-y-0 divide-y divide-white/[0.08]">
              <div
                v-for="item in contentChecklist"
                :key="item"
                class="flex gap-3 py-3 transition hover:bg-white/[0.04] first:rounded-t-xl last:rounded-b-xl"
              >
                <span
                  class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/20"
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
                <p class="text-justify text-[0.82rem] leading-[1.7] text-slate-300">
                  {{ item }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel: Quick Links -->
        <div
          class="rounded-[2rem] border border-slate-200/80 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,0.06)] sm:p-6"
        >
          <div
            class="mb-1 inline-flex items-center gap-2 rounded-full border border-blue-500/15 bg-blue-50 px-3 py-1.5 text-[0.67rem] font-extrabold uppercase tracking-[0.1em] text-blue-700"
          >
            <span class="h-1.5 w-1.5 rounded-full bg-blue-500" />
            Akses Langsung
          </div>
          <h2 class="mt-2 text-[1.15rem] font-black tracking-[-0.03em] text-slate-950">
            Halaman Penting
          </h2>

          <div class="mt-4 grid grid-cols-2 gap-2">
            <Link
              href="/admin/password/change"
              class="group flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-slate-50 p-3 transition hover:-translate-y-0.5 hover:border-amber-100 hover:bg-amber-50"
            >
              <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"
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
                    stroke-width="2.3"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"
                  />
                </svg>
              </span>
              <span class="text-[0.78rem] font-black leading-tight text-slate-800"
                >Ganti Password</span
              >
            </Link>

            <Link
              href="/admin/security/approver"
              class="group flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-slate-50 p-3 transition hover:-translate-y-0.5 hover:border-red-100 hover:bg-red-50"
            >
              <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-700"
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
                    stroke-width="2.3"
                    d="M12 3 5 6v5c0 4.55 2.91 8.42 7 9.9 4.09-1.48 7-5.35 7-9.9V6l-7-3z"
                  />
                </svg>
              </span>
              <span class="text-[0.78rem] font-black leading-tight text-slate-800"
                >Approver</span
              >
            </Link>

            <Link
              href="/admin/navbar"
              class="group flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-slate-50 p-3 transition hover:-translate-y-0.5 hover:border-violet-100 hover:bg-violet-50"
            >
              <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700"
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
                    stroke-width="2.3"
                    d="M4 6h16M4 12h16M4 18h10"
                  />
                </svg>
              </span>
              <span class="text-[0.78rem] font-black leading-tight text-slate-800"
                >Custom Navbar</span
              >
            </Link>

            <Link
              href="/"
              class="group flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-slate-50 p-3 transition hover:-translate-y-0.5 hover:border-teal-100 hover:bg-teal-50"
            >
              <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700"
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
                    stroke-width="2.3"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.3"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
              </span>
              <span class="text-[0.78rem] font-black leading-tight text-slate-800"
                >Preview Site</span
              >
            </Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

// ─── Progress Bar ─────────────────────────────────────────────────────────────
const isNavigating = ref(false);
const navProgress = ref(0);
let navTimer = null;

const startProgress = () => {
  clearInterval(navTimer);
  isNavigating.value = true;
  navProgress.value = 0;

  navTimer = setInterval(() => {
    if (navProgress.value < 85) navProgress.value += Math.random() * 8;
  }, 120);
};

const finishProgress = () => {
  navProgress.value = 100;

  setTimeout(() => {
    isNavigating.value = false;
    navProgress.value = 0;
    clearInterval(navTimer);
    navTimer = null;
  }, 380);
};

let removeStartListener = null;
let removeFinishListener = null;

// ─── Realtime Clock ───────────────────────────────────────────────────────────
const currentTime = ref("");
const currentDate = ref("");
let clockTimer = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
  });
  currentDate.value = now.toLocaleDateString("id-ID", {
    weekday: "short",
    day: "numeric",
    month: "short",
  });
};

const page = usePage();
const isOpen = ref(false);
const profileMenuOpen = ref(false);
const profileMenuRef = ref(null);

const dashboardHref = "/admin/dashboard";

const currentUrl = computed(() => page.url || "/admin/dashboard");

const authUser = computed(() => page.props.auth?.user || null);

const adminName = computed(() => {
  return authUser.value?.name || "Admin HMPS RPL";
});

const adminIdentifier = computed(() => {
  return authUser.value?.username || authUser.value?.email || "Administrator";
});

const adminInitials = computed(() => {
  const name = String(adminName.value || "").trim();

  if (!name) return "A";

  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((word) => word.charAt(0).toUpperCase())
    .join("");
});

const menus = [
  {
    label: "Dashboard",
    href: "/admin/dashboard",
    icon: "dashboard",
    description: "Ringkasan panel admin",
    group: "Utama",
  },
  {
    label: "Custom Navbar",
    href: "/admin/navbar",
    icon: "navbar",
    description: "Atur menu navigasi utama",
    group: "Tampilan Website",
  },
  {
    label: "Custom Footer",
    href: "/admin/footer",
    icon: "footer",
    description: "Atur footer website",
    group: "Tampilan Website",
  },
  {
    label: "Custom Home",
    href: "/admin/home",
    icon: "home",
    description: "Atur konten beranda",
    group: "Konten Website",
  },
  {
    label: "Custom Dokumentasi",
    href: "/admin/dokumentasi",
    icon: "gallery",
    description: "Kelola galeri kegiatan",
    group: "Konten Website",
  },
  {
    label: "Custom Kepengurusan",
    href: "/admin/kepengurusan",
    icon: "users",
    description: "Atur struktur pengurus",
    group: "Konten Website",
  },
  {
    label: "Custom Profil",
    href: "/admin/profil",
    icon: "profile",
    description: "Kelola profil HMPS",
    group: "Konten Website",
  },
  {
    label: "Custom Program Kerja",
    href: "/admin/program-kerja",
    icon: "briefcase",
    description: "Kelola daftar proker",
    group: "Konten Website",
  },
  {
    label: "Custom Layanan Jasa",
    href: "/admin/layanan-jasa",
    icon: "service",
    description: "Atur layanan dan paket",
    group: "Konten Website",
  },
  {
    label: "Custom Konsultasi",
    href: "/admin/konsultasi",
    icon: "chat",
    description: "Atur admin konsultasi",
    group: "Konten Website",
  },
  {
    label: "Ganti Password",
    href: "/admin/password/change",
    icon: "lock",
    description: "Ubah password admin",
    group: "Keamanan",
  },
  {
    label: "Approver Keamanan",
    href: "/admin/security/approver",
    icon: "shield",
    description: "Atur nomor approval keamanan",
    group: "Keamanan",
  },
];

const menuGroups = computed(() => {
  return menus.reduce((groups, menu) => {
    const groupName = menu.group || "Menu";

    if (!groups[groupName]) {
      groups[groupName] = [];
    }

    groups[groupName].push(menu);
    return groups;
  }, {});
});

const totalMenuCount = computed(() => menus.length);
const totalGroupCount = computed(() => Object.keys(menuGroups.value).length);

const activeMenu = computed(() => {
  return menus.find((menu) => isActive(menu.href));
});

const pageTitle = computed(() => {
  return activeMenu.value?.label || "Dashboard Admin";
});

const pageDescription = computed(() => {
  return (
    activeMenu.value?.description || "Kelola konten website HMPS RPL secara terstruktur."
  );
});

const isActive = (href) => {
  if (!href) return false;

  if (href === dashboardHref) {
    return currentUrl.value === href || currentUrl.value === "/admin";
  }

  return currentUrl.value === href || currentUrl.value.startsWith(`${href}/`);
};

const closeSidebar = () => {
  isOpen.value = false;
};

const toggleProfileMenu = () => {
  profileMenuOpen.value = !profileMenuOpen.value;
};

const closeProfileMenu = () => {
  profileMenuOpen.value = false;
};

const handleClickOutside = (event) => {
  if (!profileMenuRef.value) return;

  if (!profileMenuRef.value.contains(event.target)) {
    closeProfileMenu();
  }
};

const handleEscape = (event) => {
  if (event.key !== "Escape") return;

  closeSidebar();
  closeProfileMenu();
};

const logout = () => {
  const confirmed = confirm("Yakin ingin logout dari halaman admin?");

  if (!confirmed) return;

  router.post(
    "/admin/logout",
    {},
    {
      preserveScroll: false,
      onSuccess: () => {
        closeSidebar();
        closeProfileMenu();
      },
    }
  );
};

const iconPath = (icon) => {
  const icons = {
    dashboard: "M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-16v5h6V4h-6Z",
    navbar: "M4 6h16M4 12h16M4 18h10",
    footer: "M4 5h16v14H4V5Zm0 10h16",
    home: "M3 11.5 12 4l9 7.5M5 10.5V20h5v-5h4v5h5v-9.5",
    gallery:
      "M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01",
    users:
      "M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 1-8 0m8 0v1a4 4 0 0 0 4 4m-8-5v1a4 4 0 0 1-4 4m8 0H8m0 0a5 5 0 0 0-5 5h18a5 5 0 0 0-5-5",
    profile: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0m14 0H5",
    briefcase:
      "M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm8 7h.01M10 13h.01",
    service:
      "M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z",
    chat: "M7 8h10M7 12h6m8-1a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 11Z",
    lock:
      "M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4",
    shield: "M12 3 5 6v5c0 4.55 2.91 8.42 7 9.9 4.09-1.48 7-5.35 7-9.9V6l-7-3Z",
  };

  return icons[icon] || icons.dashboard;
};

watch(
  isOpen,
  (value) => {
    if (typeof document === "undefined") return;

    document.body.style.overflow = value ? "hidden" : "";
  },
  { immediate: false }
);

watch(currentUrl, () => {
  closeSidebar();
  closeProfileMenu();
});

onMounted(() => {
  removeStartListener = router.on("start", startProgress);
  removeFinishListener = router.on("finish", finishProgress);

  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscape);
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
});

onBeforeUnmount(() => {
  removeStartListener?.();
  removeFinishListener?.();

  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleEscape);
  document.body.style.overflow = "";
  clearInterval(clockTimer);
  clearInterval(navTimer);
});
</script>

<template>
  <div
    class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.08),transparent_30%),radial-gradient(circle_at_82%_0%,rgba(15,23,42,0.08),transparent_34%),linear-gradient(180deg,#f8fafc_0%,#f1f5f9_48%,#eef2f7_100%)] font-['Plus_Jakarta_Sans',sans-serif] text-slate-900"
  >
    <!-- Navigation Progress Bar -->
    <div
      v-if="isNavigating"
      class="fixed inset-x-0 top-0 z-[999] h-[3px] overflow-hidden"
    >
      <div
        class="h-full bg-[linear-gradient(90deg,#ef4444,#f87171,#ef4444)] shadow-[0_0_10px_rgba(239,68,68,0.7)] transition-all duration-200 ease-out"
        :style="{ width: navProgress + '%' }"
      />
    </div>

    <header
      class="fixed inset-x-0 top-0 z-40 border-b border-slate-200/80 bg-white/95 px-3 shadow-[0_8px_26px_rgba(15,23,42,0.05)] backdrop-blur-xl sm:px-4 lg:hidden"
    >
      <div class="flex h-14 items-center justify-between gap-2 sm:h-16 sm:gap-3">
        <Link :href="dashboardHref" class="flex min-w-0 items-center gap-2.5 sm:gap-3">
          <div
            class="relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-[1rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] text-white shadow-lg shadow-red-500/20 sm:h-10 sm:w-10 sm:rounded-2xl"
          >
            <span class="relative z-10 text-base font-black sm:text-lg">H</span>
            <span class="absolute -right-2 -top-2 h-5 w-5 rounded-full bg-white/20" />
          </div>

          <div class="min-w-0">
            <p
              class="truncate text-[0.82rem] font-black tracking-[-0.02em] text-slate-950 sm:text-sm"
            >
              Admin HMPS RPL
            </p>
            <p class="truncate text-[0.68rem] font-semibold text-slate-500 sm:text-xs">
              {{ pageTitle }}
            </p>
          </div>
        </Link>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
          <Link
            href="/"
            aria-label="Preview website"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 sm:h-10 sm:w-10 sm:rounded-2xl"
          >
            <svg
              class="h-[1.125rem] w-[1.125rem] sm:h-5 sm:w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
              />
            </svg>
          </Link>

          <button
            type="button"
            aria-label="Buka atau tutup menu admin"
            :aria-expanded="isOpen"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 sm:h-10 sm:w-10 sm:rounded-2xl"
            @click="isOpen = !isOpen"
          >
            <svg
              v-if="!isOpen"
              class="h-[1.125rem] w-[1.125rem] sm:h-5 sm:w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.4"
                d="M4 7h16M4 12h16M4 17h16"
              />
            </svg>

            <svg
              v-else
              class="h-[1.125rem] w-[1.125rem] sm:h-5 sm:w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.4"
                d="M6 18 18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
    </header>

    <div class="flex min-h-screen">
      <!-- Overlay Mobile -->
      <button
        v-if="isOpen"
        type="button"
        aria-label="Tutup menu admin"
        class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-[3px] lg:hidden"
        @click="closeSidebar"
      />

      <!-- Sidebar -->
      <aside
        class="fixed inset-y-0 left-0 z-50 w-[18rem] max-w-[calc(100vw-1.25rem)] border-r border-white/10 bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.22),transparent_28%),linear-gradient(180deg,#07111f_0%,#0f172a_44%,#0b1120_100%)] px-3 py-4 text-white shadow-[0_24px_70px_rgba(2,6,23,0.42)] transition-transform duration-300 sm:w-[20rem] sm:px-4 sm:py-5 lg:w-[20rem] lg:translate-x-0"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <div class="flex h-full min-h-0 flex-col">
          <!-- Brand -->
          <div class="flex items-center justify-between gap-3 px-2">
            <Link
              :href="dashboardHref"
              class="group flex min-w-0 items-center gap-3"
              @click="closeSidebar"
            >
              <div
                class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-[1rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] text-white shadow-lg shadow-red-500/25 transition group-hover:-translate-y-[1px] group-hover:shadow-red-500/35 sm:h-12 sm:w-12 sm:rounded-[1.25rem]"
              >
                <span class="relative z-10 text-xl font-black">H</span>
                <span class="absolute -right-3 -top-3 h-7 w-7 rounded-full bg-white/20" />
                <span
                  class="absolute -bottom-4 -left-4 h-9 w-9 rounded-full bg-white/10"
                />
              </div>

              <div class="min-w-0">
                <p
                  class="truncate text-sm font-black tracking-[-0.03em] text-white sm:text-base"
                >
                  Admin Panel
                </p>
                <p class="truncate text-xs font-semibold text-slate-400">
                  HMPS RPL Website
                </p>
              </div>
            </Link>

            <button
              type="button"
              aria-label="Tutup sidebar"
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.06] text-slate-300 transition hover:bg-white/10 hover:text-white lg:hidden"
              @click="closeSidebar"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.4"
                  d="M6 18 18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <!-- Admin Status -->
          <div
            class="mt-4 rounded-[1.2rem] border border-white/10 bg-white/[0.065] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.07)] sm:mt-6 sm:rounded-[1.45rem] sm:p-4"
          >
            <div class="flex items-center gap-3">
              <span
                class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/20 sm:h-10 sm:w-10 sm:rounded-2xl"
              >
                <!-- Pulse ring -->
                <span class="absolute -right-0.5 -top-0.5 flex h-3 w-3">
                  <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"
                  />
                  <span
                    class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"
                  />
                </span>
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
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                </svg>
              </span>

              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-black text-white">Mode Admin Aktif</p>
                <p class="mt-0.5 truncate text-xs font-medium text-slate-400">
                  {{ adminName }}
                </p>
              </div>

              <!-- Clock -->
              <div class="shrink-0 text-right">
                <p class="text-sm font-black tabular-nums text-white">
                  {{ currentTime }}
                </p>
                <p class="text-[0.62rem] font-medium text-slate-500">{{ currentDate }}</p>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <div class="mt-6 min-h-0 flex-1 overflow-y-auto pr-1 sidebar-scroll">
            <div
              v-for="(groupMenus, groupName) in menuGroups"
              :key="groupName"
              class="mb-5 last:mb-0"
            >
              <div class="mb-2.5 flex items-center justify-between px-2">
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.16em] text-slate-500"
                >
                  {{ groupName }}
                </p>

                <span
                  class="rounded-full bg-white/[0.06] px-2 py-0.5 text-[0.62rem] font-extrabold text-slate-400 ring-1 ring-white/10"
                >
                  {{ groupMenus.length }}
                </span>
              </div>

              <nav class="space-y-1.5">
                <Link
                  v-for="menu in groupMenus"
                  :key="menu.href"
                  :href="menu.href"
                  class="group relative flex items-center gap-2.5 rounded-[1rem] px-2.5 py-2.5 transition-all duration-300 sm:gap-3 sm:rounded-[1.15rem] sm:px-3 sm:py-3"
                  :class="
                    isActive(menu.href)
                      ? 'bg-white text-slate-950 shadow-[0_16px_34px_rgba(0,0,0,0.20)]'
                      : 'text-slate-300 hover:bg-white/[0.075] hover:text-white'
                  "
                  :aria-current="isActive(menu.href) ? 'page' : undefined"
                  @click="closeSidebar"
                >
                  <span
                    v-if="isActive(menu.href)"
                    class="absolute left-0 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full bg-[linear-gradient(180deg,#ef4444,#dc2626)]"
                  />

                  <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-all duration-300 sm:h-10 sm:w-10 sm:rounded-2xl"
                    :class="
                      isActive(menu.href)
                        ? 'bg-red-50 text-red-700'
                        : 'bg-white/[0.06] text-slate-400 ring-1 ring-white/10 group-hover:bg-red-500/15 group-hover:text-red-200 group-hover:ring-red-400/20'
                    "
                  >
                    <svg
                      class="h-5 w-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        :d="iconPath(menu.icon)"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.25"
                      />
                    </svg>
                  </span>

                  <span class="min-w-0 flex-1">
                    <span
                      class="block truncate text-[0.82rem] font-black leading-tight sm:text-sm"
                    >
                      {{ menu.label }}
                    </span>

                    <span
                      class="mt-0.5 hidden truncate text-[0.72rem] font-medium sm:block"
                      :class="
                        isActive(menu.href)
                          ? 'text-slate-500'
                          : 'text-slate-500 group-hover:text-slate-400'
                      "
                    >
                      {{ menu.description }}
                    </span>
                  </span>
                </Link>
              </nav>
            </div>
          </div>

          <!-- Bottom Card -->
          <div
            class="mt-4 rounded-[1.25rem] border border-white/10 bg-white/[0.065] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] sm:mt-5 sm:rounded-[1.5rem] sm:p-4"
          >
            <div class="flex items-start gap-3">
              <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-500/15 text-red-200 ring-1 ring-red-400/20 sm:h-10 sm:w-10 sm:rounded-2xl"
              >
                <svg
                  class="h-[1.125rem] w-[1.125rem] sm:h-5 sm:w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.25"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
                  />
                </svg>
              </div>

              <div>
                <p class="text-sm font-black text-white">Preview Website</p>
                <p class="mt-1 hidden text-xs leading-5 text-slate-400 sm:block">
                  Lihat hasil konten yang sudah dikelola.
                </p>
              </div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-2">
              <Link
                href="/"
                class="inline-flex min-h-[40px] w-full items-center justify-center gap-2 rounded-xl bg-white text-xs font-black text-slate-950 transition hover:-translate-y-[1px] hover:bg-red-50 hover:text-red-700 sm:min-h-[42px] sm:rounded-2xl sm:text-sm"
                @click="closeSidebar"
              >
                Lihat Website
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
                    d="M13 7h4m0 0v4m0-4-7 7"
                  />
                </svg>
              </Link>

              <button
                type="button"
                class="inline-flex min-h-[40px] w-full items-center justify-center gap-2 rounded-xl border border-red-400/20 bg-red-500/10 text-xs font-black text-red-200 transition hover:bg-red-500/15 lg:hidden"
                @click="logout"
              >
                Logout
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
                    d="M15.75 9V5.75A2.75 2.75 0 0 0 13 3H6.75A2.75 2.75 0 0 0 4 5.75v12.5A2.75 2.75 0 0 0 6.75 21H13a2.75 2.75 0 0 0 2.75-2.75V15M10 12h10m0 0-3-3m3 3-3 3"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main -->
      <main class="min-w-0 flex-1 pt-14 sm:pt-16 lg:min-h-screen lg:pl-[20rem] lg:pt-0">
        <!-- Desktop Header -->
        <div class="sticky top-0 z-30 hidden bg-white/60 backdrop-blur-2xl lg:block">
          <div class="mx-auto max-w-[94rem] px-5 pt-4 lg:px-8">
            <div
              class="rounded-[1.9rem] border border-white/80 bg-white/80 px-5 py-4 shadow-[0_18px_50px_rgba(15,23,42,0.06)] ring-1 ring-slate-200/70"
            >
              <div class="flex items-center justify-between gap-5">
                <!-- Left -->
                <div class="min-w-0 flex-1">
                  <div class="space-y-1">
                    <h1
                      class="truncate bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-[1.8rem] font-black leading-none tracking-[-0.045em] text-transparent"
                    >
                      {{ pageTitle }}
                    </h1>

                    <p class="truncate text-[0.96rem] font-medium text-slate-500">
                      {{ pageDescription }}
                    </p>
                  </div>
                </div>

                <!-- Right -->
                <div class="flex shrink-0 items-center gap-3 self-start xl:self-center">
                  <!-- Preview -->
                  <Link
                    href="/"
                    class="inline-flex h-14 items-center justify-center gap-2 rounded-[1.35rem] border border-slate-200/90 bg-white px-5 text-[1rem] font-black text-slate-700 shadow-[0_8px_22px_rgba(15,23,42,0.05)] transition hover:-translate-y-[1px] hover:border-red-200 hover:bg-red-50 hover:text-red-700"
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
                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.4"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
                      />
                    </svg>
                    Preview
                  </Link>

                  <!-- Profile Dropdown -->
                  <div ref="profileMenuRef" class="relative">
                    <button
                      type="button"
                      class="group flex h-14 w-[18rem] max-w-[22rem] items-center gap-3 xl:w-[22rem] rounded-[1.5rem] border border-slate-200/90 bg-white px-3.5 pr-3 shadow-[0_12px_28px_rgba(15,23,42,0.06)] transition hover:-translate-y-[1px] hover:border-red-100 hover:shadow-[0_18px_40px_rgba(15,23,42,0.08)]"
                      @click="toggleProfileMenu"
                    >
                      <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] text-sm font-black text-white shadow-lg shadow-red-500/20"
                      >
                        {{ adminInitials }}
                      </div>

                      <div class="min-w-0 flex-1 text-left">
                        <p
                          class="truncate text-[0.98rem] font-black leading-tight text-slate-950"
                        >
                          {{ adminName }}
                        </p>
                        <p
                          class="mt-1 truncate text-[0.82rem] font-bold leading-none text-slate-500"
                        >
                          {{ adminIdentifier }}
                        </p>
                      </div>

                      <span
                        class="ml-1 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition group-hover:bg-red-50 group-hover:text-red-700"
                      >
                        <svg
                          class="h-4 w-4 transition duration-200"
                          :class="profileMenuOpen ? 'rotate-180' : ''"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="m6 9 6 6 6-6"
                          />
                        </svg>
                      </span>
                    </button>

                    <!-- Dropdown -->
                    <Transition
                      enter-active-class="transition duration-200 ease-out"
                      enter-from-class="opacity-0 scale-95 -translate-y-2"
                      enter-to-class="opacity-100 scale-100 translate-y-0"
                      leave-active-class="transition duration-150 ease-in"
                      leave-from-class="opacity-100 scale-100 translate-y-0"
                      leave-to-class="opacity-0 scale-95 -translate-y-2"
                    >
                      <div
                        v-if="profileMenuOpen"
                        class="absolute right-0 top-[calc(100%+0.85rem)] z-50 w-[290px] origin-top-right overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.16)]"
                      >
                        <div class="border-b border-slate-100 px-4 py-4">
                          <div class="flex items-center gap-3">
                            <div
                              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] text-sm font-black text-white"
                            >
                              {{ adminInitials }}
                            </div>

                            <div class="min-w-0">
                              <p class="truncate text-sm font-black text-slate-950">
                                {{ adminName }}
                              </p>
                              <p
                                class="mt-0.5 truncate text-xs font-semibold text-slate-500"
                              >
                                {{ adminIdentifier }}
                              </p>
                            </div>
                          </div>
                        </div>

                        <div class="p-2.5">
                          <Link
                            href="/admin/password/change"
                            class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 hover:text-slate-950"
                            @click="closeProfileMenu"
                          >
                            <span
                              class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"
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
                                  stroke-width="2.2"
                                  d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4"
                                />
                              </svg>
                            </span>

                            <span class="min-w-0">
                              <span class="block">Ganti Password</span>
                              <span
                                class="mt-0.5 block text-xs font-medium text-slate-500"
                              >
                                Kelola keamanan akun admin
                              </span>
                            </span>
                          </Link>

                          <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left text-sm font-bold text-red-700 transition hover:bg-red-50"
                            @click="logout"
                          >
                            <span
                              class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-700"
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
                                  d="M15.75 9V5.75A2.75 2.75 0 0 0 13 3H6.75A2.75 2.75 0 0 0 4 5.75v12.5A2.75 2.75 0 0 0 6.75 21H13a2.75 2.75 0 0 0 2.75-2.75V15M10 12h10m0 0-3-3m3 3-3 3"
                                />
                              </svg>
                            </span>

                            <span class="min-w-0">
                              <span class="block">Logout</span>
                              <span
                                class="mt-0.5 block text-xs font-medium text-red-500/80"
                              >
                                Keluar dari panel admin
                              </span>
                            </span>
                          </button>
                        </div>
                      </div>
                    </Transition>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mx-auto max-w-[94rem] px-3 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-7">
          <div
            class="mb-4 rounded-[1.15rem] border border-white/80 bg-white/80 p-3 shadow-[0_14px_34px_rgba(15,23,42,0.05)] backdrop-blur-xl sm:hidden"
          >
            <div class="flex items-center gap-3">
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600"
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
                    stroke-width="2.2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                </svg>
              </span>

              <div class="min-w-0 flex-1">
                <p
                  class="truncate text-[0.68rem] font-black uppercase tracking-[0.13em] text-slate-400"
                >
                  Panel Admin
                </p>
                <p
                  class="mt-0.5 truncate text-sm font-black tracking-[-0.02em] text-slate-950"
                >
                  {{ pageTitle }}
                </p>
              </div>

              <span
                class="rounded-full bg-emerald-50 px-2.5 py-1 text-[0.68rem] font-black text-emerald-700 ring-1 ring-emerald-100"
              >
                Aktif
              </span>
            </div>
          </div>

          <div
            class="mb-5 hidden grid-cols-1 gap-3 sm:grid sm:grid-cols-2 xl:grid-cols-4"
          >
            <!-- Card: Halaman Aktif -->
            <div
              class="flex items-center gap-3 rounded-[1.25rem] border border-white/80 bg-white/75 p-4 shadow-[0_16px_40px_rgba(15,23,42,0.05)] backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_22px_50px_rgba(15,23,42,0.09)]"
            >
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600"
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
                    stroke-width="2.2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                  />
                </svg>
              </span>
              <div class="min-w-0">
                <p
                  class="text-[0.65rem] font-black uppercase tracking-[0.13em] text-slate-400"
                >
                  Halaman Aktif
                </p>
                <p
                  class="mt-0.5 truncate text-sm font-black tracking-[-0.02em] text-slate-950"
                >
                  {{ pageTitle }}
                </p>
              </div>
            </div>

            <!-- Card: Total Menu -->
            <div
              class="flex items-center gap-3 rounded-[1.25rem] border border-white/80 bg-white/75 p-4 shadow-[0_16px_40px_rgba(15,23,42,0.05)] backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_22px_50px_rgba(15,23,42,0.09)]"
            >
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"
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
                    stroke-width="2.2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16"
                  />
                </svg>
              </span>
              <div class="min-w-0">
                <p
                  class="text-[0.65rem] font-black uppercase tracking-[0.13em] text-slate-400"
                >
                  Total Menu
                </p>
                <p class="mt-0.5 text-sm font-black tracking-[-0.02em] text-slate-950">
                  {{ totalMenuCount }} Modul
                </p>
              </div>
            </div>

            <!-- Card: Kategori -->
            <div
              class="flex items-center gap-3 rounded-[1.25rem] border border-white/80 bg-white/75 p-4 shadow-[0_16px_40px_rgba(15,23,42,0.05)] backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_22px_50px_rgba(15,23,42,0.09)]"
            >
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
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
                    stroke-width="2.2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                  />
                </svg>
              </span>
              <div class="min-w-0">
                <p
                  class="text-[0.65rem] font-black uppercase tracking-[0.13em] text-slate-400"
                >
                  Kategori
                </p>
                <p class="mt-0.5 text-sm font-black tracking-[-0.02em] text-slate-950">
                  {{ totalGroupCount }} Grup
                </p>
              </div>
            </div>

            <!-- Card: Status Panel -->
            <div
              class="flex items-center gap-3 rounded-[1.25rem] border border-red-100 bg-[linear-gradient(135deg,#fff,#fff1f2)] p-4 shadow-[0_16px_40px_rgba(220,38,38,0.06)] backdrop-blur-xl transition hover:-translate-y-0.5"
            >
              <span
                class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-500"
              >
                <span class="absolute -right-0.5 -top-0.5 flex h-2.5 w-2.5">
                  <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"
                  />
                  <span
                    class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"
                  />
                </span>
                <svg
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                </svg>
              </span>
              <div class="min-w-0">
                <p
                  class="text-[0.65rem] font-black uppercase tracking-[0.13em] text-red-500"
                >
                  Status Panel
                </p>
                <p class="mt-0.5 text-sm font-black tracking-[-0.02em] text-slate-950">
                  Aktif &amp; Aman
                </p>
              </div>
            </div>
          </div>

          <div
            class="relative overflow-hidden rounded-[1.2rem] border border-white/80 bg-white/55 p-1.5 shadow-[0_18px_50px_rgba(15,23,42,0.06)] backdrop-blur-xl sm:rounded-[1.75rem] sm:p-2 sm:shadow-[0_24px_70px_rgba(15,23,42,0.07)]"
          >
            <div
              class="relative z-10 min-h-[calc(100vh-8.5rem)] rounded-[1rem] sm:min-h-[calc(100vh-11rem)] sm:rounded-[1.35rem]"
              data-aos="fade-up"
              data-aos-duration="500"
            >
              <slot />
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.sidebar-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(148, 163, 184, 0.35) transparent;
}

.sidebar-scroll::-webkit-scrollbar {
  width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.35);
  border-radius: 999px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.55);
}

@media (prefers-reduced-motion: reduce) {
  * {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

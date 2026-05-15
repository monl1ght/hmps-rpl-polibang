<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const fallbackNavbar = {
  logoPath: "/Images/logo/hmps-rpl-logo.png",
  logoAlt: "Logo HMPS RPL",
  brandTitle: "HMPS RPL",
  brandSubtitle: "Rekayasa Perangkat Lunak",
  ctaLabel: "Hubungi Kami",
  ctaContactName: "Admin",
  ctaWhatsappNumber: "6285712324386",
  ctaMessage: "Halo Kak, saya ingin menghubungi HMPS RPL.",
  ctaIsActive: true,
  items: [
    {
      label: "Beranda",
      href: "/",
      path: "/",
      iconPath: "M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z",
    },
    {
      label: "Profil",
      href: "/profil",
      path: "/profil",
      iconPath:
        "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z",
    },
    {
      label: "Kepengurusan",
      href: "/kepengurusan",
      path: "/kepengurusan",
      iconPath:
        "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0M18 8h3M19.5 6.5v3M3 8h3M4.5 6.5v3",
    },
    {
      label: "Program Kerja",
      href: "/program-kerja",
      path: "/program-kerja",
      iconPath:
        "M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12",
    },
    {
      label: "Layanan Jasa",
      href: "/layananJasa",
      path: "/layananJasa",
      iconPath:
        "M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z",
    },
    {
      label: "Konsultasi",
      href: "/konsultasi",
      path: "/konsultasi",
      iconPath: "M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5",
    },
    {
      label: "Dokumentasi",
      href: "/dokumentasi",
      path: "/dokumentasi",
      iconPath:
        "M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01",
    },
  ],
};

const navbar = computed(() => {
  const sharedNavbar = page.props.navbar || {};

  return {
    ...fallbackNavbar,
    ...sharedNavbar,
    items:
      Array.isArray(sharedNavbar.items) && sharedNavbar.items.length
        ? sharedNavbar.items
        : fallbackNavbar.items,
  };
});

const logoPath = computed(() => navbar.value.logoPath || fallbackNavbar.logoPath);
const logoAlt = computed(() => navbar.value.logoAlt || fallbackNavbar.logoAlt);
const brandTitle = computed(() => navbar.value.brandTitle || fallbackNavbar.brandTitle);
const brandSubtitle = computed(
  () => navbar.value.brandSubtitle || fallbackNavbar.brandSubtitle
);

const navItems = computed(() =>
  navbar.value.items
    .filter((item) => item?.label && item?.href)
    .map((item) => ({
      ...item,
      path: item.path || item.href,
      iconPath: item.iconPath || fallbackNavbar.items[0].iconPath,
      target: item.target || "_self",
    }))
);

const normalizeWhatsappNumber = (number) => {
  const clean = String(number || "").replace(/[^0-9]/g, "");

  if (clean.startsWith("0")) return `62${clean.slice(1)}`;
  if (clean.startsWith("8")) return `62${clean}`;

  return clean;
};

const whatsappContactName = computed(
  () => navbar.value.ctaContactName || fallbackNavbar.ctaContactName
);

const whatsappNumber = computed(() =>
  normalizeWhatsappNumber(
    navbar.value.ctaWhatsappNumber || fallbackNavbar.ctaWhatsappNumber
  )
);

const whatsappMessage = computed(() =>
  encodeURIComponent(navbar.value.ctaMessage || fallbackNavbar.ctaMessage)
);

const whatsappUrl = computed(() => {
  if (!whatsappNumber.value) return "#";

  return `https://wa.me/${whatsappNumber.value}?text=${whatsappMessage.value}`;
});

const ctaLabel = computed(() => navbar.value.ctaLabel || fallbackNavbar.ctaLabel);
const ctaIsActive = computed(() => Boolean(navbar.value.ctaIsActive));

const mobileMenuId = "main-mobile-navigation";

const scrolled = ref(false);
const mobileOpen = ref(false);
const progress = ref(0);

let ticking = false;
let previousBodyOverflow = "";

const normalizePath = (path = "/") => {
  const cleanPath = String(path).split("?")[0].split("#")[0] || "/";

  if (cleanPath === "/") {
    return "/";
  }

  return cleanPath.replace(/\/+$/, "");
};

const currentPath = computed(() => normalizePath(page.url || "/"));

const isActive = (path) => {
  const normalizedPath = normalizePath(path);

  if (normalizedPath === "/") {
    return currentPath.value === "/";
  }

  return (
    currentPath.value === normalizedPath ||
    currentPath.value.startsWith(`${normalizedPath}/`)
  );
};

const activeNavItem = computed(
  () => navItems.value.find((item) => isActive(item.path)) || navItems.value[0]
);

const calculateNavState = () => {
  if (typeof window === "undefined") return;

  scrolled.value = window.scrollY > 24;

  const el = document.documentElement;
  const total = el.scrollHeight - el.clientHeight;

  progress.value = total > 0 ? Math.min((window.scrollY / total) * 100, 100) : 0;

  if (window.innerWidth >= 1280 && mobileOpen.value) {
    mobileOpen.value = false;
  }
};

const updateNavState = () => {
  if (ticking || typeof window === "undefined") return;

  ticking = true;

  window.requestAnimationFrame(() => {
    calculateNavState();
    ticking = false;
  });
};

const closeMobile = () => {
  mobileOpen.value = false;
};

const toggleMobile = () => {
  mobileOpen.value = !mobileOpen.value;
};

const handleKeydown = (event) => {
  if (event.key === "Escape") {
    closeMobile();
  }
};

watch(mobileOpen, (open) => {
  if (typeof document === "undefined") return;

  if (open) {
    previousBodyOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return;
  }

  document.body.style.overflow = previousBodyOverflow || "";
});

watch(
  () => page.url,
  async () => {
    closeMobile();
    await nextTick();
    updateNavState();
  }
);

onMounted(() => {
  calculateNavState();

  window.addEventListener("scroll", updateNavState, { passive: true });
  window.addEventListener("resize", updateNavState, { passive: true });
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("scroll", updateNavState);
  window.removeEventListener("resize", updateNavState);
  window.removeEventListener("keydown", handleKeydown);

  if (typeof document !== "undefined") {
    document.body.style.overflow = previousBodyOverflow || "";
  }
});

const isHeaderDark = computed(() => mobileOpen.value || !scrolled.value);

const headerClasses = computed(() => {
  if (mobileOpen.value) {
    return "border-b border-white/10 bg-slate-950/95 shadow-[0_14px_32px_rgba(2,6,23,0.22)] backdrop-blur-[22px] supports-[backdrop-filter]:bg-slate-950/90";
  }

  return scrolled.value
    ? "border-b border-slate-900/10 bg-white/90 shadow-[0_10px_35px_rgba(15,23,42,0.09)] backdrop-blur-[20px] supports-[backdrop-filter]:bg-white/80"
    : "border-b border-white/5 bg-[linear-gradient(180deg,rgba(8,17,32,0.94),rgba(15,23,42,0.86))] backdrop-blur-[18px]";
});

const desktopNavClasses = computed(() =>
  scrolled.value
    ? "border border-slate-900/15 bg-slate-900/[0.06] shadow-[inset_0_1px_0_rgba(255,255,255,0.8),0_2px_10px_rgba(15,23,42,0.07)]"
    : "border border-white/10 bg-white/5"
);

const navLinkClasses = (item) => {
  const active = isActive(item.path);

  if (scrolled.value) {
    return active
      ? "bg-red-500/10 text-red-700 shadow-[inset_0_0_0_1px_rgba(239,68,68,0.15)]"
      : "text-slate-700 hover:bg-slate-900/5 hover:text-slate-950";
  }

  return active
    ? "bg-white/10 text-white shadow-[inset_0_0_0_1px_rgba(255,255,255,0.08)]"
    : "text-white/80 hover:bg-white/10 hover:text-white";
};
</script>

<template>
  <header
    :class="headerClasses"
    class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
  >
    <div
      class="absolute bottom-0 left-0 h-[2px] rounded-r-full bg-gradient-to-r from-red-500 via-red-600 to-red-800 transition-all duration-200"
      :style="{ width: `${progress}%` }"
      aria-hidden="true"
    />

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div
        class="flex min-h-[66px] items-center justify-between gap-3 py-2 sm:min-h-[72px] sm:gap-5 xl:min-h-0 xl:py-3.5"
      >
        <Link
          href="/"
          class="group flex min-w-0 shrink-0 items-center gap-2.5 sm:gap-3.5"
          aria-label="Kembali ke Beranda HMPS RPL"
          @click="closeMobile"
        >
          <div class="relative shrink-0">
            <div
              class="absolute inset-[-8px] -z-10 rounded-[1.2rem] bg-[radial-gradient(circle,rgba(239,68,68,0.22),transparent_65%)] opacity-0 blur-[10px] transition-opacity duration-300 group-hover:opacity-100"
              aria-hidden="true"
            />

            <div
              class="relative flex h-9 w-9 items-center justify-center overflow-hidden rounded-[1rem] bg-white shadow-[0_10px_24px_rgba(15,23,42,0.14)] ring-1 ring-white/20 transition duration-300 group-hover:-translate-y-[1px] group-hover:scale-[1.02] group-hover:shadow-[0_14px_30px_rgba(15,23,42,0.18)] sm:h-[2.65rem] sm:w-[2.65rem] xl:h-[2.9rem] xl:w-[2.9rem]"
            >
              <img
                :src="logoPath"
                :alt="logoAlt"
                class="h-full w-full object-cover"
                loading="eager"
                decoding="async"
              />
            </div>
          </div>

          <div class="min-w-0 leading-none">
            <p
              class="truncate text-[13px] font-extrabold tracking-[0.04em] transition-colors duration-300 sm:text-[16px]"
              :class="isHeaderDark ? 'text-white' : 'text-slate-900'"
            >
              {{ brandTitle }}
            </p>

            <p
              class="mt-1 truncate text-[9.5px] font-medium tracking-[0.03em] transition-colors duration-300 sm:text-[11px]"
              :class="isHeaderDark ? 'text-white/60' : 'text-slate-500'"
            >
              {{ brandSubtitle }}
            </p>
          </div>
        </Link>

        <nav
          class="hidden flex-1 items-center justify-center xl:flex"
          aria-label="Navigasi utama"
        >
          <div
            :class="desktopNavClasses"
            class="flex items-center gap-1.5 rounded-full p-[0.35rem] transition-all duration-300"
          >
            <Link
              v-for="item in navItems"
              :key="item.id || item.path"
              :href="item.href"
              :target="item.target !== '_self' ? item.target : undefined"
              :aria-current="isActive(item.path) ? 'page' : undefined"
              class="group relative inline-flex min-h-[42px] items-center justify-center whitespace-nowrap rounded-full px-4 py-[0.7rem] text-sm font-semibold tracking-[0.01em] transition-all duration-200 hover:-translate-y-px focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
              :class="navLinkClasses(item)"
            >
              {{ item.label }}

              <span
                class="absolute bottom-[6px] left-1/2 h-[2.5px] w-[54%] -translate-x-1/2 rounded-full bg-gradient-to-r from-red-300 via-red-500 to-red-800 transition-transform duration-300"
                :class="
                  isActive(item.path)
                    ? 'scale-x-100'
                    : 'scale-x-0 group-hover:scale-x-100'
                "
                aria-hidden="true"
              />
            </Link>
          </div>
        </nav>

        <div v-if="ctaIsActive" class="hidden shrink-0 items-center xl:flex">
          <a
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            :aria-label="`Hubungi ${whatsappContactName} melalui WhatsApp`"
            class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-[1.15rem] py-[0.8rem] text-sm font-bold tracking-[0.01em] text-white shadow-[0_10px_26px_rgba(220,38,38,0.26)] transition duration-200 hover:-translate-y-px hover:shadow-[0_18px_40px_rgba(220,38,38,0.20)] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/60 focus-visible:ring-offset-2"
          >
            <span
              class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
              aria-hidden="true"
            />

            <svg
              width="15"
              height="15"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="relative z-10"
              aria-hidden="true"
            >
              <path
                d="M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Zm-8.39 18.33h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.98.99-3.63-.23-.37a9.86 9.86 0 0 1-1.51-5.29c0-5.46 4.44-9.9 9.9-9.9a9.84 9.84 0 0 1 7 2.9 9.85 9.85 0 0 1 2.9 7c-.01 5.46-4.45 9.9-9.92 9.9Zm5.43-7.41c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"
              />
            </svg>

            <span class="relative z-10">{{ ctaLabel }}</span>
          </a>
        </div>

        <button
          type="button"
          :aria-label="mobileOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
          :aria-expanded="mobileOpen"
          :aria-controls="mobileMenuId"
          @click.stop="toggleMobile"
          class="flex h-11 w-11 shrink-0 touch-manipulation items-center justify-center rounded-2xl transition-all duration-200 hover:-translate-y-px active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/60 xl:hidden"
          :class="
            mobileOpen
              ? 'bg-white text-slate-950 shadow-[0_10px_24px_rgba(0,0,0,0.18)]'
              : isHeaderDark
              ? 'bg-white/10 text-white/95 ring-1 ring-white/10 hover:bg-white/20'
              : 'bg-slate-900/5 text-slate-900 hover:bg-slate-900/10'
          "
        >
          <span class="flex flex-col gap-[5px]" aria-hidden="true">
            <span
              class="block h-[2px] w-[22px] rounded-full transition-all duration-300"
              :class="[
                mobileOpen
                  ? 'bg-slate-950'
                  : isHeaderDark
                  ? 'bg-white/95'
                  : 'bg-slate-900',
                mobileOpen ? 'translate-y-[7px] rotate-45' : '',
              ]"
            />
            <span
              class="block h-[2px] w-[22px] rounded-full transition-all duration-300"
              :class="[
                mobileOpen
                  ? 'bg-slate-950'
                  : isHeaderDark
                  ? 'bg-white/95'
                  : 'bg-slate-900',
                mobileOpen ? 'scale-x-0 opacity-0' : '',
              ]"
            />
            <span
              class="block h-[2px] w-[22px] rounded-full transition-all duration-300"
              :class="[
                mobileOpen
                  ? 'bg-slate-950'
                  : isHeaderDark
                  ? 'bg-white/95'
                  : 'bg-slate-900',
                mobileOpen ? '-translate-y-[7px] -rotate-45' : '',
              ]"
            />
          </span>
        </button>
      </div>
    </div>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <button
        v-if="mobileOpen"
        type="button"
        aria-label="Tutup menu navigasi"
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-[3px] xl:hidden"
        @click="closeMobile"
      />
    </Transition>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="-translate-y-2 scale-[0.98] opacity-0"
      enter-to-class="translate-y-0 scale-100 opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="translate-y-0 scale-100 opacity-100"
      leave-to-class="-translate-y-2 scale-[0.98] opacity-0"
    >
      <section
        v-if="mobileOpen"
        :id="mobileMenuId"
        class="fixed inset-x-3 top-[74px] z-50 overflow-hidden rounded-[1.55rem] border border-white/40 bg-white shadow-[0_24px_60px_rgba(2,6,23,0.26)] ring-1 ring-slate-950/5 sm:inset-x-5 sm:top-[82px] xl:hidden"
        aria-label="Menu navigasi mobile"
      >
        <div class="relative overflow-hidden bg-slate-950 px-4 py-4 text-white">
          <div
            class="absolute -right-12 -top-16 h-36 w-36 rounded-full bg-red-500/25 blur-3xl"
            aria-hidden="true"
          />
          <div
            class="absolute -bottom-20 left-8 h-36 w-36 rounded-full bg-white/10 blur-3xl"
            aria-hidden="true"
          />

          <div class="relative flex items-center gap-3">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-[0_12px_26px_rgba(0,0,0,0.20)] ring-1 ring-white/20"
            >
              <img
                :src="logoPath"
                :alt="logoAlt"
                class="h-full w-full object-cover"
                loading="eager"
                decoding="async"
              />
            </div>

            <div class="min-w-0 flex-1">
              <p class="text-[10px] font-black uppercase tracking-[0.18em] text-red-300">
                Menu Navigasi
              </p>
              <p
                class="mt-1 truncate text-sm font-extrabold tracking-[0.01em] text-white"
              >
                {{ activeNavItem?.label || brandTitle }}
              </p>
            </div>

            <button
              type="button"
              aria-label="Tutup menu navigasi"
              class="flex h-10 w-10 shrink-0 touch-manipulation items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/10 transition hover:bg-white/20 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400/70"
              @click="closeMobile"
            >
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.4"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path d="M18 6 6 18M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div
          class="max-h-[calc(100svh-168px)] overflow-y-auto overscroll-contain bg-white px-3 py-3 sm:max-h-[calc(100svh-180px)] sm:px-4"
        >
          <div class="grid gap-2">
            <Link
              v-for="item in navItems"
              :key="`mobile-${item.id || item.path}`"
              :href="item.href"
              :target="item.target !== '_self' ? item.target : undefined"
              :aria-current="isActive(item.path) ? 'page' : undefined"
              @click="closeMobile"
              class="group flex min-h-[50px] w-full touch-manipulation items-center gap-3 rounded-2xl px-3 py-2.5 text-[0.92rem] font-bold transition-all duration-200 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40"
              :class="
                isActive(item.path)
                  ? 'bg-red-50 text-red-700 shadow-[inset_0_0_0_1px_rgba(239,68,68,0.12)]'
                  : 'bg-white text-slate-700 shadow-[inset_0_0_0_1px_rgba(15,23,42,0.075)] hover:bg-slate-50 hover:text-slate-950'
              "
            >
              <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.9rem] transition-all duration-200"
                :class="
                  isActive(item.path)
                    ? 'bg-red-500 text-white shadow-[0_8px_18px_rgba(239,68,68,0.22)]'
                    : 'bg-slate-100 text-slate-500 group-hover:bg-red-50 group-hover:text-red-600'
                "
                aria-hidden="true"
              >
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path :d="item.iconPath" />
                </svg>
              </span>

              <span class="min-w-0 flex-1 truncate">{{ item.label }}</span>

              <span
                v-if="isActive(item.path)"
                class="rounded-full bg-white px-2 py-1 text-[10px] font-black uppercase tracking-[0.08em] text-red-600 shadow-sm"
              >
                Aktif
              </span>

              <svg
                v-else
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.4"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="text-slate-300 transition-all duration-200 group-hover:translate-x-[2px] group-hover:text-red-600"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </Link>
          </div>

          <div v-if="ctaIsActive" class="my-3 border-t border-slate-100" />

          <a
            v-if="ctaIsActive"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            :aria-label="`Hubungi ${whatsappContactName} melalui WhatsApp`"
            @click="closeMobile"
            class="group relative inline-flex min-h-[50px] w-full touch-manipulation items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-4 py-3 text-sm font-extrabold text-white shadow-[0_14px_30px_rgba(220,38,38,0.28)] transition duration-200 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
          >
            <span
              class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
              aria-hidden="true"
            />

            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="relative z-10"
              aria-hidden="true"
            >
              <path
                d="M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Zm-8.39 18.33h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.98.99-3.63-.23-.37a9.86 9.86 0 0 1-1.51-5.29c0-5.46 4.44-9.9 9.9-9.9a9.84 9.84 0 0 1 7 2.9 9.85 9.85 0 0 1 2.9 7c-.01 5.46-4.45 9.9-9.92 9.9Zm5.43-7.41c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"
              />
            </svg>

            <span class="relative z-10">{{ ctaLabel }}</span>
          </a>
        </div>
      </section>
    </Transition>
  </header>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const currentYear = computed(() => new Date().getFullYear());

const iconPathByType = {
  location:
    "M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z",
  whatsapp:
    "M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Zm-8.39 18.33h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.98.99-3.63-.23-.37a9.86 9.86 0 0 1-1.51-5.29c0-5.46 4.44-9.9 9.9-9.9a9.84 9.84 0 0 1 7 2.9 9.85 9.85 0 0 1 2.9 7c-.01 5.46-4.45 9.9-9.92 9.9Zm5.43-7.41c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z",
  instagram:
    "M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z",
  email:
    "M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z",
  info: "M13 16h-1v-4h-1m1-4h.01",
};

const fallbackFooter = {
  logoPath: "/Images/logo/hmps-rpl-logo.png",
  logoAlt: "Logo HMPS RPL",
  brandTitle: "HMPS RPL",
  brandSubtitle: "Rekayasa Perangkat Lunak",
  description:
    "Wadah aspirasi, kolaborasi, dan kreativitas mahasiswa Rekayasa Perangkat Lunak untuk membangun ekosistem teknologi yang aktif, profesional, dan berdampak.",
  identityLabel: "Identitas Organisasi",
  identityText: "Himpunan Mahasiswa Program Studi Rekayasa Perangkat Lunak.",
  navigationTitle: "Navigasi",
  contactTitle: "Kontak",
  contactCtaLabel: "Hubungi via WhatsApp",
  contactCtaUrl:
    "https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.",
  copyrightText: "© {year} HMPS Rekayasa Perangkat Lunak. All rights reserved.",
  navLinks: [
    {
      label: "Beranda",
      href: "/",
      icon: "M3 10.5 12 3l9 7.5M5 10v10h14V10M9 20v-6h6v6",
      target: "_self",
    },
    {
      label: "Profil",
      href: "/profil",
      icon: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0",
      target: "_self",
    },
    {
      label: "Kepengurusan",
      href: "/kepengurusan",
      icon: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0",
      target: "_self",
    },
    {
      label: "Program Kerja",
      href: "/program-kerja",
      icon:
        "M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12",
      target: "_self",
    },
    {
      label: "Layanan Jasa",
      href: "/layananJasa",
      icon:
        "M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z",
      target: "_self",
    },
    {
      label: "Konsultasi",
      href: "/konsultasi",
      icon: "M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5",
      target: "_self",
    },
    {
      label: "Dokumentasi",
      href: "/dokumentasi",
      icon:
        "M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01",
      target: "_self",
    },
  ],
  socials: [],
  contactItems: [
    {
      type: "location",
      title: "Alamat Sekretariat",
      value:
        "Balekambang, RT.02/RW.07, Kepel, Gemiring Lor, Kec. Nalumsari, Kabupaten Jepara, Jawa Tengah 59465",
      helper: "Politeknik Balekambang Jepara",
      href: null,
      icon: iconPathByType.location,
      target: "_self",
    },
    {
      type: "whatsapp",
      title: "WhatsApp",
      value: "6285712324386",
      helper: "Hubungi HMPS RPL",
      href:
        "https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.",
      icon: iconPathByType.whatsapp,
      target: "_blank",
    },
    {
      type: "email",
      title: "Email",
      value: "muhammadbahaudinnajib@gmail.com",
      helper: "Kirim email",
      href: "mailto:muhammadbahaudinnajib@gmail.com",
      icon: iconPathByType.email,
      target: "_blank",
    },
  ],
  legalLinks: [
    {
      label: "Kebijakan Privasi",
      href: null,
      target: "_self",
    },
    {
      label: "Syarat & Ketentuan",
      href: null,
      target: "_self",
    },
  ],
};

const footer = computed(() => {
  const sharedFooter = page.props.footer || {};

  return {
    ...fallbackFooter,
    ...sharedFooter,
    navLinks: Array.isArray(sharedFooter.navLinks)
      ? sharedFooter.navLinks
      : fallbackFooter.navLinks,
    socials: Array.isArray(sharedFooter.socials)
      ? sharedFooter.socials
      : fallbackFooter.socials,
    contactItems: Array.isArray(sharedFooter.contactItems)
      ? sharedFooter.contactItems
      : fallbackFooter.contactItems,
    legalLinks: Array.isArray(sharedFooter.legalLinks)
      ? sharedFooter.legalLinks
      : fallbackFooter.legalLinks,
  };
});

const currentPath = computed(() => {
  const raw = page.url || "/";
  const cleanPath = raw.split("?")[0].split("#")[0] || "/";

  if (cleanPath === "/") return "/";

  return cleanPath.replace(/\/+$/, "");
});

const isExternalLink = (href) => {
  const value = String(href || "");

  return /^(https?:)?\/\//i.test(value) || /^(mailto:|tel:)/i.test(value);
};

const normalizePath = (href) => {
  const value =
    String(href || "/")
      .split("?")[0]
      .split("#")[0] || "/";

  if (value === "/") return "/";

  return value.replace(/\/+$/, "");
};

const isActive = (href) => {
  if (!href || isExternalLink(href)) return false;

  const normalizedHref = normalizePath(href);

  if (normalizedHref === "/") return currentPath.value === "/";

  return (
    currentPath.value === normalizedHref ||
    currentPath.value.startsWith(`${normalizedHref}/`)
  );
};

const copyrightText = computed(() => {
  return String(footer.value.copyrightText || fallbackFooter.copyrightText).replaceAll(
    "{year}",
    currentYear.value
  );
});

const safeNavLinks = computed(() => {
  return footer.value.navLinks
    .filter((link) => link?.label && link?.href)
    .map((link) => ({
      ...link,
      target: link.target || "_self",
      icon: link.icon || iconPathByType.info,
    }));
});

const safeSocials = computed(() => {
  const seen = new Set();

  return footer.value.socials
    .filter((social) => social?.name && social?.href)
    .map((social) => ({
      ...social,
      target: social.target || "_blank",
      icon: social.icon || iconPathByType[social.type] || iconPathByType.instagram,
    }))
    .filter((social) => {
      const key = `${social.name}-${social.href}`.toLowerCase();

      if (seen.has(key)) return false;

      seen.add(key);

      return true;
    });
});

const safeContactItems = computed(() => {
  return footer.value.contactItems.filter((item) => item?.title);
});

const normalizeWhatsappNumber = (number) => {
  const clean = String(number || "").replace(/[^0-9]/g, "");

  if (!clean) return "";

  if (clean.startsWith("0")) return `62${clean.slice(1)}`;
  if (clean.startsWith("8")) return `62${clean}`;

  return clean;
};

const normalizeContactItem = (item) => {
  if (!item) return null;

  const type = item.type || "info";
  const value = item.value || "";
  let href = item.href || "";

  if (type === "email" && !href && value) {
    href = `mailto:${value}`;
  }

  if (type === "whatsapp" && !href && value) {
    const normalizedNumber = normalizeWhatsappNumber(value);
    href = normalizedNumber ? `https://wa.me/${normalizedNumber}` : "";
  }

  const isClickableExternal =
    href.startsWith("http") || href.startsWith("mailto:") || href.startsWith("tel:");

  return {
    ...item,
    type,
    href,
    target: item.target || (isClickableExternal ? "_blank" : "_self"),
    icon: item.icon || iconPathByType[type] || iconPathByType.info,
  };
};

const normalizedContactItems = computed(() => {
  return safeContactItems.value.map((item) => normalizeContactItem(item)).filter(Boolean);
});

const locationContactItem = computed(() => {
  return (
    normalizedContactItems.value.find((item) => item.type === "location") ||
    normalizedContactItems.value[0] ||
    null
  );
});

const quickContactItems = computed(() => {
  const seen = new Set();

  return normalizedContactItems.value
    .filter((item) => item !== locationContactItem.value && item.type !== "instagram")
    .filter((item) => {
      const key = `${item.type}-${item.href || item.value || item.title}`.toLowerCase();

      if (seen.has(key)) return false;

      seen.add(key);

      return item?.title && (item?.href || item?.value || item?.type === "email");
    });
});

const safeLegalLinks = computed(() => {
  return footer.value.legalLinks
    .filter((link) => link?.label)
    .map((link) => ({
      ...link,
      target: link.target || "_self",
    }));
});

const contactAriaLabel = (item) => {
  return [item.title, item.value, item.helper].filter(Boolean).join(" - ");
};
</script>

<template>
  <footer
    class="relative overflow-hidden text-white [background:radial-gradient(circle_at_top_left,rgba(239,68,68,0.14),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(220,38,38,0.13),transparent_34%),linear-gradient(180deg,#060b16_0%,#0b1220_46%,#111827_100%)]"
  >
    <div
      class="pointer-events-none absolute -top-16 right-4 h-44 w-44 rounded-full bg-red-500/10 blur-3xl sm:h-64 sm:w-64 lg:h-80 lg:w-80"
      aria-hidden="true"
    />

    <div
      class="pointer-events-none absolute -bottom-20 left-0 h-52 w-52 rounded-full bg-red-700/[0.15] blur-3xl sm:h-72 sm:w-72 lg:h-96 lg:w-96"
      aria-hidden="true"
    />

    <div
      class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-red-500/60 to-transparent"
      aria-hidden="true"
    />

    <div
      class="relative z-10 mx-auto max-w-7xl px-4 pb-5 pt-6 sm:px-6 sm:pb-6 sm:pt-8 lg:px-8 lg:pb-7 lg:pt-12"
    >
      <div
        class="grid grid-cols-1 gap-3.5 lg:grid-cols-[1fr_1.15fr_1fr] lg:gap-5 xl:gap-6"
      >
        <!-- Brand -->
        <section data-aos="fade-up" data-aos-delay="0">
          <div
            class="h-full rounded-[1.35rem] border border-white/10 bg-white/[0.045] p-4 shadow-[0_14px_30px_rgba(2,6,23,0.20)] backdrop-blur-sm sm:rounded-3xl sm:p-6 lg:p-7"
          >
            <Link
              href="/"
              class="group inline-flex max-w-full items-center gap-3"
              aria-label="Kembali ke Beranda HMPS RPL"
            >
              <div
                class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-[0_12px_24px_rgba(15,23,42,0.20)] ring-1 ring-white/20 transition duration-300 group-hover:-translate-y-[2px] group-hover:scale-[1.02] sm:h-12 sm:w-12"
              >
                <img
                  :src="footer.logoPath"
                  :alt="footer.logoAlt"
                  class="h-full w-full object-cover"
                  loading="lazy"
                  decoding="async"
                />
              </div>

              <div class="min-w-0 leading-none">
                <h2
                  class="truncate text-[0.98rem] font-extrabold uppercase tracking-[0.03em] text-white sm:text-[1.15rem]"
                >
                  {{ footer.brandTitle }}
                </h2>

                <p
                  class="mt-1 truncate text-[0.68rem] font-semibold text-slate-400 sm:text-sm"
                >
                  {{ footer.brandSubtitle }}
                </p>
              </div>
            </Link>

            <p
              class="mt-4 line-clamp-2 max-w-xl text-sm leading-6 text-slate-400 sm:mt-5 sm:line-clamp-3 sm:leading-7 lg:text-justify"
            >
              {{ footer.description }}
            </p>

            <div
              class="mt-4 rounded-[1.1rem] border border-white/10 bg-white/[0.035] p-3.5 sm:mt-5 sm:rounded-2xl sm:p-4"
            >
              <p
                class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-300 sm:text-[0.72rem]"
              >
                {{ footer.identityLabel }}
              </p>

              <p
                class="mt-1.5 line-clamp-2 text-[0.82rem] leading-6 text-slate-400 sm:text-sm sm:leading-7"
              >
                {{ footer.identityText }}
              </p>
            </div>
          </div>
        </section>

        <!-- Navigation -->
        <nav data-aos="fade-up" data-aos-delay="80" aria-label="Navigasi footer">
          <div
            class="h-full rounded-[1.35rem] border border-white/10 bg-white/[0.045] p-4 shadow-[0_14px_30px_rgba(2,6,23,0.20)] backdrop-blur-sm sm:rounded-3xl sm:p-6 lg:p-7"
          >
            <div class="mb-3.5 flex items-center justify-between gap-3 sm:mb-5">
              <div class="min-w-0">
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-300 sm:text-[0.72rem]"
                >
                  Menu Website
                </p>

                <h4
                  class="mt-0.5 text-[0.95rem] font-bold tracking-tight text-white sm:mt-1 sm:text-lg"
                >
                  {{ footer.navigationTitle }}
                </h4>
              </div>

              <span
                class="shrink-0 rounded-full border border-white/10 bg-white/[0.04] px-2.5 py-1 text-[0.65rem] font-bold text-slate-400 sm:px-3 sm:text-xs"
              >
                {{ safeNavLinks.length }} Menu
              </span>
            </div>

            <ul
              class="grid grid-cols-2 gap-2 sm:grid-cols-2 sm:gap-2.5 lg:grid-cols-1 xl:grid-cols-2"
            >
              <li
                v-for="link in safeNavLinks"
                :key="link.id || link.href"
                class="min-w-0"
              >
                <component
                  :is="isExternalLink(link.href) || link.target === '_blank' ? 'a' : Link"
                  :href="link.href"
                  :target="link.target === '_blank' ? '_blank' : undefined"
                  :rel="link.target === '_blank' ? 'noopener noreferrer' : undefined"
                  class="group flex min-h-[42px] w-full min-w-0 items-center gap-2 rounded-[0.95rem] border px-2.5 py-2 text-xs font-bold transition-all duration-200 hover:-translate-y-[1px] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/45 sm:min-h-[48px] sm:gap-2.5 sm:rounded-2xl sm:px-3 sm:text-sm"
                  :class="
                    isActive(link.href)
                      ? 'border-red-500/[0.35] bg-red-500/[0.12] text-white shadow-[inset_0_0_0_1px_rgba(239,68,68,0.10)]'
                      : 'border-white/10 bg-white/[0.03] text-slate-300 hover:border-red-500/25 hover:bg-white/[0.06] hover:text-white'
                  "
                >
                  <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-[0.8rem] border transition-all duration-200 sm:h-8 sm:w-8 sm:rounded-xl"
                    :class="
                      isActive(link.href)
                        ? 'border-red-500/30 bg-red-500/[0.15] text-red-200'
                        : 'border-white/10 bg-white/[0.035] text-red-300 group-hover:border-red-500/25 group-hover:bg-red-500/[0.15] group-hover:text-white'
                    "
                    aria-hidden="true"
                  >
                    <svg
                      class="h-3.5 w-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        :d="link.icon"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.15"
                      />
                    </svg>
                  </span>

                  <span class="min-w-0 flex-1 truncate leading-snug">
                    {{ link.label }}
                  </span>

                  <svg
                    class="hidden h-3.5 w-3.5 shrink-0 text-slate-500 transition duration-200 group-hover:translate-x-0.5 group-hover:text-red-300 sm:block"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.4"
                      d="M9 18l6-6-6-6"
                    />
                  </svg>
                </component>
              </li>
            </ul>
          </div>
        </nav>

        <!-- Contact -->
        <section data-aos="fade-up" data-aos-delay="140">
          <div
            class="h-full rounded-[1.35rem] border border-white/10 bg-white/[0.045] p-4 shadow-[0_14px_30px_rgba(2,6,23,0.20)] backdrop-blur-sm sm:rounded-3xl sm:p-6 lg:p-7"
          >
            <div class="mb-3.5 sm:mb-5">
              <p
                class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-red-300 sm:text-[0.72rem]"
              >
                Hubungi Kami
              </p>

              <h4
                class="mt-0.5 text-[0.95rem] font-bold tracking-tight text-white sm:mt-1 sm:text-lg"
              >
                {{ footer.contactTitle }}
              </h4>
            </div>

            <div
              v-if="locationContactItem"
              class="rounded-[1.1rem] border border-white/10 bg-white/[0.03] p-3.5 transition duration-200 hover:border-red-500/20 hover:bg-white/[0.055] sm:rounded-2xl sm:p-4"
            >
              <component
                :is="locationContactItem.href ? 'a' : 'div'"
                :href="locationContactItem.href || undefined"
                :target="
                  locationContactItem.href
                    ? locationContactItem.target || '_blank'
                    : undefined
                "
                :rel="locationContactItem.href ? 'noopener noreferrer' : undefined"
                class="flex items-start gap-3"
              >
                <span
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.9rem] border border-red-500/10 bg-[linear-gradient(135deg,rgba(239,68,68,0.16),rgba(220,38,38,0.08))] text-red-300 sm:h-10 sm:w-10 sm:rounded-2xl"
                  aria-hidden="true"
                >
                  <svg
                    class="h-[18px] w-[18px] sm:h-5 sm:w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      :d="locationContactItem.icon || iconPathByType.location"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                    />
                  </svg>
                </span>

                <span class="min-w-0 flex-1">
                  <span
                    class="block text-[0.84rem] font-bold leading-5 text-slate-200 sm:text-sm sm:leading-6"
                  >
                    {{ locationContactItem.title }}
                  </span>

                  <span
                    class="mt-1 line-clamp-2 block text-[0.8rem] leading-5 text-slate-400 sm:text-sm sm:leading-6"
                  >
                    {{ locationContactItem.value }}
                  </span>

                  <span
                    v-if="locationContactItem.helper"
                    class="mt-1 block truncate text-[0.7rem] font-semibold text-slate-500 sm:text-xs"
                  >
                    {{ locationContactItem.helper }}
                  </span>
                </span>
              </component>
            </div>

            <a
              v-if="footer.contactCtaUrl"
              :href="footer.contactCtaUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="group relative mt-3 flex min-h-[44px] w-full items-center justify-center gap-2 overflow-hidden rounded-full bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-4 py-3 text-[0.82rem] font-extrabold text-white shadow-[0_12px_28px_rgba(220,38,38,0.24)] transition duration-200 hover:-translate-y-px active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 sm:mt-4 sm:min-h-[48px] sm:text-sm"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent)] transition-transform duration-500 group-hover:translate-x-[140%]"
                aria-hidden="true"
              />

              <svg
                class="relative z-10 h-4 w-4"
                fill="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path :d="iconPathByType.whatsapp" />
              </svg>

              <span class="relative z-10">{{ footer.contactCtaLabel }}</span>
            </a>

            <div
              v-if="quickContactItems.length || safeSocials.length"
              class="mt-3 rounded-[1.1rem] border border-white/10 bg-white/[0.025] p-3 sm:mt-4 sm:rounded-2xl sm:p-3.5"
            >
              <div class="mb-2.5 flex items-center justify-between gap-3 sm:mb-3">
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400 sm:text-xs"
                >
                  Kontak Cepat
                </p>

                <span
                  class="rounded-full border border-white/10 bg-white/[0.035] px-2.5 py-1 text-[0.62rem] font-bold text-slate-500"
                >
                  Akses Cepat
                </span>
              </div>

              <div class="flex flex-nowrap items-center gap-2 overflow-x-auto overflow-y-hidden pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:gap-2.5">
                <component
                  v-for="item in quickContactItems"
                  :key="item.id || `${item.type}-${item.title}`"
                  :is="item.href ? 'a' : 'button'"
                  :href="item.href || undefined"
                  :type="item.href ? undefined : 'button'"
                  :target="item.href ? item.target || '_blank' : undefined"
                  :rel="item.href ? 'noopener noreferrer' : undefined"
                  :aria-label="contactAriaLabel(item)"
                  :title="contactAriaLabel(item)"
                  class="group flex h-11 min-h-11 w-11 flex-none items-center justify-center rounded-[0.95rem] border border-white/10 bg-white/[0.045] p-0 text-slate-300 transition-all duration-200 hover:-translate-y-[1px] hover:border-red-500/30 hover:bg-red-500/[0.15] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 sm:h-11 sm:w-11"
                >
                  <svg
                    class="h-[17px] w-[17px] shrink-0 text-red-300 transition-colors duration-200 group-hover:text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      :d="item.icon || iconPathByType[item.type] || iconPathByType.info"
                    />
                  </svg>

                  <span class="sr-only">{{ item.title }}</span>
                </component>

                <a
                  v-for="social in safeSocials"
                  :key="social.id || social.name"
                  :href="social.href"
                  :aria-label="social.name"
                  :title="social.name"
                  :target="social.target || '_blank'"
                  rel="noopener noreferrer"
                  class="group flex h-11 min-h-11 w-11 flex-none items-center justify-center rounded-[0.95rem] border border-white/10 bg-white/[0.045] p-0 text-slate-300 transition-all duration-200 hover:-translate-y-[1px] hover:border-red-500/30 hover:bg-red-500/[0.15] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 sm:h-11 sm:w-11"
                >
                  <svg
                    class="h-[17px] w-[17px] shrink-0 text-red-300 transition-colors duration-200 group-hover:text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path :d="social.icon" />
                  </svg>

                  <span class="sr-only">{{ social.name }}</span>
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div
        data-aos="fade-up"
        data-aos-delay="220"
        class="mt-4 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-4 text-center sm:mt-6 sm:gap-4 sm:pt-5 lg:flex-row lg:text-left"
      >
        <div class="flex flex-col gap-0.5">
          <p
            class="text-[0.78rem] font-medium leading-5 text-slate-400 sm:text-sm sm:leading-6"
          >
            {{ copyrightText }}
          </p>

          <p class="text-[0.68rem] font-semibold text-slate-600 sm:text-xs">
            Website resmi HMPS Rekayasa Perangkat Lunak
          </p>
        </div>

        <div
          v-if="safeLegalLinks.length"
          class="flex flex-wrap items-center justify-center gap-2 text-sm lg:justify-end"
        >
          <template v-for="link in safeLegalLinks" :key="link.id || link.label">
            <component
              v-if="link.href"
              :is="isExternalLink(link.href) || link.target === '_blank' ? 'a' : Link"
              :href="link.href"
              :target="link.target === '_blank' ? '_blank' : undefined"
              :rel="link.target === '_blank' ? 'noopener noreferrer' : undefined"
              class="rounded-full border border-white/10 bg-white/[0.035] px-3.5 py-2 text-[0.68rem] font-bold text-slate-400 transition-colors duration-200 hover:border-red-500/25 hover:bg-white/[0.06] hover:text-white sm:px-4 sm:text-xs"
            >
              {{ link.label }}
            </component>

            <span
              v-else
              class="cursor-not-allowed rounded-full border border-white/10 bg-white/[0.035] px-3.5 py-2 text-[0.68rem] font-bold text-slate-500 sm:px-4 sm:text-xs"
              :title="`${link.label} belum tersedia`"
            >
              {{ link.label }}
            </span>
          </template>
        </div>
      </div>
    </div>
  </footer>
</template>

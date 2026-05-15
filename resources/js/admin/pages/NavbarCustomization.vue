<script setup>
import { computed, onBeforeUnmount, ref } from "vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  setting: {
    type: Object,
    default: () => ({}),
  },
  items: {
    type: Array,
    default: () => [],
  },
  targetOptions: {
    type: Array,
    default: () => [
      { value: "_self", label: "Halaman yang sama" },
      { value: "_blank", label: "Tab baru" },
    ],
  },
});

const page = usePage();

const logoInput = ref(null);
const logoPreviewUrl = ref(null);
const editingItem = ref(null);
const itemSearch = ref("");
const selectedStatus = ref("semua");

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const fallbackLogo = "/Images/logo/hmps-rpl-logo.png";

const items = computed(() => (Array.isArray(props.items) ? props.items : []));
const targetOptions = computed(() =>
  Array.isArray(props.targetOptions) ? props.targetOptions : []
);

const currentLogoPath = computed(() => {
  return logoPreviewUrl.value || props.setting?.logo_path || fallbackLogo;
});

const activeItemsCount = computed(() => {
  return items.value.filter((item) => item.is_active).length;
});

const inactiveItemsCount = computed(() => {
  return items.value.filter((item) => !item.is_active).length;
});

const externalItemsCount = computed(() => {
  return items.value.filter((item) => item.target === "_blank").length;
});

const filteredItems = computed(() => {
  const keyword = itemSearch.value.trim().toLowerCase();

  return items.value.filter((item) => {
    const statusMatch =
      selectedStatus.value === "semua" ||
      (selectedStatus.value === "aktif" && item.is_active) ||
      (selectedStatus.value === "nonaktif" && !item.is_active);

    const searchMatch =
      !keyword ||
      String(item.label || "")
        .toLowerCase()
        .includes(keyword) ||
      String(item.href || "")
        .toLowerCase()
        .includes(keyword) ||
      String(item.path || "")
        .toLowerCase()
        .includes(keyword) ||
      String(item.target || "")
        .toLowerCase()
        .includes(keyword);

    return statusMatch && searchMatch;
  });
});

const stats = computed(() => [
  {
    label: "Brand Navbar",
    value: props.setting?.brand_title || "HMPS RPL",
    helper: props.setting?.brand_subtitle || "Subtitle navbar",
    icon: "brand",
  },
  {
    label: "Menu Aktif",
    value: activeItemsCount.value,
    helper: `${items.value.length} total menu`,
    icon: "active",
  },
  {
    label: "Menu Nonaktif",
    value: inactiveItemsCount.value,
    helper: "Tidak tampil di navbar user",
    icon: "inactive",
  },
  {
    label: "CTA WhatsApp",
    value: props.setting?.cta_is_active ? "Aktif" : "Nonaktif",
    helper: props.setting?.cta_label || "Tombol CTA navbar",
    icon: "whatsapp",
  },
]);

const settingForm = useForm({
  logo_file: null,
  logo_path: props.setting?.logo_path || fallbackLogo,
  logo_alt: props.setting?.logo_alt || "Logo HMPS RPL",
  brand_title: props.setting?.brand_title || "HMPS RPL",
  brand_subtitle: props.setting?.brand_subtitle || "Rekayasa Perangkat Lunak",
  cta_label: props.setting?.cta_label || "Hubungi Kami",
  cta_contact_name: props.setting?.cta_contact_name || "Admin",
  cta_whatsapp_number: props.setting?.cta_whatsapp_number || "6285712324386",
  cta_message: props.setting?.cta_message || "Halo Kak, saya ingin menghubungi HMPS RPL.",
  cta_is_active: Boolean(props.setting?.cta_is_active ?? true),
  is_active: Boolean(props.setting?.is_active ?? true),
});

const itemForm = useForm({
  label: "",
  href: "",
  path: "",
  icon_path: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const itemEditForm = useForm({
  label: "",
  href: "",
  path: "",
  icon_path: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const revokeLogoPreview = () => {
  if (logoPreviewUrl.value) {
    URL.revokeObjectURL(logoPreviewUrl.value);
    logoPreviewUrl.value = null;
  }
};

const handleLogoChange = (event) => {
  const file = event.target.files?.[0] || null;

  revokeLogoPreview();
  settingForm.logo_file = file;
  logoPreviewUrl.value = file ? URL.createObjectURL(file) : null;
};

const updateSetting = () => {
  settingForm.post("/admin/navbar/settings", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      settingForm.logo_file = null;
      revokeLogoPreview();

      if (logoInput.value) {
        logoInput.value.value = "";
      }
    },
  });
};

const resetItemForm = () => {
  itemForm.reset();
  itemForm.target = "_self";
  itemForm.is_active = true;
  itemForm.sort_order = 0;
};

const storeItem = () => {
  itemForm.post("/admin/navbar/items", {
    preserveScroll: true,
    onSuccess: () => resetItemForm(),
  });
};

const editItem = (item) => {
  editingItem.value = item;

  itemEditForm.label = item.label || "";
  itemEditForm.href = item.href || "";
  itemEditForm.path = item.path || "";
  itemEditForm.icon_path = item.icon_path || "";
  itemEditForm.target = item.target || "_self";
  itemEditForm.is_active = Boolean(item.is_active);
  itemEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditItem = () => {
  editingItem.value = null;
  itemEditForm.reset();
};

const updateItem = () => {
  if (!editingItem.value) return;

  itemEditForm.put(`/admin/navbar/items/${editingItem.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditItem(),
  });
};

const deleteItem = (item) => {
  if (!confirm(`Hapus menu "${item.label}" dari navbar?`)) return;

  router.delete(`/admin/navbar/items/${item.id}`, {
    preserveScroll: true,
  });
};

const statusClass = (status) => {
  return status
    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
    : "border-slate-200 bg-slate-100 text-slate-600";
};

const ctaStatusClass = computed(() => statusClass(settingForm.cta_is_active));
const settingStatusClass = computed(() => statusClass(settingForm.is_active));

const targetLabel = (targetValue) => {
  return (
    targetOptions.value.find((target) => target.value === targetValue)?.label ||
    targetValue ||
    "Halaman yang sama"
  );
};

const targetClass = (targetValue) => {
  return targetValue === "_blank"
    ? "border-violet-200 bg-violet-50 text-violet-700"
    : "border-blue-200 bg-blue-50 text-blue-700";
};

const shortIconPreview = (iconPath) => {
  if (!iconPath) return "Default icon";
  return iconPath.length > 76 ? `${iconPath.slice(0, 76)}...` : iconPath;
};

const defaultIconPath = "M4 6h16M4 12h16M4 18h16";

const menuIconPath = (item) => item.icon_path || defaultIconPath;

onBeforeUnmount(() => {
  revokeLogoPreview();
});
</script>

<template>
  <Head title="Custom Navbar" />

  <div class="custom-navbar-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8">
    <!-- Premium Header -->
    <section
      class="relative overflow-hidden rounded-[2rem] border border-slate-800/80 bg-[linear-gradient(135deg,#07111f_0%,#0f172a_45%,#7f1d1d_100%)] p-6 text-white shadow-[0_28px_80px_rgba(2,6,23,0.28)] sm:p-8 lg:p-10"
    >
      <div
        class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-red-600/25 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute -bottom-20 -left-16 h-72 w-72 rounded-full bg-blue-800/20 blur-[90px]"
      />
      <div
        class="pointer-events-none absolute bottom-0 right-1/3 h-48 w-48 rounded-full bg-red-900/30 blur-[60px]"
      />
      <div
        class="pointer-events-none absolute inset-0 opacity-[0.04]"
        style="
          background-image: radial-gradient(circle, #fff 1px, transparent 1px);
          background-size: 28px 28px;
        "
      />

      <div class="relative z-10 grid gap-8 xl:grid-cols-[1.08fr_0.92fr] xl:items-center">
        <div>
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
              Admin / Custom Navbar
            </div>

            <div
              class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.07] px-3 py-1.5 text-[0.68rem] font-semibold text-slate-300 ring-1 ring-white/10"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
              {{ settingForm.is_active ? "Setting Aktif" : "Setting Nonaktif" }}
            </div>
          </div>

          <p class="mb-1 text-sm font-bold tracking-wide text-red-300/80">
            Panel Pengaturan Navigasi
          </p>

          <h1
            class="max-w-3xl text-[2rem] font-black leading-[1.06] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.1rem]"
          >
            Kelola Navbar
            <span
              class="block bg-[linear-gradient(90deg,#fca5a5,#f87171)] bg-clip-text text-transparent"
            >
              Website HMPS RPL
            </span>
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300/90 sm:text-[0.95rem]"
          >
            Atur logo, brand, tombol WhatsApp, dan menu navigasi utama agar navbar user
            tampil dinamis, rapi, konsisten, dan tetap selaras dengan identitas HMPS RPL.
          </p>

          <div class="mt-5 grid grid-cols-1 gap-2 sm:mt-7 sm:flex sm:flex-wrap sm:gap-3">
            <a
              href="/"
              target="_blank"
              rel="noopener noreferrer"
              class="group relative inline-flex min-h-[46px] w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-sm font-black text-slate-950 shadow-[0_14px_34px_rgba(255,255,255,0.14)] transition hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700 sm:w-auto sm:min-h-[50px] sm:px-6"
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
                  d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.4"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
                />
              </svg>
              <span class="relative z-10">Preview Website</span>
            </a>

            <button
              type="button"
              class="inline-flex min-h-[46px] w-full items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/[0.08] px-5 text-sm font-black text-white backdrop-blur transition hover:-translate-y-[2px] hover:bg-white/15 sm:w-auto sm:min-h-[50px]"
              @click="logoInput?.click()"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.4"
                  d="M12 16V4m0 0-4 4m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                />
              </svg>
              Upload Logo
            </button>
          </div>
        </div>

        <!-- Live Navbar Preview -->
        <div
          class="rounded-[1.65rem] border border-white/10 bg-white/[0.07] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] backdrop-blur-xl sm:p-5"
        >
          <div class="mb-4 flex items-center justify-between gap-3">
            <div>
              <p
                class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-red-200"
              >
                Live Preview
              </p>
              <h2 class="mt-1 text-lg font-black tracking-[-0.03em] text-white">
                Tampilan Brand Navbar
              </h2>
            </div>
            <span
              class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-emerald-200"
            >
              {{ activeItemsCount }} Aktif
            </span>
          </div>

          <div
            class="rounded-[1.5rem] border border-white/10 bg-white/[0.08] p-4 shadow-[0_16px_40px_rgba(2,6,23,0.12)]"
          >
            <div class="flex items-center gap-4">
              <div
                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-[0_14px_30px_rgba(2,6,23,0.22)] ring-1 ring-white/20"
              >
                <img
                  :src="currentLogoPath"
                  :alt="settingForm.logo_alt"
                  class="h-full w-full object-cover"
                />
              </div>

              <div class="min-w-0">
                <p class="truncate text-lg font-black tracking-[-0.03em] text-white">
                  {{ settingForm.brand_title || "HMPS RPL" }}
                </p>
                <p class="mt-1 truncate text-sm font-semibold text-slate-300">
                  {{ settingForm.brand_subtitle || "Rekayasa Perangkat Lunak" }}
                </p>
              </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-2">
              <div
                class="rounded-2xl border border-white/10 bg-white/[0.07] p-3 text-center"
              >
                <p class="text-lg font-black leading-none text-white">
                  {{ items.length }}
                </p>
                <p
                  class="mt-1 text-[0.62rem] font-black uppercase tracking-[0.1em] text-slate-400"
                >
                  Total
                </p>
              </div>
              <div
                class="rounded-2xl border border-emerald-400/10 bg-emerald-500/10 p-3 text-center"
              >
                <p class="text-lg font-black leading-none text-emerald-200">
                  {{ activeItemsCount }}
                </p>
                <p
                  class="mt-1 text-[0.62rem] font-black uppercase tracking-[0.1em] text-emerald-300/80"
                >
                  Aktif
                </p>
              </div>
              <div
                class="rounded-2xl border border-violet-400/10 bg-violet-500/10 p-3 text-center"
              >
                <p class="text-lg font-black leading-none text-violet-200">
                  {{ externalItemsCount }}
                </p>
                <p
                  class="mt-1 text-[0.62rem] font-black uppercase tracking-[0.1em] text-violet-300/80"
                >
                  Tab Baru
                </p>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <span
                class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                :class="settingStatusClass"
              >
                {{ settingForm.is_active ? "Setting Aktif" : "Setting Nonaktif" }}
              </span>
              <span
                class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                :class="ctaStatusClass"
              >
                CTA {{ settingForm.cta_is_active ? "Aktif" : "Nonaktif" }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Flash Messages -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="flashSuccess"
        class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_10px_28px_rgba(16,185,129,0.08)]"
      >
        {{ flashSuccess }}
      </div>
    </transition>

    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="flashError"
        class="rounded-[1.35rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_10px_28px_rgba(239,68,68,0.08)]"
      >
        {{ flashError }}
      </div>
    </transition>

    <!-- Stats -->
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
      <div
        v-for="item in stats"
        :key="item.label"
        class="group rounded-[1.65rem] border border-slate-200/80 bg-white p-5 shadow-[0_8px_30px_rgba(15,23,42,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_22px_52px_rgba(15,23,42,0.10)]"
      >
        <div class="flex items-start justify-between gap-4">
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100 transition group-hover:scale-105 group-hover:bg-red-600 group-hover:text-white"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                v-if="item.icon === 'brand'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 4h8M8 14h5"
              />
              <path
                v-else-if="item.icon === 'active'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
              />
              <path
                v-else-if="item.icon === 'inactive'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M6 18 18 6M6 6l12 12"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M20.5 3.5A11 11 0 0 0 3.3 16.9L2 22l5.2-1.3A11 11 0 1 0 20.5 3.5ZM8.5 8.5c.3 3 3 5.7 6 6l1.5-1.5 3 1.5c-.3 1.4-1.6 2.5-3 2.5A9 9 0 0 1 7 8c0-1.4 1.1-2.7 2.5-3l1.5 3-1.5 1.5Z"
              />
            </svg>
          </div>

          <span
            class="rounded-full bg-slate-50 px-2.5 py-1 text-[0.62rem] font-extrabold uppercase tracking-[0.1em] text-slate-400 ring-1 ring-slate-100"
          >
            Live
          </span>
        </div>

        <p
          class="mt-5 text-[0.68rem] font-black uppercase tracking-[0.14em] text-slate-400"
        >
          {{ item.label }}
        </p>
        <h3 class="mt-2 truncate text-2xl font-black tracking-[-0.04em] text-slate-950">
          {{ item.value }}
        </h3>
        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
          {{ item.helper }}
        </p>
      </div>
    </section>

    <!-- Brand Setting + Add Menu -->
    <section class="grid gap-6 xl:grid-cols-[0.44fr_0.56fr]">
      <!-- Brand Setting -->
      <div
        class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Pengaturan Brand
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Brand Navbar
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Pengaturan ini mengubah logo, teks brand, subtitle, status navbar, dan
              tombol WhatsApp pada navbar user.
            </p>
          </div>

          <form class="grid gap-5" @submit.prevent="updateSetting">
            <!-- Brand Preview -->
            <div
              class="relative overflow-hidden rounded-[1.65rem] border border-slate-800/80 bg-[linear-gradient(135deg,#07111f_0%,#0f172a_58%,#7f1d1d_100%)] p-5 text-white shadow-[0_18px_44px_rgba(15,23,42,0.18)]"
            >
              <div
                class="pointer-events-none absolute -right-12 -top-12 h-32 w-32 rounded-full bg-red-500/20 blur-3xl"
              />
              <div
                class="pointer-events-none absolute inset-0 opacity-[0.05]"
                style="
                  background-image: radial-gradient(circle, #fff 1px, transparent 1px);
                  background-size: 24px 24px;
                "
              />

              <div class="relative z-10 flex items-center gap-4">
                <div
                  class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-[0_14px_30px_rgba(2,6,23,0.20)] ring-1 ring-white/20"
                >
                  <img
                    :src="currentLogoPath"
                    :alt="settingForm.logo_alt"
                    class="h-full w-full object-cover"
                  />
                </div>

                <div class="min-w-0">
                  <p class="truncate text-lg font-black tracking-[-0.03em] text-white">
                    {{ settingForm.brand_title || "Brand Title" }}
                  </p>
                  <p class="mt-1 truncate text-sm font-semibold text-slate-300">
                    {{ settingForm.brand_subtitle || "Brand Subtitle" }}
                  </p>

                  <div class="mt-3 flex flex-wrap gap-2">
                    <span
                      class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="settingStatusClass"
                    >
                      {{ settingForm.is_active ? "Setting Aktif" : "Setting Nonaktif" }}
                    </span>
                    <span
                      class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                      :class="ctaStatusClass"
                    >
                      CTA {{ settingForm.cta_is_active ? "Aktif" : "Nonaktif" }}
                    </span>
                  </div>
                </div>
              </div>

              <p
                v-if="settingForm.logo_file"
                class="relative z-10 mt-4 rounded-2xl bg-white/10 px-4 py-2 text-xs font-bold text-red-100 ring-1 ring-white/10"
              >
                File logo baru: {{ settingForm.logo_file.name }}
              </p>
            </div>

            <!-- Upload -->
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Upload Logo
              </label>

              <label
                class="group flex cursor-pointer flex-col items-center justify-center rounded-[1.45rem] border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/40"
              >
                <div
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm ring-1 ring-slate-200 transition duration-300 group-hover:scale-105 group-hover:ring-red-100"
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
                      stroke-width="2.25"
                      d="M12 16V4m0 0-4 4m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                    />
                  </svg>
                </div>

                <p class="mt-4 text-sm font-black text-slate-950">
                  Klik untuk memilih logo
                </p>
                <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
                  Format PNG, JPG, JPEG, WEBP, atau SVG. Gunakan logo rasio 1:1 agar
                  tampil rapi.
                </p>

                <input
                  ref="logoInput"
                  type="file"
                  accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                  class="sr-only"
                  @change="handleLogoChange"
                />
              </label>

              <p
                v-if="settingForm.errors.logo_file"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ settingForm.errors.logo_file }}
              </p>
            </div>

            <div class="grid gap-4">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Path Logo
                </label>
                <input
                  v-model="settingForm.logo_path"
                  type="text"
                  placeholder="/Images/logo/hmps-rpl-logo.png"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                  Jika upload logo baru, path dapat tersimpan otomatis dari storage sesuai
                  proses backend yang sudah ada.
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Alt Logo
                </label>
                <input
                  v-model="settingForm.logo_alt"
                  type="text"
                  placeholder="Logo HMPS RPL"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <p
                  v-if="settingForm.errors.logo_alt"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ settingForm.errors.logo_alt }}
                </p>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Brand Title
                </label>
                <input
                  v-model="settingForm.brand_title"
                  type="text"
                  placeholder="HMPS RPL"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <p
                  v-if="settingForm.errors.brand_title"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ settingForm.errors.brand_title }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Brand Subtitle
                </label>
                <input
                  v-model="settingForm.brand_subtitle"
                  type="text"
                  placeholder="Rekayasa Perangkat Lunak"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>
            </div>

            <!-- CTA -->
            <div class="rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5">
              <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
              >
                <div>
                  <p
                    class="text-xs font-black uppercase tracking-[0.12em] text-slate-500"
                  >
                    Tombol WhatsApp
                  </p>
                  <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                    Tombol CTA yang tampil di kanan navbar desktop dan bagian bawah mobile
                    menu.
                  </p>
                </div>

                <span
                  class="w-fit rounded-full border px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                  :class="ctaStatusClass"
                >
                  {{ settingForm.cta_is_active ? "Aktif" : "Nonaktif" }}
                </span>
              </div>

              <div class="grid gap-4">
                <input
                  v-model="settingForm.cta_label"
                  type="text"
                  placeholder="Hubungi Kami"
                  class="h-[3.15rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="settingForm.cta_contact_name"
                    type="text"
                    placeholder="Nama kontak"
                    class="h-[3.15rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <input
                    v-model="settingForm.cta_whatsapp_number"
                    type="text"
                    placeholder="6285712324386"
                    class="h-[3.15rem] rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />
                </div>

                <p
                  v-if="settingForm.errors.cta_whatsapp_number"
                  class="text-xs font-bold text-red-600"
                >
                  {{ settingForm.errors.cta_whatsapp_number }}
                </p>

                <textarea
                  v-model="settingForm.cta_message"
                  rows="4"
                  placeholder="Pesan otomatis WhatsApp..."
                  class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />
              </div>
            </div>

            <div
              class="flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex flex-col gap-3">
                <label
                  class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
                >
                  <input
                    v-model="settingForm.cta_is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Tampilkan Tombol CTA
                </label>

                <label
                  class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
                >
                  <input
                    v-model="settingForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Setting Aktif
                </label>
              </div>

              <button
                type="submit"
                :disabled="settingForm.processing"
                class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
                />
                <span class="relative z-10">
                  {{ settingForm.processing ? "Menyimpan..." : "Simpan Pengaturan" }}
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Menu -->
      <div
        class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Tambah Menu
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Tambah Menu Navbar
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Tambahkan menu baru untuk tampil di navbar desktop dan mobile. Pastikan href
              sesuai route Laravel yang sudah tersedia.
            </p>
          </div>

          <form
            class="grid gap-5 rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5"
            @submit.prevent="storeItem"
          >
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Label Menu
                </label>
                <input
                  v-model="itemForm.label"
                  type="text"
                  placeholder="Contoh: Dokumentasi"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />
                <p
                  v-if="itemForm.errors.label"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ itemForm.errors.label }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Href
                </label>
                <input
                  v-model="itemForm.href"
                  type="text"
                  placeholder="/dokumentasi"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />
                <p
                  v-if="itemForm.errors.href"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ itemForm.errors.href }}
                </p>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Path Active
                </label>
                <input
                  v-model="itemForm.path"
                  type="text"
                  placeholder="/dokumentasi"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />
                <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                  Kosongkan jika sama dengan href.
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Target
                </label>
                <select
                  v-model="itemForm.target"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                >
                  <option
                    v-for="target in targetOptions"
                    :key="target.value"
                    :value="target.value"
                  >
                    {{ target.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-2 block text-sm font-extrabold text-slate-800">
                  Urutan
                </label>
                <input
                  v-model="itemForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                SVG Icon Path
              </label>
              <textarea
                v-model="itemForm.icon_path"
                rows="5"
                placeholder="M3 9.5 12 3l9 6.5..."
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />
              <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                Isi hanya nilai path SVG, bukan tag SVG lengkap. Jika dikosongkan, sistem
                memakai icon default pada preview admin.
              </p>
            </div>

            <div
              class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <label
                class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
              >
                <input
                  v-model="itemForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                />
                Aktif
              </label>

              <button
                type="submit"
                :disabled="itemForm.processing"
                class="inline-flex min-h-[48px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:-translate-y-[2px] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ itemForm.processing ? "Menyimpan..." : "Tambah Menu" }}
              </button>
            </div>
          </form>

          <div
            class="mt-6 rounded-[1.55rem] border border-red-100 bg-[linear-gradient(135deg,#ffffff,#fff7f7)] p-5 shadow-[0_10px_28px_rgba(2,6,23,0.04)]"
          >
            <div
              class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.3"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                />
              </svg>
            </div>
            <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
              Catatan Profesional
            </p>
            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Gunakan href sesuai route Laravel, misalnya
              <span class="font-bold text-slate-700">/layananJasa</span>,
              <span class="font-bold text-slate-700">/program-kerja</span>, atau
              <span class="font-bold text-slate-700">/dokumentasi</span>.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Menu List -->
    <section
      class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
    >
      <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

      <div class="p-5 sm:p-6 lg:p-7">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Data Menu
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Daftar Menu Navbar
            </h2>

            <p class="mt-2 max-w-3xl text-justify text-sm leading-7 text-slate-500">
              Menu aktif akan tampil di navbar user. Urutan paling kecil akan tampil lebih
              awal pada navigasi website.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[520px]">
            <input
              v-model="itemSearch"
              type="text"
              placeholder="Cari label, href, path..."
              class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <select
              v-model="selectedStatus"
              class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            >
              <option value="semua">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <span
            class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
          >
            {{ filteredItems.length }} hasil
          </span>

          <span
            class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700 ring-1 ring-emerald-100"
          >
            {{ activeItemsCount }} aktif
          </span>

          <span
            class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 ring-1 ring-slate-200"
          >
            {{ inactiveItemsCount }} nonaktif
          </span>

          <span
            class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-black text-violet-700 ring-1 ring-violet-100"
          >
            {{ externalItemsCount }} tab baru
          </span>
        </div>

        <div class="mt-6 grid gap-4">
          <div
            v-for="item in filteredItems"
            :key="item.id"
            class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-slate-50 transition duration-300 hover:border-red-200 hover:shadow-[0_18px_42px_rgba(15,23,42,0.08)]"
          >
            <!-- Read Mode -->
            <div
              v-if="editingItem?.id !== item.id"
              class="grid gap-4 p-4 lg:grid-cols-[56px_1fr_auto] lg:items-start"
            >
              <div
                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm ring-1 ring-slate-200"
              >
                <svg
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    :d="menuIconPath(item)"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.2"
                  />
                </svg>
              </div>

              <div class="min-w-0">
                <div class="flex flex-wrap gap-2">
                  <span
                    class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
                  >
                    {{ item.href }}
                  </span>

                  <span
                    class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                    :class="statusClass(item.is_active)"
                  >
                    {{ item.is_active ? "Aktif" : "Nonaktif" }}
                  </span>

                  <span
                    class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600"
                  >
                    Urutan {{ item.sort_order }}
                  </span>

                  <span
                    class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                    :class="targetClass(item.target)"
                  >
                    {{ targetLabel(item.target) }}
                  </span>
                </div>

                <h3 class="mt-3 text-lg font-black tracking-[-0.03em] text-slate-950">
                  {{ item.label }}
                </h3>

                <p class="mt-1 text-sm font-semibold text-slate-500">
                  Path active:
                  <span class="text-slate-800">
                    {{ item.path || item.href }}
                  </span>
                </p>

                <div class="mt-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                  <p
                    class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400"
                  >
                    SVG Icon Path
                  </p>
                  <p class="mt-1 break-all text-xs font-medium leading-5 text-slate-500">
                    {{ shortIconPreview(item.icon_path) }}
                  </p>
                </div>
              </div>

              <div class="flex gap-2 lg:flex-col">
                <button
                  type="button"
                  class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-white px-4 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 hover:text-red-700 lg:flex-none"
                  @click="editItem(item)"
                >
                  Edit
                </button>

                <button
                  type="button"
                  class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100 lg:flex-none"
                  @click="deleteItem(item)"
                >
                  Hapus
                </button>
              </div>
            </div>

            <!-- Edit Mode -->
            <form v-else class="grid gap-4 bg-white p-5" @submit.prevent="updateItem">
              <div
                class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <span
                    class="inline-flex rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-red-700 ring-1 ring-red-100"
                  >
                    Mode Edit Menu
                  </span>

                  <h3 class="mt-2 text-lg font-black text-slate-950">
                    Edit {{ item.label }}
                  </h3>
                </div>

                <button
                  type="button"
                  class="inline-flex min-h-[40px] w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                  @click="cancelEditItem"
                >
                  Batal Edit
                </button>
              </div>

              <div class="grid gap-4 sm:grid-cols-2">
                <input
                  v-model="itemEditForm.label"
                  type="text"
                  placeholder="Label"
                  class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <input
                  v-model="itemEditForm.href"
                  type="text"
                  placeholder="Href"
                  class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <div class="grid gap-4 sm:grid-cols-3">
                <input
                  v-model="itemEditForm.path"
                  type="text"
                  placeholder="Path active"
                  class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />

                <select
                  v-model="itemEditForm.target"
                  class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                >
                  <option
                    v-for="target in targetOptions"
                    :key="target.value"
                    :value="target.value"
                  >
                    {{ target.label }}
                  </option>
                </select>

                <input
                  v-model="itemEditForm.sort_order"
                  type="number"
                  min="0"
                  class="h-[3.15rem] rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
              </div>

              <textarea
                v-model="itemEditForm.icon_path"
                rows="5"
                placeholder="SVG icon path"
                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <div
                class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
              >
                <label
                  class="flex items-center gap-2 text-sm font-extrabold text-slate-700"
                >
                  <input
                    v-model="itemEditForm.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                  />
                  Aktif
                </label>

                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                  <button
                    type="button"
                    class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="cancelEditItem"
                  >
                    Batal
                  </button>

                  <button
                    type="submit"
                    :disabled="itemEditForm.processing"
                    class="inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-red-600 px-5 text-sm font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {{ itemEditForm.processing ? "Menyimpan..." : "Simpan Menu" }}
                  </button>
                </div>
              </div>
            </form>
          </div>

          <div
            v-if="!filteredItems.length"
            class="rounded-[1.55rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
          >
            <div
              class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                />
              </svg>
            </div>

            <h3 class="text-xl font-black text-slate-950">Menu navbar tidak ditemukan</h3>

            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-500">
              Ubah kata kunci pencarian atau filter status agar data menu navbar dapat
              ditampilkan kembali.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
@media (max-width: 639px) {
  .custom-navbar-mobile-page,
  .custom-navbar-mobile-page * {
    box-sizing: border-box;
  }

  .custom-navbar-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-navbar-mobile-page :is(section, article, form, div) {
    min-width: 0;
  }

  .custom-navbar-mobile-page :is(input, select, textarea, button, a) {
    max-width: 100%;
  }

  .custom-navbar-mobile-page p {
    text-align: left !important;
  }

  .custom-navbar-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-navbar-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-navbar-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-navbar-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-navbar-mobile-page > section:first-of-type .relative.z-10.grid {
    gap: 1rem !important;
  }

  .custom-navbar-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-navbar-mobile-page > section:first-of-type .rounded-\[1\.65rem\] {
    border-radius: 1.05rem !important;
    padding: 0.85rem !important;
  }

  .custom-navbar-mobile-page > section:first-of-type .rounded-\[1\.5rem\] {
    border-radius: 1rem !important;
    padding: 0.85rem !important;
  }

  .custom-navbar-mobile-page > section:first-of-type .h-16.w-16 {
    width: 3rem !important;
    height: 3rem !important;
    border-radius: 0.9rem !important;
  }

  .custom-navbar-mobile-page > section:first-of-type .grid.grid-cols-3 {
    gap: 0.5rem !important;
  }

  .custom-navbar-mobile-page > section:first-of-type .grid.grid-cols-3 > div {
    padding: 0.65rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(2) > div {
    border-radius: 1.1rem !important;
    padding: 0.85rem !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.055) !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(2) h3 {
    font-size: 1.05rem !important;
    line-height: 1.15 !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(2) p {
    line-height: 1.45 !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(2) .h-11.w-11 {
    width: 2.45rem !important;
    height: 2.45rem !important;
    border-radius: 0.9rem !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(3) {
    gap: 1rem !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(3) > div,
  .custom-navbar-mobile-page > section:nth-of-type(4) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-navbar-mobile-page > section:nth-of-type(3) > div > div:not(.h-1\.5),
  .custom-navbar-mobile-page > section:nth-of-type(4) > div:not(.h-1\.5) {
    padding: 1rem !important;
  }

  .custom-navbar-mobile-page form.rounded-\[1\.55rem\],
  .custom-navbar-mobile-page .rounded-\[1\.55rem\],
  .custom-navbar-mobile-page .rounded-\[1\.65rem\],
  .custom-navbar-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-navbar-mobile-page label,
  .custom-navbar-mobile-page .text-sm.font-extrabold,
  .custom-navbar-mobile-page .text-sm.font-black {
    font-size: 0.82rem !important;
  }

  .custom-navbar-mobile-page input,
  .custom-navbar-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-navbar-mobile-page textarea {
    min-height: 118px !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-navbar-mobile-page button,
  .custom-navbar-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-navbar-mobile-page button[type="submit"],
  .custom-navbar-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-navbar-mobile-page .grid.sm\:grid-cols-2,
  .custom-navbar-mobile-page .grid.sm\:grid-cols-3,
  .custom-navbar-mobile-page .grid.lg\:min-w-\[520px\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-navbar-mobile-page .grid.grid-cols-2.gap-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-navbar-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-navbar-mobile-page .flex.flex-wrap.gap-2 > .rounded-full {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-navbar-mobile-page .break-all,
  .custom-navbar-mobile-page .break-words,
  .custom-navbar-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-navbar-mobile-page .truncate {
    min-width: 0;
  }

  .custom-navbar-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-navbar-mobile-page .h-16.w-16 {
    width: 3.2rem !important;
    height: 3.2rem !important;
  }

  .custom-navbar-mobile-page .h-14.w-14 {
    width: 2.9rem !important;
    height: 2.9rem !important;
  }

  .custom-navbar-mobile-page .p-5,
  .custom-navbar-mobile-page .sm\:p-6,
  .custom-navbar-mobile-page .lg\:p-7 {
    padding: 1rem !important;
  }

  .custom-navbar-mobile-page .mt-7 {
    margin-top: 1.25rem !important;
  }

  .custom-navbar-mobile-page .mt-6 {
    margin-top: 1.25rem !important;
  }

  .custom-navbar-mobile-page .mb-6 {
    margin-bottom: 1.25rem !important;
  }

  .custom-navbar-mobile-page .gap-5 {
    gap: 1rem !important;
  }

  .custom-navbar-mobile-page .gap-6 {
    gap: 1rem !important;
  }

  .custom-navbar-mobile-page .shadow-\[0_16px_44px_rgba\(15\,23\,42\,0\.06\)\],
  .custom-navbar-mobile-page .shadow-\[0_18px_50px_rgba\(15\,23\,42\,0\.06\)\] {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055) !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-navbar-mobile-page *,
  .custom-navbar-mobile-page *::before,
  .custom-navbar-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

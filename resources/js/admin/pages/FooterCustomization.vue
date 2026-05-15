<script setup>
import { computed, ref } from "vue";
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
  navItems: {
    type: Array,
    default: () => [],
  },
  socialLinks: {
    type: Array,
    default: () => [],
  },
  contactItems: {
    type: Array,
    default: () => [],
  },
  legalLinks: {
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
  contactTypeOptions: {
    type: Array,
    default: () => [
      { value: "location", label: "Alamat / Lokasi" },
      { value: "whatsapp", label: "WhatsApp" },
      { value: "instagram", label: "Instagram" },
      { value: "email", label: "Email" },
      { value: "info", label: "Informasi Umum" },
    ],
  },
});

const page = usePage();

const logoInput = ref(null);
const activeTab = ref("nav");
const searchKeyword = ref("");

const editingNav = ref(null);
const editingSocial = ref(null);
const editingContact = ref(null);
const editingLegal = ref(null);

const fallbackLogo = "/Images/logo/hmps-rpl-logo.png";

const footerIconPresets = {
  home: "M3 10.5 12 3l9 7.5M5 10v10h14V10M9 20v-6h6v6",
  profile: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0",
  organization: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0",
  program:
    "M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12",
  service:
    "M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z",
  consultation: "M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5",
  gallery:
    "M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01",
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

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const normalizeBoolean = (value) => {
  if (value === true || value === 1 || value === "1") return true;
  if (value === false || value === 0 || value === "0") return false;
  return Boolean(value);
};

const currentLogoPath = computed(() => props.setting?.logo_path || fallbackLogo);

const totalItems = computed(() => {
  return (
    props.navItems.length +
    props.socialLinks.length +
    props.contactItems.length +
    props.legalLinks.length
  );
});

const activeItems = computed(() => {
  return [
    ...props.navItems,
    ...props.socialLinks,
    ...props.contactItems,
    ...props.legalLinks,
  ].filter((item) => normalizeBoolean(item.is_active)).length;
});

const activeNavCount = computed(
  () => props.navItems.filter((item) => normalizeBoolean(item.is_active)).length
);

const activeSocialCount = computed(
  () => props.socialLinks.filter((item) => normalizeBoolean(item.is_active)).length
);

const activeContactCount = computed(
  () => props.contactItems.filter((item) => normalizeBoolean(item.is_active)).length
);

const activeLegalCount = computed(
  () => props.legalLinks.filter((item) => normalizeBoolean(item.is_active)).length
);

const stats = computed(() => [
  {
    label: "Brand Footer",
    value: props.setting?.brand_title || "HMPS RPL",
    helper: props.setting?.brand_subtitle || "Subtitle footer",
    icon: "brand",
  },
  {
    label: "Item Aktif",
    value: activeItems.value,
    helper: `${totalItems.value} total item footer`,
    icon: "active",
  },
  {
    label: "Navigasi",
    value: activeNavCount.value,
    helper: `${props.navItems.length} total menu`,
    icon: "nav",
  },
  {
    label: "Kontak",
    value: activeContactCount.value,
    helper: `${activeSocialCount.value} social link aktif`,
    icon: "contact",
  },
]);

const settingForm = useForm({
  logo_file: null,
  logo_path: props.setting?.logo_path || fallbackLogo,
  logo_alt: props.setting?.logo_alt || "Logo HMPS RPL",
  brand_title: props.setting?.brand_title || "HMPS RPL",
  brand_subtitle: props.setting?.brand_subtitle || "Rekayasa Perangkat Lunak",
  description:
    props.setting?.description ||
    "Wadah aspirasi, kolaborasi, dan kreativitas mahasiswa Rekayasa Perangkat Lunak.",
  identity_label: props.setting?.identity_label || "Identitas Organisasi",
  identity_text:
    props.setting?.identity_text ||
    "Himpunan Mahasiswa Program Studi Rekayasa Perangkat Lunak.",
  navigation_title: props.setting?.navigation_title || "Navigasi",
  contact_title: props.setting?.contact_title || "Kontak",
  contact_cta_label: props.setting?.contact_cta_label || "Hubungi via WhatsApp",
  contact_cta_url: props.setting?.contact_cta_url || "",
  copyright_text:
    props.setting?.copyright_text ||
    "© {year} HMPS Rekayasa Perangkat Lunak. All rights reserved.",
  is_active: normalizeBoolean(props.setting?.is_active ?? true),
});

const navForm = useForm({
  label: "",
  href: "",
  icon_path: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const navEditForm = useForm({
  label: "",
  href: "",
  icon_path: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const socialForm = useForm({
  name: "",
  href: "",
  icon_path: "",
  target: "_blank",
  is_active: true,
  sort_order: 0,
});

const socialEditForm = useForm({
  name: "",
  href: "",
  icon_path: "",
  target: "_blank",
  is_active: true,
  sort_order: 0,
});

const contactForm = useForm({
  title: "",
  value: "",
  helper: "",
  href: "",
  type: "info",
  icon_path: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const contactEditForm = useForm({
  title: "",
  value: "",
  helper: "",
  href: "",
  type: "info",
  icon_path: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const legalForm = useForm({
  label: "",
  href: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const legalEditForm = useForm({
  label: "",
  href: "",
  target: "_self",
  is_active: true,
  sort_order: 0,
});

const filterCollection = (collection, fields) => {
  const keyword = searchKeyword.value.trim().toLowerCase();

  if (!keyword) return collection;

  return collection.filter((item) =>
    fields.some((field) =>
      String(item[field] || "")
        .toLowerCase()
        .includes(keyword)
    )
  );
};

const filteredNavItems = computed(() =>
  filterCollection(props.navItems, ["label", "href"])
);

const filteredSocialLinks = computed(() =>
  filterCollection(props.socialLinks, ["name", "href"])
);

const filteredContactItems = computed(() =>
  filterCollection(props.contactItems, ["title", "value", "helper", "type", "href"])
);

const filteredLegalLinks = computed(() =>
  filterCollection(props.legalLinks, ["label", "href"])
);

const statusClass = (status) =>
  normalizeBoolean(status)
    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
    : "border-slate-200 bg-slate-100 text-slate-600";

const targetLabel = (targetValue) =>
  props.targetOptions.find((target) => target.value === targetValue)?.label ||
  targetValue ||
  "Halaman yang sama";

const contactTypeLabel = (typeValue) =>
  props.contactTypeOptions.find((type) => type.value === typeValue)?.label ||
  typeValue ||
  "Informasi Umum";

const shortPreview = (text, length = 72) => {
  if (!text) return "Tidak diisi";
  return text.length > length ? `${text.slice(0, length)}...` : text;
};

const normalizeWhatsappNumber = (value) => {
  const clean = String(value || "").replace(/[^0-9]/g, "");

  if (!clean) return "";
  if (clean.startsWith("0")) return `62${clean.slice(1)}`;
  if (clean.startsWith("8")) return `62${clean}`;

  return clean;
};

const handleLogoChange = (event) => {
  settingForm.logo_file = event.target.files?.[0] || null;
};

const applyContactPreset = (form) => {
  const type = form.type || "info";

  if (!form.icon_path) {
    form.icon_path = footerIconPresets[type] || footerIconPresets.info;
  }

  if (type === "email") {
    if (!form.title) form.title = "Email";
    if (!form.helper) form.helper = "Kirim email";
    if (form.value && !form.href) {
      form.href = `mailto:${form.value}`;
    }
    form.target = "_blank";
  }

  if (type === "whatsapp") {
    if (!form.helper) form.helper = "WhatsApp HMPS RPL";

    if (!form.href) {
      const number = normalizeWhatsappNumber(form.value || form.title);

      if (number) {
        form.href = `https://wa.me/${number}`;
      }
    }

    form.target = "_blank";
  }

  if (type === "instagram") {
    if (!form.helper) form.helper = "Media sosial resmi";
    form.target = "_blank";
  }

  if (type === "location") {
    if (!form.helper) form.helper = "Lokasi sekretariat";
    if (!form.target) form.target = "_self";
  }

  if (type === "info") {
    if (!form.icon_path) {
      form.icon_path = footerIconPresets.info;
    }
  }
};

const applyNavIconPreset = (form) => {
  if (form.icon_path) return;

  const href = String(form.href || "").toLowerCase();

  if (href === "/") form.icon_path = footerIconPresets.home;
  else if (href.includes("profil")) form.icon_path = footerIconPresets.profile;
  else if (href.includes("kepengurusan")) form.icon_path = footerIconPresets.organization;
  else if (href.includes("program")) form.icon_path = footerIconPresets.program;
  else if (href.includes("layanan")) form.icon_path = footerIconPresets.service;
  else if (href.includes("konsultasi")) form.icon_path = footerIconPresets.consultation;
  else if (href.includes("dokumentasi")) form.icon_path = footerIconPresets.gallery;
  else form.icon_path = footerIconPresets.info;
};

const applySocialPreset = (form) => {
  const name = String(form.name || "").toLowerCase();
  const href = String(form.href || "").toLowerCase();

  if (!form.icon_path && (name.includes("instagram") || href.includes("instagram"))) {
    form.icon_path = footerIconPresets.instagram;
  }

  if (!form.icon_path) {
    form.icon_path = footerIconPresets.info;
  }

  if (!form.target) {
    form.target = "_blank";
  }
};

const updateSetting = () => {
  settingForm.post("/admin/footer/settings", {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      settingForm.logo_file = null;

      if (logoInput.value) {
        logoInput.value.value = "";
      }
    },
  });
};

const resetNavForm = () => {
  navForm.reset();
  navForm.target = "_self";
  navForm.is_active = true;
  navForm.sort_order = 0;
};

const storeNav = () => {
  applyNavIconPreset(navForm);

  navForm.post("/admin/footer/nav-items", {
    preserveScroll: true,
    onSuccess: () => resetNavForm(),
  });
};

const editNav = (item) => {
  editingNav.value = item;
  navEditForm.label = item.label || "";
  navEditForm.href = item.href || "";
  navEditForm.icon_path = item.icon_path || "";
  navEditForm.target = item.target || "_self";
  navEditForm.is_active = normalizeBoolean(item.is_active);
  navEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditNav = () => {
  editingNav.value = null;
  navEditForm.reset();
};

const updateNav = () => {
  if (!editingNav.value) return;

  applyNavIconPreset(navEditForm);

  navEditForm.put(`/admin/footer/nav-items/${editingNav.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditNav(),
  });
};

const deleteNav = (item) => {
  if (!confirm(`Hapus menu footer "${item.label}"?`)) return;

  router.delete(`/admin/footer/nav-items/${item.id}`, {
    preserveScroll: true,
  });
};

const resetSocialForm = () => {
  socialForm.reset();
  socialForm.target = "_blank";
  socialForm.is_active = true;
  socialForm.sort_order = 0;
};

const storeSocial = () => {
  applySocialPreset(socialForm);

  socialForm.post("/admin/footer/social-links", {
    preserveScroll: true,
    onSuccess: () => resetSocialForm(),
  });
};

const editSocial = (item) => {
  editingSocial.value = item;
  socialEditForm.name = item.name || "";
  socialEditForm.href = item.href || "";
  socialEditForm.icon_path = item.icon_path || "";
  socialEditForm.target = item.target || "_blank";
  socialEditForm.is_active = normalizeBoolean(item.is_active);
  socialEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditSocial = () => {
  editingSocial.value = null;
  socialEditForm.reset();
};

const updateSocial = () => {
  if (!editingSocial.value) return;

  applySocialPreset(socialEditForm);

  socialEditForm.put(`/admin/footer/social-links/${editingSocial.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditSocial(),
  });
};

const deleteSocial = (item) => {
  if (!confirm(`Hapus social link "${item.name}"?`)) return;

  router.delete(`/admin/footer/social-links/${item.id}`, {
    preserveScroll: true,
  });
};

const resetContactForm = () => {
  contactForm.reset();
  contactForm.type = "info";
  contactForm.target = "_self";
  contactForm.is_active = true;
  contactForm.sort_order = 0;
};

const storeContact = () => {
  applyContactPreset(contactForm);

  contactForm.post("/admin/footer/contact-items", {
    preserveScroll: true,
    onSuccess: () => resetContactForm(),
  });
};

const editContact = (item) => {
  editingContact.value = item;
  contactEditForm.title = item.title || "";
  contactEditForm.value = item.value || "";
  contactEditForm.helper = item.helper || "";
  contactEditForm.href = item.href || "";
  contactEditForm.type = item.type || "info";
  contactEditForm.icon_path = item.icon_path || "";
  contactEditForm.target = item.target || "_self";
  contactEditForm.is_active = normalizeBoolean(item.is_active);
  contactEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditContact = () => {
  editingContact.value = null;
  contactEditForm.reset();
};

const updateContact = () => {
  if (!editingContact.value) return;

  applyContactPreset(contactEditForm);

  contactEditForm.put(`/admin/footer/contact-items/${editingContact.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditContact(),
  });
};

const deleteContact = (item) => {
  if (!confirm(`Hapus kontak footer "${item.title}"?`)) return;

  router.delete(`/admin/footer/contact-items/${item.id}`, {
    preserveScroll: true,
  });
};

const resetLegalForm = () => {
  legalForm.reset();
  legalForm.target = "_self";
  legalForm.is_active = true;
  legalForm.sort_order = 0;
};

const storeLegal = () => {
  legalForm.post("/admin/footer/legal-links", {
    preserveScroll: true,
    onSuccess: () => resetLegalForm(),
  });
};

const editLegal = (item) => {
  editingLegal.value = item;
  legalEditForm.label = item.label || "";
  legalEditForm.href = item.href || "";
  legalEditForm.target = item.target || "_self";
  legalEditForm.is_active = normalizeBoolean(item.is_active);
  legalEditForm.sort_order = Number(item.sort_order || 0);
};

const cancelEditLegal = () => {
  editingLegal.value = null;
  legalEditForm.reset();
};

const updateLegal = () => {
  if (!editingLegal.value) return;

  legalEditForm.put(`/admin/footer/legal-links/${editingLegal.value.id}`, {
    preserveScroll: true,
    onSuccess: () => cancelEditLegal(),
  });
};

const deleteLegal = (item) => {
  if (!confirm(`Hapus legal link "${item.label}"?`)) return;

  router.delete(`/admin/footer/legal-links/${item.id}`, {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Custom Footer" />

  <div class="custom-footer-mobile-page space-y-5 overflow-x-hidden pb-8 sm:space-y-8">
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
              Admin / Custom Footer
            </div>

            <div
              class="inline-flex items-center gap-1.5 rounded-full bg-white/[0.07] px-3 py-1.5 text-[0.68rem] font-semibold text-slate-300 ring-1 ring-white/10"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
              {{ settingForm.is_active ? "Footer Aktif" : "Footer Nonaktif" }}
            </div>
          </div>

          <p class="mb-1 text-sm font-bold tracking-wide text-red-300/80">
            Panel Pengaturan Footer
          </p>

          <h1
            class="max-w-3xl text-[2rem] font-black leading-[1.06] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.1rem]"
          >
            Kelola Footer
            <span
              class="block bg-[linear-gradient(90deg,#fca5a5,#f87171)] bg-clip-text text-transparent"
            >
              Website HMPS RPL
            </span>
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300/90 sm:text-[0.95rem]"
          >
            Atur brand footer, navigasi, social media, kontak, email, CTA, dan legal link
            agar footer user tampil dinamis, rapi, konsisten, dan tetap selaras dengan
            identitas HMPS RPL.
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
                Tampilan Brand Footer
              </h2>
            </div>
            <span
              class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-emerald-200"
            >
              {{ activeItems }} Aktif
            </span>
          </div>

          <div
            class="rounded-[1.5rem] border border-white/10 bg-white/[0.08] p-4 shadow-[0_16px_40px_rgba(2,6,23,0.12)]"
          >
            <div class="flex items-start gap-4">
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
                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-400">
                  {{ settingForm.description }}
                </p>
              </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-2">
              <div
                class="rounded-2xl border border-white/10 bg-white/[0.07] p-3 text-center"
              >
                <p class="text-lg font-black leading-none text-white">{{ totalItems }}</p>
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
                  {{ activeItems }}
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
                  {{ props.legalLinks.length }}
                </p>
                <p
                  class="mt-1 text-[0.62rem] font-black uppercase tracking-[0.1em] text-violet-300/80"
                >
                  Legal
                </p>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <span
                class="rounded-full border px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                :class="statusClass(settingForm.is_active)"
              >
                {{ settingForm.is_active ? "Footer Aktif" : "Footer Nonaktif" }}
              </span>
              <span
                class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[0.68rem] font-black uppercase tracking-[0.08em] text-red-700"
              >
                {{ props.navItems.length }} Navigasi
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div
      v-if="flashSuccess"
      class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_10px_28px_rgba(16,185,129,0.08)]"
    >
      {{ flashSuccess }}
    </div>

    <div
      v-if="flashError"
      class="rounded-[1.35rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_10px_28px_rgba(239,68,68,0.08)]"
    >
      {{ flashError }}
    </div>

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
                v-else-if="item.icon === 'nav'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M4 6h16M4 12h16M4 18h10"
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
    <section class="grid gap-6 xl:grid-cols-[0.44fr_0.56fr]">
      <div
        class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white p-5 shadow-[0_16px_44px_rgba(15,23,42,0.06)] sm:p-6 lg:p-7"
      >
        <h2 class="text-lg font-black text-slate-950">Pengaturan Utama Footer</h2>

        <p class="mt-2 text-sm leading-7 text-slate-500">
          Bagian ini mengatur logo, teks brand, deskripsi, identitas organisasi, judul
          kolom, tombol CTA, dan copyright.
        </p>

        <form class="mt-6 grid gap-4" @submit.prevent="updateSetting">
          <div class="rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5">
            <p class="mb-3 text-xs font-black uppercase tracking-[0.12em] text-slate-500">
              Preview Brand Footer
            </p>

            <div class="flex items-center gap-4">
              <div
                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"
              >
                <img
                  :src="currentLogoPath"
                  :alt="settingForm.logo_alt"
                  class="h-full w-full object-cover"
                />
              </div>

              <div class="min-w-0">
                <p class="truncate text-base font-black text-slate-950">
                  {{ settingForm.brand_title || "Brand Title" }}
                </p>
                <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                  {{ settingForm.brand_subtitle || "Brand Subtitle" }}
                </p>

                <p
                  v-if="settingForm.logo_file"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  File baru: {{ settingForm.logo_file.name }}
                </p>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">
              Upload Logo Footer
            </label>

            <input
              ref="logoInput"
              type="file"
              accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none file:mr-4 file:rounded-xl file:border-0 file:bg-red-600 file:px-4 file:py-2 file:text-sm file:font-black file:text-white hover:file:bg-red-700 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              @change="handleLogoChange"
            />

            <p
              v-if="settingForm.errors.logo_file"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ settingForm.errors.logo_file }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Path Logo</label>

            <input
              v-model="settingForm.logo_path"
              type="text"
              placeholder="/Images/logo/hmps-rpl-logo.png"
              class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p
              v-if="settingForm.errors.logo_path"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ settingForm.errors.logo_path }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Alt Logo</label>

            <input
              v-model="settingForm.logo_alt"
              type="text"
              placeholder="Logo HMPS RPL"
              class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p
              v-if="settingForm.errors.logo_alt"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ settingForm.errors.logo_alt }}
            </p>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-700">
                Brand Title
              </label>

              <input
                v-model="settingForm.brand_title"
                type="text"
                placeholder="HMPS RPL"
                class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="settingForm.errors.brand_title"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ settingForm.errors.brand_title }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-700">
                Brand Subtitle
              </label>

              <input
                v-model="settingForm.brand_subtitle"
                type="text"
                placeholder="Rekayasa Perangkat Lunak"
                class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="settingForm.errors.brand_subtitle"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ settingForm.errors.brand_subtitle }}
              </p>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">
              Deskripsi Footer
            </label>

            <textarea
              v-model="settingForm.description"
              rows="4"
              placeholder="Deskripsi footer..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p
              v-if="settingForm.errors.description"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ settingForm.errors.description }}
            </p>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-700">
                Label Identitas
              </label>

              <input
                v-model="settingForm.identity_label"
                type="text"
                placeholder="Identitas Organisasi"
                class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="settingForm.errors.identity_label"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ settingForm.errors.identity_label }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-700">
                Judul Navigasi
              </label>

              <input
                v-model="settingForm.navigation_title"
                type="text"
                placeholder="Navigasi"
                class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="settingForm.errors.navigation_title"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ settingForm.errors.navigation_title }}
              </p>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">
              Teks Identitas
            </label>

            <textarea
              v-model="settingForm.identity_text"
              rows="3"
              placeholder="Himpunan Mahasiswa Program Studi..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p
              v-if="settingForm.errors.identity_text"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ settingForm.errors.identity_text }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">Copyright</label>

            <input
              v-model="settingForm.copyright_text"
              type="text"
              placeholder="© {year} HMPS Rekayasa Perangkat Lunak. All rights reserved."
              class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
            />

            <p class="mt-2 text-xs font-semibold text-slate-500">
              Gunakan <span class="font-black">{year}</span> agar tahun otomatis berubah.
            </p>

            <p
              v-if="settingForm.errors.copyright_text"
              class="mt-2 text-xs font-bold text-red-600"
            >
              {{ settingForm.errors.copyright_text }}
            </p>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-4">
            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
              <input
                v-model="settingForm.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
              />
              Footer Setting Aktif
            </label>

            <button
              type="submit"
              :disabled="settingForm.processing"
              class="inline-flex min-h-[50px] items-center justify-center rounded-2xl bg-red-600 px-6 text-sm font-black text-white shadow-lg shadow-red-500/20 transition hover:-translate-y-[2px] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ settingForm.processing ? "Menyimpan..." : "Simpan Pengaturan" }}
            </button>
          </div>
        </form>
      </div>

      <div
        class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white p-5 shadow-[0_16px_44px_rgba(15,23,42,0.06)] sm:p-6 lg:p-7"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h2 class="text-lg font-black text-slate-950">Kelola Item Footer</h2>
            <p class="mt-2 text-sm leading-7 text-slate-500">
              Pilih tab untuk mengelola navigasi, social media, kontak, atau legal link.
            </p>
          </div>

          <input
            v-model="searchKeyword"
            type="text"
            placeholder="Cari item..."
            class="h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 lg:w-64"
          />
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-2xl px-4 py-2 text-sm font-black transition"
            :class="
              activeTab === 'nav'
                ? 'bg-red-600 text-white'
                : 'bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700'
            "
            @click="activeTab = 'nav'"
          >
            Navigasi
            <span class="ml-1 text-xs opacity-80">({{ props.navItems.length }})</span>
          </button>

          <button
            type="button"
            class="rounded-2xl px-4 py-2 text-sm font-black transition"
            :class="
              activeTab === 'social'
                ? 'bg-red-600 text-white'
                : 'bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700'
            "
            @click="activeTab = 'social'"
          >
            Social
            <span class="ml-1 text-xs opacity-80">({{ props.socialLinks.length }})</span>
          </button>

          <button
            type="button"
            class="rounded-2xl px-4 py-2 text-sm font-black transition"
            :class="
              activeTab === 'contact'
                ? 'bg-red-600 text-white'
                : 'bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700'
            "
            @click="activeTab = 'contact'"
          >
            Kontak
            <span class="ml-1 text-xs opacity-80">({{ props.contactItems.length }})</span>
          </button>

          <button
            type="button"
            class="rounded-2xl px-4 py-2 text-sm font-black transition"
            :class="
              activeTab === 'legal'
                ? 'bg-red-600 text-white'
                : 'bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700'
            "
            @click="activeTab = 'legal'"
          >
            Legal
            <span class="ml-1 text-xs opacity-80">({{ props.legalLinks.length }})</span>
          </button>
        </div>
        <!-- NAV TAB -->
        <div v-if="activeTab === 'nav'" class="mt-6 space-y-5">
          <form
            class="grid gap-4 rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5"
            @submit.prevent="storeNav"
          >
            <div>
              <p class="text-sm font-black text-slate-950">Tambah Menu Navigasi</p>
              <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                Menu ini akan tampil pada kolom navigasi footer user.
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Label Menu
                </label>

                <input
                  v-model="navForm.label"
                  type="text"
                  placeholder="Profil"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="navForm.errors.label"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ navForm.errors.label }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Link / URL
                </label>

                <input
                  v-model="navForm.href"
                  type="text"
                  placeholder="/profil"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p v-if="navForm.errors.href" class="mt-2 text-xs font-bold text-red-600">
                  {{ navForm.errors.href }}
                </p>
              </div>
            </div>

            <div>
              <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <label class="block text-sm font-bold text-slate-700">
                  SVG Icon Path
                </label>

                <button
                  type="button"
                  class="inline-flex min-h-[34px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-[0.7rem] font-black text-red-700 transition hover:bg-red-100"
                  @click="applyNavIconPreset(navForm)"
                >
                  Gunakan Icon Otomatis
                </button>
              </div>

              <textarea
                v-model="navForm.icon_path"
                rows="3"
                placeholder="SVG icon path opsional"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="navForm.errors.icon_path"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ navForm.errors.icon_path }}
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <select
                v-model="navForm.target"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="target in props.targetOptions"
                  :key="target.value"
                  :value="target.value"
                >
                  {{ target.label }}
                </option>
              </select>

              <input
                v-model="navForm.sort_order"
                type="number"
                min="0"
                class="h-11 w-28 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input
                  v-model="navForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600"
                />
                Aktif
              </label>

              <button
                type="submit"
                :disabled="navForm.processing"
                class="ml-auto inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-xs font-black text-white transition hover:-translate-y-[1px] hover:bg-red-700 disabled:opacity-60"
              >
                {{ navForm.processing ? "Menyimpan..." : "Tambah Menu" }}
              </button>
            </div>
          </form>

          <div class="space-y-3">
            <div
              v-if="!filteredNavItems.length"
              class="rounded-[1.45rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
              <p class="text-sm font-black text-slate-700">Belum ada menu navigasi.</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">
                Tambahkan menu footer melalui form di atas.
              </p>
            </div>

            <div
              v-for="item in filteredNavItems"
              :key="item.id"
              class="rounded-[1.45rem] border border-slate-200 bg-slate-50 p-5"
            >
              <div
                v-if="editingNav?.id !== item.id"
                class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black text-red-700"
                    >
                      {{ item.href }}
                    </span>
                    <span
                      class="rounded-full px-3 py-1 text-[0.68rem] font-black"
                      :class="statusClass(item.is_active)"
                    >
                      {{ normalizeBoolean(item.is_active) ? "Aktif" : "Nonaktif" }}
                    </span>
                    <span
                      class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black text-slate-600"
                    >
                      Urutan {{ item.sort_order }}
                    </span>
                  </div>

                  <h3 class="mt-3 text-base font-black text-slate-950">
                    {{ item.label }}
                  </h3>

                  <p class="mt-1 text-xs font-semibold text-slate-500">
                    Target: {{ targetLabel(item.target) }}
                  </p>

                  <p class="mt-2 max-w-full break-all text-xs leading-5 text-slate-400">
                    Icon: {{ shortPreview(item.icon_path) }}
                  </p>
                </div>

                <div
                  class="grid grid-cols-2 gap-2 sm:flex sm:flex-row xl:w-[11rem] xl:flex-col"
                >
                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="editNav(item)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteNav(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-3" @submit.prevent="updateNav">
                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <input
                      v-model="navEditForm.label"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="navEditForm.errors.label"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ navEditForm.errors.label }}
                    </p>
                  </div>

                  <div>
                    <input
                      v-model="navEditForm.href"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="navEditForm.errors.href"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ navEditForm.errors.href }}
                    </p>
                  </div>
                </div>

                <div>
                  <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label
                      class="text-xs font-black uppercase tracking-[0.1em] text-slate-500"
                    >
                      SVG Icon Path
                    </label>

                    <button
                      type="button"
                      class="inline-flex min-h-[32px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-[0.68rem] font-black text-red-700 transition hover:bg-red-100"
                      @click="applyNavIconPreset(navEditForm)"
                    >
                      Gunakan Icon Otomatis
                    </button>
                  </div>

                  <textarea
                    v-model="navEditForm.icon_path"
                    rows="3"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="navEditForm.errors.icon_path"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ navEditForm.errors.icon_path }}
                  </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <select
                    v-model="navEditForm.target"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="target in props.targetOptions"
                      :key="target.value"
                      :value="target.value"
                    >
                      {{ target.label }}
                    </option>
                  </select>

                  <input
                    v-model="navEditForm.sort_order"
                    type="number"
                    min="0"
                    class="h-10 w-24 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                    <input
                      v-model="navEditForm.is_active"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600"
                    />
                    Aktif
                  </label>

                  <button
                    type="submit"
                    :disabled="navEditForm.processing"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                  >
                    {{ navEditForm.processing ? "Menyimpan..." : "Simpan" }}
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="cancelEditNav"
                  >
                    Batal
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- SOCIAL TAB -->
        <div v-if="activeTab === 'social'" class="mt-6 space-y-5">
          <form
            class="grid gap-4 rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5"
            @submit.prevent="storeSocial"
          >
            <div>
              <p class="text-sm font-black text-slate-950">Tambah Social Link</p>
              <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                Social link akan tampil sebagai ikon social media pada footer user.
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Nama Social
                </label>

                <input
                  v-model="socialForm.name"
                  type="text"
                  placeholder="Instagram"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="socialForm.errors.name"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ socialForm.errors.name }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Link Social
                </label>

                <input
                  v-model="socialForm.href"
                  type="text"
                  placeholder="https://instagram.com/..."
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="socialForm.errors.href"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ socialForm.errors.href }}
                </p>
              </div>
            </div>

            <div>
              <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <label class="block text-sm font-bold text-slate-700">
                  SVG Icon Path
                </label>

                <button
                  type="button"
                  class="inline-flex min-h-[34px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-[0.7rem] font-black text-red-700 transition hover:bg-red-100"
                  @click="applySocialPreset(socialForm)"
                >
                  Gunakan Icon Otomatis
                </button>
              </div>

              <textarea
                v-model="socialForm.icon_path"
                rows="4"
                placeholder="SVG icon path wajib diisi untuk social link"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="socialForm.errors.icon_path"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ socialForm.errors.icon_path }}
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <select
                v-model="socialForm.target"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="target in props.targetOptions"
                  :key="target.value"
                  :value="target.value"
                >
                  {{ target.label }}
                </option>
              </select>

              <input
                v-model="socialForm.sort_order"
                type="number"
                min="0"
                class="h-11 w-28 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input
                  v-model="socialForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600"
                />
                Aktif
              </label>

              <button
                type="submit"
                :disabled="socialForm.processing"
                class="ml-auto inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-xs font-black text-white transition hover:-translate-y-[1px] hover:bg-red-700 disabled:opacity-60"
              >
                {{ socialForm.processing ? "Menyimpan..." : "Tambah Social" }}
              </button>
            </div>
          </form>

          <div class="space-y-3">
            <div
              v-if="!filteredSocialLinks.length"
              class="rounded-[1.45rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
              <p class="text-sm font-black text-slate-700">Belum ada social link.</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">
                Tambahkan social media melalui form di atas.
              </p>
            </div>

            <div
              v-for="item in filteredSocialLinks"
              :key="item.id"
              class="rounded-[1.45rem] border border-slate-200 bg-slate-50 p-5"
            >
              <div
                v-if="editingSocial?.id !== item.id"
                class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black text-red-700"
                    >
                      {{ item.name }}
                    </span>
                    <span
                      class="rounded-full px-3 py-1 text-[0.68rem] font-black"
                      :class="statusClass(item.is_active)"
                    >
                      {{ normalizeBoolean(item.is_active) ? "Aktif" : "Nonaktif" }}
                    </span>
                    <span
                      class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black text-slate-600"
                    >
                      Urutan {{ item.sort_order }}
                    </span>
                  </div>

                  <p class="mt-3 break-all text-sm font-semibold text-slate-700">
                    {{ item.href }}
                  </p>

                  <p class="mt-2 text-xs font-semibold text-slate-500">
                    Target: {{ targetLabel(item.target) }}
                  </p>

                  <p class="mt-2 max-w-full break-all text-xs leading-5 text-slate-400">
                    Icon: {{ shortPreview(item.icon_path) }}
                  </p>
                </div>

                <div
                  class="grid grid-cols-2 gap-2 sm:flex sm:flex-row xl:w-[11rem] xl:flex-col"
                >
                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="editSocial(item)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteSocial(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-3" @submit.prevent="updateSocial">
                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <input
                      v-model="socialEditForm.name"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="socialEditForm.errors.name"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ socialEditForm.errors.name }}
                    </p>
                  </div>

                  <div>
                    <input
                      v-model="socialEditForm.href"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="socialEditForm.errors.href"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ socialEditForm.errors.href }}
                    </p>
                  </div>
                </div>

                <div>
                  <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label
                      class="text-xs font-black uppercase tracking-[0.1em] text-slate-500"
                    >
                      SVG Icon Path
                    </label>

                    <button
                      type="button"
                      class="inline-flex min-h-[32px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-[0.68rem] font-black text-red-700 transition hover:bg-red-100"
                      @click="applySocialPreset(socialEditForm)"
                    >
                      Gunakan Icon Otomatis
                    </button>
                  </div>

                  <textarea
                    v-model="socialEditForm.icon_path"
                    rows="4"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="socialEditForm.errors.icon_path"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ socialEditForm.errors.icon_path }}
                  </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <select
                    v-model="socialEditForm.target"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="target in props.targetOptions"
                      :key="target.value"
                      :value="target.value"
                    >
                      {{ target.label }}
                    </option>
                  </select>

                  <input
                    v-model="socialEditForm.sort_order"
                    type="number"
                    min="0"
                    class="h-10 w-24 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                    <input
                      v-model="socialEditForm.is_active"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600"
                    />
                    Aktif
                  </label>

                  <button
                    type="submit"
                    :disabled="socialEditForm.processing"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                  >
                    {{ socialEditForm.processing ? "Menyimpan..." : "Simpan" }}
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="cancelEditSocial"
                  >
                    Batal
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- CONTACT TAB -->
        <div v-if="activeTab === 'contact'" class="mt-6 space-y-5">
          <form
            class="grid gap-4 rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5"
            @submit.prevent="storeContact"
          >
            <div>
              <p class="text-sm font-black text-slate-950">Tambah Kontak Footer</p>
              <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                Kontak ini dipakai footer user untuk alamat, WhatsApp, Instagram, email,
                atau informasi umum. Pilih tipe kontak agar sistem bisa membantu membuat
                icon dan link otomatis.
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Judul Kontak
                </label>

                <input
                  v-model="contactForm.title"
                  type="text"
                  placeholder="Email"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="contactForm.errors.title"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ contactForm.errors.title }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Isi Kontak
                </label>

                <input
                  v-model="contactForm.value"
                  type="text"
                  placeholder="email@domain.com / 628xxx / alamat"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="contactForm.errors.value"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ contactForm.errors.value }}
                </p>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Helper Text
                </label>

                <input
                  v-model="contactForm.helper"
                  type="text"
                  placeholder="Kirim email resmi HMPS RPL"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="contactForm.errors.helper"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ contactForm.errors.helper }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Link Kontak
                </label>

                <input
                  v-model="contactForm.href"
                  type="text"
                  placeholder="mailto:email@domain.com / https://wa.me/628..."
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="contactForm.errors.href"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ contactForm.errors.href }}
                </p>
              </div>
            </div>

            <div>
              <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <label class="block text-sm font-bold text-slate-700">
                  SVG Icon Path
                </label>

                <button
                  type="button"
                  class="inline-flex min-h-[34px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-[0.7rem] font-black text-red-700 transition hover:bg-red-100"
                  @click="applyContactPreset(contactForm)"
                >
                  Gunakan Icon & Link Otomatis
                </button>
              </div>

              <textarea
                v-model="contactForm.icon_path"
                rows="3"
                placeholder="SVG icon path opsional"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium leading-7 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <p
                v-if="contactForm.errors.icon_path"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ contactForm.errors.icon_path }}
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <select
                v-model="contactForm.type"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="type in props.contactTypeOptions"
                  :key="type.value"
                  :value="type.value"
                >
                  {{ type.label }}
                </option>
              </select>

              <select
                v-model="contactForm.target"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="target in props.targetOptions"
                  :key="target.value"
                  :value="target.value"
                >
                  {{ target.label }}
                </option>
              </select>

              <input
                v-model="contactForm.sort_order"
                type="number"
                min="0"
                class="h-11 w-28 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input
                  v-model="contactForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600"
                />
                Aktif
              </label>

              <button
                type="submit"
                :disabled="contactForm.processing"
                class="ml-auto inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-xs font-black text-white transition hover:-translate-y-[1px] hover:bg-red-700 disabled:opacity-60"
              >
                {{ contactForm.processing ? "Menyimpan..." : "Tambah Kontak" }}
              </button>
            </div>
          </form>

          <div class="space-y-3">
            <div
              v-if="!filteredContactItems.length"
              class="rounded-[1.45rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
              <p class="text-sm font-black text-slate-700">Belum ada kontak footer.</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">
                Tambahkan kontak seperti alamat, WhatsApp, Instagram, atau email.
              </p>
            </div>

            <div
              v-for="item in filteredContactItems"
              :key="item.id"
              class="rounded-[1.45rem] border border-slate-200 bg-slate-50 p-5"
            >
              <div
                v-if="editingContact?.id !== item.id"
                class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black text-red-700"
                    >
                      {{ contactTypeLabel(item.type) }}
                    </span>
                    <span
                      class="rounded-full px-3 py-1 text-[0.68rem] font-black"
                      :class="statusClass(item.is_active)"
                    >
                      {{ normalizeBoolean(item.is_active) ? "Aktif" : "Nonaktif" }}
                    </span>
                    <span
                      class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black text-slate-600"
                    >
                      Urutan {{ item.sort_order }}
                    </span>
                  </div>

                  <h3 class="mt-3 text-base font-black text-slate-950">
                    {{ item.title }}
                  </h3>

                  <p class="mt-1 break-words text-sm text-slate-600">
                    {{ item.value || "Tidak ada value" }}
                  </p>

                  <p class="mt-1 text-xs font-semibold text-slate-400">
                    {{ item.helper || "Tidak ada helper" }}
                  </p>

                  <p class="mt-2 break-all text-xs font-semibold text-slate-400">
                    Link: {{ item.href || "Tidak ada link" }}
                  </p>

                  <p class="mt-2 max-w-full break-all text-xs leading-5 text-slate-400">
                    Icon: {{ shortPreview(item.icon_path) }}
                  </p>
                </div>

                <div
                  class="grid grid-cols-2 gap-2 sm:flex sm:flex-row xl:w-[11rem] xl:flex-col"
                >
                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="editContact(item)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteContact(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-3" @submit.prevent="updateContact">
                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <input
                      v-model="contactEditForm.title"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="contactEditForm.errors.title"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ contactEditForm.errors.title }}
                    </p>
                  </div>

                  <div>
                    <input
                      v-model="contactEditForm.value"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="contactEditForm.errors.value"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ contactEditForm.errors.value }}
                    </p>
                  </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <input
                      v-model="contactEditForm.helper"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="contactEditForm.errors.helper"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ contactEditForm.errors.helper }}
                    </p>
                  </div>

                  <div>
                    <input
                      v-model="contactEditForm.href"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="contactEditForm.errors.href"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ contactEditForm.errors.href }}
                    </p>
                  </div>
                </div>

                <div>
                  <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label
                      class="text-xs font-black uppercase tracking-[0.1em] text-slate-500"
                    >
                      SVG Icon Path
                    </label>

                    <button
                      type="button"
                      class="inline-flex min-h-[32px] items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-[0.68rem] font-black text-red-700 transition hover:bg-red-100"
                      @click="applyContactPreset(contactEditForm)"
                    >
                      Gunakan Icon & Link Otomatis
                    </button>
                  </div>

                  <textarea
                    v-model="contactEditForm.icon_path"
                    rows="3"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <p
                    v-if="contactEditForm.errors.icon_path"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ contactEditForm.errors.icon_path }}
                  </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <select
                    v-model="contactEditForm.type"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="type in props.contactTypeOptions"
                      :key="type.value"
                      :value="type.value"
                    >
                      {{ type.label }}
                    </option>
                  </select>

                  <select
                    v-model="contactEditForm.target"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="target in props.targetOptions"
                      :key="target.value"
                      :value="target.value"
                    >
                      {{ target.label }}
                    </option>
                  </select>

                  <input
                    v-model="contactEditForm.sort_order"
                    type="number"
                    min="0"
                    class="h-10 w-24 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                    <input
                      v-model="contactEditForm.is_active"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600"
                    />
                    Aktif
                  </label>

                  <button
                    type="submit"
                    :disabled="contactEditForm.processing"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                  >
                    {{ contactEditForm.processing ? "Menyimpan..." : "Simpan" }}
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="cancelEditContact"
                  >
                    Batal
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- LEGAL TAB -->
        <div v-if="activeTab === 'legal'" class="mt-6 space-y-5">
          <form
            class="grid gap-4 rounded-[1.55rem] border border-slate-200 bg-slate-50 p-5"
            @submit.prevent="storeLegal"
          >
            <div>
              <p class="text-sm font-black text-slate-950">Tambah Legal Link</p>
              <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                Legal link bisa dibuat aktif sebagai tautan atau dibiarkan tanpa href jika
                halaman kebijakan belum tersedia.
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Label Legal
                </label>

                <input
                  v-model="legalForm.label"
                  type="text"
                  placeholder="Kebijakan Privasi"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="legalForm.errors.label"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ legalForm.errors.label }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                  Link Legal
                </label>

                <input
                  v-model="legalForm.href"
                  type="text"
                  placeholder="/kebijakan-privasi atau kosongkan"
                  class="h-[3.15rem] w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
                />

                <p
                  v-if="legalForm.errors.href"
                  class="mt-2 text-xs font-bold text-red-600"
                >
                  {{ legalForm.errors.href }}
                </p>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <select
                v-model="legalForm.target"
                class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              >
                <option
                  v-for="target in props.targetOptions"
                  :key="target.value"
                  :value="target.value"
                >
                  {{ target.label }}
                </option>
              </select>

              <input
                v-model="legalForm.sort_order"
                type="number"
                min="0"
                class="h-11 w-28 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
              />

              <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input
                  v-model="legalForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-red-600"
                />
                Aktif
              </label>

              <button
                type="submit"
                :disabled="legalForm.processing"
                class="ml-auto inline-flex min-h-[46px] items-center justify-center rounded-2xl bg-slate-950 px-5 text-xs font-black text-white transition hover:-translate-y-[1px] hover:bg-red-700 disabled:opacity-60"
              >
                {{ legalForm.processing ? "Menyimpan..." : "Tambah Legal Link" }}
              </button>
            </div>
          </form>

          <div class="space-y-3">
            <div
              v-if="!filteredLegalLinks.length"
              class="rounded-[1.45rem] border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
              <p class="text-sm font-black text-slate-700">Belum ada legal link.</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">
                Tambahkan legal link melalui form di atas.
              </p>
            </div>

            <div
              v-for="item in filteredLegalLinks"
              :key="item.id"
              class="rounded-[1.45rem] border border-slate-200 bg-slate-50 p-5"
            >
              <div
                v-if="editingLegal?.id !== item.id"
                class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start"
              >
                <div class="min-w-0">
                  <div class="flex flex-wrap gap-2">
                    <span
                      class="rounded-full bg-red-50 px-3 py-1 text-[0.68rem] font-black text-red-700"
                    >
                      {{ item.href || "Belum ada halaman" }}
                    </span>
                    <span
                      class="rounded-full px-3 py-1 text-[0.68rem] font-black"
                      :class="statusClass(item.is_active)"
                    >
                      {{ normalizeBoolean(item.is_active) ? "Aktif" : "Nonaktif" }}
                    </span>
                    <span
                      class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black text-slate-600"
                    >
                      Urutan {{ item.sort_order }}
                    </span>
                  </div>

                  <h3 class="mt-3 text-base font-black text-slate-950">
                    {{ item.label }}
                  </h3>

                  <p class="mt-2 text-xs font-semibold text-slate-500">
                    Target: {{ targetLabel(item.target) }}
                  </p>
                </div>

                <div
                  class="grid grid-cols-2 gap-2 sm:flex sm:flex-row xl:w-[11rem] xl:flex-col"
                >
                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="editLegal(item)"
                  >
                    Edit
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl bg-red-50 px-4 text-xs font-black text-red-700 ring-1 ring-red-100 transition hover:bg-red-100"
                    @click="deleteLegal(item)"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <form v-else class="grid gap-3" @submit.prevent="updateLegal">
                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <input
                      v-model="legalEditForm.label"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="legalEditForm.errors.label"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ legalEditForm.errors.label }}
                    </p>
                  </div>

                  <div>
                    <input
                      v-model="legalEditForm.href"
                      type="text"
                      class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                    />
                    <p
                      v-if="legalEditForm.errors.href"
                      class="mt-2 text-xs font-bold text-red-600"
                    >
                      {{ legalEditForm.errors.href }}
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <select
                    v-model="legalEditForm.target"
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  >
                    <option
                      v-for="target in props.targetOptions"
                      :key="target.value"
                      :value="target.value"
                    >
                      {{ target.label }}
                    </option>
                  </select>

                  <input
                    v-model="legalEditForm.sort_order"
                    type="number"
                    min="0"
                    class="h-10 w-24 rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100"
                  />

                  <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                    <input
                      v-model="legalEditForm.is_active"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 text-red-600"
                    />
                    Aktif
                  </label>

                  <button
                    type="submit"
                    :disabled="legalEditForm.processing"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-700 disabled:opacity-60"
                  >
                    {{ legalEditForm.processing ? "Menyimpan..." : "Simpan" }}
                  </button>

                  <button
                    type="button"
                    class="inline-flex min-h-[40px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:bg-red-50 hover:text-red-700"
                    @click="cancelEditLegal"
                  >
                    Batal
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
@media (max-width: 639px) {
  .custom-footer-mobile-page,
  .custom-footer-mobile-page * {
    box-sizing: border-box;
  }

  .custom-footer-mobile-page {
    width: 100%;
    max-width: 100%;
  }

  .custom-footer-mobile-page :is(section, article, form, div) {
    min-width: 0;
  }

  .custom-footer-mobile-page :is(input, select, textarea, button, a) {
    max-width: 100%;
  }

  .custom-footer-mobile-page p {
    text-align: left !important;
  }

  .custom-footer-mobile-page h1 {
    font-size: 1.65rem !important;
    line-height: 1.08 !important;
    letter-spacing: -0.045em !important;
  }

  .custom-footer-mobile-page h2 {
    font-size: 1.18rem !important;
    line-height: 1.18 !important;
    letter-spacing: -0.035em !important;
  }

  .custom-footer-mobile-page h3 {
    font-size: 1rem !important;
    line-height: 1.25 !important;
  }

  .custom-footer-mobile-page > section:first-of-type {
    border-radius: 1.25rem !important;
    padding: 1rem !important;
    box-shadow: 0 18px 48px rgba(2, 6, 23, 0.2) !important;
  }

  .custom-footer-mobile-page > section:first-of-type .relative.z-10.grid {
    gap: 1rem !important;
  }

  .custom-footer-mobile-page > section:first-of-type p {
    line-height: 1.65 !important;
  }

  .custom-footer-mobile-page > section:first-of-type .rounded-\[1\.65rem\] {
    border-radius: 1.05rem !important;
    padding: 0.85rem !important;
  }

  .custom-footer-mobile-page > section:first-of-type .rounded-\[1\.5rem\] {
    border-radius: 1rem !important;
    padding: 0.85rem !important;
  }

  .custom-footer-mobile-page > section:first-of-type .h-16.w-16 {
    width: 3rem !important;
    height: 3rem !important;
    border-radius: 0.9rem !important;
  }

  .custom-footer-mobile-page > section:first-of-type .grid.grid-cols-3 {
    gap: 0.5rem !important;
  }

  .custom-footer-mobile-page > section:first-of-type .grid.grid-cols-3 > div {
    padding: 0.65rem !important;
    border-radius: 0.95rem !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(2) > div {
    border-radius: 1.1rem !important;
    padding: 0.85rem !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.055) !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(2) h3 {
    font-size: 1.05rem !important;
    line-height: 1.15 !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(2) p {
    line-height: 1.45 !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(2) .h-11.w-11 {
    width: 2.45rem !important;
    height: 2.45rem !important;
    border-radius: 0.9rem !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(3) {
    gap: 1rem !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(3) > div,
  .custom-footer-mobile-page > section:nth-of-type(4) {
    border-radius: 1.25rem !important;
    overflow: visible !important;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06) !important;
  }

  .custom-footer-mobile-page > section:nth-of-type(3) > div,
  .custom-footer-mobile-page > section:nth-of-type(3) > div > div:not(.h-1\.5),
  .custom-footer-mobile-page > section:nth-of-type(4) > div:not(.h-1\.5) {
    padding: 1rem !important;
  }

  .custom-footer-mobile-page form.rounded-\[1\.55rem\],
  .custom-footer-mobile-page .rounded-\[1\.45rem\],
  .custom-footer-mobile-page .rounded-\[1\.55rem\],
  .custom-footer-mobile-page .rounded-\[1\.65rem\],
  .custom-footer-mobile-page .rounded-\[2rem\] {
    border-radius: 1.15rem !important;
  }

  .custom-footer-mobile-page label,
  .custom-footer-mobile-page .text-sm.font-bold,
  .custom-footer-mobile-page .text-sm.font-black {
    font-size: 0.82rem !important;
  }

  .custom-footer-mobile-page input:not([type="checkbox"]),
  .custom-footer-mobile-page select {
    min-height: 46px !important;
    height: 46px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding-inline: 0.875rem !important;
    font-size: 0.86rem !important;
  }

  .custom-footer-mobile-page input[type="file"] {
    height: auto !important;
    min-height: 48px !important;
    padding: 0.75rem !important;
  }

  .custom-footer-mobile-page textarea {
    min-height: 112px !important;
    width: 100% !important;
    border-radius: 1rem !important;
    padding: 0.85rem !important;
    font-size: 0.86rem !important;
    line-height: 1.6 !important;
  }

  .custom-footer-mobile-page button,
  .custom-footer-mobile-page a {
    min-height: 44px;
    border-radius: 1rem !important;
  }

  .custom-footer-mobile-page button[type="submit"],
  .custom-footer-mobile-page form button {
    width: 100%;
    justify-content: center;
  }

  .custom-footer-mobile-page .grid.sm\:grid-cols-2,
  .custom-footer-mobile-page .grid.sm\:grid-cols-3,
  .custom-footer-mobile-page .grid.lg\:grid-cols-2,
  .custom-footer-mobile-page .grid.xl\:grid-cols-\[minmax\(0\,1fr\)_auto\] {
    grid-template-columns: minmax(0, 1fr) !important;
  }

  .custom-footer-mobile-page .grid.grid-cols-2.gap-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }

  .custom-footer-mobile-page .flex.flex-wrap.items-center.gap-3,
  .custom-footer-mobile-page .flex.flex-wrap.items-center.gap-2,
  .custom-footer-mobile-page .flex.flex-wrap.items-center.justify-between.gap-4 {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    align-items: stretch !important;
  }

  .custom-footer-mobile-page .flex.flex-wrap.items-center.gap-3 > *,
  .custom-footer-mobile-page .flex.flex-wrap.items-center.gap-2 > *,
  .custom-footer-mobile-page .flex.flex-wrap.items-center.justify-between.gap-4 > * {
    width: 100% !important;
  }

  .custom-footer-mobile-page .mt-6.flex.flex-wrap.gap-2 {
    display: flex !important;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
    padding-bottom: 0.35rem !important;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
  }

  .custom-footer-mobile-page .mt-6.flex.flex-wrap.gap-2 > button {
    width: auto !important;
    min-width: max-content !important;
    scroll-snap-align: start;
  }

  .custom-footer-mobile-page .flex.flex-wrap.gap-2 > span,
  .custom-footer-mobile-page .flex.flex-wrap.gap-2 > .rounded-full {
    max-width: 100%;
    overflow-wrap: anywhere;
  }

  .custom-footer-mobile-page .break-all,
  .custom-footer-mobile-page .break-words,
  .custom-footer-mobile-page [class*="break-"] {
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .custom-footer-mobile-page .truncate {
    min-width: 0;
  }

  .custom-footer-mobile-page .rounded-full {
    max-width: 100%;
  }

  .custom-footer-mobile-page .h-16.w-16 {
    width: 3.2rem !important;
    height: 3.2rem !important;
  }

  .custom-footer-mobile-page .h-14.w-14 {
    width: 2.9rem !important;
    height: 2.9rem !important;
  }

  .custom-footer-mobile-page .p-5,
  .custom-footer-mobile-page .sm\:p-6,
  .custom-footer-mobile-page .lg\:p-7 {
    padding: 1rem !important;
  }

  .custom-footer-mobile-page .mt-7 {
    margin-top: 1.25rem !important;
  }

  .custom-footer-mobile-page .mt-6 {
    margin-top: 1.25rem !important;
  }

  .custom-footer-mobile-page .mb-6 {
    margin-bottom: 1.25rem !important;
  }

  .custom-footer-mobile-page .gap-5 {
    gap: 1rem !important;
  }

  .custom-footer-mobile-page .gap-6 {
    gap: 1rem !important;
  }

  .custom-footer-mobile-page .ml-auto {
    margin-left: 0 !important;
  }

  .custom-footer-mobile-page .w-28,
  .custom-footer-mobile-page .w-24,
  .custom-footer-mobile-page .lg\:w-64,
  .custom-footer-mobile-page .xl\:w-\[11rem\] {
    width: 100% !important;
  }

  .custom-footer-mobile-page .shadow-\[0_16px_44px_rgba\(15\,23\,42\,0\.06\)\],
  .custom-footer-mobile-page .shadow-\[0_18px_50px_rgba\(15\,23\,42\,0\.06\)\] {
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055) !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .custom-footer-mobile-page *,
  .custom-footer-mobile-page *::before,
  .custom-footer-mobile-page *::after {
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>

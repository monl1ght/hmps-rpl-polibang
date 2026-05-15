<script setup>
import { computed } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  activeApprover: {
    type: Object,
    required: true,
  },
  latestRequest: {
    type: Object,
    default: null,
  },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const form = useForm({
  new_name: "",
  new_position: "Ketua HMPS",
  new_whatsapp_number: "",
  request_reason: "",
});

const hasPendingRequest = computed(() => props.latestRequest?.status === "pending");

const statusMeta = computed(() => {
  const status = props.latestRequest?.status;

  const meta = {
    approved: {
      label: props.latestRequest?.status_label || "Disetujui",
      badge: "border-emerald-200 bg-emerald-50 text-emerald-700",
      soft: "border-emerald-200 bg-emerald-50 text-emerald-700",
      iconBg: "bg-emerald-50 text-emerald-700 ring-emerald-100",
      dot: "bg-emerald-500 shadow-[0_0_0_6px_rgba(16,185,129,0.12)]",
      icon: "M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z",
    },
    pending: {
      label: props.latestRequest?.status_label || "Menunggu Approval",
      badge: "border-amber-200 bg-amber-50 text-amber-700",
      soft: "border-amber-200 bg-amber-50 text-amber-700",
      iconBg: "bg-amber-50 text-amber-700 ring-amber-100",
      dot: "bg-amber-500 shadow-[0_0_0_6px_rgba(245,158,11,0.12)]",
      icon: "M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z",
    },
    rejected: {
      label: props.latestRequest?.status_label || "Ditolak",
      badge: "border-red-200 bg-red-50 text-red-700",
      soft: "border-red-200 bg-red-50 text-red-700",
      iconBg: "bg-red-50 text-red-700 ring-red-100",
      dot: "bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.12)]",
      icon: "M6 18 18 6M6 6l12 12",
    },
    expired: {
      label: props.latestRequest?.status_label || "Kedaluwarsa",
      badge: "border-slate-200 bg-slate-100 text-slate-600",
      soft: "border-slate-200 bg-slate-100 text-slate-600",
      iconBg: "bg-slate-100 text-slate-600 ring-slate-200",
      dot: "bg-slate-400 shadow-[0_0_0_6px_rgba(148,163,184,0.12)]",
      icon:
        "M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z",
    },
    empty: {
      label: "Belum Ada Request",
      badge: "border-slate-200 bg-slate-100 text-slate-600",
      soft: "border-slate-200 bg-slate-50 text-slate-600",
      iconBg: "bg-slate-100 text-slate-600 ring-slate-200",
      dot: "bg-slate-400 shadow-[0_0_0_6px_rgba(148,163,184,0.12)]",
      icon:
        "M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z",
    },
  };

  return meta[status] || meta.empty;
});

const statusClass = computed(() => statusMeta.value.badge);

const stats = computed(() => [
  {
    label: "Approver Aktif",
    value: props.activeApprover?.name || "-",
    helper: props.activeApprover?.position || "Ketua HMPS",
    icon: "user",
  },
  {
    label: "Nomor WhatsApp",
    value: props.activeApprover?.whatsapp_number || "-",
    helper: "Nomor yang sedang digunakan",
    icon: "whatsapp",
  },
  {
    label: "Status Request",
    value: statusMeta.value.label,
    helper: props.latestRequest ? "Request perubahan terbaru" : "Belum ada pengajuan",
    icon: "status",
  },
  {
    label: "Approval Aktif",
    value: hasPendingRequest.value ? "Menunggu" : "Aman",
    helper: hasPendingRequest.value
      ? "Perlu dikirim ke WhatsApp"
      : "Tidak ada request pending",
    icon: "shield",
  },
]);

const activeApproverDetails = computed(() => [
  {
    label: "Nama",
    value: props.activeApprover?.name || "-",
    icon: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0m14 0H5",
  },
  {
    label: "Jabatan",
    value: props.activeApprover?.position || "-",
    icon:
      "M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z",
  },
  {
    label: "WhatsApp",
    value: props.activeApprover?.whatsapp_number || "-",
    icon:
      "M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Z",
    filled: true,
  },
  {
    label: "Nomor Normalisasi",
    value: props.activeApprover?.normalized_whatsapp_number || "-",
    icon: "M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z",
  },
]);

const submit = () => {
  form.post("/admin/security/approver/request-change", {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      form.new_position = "Ketua HMPS";
    },
  });
};

const openWhatsapp = () => {
  if (!props.latestRequest?.whatsapp_url) return;

  window.open(props.latestRequest.whatsapp_url, "_blank", "noopener,noreferrer");
};
</script>

<template>
  <Head title="Pengaturan Approver Keamanan" />

  <div class="space-y-8">
    <!-- Header -->
    <section
      class="relative overflow-hidden rounded-[2rem] border border-slate-800 bg-[linear-gradient(135deg,#0f172a,#111827_52%,#7f1d1d)] p-6 text-white shadow-[0_24px_70px_rgba(2,6,23,0.22)] sm:p-8 lg:p-10"
    >
      <div
        class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-red-500/20 blur-[80px]"
      />
      <div
        class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-white/10 blur-[80px]"
      />

      <div
        class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between"
      >
        <div>
          <div
            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-[0.72rem] font-extrabold uppercase tracking-[0.12em] text-red-100 ring-1 ring-white/10"
          >
            <span
              class="h-2 w-2 rounded-full bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.14)]"
            />
            Admin / Keamanan Sistem
          </div>

          <h1
            class="max-w-4xl text-[2rem] font-black leading-[1.08] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.15rem]"
          >
            Pengaturan Ketua HMPS
            <span class="block text-red-300">Sebagai Approver Keamanan</span>
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300 sm:text-base"
          >
            Nomor approver hanya dapat diganti melalui proses persetujuan Ketua HMPS yang
            sedang aktif agar fitur keamanan tetap terkontrol dan tidak dapat diubah
            sembarangan.
          </p>
        </div>

        <a
          href="/admin/password/change"
          class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-white px-5 text-sm font-black text-slate-950 shadow-[0_14px_34px_rgba(255,255,255,0.12)] transition hover:-translate-y-[2px] hover:bg-red-50 hover:text-red-700"
        >
          <span
            class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(239,68,68,0.14),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
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
              stroke-width="2.35"
              d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4"
            />
          </svg>
          <span class="relative z-10">Ganti Password</span>
        </a>
      </div>
    </section>

    <!-- Flash -->
    <div
      v-if="flashSuccess"
      class="flex items-start gap-3 rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700 shadow-[0_10px_28px_rgba(16,185,129,0.08)]"
    >
      <svg
        class="mt-0.5 h-5 w-5 shrink-0"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2.35"
          d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
        />
      </svg>
      <span>{{ flashSuccess }}</span>
    </div>

    <div
      v-if="flashError"
      class="flex items-start gap-3 rounded-[1.35rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 shadow-[0_10px_28px_rgba(239,68,68,0.08)]"
    >
      <svg
        class="mt-0.5 h-5 w-5 shrink-0"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2.35"
          d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
        />
      </svg>
      <span>{{ flashError }}</span>
    </div>

    <!-- Stats -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="item in stats"
        :key="item.label"
        class="group rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-[0_14px_36px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_22px_52px_rgba(15,23,42,0.10)]"
      >
        <div
          class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100 transition group-hover:scale-105"
        >
          <svg
            v-if="item.icon !== 'whatsapp'"
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              v-if="item.icon === 'user'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0m14 0H5"
            />
            <path
              v-else-if="item.icon === 'status'"
              :d="statusMeta.icon"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 3 5 6v5c0 4.55 2.91 8.42 7 9.9 4.09-1.48 7-5.35 7-9.9V6l-7-3Z"
            />
          </svg>
          <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Z"
            />
          </svg>
        </div>

        <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
          {{ item.label }}
        </p>

        <h3 class="mt-3 truncate text-2xl font-black tracking-[-0.04em] text-slate-950">
          {{ item.value }}
        </h3>

        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
          {{ item.helper }}
        </p>
      </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.42fr_0.58fr]">
      <!-- Active Approver -->
      <div class="space-y-6">
        <div
          class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
        >
          <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

          <div class="p-5 sm:p-6 lg:p-7">
            <div class="mb-6">
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Approver Aktif
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Ketua HMPS Saat Ini
              </h2>

              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Data ini digunakan untuk mengirim approval ganti password dan approval
                perubahan nomor Ketua HMPS.
              </p>
            </div>

            <div
              class="relative overflow-hidden rounded-[1.65rem] border border-slate-800 bg-[linear-gradient(135deg,#0f172a,#111827_60%,#7f1d1d)] p-5 text-white shadow-[0_18px_40px_rgba(15,23,42,0.16)]"
            >
              <div
                class="pointer-events-none absolute -right-16 -top-20 h-44 w-44 rounded-full bg-red-500/20 blur-3xl"
              />

              <div class="relative z-10 flex items-start gap-4">
                <div
                  class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-xl font-black text-white ring-1 ring-white/10"
                >
                  {{
                    String(activeApprover.name || "A")
                      .slice(0, 2)
                      .toUpperCase()
                  }}
                </div>

                <div class="min-w-0">
                  <p class="text-xs font-black uppercase tracking-[0.14em] text-red-200">
                    Approver Aktif
                  </p>
                  <h3
                    class="mt-2 break-words text-2xl font-black tracking-[-0.04em] text-white"
                  >
                    {{ activeApprover.name }}
                  </h3>
                  <p class="mt-1 text-sm font-semibold text-slate-300">
                    {{ activeApprover.position }}
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
              <div
                v-for="item in activeApproverDetails"
                :key="item.label"
                class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4 transition hover:border-red-200 hover:bg-white hover:shadow-[0_12px_30px_rgba(15,23,42,0.06)]"
                :class="item.label === 'Nomor Normalisasi' ? 'sm:col-span-2' : ''"
              >
                <div
                  class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-red-700 shadow-sm ring-1 ring-slate-200"
                >
                  <svg
                    v-if="!item.filled"
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      :d="item.icon"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.25"
                    />
                  </svg>
                  <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path :d="item.icon" />
                  </svg>
                </div>

                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                  {{ item.label }}
                </p>
                <p class="mt-2 break-words text-sm font-black leading-6 text-slate-800">
                  {{ item.value }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Latest Request -->
        <div
          v-if="latestRequest"
          class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
        >
          <div class="h-1.5 bg-[linear-gradient(90deg,#0f172a,#1e293b,#7f1d1d)]" />

          <div class="p-5 sm:p-6">
            <div
              class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
            >
              <div>
                <div
                  class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
                >
                  <span class="h-2 w-2 rounded-full bg-red-500" />
                  Request Terbaru
                </div>
                <h2 class="text-xl font-black tracking-[-0.03em] text-slate-950">
                  Riwayat Pengajuan Terakhir
                </h2>
              </div>

              <span
                class="w-fit rounded-full border px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                :class="statusClass"
              >
                {{ latestRequest.status_label }}
              </span>
            </div>

            <div class="rounded-[1.45rem] border border-slate-200 bg-slate-50 p-5">
              <div class="grid gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                  <p
                    class="text-xs font-black uppercase tracking-[0.12em] text-slate-400"
                  >
                    Nomor Lama
                  </p>
                  <p class="mt-2 break-words text-sm font-black text-slate-800">
                    {{ latestRequest.old_whatsapp_number }}
                  </p>
                </div>

                <div
                  class="hidden h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100 sm:flex"
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
                      d="M13 7l5 5m0 0-5 5m5-5H6"
                    />
                  </svg>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-red-100">
                  <p class="text-xs font-black uppercase tracking-[0.12em] text-red-500">
                    Nomor Baru
                  </p>
                  <p class="mt-2 break-words text-sm font-black text-red-700">
                    {{ latestRequest.new_whatsapp_number }}
                  </p>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-if="latestRequest.expires_at"
                  class="rounded-full bg-slate-100 px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-600 ring-1 ring-slate-200"
                >
                  Exp: {{ latestRequest.expires_at }}
                </span>
                <span
                  class="rounded-full px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em] ring-1"
                  :class="statusMeta.soft"
                >
                  {{ statusMeta.label }}
                </span>
              </div>

              <div
                v-if="latestRequest.request_reason"
                class="mt-4 rounded-2xl border border-slate-200 bg-white p-4"
              >
                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                  Alasan
                </p>
                <p class="mt-2 text-justify text-sm leading-7 text-slate-600">
                  {{ latestRequest.request_reason }}
                </p>
              </div>

              <button
                v-if="hasPendingRequest && latestRequest.whatsapp_url"
                type="button"
                class="mt-5 inline-flex min-h-[50px] w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_14px_30px_rgba(16,185,129,0.20)] transition hover:-translate-y-[2px] hover:bg-emerald-700"
                @click="openWhatsapp"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Z"
                  />
                </svg>
                Kirim Approval ke WhatsApp Ketua Aktif
              </button>
            </div>
          </div>
        </div>

        <div
          v-else
          class="rounded-[1.8rem] border border-dashed border-slate-300 bg-white p-6 text-center shadow-[0_16px_44px_rgba(15,23,42,0.04)]"
        >
          <div
            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-200"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
              />
            </svg>
          </div>
          <h3 class="text-lg font-black text-slate-950">Belum Ada Request Perubahan</h3>
          <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
            Setelah ada pengajuan perubahan nomor approver, status dan tombol pengiriman
            approval akan tampil di sini.
          </p>
        </div>
      </div>

      <!-- Change Form -->
      <div
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div class="mb-6">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
            >
              <span class="h-2 w-2 rounded-full bg-red-500" />
              Pengajuan Perubahan
            </div>

            <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
              Ajukan Nomor Approver Baru
            </h2>

            <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
              Form ini tidak langsung mengganti nomor. Sistem akan meminta persetujuan
              Ketua HMPS yang sedang aktif terlebih dahulu.
            </p>
          </div>

          <div
            v-if="hasPendingRequest"
            class="mb-5 rounded-[1.35rem] border border-amber-200 bg-amber-50 px-4 py-4 text-sm font-bold leading-7 text-amber-700"
          >
            Masih ada request perubahan yang berstatus pending. Pastikan approval
            sebelumnya diproses terlebih dahulu agar alur keamanan tetap jelas.
          </div>

          <form class="grid gap-5" @submit.prevent="submit">
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Nama Ketua HMPS Baru
              </label>
              <input
                v-model="form.new_name"
                type="text"
                placeholder="Contoh: Maritza Lintang Ardana"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p v-if="form.errors.new_name" class="mt-2 text-xs font-bold text-red-600">
                {{ form.errors.new_name }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Jabatan
              </label>
              <input
                v-model="form.new_position"
                type="text"
                placeholder="Ketua HMPS"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="form.errors.new_position"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ form.errors.new_position }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Nomor WhatsApp Baru
              </label>
              <input
                v-model="form.new_whatsapp_number"
                type="text"
                placeholder="Contoh: 0857xxxxxxxx atau 62857xxxxxxxx"
                class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="form.errors.new_whatsapp_number"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ form.errors.new_whatsapp_number }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Alasan Perubahan
              </label>
              <textarea
                v-model="form.request_reason"
                rows="5"
                placeholder="Contoh: Ketua HMPS sudah berganti periode sehingga nomor approver perlu diperbarui."
                class="w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm font-medium leading-7 text-slate-900 outline-none transition duration-300 placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
              />
              <p
                v-if="form.errors.request_reason"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ form.errors.request_reason }}
              </p>
            </div>

            <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <div
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100"
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
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                  </svg>
                </div>
                <p class="text-justify text-sm leading-7 text-slate-500">
                  Setelah pengajuan dikirim, sistem akan membuat link approval yang perlu
                  dikirim ke WhatsApp Ketua HMPS aktif untuk disetujui atau ditolak.
                </p>
              </div>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="group relative inline-flex min-h-[52px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-6 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[2px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            >
              <span
                class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
              />
              <svg
                class="relative z-10 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.35"
                  d="M12 5v14m7-7H5"
                />
              </svg>
              <span class="relative z-10">{{
                form.processing ? "Mengajukan..." : "Ajukan Perubahan Nomor"
              }}</span>
            </button>
          </form>
        </div>
      </div>
    </section>
  </div>
</template>

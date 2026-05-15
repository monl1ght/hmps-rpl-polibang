<script setup>
import { computed, ref } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/admin/layouts/AdminLayout.vue";

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  approvalPhone: {
    type: String,
    default: "087879175646",
  },
  latestRequest: {
    type: Object,
    default: null,
  },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const canChangePassword = computed(() => {
  return props.latestRequest?.status === "approved";
});

const hasPendingRequest = computed(() => {
  return props.latestRequest?.status === "pending";
});

const requestForm = useForm({});

const passwordForm = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

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
    completed: {
      label: props.latestRequest?.status_label || "Selesai",
      badge: "border-blue-200 bg-blue-50 text-blue-700",
      soft: "border-blue-200 bg-blue-50 text-blue-700",
      iconBg: "bg-blue-50 text-blue-700 ring-blue-100",
      dot: "bg-blue-500 shadow-[0_0_0_6px_rgba(59,130,246,0.12)]",
      icon: "M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z",
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

const approvalFlowSteps = computed(() => [
  {
    title: "Buat Request",
    description: "Admin membuat permintaan approval penggantian password.",
    active: !props.latestRequest || hasPendingRequest.value || canChangePassword.value,
  },
  {
    title: "Kirim ke WhatsApp",
    description: "Link approval dikirim ke Ketua HMPS yang sedang aktif.",
    active: hasPendingRequest.value,
  },
  {
    title: "Ganti Password",
    description: "Form password aktif setelah request disetujui.",
    active: canChangePassword.value,
  },
]);

const stats = computed(() => [
  {
    label: "Nomor Approval",
    value: props.approvalPhone || "-",
    helper: "WhatsApp Ketua HMPS aktif",
    icon: "whatsapp",
  },
  {
    label: "Status Request",
    value: statusMeta.value.label,
    helper: props.latestRequest ? "Request terbaru" : "Belum ada pengajuan",
    icon: "status",
  },
  {
    label: "Akses Form",
    value: canChangePassword.value ? "Terbuka" : "Terkunci",
    helper: canChangePassword.value ? "Password dapat diganti" : "Menunggu approval",
    icon: "lock",
  },
  {
    label: "Approval Pending",
    value: hasPendingRequest.value ? "Ada" : "Tidak Ada",
    helper: hasPendingRequest.value ? "Perlu dikirim ke WhatsApp" : "Alur aman",
    icon: "shield",
  },
]);

const requestDetails = computed(() => [
  {
    label: "Status",
    value: statusMeta.value.label,
    icon: statusMeta.value.icon,
  },
  {
    label: "Dibuat Pada",
    value: props.latestRequest?.requested_at || "-",
    icon:
      "M8 7V3m8 4V3M4 11h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z",
  },
  {
    label: "Kedaluwarsa",
    value: props.latestRequest?.expires_at || "-",
    icon: "M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z",
  },
]);

const passwordRequirements = [
  "Gunakan minimal 10 karakter.",
  "Kombinasikan huruf besar, huruf kecil, dan angka.",
  "Jangan gunakan password yang sama dengan akun lain.",
];

const requestApproval = () => {
  requestForm.post("/admin/password/request", {
    preserveScroll: true,
  });
};

const openWhatsapp = () => {
  if (!props.latestRequest?.whatsapp_url) return;

  window.open(props.latestRequest.whatsapp_url, "_blank", "noopener,noreferrer");
};

const updatePassword = () => {
  if (!props.latestRequest?.id) return;

  passwordForm.put(`/admin/password/${props.latestRequest.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset();
    },
  });
};
</script>

<template>
  <Head title="Ganti Password Admin" />

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
            Admin / Keamanan Akun
          </div>

          <h1
            class="max-w-4xl text-[2rem] font-black leading-[1.08] tracking-[-0.045em] sm:text-[2.7rem] lg:text-[3.15rem]"
          >
            Ganti Password Admin
            <span class="block text-red-300">Dengan Approval Ketua HMPS</span>
          </h1>

          <p
            class="mt-5 max-w-2xl text-justify text-sm leading-8 text-slate-300 sm:text-base"
          >
            Penggantian password admin hanya dapat dilakukan setelah mendapat persetujuan
            Ketua HMPS melalui WhatsApp agar keamanan panel admin tetap terjaga.
          </p>
        </div>

        <a
          href="/admin/dashboard"
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
              d="M10 19l-7-7m0 0 7-7m-7 7h18"
            />
          </svg>
          <span class="relative z-10">Kembali Dashboard</span>
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
              v-if="item.icon === 'status'"
              :d="statusMeta.icon"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
            />
            <path
              v-else-if="item.icon === 'lock'"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.35"
              d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4"
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
      <!-- Approval Panel -->
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
                Persetujuan Ketua HMPS
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Request Approval Password
              </h2>

              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Sebelum mengganti password, buat permintaan approval dan kirimkan link
                persetujuan ke Ketua HMPS aktif melalui WhatsApp.
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
                  class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-red-200 ring-1 ring-white/10"
                >
                  <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                    <path
                      d="M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Z"
                    />
                  </svg>
                </div>

                <div class="min-w-0">
                  <p class="text-xs font-black uppercase tracking-[0.14em] text-red-200">
                    WhatsApp Ketua HMPS
                  </p>
                  <h3
                    class="mt-2 break-words text-2xl font-black tracking-[-0.04em] text-white"
                  >
                    {{ approvalPhone }}
                  </h3>
                  <p class="mt-2 text-sm leading-7 text-slate-300">
                    Sistem mengirim link approval, bukan password baru. Password tetap
                    diisi langsung oleh admin pada form aman.
                  </p>
                </div>
              </div>
            </div>

            <!-- Flow -->
            <div class="mt-5 rounded-[1.45rem] border border-slate-200 bg-slate-50 p-4">
              <p
                class="mb-4 text-xs font-black uppercase tracking-[0.12em] text-slate-400"
              >
                Alur Keamanan
              </p>

              <div class="grid gap-3">
                <div
                  v-for="(step, index) in approvalFlowSteps"
                  :key="step.title"
                  class="flex gap-3 rounded-2xl border bg-white p-4 transition"
                  :class="
                    step.active
                      ? 'border-red-100 shadow-sm'
                      : 'border-slate-200 opacity-70'
                  "
                >
                  <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black ring-1"
                    :class="
                      step.active
                        ? 'bg-red-50 text-red-700 ring-red-100'
                        : 'bg-slate-100 text-slate-500 ring-slate-200'
                    "
                  >
                    {{ index + 1 }}
                  </span>
                  <div>
                    <h3 class="text-sm font-black text-slate-950">{{ step.title }}</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                      {{ step.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
              <button
                type="button"
                :disabled="requestForm.processing"
                class="group relative inline-flex min-h-[50px] flex-1 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:-translate-y-[2px] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                @click="requestApproval"
              >
                <span
                  class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.16),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
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
                  requestForm.processing ? "Memproses..." : "Buat Request Approval"
                }}</span>
              </button>

              <button
                v-if="hasPendingRequest && latestRequest?.whatsapp_url"
                type="button"
                class="inline-flex min-h-[50px] flex-1 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_14px_30px_rgba(16,185,129,0.20)] transition hover:-translate-y-[2px] hover:bg-emerald-700"
                @click="openWhatsapp"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Z"
                  />
                </svg>
                Kirim ke WhatsApp Ketua
              </button>
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
                  Status Pengajuan Password
                </h2>
              </div>

              <span
                class="w-fit rounded-full border px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
                :class="statusClass"
              >
                {{ latestRequest.status_label }}
              </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div
                v-for="item in requestDetails"
                :key="item.label"
                class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4"
              >
                <div
                  class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-red-700 shadow-sm ring-1 ring-slate-200"
                >
                  <svg
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
                </div>
                <p
                  class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400"
                >
                  {{ item.label }}
                </p>
                <p class="mt-2 break-words text-sm font-black leading-6 text-slate-800">
                  {{ item.value }}
                </p>
              </div>
            </div>

            <div
              v-if="latestRequest.approval_note"
              class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
            >
              <p class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700">
                Catatan Approval
              </p>
              <p class="mt-2 text-sm leading-7 text-emerald-700">
                {{ latestRequest.approval_note }}
              </p>
            </div>

            <div
              v-if="latestRequest.reject_reason"
              class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4"
            >
              <p class="text-xs font-black uppercase tracking-[0.12em] text-red-700">
                Alasan Penolakan
              </p>
              <p class="mt-2 text-sm leading-7 text-red-700">
                {{ latestRequest.reject_reason }}
              </p>
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
          <h3 class="text-lg font-black text-slate-950">Belum Ada Request Password</h3>
          <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
            Buat request approval terlebih dahulu agar link persetujuan dapat dikirim ke
            WhatsApp Ketua HMPS.
          </p>
        </div>
      </div>

      <!-- Password Form -->
      <div
        class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_16px_44px_rgba(15,23,42,0.06)]"
      >
        <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

        <div class="p-5 sm:p-6 lg:p-7">
          <div
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
          >
            <div>
              <div
                class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-3 py-1.5 text-[0.68rem] font-extrabold uppercase tracking-[0.1em] text-red-700"
              >
                <span class="h-2 w-2 rounded-full bg-red-500" />
                Form Keamanan
              </div>

              <h2 class="text-2xl font-black tracking-[-0.04em] text-slate-950">
                Form Ganti Password
              </h2>

              <p class="mt-2 text-justify text-sm leading-7 text-slate-500">
                Form ini hanya aktif jika permintaan sudah disetujui oleh Ketua HMPS.
              </p>
            </div>

            <span
              class="w-fit rounded-full border px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em]"
              :class="
                canChangePassword
                  ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                  : 'border-amber-200 bg-amber-50 text-amber-700'
              "
            >
              {{ canChangePassword ? "Form Aktif" : "Form Terkunci" }}
            </span>
          </div>

          <div
            v-if="!canChangePassword"
            class="mb-6 rounded-[1.45rem] border border-amber-200 bg-amber-50 p-5"
          >
            <div class="flex items-start gap-3">
              <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-amber-700 ring-1 ring-amber-100"
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
                    d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                  />
                </svg>
              </div>
              <div>
                <h3 class="text-base font-black text-amber-800">
                  Belum Bisa Mengganti Password
                </h3>
                <p class="mt-2 text-justify text-sm leading-7 text-amber-700">
                  Buat request approval terlebih dahulu, lalu minta Ketua HMPS untuk
                  menyetujui melalui link WhatsApp.
                </p>
              </div>
            </div>
          </div>

          <form
            class="grid gap-5 transition duration-300"
            :class="
              !canChangePassword ? 'pointer-events-none opacity-50 grayscale-[0.15]' : ''
            "
            @submit.prevent="updatePassword"
          >
            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Password Lama
              </label>
              <div class="relative">
                <input
                  v-model="passwordForm.current_password"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  placeholder="Masukkan password lama"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 pr-12 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-white hover:text-red-700"
                  @click="showCurrentPassword = !showCurrentPassword"
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
                      stroke-width="2.2"
                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
                    />
                  </svg>
                </button>
              </div>
              <p
                v-if="passwordForm.errors.current_password"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Password Baru
              </label>
              <div class="relative">
                <input
                  v-model="passwordForm.password"
                  :type="showNewPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  placeholder="Minimal 10 karakter, huruf besar, huruf kecil, dan angka"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 pr-12 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-white hover:text-red-700"
                  @click="showNewPassword = !showNewPassword"
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
                      stroke-width="2.2"
                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
                    />
                  </svg>
                </button>
              </div>
              <p
                v-if="passwordForm.errors.password"
                class="mt-2 text-xs font-bold text-red-600"
              >
                {{ passwordForm.errors.password }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-extrabold text-slate-800">
                Konfirmasi Password Baru
              </label>
              <div class="relative">
                <input
                  v-model="passwordForm.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  placeholder="Ulangi password baru"
                  class="h-[3.25rem] w-full rounded-2xl border border-slate-200 bg-slate-50/70 px-4 pr-12 text-sm font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-white hover:text-red-700"
                  @click="showConfirmPassword = !showConfirmPassword"
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
                      stroke-width="2.2"
                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
              <p
                class="mb-3 text-xs font-black uppercase tracking-[0.12em] text-slate-400"
              >
                Saran Password Aman
              </p>
              <ul class="grid gap-2">
                <li
                  v-for="item in passwordRequirements"
                  :key="item"
                  class="flex items-start gap-2 text-sm leading-6 text-slate-600"
                >
                  <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500" />
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>

            <button
              type="submit"
              :disabled="passwordForm.processing || !canChangePassword"
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
                  d="M9 12l2 2 4-4"
                />
              </svg>
              <span class="relative z-10">{{
                passwordForm.processing ? "Menyimpan..." : "Simpan Password Baru"
              }}</span>
            </button>
          </form>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

defineOptions({
  layout: null,
});

const props = defineProps({
  changeRequest: {
    type: Object,
    required: true,
  },
});

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const isPending = computed(() => props.changeRequest.status === "pending");

const approveForm = useForm({
  approver_name: "Ketua HMPS",
  approval_note: "",
});

const rejectForm = useForm({
  approver_name: "Ketua HMPS",
  reject_reason: "",
});

const statusMeta = computed(() => {
  const status = props.changeRequest.status;

  const meta = {
    approved: {
      label: props.changeRequest.status_label || "Disetujui",
      badge: "border-emerald-400/25 bg-emerald-400/12 text-emerald-200",
      panel: "border-emerald-400/25 bg-emerald-500/10",
      dot: "bg-emerald-400 shadow-[0_0_0_6px_rgba(52,211,153,0.14)]",
      icon: "M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z",
    },
    pending: {
      label: props.changeRequest.status_label || "Menunggu Persetujuan",
      badge: "border-amber-400/25 bg-amber-400/12 text-amber-200",
      panel: "border-amber-400/25 bg-amber-500/10",
      dot: "bg-amber-300 shadow-[0_0_0_6px_rgba(252,211,77,0.14)]",
      icon: "M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z",
    },
    rejected: {
      label: props.changeRequest.status_label || "Ditolak",
      badge: "border-red-400/25 bg-red-400/12 text-red-200",
      panel: "border-red-400/25 bg-red-500/10",
      dot: "bg-red-400 shadow-[0_0_0_6px_rgba(248,113,113,0.14)]",
      icon: "M6 18 18 6M6 6l12 12",
    },
    completed: {
      label: props.changeRequest.status_label || "Selesai",
      badge: "border-blue-400/25 bg-blue-400/12 text-blue-200",
      panel: "border-blue-400/25 bg-blue-500/10",
      dot: "bg-blue-400 shadow-[0_0_0_6px_rgba(96,165,250,0.14)]",
      icon: "M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z",
    },
    expired: {
      label: props.changeRequest.status_label || "Kedaluwarsa",
      badge: "border-slate-400/25 bg-slate-400/12 text-slate-200",
      panel: "border-slate-400/25 bg-slate-500/10",
      dot: "bg-slate-400 shadow-[0_0_0_6px_rgba(148,163,184,0.14)]",
      icon:
        "M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z",
    },
  };

  return meta[status] || meta.expired;
});

const statusClass = computed(() => statusMeta.value.badge);

const requestSummary = computed(() => [
  {
    label: "Status Request",
    value: statusMeta.value.label,
    icon: statusMeta.value.icon,
  },
  {
    label: "Waktu Request",
    value: props.changeRequest.requested_at || "-",
    icon:
      "M8 7V3m8 4V3M4 11h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z",
  },
  {
    label: "Batas Waktu",
    value: props.changeRequest.expires_at || "-",
    icon: "M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z",
  },
]);

const adminInfo = computed(() => [
  {
    label: "Nama Admin",
    value: props.changeRequest.requested_by?.name || "-",
    icon: "M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0m14 0H5",
  },
  {
    label: "Username",
    value: props.changeRequest.requested_by?.username || "-",
    icon:
      "M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0",
  },
  {
    label: "Token Approval",
    value: props.changeRequest.token
      ? `${String(props.changeRequest.token).slice(0, 12)}...`
      : "-",
    icon:
      "M15.75 5.25a3 3 0 0 1 0 4.243l-6.258 6.258a3 3 0 0 1-1.42.789l-3.321.83.83-3.322a3 3 0 0 1 .789-1.42l6.258-6.258a3 3 0 0 1 4.243 0Z",
  },
]);

const approvalSteps = computed(() => [
  {
    title: "Request Dibuat",
    description: "Admin mengajukan permintaan untuk mengganti password.",
    active: true,
  },
  {
    title: "Menunggu Ketua HMPS",
    description: "Ketua HMPS meninjau dan menentukan keputusan approval.",
    active: props.changeRequest.status === "pending",
  },
  {
    title: "Form Password Dibuka",
    description: "Jika disetujui, admin dapat mengganti password dari dashboard.",
    active:
      props.changeRequest.status === "approved" ||
      props.changeRequest.status === "completed",
  },
]);

const approve = () => {
  approveForm.post(`/admin/password-approval/${props.changeRequest.token}/approve`, {
    preserveScroll: true,
  });
};

const reject = () => {
  rejectForm.post(`/admin/password-approval/${props.changeRequest.token}/reject`, {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Approval Ganti Password" />

  <main
    class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.18),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(220,38,38,0.13),transparent_32%),linear-gradient(135deg,#020617_0%,#0f172a_48%,#111827_100%)] px-4 py-8 text-white sm:px-6 lg:px-8 lg:py-10"
  >
    <div
      class="pointer-events-none absolute -left-32 top-20 h-80 w-80 rounded-full bg-red-500/10 blur-[90px]"
    />
    <div
      class="pointer-events-none absolute -right-32 bottom-10 h-96 w-96 rounded-full bg-red-700/12 blur-[100px]"
    />
    <div
      class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.035)_1px,transparent_1px)] bg-[size:44px_44px] opacity-[0.18]"
    />

    <section
      class="relative z-10 mx-auto flex min-h-[calc(100vh-5rem)] max-w-6xl items-center justify-center"
    >
      <div
        class="w-full overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.075] shadow-[0_32px_110px_rgba(0,0,0,0.36)] backdrop-blur-2xl"
      >
        <!-- Header -->
        <div
          class="relative overflow-hidden border-b border-white/10 bg-white/[0.045] px-5 py-6 sm:px-7 lg:px-8 lg:py-7"
        >
          <div
            class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-red-500/18 blur-[70px]"
          />

          <div
            class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
          >
            <div class="max-w-3xl">
              <div
                class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-300/15 bg-red-500/10 px-3.5 py-2 text-[0.7rem] font-black uppercase tracking-[0.14em] text-red-100"
              >
                <span
                  class="h-2 w-2 rounded-full bg-red-300 shadow-[0_0_0_6px_rgba(248,113,113,0.14)]"
                />
                Approval Ketua HMPS
              </div>

              <h1
                class="text-[2rem] font-black leading-[1.08] tracking-[-0.045em] text-white sm:text-[2.55rem] lg:text-[3rem]"
              >
                Persetujuan Ganti
                <span class="block text-red-300">Password Admin</span>
              </h1>

              <p
                class="mt-4 max-w-2xl text-justify text-sm leading-8 text-slate-300 sm:text-base"
              >
                Tinjau permintaan penggantian password admin website HMPS RPL. Keputusan
                approval akan menentukan apakah admin dapat melanjutkan proses ganti
                password di dashboard.
              </p>
            </div>

            <div class="rounded-[1.4rem] border px-4 py-3" :class="statusClass">
              <div class="flex items-center gap-3">
                <span class="h-2.5 w-2.5 rounded-full" :class="statusMeta.dot" />
                <div>
                  <p
                    class="text-[0.68rem] font-black uppercase tracking-[0.12em] opacity-75"
                  >
                    Status
                  </p>
                  <p class="mt-0.5 text-sm font-black">
                    {{ statusMeta.label }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6 px-5 py-6 sm:px-7 lg:px-8 lg:py-8">
          <!-- Flash -->
          <div
            v-if="flashSuccess"
            class="flex items-start gap-3 rounded-2xl border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm font-bold text-emerald-100"
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
                stroke-width="2.3"
                d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
              />
            </svg>
            <span>{{ flashSuccess }}</span>
          </div>

          <div
            v-if="flashError"
            class="flex items-start gap-3 rounded-2xl border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-100"
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
                stroke-width="2.3"
                d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
              />
            </svg>
            <span>{{ flashError }}</span>
          </div>

          <!-- Summary -->
          <div class="grid gap-4 md:grid-cols-3">
            <div
              v-for="item in requestSummary"
              :key="item.label"
              class="rounded-[1.45rem] border border-white/10 bg-white/[0.045] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]"
            >
              <div
                class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-white/[0.08] text-red-200 ring-1 ring-white/10"
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
                class="text-[0.68rem] font-black uppercase tracking-[0.12em] text-slate-400"
              >
                {{ item.label }}
              </p>
              <p class="mt-2 break-words text-sm font-black leading-6 text-white">
                {{ item.value }}
              </p>
            </div>
          </div>

          <!-- Request Detail -->
          <div
            class="overflow-hidden rounded-[1.8rem] border border-white/10 bg-slate-950/25 shadow-[0_18px_55px_rgba(0,0,0,0.18)]"
          >
            <div class="border-b border-white/10 bg-white/[0.035] px-5 py-4 sm:px-6">
              <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <p
                    class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-red-200"
                  >
                    Detail Request
                  </p>
                  <h2 class="mt-1 text-xl font-black tracking-[-0.03em] text-white">
                    Data Admin yang Mengajukan
                  </h2>
                </div>

                <span
                  class="w-fit rounded-full border border-white/10 bg-white/[0.06] px-3 py-1.5 text-[0.68rem] font-black uppercase tracking-[0.08em] text-slate-300"
                >
                  Exp: {{ changeRequest.expires_at || "-" }}
                </span>
              </div>
            </div>

            <div class="grid gap-4 p-5 sm:p-6 lg:grid-cols-[0.95fr_1.05fr]">
              <!-- Admin Information -->
              <div class="rounded-[1.5rem] border border-white/10 bg-white/[0.045] p-5">
                <div class="mb-5 flex items-center gap-3">
                  <span
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-400/12 text-red-200 ring-1 ring-red-300/20"
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
                        d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0m14 0H5"
                      />
                    </svg>
                  </span>
                  <div>
                    <p
                      class="text-xs font-black uppercase tracking-[0.12em] text-slate-400"
                    >
                      Identitas Admin
                    </p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-500">
                      Akun yang meminta izin ganti password
                    </p>
                  </div>
                </div>

                <div class="space-y-3">
                  <div
                    v-for="item in adminInfo"
                    :key="item.label"
                    class="rounded-2xl border border-white/10 bg-slate-950/20 px-4 py-3"
                  >
                    <div class="flex items-start gap-3">
                      <svg
                        class="mt-0.5 h-4 w-4 shrink-0 text-red-200"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          :d="item.icon"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2.2"
                        />
                      </svg>
                      <div class="min-w-0 flex-1">
                        <p
                          class="text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-500"
                        >
                          {{ item.label }}
                        </p>
                        <p
                          class="mt-1 break-words text-sm font-bold leading-6 text-slate-200"
                        >
                          {{ item.value }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Flow -->
              <div class="rounded-[1.5rem] border border-white/10 bg-white/[0.045] p-5">
                <div class="mb-5 flex items-center gap-3">
                  <span
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/[0.08] text-red-200 ring-1 ring-white/10"
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
                        d="M9 12h6m-6 4h6M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"
                      />
                    </svg>
                  </span>
                  <div>
                    <p
                      class="text-xs font-black uppercase tracking-[0.12em] text-slate-400"
                    >
                      Alur Approval
                    </p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-500">
                      Urutan proses keamanan password
                    </p>
                  </div>
                </div>

                <div class="space-y-3">
                  <div
                    v-for="(step, index) in approvalSteps"
                    :key="step.title"
                    class="rounded-2xl border px-4 py-3 transition"
                    :class="
                      step.active
                        ? 'border-red-300/20 bg-red-500/10'
                        : 'border-white/10 bg-slate-950/20 opacity-70'
                    "
                  >
                    <div class="flex gap-3">
                      <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black ring-1"
                        :class="
                          step.active
                            ? 'bg-red-400/12 text-red-200 ring-red-300/20'
                            : 'bg-white/[0.06] text-slate-400 ring-white/10'
                        "
                      >
                        {{ index + 1 }}
                      </span>
                      <div>
                        <h3 class="text-sm font-black text-white">{{ step.title }}</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-400">
                          {{ step.description }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  class="mt-4 rounded-2xl border border-amber-300/15 bg-amber-500/10 px-4 py-3"
                >
                  <p class="text-justify text-sm leading-7 text-amber-100/85">
                    Password baru tidak ditampilkan di halaman ini. Halaman ini hanya
                    memberi izin agar admin dapat mengganti password melalui dashboard.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Already processed -->
          <div
            v-if="!isPending"
            class="rounded-[1.8rem] border p-5 shadow-[0_18px_55px_rgba(0,0,0,0.18)] sm:p-6"
            :class="statusMeta.panel"
          >
            <div class="flex items-start gap-4">
              <span
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/[0.08] text-white ring-1 ring-white/10"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    :d="statusMeta.icon"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.35"
                  />
                </svg>
              </span>

              <div class="min-w-0 flex-1">
                <h2 class="text-xl font-black tracking-[-0.03em] text-white">
                  Permintaan Sudah Diproses
                </h2>
                <p class="mt-2 text-sm leading-7 text-slate-300">
                  Status saat ini:
                  <span class="font-black text-white">{{
                    changeRequest.status_label
                  }}</span>
                </p>

                <p
                  v-if="changeRequest.approval_note"
                  class="mt-3 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm leading-7 text-emerald-100"
                >
                  Catatan approval: {{ changeRequest.approval_note }}
                </p>

                <p
                  v-if="changeRequest.reject_reason"
                  class="mt-3 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm leading-7 text-red-100"
                >
                  Alasan penolakan: {{ changeRequest.reject_reason }}
                </p>
              </div>
            </div>
          </div>

          <!-- Decision Forms -->
          <div v-else class="grid gap-5 lg:grid-cols-2">
            <form
              class="group relative overflow-hidden rounded-[1.8rem] border border-emerald-400/20 bg-emerald-500/10 p-5 shadow-[0_18px_55px_rgba(0,0,0,0.18)] transition hover:-translate-y-[2px] hover:border-emerald-300/30 sm:p-6"
              @submit.prevent="approve"
            >
              <div
                class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-emerald-400/15 blur-3xl"
              />

              <div class="relative z-10">
                <div
                  class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-400/14 text-emerald-100 ring-1 ring-emerald-300/20"
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
                      stroke-width="2.35"
                      d="M9 12l2 2 4-4m5 2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                    />
                  </svg>
                </div>

                <h2 class="text-xl font-black tracking-[-0.03em] text-emerald-50">
                  Setujui Request
                </h2>
                <p class="mt-2 text-justify text-sm leading-7 text-emerald-100/78">
                  Jika disetujui, admin dapat melanjutkan proses ganti password melalui
                  dashboard admin.
                </p>

                <label
                  class="mt-5 block text-xs font-black uppercase tracking-[0.12em] text-emerald-100/70"
                >
                  Nama Penyetuju
                </label>
                <input
                  v-model="approveForm.approver_name"
                  type="text"
                  placeholder="Nama penyetuju"
                  class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-slate-950/35 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-400/10"
                />

                <label
                  class="mt-4 block text-xs font-black uppercase tracking-[0.12em] text-emerald-100/70"
                >
                  Catatan Approval
                </label>
                <textarea
                  v-model="approveForm.approval_note"
                  rows="4"
                  placeholder="Catatan opsional"
                  class="mt-2 w-full rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm font-semibold leading-7 text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-400/10"
                />

                <button
                  type="submit"
                  :disabled="approveForm.processing || rejectForm.processing"
                  class="mt-5 inline-flex min-h-[50px] w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 text-sm font-black text-white shadow-[0_14px_30px_rgba(16,185,129,0.22)] transition hover:-translate-y-[1px] hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
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
                      d="M9 12l2 2 4-4"
                    />
                  </svg>
                  {{ approveForm.processing ? "Memproses..." : "Setujui Request" }}
                </button>
              </div>
            </form>

            <form
              class="group relative overflow-hidden rounded-[1.8rem] border border-red-400/20 bg-red-500/10 p-5 shadow-[0_18px_55px_rgba(0,0,0,0.18)] transition hover:-translate-y-[2px] hover:border-red-300/30 sm:p-6"
              @submit.prevent="reject"
            >
              <div
                class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-red-400/15 blur-3xl"
              />

              <div class="relative z-10">
                <div
                  class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-400/14 text-red-100 ring-1 ring-red-300/20"
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
                      stroke-width="2.35"
                      d="M6 18 18 6M6 6l12 12"
                    />
                  </svg>
                </div>

                <h2 class="text-xl font-black tracking-[-0.03em] text-red-50">
                  Tolak Request
                </h2>
                <p class="mt-2 text-justify text-sm leading-7 text-red-100/78">
                  Jika ditolak, admin tidak dapat melanjutkan proses ganti password sampai
                  membuat request baru.
                </p>

                <label
                  class="mt-5 block text-xs font-black uppercase tracking-[0.12em] text-red-100/70"
                >
                  Nama Penolak
                </label>
                <input
                  v-model="rejectForm.approver_name"
                  type="text"
                  placeholder="Nama penolak"
                  class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-slate-950/35 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-red-300 focus:ring-4 focus:ring-red-400/10"
                />

                <label
                  class="mt-4 block text-xs font-black uppercase tracking-[0.12em] text-red-100/70"
                >
                  Alasan Penolakan
                </label>
                <textarea
                  v-model="rejectForm.reject_reason"
                  rows="4"
                  placeholder="Alasan penolakan opsional"
                  class="mt-2 w-full rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm font-semibold leading-7 text-white outline-none transition placeholder:text-slate-500 focus:border-red-300 focus:ring-4 focus:ring-red-400/10"
                />

                <button
                  type="submit"
                  :disabled="rejectForm.processing || approveForm.processing"
                  class="mt-5 inline-flex min-h-[50px] w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition hover:-translate-y-[1px] hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
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
                      d="M6 18 18 6M6 6l12 12"
                    />
                  </svg>
                  {{ rejectForm.processing ? "Memproses..." : "Tolak Request" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

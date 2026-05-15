<script setup>
import { computed, ref } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

defineOptions({
  layout: null,
});

const page = usePage();

const showPassword = ref(false);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const currentYear = computed(() => new Date().getFullYear());

const logoPath = "/Images/logo/hmps-rpl-logo.png";
const logoAlt = "Logo HMPS RPL";

const form = useForm({
  username: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post("/admin/login", {
    preserveScroll: true,
    onFinish: () => {
      form.reset("password");
    },
  });
};
</script>

<template>
  <Head title="Login Admin" />

  <main
    class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,rgba(239,68,68,0.10),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(15,23,42,0.08),transparent_32%),linear-gradient(135deg,#ffffff_0%,#f8fafc_45%,#eef2f7_100%)] text-slate-950"
  >
    <div
      class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(15,23,42,0.045)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.045)_1px,transparent_1px)] bg-[size:44px_44px] opacity-60"
      aria-hidden="true"
    />

    <div
      class="pointer-events-none fixed -left-24 top-20 h-80 w-80 rounded-full bg-red-400/18 blur-[95px]"
      aria-hidden="true"
    />

    <div
      class="pointer-events-none fixed -bottom-28 right-0 h-96 w-96 rounded-full bg-slate-400/16 blur-[110px]"
      aria-hidden="true"
    />

    <section
      class="relative z-10 mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
    >
      <div class="grid w-full items-center gap-8 lg:grid-cols-[1.02fr_0.98fr] xl:gap-12">
        <!-- Left Branding -->
        <aside class="hidden lg:block">
          <div
            class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-red-700 shadow-[0_12px_35px_rgba(239,68,68,0.08)]"
          >
            <span
              class="h-2 w-2 rounded-full bg-red-500 shadow-[0_0_0_6px_rgba(239,68,68,0.13)]"
            />
            Admin Panel HMPS RPL
          </div>

          <h1
            class="mt-6 max-w-2xl text-[3.15rem] font-black leading-[1.03] tracking-[-0.065em] text-slate-950 xl:text-[4.05rem]"
          >
            Kelola website
            <span
              class="block bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] bg-clip-text text-transparent"
            >
              secara profesional.
            </span>
          </h1>

          <p class="mt-6 max-w-2xl text-justify text-base leading-8 text-slate-600">
            Masuk ke dashboard admin untuk mengatur konten beranda, navbar, footer,
            dokumentasi, kepengurusan, layanan jasa, program kerja, konsultasi, dan
            halaman lainnya secara terpusat.
          </p>

          <div
            class="mt-8 max-w-2xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.08)]"
          >
            <div class="h-1.5 bg-[linear-gradient(90deg,#ef4444,#dc2626,#7f1d1d)]" />

            <div class="relative p-6">
              <div
                class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-red-100 blur-3xl"
              />

              <div class="relative z-10 flex items-start gap-4">
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-700 ring-1 ring-red-100"
                >
                  <svg
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.3"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z"
                    />
                  </svg>
                </div>

                <div>
                  <p class="text-sm font-black uppercase tracking-[0.13em] text-red-700">
                    Secure Admin Access
                  </p>
                  <h2 class="mt-2 text-2xl font-black tracking-[-0.04em] text-slate-950">
                    Akses pengelolaan website yang aman dan terpusat.
                  </h2>
                  <p class="mt-3 text-sm leading-7 text-slate-600">
                    Gunakan akun admin resmi untuk masuk dan mengelola seluruh konten
                    website HMPS RPL.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Login Card -->
        <section class="mx-auto w-full max-w-[29rem] lg:ml-auto">
          <div
            class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_30px_100px_rgba(15,23,42,0.12)]"
          >
            <div
              class="relative overflow-hidden border-b border-slate-200 bg-slate-50 px-6 py-6 sm:px-8"
            >
              <div
                class="pointer-events-none absolute -right-14 -top-16 h-44 w-44 rounded-full bg-red-100 blur-[55px]"
              />

              <div class="relative z-10 flex items-center gap-4">
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-[0_12px_30px_rgba(15,23,42,0.10)] ring-1 ring-slate-200"
                >
                  <img
                    :src="logoPath"
                    :alt="logoAlt"
                    class="h-full w-full object-cover"
                    loading="eager"
                    decoding="async"
                  />
                </div>

                <div class="min-w-0">
                  <p
                    class="truncate text-lg font-black tracking-[-0.03em] text-slate-950"
                  >
                    Login Admin
                  </p>
                  <p class="mt-1 truncate text-sm font-semibold text-slate-500">
                    HMPS Rekayasa Perangkat Lunak
                  </p>
                </div>
              </div>
            </div>

            <div class="px-6 py-6 sm:px-8 sm:py-8">
              <div
                v-if="flashSuccess"
                class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"
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
                class="mb-4 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"
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

              <div class="mb-6 lg:hidden">
                <div
                  class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3.5 py-2 text-[0.7rem] font-black uppercase tracking-[0.12em] text-red-700"
                >
                  <span class="h-2 w-2 rounded-full bg-red-500" />
                  Secure Admin Access
                </div>

                <h1
                  class="mt-4 text-3xl font-black leading-tight tracking-[-0.05em] text-slate-950"
                >
                  Masuk ke Dashboard
                </h1>

                <p class="mt-2 text-justify text-sm leading-7 text-slate-600">
                  Kelola konten website HMPS RPL secara aman dan terpusat.
                </p>
              </div>

              <form class="grid gap-5" @submit.prevent="submit">
                <!-- Username -->
                <div>
                  <label class="mb-2 block text-sm font-bold text-slate-800">
                    Username Admin
                  </label>

                  <div class="relative">
                    <span
                      class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
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
                          stroke-width="2.15"
                          d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"
                        />
                      </svg>
                    </span>

                    <input
                      v-model="form.username"
                      type="text"
                      autocomplete="username"
                      placeholder="Masukkan username admin"
                      autofocus
                      class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-12 text-sm font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />
                  </div>

                  <p
                    v-if="form.errors.username"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ form.errors.username }}
                  </p>
                </div>

                <!-- Password -->
                <div>
                  <label class="mb-2 block text-sm font-bold text-slate-800">
                    Password
                  </label>

                  <div class="relative">
                    <span
                      class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
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
                          stroke-width="2.15"
                          d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4"
                        />
                      </svg>
                    </span>

                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      autocomplete="current-password"
                      placeholder="Masukkan password"
                      class="h-[52px] w-full rounded-2xl border border-slate-200 bg-slate-50/80 px-12 pr-[4.7rem] text-sm font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                    />

                    <button
                      type="button"
                      class="absolute right-2 top-1/2 inline-flex h-9 -translate-y-1/2 items-center justify-center rounded-xl px-3 text-xs font-black text-slate-500 transition hover:bg-red-50 hover:text-red-700"
                      @click="showPassword = !showPassword"
                    >
                      {{ showPassword ? "Sembunyi" : "Lihat" }}
                    </button>
                  </div>

                  <p
                    v-if="form.errors.password"
                    class="mt-2 text-xs font-bold text-red-600"
                  >
                    {{ form.errors.password }}
                  </p>
                </div>

                <div
                  class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                  <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                    <input
                      v-model="form.remember"
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-300 bg-white text-red-600 focus:ring-red-500"
                    />
                    Ingat saya
                  </label>

                  <a
                    href="/"
                    class="text-sm font-bold text-slate-500 transition hover:text-red-700"
                  >
                    Kembali ke website
                  </a>
                </div>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="group relative inline-flex min-h-[54px] items-center justify-center gap-2 overflow-hidden rounded-2xl bg-[linear-gradient(135deg,#ef4444,#dc2626,#991b1b)] px-6 text-sm font-black text-white shadow-[0_16px_34px_rgba(220,38,38,0.24)] transition hover:-translate-y-[2px] hover:shadow-[0_24px_50px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0"
                >
                  <span
                    class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.22),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
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
                      d="M15.75 9V5.75A2.75 2.75 0 0 0 13 3H6.75A2.75 2.75 0 0 0 4 5.75v12.5A2.75 2.75 0 0 0 6.75 21H13a2.75 2.75 0 0 0 2.75-2.75V15M10 12h10m0 0-3-3m3 3-3 3"
                    />
                  </svg>

                  <span class="relative z-10">
                    {{ form.processing ? "Memproses Login..." : "Masuk Dashboard Admin" }}
                  </span>
                </button>
              </form>

              <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex items-start gap-3">
                  <svg
                    class="mt-0.5 h-5 w-5 shrink-0 text-red-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                  </svg>
                  <p class="text-xs font-semibold leading-6 text-slate-500">
                    Akses ini hanya untuk pengelola website HMPS RPL. Pastikan username
                    dan password tidak dibagikan kepada pihak lain.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <p class="mt-5 text-center text-xs font-semibold text-slate-500">
            © {{ currentYear }} HMPS RPL. Secure Admin Access.
          </p>
        </section>
      </div>
    </section>
  </main>
</template>

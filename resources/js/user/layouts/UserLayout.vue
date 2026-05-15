<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import Navbar from "@/user/components/Navbar.vue";
import Footer from "@/user/components/Footer.vue";

const page = usePage();

const pageKey = computed(() => String(page.url || "hmps-rpl-page").split("#")[0]);
</script>

<template>
  <div
    class="relative min-h-dvh [overflow-x:clip] bg-white text-slate-900 antialiased selection:bg-red-500/15 selection:text-red-800"
  >
    <a
      href="#main-content"
      class="sr-only fixed left-3 top-3 z-[70] rounded-full bg-white px-4 py-2.5 text-sm font-bold text-red-700 shadow-[0_14px_35px_rgba(15,23,42,0.18)] ring-1 ring-slate-900/10 transition focus:not-sr-only focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/60"
    >
      Lewati ke konten utama
    </a>

    <div
      class="pointer-events-none fixed inset-x-0 top-0 z-0 h-[18rem] overflow-hidden xl:hidden"
      aria-hidden="true"
    >
      <div
        class="absolute -right-16 top-16 h-44 w-44 rounded-full bg-red-500/10 blur-3xl"
      />
      <div
        class="absolute -left-20 top-28 h-52 w-52 rounded-full bg-slate-900/[0.05] blur-3xl"
      />
    </div>

    <Navbar />

    <main
      id="main-content"
      role="main"
      tabindex="-1"
      class="relative z-10 min-h-[calc(100svh-4.25rem)] overflow-visible pt-[68px] outline-none sm:pt-[74px] xl:min-h-screen xl:pt-0"
    >
      <Transition name="layout-page" mode="out-in" appear>
        <div :key="pageKey" class="min-w-0">
          <slot />
        </div>
      </Transition>
    </main>

    <Footer />
  </div>
</template>

<style scoped>
/* Scroll fix:
   Pastikan halaman user hanya memakai satu scrollbar utama dari browser/body.
   overflow-x: clip dipakai agar tidak membuat nested scroll container seperti overflow-x-hidden. */
:global(html),
:global(body),
:global(#app) {
  min-height: 100%;
}

:global(html) {
  overflow-x: clip;
  overflow-y: auto;
  scroll-behavior: smooth;
}

:global(body) {
  overflow-x: clip;
  overflow-y: auto;
}

:global(#app) {
  overflow: visible;
}

@media (max-width: 1279px) {
  :deep(section),
  :deep([id]) {
    scroll-margin-top: 5.75rem;
  }

  :deep(img),
  :deep(video),
  :deep(canvas),
  :deep(svg) {
    max-width: 100%;
  }

  :deep(a),
  :deep(button),
  :deep(input),
  :deep(select),
  :deep(textarea) {
    -webkit-tap-highlight-color: transparent;
  }
}

.layout-page-enter-active,
.layout-page-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.layout-page-enter-from,
.layout-page-leave-to {
  opacity: 0;
  transform: translateY(0.45rem);
}

@media (prefers-reduced-motion: reduce) {
  .layout-page-enter-active,
  .layout-page-leave-active {
    transition: none;
  }

  .layout-page-enter-from,
  .layout-page-leave-to {
    opacity: 1;
    transform: none;
  }
}
</style>

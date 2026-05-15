<script setup>
import { computed } from "vue";

const props = defineProps({
  memberEditForm: {
    type: Object,
    required: true,
  },
  divisions: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  editPreviewPhoto: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["submit", "cancel", "photo-change"]);

const isCoreCategory = computed(() => props.memberEditForm.category === "core");

const selectedCategoryLabel = computed(() => {
  const selected = props.categories.find(
    (category) => category.value === props.memberEditForm.category
  );

  return selected?.label || "Kategori pengurus";
});

const handlePhotoChange = (event) => {
  emit("photo-change", event);
};
</script>

<template>
  <form
    class="space-y-4 pb-24 sm:space-y-6 sm:pb-0"
    data-aos="fade-up"
    data-aos-duration="500"
    @submit.prevent="$emit('submit')"
  >
    <!-- Header Form -->
    <div
      data-aos="fade-up"
      class="overflow-hidden rounded-[1.2rem] border border-slate-200 bg-[linear-gradient(135deg,#ffffff,#f8fafc)] shadow-[0_12px_30px_rgba(15,23,42,0.055)] sm:rounded-[1.5rem] sm:shadow-[0_14px_36px_rgba(15,23,42,0.06)]"
    >
      <div class="relative p-4 sm:p-5">
        <div
          class="pointer-events-none absolute -right-10 -top-12 h-28 w-28 rounded-full bg-red-500/10 blur-2xl"
        />

        <div class="relative flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <div
              class="mb-3 inline-flex items-center gap-2 rounded-full border border-red-500/15 bg-red-50 px-2.5 py-1.5 text-[0.62rem] font-extrabold uppercase tracking-[0.1em] text-red-700 sm:px-3 sm:text-[0.68rem]"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-red-500 sm:h-2 sm:w-2" />
              Form Pengurus
            </div>

            <h3
              class="text-lg font-black leading-tight tracking-[-0.035em] text-slate-950 sm:text-2xl"
            >
              Edit Data Pengurus
            </h3>

            <p
              class="mt-2 max-w-2xl text-left text-[0.82rem] leading-6 text-slate-500 sm:text-justify sm:text-sm sm:leading-7"
            >
              Lengkapi data pengurus dengan benar agar struktur kepengurusan tampil
              rapi pada halaman website.
            </p>
          </div>

          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] text-white shadow-lg shadow-red-500/20 sm:h-12 sm:w-12 sm:rounded-2xl"
          >
            <svg
              class="h-4.5 w-4.5 sm:h-5 sm:w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.35"
                d="M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 13a6 6 0 0 1 12 0M17 11l2 2 4-4"
              />
            </svg>
          </div>
        </div>

        <div
          class="mt-4 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/80 p-2.5 sm:hidden"
        >
          <span
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.2"
                d="M9 12l2 2 4-4"
              />
            </svg>
          </span>

          <div class="min-w-0 flex-1">
            <p class="truncate text-[0.68rem] font-black uppercase tracking-[0.1em] text-slate-400">
              Kategori Terpilih
            </p>
            <p class="truncate text-xs font-black text-slate-900">
              {{ selectedCategoryLabel }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Utama -->
    <section
      class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_12px_30px_rgba(15,23,42,0.045)] sm:rounded-[1.5rem] sm:p-6 sm:shadow-[0_14px_36px_rgba(15,23,42,0.05)]"
    >
      <div class="mb-4 flex items-start justify-between gap-3 sm:mb-5 sm:gap-4">
        <div class="min-w-0">
          <h4 class="text-[0.95rem] font-black tracking-[-0.02em] text-slate-950 sm:text-base">
            Data Utama
          </h4>
          <p class="mt-1 text-[0.78rem] leading-5 text-slate-500 sm:text-sm sm:leading-6">
            Informasi nama, jabatan, kategori, dan urutan tampil pengurus.
          </p>
        </div>

        <span
          class="hidden rounded-full bg-slate-100 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-slate-600 sm:inline-flex"
        >
          {{ selectedCategoryLabel }}
        </span>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 sm:gap-5">
        <!-- Nama -->
        <div>
          <label class="mb-2 block text-[0.82rem] font-extrabold text-slate-800 sm:text-sm">
            Nama Pengurus
            <span class="text-red-600">*</span>
          </label>

          <input
            v-model="memberEditForm.name"
            type="text"
            placeholder="Contoh: Ahmad Zuhri Fahruddin"
            class="h-12 w-full rounded-[1rem] border border-slate-200 bg-slate-50/80 px-3.5 text-[0.88rem] font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:h-[3.25rem] sm:rounded-2xl sm:px-4 sm:text-sm"
          />

          <p
            v-if="memberEditForm.errors?.name"
            class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-600 sm:text-sm"
          >
            {{ memberEditForm.errors.name }}
          </p>
        </div>

        <!-- Jabatan -->
        <div>
          <label class="mb-2 block text-[0.82rem] font-extrabold text-slate-800 sm:text-sm">
            Jabatan
            <span class="text-red-600">*</span>
          </label>

          <input
            v-model="memberEditForm.position"
            type="text"
            placeholder="Contoh: Koordinator PSDM"
            class="h-12 w-full rounded-[1rem] border border-slate-200 bg-slate-50/80 px-3.5 text-[0.88rem] font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:h-[3.25rem] sm:rounded-2xl sm:px-4 sm:text-sm"
          />

          <p
            v-if="memberEditForm.errors?.position"
            class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-600 sm:text-sm"
          >
            {{ memberEditForm.errors.position }}
          </p>
        </div>

        <!-- Kategori -->
        <div>
          <label class="mb-2 block text-[0.82rem] font-extrabold text-slate-800 sm:text-sm">
            Kategori Pengurus
            <span class="text-red-600">*</span>
          </label>

          <select
            v-model="memberEditForm.category"
            class="h-12 w-full rounded-[1rem] border border-slate-200 bg-slate-50/80 px-3.5 text-[0.88rem] font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:h-[3.25rem] sm:rounded-2xl sm:px-4 sm:text-sm"
          >
            <option
              v-for="category in categories"
              :key="category.value"
              :value="category.value"
            >
              {{ category.label }}
            </option>
          </select>

          <p class="mt-2 text-xs leading-5 text-slate-500 sm:block">
            Pilih kategori sesuai posisi pengurus.
          </p>

          <p
            v-if="memberEditForm.errors?.category"
            class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-600 sm:text-sm"
          >
            {{ memberEditForm.errors.category }}
          </p>
        </div>

        <!-- Divisi -->
        <div>
          <label class="mb-2 block text-[0.82rem] font-extrabold text-slate-800 sm:text-sm">
            Divisi
          </label>

          <select
            v-model="memberEditForm.management_division_id"
            :disabled="isCoreCategory"
            class="h-12 w-full rounded-[1rem] border border-slate-200 bg-slate-50/80 px-3.5 text-[0.88rem] font-semibold text-slate-900 outline-none transition duration-300 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:ring-0 sm:h-[3.25rem] sm:rounded-2xl sm:px-4 sm:text-sm"
          >
            <option value="">Pilih Divisi</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">
              {{ division.name }}
            </option>
          </select>

          <p
            v-if="isCoreCategory"
            class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold leading-5 text-amber-700"
          >
            Pengurus inti tidak perlu memilih divisi.
          </p>
          <p v-else class="mt-2 text-xs leading-5 text-slate-500">
            Pilih divisi jika pengurus termasuk koordinator atau anggota divisi.
          </p>

          <p
            v-if="memberEditForm.errors?.management_division_id"
            class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-600 sm:text-sm"
          >
            {{ memberEditForm.errors.management_division_id }}
          </p>
        </div>

        <!-- Urutan Tampil -->
        <div class="sm:col-span-1">
          <label class="mb-2 block text-[0.82rem] font-extrabold text-slate-800 sm:text-sm">
            Urutan Tampil
          </label>

          <input
            v-model="memberEditForm.sort_order"
            type="number"
            min="0"
            placeholder="Contoh: 1"
            class="h-12 w-full rounded-[1rem] border border-slate-200 bg-slate-50/80 px-3.5 text-[0.88rem] font-semibold text-slate-900 outline-none transition duration-300 placeholder:font-medium placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100 sm:h-[3.25rem] sm:rounded-2xl sm:px-4 sm:text-sm"
          />

          <p class="mt-2 text-xs leading-5 text-slate-500">
            Angka kecil tampil lebih awal. Gunakan 0 jika tidak menentukan urutan.
          </p>

          <p
            v-if="memberEditForm.errors?.sort_order"
            class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-600 sm:text-sm"
          >
            {{ memberEditForm.errors.sort_order }}
          </p>
        </div>
      </div>
    </section>

    <!-- Foto Pengurus -->
    <section
      class="rounded-[1.2rem] border border-slate-200 bg-white p-4 shadow-[0_12px_30px_rgba(15,23,42,0.045)] sm:rounded-[1.5rem] sm:p-6 sm:shadow-[0_14px_36px_rgba(15,23,42,0.05)]"
    >
      <div class="mb-4 flex items-start justify-between gap-3 sm:mb-5 sm:flex-row sm:gap-4">
        <div class="min-w-0">
          <h4 class="text-[0.95rem] font-black tracking-[-0.02em] text-slate-950 sm:text-base">
            Foto Pengurus
          </h4>
          <p class="mt-1 max-w-2xl text-left text-[0.78rem] leading-5 text-slate-500 sm:text-justify sm:text-sm sm:leading-6">
            Gunakan foto yang jelas, rapi, dan memiliki komposisi wajah yang baik.
          </p>
        </div>

        <span
          class="hidden w-fit shrink-0 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.08em] text-red-700 sm:inline-flex"
        >
          PNG / JPG / WEBP
        </span>
      </div>

      <div class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
        <!-- Upload -->
        <div class="order-1 lg:order-2">
          <label class="mb-2 block text-[0.82rem] font-extrabold text-slate-800 sm:text-sm">
            Upload Foto
          </label>

          <label
            class="group flex cursor-pointer flex-col items-center justify-center rounded-[1.15rem] border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition duration-300 hover:border-red-300 hover:bg-red-50/40 sm:rounded-[1.35rem] sm:px-5 sm:py-8"
          >
            <div
              class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm transition duration-300 group-hover:scale-105 sm:h-14 sm:w-14"
            >
              <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M12 16V4m0 0-4 4m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                />
              </svg>
            </div>

            <p class="mt-3 text-sm font-black text-slate-950 sm:mt-4">
              Klik untuk memilih foto
            </p>
            <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
              Format PNG, JPG, JPEG, atau WEBP. Gunakan foto vertikal agar card lebih rapi.
            </p>

            <input
              type="file"
              accept="image/png,image/jpeg,image/jpg,image/webp"
              class="sr-only"
              @change="handlePhotoChange"
            />
          </label>

          <p
            v-if="memberEditForm.errors?.photo"
            class="mt-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold leading-5 text-red-600 sm:text-sm"
          >
            {{ memberEditForm.errors.photo }}
          </p>
        </div>

        <!-- Preview -->
        <div
          class="order-2 rounded-[1.15rem] border border-slate-200 bg-slate-50 p-3 sm:rounded-[1.35rem] sm:p-4 lg:order-1"
        >
          <div v-if="editPreviewPhoto" class="flex flex-col items-center text-center">
            <div
              class="relative overflow-hidden rounded-[1.15rem] border border-white bg-white p-2 shadow-[0_14px_32px_rgba(15,23,42,0.08)] sm:rounded-[1.35rem]"
            >
              <img
                :src="editPreviewPhoto"
                alt="Preview foto pengurus"
                class="h-40 w-40 rounded-[0.95rem] object-cover sm:h-52 sm:w-52 sm:rounded-[1.1rem]"
              />
              <div
                class="absolute left-4 top-4 rounded-full bg-emerald-500 px-2.5 py-1 text-[0.6rem] font-extrabold uppercase tracking-[0.08em] text-white shadow-sm sm:px-3 sm:text-[0.65rem]"
              >
                Preview
              </div>
            </div>

            <p class="mt-3 text-sm font-black text-slate-950 sm:mt-4">
              Preview Foto Pengurus
            </p>
            <p class="mt-1 text-xs leading-5 text-slate-500">
              Foto ini akan digunakan setelah data disimpan.
            </p>
          </div>

          <div
            v-else
            class="flex min-h-[13rem] flex-col items-center justify-center rounded-[1rem] border border-dashed border-slate-300 bg-white px-4 py-7 text-center sm:min-h-[16rem] sm:rounded-[1.15rem] sm:px-5 sm:py-8"
          >
            <div
              class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 sm:h-14 sm:w-14"
            >
              <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2.25"
                  d="M4 16l4-4a2 2 0 0 1 2.828 0L14 15m-2-2 1-1a2 2 0 0 1 2.828 0L20 16M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 2h.01"
                />
              </svg>
            </div>

            <p class="mt-3 text-sm font-black text-slate-950 sm:mt-4">
              Belum ada preview foto
            </p>
            <p class="mt-1 max-w-xs text-xs leading-5 text-slate-500">
              Pilih file gambar untuk melihat preview foto di sini.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Action -->
    <div
      class="fixed inset-x-3 bottom-3 z-30 rounded-[1.2rem] border border-slate-200 bg-white/95 p-2.5 shadow-[0_18px_45px_rgba(15,23,42,0.18)] backdrop-blur-xl sm:sticky sm:inset-x-auto sm:bottom-3 sm:rounded-[1.5rem] sm:p-3 sm:shadow-[0_20px_50px_rgba(15,23,42,0.13)]"
    >
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="hidden sm:block">
          <p class="text-sm font-black text-slate-950">Simpan perubahan data?</p>
          <p class="mt-0.5 text-xs font-medium text-slate-500">
            Pastikan nama, jabatan, kategori, divisi, dan foto sudah benar.
          </p>
        </div>

        <div
          class="grid w-full grid-cols-[0.82fr_1.18fr] gap-2 sm:ml-auto sm:w-auto sm:grid-flow-col sm:auto-cols-max"
        >
          <button
            type="button"
            class="inline-flex min-h-[46px] w-full items-center justify-center rounded-[1rem] border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 sm:min-h-[48px] sm:w-auto sm:rounded-2xl sm:px-5"
            :disabled="memberEditForm.processing"
            @click="$emit('cancel')"
          >
            Batal
          </button>

          <button
            type="submit"
            :disabled="memberEditForm.processing"
            class="group relative inline-flex min-h-[46px] w-full items-center justify-center gap-2 overflow-hidden rounded-[1rem] bg-[linear-gradient(135deg,#ef4444,#dc2626,#7f1d1d)] px-4 text-sm font-black text-white shadow-[0_14px_30px_rgba(220,38,38,0.22)] transition duration-300 hover:-translate-y-[1px] hover:shadow-[0_20px_40px_rgba(220,38,38,0.28)] disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0 sm:min-h-[48px] sm:w-auto sm:rounded-2xl sm:px-5"
          >
            <span
              class="absolute inset-0 -translate-x-[140%] bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.20),transparent)] transition-transform duration-700 group-hover:translate-x-[140%]"
            />

            <svg
              v-if="memberEditForm.processing"
              class="relative z-10 h-4 w-4 animate-spin"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
              />
            </svg>

            <svg
              v-else
              class="relative z-10 h-4 w-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.4"
                d="M5 13l4 4L19 7"
              />
            </svg>

            <span class="relative z-10">
              {{ memberEditForm.processing ? "Menyimpan..." : "Simpan" }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </form>
</template>

<template>
  <!-- Perubahan 1: Tambahkan flex-row dan overflow-hidden di root utama agar layout terkunci ke kanan -->
  <div class="min-h-screen w-full flex flex-row bg-slate-100 overflow-hidden">

    <!-- MOBILE DRAWER -->
    <Drawer v-model:visible="sidebarVisible" position="left" class="w-72">
      <template #container>
        <div class="h-full flex flex-col bg-white">
          <!-- BRAND -->
          <div class="px-6 py-5 border-b border-slate-200">
            <div class="flex items-center gap-3">
              <i class="pi pi-building text-2xl text-emerald-500"></i>
              <div>
                <h2 class="font-bold text-slate-800">RUTAN APP</h2>
                <p class="text-xs text-slate-500">Portal Pengelolaan</p>
              </div>
            </div>
          </div>

          <!-- USER -->
          <div v-if="auth.user" class="px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="pi pi-user text-emerald-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-slate-800">{{ auth.user.name }}</p>
                <p class="text-xs text-slate-500">Administrator</p>
              </div>
            </div>
          </div>

          <!-- MENU MOBILE -->
          <nav class="flex-1 px-4 py-4 space-y-2">

            <div v-for="menu in menus" :key="menu.label">

              <!-- SINGLE -->
              <RouterLink v-if="!menu.children" :to="menu.to"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100"
                active-class="bg-emerald-500 text-white" @click="sidebarVisible = false">
                <i :class="menu.icon"></i>
                <span>{{ menu.label }}</span>
              </RouterLink>

              <!-- GROUP -->
              <div v-else>

                <div
                  class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 font-medium hover:bg-slate-100 cursor-pointer"
                  @click="toggleMenu(menu.label)">
                  <div class="flex items-center gap-3">
                    <i :class="menu.icon"></i>
                    <span>{{ menu.label }}</span>
                  </div>

                  <i class="pi" :class="openMenus[menu.label] ? 'pi-chevron-down' : 'pi-chevron-right'" />
                </div>

                <div v-if="openMenus[menu.label]" class="ml-6 space-y-1">

                  <RouterLink v-for="child in menu.children" :key="child.to" :to="child.to"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100"
                    active-class="bg-emerald-500 text-white" @click="sidebarVisible = false">
                    <i :class="child.icon"></i>
                    <span>{{ child.label }}</span>
                  </RouterLink>

                </div>

              </div>

            </div>

          </nav>

          <div class="p-4 border-t border-slate-200">
            <Button label="Logout" icon="pi pi-sign-out" severity="danger" outlined class="w-full"
              @click="handleLogout" />
          </div>
        </div>
      </template>
    </Drawer>

    <!-- DESKTOP SIDEBAR -->
    <!-- Perubahan 2: Tambahkan flex-shrink-0 agar lebar sidebar stabil di 64 (256px) dan tidak tertekan konten kanan -->
    <aside class="hidden md:flex flex-col w-64 flex-shrink-0 bg-white border-r border-slate-200 shadow-sm">
      <div class="px-6 py-5 border-b border-slate-200">
        <div class="flex items-center gap-3">
          <i class="pi pi-building text-2xl text-emerald-500"></i>
          <div>
            <h2 class="font-bold text-slate-800">RUTAN APP</h2>
            <p class="text-xs text-slate-500">Portal Pengelolaan</p>
          </div>
        </div>
      </div>

      <div v-if="auth.user" class="px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
            <i class="pi pi-user text-emerald-600"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-slate-800">{{ auth.user.name }}</p>
            <p class="text-xs text-slate-500">Administrator</p>
          </div>
        </div>
      </div>

      <!-- <nav class="flex-1 px-4 py-4 space-y-2">
        <RouterLink to="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 font-medium transition-all duration-200 hover:bg-slate-100 hover:text-slate-900" active-class="bg-emerald-500 text-white shadow-sm">
          <i class="pi pi-home"></i> <span>Dashboard</span>
        </RouterLink>
        <RouterLink to="/items" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 font-medium transition-all duration-200 hover:bg-slate-100 hover:text-slate-900" active-class="bg-emerald-500 text-white shadow-sm">
          <i class="pi pi-box"></i> <span>Stok Barang</span>
        </RouterLink>
        <RouterLink to="/request-management" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 font-medium transition-all duration-200 hover:bg-slate-100 hover:text-slate-900" active-class="bg-emerald-500 text-white shadow-sm">
          <i class="pi pi-inbox"></i> <span>Permohonan Barang</span>
        </RouterLink>
        <RouterLink to="/employee" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 font-medium transition-all duration-200 hover:bg-slate-100 hover:text-slate-900" active-class="bg-emerald-500 text-white shadow-sm">
          <i class="pi pi-inbox"></i> <span>Data Pegawai</span>
        </RouterLink>
        <RouterLink to="/absensi" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 font-medium transition-all duration-200 hover:bg-slate-100 hover:text-slate-900" active-class="bg-emerald-500 text-white shadow-sm">
          <i class="pi pi-inbox"></i> <span>Management Absensi</span>
        </RouterLink>
      </nav> -->


      <nav class="flex-1 px-4 py-4 space-y-2">

        <div v-for="menu in menus" :key="menu.label">

          <!-- PARENT MENU -->
          <div v-if="menu.children">

            <button
              class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100"
              @click="toggleMenu(menu.label)">

              <div class="flex items-center gap-3">
                <i :class="menu.icon"></i>
                <span>{{ menu.label }}</span>
              </div>

              <i class="pi" :class="openMenus[menu.label] ? 'pi-chevron-down' : 'pi-chevron-right'" />

            </button>

            <!-- CHILD MENU -->
            <div v-if="openMenus[menu.label]" class="ml-6 mt-1 space-y-1">

              <RouterLink v-for="child in menu.children" :key="child.to" :to="child.to"
                class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                active-class="bg-emerald-500 text-white">
                <i :class="child.icon"></i>
                <span>{{ child.label }}</span>
              </RouterLink>

            </div>

          </div>

          <!-- SINGLE MENU -->
          <RouterLink v-else :to="menu.to"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100  hover:text-slate-900"
            active-class="bg-emerald-500 text-white">
            <i :class="menu.icon"></i>
            <span>{{ menu.label }}</span>
          </RouterLink>

        </div>

      </nav>

      <div class="p-4 border-t border-slate-200">
        <Button label="Logout" icon="pi pi-sign-out" severity="danger" outlined class="w-full" @click="handleLogout" />
      </div>
    </aside>

    <!-- CONTENT AREA -->
    <!-- Perubahan 3: Dikunci dengan min-w-0 h-screen agar areanya presisi di kanan sidebar -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <!-- TOPBAR MOBILE -->
      <header
        class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between flex-shrink-0">
        <Button icon="pi pi-bars" text rounded @click="sidebarVisible = true" />
        <h1 class="font-semibold text-slate-800">RUTAN APP</h1>
        <div class="w-9"></div>
      </header>

      <!-- MAIN ROUTER VIEW -->
      <!-- Perubahan 4: overflow-auto di sini yang mengizinkan halaman melakukan scroll internal -->
      <main class="flex-1 p-4 md:p-6 overflow-auto">
        <router-view />
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'





const menus = [
  {
    label: 'Dashboard',
    icon: 'pi pi-home',
    to: '/dashboard'
  },

  {
    label: 'Perlengkapan',
    icon: 'pi pi-box',
    children: [
      { label: 'Dashboard', to: '/dashboard', icon: 'pi pi-home' },
      { label: 'Stok Barang', to: '/items', icon: 'pi pi-box' },
      { label: 'Permohonan Barang', to: '/request-management', icon: 'pi pi-inbox' },
    ]
  },

  {
    label: 'Kepegawaian',
    icon: 'pi pi-users',
    children: [
      { label: 'Data Pegawai', to: '/employee', icon: 'pi pi-user' },
      { label: 'Absensi', to: '/absensi', icon: 'pi pi-calendar' },
    ]
  },


];



const openMenus = ref({});

const toggleMenu = (label) => {
  openMenus.value[label] = !openMenus.value[label];
};


const renderMenu = (isMobile = false) => {
  return menus;
};




const auth = useAuthStore()
const sidebarVisible = ref(false)


const router = useRouter()

const handleLogout = async () => {
  await auth.logout()
  router.push('/')
}
</script>
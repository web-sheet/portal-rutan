<template>
  <Drawer :visible="visible" @update:visible="$emit('update:visible', $event)" position="left" class="w-72"
    :transitionOptions="'animation-duration: 250ms; animation-timing-function: cubic-bezier(0.25, 1, 0.5, 1);'">
    <template #container>
      <div class="h-full flex flex-col bg-white">
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
              <p class="text-xs text-slate-500 uppercase font-semibold text-emerald-600">{{ auth.user.role }}</p>
            </div>
          </div>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
          <div v-for="menu in filteredMenus" :key="menu.label">
            <RouterLink v-if="!menu.children" :to="menu.to"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors duration-150"
              active-class="bg-emerald-500 text-white" @click="$emit('close')">
              <i :class="menu.icon"></i>
              <span>{{ menu.label }}</span>
            </RouterLink>

            <div v-else>
              <div
                class="flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 font-medium hover:bg-slate-100 cursor-pointer transition-colors duration-150"
                @click="toggleMenu(menu.label)">
                <div class="flex items-center gap-3">
                  <i :class="menu.icon"></i>
                  <span>{{ menu.label }}</span>
                </div>
                <i class="pi transition-transform duration-200"
                  :class="openMenus[menu.label] ? 'pi-chevron-down rotate-180' : 'pi-chevron-right'" />
              </div>

              <div v-if="openMenus[menu.label]" class="ml-6 space-y-1 sub-menu-active animate-fade-in">
                <RouterLink v-for="child in menu.children" :key="child.to" :to="child.to"
                  class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100 transition-colors duration-150"
                  active-class="bg-emerald-500 text-white" @click="$emit('close')">
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
          <p class="text-xs text-slate-500 uppercase font-semibold text-emerald-600">{{ auth.user.role }}</p>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
      <div v-for="menu in filteredMenus" :key="menu.label">
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

          <div v-if="openMenus[menu.label]" class="ml-6 mt-1 space-y-1">
            <RouterLink v-for="child in menu.children" :key="child.to" :to="child.to"
              class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900"
              active-class="bg-emerald-500 text-white">
              <i :class="child.icon"></i>
              <span>{{ child.label }}</span>
            </RouterLink>
          </div>
        </div>

        <RouterLink v-else :to="menu.to"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900"
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
</template>

<script setup>
import { ref, computed } from 'vue'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

// Memperbaiki pembacaan binding v-model agar sinkron sempurna dengan parent
defineProps({
  visible: Boolean
})
defineEmits(['update:visible', 'close'])

const auth = useAuthStore()
const router = useRouter()
const openMenus = ref({})

const menus = [
  {
    label: 'Perlengkapan',
    icon: 'pi pi-box',
    roles: ['admin', 'kasi', 'perlengkapan','staf_perlengkapan'], // Role yang bisa akses
    children: [
      { label: 'Dashboard Perlengkapan', to: '/dashboard', icon: 'pi pi-home' },
      { label: 'Stok Barang', to: '/items', icon: 'pi pi-box' },
      { label: 'Permohonan Barang', to: '/request-management', icon: 'pi pi-inbox' },
    ]
  },
  {
    label: 'Kepegawaian',
    icon: 'pi pi-users',
    roles: ['admin', 'kasi', 'kepegawaian'],
    children: [
      { label: 'Dashboard Kepegawaian', to: { name: 'absensi.dashboard' }, icon: 'pi pi-home' },
      { label: 'Data Pegawai', to: '/employee', icon: 'pi pi-user' },
      { label: 'Absensi', to: '/absensi', icon: 'pi pi-calendar' },
    ]
  },
  {
    label: 'Manajemen Petugas',
    icon: 'pi pi-cog',
    to: '/users-management',
    roles: ['admin'] // HANYA ADMIN
  },
  { label: 'Profile', icon: 'pi pi-user', to: '/profile', roles: ['admin', 'kasi', 'kepegawaian', 'perlengkapan', 'karutan','staf_perlengkapan'] },
]

const filteredMenus = computed(() => {
  if (!auth.user) return [];
  return menus.filter(menu => menu.roles.includes(auth.user.role));
});

const toggleMenu = (label) => {
  openMenus.value[label] = !openMenus.value[label]
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/')
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
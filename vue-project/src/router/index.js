import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import AppLayout from "@/layouts/AppLayout.vue";

import Login from "@/pages/Login.vue";
import DashboardPage from "@/pages/DashboardPage.vue";

 
import PublicLanding from "@/pages/PublicLanding.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    
    {
      path: "/",
      name: "home",
      component: PublicLanding,
      meta: { public: true },
    },
 
    {
      path: "/login",
      component: Login,
      meta: { public: true },
    },

     
    {
      path: "/requests",
      name: "requests",
      component: () => import("@/pages/requests/RequestPage.vue"),
      meta: { public: true },
    },
 
    {
      path: "/",
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: "dashboard",
          name: "dashboard",
          component: DashboardPage,
          meta: { allowedRoles: ["admin", "kasi", "perlengkapan"] },
        },
        {
          path: "items",
          name: "items",
          component: () => import("@/pages/items/ItemListPage.vue"),
          meta: { allowedRoles: ["admin", "kasi", "perlengkapan"] },
        },
        {
          path: "request-management",
          name: "request-management",
          component: () => import("@/pages/requests/RequestManagementPage.vue"),
          meta: { allowedRoles: ["admin", "kasi", "perlengkapan"] },
        },
        {
          path: "employee",
          name: "employee",
          component: () => import("@/pages/employees/EmployeeData.vue"),
          meta: { allowedRoles: ["admin", "kasi", "kepegawaian"] },
        },

        {
          path: "/absensi",
          name: "absensi",
          component: () => import("@/pages/absensi/AbsensiView.vue"),
          meta: { allowedRoles: ["admin", "kasi", "kepegawaian"] },
        },

        {
          path: "/profile",
          name: "profile",
          component: () => import("@/pages/Profile.vue"),
        },

        {
          path: "/users-management",
          name: "users-management",
          component: () => import("@/pages/UserManagement.vue"),
          meta: { allowedRoles: "admin" },
        },

        {
          path: "/absensi/template",
          name: "absensi.template",
          component: () => import("@/views/AbsensiTemplate.vue"),
        },

        {
          path: "absensi/dashboard",
          name: "absensi.dashboard",
          component: () => import("@/views/AbsensiDashboard.vue"),
        },
      ],
    },
  ],
});

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();

  // route public bebas akses
  if (to.meta.public) {
    return next();
  }

  if (!auth.user) {
    await auth.fetchUser().catch(() => {});
  }

  if (!auth.user) {
    return next("/login");
  }

  // Logika pengecekan role
  if (to.meta.allowedRoles && !to.meta.allowedRoles.includes(auth.user.role)) {
    return next('/'); // Lempar kembali ke dashboard jika tidak punya akses
  }

  next();
});

export default router;

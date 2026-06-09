import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import AppLayout from "@/layouts/AppLayout.vue";

import Login from "@/pages/Login.vue";
import DashboardPage from "@/pages/DashboardPage.vue";

// public page
import PublicLanding from "@/pages/PublicLanding.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // PUBLIC LANDING PAGE
    {
      path: "/",
      name: "home",
      component: PublicLanding,
      meta: { public: true },
    },

    // LOGIN
    {
      path: "/login",
      component: Login,
      meta: { public: true },
    },

    // PUBLIC FORM
    {
      path: "/requests",
      name: "requests",
      component: () => import("@/pages/requests/RequestPage.vue"),
      meta: { public: true },
    },

    // ADMIN AREA
    {
      path: "/",
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: "dashboard",
          name: "dashboard",
          component: DashboardPage,
        },
        {
          path: "items",
          name: "items",
          component: () => import("@/pages/items/ItemListPage.vue"),
        },
        {
          path: "request-management",
          name: "request-management",
          component: () => import("@/pages/requests/RequestManagementPage.vue"),
        },
        {
          path: "employee",
          name: "employee",
          component: () => import("@/pages/employees/EmployeeData.vue"),
        },

        {
          path: "/absensi",
          name: "absensi",
               component: () => import("@/pages/absensi/AbsensiView.vue"),
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

  next();
});

export default router;

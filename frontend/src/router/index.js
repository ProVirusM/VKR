import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import UserDashboard from '@/views/UserDashboard.vue'
import CreateOrder from '@/views/customer/CreateOrder.vue'
import CustomerOrders from '@/views/customer/Orders.vue'
import ContractorReviews from '@/views/contractor/Reviews.vue'
import ApprovedOrders from '@/views/contractor/ApprovedOrders.vue'
import RespondedOrders from '@/views/contractor/RespondedOrders.vue'
//import Dashboard from '@/views/Dashboard.vue'
//import Login from '@/views/Login.vue'
import ActiveOrders from '@/views/customer/ActiveOrders.vue'
import CompletedOrders from '@/views/customer/CompletedOrders.vue'
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/customer/orders',
      name: 'orders',
      component: () => import('../views/Orders.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue')
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/UserDashboard.vue')
    },
    { path: '/customer/active-orders', component: ActiveOrders },
    { path: '/customer/completed-orders', component: CompletedOrders },
    // Customer
    { path: '/customer/create-order', component: CreateOrder },
    { path: '/customer/orders', component: CustomerOrders },

    // Contractor
    { path: '/contractor/reviews', component: ContractorReviews },
    { path: '/contractor/approved-orders', component: ApprovedOrders },
    { path: '/contractor/responded-orders', component: RespondedOrders },
  ],
})

export default router

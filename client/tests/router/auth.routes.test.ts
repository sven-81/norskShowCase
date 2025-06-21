import { createRouter, createMemoryHistory } from 'vue-router'
import { describe, expect, it } from 'vitest'

import AuthRoutes from '@/router/auth.routes'
import { Login, Register } from '@/views/auth'
import Home from '@/views/HomeLayout.vue'

describe('Auth Routes Configuration', () => {
  const routes = [
    { ...AuthRoutes }  // Spread the AuthRoutes directly into the routes array
  ];

  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  });

  it('should contain root route "/" with Home component', () => {
    const homeRoute = router.getRoutes().find(route => route.path === '/');
    expect(homeRoute).toBeDefined();
    expect(homeRoute?.components?.default || homeRoute?.component).toBe(Home);
  });

  it('should contain redirect from "/" to "/login"', () => {
    const redirectRoute = AuthRoutes.children?.find(route => route.redirect === 'login');
    expect(redirectRoute).toBeDefined();
    expect(redirectRoute?.path).toBe('');
  });

  it('should contain "/login" route with Login component', () => {
    const loginRoute = router.getRoutes().find(route => route.path === '/login');
    expect(loginRoute).toBeDefined();
    expect(loginRoute?.components?.default || loginRoute?.component).toBe(Login);
  });

  it('should contain "/register" route with Register component', () => {
    const registerRoute = router.getRoutes().find(route => route.path === '/register');
    expect(registerRoute).toBeDefined();
    expect(registerRoute?.components?.default || registerRoute?.component).toBe(Register);
  });
});

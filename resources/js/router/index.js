import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../stores/auth';

import LoginView from '../views/auth/LoginView.vue';
import RegisterView from '../views/auth/RegisterView.vue';
import TeacherDashboard from '../views/teacher/TeacherDashboard.vue';
import SessionDisplay from '../views/teacher/SessionDisplay.vue';
import StudentDashboard from '../views/student/StudentDashboard.vue';
import StudentScanView from '../views/student/StudentScanView.vue';
import AttendanceReport from '../views/reports/AttendanceReport.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        redirect: () => {
            const { isAuthenticated, isTeacher, isStudent } = useAuth();
            if (!isAuthenticated.value) return '/login';
            return isTeacher.value ? '/teacher' : '/student';
        },
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { guestOnly: true },
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: { guestOnly: true },
    },
    {
        path: '/teacher',
        name: 'teacher-dashboard',
        component: TeacherDashboard,
        meta: { requiresAuth: true, role: 'teacher' },
    },
    {
        path: '/teacher/session/:id',
        name: 'session-display',
        component: SessionDisplay,
        meta: { requiresAuth: true, role: 'teacher' },
    },
    {
        path: '/student',
        name: 'student-dashboard',
        component: StudentDashboard,
        meta: { requiresAuth: true, role: 'student' },
    },
    {
        path: '/student/scan',
        name: 'student-scan',
        component: StudentScanView,
        meta: { requiresAuth: true, role: 'student' },
    },
    {
        path: '/reports',
        name: 'attendance-report',
        component: AttendanceReport,
        meta: { requiresAuth: true },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const { isAuthenticated, isTeacher, isStudent } = useAuth();

    if (to.meta.requiresAuth && !isAuthenticated.value) {
        return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    if (to.meta.guestOnly && isAuthenticated.value) {
        return next(isTeacher.value ? '/teacher' : '/student');
    }

    if (to.meta.role) {
        if (to.meta.role === 'teacher' && !isTeacher.value) {
            return next('/student');
        }
        if (to.meta.role === 'student' && !isStudent.value) {
            return next('/teacher');
        }
    }

    next();
});

export default router;

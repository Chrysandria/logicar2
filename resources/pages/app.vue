<template>
    <div v-if="initialized">
        <router-view/>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useLoading, close } from '../services/alert';
import { useRouter } from 'vue-router';
import { useStore } from '../stores/authStore';

const store = useStore();
const router = useRouter();
const initialized = ref(false);

onMounted(async() => {
    useLoading();
    await store.getConnected();
    await router.isReady();
    initialized.value = true
    close();
})
</script>

<style>
:root {
    --brand-blue: #1769aa;
    --brand-red: #db3b43;
    --ink: #1d2b3a;
    --muted: #6b7785;
    --surface: #fff;
    --page: #f3f6fa;
}
body {
    color: var(--ink);
    background-color: var(--page);
}
.app-sidebar {
    width: 270px;
    flex: 0 0 270px;
    min-height: 100vh;
    background: #182b3d;
    box-shadow: 8px 0 26px rgba(20, 40, 60, .12);
}
.app-sidebar .offcanvas-body { overflow-y: auto; }
.app-sidebar .offcanvas-header { flex-shrink: 0; }
.offcanvas-lg.offcanvas-start { --bs-offcanvas-width: 270px; }
.user-summary { background: rgba(255,255,255,.07); }
.avatar-circle {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    color: #fff;
    background: var(--brand-blue);
    border-radius: 50%;
    font-size: 1.25rem;
}
.sidebar-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .7rem .85rem;
    color: rgba(255,255,255,.78);
    border-radius: .65rem;
    text-decoration: none;
    transition: color .18s ease, background .18s ease, transform .18s ease;
}
.sidebar-link:hover { color: #fff; background: rgba(35,128,199,.18); transform: translateX(2px); }
.sidebar-link.active { color: #fff; background: rgba(35,128,199,.48); box-shadow: inset 3px 0 0 #78c4f4; font-weight: 700; }
.sidebar-link i { width: 1.25rem; text-align: center; }
.app-content { min-width: 0; padding: clamp(1rem, 3vw, 2.5rem); }
.mobile-header { background: rgba(255,255,255,.94); border-bottom: 1px solid #e6ebf0; }
.page-heading { margin-bottom: 1.5rem; }
.page-heading h1 { font-weight: 750; letter-spacing: -.035em; }
.page-heading p { color: var(--muted); margin-bottom: 0; }
.surface-card { background: var(--surface); border: 1px solid #e7edf3; border-radius: 1rem; box-shadow: 0 10px 28px rgba(28, 54, 80, .06); }
.stat-card { height: 100%; padding: 1.1rem; }
.stat-icon { display: grid; place-items: center; width: 2.8rem; height: 2.8rem; border-radius: .8rem; font-size: 1.2rem; }
.table > :not(caption) > * > * { padding: .8rem .75rem; }
.table thead th { color: #657487; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
.form-control, .form-select { min-height: 44px; border-color: #dce4ec; }
.form-control:focus, .form-select:focus { border-color: #78add7; box-shadow: 0 0 0 .2rem rgba(23,105,170,.13); }
.btn { border-radius: .65rem; font-weight: 600; }
.empty-state { padding: 3rem 1rem; color: var(--muted); text-align: center; }
.empty-state i { display: block; margin-bottom: .75rem; font-size: 2.2rem; color: #9aa8b7; }
.home-visual { max-width: 680px; margin-inline: auto; }
.search-control { width: min(100%, 300px); }
.table-responsive { scrollbar-color: #b7c5d3 transparent; scrollbar-width: thin; }
.table-responsive .table { min-width: 620px; }
.modal-content { border: 0; border-radius: 1rem; box-shadow: 0 20px 60px rgba(20, 40, 60, .2); }
.modal-header, .modal-footer { border-color: #edf1f5; }
.alert { border-radius: .75rem; }
.badge { font-weight: 600; }
.link-secondary:hover { color: var(--brand-blue) !important; }
.navbar-brand { letter-spacing: -.03em; }
.form-check-input { width: 1.15rem; height: 1.15rem; cursor: pointer; }
.form-check-input:checked { background-color: var(--brand-blue); border-color: var(--brand-blue); }
.table tbody tr:last-child > * { border-bottom-width: 0; }
.card, .surface-card { overflow-wrap: anywhere; }
.btn-primary { --bs-btn-bg: var(--brand-blue); --bs-btn-border-color: var(--brand-blue); --bs-btn-hover-bg: #11598f; --bs-btn-hover-border-color: #11598f; }
.btn-outline-primary { --bs-btn-color: var(--brand-blue); --bs-btn-border-color: var(--brand-blue); --bs-btn-hover-bg: var(--brand-blue); --bs-btn-hover-border-color: var(--brand-blue); }
.text-primary { color: var(--brand-blue) !important; }
.text-bg-primary { background-color: var(--brand-blue) !important; }
.focus-ring:focus-visible, a:focus-visible, button:focus-visible { outline: 3px solid rgba(23,105,170,.45); outline-offset: 2px; }
.app-content > section { max-width: 1500px; margin-inline: auto; }
.mobile-header .navbar { min-height: 64px; }
.mobile-header .navbar-toggler { border: 0; box-shadow: none; }
@media (max-width: 991.98px) {
    .app-sidebar { width: min(86vw, 310px); min-height: 100%; }
    .offcanvas-lg.offcanvas-start { --bs-offcanvas-width: min(86vw, 310px); }
    .app-content { padding: 1rem; }
    .page-heading { margin-bottom: 1.1rem; }
}
@media (min-width: 992px) {
    .app-sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        height: 100vh;
        z-index: 1020;
    }
    .app-content {
        margin-left: 270px;
    }
}
@media (max-width: 575.98px) {
    .stat-card { padding: .85rem; gap: .65rem !important; }
    .stat-icon { width: 2.45rem; height: 2.45rem; flex: 0 0 2.45rem; }
    .page-heading h1 { font-size: 1.55rem; }
    .table > :not(caption) > * > * { padding: .65rem .55rem; }
    .modal-dialog { margin: .5rem; }
}
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
}
</style>

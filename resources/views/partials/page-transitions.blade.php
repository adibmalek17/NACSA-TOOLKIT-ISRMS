<style>
/* Animate page content without moving the navigation or delaying links. */
@keyframes toolkit-page-enter {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@media screen and (prefers-reduced-motion: no-preference) {
    #main-content {
        animation: toolkit-page-enter 220ms ease-out;
    }
}
@media print, (prefers-reduced-motion: reduce) {
    #main-content { animation: none !important; }
}
</style>

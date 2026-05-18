<script>
    (function() {
        var dark = localStorage.getItem('darkMode');
        if (dark === 'true' || dark === null) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>